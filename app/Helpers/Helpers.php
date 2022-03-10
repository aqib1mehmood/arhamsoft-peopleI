<?php

namespace App\Helpers;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class Helpers
{
    private $url = "http://192.168.99.29/ppi_people";
    public function SetNotification($notify)
    {

        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__ . '/peopletest-961e7-firebase-adminsdk-zc4ct-49ddb89159.json');
        $firebase       = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->withDatabaseUri('https://peopletest-961e7.firebaseio.com/')
            ->create();
        $database = $firebase->getDatabase();
        $message  = [
            'message'  => $notify['message'],
            'app_type' => $notify['app_type'],
            'app_id'   => $notify['app_id'],
        ];
        $data = [
            'client_id'    => $notify['client_id'],
            'emp_id'       => $notify['emp_id'],
            'app_token'    => $notify['app_token'],
            'notification' => $message,
        ];
        $ref = $notify['node'];
        return $database->getReference($ref)->push($data);
    }

    public function upload_attachment_file($files, $directory)
    {

        if (is_object($files)) {
            $file_temp_name = $files->getPathName();
            $file_type      = $files->getClientOriginalExtension();
            $file_name      = $files->getClientOriginalName();

        } else {

            $file_temp_name = $files['tmp_name'];
            $file_type      = $files['type'];
            $file_name      = $files['name'];

        }

        if (function_exists('curl_file_create')) {
            $cFile = curl_file_create($file_temp_name, $file_type, $file_name);
        }
        $post = array('directory' => $directory, 'document' => $cFile);
        $ch   = curl_init();
        $url  = "" . $this->url . "/file_uploader.php";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec($ch);
        curl_close($ch);
        $fileInfo = json_decode($result);

        return $fileInfo->filename;
    }

    public function include_route_files($folder)
    {
        foreach (glob("{$folder}/*.php") as $filename) {
            include $filename;
        }
    }

    public function PasswordRecover($loginname)
    {
        $post = array('uname' => $loginname);
        $ch   = curl_init();
        $url  = "" . $this->url . "/fpassword.php";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec($ch);
        curl_close($ch);
        return 0;
    }

}
