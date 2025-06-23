<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Models\PlatformUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**     * 用户注册
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function register(AuthRequest $request) :\Illuminate\Http\JsonResponse
    {
        // 获取请求数据
        $data['email'] = $request->input('email');
        $data['password'] = $request->input('password');
        $data['password_confirmation'] = $request->input('password_confirmation');

        // 检查邮箱是否已被注册
        if (PlatformUser::where('email', $data['email'])->exists()) {
            return $this->error('邮箱已被注册', 422);
        }

        // 检查密码和确认密码是否匹配
        if ($data['password'] !== $data['password_confirmation']) {
            return $this->error('密码和确认密码不匹配', 422);
        }

        $user = PlatformUser::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'vip_type' => PlatformUser::VIP_TYPE_FREE, // 默认设置为免费用户
            'vip_expiration' => null,
        ]);

        return $this->success([
            'token' => $user->createToken('api')->plainTextToken,
        ], '注册成功');
    }

    // 登录
    public function login(AuthRequest $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = PlatformUser::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return $this->error('邮箱或密码错误', 400);
        }

        return $this->success([
            'token' => $user->createToken('api')->plainTextToken,
        ], '登录成功');
    }

    // 退出登录
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success([], '退出登录成功');
    }
}
