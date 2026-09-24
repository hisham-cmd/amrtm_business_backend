<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeProfile extends Model
{
       protected $connection = 'business';

    protected $table = 'bs_office_profiles';

    protected $primaryKey = 'office_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'office_id',
        'license_number',
        'cr_number',
        'cr_expiry_date',
        'license_expiry_date',
        'mobile',
        'country',
        'governorate',
        'city',
        'district',
        'street',
        'building_number',
        'office_number',
        'description_ar',
        'description_en',
        'custom_specialty',
        'handled_cases',
        'profile_completed',
        'verification_status',
        'submitted_at',
        'approved_at',
        'office_code',
        'qr_code',
        'trademark_registration_number',
    ];

    protected $casts = [

        'profile_completed' => 'boolean',

        'submitted_at' => 'datetime',

        'approved_at' => 'datetime',

        'cr_expiry_date' => 'date',

        'license_expiry_date' => 'date',

    ];

    /*
    |--------------------------------------------------------------------------
    | Office
    |--------------------------------------------------------------------------
    */

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}