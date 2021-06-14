<?php

namespace App\Http\Controllers;

use App\Models\Permits;
use App\Models\Logg;
use App\Models\Transactions;
use App\Services\PdaService;
use App\Traits\ApiResponser;
use http\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransactionsController extends Controller
{ use ApiResponser;
    public $token_timeout;
    public $permit_timeout;
    public $pdaService;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( PdaService $pdaService)
    {
        $this->pdaService=$pdaService;
        $this->token_timeout=config('services.token.timeout');
        $this->permit_timeout=config('services.permit.timeout');
    }
    public function index(){
        return "Kontigo";
    }



    /**transaccionar con CI y CO
    "partner_id":1
     * ,"accountID":"173741409"
     * ,"reference_number":"referenceTransacion10"
     * ,"client_id":"cliente_id_referentcevisita"
     * ,"transactionType":"09"
     * ,"amount":"10.70"}
     *
     */

    public function  transaction(Request $request){
        $rules=[
            'dni_id'=>'required',
            'reference_number'=>'required',
            'client_id'=>'required',
            'transactionType'=>'required',
            'amount'=>'required',
        ];
        $this->validate($request,$rules);
        $checkSum='GMONEYCERTIFICACION';
        $transac=Transactions::create([
            'permit_id'=> $request->permit_id,
            'Client_id'=> $request->client_id,
            'partner_id'=> $request->partner_id,
            'account_id'=> $request->account_id,
            'dni_id'=> $request->dni_id,
            'token_id'=> $request->token_id,
            'transacion_type'=> $request->transactionType,
            'reference_number'=> $request->reference_number,
            'status'=>'PEN' ,
        ]);
        Log::debug('VAlores ('.$request->client_id.','. $request->reference_number
            .','.$checkSum.','.$request->accountID.','.$request->transactionType.','.$request->amount);

        $result=   $this->pdaService->OperationB2A($request->client_id, $request->reference_number
            , $checkSum,$request->account_id ,$request->transactionType,$request->amount);
        Log::debug('result SOAP: '.json_encode($result));

        if(empty($result->ErrorCode))
            $result->ErrorCode=200;

        Transactions::where('id',$transac->id)
            ->update([ 'transaction_id'=> $result->TransactionID,
                        'status'=> $result->Message,
                        'respond'=> json_encode($result),
                        'result_code'=> $result->ResultCode,
                    'error_code'=> $result->ErrorCode,
                    'datetime_end'=> $result->DateTime,
                    'message'=>$result->Message]
            );


        $res=[
            'result_code'=>$result->ResultCode,
            'datetime'=>$result->DateTime,
            'transactionID'=>$result->TransactionID,
            'status'=>$result->Message,
            'expiration_at'=>$result->ExpirationTime,
        ];
        return $this->successResponse($res,Response::HTTP_OK);

    }

    /** poder rever
     * Llega:
     *    'partner_id'=>$request->partner_id,
    'account_id'=> $request->account_id ,
    'dni_id'=> $request->dni_id ,
    'client_id'=> $request->client_id ,
    'reference_number'=> $request->reference_number,
    'transactionID'=> $request->transactionType,
     *
     */
    public function  reverse(Request $request){
        $rules=[
            'dni_id'=>'required',
            'reference_number'=>'required',
            'client_id'=>'required',
        ];
        $this->validate($request,$rules);
        $checkSum='GMONEYCERTIFICACION';
        $transac=Transactions::create([
            'permit_id'=> $request->permit_id,
            'Client_id'=> $request->client_id,
            'partner_id'=> $request->partner_id,
            'account_id'=> $request->account_id,
            'dni_id'=> $request->dni_id,
            'token_id'=> $request->token_id,
            'transacion_type'=> 'REV',
            'reference_number'=> $request->reference_number,
            'transaction_id'=> $request->transactionID,
            'status'=>'PEN' ,
        ]);
       // $clientId,$accountId, $referenceNumber, $checkSum, $transactionId

        $result=   $this->pdaService->OperationAnnulment($request->client_id,$request->account_id, $request->reference_number  , $checkSum ,$request->transactionID);
        Log::debug('result SOAP: '.json_encode($result));

        if(empty($result->ErrorCode))
            $result->ErrorCode=200;
// {"ResultCode":1,"DateTime":"2021-06-14T15:31:24.1464121-05:00","Message":"N\u00famero de referencia repetida"
//,"TransactionID":"6db9eb588bd84adfae9a678a59a29283",    "ErrorCode":"9",}
// {"ResultCode":0,"DateTime":"2021-06-14T15:56:14.6880057-05:00","Message":"OK"
//,"TransactionID":"aa733185e4eb4a4bae8387e802a8f11b"}
        Transactions::where('id',$transac->id)
            ->update([ 'result_code'=> $result->ResultCode,
                    'datetime_end'=> $result->DateTime,
                'message'=>$result->Message,
                    'transaction_id'=> $result->TransactionID,
                    'status'=> $result->Message,
                    'respond'=> json_encode($result),
                    'error_code'=> $result->ErrorCode,
                   ]
            );


        $res=[
            'result_code'=>$result->ResultCode,
            'datetime'=>$result->DateTime,
            'transactionID'=>$result->TransactionID,
            'status'=>$result->Message,
        ];
        return $this->successResponse($res,Response::HTTP_OK);

    }

}
