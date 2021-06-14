<?php

namespace App\Traits;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;


trait ConsumesExternalService
{
    /**
     * Send a request to any service
     * @return string
     */
    public function performRequest($method, $requestUrl, $formParams = [], $headers = [])
    {
        $client = new Client([
            'base_uri' => $this->baseUri,
        ]);

         //return "Esta es la url".$this->baseUri;
         Log::info("antes del secret");

       if (isset($this->secret)) {
            $headers['Authorization'] = $this->secret;
        }
        Log::info("Request:".$method.', Request'. $requestUrl);

        $response = $client->request($method, $requestUrl, ['form_params' => $formParams, 'headers' => $headers]);

        Log::info("paso el respond");
        return $response->getBody()->getContents();
    }
}
