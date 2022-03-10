<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AESEncryption;

class EncryptionController extends Controller
{
    public static function encrypt (Request $request) {

        $data = $request->all();

        if(isset($data['q'])) {

            $aesEncryption = new AESEncryption();

            $inputKey = env('ENCRYPTION_KEY');

            $encryptedText = $aesEncryption->encrypt( $data['q'], $inputKey );

            $dataArray = [
                'response' => 'success',
                'data'  =>  $encryptedText,
            ];

            return response($dataArray, 200)
                  ->header('Content-Type', 'text/json');

        }
        else {

            $dataArray = [
                'response' => 'error',
                'data'  =>  "Invalid Request Sent",
            ];

            return response($dataArray, 403)
                  ->header('Content-Type', 'text/json');

        }

    }
    
    public static function decrypt(Request $request) {
        
        // dd($request);
        $data = $request->all();
        if(isset($data['q'])) {

            $aesEncryption = new AESEncryption();

            $inputKey = env('ENCRYPTION_KEY');

            $decryptedText = $aesEncryption->decrypt( $data['q'], $inputKey );

            $dataArray = [
                'response' => 'success',
                'data'  =>  $decryptedText,
            ];

            return response($dataArray, 200)
                  ->header('Content-Type', 'text/json');

        }
        else {

            $dataArray = [
                'response' => 'error',
                'data'  =>  "Invalid Request Sent",
            ];

            return response($dataArray, 403)
                  ->header('Content-Type', 'text/json');

        }
        
    }
}
