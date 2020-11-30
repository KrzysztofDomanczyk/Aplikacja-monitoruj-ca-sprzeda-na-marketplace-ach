<?php

namespace App\Libraries\Allegro;

use Exception;
use Illuminate\Http\Request;

use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

class AllegroOffers extends AllegroRestApi
{
  
    public function __construct($account_name) {
        parent::__construct($account_name);
    }

    private function getOffers($authUrl)
    {
        $ch = curl_init($authUrl);
 
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer $this->access_token",
                "Accept: application/vnd.allegro.public.v1+json"
            ));
 
        $tokenResult = curl_exec($ch);
        $resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($tokenResult === false || $resultCode !== 200) {
            $tokenResult = json_decode($tokenResult);
            throw new Exception($tokenResult->error_description);
            ("Something went wrong" . $resultCode);
        }
        $results = $this->jsonResponse($tokenResult);
        if ($results->totalCount == 0) {
            throw new Exception("totalCount = 0");
        }

        return $results;
    }

    public function getOfferById($offerId) {
        $authUrl = "$this->environment/sale/offers/{$offerId}";
        return $this->getOffers($authUrl);
    }

    public function getOffersByExternalId($externalId) {
        $authUrl = "$this->environment/sale/offers?external.id={$externalId}";
        return $this->getOffers($authUrl);
    }

    public function getAllOffers($offset = 0, $limit = 1000)
    {
        $params = "limit={$limit}&offset={$offset}&publication.status=ACTIVE&publication.status=ACTIVATING";
        $authUrl = "$this->environment/sale/offers?" . $params;
        return $this->getOffers($authUrl);
    }
    
}
