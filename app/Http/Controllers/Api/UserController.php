<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    // 获取用户信息
    public function getUserInfo()
    {
        $user = auth()->user();

        return $this->success([
            'id' => $user->id,
            'email' => $user->email,
            'vip_type' => $user->vip_type,
            'vip_expiration' => $user->vip_expiration,
        ]);
    }
}
