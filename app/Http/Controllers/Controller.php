<?php

namespace App\Http\Controllers;

abstract class Controller
{
    // success response
    protected function success($data = [], $message = '操作成功', $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    // error response
    protected function error($message = '操作失败', $code = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }
}
