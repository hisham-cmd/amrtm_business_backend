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

    protected $appends = ['avatar_url', 'initials'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean',
        'permissions'       => 'array',
    ];

    /**
     * رابط صورة الملف الشخصي الموجودة فعلاً في قاعدة البيانات، أو null.
     *
     * ممنوع توليد صور رمزية من خدمات خارجية: إن لم تُرفع صورة حقيقية
     * تكون القيمة null وتعرض الواجهة الأحرف الأولى محلياً بلا أي طلب شبكة.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $file = trim((string) $this->profile_photo);

        if ($file === '') {
            return null;
        }

        if (str_starts_with($file, 'http://') || str_starts_with($file, 'https://') || str_starts_with($file, '/')) {
            return $file;
        }

        return url('/media/uploads/' . rawurlencode($file));
    }

    /**
     * الأحرف الأولى من الاسم — تُعرض محلياً بلا أي جلب من الشبكة.
     */
    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/u', trim((string) $this->name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $out   = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            $out .= mb_substr($part, 0, 1, 'UTF-8');
        }

        return $out !== '' ? $out : '؟';
    }

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
