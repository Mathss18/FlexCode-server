<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class APIHelper
{

    public static function APIResponse($success, $code, $message = null, $data = null, $exeption = null)
    {
        $response = [];
        if (!$success) {

            if ($exeption instanceof ModelNotFoundException) {
                $errorMessage = 'Não foi possivel encontrar';
            }
            else if($exeption instanceof QueryException){
                switch ($exeption->errorInfo[1]) {
                    case 1048:
                        $errorMessage = 'Favor preencher todos os campos obrigatorios';
                        break;
                    default:
                        $errorMessage = $exeption->getMessage();
                        break;
                }
            }
            else{
                $errorMessage = $exeption->getMessage();
            }

            $response['success'] = $success;
            $response['code'] = $code;
            $response['message'] = $message ?? $errorMessage;
            // $response['data'] = null;
        }
        else {
            $response['success'] = $success;
            $response['code'] = $code;
            $response['message'] = $message;
            $response['data'] = $data;
        }

        return $response;
    }
}