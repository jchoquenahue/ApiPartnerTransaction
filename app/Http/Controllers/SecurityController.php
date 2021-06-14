<?php

namespace App\Http\Controllers;

use App\Models\Permits;
use App\Models\Logg;
use App\Services\CoreKontigoService;
use App\Services\PartnerService;
use App\Services\PdaService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Helpers\Functions;
use PhpParser\Node\Expr\Cast\Object_;

class SecurityController extends Controller
{
    public $token_timeout;
    public $permit_timeout;
    use ApiResponser;

    public  $pdaService;
    public  $coreKontigoService;
    public  $partnerService;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PdaService $pdaService,
                                CoreKontigoService $coreKontigoService,
                                PartnerService $partnerService)
    {
          $this->pdaService=$pdaService;
          $this->coreKontigoService=$coreKontigoService;
          $this->partnerService=$partnerService;

        $this->token_timeout=config('services.token.timeout');
        $this->permit_timeout=config('services.permit.timeout');
    }
    public function index(){
        return "Kontigo";
    }

    /** Solicitar enviar a Core la solicitud de permiso
    client_id
    account_id
    partner_id
    reference_number
    phone_number
    account_status


        cliente Core:
        'ConsumerID'   //KONTIGODEV01  -> esperamos valor
        'ClientID'   // pedir a partner código unico de navegacion del cliente
        'AcountID // code documento+número de documento
        'ReferenceNumber'   // reference number enviado por el partner (GUUID 32)
        'DateTime' => date(DATE_ATOM),
     * @param Request $request
     */
    /**
     * @param Request $request
     * @return \App\Traits\Illuminate\Http\JsonResponse|\App\Traits\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sendRequestTokenService(Request $request) {


         $rules = [
                'dni_number'         => 'required'   ,
                'client_id'         => 'required'   ,
                'account_id'        => 'required'   ,
                'partner_id'        => 'required'   ,
                'reference_number'  => 'required|string|max:32'   ,
                'phone_number'      => 'required'   ,
                'account_status'    => 'required'  ,

        ];

        $request->created_at    =date('Y-m-d H:i:s');
        $request->expire_at    =date('Y-m-d H:i:s', strtotime("+".$this->permit_timeout." minutes"));
         $this->validate($request,$rules);

         //No sea repetido
        $permitsv=Permits::where('account_id',$request->account_id)
                        ->whereRaw("expire_at >='".$request->created_at."'")->first();
        if($permitsv!=null)
            return $this->errorResponse('There is a pending request.', Response::HTTP_UNPROCESSABLE_ENTITY);
        //notificar a Core, si es error no se registra para que pueda volver a solicitar.

        $notify=$this->coreKontigoService->sendRequestPermits($request);

        if($notify==null){
             return $this->errorResponse('Something happened, please try in a moment.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        Log::info("Servicio Permits result ".$notify->result_code);
        if($notify->result_code==1){
            return $this->errorResponse('Something happened, please try in a moment.:'.$notify->error_message, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $permits=Permits::create([
            'client_id'=> $request->client_id,
            'expire_at'=> $request->expire_at,
            'account_id'=> $request->account_id,
            'partner_id'=>$request->partner_id ,
            'reference_number'=> $request->reference_number,
            'status'=>$notify->status,
            'dni_id' => $notify->dni_id,
        ]);

        $respond=[
            'account_id'=>$notify->dni_id,
            'status'=>$notify->status,
            'expiration_at'=>$request->expire_at,
            'result_code'=>$notify->result_code,
        ];
       Log::info("Registro del Permits".json_encode($permits)." y notificando al core");


        return $this->successResponse($respond, Response::HTTP_OK);

    }


    /**recibir respuesta de permiso del Core Kontigo
    ResultCode: se proceso con exito 0, existió error 1  
    Status     : resultado del permiso:  ALLOW, DENY  
    Datetime   :hora que acepto  
    AcountID    : ide de cliente tipodni+dni  
    ReferenceNumber : referencia de solicitud
     * @param Request $request
     */
    public function whReceiveTokenService(Request $request) {

        $rules=[
            'ResultCode'=> 'required',
            'Status' => 'required',
            'Datetime'=> 'required',
            'AccountID'=> 'required',
            'ReferenceNumber'=> 'required',
        ];
        $this->validate($request,$rules);
        $request=$this->parameters($request);

        $request->isToken=true;

        $permits=Permits::where('dni_id',$request->account_id)
            ->where('reference_number',$request->reference_number)
            ->where('status','PEN')->first();


        Log::debug('whReceiveTokenService Permits: '.json_encode($permits));
        if($permits==null){ //dni_id y  reference no existe y no hacemos nada solo responder a core error
            return $this->errorResponse('Invalid reference', Response::HTTP_UNPROCESSABLE_ENTITY);
            $isPermiso=false;
        }
        $request->permits_id=$permits->id;
        if(strtotime($request->datetime)>strtotime($permits->expire_at)){
            $request->error='The request has expired, please try again';
            $request->result_code=1;
            $request->isToken=false;
        }
        if($request->result_code!=0) { // Notifica al pertner para que vuelav intentar
            $request->error='There was an error, please try again';
            $request->isToken=false;
        }
        if($request->status=='DENY'){ //Notofica al partner no se permite
            $request->error='The client does not authorize the action';
            $request->result_code=0;
            $request->isToken=false;
        }

        if(!$request->isToken){
            Permits::where('dni_id',$request->AccountID)
                ->where('reference_number',$request->ReferenceNumber)
                ->update(['respond_at' => $request->Datetime,
                    'status'=>$request->status,
                    'error'=>$request->error,
                    'result_code'=>$request->result_code]
                );
            $this->partnerService->tokenError($request);
        }

        if($request->Status=='ALLOW'){
            $request=$this->getToken($request);
            Permits::where('dni_id',$request->account_id)
                ->where('reference_number',$request->ReferenceNumber)
                ->update(['token' => $request->token,
                    'token_refresh'=>$request->token_refresh,
                    'token_expire_at'=>$request->token_expire_at ,
                    'grant_type'=> $request->grant_type,
                    'status'=>  $request->status ,
                ]);
            $this->partnerService->tokenSuccess($request);
            return $this->successResponse('OK', Response::HTTP_OK);
        }
        return $this->errorResponse('Undefined error', Response::HTTP_OK);
    }


    /** Coonsulta si es valido un token service
     * @param Request $request
     */
    public function getTokenService(Request $request) {
        $permits=Permits::findOrFail($request->id);
        return$permits;

        //Busca Permits usando id de usuario y token

        //Valida si esta activo

        //reponde token
      //  return $this->successResponse($notify, Response::HTTP_OK);
    }

    /**************
     *** SOPORTE***
     ***************/

    /**
     * @param Request $request
     * @return Request
     */
    private function parameters(Request $request){
        $request->result_code  =$request->ResultCode;
        $request->status  =$request->Status;
        $request->datetime  =$request->Datetime;
        $request->account_id  =$request->AccountID;
        $request->reference_number  =$request->ReferenceNumber;
        return $request;
    }
    /**
     * @param $request
     * @return mixed
     */
    private function getToken($request){
        $helper = new Functions();

        $request->grant_type= 'merchant';
        $request->token = $request->permits_id.'-'.$helper->random(500);
        $request->token_refresh = $request->permits_id.'-'.$helper->random(500);
        $request->token_created_at = $request->datetime;
        $request->token_expire_at = date ( 'Y-m-d H:i:s' ,strtotime('+'. $this->token_timeout.' minute', strtotime ( $request->datetime)));

        return $request;
    }








}
