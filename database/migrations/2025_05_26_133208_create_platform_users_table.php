<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('platform_users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('vip_expiration')->nullable(); // VIP到期时间
            $table->tinyInteger('vip_type')->default(0); // VIP类型，0: 普通用户, 1: 月度VIP, 2: 年度VIP
            $table->json('settings')->nullable(); // 暗黑模式、语言偏好等
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_users');
    }
};
