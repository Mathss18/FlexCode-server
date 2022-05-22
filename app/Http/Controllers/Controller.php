<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function is_base64($file)
    {
        $replace = substr($file, 0, strpos($file, ',') + 1);
        $file = str_replace($replace, '', $file);
        $file = str_replace(' ', '+', $file);

        if (base64_encode(base64_decode($file, true)) === $file) {
            return true;
        } else {
            return false;
        }
    }

    public function upload($file, $fileName, $folderName)
    {
        $extension = explode('/', explode(':', substr($file, 0, strpos($file, ';')))[1])[1];
        $replace = substr($file, 0, strpos($file, ',') + 1);
        $file = str_replace($replace, '', $file);
        $file = str_replace(' ', '+', $file);

        $fName = Str::kebab($fileName) . '.' . $extension;
        $fileUploaded = Storage::put('public/' . session('tenant')->nome . '/' . $folderName . '/' . $fName, base64_decode($file));

        if ($fileUploaded) {
            $url = config('app.url') . config('app.port') . '/' . "storage/" . session('tenant')->nome . '/' . $folderName . '/' . $fName;
            return $url;
        }
        return $fileUploaded;
    }

    public function uploadXML($xml, $chave)
    {
        $mes = date('m');
        $ano = date('Y');
        // Storage::put('nfe' . '/' . $mes . '-' . $ano . '/' . $chave . '.xml', $resp['xml']);
        $fName = $chave . '.xml';
        $fileUploaded = Storage::put('public/' . session('tenant')->nome . '/' . 'nfe' . '/'.$mes.'-'.$ano.'/'. $fName, $xml);

        if ($fileUploaded) {
            $url = config('app.url') . config('app.port') . '/' . "storage/" . session('tenant')->nome . '/' . 'nfe' . '/'.$mes.'-'.$ano.'/'. $fName;
            return $url;
        }
        return $fileUploaded;
    }

    public function uploadDANFE($pdf, $chave)
    {
        $mes = date('m');
        $ano = date('Y');

        $fName = $chave . '.pdf';
        $fileUploaded = Storage::put('public/' . session('tenant')->nome . '/' . 'nfe' . '/'.$mes.'-'.$ano.'/'. $fName, $pdf);

        if ($fileUploaded) {
            $url = config('app.url') . config('app.port') . '/' . "storage/" . session('tenant')->nome . '/' . 'nfe' . '/'.$mes.'-'.$ano.'/'. $fName;
            return $url;
        }
        return $fileUploaded;
    }

    public function tirarAcentos($string)
    {
        return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/", "/(Ç)/", "/(ç)/"), explode(" ", "a A e E i I o O u U n N C c"), $string);
    }
}
