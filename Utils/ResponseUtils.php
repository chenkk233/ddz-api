<?php
// ResponseUtils.php

class ResponseUtils
{
    public static function ok_js($data = null, $msg = 'OK', $code = 200)
    {
        $response = [
            'code' => $code,
            'message' => $msg,
            'data' => $data,
        ];
        return json_encode($response,256);
    }

    public static function err_js($msg = 'Error', $code = 400)
    {
        $response = [
            'code' => $code,
            'message' => $msg
        ];
        return json_encode($response,256);
    }
}
