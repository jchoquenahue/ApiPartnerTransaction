<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;
use Illuminate\Support\Facades\Log;
use SoapClient;


class PdaService
{
    use ConsumesExternalService;

    /**
     * The base uri to be used to consume the authors service
     * @var string
     */
    public $baseUri;

    /**
     * The secret to be used to consume the authors service
     * @var string
     */
    public $secret;
    /**
     * @var \Laravel\Lumen\Application|mixed
     */
    public $clientId;
    /**
     * @var \Laravel\Lumen\Application|mixed
     */
    public $cheksum;
    public $partner_name;

    public function __construct()
    {
        $this->baseUri = config('services.pda.base_uri');
        $this->secret = config('services.pda.secret');
        $this->clientId = config('services.pda.clientId');
        $this->hash = config('services.pda.hash');
        $this->timeout = config('services.pda.timeout');
        $this->cheksum = config('services.pda.chek');
        $this->partner_name = config('services.pda.partner_name');

        $options = [
            'cache_wsdl'     => WSDL_CACHE_NONE,
            'trace'          => 1,
            'stream_context' => stream_context_create(
                [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => false
                    ]
                ]
            ),
            'connection_timeout' => $this->timeout,
        ];
        $this->service = new SoapClient($this->baseUri, $options);

    }
    public function CheckAccount(
        // $clientId,
        $referenceNumber,
        // $checkSum,
        $phoneNumber, $documentNumber)
    {
        $date = date('YmdHis');
        $hash = strtoupper($this->hash).$date;
        $hash = strtoupper(hash('sha256', $hash));
        $hash = strtoupper($hash.$referenceNumber.$this->clientId.$phoneNumber.$documentNumber);
        $service = $this->service->CheckAccount([
            'checkAccountRequest' => [
                'ConsumerID' => $this->partner_name,              //KONTIGODEV01
                'ClientID' => $this->clientId,                    //IDCLIENTE009
                'ReferenceNumber' => $referenceNumber,
                'Checksum' => $this->cheksum,                    //GMONEYCERTIFICACION
                'DateTime' => date(DATE_ATOM),
                'PhoneNumber' => $phoneNumber,
                'DocumentNumber' => $documentNumber,
            ]
        ]);
        if($service->CheckAccountResult->ResultCode == 1)
            return $service->CheckAccountResult->Message;
        if($service->CheckAccountResult->ResultCode != 0)
            return false;
        return $service->CheckAccountResult;
    }

//($request->acount_id, $request->reference_number
//            , $checkSum,$request->account_id ,$request->transactionType,$request->amount);
    public function OperationB2A($clientId, $referenceNumber, $checkSum, $accountId, $transactionType,$amount)
    {
        Log::debug('OperationB2A: '.$this->partner_name.', '.$clientId.','. $referenceNumber.','. $checkSum.','. $accountId.','.
            $transactionType.','.$amount);
        $service = $this->service->OperationB2A([
            'business2AgentRequest' => [
                'ConsumerID' => $this->partner_name,
                'ClientID' => $clientId,
                'ReferenceNumber' => $referenceNumber,
                'Checksum' => $checkSum,
                'DateTime' => date(DATE_ATOM),
                'AccountID' => $accountId,
                'TransactionType' => $transactionType,
                'Amount' => $amount,
            ]
        ]);
        Log::debug("PDA REsultado: ".json_encode($service->OperationB2AResult));
        Log::debug("PDA REsultado: ".json_encode($service->OperationB2AResult->ResultCode));

        return $service->OperationB2AResult;
    }
    public function OperationAnnulment($clientId,$accountId, $referenceNumber, $checkSum, $transactionId)
    {
        Log::debug('OperationAnnulment: '.$clientId.','.$accountId.','.$referenceNumber.','. $checkSum.','.$transactionId);
        $service = $this->service->OperationAnnulment([
            'annulRequest' => [
                'ConsumerID' => $this->partner_name,
                'ClientID' => $clientId,
                'ReferenceNumber' => $referenceNumber,
                'Checksum' => $checkSum,
                'DateTime' => date(DATE_ATOM),
                'TransactionID' => $transactionId,
            ]
        ]);

        return $service->OperationAnnulmentResult;
    }




    public function AccountBalance($clientId, $referenceNumber, $checkSum, $accountId)
    {
        $service = $this->service->GetAccountBalance([
            'getAccountBalanceRequest' => [
                'ConsumerID' => $this->partner_name,
                'ClientID' => $clientId,
                'ReferenceNumber' => $referenceNumber,
                'Checksum' => $checkSum,
                'DateTime' => date(DATE_ATOM),
                'AccountID' => $accountId,
            ]
        ]);
        if($service->GetAccountBalanceResult->ResultCode != 0)
            return false;
        return $service->GetAccountBalanceResult;
    }
    public function AccountMovements($clientId, $referenceNumber, $checkSum, $accountId)
    {
        $service = $this->service->GetAccountMovements([
            'getAccountMovementsRequest' => [
                'ConsumerID' => $this->partner_name,
                'ClientID' => $clientId,
                'ReferenceNumber' => $referenceNumber,
                'Checksum' => $checkSum,
                'DateTime' => date(DATE_ATOM),
                'AccountID' => $accountId,
            ]
        ]);
        if($service->GetAccountMovementsResult->ResultCode != 0)
            return false;
        return $service->GetAccountMovementsResult;
    }

}
