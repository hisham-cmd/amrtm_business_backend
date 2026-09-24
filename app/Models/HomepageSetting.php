<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class HomepageSetting extends Model
{
    protected $table = 'homepage_settings';

    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value with an optional fallback default.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $row = static::query()->where('key', $key)->first();

        return $row->value ?? $default;
    }

    /**
     * Set (insert-or-update) a setting value.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
