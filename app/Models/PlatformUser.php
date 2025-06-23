<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;


class PlatformUser extends Model
{
    use HasApiTokens;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'vip_expiration',
        'vip_type',
        'settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function tasks()
    {
        return $this->hasMany(\App\Models\Task::class, 'user_id', 'id');
    }

    // vip type 1免费 2月度 3年度
    const VIP_TYPE_FREE = 1; // 免费用户
    const VIP_TYPE_MONTHLY = 2; // 月度VIP
    const VIP_TYPE_YEARLY = 3; // 年度VIP

    public function isVip(): bool
    {
        return $this->vip_type !== self::VIP_TYPE_FREE && $this->vip_expiration && $this->vip_expiration > now();
    }

    public function isMonthlyVip(): bool
    {
        return $this->vip_type === self::VIP_TYPE_MONTHLY && $this->vip_expiration && $this->vip_expiration > now();
    }

    public function isYearlyVip(): bool
    {
        return $this->vip_type === self::VIP_TYPE_YEARLY && $this->vip_expiration && $this->vip_expiration > now();
    }

    // 获取vip枚举
    public static function getVipTypes(): array
    {
        return [
            self::VIP_TYPE_FREE => '免费用户',
            self::VIP_TYPE_MONTHLY => '月度 VIP',
            self::VIP_TYPE_YEARLY => '年度 VIP',
        ];
    }

    //getVipTypeLabel
    public static function getVipTypeLabel(int $type): string
    {
        return match ($type) {
            self::VIP_TYPE_FREE => '免费用户',
            self::VIP_TYPE_MONTHLY => '月度 VIP',
            self::VIP_TYPE_YEARLY => '年度 VIP',
            default => '未知类型',
        };
    }

    // 检查用户是否超过限制
    public function hasExceededLimit(string $limitType): bool
    {
        $limits = config('app.limit');
        $currentCount = $this->tasks()->count();
        return $currentCount >= $limits;
    }
}
