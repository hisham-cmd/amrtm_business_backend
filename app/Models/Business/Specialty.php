<?php

namespace App\Models\Business;

use App\Models\GovService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Specialty extends Model
{
    protected $connection = 'business';

    protected $table = 'bs_specialties';

    protected $fillable = [

        'office_type',

        'name_ar',

        'name_en',

        'is_active',

        'is_consultant',

        'category',

        'business_activity',

    ];

    protected $casts = [

        'is_active' => 'boolean',

    ];

    /*
    |--------------------------------------------------------------------------
    | Offices
    |--------------------------------------------------------------------------
    */

    public function offices(): BelongsToMany
    {
        return $this->belongsToMany(
            Office::class,
            'bs_office_specialties',
            'specialty_id',
            'office_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Linked government services (bs_specialty_services)
    |--------------------------------------------------------------------------
    */

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(
            GovService::class,
            'bs_specialty_services',
            'specialty_id',
            'service_id'
        );
    }
}