<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessNotification extends Model
{
    protected $connection = 'business';
    protected $table      = 'bs_notifications';

    protected $fillable = [
        'recipient_type', 'user_id', 'office_id',
        'type', 'title', 'body', 'data', 'request_id', 'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'data'    => 'array',
    ];

    /* ── Factory helpers ── */

    public static function forUser(int $userId, string $type, string $title, string $body = '', array $data = [], ?int $requestId = null): self
    {
        return self::create([
            'recipient_type' => 'user',
            'user_id'        => $userId,
            'type'           => $type,
            'title'          => $title,
            'body'           => $body ?: null,
            'data'           => $data ?: null,
            'request_id'     => $requestId,
        ]);
    }

    public static function forOffice(int $officeId, string $type, string $title, string $body = '', array $data = [], ?int $requestId = null): self
    {
        return self::create([
            'recipient_type' => 'office',
            'office_id'      => $officeId,
            'type'           => $type,
            'title'          => $title,
            'body'           => $body ?: null,
            'data'           => $data ?: null,
            'request_id'     => $requestId,
        ]);
    }

    public static function forAdmin(string $type, string $title, string $body = '', array $data = [], ?int $requestId = null): self
    {
        return self::create([
            'recipient_type' => 'admin',
            'type'           => $type,
            'title'          => $title,
            'body'           => $body ?: null,
            'data'           => $data ?: null,
            'request_id'     => $requestId,
        ]);
    }

    /** @deprecated use forUser/forOffice/forAdmin */
    public static function send(int $userId, string $type, string $title, string $body, ?int $requestId = null): self
    {
        return self::forUser($userId, $type, $title, $body, [], $requestId);
    }
}