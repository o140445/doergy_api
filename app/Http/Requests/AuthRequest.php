<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
            'status' => 'error',
            'message' => $validator->errors()->first(),
        ], 400));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $path = $this->path();
        $paths = explode('/', $path);
        switch (end($paths)) {
            case "login":
                return [
                    'email' => 'required|email',
                    'password' => 'required|string',
                ];
            case "register":
                return [
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:6|confirmed',
                    'password_confirmation' => 'required|min:6',
                ];
            default:
                return [];
        }
    }

    public function messages()
    {
        return [
            'email.required' => '邮箱是必须的',
            'email.email' => '邮箱格式不正确',
            'password.required' => '密码是必须的',
            'password.min' => '密码长度不能少于6位',
            'password.confirmed' => '密码和确认密码不匹配',
            'password_confirmation.required' => '确认密码是必须的',
            'password_confirmation.min' => '确认密码长度不能少于6位',
        ];
    }
}
