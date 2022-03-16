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
                    case 1366:
                        $errorMessage = 'Favor preencher os campos corretamente';
                        break;
                    case 1452:
                        $errorMessage = 'Esta ação têm dependencias de outra(s) tabela(s)';
                        break;
                    default:
                        //dd($exeption);
                        $errorMessage = $exeption->getMessage();
                        break;
                }
            }
            else{
                if(!$exeption == null)
                    $errorMessage = $exeption->getMessage();
                else
                    $errorMessage = 'Erro desconhecido';
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
