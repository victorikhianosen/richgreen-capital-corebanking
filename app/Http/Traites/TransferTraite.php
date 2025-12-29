<?php
namespace App\Http\Traites;

use Illuminate\Support\Facades\Http;

trait TransferTraite{
    public function monnifyTranfer($url,$apikey,$sercetkey,$amount,$transref,$desc,$bankcode,$destination_account,$sacctno,$receipient_name){
        $authbasic = base64_encode($apikey.":".$sercetkey);
        $bankTransfer = Http::withHeaders([
            "Authorization" => "Basic ".$authbasic
        ])->post($url,[
            "amount" => $amount,
            "reference" => $transref,
            "narration" => $desc,
            "destinationBankCode" => $bankcode,
            "destinationAccountNumber" => $destination_account,
            "currency" => "NGN",
            "sourceAccountNumber" => $sacctno,
            "destinationAccountName" => $receipient_name
        ])->json();

        return $bankTransfer;
    }
    
     public function bankTransferviaPayout($url,$amount,$destination_account,$bankcode,$usrname,$transref,$desc){
        $bankTransferPayout = Http::withHeaders([
             "PublicKey" => env('PUBLIC_KEY'),
            "EncryptKey" => env('ENCRYPT_KEY'),
            "Content-Type" => "application/json",
            "Accept" => "application/json"
        ])->post($url,[
            "amount" => $amount,
            "destination_account" => $destination_account,
            "bank_code" => $bankcode,
            "username" => $usrname,
            "payment_reference" => $transref,
            "description" => $desc,
        ])->json();
       
         return ["status" => $bankTransferPayout['status']];
    }

    public function NibbsPayTransfer($url,$amount,$bname,$bacctno,$sname,$sacctno,$pyref,$desc,$billerid,$bnkcode,$trnid,$Neref,$setlacname,$setlacno){

        $t="";
       $response = Http::withHeaders([
            "Authorization" => "Bearer ".$t
        ])->post($url."nip/fundstransfer",[      
            "amount" => $amount,
            "beneficiaryAccountName" => $bname,
            "beneficiaryAccountNumber" => $bacctno,
            "beneficiaryBankVerificationNumber" => "",
            "beneficiaryKYCLevel" => "1",
            "channelCode" => "1",
            "originatorAccountName" => $sname,
            "originatorAccountNumber" => $sacctno,
            "originatorKYCLevel" => "1",
            "mandateReferenceNumber" => "MA-0112345678-2022315-53097",
            "paymentReference" => $pyref,
            "transactionLocation" => "1.38716,3.05117",
            "originatorNarration" => $desc,
            "beneficiaryNarration" => $desc,
            "billerId" => $billerid,
            "destinationInstitutionCode" => $bnkcode,
            "sourceInstittioncode" => "999998",
            "transactionId" => $trnid,
            "originatorBankVerificationNumber" => "",
            "nameEnquiryRef" => $Neref,
            "InitiatorAccountName" => $setlacname,
            "InitiatorAccountNumber" => $setlacno
        ]);
    }


    public function validateSettlementBalance($url,$amout,$tacctno,$tacctnme,$autocd,$intncod,$billerid,$trnxid){

           $checkbalanace = Http::withHeaders([
               "Authorization" => "Bearer "
           ])->post($url."nip/balanceenquiry",[      
            "channelCode" => "1",
            "targetAccountName" => $tacctnme,
            "targetAccountNumber" => $tacctno,
            "targetBankVerificationNumber" => "",
            "authorizationCode" => $autocd,
            "destinationInstitutionCode" => $intncod,
            "billerId" => $billerid,
            "transactionId" => $trnxid
           ])->json();
           
           $this->logInfo("validating settlement balance",$checkbalanace);
           //return $checkbalanace;
      
           if($checkbalanace["availableBalance"] < $amout){
                $response = ["status" => false, 'message' => "Switcher Error... Please contact support"];
           }else{
                $response = ["status" => true,'message' => "Amount is Valid",];
           }
           return $response;
    }
    
      public function WirelessTransfer($akey,$amount,$treference,$bankcode,$accountnum,$receipient,$desc){
        $response = Http::withHeaders([
            "ApiKey" => $akey,
            "Content-Type" => 'application/json',
            "Accept" => "application/json"
        ])->post($this->url."bank-transfer",[
            "amount" => $amount,
            "transaction_reference" => $treference,
            "bank_code" => $bankcode,
            "account_number" => $accountnum,
            "receipient_name" => $receipient,
            "description" => $desc,
        ])->json();
        
        return $response;
     }
}//end