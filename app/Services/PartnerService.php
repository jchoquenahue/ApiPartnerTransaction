<?php
namespace App\Services;
use App\Traits\ConsumesExternalService;
use Illuminate\Support\Facades\Log;

class PartnerService
{
    use ConsumesExternalService;

    /**
     * Url de author
     * @var  string
     */
    public $baseUri;
    /**
     * secret para usar Authors
     * @var String
     */
    public $secret;
    public $webhook;
    public function __construct()
    {
        $this->baseUri = config('services.partner.base_uri');
        $this->secret = config('services.partner.secret');
        $this->webhook = config('services.partner.webhook');

    }

    /**
     * @param $request
     * @return string
     */
    public function tokenError( $request){
        $data=[
            'reference_number'=>$request->reference_number,
            'result_code'=>$request->result_code,
            'error'=>$request->error,
        ];
        return $this->performRequest('POST','/'.$this->webhook,$data);
    }


    /**
     * @param $request
     * @return string
     */
    public function tokenSuccess( $request){
        $data=[
            'reference_number'=>$request->ReferenceNumber,
            'token'=>$request->token,
            'refresh_token'=> $request->token_refresh,
            'expiration_at'=>$request->token_expire_at,
            'result_code'=>$request->ResultCode,
        ];
        return $this->performRequest('POST','/'.$this->webhook,$data);

    }





}
