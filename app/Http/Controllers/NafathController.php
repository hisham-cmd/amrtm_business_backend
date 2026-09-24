<?php

namespace App\Http\Controllers;

use App\Models\Business\BusinessUser;
use App\Services\NafathException;
use App\Services\NafathService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NafathController extends Controller
{
    public const INTENTS = ['login', 'register'];

    public function __construct(private NafathService $nafath) {}

    /**
     * عرض صفحة بدء التحقق عبر النفاذ الوطني.
     */
    public function show(Request $request): View|RedirectResponse
    {
        if ($this->authenticated()) {
            return redirect()->route('amrtm.dashboard.hub');
        }

        return view('update_service.nafath.verify', [
            'intent'                => $this->intent($request->query('intent')),
            'initialNationalId'     => $request->query('national_id'),
            'nafathConfigured'      => $this->nafath->isConfigured(),
        ]);
    }

    /**
     * بدء طلب تحقق: يُصدر إشعاراً لتطبيق نفاذ على جوال صاحب الهوية.
     */
    public function verify(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'national_id' => ['required', 'digits:10', 'regex:/^[12][0-9]{9}$/'],
        ]);

        $intent = $this->intent($request->input('intent'));

        if (!$this->nafath->isConfigured()) {
            return $this->failure($request, 'واجهة النفاذ الوطني غير مفعّلة بعد في هذه البيئة. تواصل مع إدارة المنصة.');
        }

        $nationalId = $validated['national_id'];
        $random     = $this->nafath->randomCode();

        try {
            $result   = $this->nafath->initiate($nationalId, $random);
            $transId  = data_get($result, 'transId');
        } catch (NafathException $e) {
            report($e);
            return $this->failure($request, 'تعذر الاتصال بخدمة النفاذ الوطني. حاول مرة أخرى لاحقاً.');
        }

        if (!is_string($transId) || $transId === '') {
            return $this->failure($request, 'لم تستجب خدمة النفاذ الوطني بشكل صحيح. أعد المحاولة.');
        }

        $request->session()->put('nafath_pending', [
            'national_id' => $nationalId,
            'random'      => $random,
            'trans_id'    => $transId,
            'intent'      => $intent,
            'expires_at'  => now()->addMinutes((int) config('nafath.request_ttl_minutes'))->timestamp,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'trans_id' => $transId,
                'random'   => $random,
                'status'   => 'WAITING',
                'redirect' => route('amrtm.nafath.wait'),
            ]);
        }

        return redirect()->route('amrtm.nafath.wait');
    }

    /**
     * صفحة الانتظار: تُعرض رقم الطلب للمطابقة داخل تطبيق نفاذ وتستطلع
     * النتيجة كل ثوانٍ معدودة.
     */
    public function wait(Request $request): View|RedirectResponse
    {
        if ($this->authenticated()) {
            return redirect()->route('amrtm.dashboard.hub');
        }

        $pending = $request->session()->get('nafath_pending');

        if (!$pending || now()->timestamp > (int) ($pending['expires_at'] ?? 0)) {
            $intent = is_array($pending) ? ($pending['intent'] ?? 'login') : 'login';
            $request->session()->forget('nafath_pending');

            return redirect()->route('amrtm.nafath.show', ['intent' => $intent]);
        }

        return view('update_service.nafath.wait', [
            'random'        => $pending['random'],
            'transId'       => $pending['trans_id'],
            'intent'        => $pending['intent'],
            'pollInterval'  => config('nafath.poll_interval_seconds'),
        ]);
    }

    /**
     * استطلاع حالة الطلب من الواجهة (يُستدعى كل N ثانية).
     */
    public function status(Request $request, string $transId): JsonResponse
    {
        $pending = $request->session()->get('nafath_pending');

        if (!is_array($pending) || ($pending['trans_id'] ?? null) !== $transId) {
            return response()->json([
                'status'  => 'EXPIRED',
                'message' => 'انتهت صلاحية الطلب أو لا توجد عملية تحقق نشطة. أعد المحاولة.',
            ], 410);
        }

        if (now()->timestamp > (int) ($pending['expires_at'] ?? 0)) {
            $request->session()->forget('nafath_pending');

            return response()->json([
                'status'  => 'EXPIRED',
                'message' => 'انتهت مهلة الموافقة. أعد المحاولة.',
            ]);
        }

        try {
            $result = $this->nafath->checkStatus(
                $pending['national_id'],
                $pending['random'],
                $transId
            );
        } catch (NafathException $e) {
            report($e);

            return response()->json([
                'status'  => 'ERROR',
                'message' => 'تعذر الاتصال بخدمة النفاذ الوطني. أعد المحاولة لاحقاً.',
            ], 502);
        }

        $status = strtoupper((string) data_get($result, 'status', 'WAITING'));

        if ($status === 'COMPLETED') {
            return $this->complete($request, $pending, $transId);
        }

        return response()->json([
            'status'  => $status,
            'message' => match ($status) {
                'REJECTED' => 'تم رفض طلب التحقق من الهوية.',
                'EXPIRED'  => 'انتهت مهلة الموافقة. أعد المحاولة.',
                default    => 'في انتظار موافقتك على الطلب داخل تطبيق نفاذ…',
            },
        ]);
    }

    /**
     * نقطة عودة آمنة تُستخدم كبديل احتياطي إن فقدت الواجهة مسارها.
     */
    public function callback(Request $request): RedirectResponse
    {
        if ($this->authenticated()) {
            return redirect()->route('amrtm.dashboard.hub');
        }

        if ($request->session()->has('nafath_pending')) {
            return redirect()->route('amrtm.nafath.wait');
        }

        return redirect()->route('amrtm.login');
    }

    /**
     * معالجة اكتمال التحقق: إما الدخول بحساب مرتبط بالهوية، أو تمييز
     * الهوية الموثّقة لتُرفق بحساب يُنشأ لاحقاً من نفس الجلسة.
     */
    private function complete(Request $request, array $pending, string $transId): JsonResponse
    {
        $nationalId = $pending['national_id'];

        if (($pending['intent'] ?? 'login') === 'register') {
            $request->session()->put('nafath_verified', [
                'national_id' => $nationalId,
                'verified_at' => now(),
            ]);
            $request->session()->forget('nafath_pending');

            return response()->json([
                'status'                 => 'VERIFIED',
                'message'                => 'تم التحقق من هويتك بنجاح. أكمل بيانات الحساب الآن.',
                'verified_national_id'   => $nationalId,
                'redirect'               => route('amrtm.login', ['mode' => 'register']),
            ]);
        }

        $user = BusinessUser::query()
            ->where('id_number', $nationalId)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            $request->session()->forget('nafath_pending');

            return response()->json([
                'status'   => 'NO_ACCOUNT',
                'message'  => 'لا يوجد حساب مرتبط بهذه الهوية. أنشئ حساباً جديداً أولاً.',
                'redirect' => route('amrtm.register'),
            ], 404);
        }

        $this->markVerified($user);
        Auth::guard('business')->login($user);
        $request->session()->regenerate();
        $request->session()->forget('nafath_pending');

        return response()->json([
            'status'   => 'COMPLETED',
            'message'  => 'تم التحقق من الهوية وتسجيل الدخول بنجاح.',
            'redirect' => route('amrtm.dashboard.hub'),
        ]);
    }

    private function markVerified(BusinessUser $user): void
    {
        $user->update(['nafath_verified_at' => now()]);
    }

    private function authenticated(): bool
    {
        return Auth::guard('business')->check() || Auth::guard('office')->check();
    }

    private function intent(mixed $value): string
    {
        return in_array($value, self::INTENTS, true) ? $value : 'login';
    }

    private function failure(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => $message], $this->nafath->isConfigured() ? 502 : 503);
        }

        return back()
            ->withErrors(['national_id' => $message])
            ->withInput();
    }
}