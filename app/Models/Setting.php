<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'description',
        'is_public',
        'is_editable',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_editable' => 'boolean',
    ];

    /**
     * Boot 方法
     */
    protected static function boot()
    {
        parent::boot();

        // 清除快取
        static::saved(function () {
            Cache::forget('settings');
        });

        static::deleted(function () {
            Cache::forget('settings');
        });
    }

    /**
     * 獲取設定值
     */
    public static function get(string $key, $default = null)
    {
        $settings = Cache::rememberForever('settings', function () {
            return static::all()->pluck('value', 'key')->toArray();
        });

        if (!isset($settings[$key])) {
            return $default;
        }

        $setting = static::where('key', $key)->first();

        return static::castValue($settings[$key], $setting->type ?? 'string');
    }

    /**
     * 設定值
     */
    public static function set(string $key, $value, string $group = 'general'): void
    {
        $setting = static::firstOrNew(['key' => $key]);

        $setting->value = is_array($value) ? json_encode($value) : $value;
        $setting->group = $group;
        $setting->type = static::detectType($value);
        $setting->save();

        Cache::forget('settings');
    }

    /**
     * 批次設定
     */
    public static function setMany(array $settings, string $group = 'general'): void
    {
        foreach ($settings as $key => $value) {
            static::set($key, $value, $group);
        }
    }

    /**
     * 檢測資料類型
     */
    protected static function detectType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_int($value)) {
            return 'integer';
        }

        if (is_array($value)) {
            return 'array';
        }

        return 'string';
    }

    /**
     * 轉換值類型
     */
    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'array', 'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * 獲取群組設定
     */
    public static function getGroup(string $group): array
    {
        $settings = static::where('group', $group)->get();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = static::castValue($setting->value, $setting->type);
        }

        return $result;
    }

    /**
     * 獲取公開設定
     */
    public static function getPublic(): array
    {
        $settings = static::where('is_public', true)->get();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = static::castValue($setting->value, $setting->type);
        }

        return $result;
    }

    /**
     * 檢查是否存在
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * 刪除設定
     */
    public static function remove(string $key): bool
    {
        Cache::forget('settings');
        return static::where('key', $key)->delete();
    }
}
