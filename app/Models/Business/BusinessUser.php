<?php

namespace App\Models\Business;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class BusinessUser extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'business';
    protected $table      = 'bs_users';

    protected $fillable = [
        'name',
        'father_name',
        'grandfather_name',
        'family_name',
        'id_number',
        'nafath_verified_at',
        'job_sector',
        'employment_status',
        'profile_photo',
        'legal_name',
        'entity_type',
        'cr_number',
        'cr_expiry_date',
        'license_expiry_date',
        'country',
        'region',
        'city',
        'district',
        'street',
        'building_number',
        'office_number',
        'postal_code',
        'account_type',
        'phone_dial',
        'representative_name',
        'representative_role',
        'email',
        'phone',
        'password',
        'role',
        'is_active',
        'permissions',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean',
        'permissions'       => 'array',
    ];

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'supervisor']);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'supervisor') return true;
        return in_array($permission, $this->permissions ?? []);
    }

    public function grantPermission(string $permission): void
    {
        $perms = $this->permissions ?? [];
        if (!in_array($permission, $perms)) {
            $perms[] = $permission;
            $this->update(['permissions' => $perms]);
        }
    }

    public function revokePermission(string $permission): void
    {
        $this->update(['permissions' => array_values(array_filter($this->permissions ?? [], fn($p) => $p !== $permission))]);
    }
}
