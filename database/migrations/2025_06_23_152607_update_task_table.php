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
        // add start_time end_time to tasks
        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('start_time')->nullable()->after('due_date'); // 任务开始时间
            $table->timestamp('end_time')->nullable()->after('start_time'); // 任务结束时间
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // remove start_time end_time from tasks
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });
    }
};
