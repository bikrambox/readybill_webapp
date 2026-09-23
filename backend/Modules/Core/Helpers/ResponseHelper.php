<?php

namespace Modules\Core\Helpers;

class ResponseHelper
{

    public static function responseFn($status, $code, $message = '', $data = '')
    {
        $responseData = [
            'status' => $status,
            // 'message' => $message,
        ];
        if (is_array($message)) {
            $responseData['message'] = implode(' ', $message);
        } else {
            $responseData['message'] = strval($message);
        }

        if ($data !== null) {
            $responseData['data'] = $data;
        }

        if ($code !== null) {
            $responseData['code'] = $code;
        }

        return response()->json($responseData, $code);
    }
}