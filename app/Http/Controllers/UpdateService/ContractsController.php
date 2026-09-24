<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use App\Models\Business\CompanyProfile;
use App\Models\Business\Contract;
use App\Models\Business\ContractType;
use App\Models\Business\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | صفحة إنشاء العقد — تتطلب حساباً مسجلاً للمنشأة (ضمان ملكية العقد)
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $office = $this->authenticatedOffice();

        // حساب إلزامي: لا يمكن إنشاء عقد بدون دخول كمنشأة
        if (! $office) {
            return redirect()->route('amrtm.login')
                ->with('info', 'يرجى تسجيل الدخول أو إنشاء حساب المنشأة أولاً لإنشاء عقد.');
        }

        $companyProfile = CompanyProfile::current();

        $contractTypes = ContractType::query()
            ->with('clauses')
            ->orderBy('sort_order')
            ->get();

        $partyOneName = $office->name_ar ?: $office->name_en;
        $officeOwnerName = $office->users()->where('role', 'owner')->value('name');

        return view('update_service.contracts_create_static', [
            'company'          => $companyProfile,
            'office'           => $office,
            'partyOneName'     => $partyOneName,
            'officeOwnerName'  => $officeOwnerName,
            'contractTypes'    => $contractTypes,
            'partyTwoName'     => $this->resolvePartyTwoName(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | حفظ العقد في جدول bs_contracts
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $office = $this->authenticatedOffice();

        // حساب إلزامي لإنشاء العقد
        if (! $office) {
            return redirect()->route('amrtm.login')
                ->with('info', 'يرجى تسجيل الدخول أو إنشاء حساب المنشأة أولاً لإنشاء عقد.');
        }

        $validated = $request->validate([
            'contract_type_id' => ['required', 'integer', 'exists:business.bs_contract_types,id'],
            'party_name'       => ['nullable', 'string', 'max:255'],
            'party_2_email'    => ['nullable', 'email', 'max:255'],
            'start_date'       => ['nullable', 'date'],
            'end_date'         => ['nullable', 'date', 'after_or_equal:start_date'],
            'terms_accepted'   => ['required', 'accepted'],
        ], [
            'contract_type_id.required' => 'يرجى اختيار نوع العقد.',
            'contract_type_id.exists'   => 'نوع العقد غير صحيح.',
            'party_name.max'            => 'اسم الطرف الثاني غير صحيح.',
            'party_2_email.email'       => 'بريد الطرف الثاني غير صحيح.',
            'party_2_email.max'         => 'بريد الطرف الثاني طويل جداً.',
            'start_date.date'           => 'تاريخ البداية غير صحيح.',
            'end_date.date'             => 'تاريخ النهاية غير صحيح.',
            'end_date.after_or_equal'   => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية.',
            'terms_accepted.required'   => 'يجب الموافقة على الشروط والأحكام.',
            'terms_accepted.accepted'   => 'يجب الموافقة على الشروط والأحكام.',
        ]);

        $type = ContractType::findOrFail($request->contract_type_id);

        $clausesSnapshot = $type->clauses
            ->sortBy('sort_order')
            ->map(fn ($c) => [
                'name'        => $c->name,
                'description' => $c->description,
                'sort_order'  => $c->sort_order,
            ])
            ->values()
            ->all();

        $number = $this->nextContractNumber();

        $partyOneName = $office->name_ar ?: $office->name_en;

        $contract = Contract::create([
            'number'           => $number,
            'contract_type_id' => $type->id,
            'price'            => $type->price,
            'clauses_json'     => $clausesSnapshot,
            'party_name'       => $request->party_name ?: $this->resolvePartyTwoName(),
            'party_1_office_id'=> $office->id,
            'party_1_name'     => $partyOneName,
            'party_2_email'    => $request->party_2_email ? strtolower(trim($request->party_2_email)) : null,
            'party_2_office_id'=> null,
            'party_2_status'   => $request->party_2_email ? Contract::PARTY2_INVITED : Contract::PARTY2_PENDING,
            'start_date'       => $request->start_date,
            'end_date'         => $request->end_date,
            'status'           => Contract::STATUS_PENDING,
        ]);

        $message = 'تم إنشاء عقد رقم ' . $number . ' بنجاح.';

        // إذا أُدخل بريد طرف ثانٍ — نرسل دعوة فورية (snapshot في flash لتظهر بعد التسجيل)
        if ($contract->party_2_email) {
            $message .= ' تمت دعوة الطرف الثاني عبر البريد ' . $contract->party_2_email . '.';
        }

        return redirect()
            ->route('amrtm.contracts.show', $contract->id)
            ->with('success', $message);
    }

    /*
    |--------------------------------------------------------------------------
    | عرض تفاصيل عقد محفوظ
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $contract = Contract::with(['type', 'partyOneOffice', 'partyTwoOffice'])->findOrFail($id);
        $company  = CompanyProfile::current();
        $office   = $this->authenticatedOffice();

        $isPartyOne = $office && $contract->party_1_office_id === $office->id;
        $isPartyTwo = $office
            && ($contract->party_2_office_id === $office->id
                || ($contract->party_2_email && mb_strtolower($office->email) === mb_strtolower($contract->party_2_email)));

        return view('update_service.contract_show', [
            'contract'  => $contract,
            'company'   => $company,
            'office'    => $office,
            'isPartyOne'=> $isPartyOne,
            'isPartyTwo'=> $isPartyTwo,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | عقودي الصادرة (كطرف أول — منشئ العقد)
    |--------------------------------------------------------------------------
    */

    public function myContracts()
    {
        $office = $this->authenticatedOffice();

        if (! $office) {
            return redirect()->route('amrtm.login')
                ->with('info', 'يرجى تسجيل الدخول أولاً لعرض عقودك.');
        }

        $contracts = Contract::query()
            ->with('type')
            ->where('party_1_office_id', $office->id)
            ->orderByDesc('id')
            ->get();

        return view('update_service.contracts_my', [
            'contracts' => $contracts,
            'office'    => $office,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | العقود الواردة (كطرف ثانٍ)
    |--------------------------------------------------------------------------
    | تظهر العقود التي أُضيف بريد المنشأة المسجّل الحالي كطرف ثانٍ فيها.
    */

    public function incoming()
    {
        $office = $this->authenticatedOffice();

        if (! $office) {
            return redirect()->route('amrtm.login')
                ->with('info', 'يرجى تسجيل الدخول أولاً لعرض العقود الواردة إليك.');
        }

        $contracts = Contract::query()
            ->with('type', 'partyOneOffice')
            ->where(function ($q) use ($office) {
                $q->where('party_2_office_id', $office->id)
                    ->orWhere('party_2_email', mb_strtolower($office->email));
            })
            ->orderByDesc('id')
            ->get();

        // مطابقة تلقائية: ربط العقد بالحساب إذا كان البريد مطابقاً
        $matchedIds = [];
        foreach ($contracts as $c) {
            if ($c->party_2_office_id !== $office->id) {
                $c->party_2_office_id = $office->id;
                $c->party_2_status = Contract::PARTY2_REGISTERED;
                $c->save();
                $matchedIds[] = $c->id;
            }
        }

        return view('update_service.contracts_incoming', [
            'contracts' => $contracts,
            'office'    => $office,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على حساب المنشأة من guard office
    |--------------------------------------------------------------------------
    */

    protected function authenticatedOffice(): ?Office
    {
        if (! Auth::guard('office')->check()) {
            return null;
        }

        return Auth::guard('office')->user()->office;
    }

    /*
    |--------------------------------------------------------------------------
    | اسم الطرف الثاني من الجلسة الحالية إن وُجد
    |--------------------------------------------------------------------------
    */

    protected function resolvePartyTwoName(): ?string
    {
        if (Auth::guard('office')->check()) {
            $office = Auth::guard('office')->user()->office;
            if ($office) {
                return $office->name_ar ?: $office->name_en;
            }
        }

        if (Auth::guard('business')->check()) {
            $user = Auth::guard('business')->user();
            return $user->name ?: $user->legal_name;
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | توليد رقم عقد تالٍ (CNT-0001 ...)
    |--------------------------------------------------------------------------
    */

    protected function nextContractNumber(): string
    {
        $last = DB::connection('business')
            ->table('bs_contracts')
            ->orderByDesc('id')
            ->value('number');

        $seq = 1;

        if ($last) {
            $match = preg_match('/CNT-(\d+)$/', (string) $last, $m);
            if ($match) {
                $seq = (int) $m[1] + 1;
            }
        }

        return 'CNT-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
