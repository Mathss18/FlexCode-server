<?php

namespace App\Helpers;

class APIHelper{

    public static function APIResponse($success, $code, $message, $data=null){
        $response = [];
        if(!$success){
            $response['success'] = $success;
            $response['code'] = $code;
            $response['message'] = $message;
            // $response['data'] = null;
        }
        else{
            $response['success'] = $success;
            $response['code'] = $code;
            $response['message'] = $message;
            $response['data'] = $data;
        }

        return $response;
    }
}
