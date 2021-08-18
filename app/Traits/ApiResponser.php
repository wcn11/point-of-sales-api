<?php


namespace App\Traits;


trait ApiResponser{

    protected function successResponse($data = [], $message = null, $code = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'=> true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message = null, $redirect = false, $code = 401): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'=> false,
            'message' => $message,
            'data' => null,
            'redirect' => $redirect
        ], $code);
    }

}
