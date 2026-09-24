<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Business\OfficeDocument;
use App\Models\Business\OfficeUser;
use App\Models\ServiceRequest;
use App\Models\OfficeSettlement;
use Illuminate\Support\Str;

class Office extends Model
{
    protected $connection = 'business';

    protected $table = 'bs_offices';

    public const ACCOUNT_TYPE_SUPPORT_OFFICE = 'support_office';
    public const ACCOUNT_TYPE_CONSULTANT = 'consultant';

    public const ALL_ACCOUNT_TYPES = [
        self::ACCOUNT_TYPE_SUPPORT_OFFICE,
        self::ACCOUNT_TYPE_CONSULTANT,
    ];

    protected $fillable = [
        'type',
        'business_activity',
        'category',
        'business_categories',
        'name_ar',
        'name_en',
        'entity_type',
        'description_ar',
        'description_en',
        'phone',
        'email',
        'city',
        'cr_number',
        'logo',
        'specialties',
        'is_active',
        'is_verified',
        'commission_rate',
        'subscription_type',
        'account_types',
        'views_count',
        'video_consultation_enabled',

    // كود ورابط المكتب
    'office_code',
    'public_token',
    ];

    protected $casts = [
        'specialties' => 'array',
        'business_categories' => 'array',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'commission_rate' => 'float',
        'subscription_type' => 'string',
        'account_types' => 'array',
        'views_count' => 'integer',
        'video_consultation_enabled' => 'boolean',
    ];

    public static array $typeLabels = [

        'law' => [
            'ar' => 'شركة/مكتب محاماة',
            'en' => 'Law Firm',
        ],

        'services' => [
            'ar' => 'مكتب خدمات وتعقيب',
            'en' => 'Service & Expediting Office',
        ],

        'customs' => [
            'ar' => 'شركة/مكتب تخليص جمركي',
            'en' => 'Customs Clearance',
        ],

        'accounting' => [
            'ar' => 'مكاتب المحاسبة والاستشارات المالية والضريبية',
            'en' => 'Accounting & Tax Consulting',
        ],

        'engineering' => [
            'ar' => 'الاستشارات الهندسية والتصميم والإشراف',
            'en' => 'Engineering Consulting',
        ],

        'freelance' => [
            'ar' => 'أصحاب المهن الحرة',
            'en' => 'Freelance Professionals',
        ],

    ];

    public static array $subscriptionLabels = [

        'commission' => [
            'ar' => 'مكتب مساند (عمولة)',
            'en' => 'Supporting Office (Commission)',
        ],

        'subscription' => [
            'ar' => 'مستشار (اشتراك سنوي)',
            'en' => 'Consultant (Annual Subscription)',
        ],

    ];

    public static array $accountTypeLabels = [
        self::ACCOUNT_TYPE_SUPPORT_OFFICE => [
            'ar' => 'مكتب مساند',
            'en' => 'Supporting Office',
        ],
        self::ACCOUNT_TYPE_CONSULTANT => [
            'ar' => 'مستشار',
            'en' => 'Consultant',
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes — حسب نوع الحساب (موحّد)
    |--------------------------------------------------------------------------
    */

    public function scopeSupportingOffices($query)
    {
        return $query->where(function ($q) {
            $q->whereJsonContains('account_types', self::ACCOUNT_TYPE_SUPPORT_OFFICE)
                ->orWhereNull('account_types')
                ->where('subscription_type', 'commission');
        });
    }

    public function scopeConsultants($query)
    {
        return $query->where(function ($q) {
            $q->whereJsonContains('account_types', self::ACCOUNT_TYPE_CONSULTANT)
                ->orWhereNull('account_types')
                ->where('subscription_type', 'subscription');
        });
    }

    public function getBusinessActivityLabelAttribute()
    {
        if (!$this->business_activity) return null;
        return \App\Support\ConsultantCatalog::BUSINESS_ACTIVITIES[$this->business_activity]['label_ar'] ?? $this->business_activity;
    }

    public function getCategoryLabelAttribute()
    {
        if (!$this->category) return null;
        return \App\Support\ConsultantCatalog::CATEGORIES[$this->category]['label_ar'] ?? $this->category;
    }

    /*
    |--------------------------------------------------------------------------
    | Scope — المكاتب الظاهرة في الدليل المهني
    |--------------------------------------------------------------------------
    */

    public function scopeVisibleInDirectory($query)
    {
        return $query->where(function ($q) {
            $q->whereJsonContains('account_types', self::ACCOUNT_TYPE_SUPPORT_OFFICE)
                ->orWhereJsonContains('account_types', self::ACCOUNT_TYPE_CONSULTANT)
                ->orWhereNull('account_types');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scope — المكاتب المؤهلة لشبكة المكاتب (Request Pool)
    |--------------------------------------------------------------------------
    | مكتب مساند نشط وموثّق يقدّم نفس الخدمة بشرط أن تكون خدمته
    | معتمدة (approval_status=approved) ومفعّلة (is_active=true).
    | التطابق يتم عبر أحد المسارات:
    |   1) يقدّم نفس خدمة المكتب المختارة (office_service_id).
    |   2) يقدّم نفس الخدمة الحكومية (source_service_id = service_id).
    |   3) يشارك نفس التخصص (specialty_id) لخدمة/أعمال مخصصة غير مربوطة بالكتالوج.
    |
    */

    public function scopeEligibleForRequest($query, ServiceRequest $request)
    {
        $sourceService = $request->officeService;

        return $query
            ->supportingOffices()
            ->where('is_active', true)
            ->where('is_verified', true)
            ->where(function ($q) use ($request, $sourceService) {
                // نقطة ارتكاز تضمن صحة SQL عند غياب أيٍّ من الشروط أدناه.
                $q->where('id', -1);

                if ($request->office_service_id) {
                    $q->orWhereHas('services', fn ($sq) => $sq
                        ->where('id', $request->office_service_id)
                        ->where('is_active', true)
                        ->where('approval_status', 'approved'));
                }

                if ($request->service_id) {
                    $q->orWhereHas('services', fn ($sq) => $sq
                        ->where('source_service_id', $request->service_id)
                        ->where('is_active', true)
                        ->where('approval_status', 'approved'));
                }

                if ($sourceService?->specialty_id) {
                    $q->orWhereHas('services', fn ($sq) => $sq
                        ->where('specialty_id', $sourceService->specialty_id)
                        ->where('is_active', true)
                        ->where('approval_status', 'approved'));
                }
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers — نوع الاشتراك (للمرجعية والاستمرارية)
    |--------------------------------------------------------------------------
    */

    public function isConsultant(): bool
    {
        return $this->subscription_type === 'subscription';
    }

    public function isSupportingOffice(): bool
    {
        return $this->subscription_type === 'commission';
    }

    public function subscriptionLabelAr(): string
    {
        return self::$subscriptionLabels[$this->subscription_type]['ar']
            ?? self::$subscriptionLabels['commission']['ar'];
    }

    public function subscriptionLabelEn(): string
    {
        return self::$subscriptionLabels[$this->subscription_type]['en']
            ?? self::$subscriptionLabels['commission']['en'];
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers — أنواع الحسابات الموحّدة
    |--------------------------------------------------------------------------
    */

    public function accountTypes(): array
    {
        $types = $this->account_types;

        if (empty($types)) {
            // مرجعية: اشتق من subscription_type إن لم تُفعّل أنواع صريحة بعد
            return [$this->subscription_type === 'subscription'
                ? self::ACCOUNT_TYPE_CONSULTANT
                : self::ACCOUNT_TYPE_SUPPORT_OFFICE];
        }

        return array_values(array_intersect(self::ALL_ACCOUNT_TYPES, (array) $types));
    }

    public function hasAccountType(string $type): bool
    {
        return in_array($type, $this->accountTypes(), true);
    }

    public function activateAccountType(string $type): self
    {
        if (! in_array($type, self::ALL_ACCOUNT_TYPES, true)) {
            return $this;
        }

        $types = $this->accountTypes();
        if (! in_array($type, $types, true)) {
            $types[] = $type;
        }

        $this->account_types = array_values($types);
        $this->save();

        return $this;
    }

    public function deactivateAccountType(string $type): self
    {
        $types = array_values(array_diff($this->accountTypes(), [$type]));

        $this->account_types = $types ?: null;
        $this->save();

        return $this;
    }

    public function toggleAccountType(string $type): bool
    {
        $active = $this->hasAccountType($type);

        if ($active) {
            $this->deactivateAccountType($type);
        } else {
            $this->activateAccountType($type);
        }

        return ! $active;
    }

    public function accountTypeLabelAr(string $type): string
    {
        return self::$accountTypeLabels[$type]['ar']
            ?? self::$accountTypeLabels[self::ACCOUNT_TYPE_SUPPORT_OFFICE]['ar'];
    }

    public function accountTypeLabelEn(string $type): string
    {
        return self::$accountTypeLabels[$type]['en']
            ?? self::$accountTypeLabels[self::ACCOUNT_TYPE_SUPPORT_OFFICE]['en'];
    }


protected static function booted()
{
    static::creating(function ($office) {
        do {
            $code = 'OF-' . strtoupper(Str::random(6));
        } while (static::where('office_code', $code)->exists());

        $office->office_code = $code;
        $office->public_token = Str::random(40);
    });
}
    



    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */

    public function users(): HasMany
    {
        return $this->hasMany(OfficeUser::class, 'office_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Requests
    |--------------------------------------------------------------------------
    */

    public function requests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'office_id');
    }

    public function directRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'office_id')
            ->where('origin', ServiceRequest::ORIGIN_OFFICE);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(OfficeSettlement::class, 'office_id');
    }

    public function outstandingSettlements(): HasMany
    {
        return $this->hasMany(OfficeSettlement::class, 'office_id')
            ->where('status', OfficeSettlement::STATUS_PENDING);
    }

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    public function messages(): HasMany
    {
        return $this->hasMany(OfficeMessage::class, 'office_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Services
    |--------------------------------------------------------------------------
    */

    public function services(): HasMany
    {
        return $this->hasMany(OfficeService::class, 'office_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Office Profile
    |--------------------------------------------------------------------------
    */

    public function profile()
{
    return $this->hasOne(
        \App\Models\Business\OfficeProfile::class,
        'office_id',
        'id'
    );
}
    /*
    |--------------------------------------------------------------------------
    | Office Documents
    |--------------------------------------------------------------------------
    */


    public function documents()
{
    return $this->hasMany(
        OfficeDocument::class,
        'office_id',
        'id'
    );
}
    /*
    |--------------------------------------------------------------------------
    | Specialties
    |--------------------------------------------------------------------------
    */

    public function specialtiesRelation(): BelongsToMany
    {
        return $this->belongsToMany(
            Specialty::class,
            'bs_office_specialties',
            'office_id',
            'specialty_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Labels
    |--------------------------------------------------------------------------
    */

    public function typeLabelAr(): string
    {
        return self::$typeLabels[$this->type]['ar'] ?? $this->type;
    }

    public function typeLabelEn(): string
    {
        return self::$typeLabels[$this->type]['en'] ?? $this->type;
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        // الشعارات المرفوعة ضمن public/images/uploads تُخدم مباشرة بدون symlink.
        if (str_starts_with($this->logo, 'images/')) {
            return asset($this->logo);
        }

        // الشعارات المخزنة على قرص public تُخدم عبر route /media/public/*
        // (تعمل عبر Laravel بلا الحاجة إلى سيم لينك public/storage).
        return url('media/public/' . ltrim($this->logo, '/'));
    }

    public function getDisplaySpecialtyArAttribute(): string
    {
        if ($this->relationLoaded('specialtiesRelation') && $this->specialtiesRelation->isNotEmpty()) {
            return $this->specialtiesRelation->first()->name_ar;
        }
        $spec = $this->specialtiesRelation()->first();
        if ($spec) {
            return $spec->name_ar;
        }
        if (is_array($this->specialties) && count($this->specialties) > 0) {
            return $this->specialties[0];
        }
        return self::$typeLabels[$this->type]['ar'] ?? 'تخصص معتمد';
    }

    public function getDisplaySpecialtyEnAttribute(): string
    {
        if ($this->relationLoaded('specialtiesRelation') && $this->specialtiesRelation->isNotEmpty()) {
            return $this->specialtiesRelation->first()->name_en ?? $this->specialtiesRelation->first()->name_ar;
        }
        $spec = $this->specialtiesRelation()->first();
        if ($spec) {
            return $spec->name_en ?? $spec->name_ar;
        }
        if (is_array($this->specialties) && count($this->specialties) > 0) {
            return $this->specialties[0];
        }
        return self::$typeLabels[$this->type]['en'] ?? 'Certified Specialty';
    }

    /*
    |--------------------------------------------------------------------------
    | Views Counter — عداد الزوار
    |--------------------------------------------------------------------------
    */

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /*
    |--------------------------------------------------------------------------
    | Completed Consultations — عدد الاستشارات المكتملة
    |--------------------------------------------------------------------------
    */

    public function getCompletedConsultationsCountAttribute(): int
    {
        if ($this->relationLoaded('requests')) {
            return $this->requests->where('status', 'done')->count();
        }
        return $this->requests()->where('status', 'done')->count();
    }
}

    

