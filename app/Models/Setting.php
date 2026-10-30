<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'label', 'value', 'type', 'group'];

    private static array $cache = [];

    // Read a setting's value, falling back to $default. Safe before the table
    // exists (returns the default) so views never break pre-migration.
    public static function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key] ?? $default;
        }

        try {
            $value = static::query()->where('key', $key)->value('value');
        } catch (\Throwable $e) {
            return $default;
        }

        self::$cache[$key] = $value;

        return $value ?? $default;
    }

    public static function flushCache(): void
    {
        self::$cache = [];
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::flushCache());
        static::deleted(fn () => self::flushCache());
    }
}
