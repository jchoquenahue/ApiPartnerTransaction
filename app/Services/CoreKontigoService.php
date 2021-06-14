<?php
namespace App\Services;
use App\Traits\ConsumesExternalService;
use Illuminate\Support\Facades\Log;

class CoreKontigoService
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
    public function __construct()
    {
        $this->baseUri = config('services.corek.base_uri');
        $this->secret = config('services.corek.secret');
    }

    /**
     * @return string
     */
    public function sendRequestPermits($data)
    {

        /**
         *  'dni'         => 'required'   ,
        'client_id'         => 'required'   ,
        'account_id'        => 'required'   ,
        'partner_id'        => 'required'   ,
        'reference_number'  => 'required|string|max:32'   ,
        'phone_number'      => 'required'   ,
        'account_status'    => 'required'  ,
        'created_at'    => 'required'  ,
        'expiration_at'    => 'required'  ,
         */
        $document_type=1;

        $solicitud=[
            'PartnerID'=>$data['partner_id']   ,//KONTIGODEV01  -> esperamos valor
            'ClientID'=>$data['client_id']  ,//pedir a partner código unico de navegacion del cliente
            'AccountID'=>$document_type.$data['dni_number'],
            'ReferenceNumber'=> $data['reference_number']  ,// reference number partner (GUUID 32)
            'DateTime'=>date('Y-m-d H:i:s'),
            'ExpirationAt'=> $data['expire_at'] ,
        ];

        $result=json_decode($this->performRequest('POST','/security/tokenService',$solicitud));
        Log::info("CoreKontigoService Result: ".json_encode($result));
        $respo=[

            'result_code'=>$result->ResultCode,//:  se proceso con exito 0, existió error 1
            'status'=>$result->Status   ,//  : resultado del permiso:  ALLOW, DENY
            'reference_number'=>$result->ReferenceNumber ,//: referencia de solicitud
            'error_message'=>$result->Message ,//: referencia de solicitud
            'dni_id'=>$solicitud['AccountID'] ,//: referencia de solicitud
        ];
        return (object)$respo;
    }


}
