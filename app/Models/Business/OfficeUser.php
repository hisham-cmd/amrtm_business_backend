<?php

namespace App\Models\Business;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;

class OfficeUser extends Authenticatable
{
    use HasApiTokens;
    protected $connection = 'business';
    protected $table = 'bs_office_users';

    protected $fillable = [
        'office_id', 'name', 'email', 'password', 'role', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function isOwner(): bool { return $this->role === 'owner'; }
    public function isManager(): bool { return in_array($this->role, ['owner', 'manager']); }
}