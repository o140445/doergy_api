<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;

// v1 API 路由
Route::prefix('v1')->middleware('auth:sanctum')->name('api.v1.')->group(function () {
        // 注册
        Route::post('register', [AuthController::class, 'register'])->name('register')->withoutMiddleware('auth:sanctum');
        // 登录
        Route::post('login', [AuthController::class, 'login'])->name('login')->withoutMiddleware('auth:sanctum');

        // 退出登录
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        // 获取用户信息
        Route::get('user', [UserController::class, 'getUserInfo'])->name('user.info');

        // 项目相关路由
        Route::Resource('projects', ProjectController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

        // 任务相关路由
        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/', [TaskController::class, 'index'])->name('index');
            Route::post('/', [TaskController::class, 'store'])->name('store');
            Route::get('/{id}', [TaskController::class, 'show'])->name('show');
            Route::put('/{id}', [TaskController::class, 'update'])->name('update');
            Route::delete('/{id}', [TaskController::class, 'destroy'])->name('destroy');

            // 完成
            Route::put('/complete/{id}', [TaskController::class, 'complete'])->name('complete');
            // 回滚状态
            Route::put('/restore/{id}', [TaskController::class, 'restore'])->name('restore');
        });
});

