<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use ArPHP\I18N\Arabic;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ContractController extends Controller
{
    // ── Office helpers ──────────────────────────────────────────────────────────

    private function officeUser()
    {
        return Auth::guard('office')->user();
    }

    private function office()
    {
        return $this->officeUser()->office;
    }

    private function findUsableContract($id)
    {
        return DB::connection('business')
            ->table('bs_contracts as c')
            ->leftJoin('bs_contract_types as ct', 'c.contract_type_id', '=', 'ct.id')
            ->where('c.id', $id)
            ->select('c.*', 'ct.name as type_name', 'ct.category as type_category')
            ->first();
    }

    private function ensureViewToken($contract)
    {
        if ($contract->view_token) {
            return $contract->view_token;
        }

        $token = Str::random(40);

        DB::connection('business')
            ->table('bs_contracts')
            ->where('id', $contract->id)
            ->update(['view_token' => $token, 'updated_at' => now()]);

        return $token;
    }

    private function renderClauses($contract)
    {
        $clauses = json_decode($contract->clauses_json ?? '[]', true);
        if (! is_array($clauses)) {
            $clauses = [];
        }

        $start = $this->formatDate($contract->start_date);
        $end   = $this->formatDate($contract->end_date);

        foreach ($clauses as &$clause) {
            $text = $clause['description'] ?? '';
            $text = str_replace(['{start}', '{end}'], [$start, $end], $text);
            $text = str_replace('{price}', number_format((float) $contract->price), $text);
            $clause['rendered'] = $text;
        }

        return $clauses;
    }

    private function formatDate($date)
    {
        if (! $date) {
            return '';
        }

        try {
            return \Illuminate\Support\Carbon::parse($date)->translatedFormat('d F Y');
        } catch (\Throwable $e) {
            return $date;
        }
    }

    // ── Office: send contract to second party ──────────────────────────────────

    public function sendToParty(Request $request, int $id): JsonResponse
    {
        $officeId = $this->office()->id;

        $contract = $this->findUsableContract($id);
        if (! $contract) {
            return response()->json(['message' => 'العقد غير موجود'], 404);
        }

        if ((int) $contract->created_by_office_id !== (int) $officeId) {
            return response()->json(['message' => 'غير مصرح بذلك'], 403);
        }

        if (! $contract->party1_signed_at) {
            return response()->json(['message' => 'يجب توقيع العقد أولاً قبل الإرسال للطرف الثاني'], 409);
        }

        $email = $request->input('email') ?: $contract->party_email;
        if (! $email) {
            return response()->json(['message' => 'لا يوجد بريد إلكتروني للطرف الثاني'], 422);
        }

        $request->validate(['email' => 'nullable|email']);

        $token = $this->ensureViewToken($contract);

        $link = URL::signedRoute('amrtm.public.contract.view', ['token' => $token], now()->addDays(30));

        try {
            Mail::html(
                view('update_service.emails.contract_link', [
                    'contract' => $contract,
                    'link'     => $link,
                ])->render(),
                function ($message) use ($email, $contract) {
                    $message->to($email)
                        ->subject('عقد رقم ' . $contract->number);
                }
            );
            \Log::info('Contract email sent OK', ['contract_id' => $id, 'email' => $email]);
        } catch (\Throwable $e) {
            \Log::error('Contract email failed', [
                'contract_id' => $id,
                'email'       => $email,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'فشل إرسال البريد: ' . $e->getMessage()], 500);
        }

        DB::connection('business')
            ->table('bs_contracts')
            ->where('id', $contract->id)
            ->update([
                'party_email' => $email,
                'second_party_email_sent_at' => now(),
                'status' => 'party2_sent',
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'تم إرسال رابط العقد إلى البريد الإلكتروني', 'link' => $link]);
    }

    // ── Office: download PDF with QR ───────────────────────────────────────────

    public function downloadPdf(int $id)
    {
        $officeId = $this->office()->id;

        $contract = $this->findUsableContract($id);
        if (! $contract) {
            abort(404, 'العقد غير موجود');
        }

        if ((int) $contract->created_by_office_id !== (int) $officeId
            && (int) $contract->party_office_id !== (int) $officeId) {
            abort(403);
        }

        $token    = $this->ensureViewToken($contract);
        $viewLink = URL::signedRoute('amrtm.public.contract.view', ['token' => $token], now()->addDays(30));

        $qr = $this->qrHtml($viewLink);

        $html = view('update_service.office.contract_pdf', [
            'contract' => $contract,
            'clauses'  => $this->renderClauses($contract),
            'qr'       => $qr,
            'ar'       => fn($t) => $this->arShape((string) $t),
        ])->render();

        $pdf = Pdf::loadHTML($html);
        $this->registerArabicFont($pdf);

        return $pdf->download('contract-' . $contract->number . '.pdf');
    }

    private function qrHtml(string $content): string
    {
        try {
            $code   = Encoder::encode($content, ErrorCorrectionLevel::M());
            $matrix = $code->getMatrix();
            $w      = $matrix->getWidth();
            $h      = $matrix->getHeight();

            $html  = '<table style="border-collapse:collapse;border-spacing:0;margin:0 auto;">';
            for ($y = 0; $y < $h; $y++) {
                $html .= '<tr>';
                for ($x = 0; $x < $w; $x++) {
                    $on   = $matrix->get($x, $y) === 1;
                    $html .= '<td style="width:2px;height:2px;padding:0;line-height:0;font-size:0;background:'
                        . ($on ? '#000000' : '#ffffff') . ';"></td>';
                }
                $html .= '</tr>';
            }
            $html .= '</table>';

            return $html;
        } catch (\Throwable $e) {
            return '';
        }
    }

    // ── Public: read-only contract view ───────────────────────────────────────

    public function publicShow(Request $request, string $token)
    {
        $contract = DB::connection('business')
            ->table('bs_contracts as c')
            ->leftJoin('bs_contract_types as ct', 'c.contract_type_id', '=', 'ct.id')
            ->where('c.view_token', $token)
            ->select('c.*', 'ct.name as type_name', 'ct.category as type_category')
            ->first();

        if (! $contract) {
            abort(404);
        }

        DB::connection('business')
            ->table('bs_contracts')
            ->where('id', $contract->id)
            ->whereNull('second_party_viewed_at')
            ->update(['second_party_viewed_at' => now(), 'updated_at' => now()]);

        return view('update_service.public.contract_view', [
            'contract' => $contract,
            'clauses'  => $this->renderClauses($contract),
            'token'    => $token,
        ]);
    }

    public function publicPdf(Request $request, string $token)
    {
        $contract = DB::connection('business')
            ->table('bs_contracts as c')
            ->leftJoin('bs_contract_types as ct', 'c.contract_type_id', '=', 'ct.id')
            ->where('c.view_token', $token)
            ->select('c.*', 'ct.name as type_name', 'ct.category as type_category')
            ->first();

        if (! $contract) {
            abort(404);
        }

        $viewLink = URL::signedRoute('amrtm.public.contract.view', ['token' => $token], now()->addDays(30));
        $qr       = $this->qrHtml($viewLink);

        $html = view('update_service.office.contract_pdf', [
            'contract' => $contract,
            'clauses'  => $this->renderClauses($contract),
            'qr'       => $qr,
            'ar'       => fn($t) => $this->arShape((string) $t),
        ])->render();

        $pdf = Pdf::loadHTML($html);
        $this->registerArabicFont($pdf);

        return $pdf->download('contract-' . $contract->number . '.pdf');
    }

    // ── Admin: contract types CRUD ─────────────────────────────────────────────

    public function adminListTypes(): JsonResponse
    {
        $rows = DB::connection('business')
            ->table('bs_contract_types as t')
            ->selectRaw('t.*, (SELECT COUNT(*) FROM bs_contract_clauses cl WHERE cl.contract_type_id = t.id) as clauses_count')
            ->orderBy('t.category')
            ->orderBy('t.sort_order')
            ->get();

        return response()->json($rows);
    }

    public function adminStoreType(Request $request): JsonResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'category'   => 'nullable|in:تجاري,صناعي,service',
            'price'      => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer',
        ]);

        $id = DB::connection('business')->table('bs_contract_types')->insertGetId([
            'name'       => $request->name,
            'category'   => $request->category,
            'price'      => $request->price,
            'sort_order' => $request->sort_order ?: 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'تم إضافة نوع العقد',
            'type'    => DB::connection('business')->table('bs_contract_types')->where('id', $id)->first(),
        ], 201);
    }

    public function adminUpdateType(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'category'   => 'nullable|in:تجاري,صناعي,service',
            'price'      => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer',
        ]);

        DB::connection('business')->table('bs_contract_types')->where('id', $id)->update([
            'name'       => $request->name,
            'category'   => $request->category,
            'price'      => $request->price,
            'sort_order' => $request->sort_order ?: 0,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'تم تحديث نوع العقد']);
    }

    public function adminDeleteType(int $id): JsonResponse
    {
        DB::connection('business')->table('bs_contract_types')->where('id', $id)->delete();

        return response()->json(['message' => 'تم حذف نوع العقد']);
    }

    // ── Admin: contract clauses CRUD ───────────────────────────────────────────

    public function adminListClauses(int $typeId): JsonResponse
    {
        $clauses = DB::connection('business')
            ->table('bs_contract_clauses')
            ->where('contract_type_id', $typeId)
            ->orderBy('sort_order')
            ->get();

        return response()->json($clauses);
    }

    public function adminStoreClause(Request $request, int $typeId): JsonResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'sort_order'  => 'nullable|integer',
        ]);

        $id = DB::connection('business')->table('bs_contract_clauses')->insertGetId([
            'contract_type_id' => $typeId,
            'name'             => $request->name,
            'description'      => $request->description,
            'sort_order'       => $request->sort_order ?: 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return response()->json([
            'message' => 'تم إضافة البند',
            'clause'  => DB::connection('business')->table('bs_contract_clauses')->where('id', $id)->first(),
        ], 201);
    }

    public function adminUpdateClause(Request $request, int $typeId, int $clauseId): JsonResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'sort_order'  => 'nullable|integer',
        ]);

        DB::connection('business')
            ->table('bs_contract_clauses')
            ->where('id', $clauseId)
            ->where('contract_type_id', $typeId)
            ->update([
                'name'        => $request->name,
                'description' => $request->description,
                'sort_order'  => $request->sort_order ?: 0,
                'updated_at'  => now(),
            ]);

        return response()->json(['message' => 'تم تحديث البند']);
    }

    public function adminDeleteClause(int $typeId, int $clauseId): JsonResponse
    {
        DB::connection('business')
            ->table('bs_contract_clauses')
            ->where('id', $clauseId)
            ->where('contract_type_id', $typeId)
            ->delete();

        return response()->json(['message' => 'تم حذف البند']);
    }

    // ── Admin: contracts (all offices) ──────────────────────────────────────────

    public function adminListContracts(Request $request): JsonResponse
    {
        $status = $request->input('status', 'all');
        $search = $request->input('search', '');

        $query = DB::connection('business')
            ->table('bs_contracts as c')
            ->leftJoin('bs_contract_types as ct', 'c.contract_type_id', '=', 'ct.id')
            ->leftJoin('bs_offices as bo', 'c.created_by_office_id', '=', 'bo.id')
            ->leftJoin('bs_offices as bt', 'c.party_office_id', '=', 'bt.id')
            ->select(
                'c.id',
                'c.number',
                'c.contract_type_id',
                'c.created_by_office_id',
                'c.party_office_id',
                'c.price',
                'c.start_date',
                'c.end_date',
                'c.status',
                'c.party_name',
                'c.party_email',
                'c.description',
                'c.category',
                'c.view_token',
                'c.pdf_path',
                'c.second_party_email_sent_at',
                'c.second_party_viewed_at',
                'c.clauses_json',
                'c.created_at',
                'c.updated_at',
                'ct.name as type_name',
                'ct.category as type_category',
                'bo.name_ar as party1_office_name',
                'bo.name_en as party1_office_name_en',
                'bt.name_ar as party2_office_name',
                'bt.name_en as party2_office_name_en'
            );

        if ($status !== 'all') {
            $query->where('c.status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('c.number', 'like', "%$search%")
                  ->orWhere('c.party_name', 'like', "%$search%")
                  ->orWhere('ct.name', 'like', "%$search%")
                  ->orWhere('bo.name_ar', 'like', "%$search%")
                  ->orWhere('bo.name_en', 'like', "%$search%");
            });
        }

        $contracts = $query->orderByDesc('c.created_at')->get();

        return response()->json($contracts);
    }

    public function adminStoreContract(Request $request): JsonResponse
    {
        $request->validate([
            'contract_type_id' => 'required|integer|exists:bs_contract_types,id',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
            'party_name'       => 'nullable|string|max:255',
            'party_email'      => 'nullable|email',
            'description'      => 'nullable|string|max:2000',
            'price'            => 'nullable|numeric|min:0',
            'status'           => 'nullable|in:draft,active,expired',
        ]);

        $type = DB::connection('business')
            ->table('bs_contract_types')
            ->where('id', $request->contract_type_id)
            ->first();

        if (! $type) {
            return response()->json(['message' => 'نوع العقد غير موجود'], 404);
        }

        $adminClauses = DB::connection('business')
            ->table('bs_contract_clauses')
            ->where('contract_type_id', $request->contract_type_id)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($c) => [
                'name'        => $c->name,
                'description' => $c->description,
                'is_admin'    => true,
            ])
            ->toArray();

        $number = 'CNT-' . str_pad((string) (DB::connection('business')
            ->table('bs_contracts')
            ->max('id') + 1), 4, '0', STR_PAD_LEFT);

        $id = DB::connection('business')->table('bs_contracts')->insertGetId([
            'number'               => $number,
            'contract_type_id'     => $request->contract_type_id,
            'created_by_office_id' => null,
            'party_office_id'      => null,
            'start_date'           => $request->start_date,
            'end_date'             => $request->end_date,
            'status'               => $request->status ?: 'active',
            'description'          => $request->description,
            'category'             => $type->category,
            'price'                => $request->price !== null ? $request->price : $type->price,
            'clauses_json'         => json_encode($adminClauses),
            'party_name'           => $request->party_name,
            'party_email'          => $request->party_email,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        $contract = DB::connection('business')
            ->table('bs_contracts as c')
            ->leftJoin('bs_contract_types as ct', 'c.contract_type_id', '=', 'ct.id')
            ->where('c.id', $id)
            ->select('c.*', 'ct.name as type_name', 'ct.category as type_category')
            ->first();

        return response()->json([
            'message'  => 'تم إنشاء العقد بنجاح',
            'contract' => $contract,
        ], 201);
    }

    // ── Arabic shaping + font for dompdf ─────────────────────────────────────────

    public function adminContractPdf(int $id)
    {
        $contract = $this->findUsableContract($id);
        if (! $contract) {
            abort(404, 'العقد غير موجود');
        }

        $token    = $this->ensureViewToken($contract);
        $viewLink = URL::signedRoute('amrtm.public.contract.view', ['token' => $token], now()->addDays(30));
        $qr       = $this->qrHtml($viewLink);

        $html = view('update_service.office.contract_pdf', [
            'contract' => $contract,
            'clauses'  => $this->renderClauses($contract),
            'qr'       => $qr,
            'ar'       => fn($t) => $this->arShape((string) $t),
        ])->render();

        $pdf = Pdf::loadHTML($html);
        $this->registerArabicFont($pdf);

        return $pdf->download('contract-' . $contract->number . '.pdf');
    }

    private function arShape(string $text): string
    {
        $text = ($text === null) ? '' : (string) $text;
        if ($text === '') {
            return '';
        }
        if (! preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFC}]/u', $text)) {
            return $text;
        }
        static $ar = null;
        if ($ar === null) {
            $ar = new Arabic();
        }
        return $ar->utf8Glyphs($text, 100000, false, false);
    }

    private function registerArabicFont($pdf): void
    {
        $regular = storage_path('fonts/Tajawal-Regular.ttf');
        $bold    = storage_path('fonts/Tajawal-Bold.ttf');

        if (! is_file($regular) || ! is_file($bold)) {
            return;
        }

        $fm = $pdf->getDomPDF()->getFontMetrics();

        $fm->registerFont([
            'family' => 'Tajawal',
            'style'  => 'normal',
            'weight' => 'normal',
        ], $regular);

        $fm->registerFont([
            'family' => 'Tajawal',
            'style'  => 'normal',
            'weight' => 'bold',
        ], $bold);
    }

    // ── Contract Signing (Verification Code) ──────────────────────────────────

    private function generateVerificationCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function createVerificationCode(int $contractId, string $email, string $type): string
    {
        DB::connection('business')
            ->table('bs_contract_verification_codes')
            ->where('contract_id', $contractId)
            ->where('type', $type)
            ->where('used', false)
            ->update(['used' => true]);

        $code     = $this->generateVerificationCode();
        $expiresAt = now()->addMinutes(5);

        DB::connection('business')->table('bs_contract_verification_codes')->insert([
            'contract_id' => $contractId,
            'email'       => $email,
            'code'        => $code,
            'type'        => $type,
            'expires_at'  => $expiresAt,
            'used'        => false,
            'attempts'    => 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return $code;
    }

    private function sendVerificationEmail(string $email, string $code, string $contractNumber): void
    {
        $html = view('update_service.emails.verification_code', [
            'code'           => $code,
            'contractNumber' => $contractNumber,
        ])->render();

        Mail::html($html, function ($message) use ($email, $contractNumber) {
            $message->to($email)
                ->subject('كود التوقيع على العقد رقم ' . $contractNumber);
        });
    }

    private function verifyCode(int $contractId, string $email, string $type, string $code): array
    {
        $record = DB::connection('business')
            ->table('bs_contract_verification_codes')
            ->where('contract_id', $contractId)
            ->where('email', $email)
            ->where('type', $type)
            ->where('used', false)
            ->orderByDesc('id')
            ->first();

        if (! $record) {
            return ['success' => false, 'message' => 'لم يتم العثور على كود تحقق صالح'];
        }

        if (now()->greaterThan($record->expires_at)) {
            return ['success' => false, 'message' => 'انتهت صلاحية الكود. يرجى طلب كود جديد'];
        }

        if ($record->attempts >= 5) {
            return ['success' => false, 'message' => 'تم تجاوز الحد الأقصى للمحاولات. يرجى طلب كود جديد'];
        }

        DB::connection('business')
            ->table('bs_contract_verification_codes')
            ->where('id', $record->id)
            ->increment('attempts');

        if ($record->code !== $code) {
            $remaining = 5 - ($record->attempts + 1);
            return ['success' => false, 'message' => "كود خاطئ. متبقي {$remaining} محاولات"];
        }

        DB::connection('business')
            ->table('bs_contract_verification_codes')
            ->where('id', $record->id)
            ->update(['used' => true, 'updated_at' => now()]);

        return ['success' => true];
    }

    // ── Office: Party 1 sign request ────────────────────────────────────────

    public function requestParty1Code(int $id): JsonResponse
    {
        $officeId = $this->office()->id;
        $office   = $this->office();

        $contract = $this->findUsableContract($id);
        if (! $contract) {
            return response()->json(['message' => 'العقد غير موجود'], 404);
        }

        if ((int) $contract->created_by_office_id !== (int) $officeId) {
            return response()->json(['message' => 'غير مصرح بذلك'], 403);
        }

        if ($contract->party1_signed_at) {
            return response()->json(['message' => 'تم توقيع العقد مسبقاً'], 409);
        }

        $email = $office->email ?? $this->officeUser()->email;
        if (! $email) {
            return response()->json(['message' => 'لا يوجد بريد إلكتروني مرتبط بالحساب'], 422);
        }

        $code = $this->createVerificationCode($contract->id, $email, 'party1_sign');

        try {
            $this->sendVerificationEmail($email, $code, $contract->number);
        } catch (\Throwable $e) {
            \Log::error('Party1 verification email failed', [
                'contract_id' => $id,
                'email'       => $email,
                'error'       => $e->getMessage(),
            ]);
            return response()->json(['message' => 'فشل إرسال كود التحقق: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'تم إرسال كود التحقق إلى بريدك الإلكتروني',
            'email'   => $email,
        ]);
    }

    public function verifyParty1Code(Request $request, int $id): JsonResponse
    {
        $officeId = $this->office()->id;

        $contract = $this->findUsableContract($id);
        if (! $contract) {
            return response()->json(['message' => 'العقد غير موجود'], 404);
        }

        if ((int) $contract->created_by_office_id !== (int) $officeId) {
            return response()->json(['message' => 'غير مصرح بذلك'], 403);
        }

        if ($contract->party1_signed_at) {
            return response()->json(['message' => 'تم توقيع العقد مسبقاً'], 409);
        }

        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $office   = $this->office();
        $email    = $office->email ?? $this->officeUser()->email;

        $result = $this->verifyCode($contract->id, $email, 'party1_sign', $request->code);
        if (! $result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        DB::connection('business')
            ->table('bs_contracts')
            ->where('id', $contract->id)
            ->update([
                'party1_signed_at' => now(),
                'updated_at'       => now(),
            ]);

        return response()->json(['message' => 'تم توقيع العقد بنجاح']);
    }

    // ── Public: Party 2 sign request ────────────────────────────────────────

    public function requestParty2Code(Request $request, string $token): JsonResponse
    {
        if (! URL::hasValidSignature($request)) {
            return response()->json(['message' => 'الرابط غير صالح أو منتهي الصلاحية'], 403);
        }

        $contract = DB::connection('business')
            ->table('bs_contracts as c')
            ->leftJoin('bs_contract_types as ct', 'c.contract_type_id', '=', 'ct.id')
            ->where('c.view_token', $token)
            ->select('c.*', 'ct.name as type_name', 'ct.category as type_category')
            ->first();

        if (! $contract) {
            return response()->json(['message' => 'العقد غير موجود'], 404);
        }

        if (! $contract->party1_signed_at) {
            return response()->json(['message' => 'العقد لم يُوقَّع من الطرف الأول بعد'], 409);
        }

        if ($contract->party2_signed_at) {
            return response()->json(['message' => 'تم توقيع العقد مسبقاً'], 409);
        }

        $email = $contract->party_email;
        if (! $email) {
            return response()->json(['message' => 'لا يوجد بريد إلكتروني مسجل للطرف الثاني'], 422);
        }

        $code = $this->createVerificationCode($contract->id, $email, 'party2_sign');

        try {
            $this->sendVerificationEmail($email, $code, $contract->number);
        } catch (\Throwable $e) {
            \Log::error('Party2 verification email failed', [
                'contract_id' => $contract->id,
                'email'       => $email,
                'error'       => $e->getMessage(),
            ]);
            return response()->json(['message' => 'فشل إرسال كود التحقق: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'تم إرسال كود التحقق إلى بريدك الإلكتروني',
            'email'   => $email,
        ]);
    }

    public function verifyParty2Code(Request $request, string $token): JsonResponse
    {
        if (! URL::hasValidSignature($request)) {
            return response()->json(['message' => 'الرابط غير صالح أو منتهي الصلاحية'], 403);
        }

        $contract = DB::connection('business')
            ->table('bs_contracts as c')
            ->leftJoin('bs_contract_types as ct', 'c.contract_type_id', '=', 'ct.id')
            ->where('c.view_token', $token)
            ->select('c.*', 'ct.name as type_name', 'ct.category as type_category')
            ->first();

        if (! $contract) {
            return response()->json(['message' => 'العقد غير موجود'], 404);
        }

        if (! $contract->party1_signed_at) {
            return response()->json(['message' => 'العقد لم يُوقَّع من الطرف الأول بعد'], 409);
        }

        if ($contract->party2_signed_at) {
            return response()->json(['message' => 'تم توقيع العقد مسبقاً'], 409);
        }

        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $email = $contract->party_email;

        $result = $this->verifyCode($contract->id, $email, 'party2_sign', $request->code);
        if (! $result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        DB::connection('business')
            ->table('bs_contracts')
            ->where('id', $contract->id)
            ->update([
                'party2_signed_at' => now(),
                'status'           => 'fully_signed',
                'updated_at'       => now(),
            ]);

        return response()->json(['message' => 'تم توقيع العقد بنجاح']);
    }
}
