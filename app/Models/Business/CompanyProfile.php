<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $connection = 'business';
    protected $table      = 'bs_company_profiles';

    protected $fillable = [
        'name',
        'commercial_registration',
        'address',
        'email',
        'phone',
        'manager_name',
    ];

    public static function current(): ?self
    {
        return static::query()->first();
    }
}
