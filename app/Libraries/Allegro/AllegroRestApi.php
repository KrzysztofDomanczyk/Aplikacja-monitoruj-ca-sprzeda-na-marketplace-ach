<?php

namespace App\Libraries\Allegro;

use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use function GuzzleHttp\json_encode;

class AllegroRestApi
{
    protected $account_name;
    protected $environment = "https://api.allegro.pl";
    protected $settings;
    protected $clientId;
    protected $clientSecret;
    protected $authString;

    protected $deviceCode;
    protected $userCode;
    protected $expiresIn;
    protected $interval;
    protected $verificationUri;
    protected $verificationUriComplete;
    protected $access_token;
    protected $refresh_token;
    



    public function __construct($account_name)
    {
        $this->account_name = $account_name;
        $this->settings = Setting::where('account_name', $this->account_name)->first();
        if ($this->settings == null) {
            exit("Nie znaleziono danych dostępowych");
        }
        $this->clientId = $this->settings->clientId;
        $this->clientSecret = $this->settings->clientSecret;
        $this->deviceCode = $this->settings->clientSecret;
        $this->userCode = $this->settings->userCode;
        $this->expiresIn = $this->settings->expiresIn;
        $this->interval = $this->settings->interval;
        $this->verificationUri = $this->settings->verificationUri;
        $this->verificationUriComplete = $this->settings->verificationUriComplete;
        $this->access_token = $this->settings->access_token;
        $this->refresh_token = $this->settings->refresh_token;
        $this->authString = base64_encode("$this->clientId:$this->clientSecret");
    }

    public function getAccessToken(): void
    {
        $authUrl = "https://allegro.pl/auth/oauth/device";
        $ch = curl_init($authUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Basic $this->authString",
            'Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id=$this->clientId");
        dump($this->clientId);
        dump($this->clientSecret);
        

        $tokenResult = curl_exec($ch);
        $resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
     
        if ($tokenResult === false || $resultCode !== 200) {
            exit("Something went wrong");
        }
 
        $responseObject = json_decode($tokenResult);
        $this->loadTokenData($responseObject);
    }

    public function loadTokenData($responseObject = null)
    {
        if ($responseObject !== null) {
            $this->deviceCode = $responseObject->device_code;
            $this->userCode = $responseObject->user_code;
            $this->expiresIn = $responseObject->expires_in;
            $this->interval = $responseObject->interval;
            $this->verificationUri = $responseObject->verification_uri;
            $this->verificationUriComplete = $responseObject->verification_uri_complete;
            $this->settings->deviceCode = $this->deviceCode;
            $this->settings->userCode = $this->userCode;
            $this->settings->expiresIn = $this->expiresIn;
            $this->settings->interval = $this->interval;
            $this->settings->verificationUri = $this->verificationUri;
            $this->settings->verificationUriComplete = $this->verificationUriComplete;
            $this->settings->save();
        }
    }

    public function checkUserAcceptance()
    {
        $settings = Setting::where('account_name', $this->account_name)->first();

        $authUrl = "https://allegro.pl/auth/oauth/token?grant_type=urn%3Aietf%3Aparams%3Aoauth%3Agrant-type%3Adevice_code&device_code={$settings->deviceCode}";
        $ch = curl_init($authUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Basic $this->authString"
            ));
        $tokenResult = curl_exec($ch);
        $resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($tokenResult === false || $resultCode !== 200) {
            dd($tokenResult);
            exit("Something went wrong: $resultCode");
        }
        $tokenObject = json_decode($tokenResult);
        $settings->access_token = $tokenObject->access_token;
        $settings->refresh_token = $tokenObject->refresh_token;
        $settings->expiresIn = $tokenObject->expires_in;
        $settings->save();
    }

  
    protected function jsonResponse($rawResponse)
    {
        $json = json_decode($rawResponse, true);
        return json_decode(json_encode($json));
    }
    
    public function getVerificationUriComplete()
    {
        return $this->verificationUriComplete;
    }

    public function refreshToken()
    {
        dump($this->refresh_token);
        $authUrl = "https://allegro.pl/auth/oauth/token?grant_type=refresh_token&refresh_token={$this->refresh_token}";
        $ch = curl_init($authUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Basic $this->authString"
            ));
        $tokenResult = curl_exec($ch);
        $resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($tokenResult === false || $resultCode !== 200) {
            dump($tokenResult);
            exit("Something went wrong: $resultCode");
        }
        $result = json_decode($tokenResult, true);
        dump(json_decode($tokenResult, true));
        $setting = Setting::where('account_name', $this->account_name)->first();
        $setting->access_token = $result['access_token'];
        $setting->refresh_token = $result['refresh_token'];
        $setting->expiresIn = $result['expires_in'];
        $setting->save();
    }
}
