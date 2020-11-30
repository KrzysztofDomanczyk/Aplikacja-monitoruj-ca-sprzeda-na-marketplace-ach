<?php

namespace App\Libraries\IAI;

use Exception;

class IaiApi extends Auth
{

    protected $products;

    protected function getResponse($params, $settings = null, $old = false)
    {
        if ($old == false) {
            $request = [
                'authenticate' => [
                    'userLogin' =>  Auth::getLogin(),
                    'authenticateKey' => Auth::getAuthenticatedKey()
                ],
                key($params) => $params[key($params)]
            ];
        } else {

            $request = [
                'authenticate' => [
                    'system_key' =>  sha1(date('Ymd') . sha1("3Wgq8Xs6BU")),
                    'system_login' => "DEV"
                ],
                key($params) => $params[key($params)],
                key($settings) => $settings[key($settings)]
            ];
        }
       
        $request = $this->convertToArray($this->send($request));
        if ($request['errors']['faultCode'] != 0 && $request['errors']['faultCode'] != 2) {
            throw new Exception($request['errors']['faultString']);
        }

        return $request;
    }

    protected function convertToArray($array)
    {
        return json_decode($array, true);
    }

    protected function send($request)
    {

        $request_json = json_encode($request);
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json;charset=UTF-8'
        );
        $curl = curl_init($this->address);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        $status = curl_getinfo($curl);
        curl_close($curl);
        return $response;
    }
}
