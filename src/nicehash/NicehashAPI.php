<?php

namespace SoloDed\Nicehash;

class NicehashAPI
{

    protected $baseURL = 'https://api2.nicehash.com';
    protected $APIKey;
    protected $SecretKey;
    protected $OrganizationID;

    public function __construct($APIKey, $SecretKey, $OrganizationID)
    {
        $this->APIKey = $APIKey;
        $this->SecretKey = $SecretKey;
        $this->OrganizationID = $OrganizationID;
    }


    public static function createNonce()
    {
        return substr(hash_hmac('sha256', uniqid('', true), 'randomstring'), 0, 32);
    }

    public static function getServerTime()
    {

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://api2.nicehash.com/api/v2/time');

        if ($response->getStatusCode() != 200){
            return false;
        } else {
            $result = json_decode($response->getBody(), true);

            if (empty($result['serverTime'])) {
                return false;
            } else {
                return $result['serverTime'];
            }
        }

    }

    public function get($path = '')
    {
        $time      = self::getServerTime();
        $nonce     = self::createNonce();

        $signature = $this->APIKey."\x00".$time."\x00".$nonce."\x00"."\x00".$this->OrganizationID."\x00"."\x00"."GET"."\x00".$path."\x00";
        $signhash  = hash_hmac('sha256', $signature, $this->SecretKey);
    
        $headers = [
            "X-Time" => $time,
            "X-Nonce" => $nonce,
            "X-Organization-Id" => $this->OrganizationID,
            "X-Request-Id" => $nonce,
            "X-Auth" => $this->APIKey . ":" . $signhash
        ];

        $client = new \GuzzleHttp\Client([
            'headers' => $headers
        ]);
        $response = $client->request('GET', $this->baseURL . $path);
        
        if ($response->getStatusCode() != 200){
            return false;
        } else {
            return json_decode($response->getBody(), true);
        }
    }

    public function getWithQueryString($path = '',array $query = [])
    {

        $queryArr = [];

        foreach ($query as $key => $value) {
            $queryArr[] = $key . '=' . $value;
        }

        $qs = join('&', $queryArr);
    
        $time      = self::getServerTime();
        $nonce     = self::createNonce();

        $signature = $this->APIKey."\x00".$time."\x00".$nonce."\x00"."\x00".$this->OrganizationID."\x00"."\x00"."GET"."\x00".$path."\x00".$qs;
        $signhash  = hash_hmac('sha256', $signature, $this->SecretKey);
    
        $headers = [
            "X-Time" => $time,
            "X-Nonce" => $nonce,
            "X-Organization-Id" => $this->OrganizationID,
            "X-Request-Id" => $nonce,
            "X-Auth" => $this->APIKey . ":" . $signhash
        ];

        $client = new \GuzzleHttp\Client([
            'headers' => $headers
        ]);
        $response = $client->request('GET', $this->baseURL . $path . '?' . $qs);
        
        if ($response->getStatusCode() != 200){
            return false;
        } else {
            return json_decode($response->getBody(), true);
        }
    }

}