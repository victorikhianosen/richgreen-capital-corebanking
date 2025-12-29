<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traites\AuditTraite;
use App\Http\Traites\SavingTraite;
use App\Http\Traites\TransferTraite;
use App\Http\Traites\UserTraite;
use App\Http\Traites\VtpassTraite;
use App\Models\Bank;
use App\Models\Charge;
use App\Models\Customer;
use App\Models\Email;
use App\Models\GeneralLedger;
use App\Models\Saving;
use Illuminate\Support\Str;
use App\Models\SavingsProduct;
use App\Models\SavingsTransaction;
use App\Models\Setting;
use App\Models\TransferGateway;
use App\Models\WhatsappLog;
use App\Models\WhatsappUssdSession;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class WhatsappController extends Controller
{
  use UserTraite;
  use SavingTraite;
  use AuditTraite;
  use TransferTraite;
  use VtpassTraite;

  public $sessionid;
  public $whtappsesslog;
  public $getsessionid;

  private $wemabaseurl;
  private $url;
  private $apikey;
  private $sercetkey;
  private $acctno;
  private $wurl;
 private $wapikey;
private $wacctno;

  public $banksearharray = ["banksearch","0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20",
                            "21","22","23","24","25","26","27","28","29","30","31","32","33","34","35","36","37","38","39","40"];

  public $network = ["airtime","mtn","9mobile","airtel","glo"];
  public $networkdata = ["data","mtn-data","etisalat-data","airtel-data","glo-data"];
  public $cableRTv = ["cable","dstv","gotv","startimes","showmax"];
  public $disco = ["disco","abuja-electric","enugu-electric","eko-electric","ikeja-electric","ibadan-electric",
                  "jos-electric","benin-electric","kano-electric","kaduna-electric","portharcourt-electric"];

 public $Acountno;
 public $savpd;
  private $vtpassurl;
  private $datalist = array();
  private $cablelist = array();
  public $sessioncharges;
  public $glsavingdacct;
  public $glcurrentacct;
  public $whtdta;


  public function __construct()
  {
    if(env('APP_MODE') == "test"){
      $this->vtpassurl = env('vtpass_test_url');

      $this->url = env('MONNIFY_SANDBOX_URL');
      $this->apikey = env('MONNIFY_SANDBOX_API_KEY');
      $this->sercetkey = env('MONNIFY_SANDBOX_SECRET_KEY');
      $this->acctno = env('MONNIFY_SANDBOX_ACCOUNT_NUMBER');
    }else{
      $this->vtpassurl = env('vtpass_live_url');

      $this->url = env('MONNIFY_LIVE_URL');
      $this->apikey = env('MONNIFY_LIVE_API_KEY');
      $this->sercetkey = env('MONNIFY_LIVE_SECRET_KEY');
      $this->acctno = env('MONNIFY_LIVE_ACCOUNT_NUMBER');

      $this->wurl = env('WIRELESS_URL');
      $this->wapikey = env('WIRELESS_API_KEY');
      $this->wacctno = env('WIRELESS_LIVE_ACCOUNT_NUMBER');
    }
  }

    public function whatsapp_index(Request $r){

        $this->logInfo("whatsapp log",$r->all());

        if ($r['type'] != 'message') {
            die;
        }

        global $branch;

      
        
        try {

            $this->sessionid = $this->setSession(); //set unique id

            $userphone = !empty($r["payload"]["sender"]["phone"]) ? $r["payload"]["sender"]["phone"] : $r["payload"]["source"];
        $text = !empty($r['payload']['payload']['text']) ? $r['payload']['payload']['text'] : null;

        $matchphone = "0".substr($userphone,3);
        $customer = Customer::select('id','first_name','last_name','email','phone','pin','enable_sms_alert','enable_email_alert','whatsapp','status','acctno')->where('phone',$matchphone)->first();
        
        if ($customer->whatsapp == 1) {//checking if customer is subcribed to whatsapp

                $this->getsessionid = $this->getSession();//get unique id
                
                $this->Acountno = $customer->acctno;
                $branch = null;
                $usern = $customer->last_name." ".$customer->first_name;
                $userid = $customer->id;
                $this->whtappsesslog = $customer->wapsessionLog;
                    
                $this->whtdta = new Setting();

                $customeracctbal = Saving::select('account_balance')->where('customer_id',$userid)->first();


                $this->glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->first();
                $this->glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->first();

                $glwhatsappincacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','40628214')->first();//whatsapp income gl
                
        
                if($this->whtdta->getsettingskey('whatsapp_channel') == "1"){
        
                      if ($customer->status != "1") { //checking customer status 
                                $msg = $this->accountStatus($customer->status);
                                $this->responsemsg($msg,$userphone,$branch);
                                die;
                            }
        
                        $compbal = $this->validatecompanybalance("200","whapp");
                        if($compbal["status"] == false){
                            $this->logInfo("validating whatsapp balance",$compbal);
                         $msg = $compbal["message"];
                            $this->responsemsg($msg,$userphone,$branch);
                            die;
                           }
        
                           $validateuserbalance = $this->validatecustomerbalance($userid,"20",$branch);
                       if($validateuserbalance["status"] == false){
                           $this->tracktrails(null,$branch,$usern,'customer',$validateuserbalance["message"],'');
                           
                           $this->logInfo("customer balance",$validateuserbalance);
                           
                           $msg = "You do not have sufficient balance to use this service";
                           $this->responsemsg($msg,$userphone,$branch);
                           die;
                       }
        
                       if(!isset($this->whtappsesslog->id) || $this->whtappsesslog->operation != "continue" 
                            || Carbon::now()->diffInSeconds($this->whtappsesslog->session_time) > 900 ||
                            $text == 'back'|| $text == '00'){

                             $msg = $this->gettimeOfDay()." ".ucwords($usern)."%2C%0D%0A\n Welcome to ".$this->whtdta->getsettingskey('company_name')."%2C%0D%0A\nMy name is ".ucwords($this->whtdta->getsettingskey('botname'))." What would you like to do today?,\n\n kindly Note: you will b charged N(".$this->whtdta->getsettingskey('whatsapp_session_charge').") for each session of service.\n\nExample: Reply with 1 to check balance.\n\n 1. Check Balance\n 2. Trnsf to ".$this->whtdta->getsettingskey('company_name')."\n 3. Trnsf to other banks\n 4. Buy Data\n 5. Buy Airtime\n 6. Buy Cable Subcriptions\n 7. Buy Electricity\n 8. Update Pin\n 9. Statement\n\n Reply 00 or back to return to Main Menu %F0%9F%8F%A0%0D%0A\n Reply cancel to end session";
                        
                             $this->whatsapppSessionLog($userid,$branch,'initiated','continue');
                    
                            $this->responsemsg($msg,$userphone,$branch);
                            die;
                            
                       }elseif($text == 'cancel'){

                            session()->forget('sessionid');
                            $this->whatsapppSessionLog($userid,$branch,'stopped','end');
        
                            $msg = "Whatsapp session cancelled\n\n See you next time ".ucwords(strtolower($usern));
                            $this->responsemsg($msg,$userphone,$branch);
                            die;

                       }else{
                       
                       if(isset($this->whtappsesslog->id) && $this->whtappsesslog->operation == "continue"){
        
                            if($this->whtappsesslog->session_status == "initiated"){
        
                                if($text == "1"){

                                    $msg = "Hi ".ucwords($usern)." your balance as at ".date('d-m-Y')."\n\n Balance: N".number_format($customeracctbal->account_balance,'2')."\n\n Reply 00 or back to return to Main Menu";
                                    $this->whatsapppSessionLog($userid,$branch,'stopped','end');
                                    
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;
                                }
        
                                if($text == "2" || $text == "3"){ //wallet transfer or bank transfer
                                    $msg = "Please enter destination account number";
                                    $this->whtappsesslog->type=$text;
                                    $this->whtappsesslog->save();
                                    $this->whatsapppSessionLog($userid,$branch,'accountno','continue');
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;
                                }
        
                                if($text == "4"){//data
                                    
                                    $msg = "Select Network\n\n\n 1. Mtn\n2. 9mobile\n3. Airtel\n4. Glo\n\n Reply 00 or back to return to Main Menu";
                                     $this->whtappsesslog->type=$text;
                                     $this->whtappsesslog->save();
                                    $this->whatsapppSessionLog($userid,$branch,'bdata','continue');
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;
                                }
        
                                if($text == "5"){//airtime
                                    $msg = "Select Network\n\n\n 1. Mtn\n2. 9mobile\n3. Airtel\n4. Glo\n\n Reply 00 or back to return to Main Menu";
                                     $this->whtappsesslog->type=$text;
                                     $this->whtappsesslog->save();
                                    $this->whatsapppSessionLog($userid,$branch,'bat','continue');
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;
                                }
        
                                if($text == "6"){//cabletv
                                    $msg = "Select Cable Type\n\n\n 1. Dstv\n2. Gotv\n3. Startimes\n4. Showmax\n\n Reply 00 or back to return to Main Menu";
                                    $this->whtappsesslog->type=$text;
                                    $this->whtappsesslog->save();
                                    $this->whatsapppSessionLog($userid,$branch,'bct','continue');
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;
                                }
        
                                if($text == "7"){//electricity
                                    $msg = "Select meter type\n\n1. prepaid\n2. postpaid";
                                    $this->whtappsesslog->type=$text;
                                    $this->whtappsesslog->save();
                                    $this->whatsapppSessionLog($userid,$branch,'belec','continue');
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;
                                }
        
                                if($text == "8"){//pin
                                    $msg = "Please enter your old pin";
                                    $this->whatsapppSessionLog($userid,$branch,'oldpin','continue');
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;
                                }
        
                                if($text == "9"){//statement
                                     if($customer->enable_email_alert){
                                        if(!is_null($customer->email)){
                                            $msg = "Enter start date and end date (Eg: YYYY/MM/DD - YYYY/MM/DD)";
                                           
                                            $this->whtappsesslog->type=$text;
                                            $this->whatsapppSessionLog($userid,$branch,'statement','continue');
                                            $this->responsemsg($msg,$userphone,$branch);
                                       
                                        }else{
                                          
                                            $msg = "No Email is linked to your account, statement can not be sent. Contact support to update\n\n Reply 00 or back to return to Main Menu";
                                            $this->whatsapppSessionLog($userid,$branch,'stopped','end');
                                            $this->responsemsg($msg,$userphone,$branch);
        
                                        }
        
                                    }else{
        
                                        $msg = "Email is not enable on your account, statement can not be sent. Contact support to update\n\n Reply 00 or back to return to Main Menu";
                                        $this->whatsapppSessionLog($userid,$branch,'stopped','end');
                                        $this->responsemsg($msg,$userphone,$branch);
        
                                     }
                                    die;
                                }
                            }
        
                            if($this->whtappsesslog->session_status == "statement"){
                              
                                $this->whtappsesslog->statement_date = $text;
                                $this->whtappsesslog->transaction_type = "statement";
                                $this->whtappsesslog->save();
                                $this->whatsapppSessionLog($userid,$branch,'pin','continue');
                                $this->responsemsg("Enter pin",$userphone,$branch);
                                die;
        
                            }
        
                            if($this->whtappsesslog->session_status == "accountno"){
                               if(strlen($text) == 10){
                                 if(is_numeric($text)){
                                        $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
        
                                    if($this->whtappsesslog->type == "2"){//validate wallet account
        
                                        $msg = "Verifying account please wait...";
                                        $this->responsemsg($msg,$userphone,$branch);

                                        $rmsg = $this->verifyAccount($userid,$text,"","wallet",$branch);
                                        $this->whtappsesslog->account_number = $text;
                                        $this->whtappsesslog->transaction_type = "trnswallet";
                                        $this->whtappsesslog->save();

                                        $this->whatsapppSessionLog($userid,$branch,'confirmAccount','continue');
        
                                        $this->responsemsg($rmsg,$userphone,$branch);
                                        die;
        
                                    }elseif($this->whtappsesslog->type == "3"){//validate other account
        
                                        $msg = "Loading corresponding bank. Please wait...";
                                        $this->responsemsg($msg,$userphone,$branch);
        
                                        $this->whtappsesslog->account_number = $text;
                                        $this->whtappsesslog->save();
                                        $this->whatsapppSessionLog($userid,$branch,'choosebank','continue');
        
                                        $smsg = $this->selectBanks("15","");
                                        $this->responsemsg($smsg,$userphone,$branch);
                                        die;
                                    }
                                 }else{
                                    $msg = "Account number must be numeric";
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;
                                 }
        
                               }else{
                                    $msg = "Account number must be 10 digits";
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;
                               }
                            }
        
        
                            if($this->whtappsesslog->session_status == "choosebank"){
                                if(is_numeric($text)){
                                if($text == "70"){

                                    $msg = "Enter bank name";
                                    //$msg = $this->selectBanks('15',$text);
                                    $this->whatsapppSessionLog($userid,$branch,'searchbank','continue');
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;
                                     
                                }else{
                                    $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
        
                                    $msg = "Verifying account please wait...";
                                    $this->responsemsg($msg,$userphone,$branch);
        
                                    $rbankcode = $this->getBanks($text);
                                    $this->whtappsesslog->bank_code = $rbankcode;
                                    $this->whtappsesslog->transaction_type = "trnsbank";
                                    $this->whtappsesslog->save();
                                    $this->whatsapppSessionLog($userid,$branch,'confirmAccount','continue');
        
                                    $brmsg = $this->verifyAccount($userid,$this->whtappsesslog->account_number,$rbankcode,"bank",$branch);
                                    $this->responsemsg($brmsg,$userphone,$branch);
                                    die;
                                }

                            }else{
                                $this->responsemsg("Invalid input",$userphone,$branch);
                              die;
                            }
                          }
                                
                             if($this->whtappsesslog->session_status == "searchbank"){
        
                                if($text == "70"){
                                    
                                    $msg = "Enter bank name";
                                   
                                    $this->whatsapppSessionLog($userid,$branch,'searchbank','continue');
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;

                                    
                                }else{

                                    $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
        
                                    $msg = "Searching for corresponding bank. Please wait...";
                                    $this->responsemsg($msg,$userphone,$branch);
        
                                    $rbanksearch = $this->searchBanks($text);
                                    // $this->whtappsesslog->bank_code = $rbankcode;
                                    // $this->whtappsesslog->transaction_type = "trnsbank";
                                    // $this->whtappsesslog->save();
                                     $this->whatsapppSessionLog($userid,$branch,'choosebank','continue');
        
                                    // $brmsg = $this->verifyAccount($userid,$text,$rbankcode,"bank");
                                   
                                        $this->responsemsg($rbanksearch,$userphone,$branch);
                                    die;
                                    
                                    
                                }
                                
                            }
        
                            if($this->whtappsesslog->session_status == "confirmAccount"){
        
                                if($text == "1"){//confirm transfer
                                    $this->whtappsesslog->type=$text;
                                    $this->whtappsesslog->save();
                                    $this->whatsapppSessionLog($userid,$branch,'amount','continue');
                                    $this->responsemsg("Enter Amount",$userphone,$branch);
                                    die;
                                    
                                }elseif($text == "2"){//cancel tranfer
                                   
                                    session()->forget('sessionid');
                                    $this->whatsapppSessionLog($userid,$branch,'stopped','end');
                  
                                    $msg = "Thank you ".ucwords($usern)." see you next time\n\n Reply 00 or back to return to Main Menu";
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;
                                }
                            }
        
                            if($this->whtappsesslog->session_status == "amount"){
                                if(is_numeric($text) && $text > 0){
                                    $this->whtappsesslog->amount = $text;
                                    $this->whtappsesslog->save();
                                    $this->whatsapppSessionLog($userid,$branch,'pin','continue');
                                    $this->responsemsg("Enter pin",$userphone,$branch);
                                    die;
                                }else{
                                    $msg = "Enter a valid amount";
                                    $this->responsemsg($msg,$userphone,$branch); 
                                    die;
                                }
                            }
        
                            
                            if($this->whtappsesslog->session_status == "oldpin"){
                                $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
        
                                if(is_numeric($text) && $text > 0){
                                    if (Hash::check($text,$customer->pin)) {
        
                                        $msg = "Enter new pin";
                                        $this->whatsapppSessionLog($userid,$branch,'newpin','continue');
                                        $this->responsemsg($msg,$userphone,$branch); 
                                        die;

                                    }else{
                                        if($customer->failed_pin < 4){
                                            $customer->failed_pin += 1;
                                            $customer->save();
                                            $this->responsemsg("valid pin",$userphone,$branch); 
                                            die;

                                          }else{

                                            $customer->status = 4;
                                            $customer->save();
                                            $this->responsemsg("Your account has been restricted due to multiple pin trials",$userphone,$branch); 
                                            die;

                                          }  
                                    }
                                }else{
                                    
                                    $this->responsemsg("valid pin format",$userphone,$branch); 
                                    die;
                                }
                                
                            }
        
                            if($this->whtappsesslog->session_status == "newpin"){
        
                                $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
        
                                if(is_numeric($text) && $text > 0){
                                    if (Hash::check($text,$customer->pin)) { 
                                        $msg = "new pin cannot be same as current pin"; 
                                    }else{
                                       $customer->pin = Hash::make($text);
                                        $customer->save();
                                        $msg = "pin updated successfully";
                                      $this->whatsapppSessionLog($userid,$branch,'stopped','end');
        
                                    }
                                }else{
                                    $msg = "valid pin format";
                                }
                                $this->responsemsg($msg,$userphone,$branch); 
                                die;
                            }
        
                            if($this->whtappsesslog->session_status == "bdata" || $this->whtappsesslog->session_status == "bat" || 
                            $this->whtappsesslog->session_status == "bct" || $this->whtappsesslog->session_status == "belec"){//airtime, data, cable and electricity
        
                                if($this->whtappsesslog->type == "5"){
        
                                    if(is_numeric($text) && $text > 0 && $text < 6){//airtime

                                        $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');

                                        $msg = "Enter phone number";
                                        $this->whtappsesslog->vtu_code = $text;
                                        $this->whtappsesslog->vtu_network = $this->network[$text];
                                        $this->whtappsesslog->save();
                                        $this->whatsapppSessionLog($userid,$branch,'phone','continue');
                                        $this->responsemsg($msg,$userphone,$branch); 
                                        die;

                                    }else{

                                        $msg = "Invalid selection";
                                        $this->responsemsg($msg,$userphone,$branch);
                                        die;
                                    }
        
                                }elseif($this->whtappsesslog->type == "4"){
                                    $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
                                    if(is_numeric($text) && $text > 0 && $text < 5){//data
                                        $netwk = $this->networkdata[$text];

                                        $msg = "Loading data bundle Please wait...";
                                        $this->responsemsg($msg,$userphone,$branch);

                                        $this->whtappsesslog->vtu_code = $text;
                                        $this->whtappsesslog->vtu_network = $netwk;
                                        $this->whtappsesslog->save();
                                        $this->whatsapppSessionLog($userid,$branch,'serviceplan','continue');

                                        $networkProvider = $this->SelectDataBundlesList($netwk,"");
                                        $dmsg = "Select Data Bundle\n\n".$networkProvider."\n\n Reply 00 or back to return to Main Menu";
                                        $this->responsemsg($dmsg,$userphone,$branch); 
                                        die;
                                    }else{

                                        $msg = "Invalid selection";
                                        $this->responsemsg($msg,$userphone,$branch);
                                        die;
        
                                    }
        
                                }elseif($this->whtappsesslog->type == "6"){//cable
                                    $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
                                    if(is_numeric($text) && $text > 0 && $text < 7){
                                        $cble = $this->cableRTv[$text];

                                        $msg = "Loading subscription plans Please wait...";
                                        $this->responsemsg($msg,$userphone,$branch);

                                        $this->whtappsesslog->vtu_code = $text;
                                        $this->whtappsesslog->vtu_network = $cble;
                                        $this->whtappsesslog->save();
                                        $this->whatsapppSessionLog($userid,$branch,'cabletvplan','continue');

                                        $cableProvider = $this->SelectCableBundlesList($cble,"10","");
                                        $cbmsg = "Select a subscription plan\n\n".$cableProvider."\n\n Reply 00 or back to return to Main Menu";
                                        $this->responsemsg($cbmsg,$userphone,$branch); 
                                        die;
                                    }else{

                                        $msg = "Invalid selection";
                                        $this->responsemsg($msg,$userphone,$branch);
                                        die;
        
                                    }

                                }elseif($this->whtappsesslog->type == "7"){//electricity
                                    $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');

                                    if(is_numeric($text) && $text > 0 && $text < 3){
                                        $msg = "Select meter service \n\n1. Abuja Electricity (AEDC)\n2. Enugu Electricity (EEDC)\n3. Eko Electricity (EKEDC)\n4. Ikeja Electricity (IKEDC)\n5. Ibadan Electricity (IBEDC)\n6. Jos Electricity (JED)\n7. Benin Electricity(BEDC)\n8. Kano Electricity (KEDCO)\n9. Kaduna Electricity (KAEDCO)\n10. Port Harcourt Electricity (PHED)\n\n Reply 00 or back to return to Main Menu";

                                        $this->whtappsesslog->vtu_code = $text;
                                        $this->whtappsesslog->save();
                                        $this->whatsapppSessionLog($userid,$branch,'discos','continue');

                                        $this->responsemsg($msg,$userphone,$branch);
                                        die;
                                    }else{
                                        $msg = "Invalid selection"; 
                                        $this->responsemsg($msg,$userphone,$branch);
                                        die;
                                    }

                                  
    
                                }else{
                                    $this->responsemsg("error",$userphone,$branch);
                                    die;
                                }
                            }

                            if($this->whtappsesslog->session_status == "discos"){
                                $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
                                if(is_numeric($text) && $text > 0 && $text < 11){
                                    $disco = $this->disco[$text];

                                    $this->whtappsesslog->vtu_network = $disco;
                                     $this->whtappsesslog->save();
                                    $this->whatsapppSessionLog($userid,$branch,'meter','continue');

                                    $this->responsemsg("Enter Meter Number",$userphone,$branch);
                                    die;
                                }else{
                                    $this->responsemsg("Invalid selection",$userphone,$branch);
                                    die;
                                }
                            }

                            if($this->whtappsesslog->session_status == "meter"){

                                if(is_numeric($text)){
                                    if(strlen($text) >= 11){

                                        $msg = "Verifying meter number please wait...";
                                        $this->responsemsg($msg,$userphone,$branch);
                                        
                                     $this->whtappsesslog->vtu_phone = $text;
                                     $this->whtappsesslog->transaction_type = "vtu";
                                     $this->whtappsesslog->save();

                                     $meter_type = $this->whtappsesslog->vtu_code == '1' ? 'prepaid' : 'postpaid';
                                    $verymetr = $this->verify_meter_number($this->whtappsesslog->vtu_network,$text,$meter_type);
                                    $this->responsemsg($verymetr,$userphone,$branch);
                                    $this->whatsapppSessionLog($userid,$branch,'amount','continue');

                                   
                                    $this->responsemsg("Enter Amount",$userphone,$branch);
                                    die;
                                    }else{
                                        $this->responsemsg("invalid meter number length\n\n Reply 00 or back to return to Main Menu",$userphone,$branch);
                                        die;
                                    }
                                }else{
                                    $this->responsemsg("only numeric value is allowed\n\n Reply 00 or back to return to Main Menu",$userphone,$branch);
                                    die;
                                }
                            }

                            if($this->whtappsesslog->session_status == "cabletvplan"){
                                if($text == "next" || $text == "prev"){
        
                                    // $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
        
                                    $msg = $networkProvider = $this->SelectCableBundlesList("","10",$text);
                                    $this->whatsapppSessionLog($userid,$branch,'cabletvplan','continue');
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;
                                    
                                }else{
                                    $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
                                    
                                   
                                    $variationcode = $this->getCableVariationcode($text);
                                    
                                    $this->whtappsesslog->variation_code = $variationcode["ccode"];
                                    $this->whtappsesslog->amount = $variationcode["camount"];
                                    $this->whtappsesslog->save();

                                    if($this->whtappsesslog->vtu_network == "showmax"){
                                        $msg = "Please enter your phone number";
                                     }else{
                                         $msg = "Please enter your smart card number";
                                     }

                                    $this->whatsapppSessionLog($userid,$branch,'smartcard','continue');
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;
                                }
                            }

                            if($this->whtappsesslog->session_status == "smartcard"){
                                $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
                                if(is_numeric($text)){
                                   if(strlen($text) == 10 || strlen($text) == 11){
                   
                                    if($this->whtappsesslog->vtu_network == 'showmax'){
                                           
                                        $this->whtappsesslog->transaction_type = "vtu";
                                       $this->whtappsesslog->vtu_phone = $text;
                                       $this->whtappsesslog->save();
                                       $this->whatsapppSessionLog($userid,$branch,'pin','continue');
                                       $this->responsemsg("Enter pin",$userphone,$branch); 
                                        die;
                                      }else{
                                          
                                           $this->responsemsg("Verifying smartcard please wait...",$userphone,$branch); 
                                           
                                          $verysm = $this->verifySmartCard($this->whtappsesslog->vtu_network,$text);
                                          $this->responsemsg($verysm,$userphone,$branch); 
                                          
                                        $this->whtappsesslog->transaction_type = "vtu";
                                       $this->whtappsesslog->vtu_phone = $text;
                                       $this->whtappsesslog->save();
                                       $this->whatsapppSessionLog($userid,$branch,'pin','continue');
                                       $this->responsemsg("Enter pin",$userphone,$branch); 
                                       die;
                                       
                                      }

                                   }else{
                                    $msg = "Invalid number type\n\n Reply 00 or back to return to Main Menu";
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;
                                  }
                                }else{
                                    $msg = "only number is allowed\n\n Reply 00 or back to return to Main Menu";
                                        $this->responsemsg($msg,$userphone,$branch);
                                        die;
        
                                }
                            }

                            if($this->whtappsesslog->session_status == "serviceplan"){
                                
                                if($text == "next" || $text == "prev"){
        
                                    // $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
        
                                    $msg = $networkProvider = $this->SelectDataBundlesList("","10",$text);
                                    $this->whatsapppSessionLog($userid,$branch,'serviceplan','continue');
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;
                                    
                                }else{
                                    $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
                                    
                                   
                                    $variationcode = $this->getdataVariationcode($text);
                                    
                                    $this->whtappsesslog->variation_code = $variationcode["vcode"];
                                    $this->whtappsesslog->amount = $variationcode["vamount"];
                                    $this->whtappsesslog->save();

                                    $this->whatsapppSessionLog($userid,$branch,'phone','continue');
                                    $this->responsemsg("Enter phone number",$userphone,$branch);
                                    die;
                                }
                                    
                            }

                            if($this->whtappsesslog->session_status == "phone"){
                                $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
                                if(is_numeric($text)){
                                    if(strlen($text) == 11){
                                        if($this->whtappsesslog->type == "4"){//data
                                           $msg = "Enter pin";
                                           $sttus = "pin";

                                           $this->whtappsesslog->transaction_type = "vtu";
                                        $this->whtappsesslog->vtu_phone = $text;
                                        $this->whtappsesslog->save();
                                        $this->whatsapppSessionLog($userid,$branch,$sttus,'continue');
                                        $this->responsemsg($msg,$userphone,$branch); 
                                            die;

                                        }elseif($this->whtappsesslog->type == "5"){//airtime
                                            $msg = "Enter amount";
                                            $sttus = "amount";

                                            $this->whtappsesslog->transaction_type = "vtu";
                                        $this->whtappsesslog->vtu_phone = $text;
                                        $this->whtappsesslog->save();
                                        $this->whatsapppSessionLog($userid,$branch,$sttus,'continue');
                                        $this->responsemsg($msg,$userphone,$branch); 
                                            die;
                                        }
                                        
                                    }else{
                                        $msg = "invalid number length\n\n Reply 00 or back to return to Main Menu";
                                        $this->responsemsg($msg,$userphone,$branch);
                                        die;
                                    }
                                }else{
                                    $msg = "Invalid number format\n\n Reply 00 or back to return to Main Menu";
                                        $this->responsemsg($msg,$userphone,$branch);
                                        die;
                                }
                            }  
        
                                
                            if($this->whtappsesslog->session_status == "pin"){

                                if(is_numeric($text) && $text > 0){

                                    if($this->whtappsesslog->transaction_type == "trnswallet"){

                                        $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
        
                                        $msg = "Please wait your request is processing...";
                                        $this->responsemsg($msg,$userphone,$branch);
                                        
                                        $wltsbnk = $this->WalletTransferAction($userid,$this->whtappsesslog->account_name,$this->whtappsesslog->account_number,$this->whtappsesslog->amount,$text,$branch);
                                    
                                         
                                        if($this->sessioncharges > 0){
                                              //companybal
                                           $this->debitcreditCompanyBalance($this->sessioncharges,"debit","whapp",$branch);

                                            $this->LogTransaction($userid,$glwhatsappincacct,$customer->account_type,$this->sessioncharges,$this->sessioncharges,$usern,"whatsapp service charge",$branch);
                                        } 

                                        $this->responsemsg($wltsbnk,$userphone,$branch);
                                        die;
        
                                    }elseif($this->whtappsesslog->transaction_type == "trnsbank"){
                                        $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');

                                        $msg = "Please wait your request is processing...";
                                        $this->responsemsg($msg,$userphone,$branch);
        
                                        $rsbnk = $this->bankTransferAction($userid,$this->whtappsesslog->account_name,$this->whtappsesslog->account_number,$this->whtappsesslog->amount,$this->whtappsesslog->bank_code,$this->whtappsesslog->bank_name,$text,$branch);
                                          
                                        
                                        if($this->sessioncharges > 0){
                                             //companybal
                                           $this->debitcreditCompanyBalance($this->sessioncharges,"debit","whapp",$branch);

                                            $this->LogTransaction($userid,$glwhatsappincacct,$customer->account_type,$this->sessioncharges,$this->sessioncharges,$usern,"whatsapp service charge",$branch);
                                        }
                                     
                                        $this->responsemsg($rsbnk,$userphone,$branch);
                                        die;
        
                                    }elseif($this->whtappsesslog->transaction_type == "vtu"){
                                        $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
        
                                         $msg = "Please wait your request is processing...";
                                        $this->responsemsg($msg,$userphone,$branch);
        
                                        if($this->whtappsesslog->type == "5"){//airtime

                                            $byairtme = $this->buyAirtime($userid,$usern,$this->whtappsesslog->amount,$this->whtappsesslog->vtu_phone,$this->whtappsesslog->vtu_network,$text,$branch);
                                             
                                           
                                            if($this->sessioncharges > 0){
                                              //companybal
                                           $this->debitcreditCompanyBalance($this->sessioncharges,"debit","whapp",$branch);

                                             $this->LogTransaction($userid,$glwhatsappincacct,$customer->account_type,$this->sessioncharges,$this->sessioncharges,$usern,"whatsapp service charge",$branch);
                                         }

                                            $this->responsemsg($byairtme,$userphone,$branch);
                                           die; 

                                        }elseif($this->whtappsesslog->type == "4"){//data

                                           $datrep = $this->buyDataBundle($userid,$usern,$this->whtappsesslog->amount,$this->whtappsesslog->vtu_phone,$this->whtappsesslog->vtu_network,$this->whtappsesslog->variation_code,$text,$branch);
                                         
                                           if($this->sessioncharges > 0){
                                          //companybal
                                           $this->debitcreditCompanyBalance($this->sessioncharges,"debit","whapp",$branch);

                                         $this->LogTransaction($userid,$glwhatsappincacct,$customer->account_type,$this->sessioncharges,$this->sessioncharges,$usern,"whatsapp service charge",$branch);
                                     }

                                           $this->responsemsg($datrep,$userphone,$branch);
                                           die;

                                        }elseif($this->whtappsesslog->type == "6"){//cable

                                            if($this->whtappsesslog->vtu_network == "showmax"){

                                              $shwmax = $this->pay_cable_tv($userid,$usern,$this->whtappsesslog->vtu_network,"",$this->whtappsesslog->amount,$this->whtappsesslog->vtu_phone,$this->whtappsesslog->variation_code,$text,$branch);
                                          
                                              if($this->sessioncharges > 0){
                                               //companybal
                                           $this->debitcreditCompanyBalance($this->sessioncharges,"debit","whapp",$branch);

                                             $this->LogTransaction($userid,$glwhatsappincacct,$customer->account_type,$this->sessioncharges,$this->sessioncharges,$usern,"whatsapp service charge",$branch);
                                         }

                                              $this->responsemsg($shwmax,$userphone,$branch);
                                                die;

                                            }else{

                                                $calbtv = $this->pay_cable_tv($userid,$usern,$this->whtappsesslog->vtu_network,$this->whtappsesslog->vtu_phone,$this->whtappsesslog->amount,$matchphone,$this->whtappsesslog->variation_code,$text,$branch);
                                                
                                                if($this->sessioncharges > 0){
                                                   //companybal
                                           $this->debitcreditCompanyBalance($this->sessioncharges,"debit","whapp",$branch);

                                                 $this->LogTransaction($userid,$glwhatsappincacct,$customer->account_type,$this->sessioncharges,$this->sessioncharges,$usern,"whatsapp service charge",$branch);
                                             }

                                                $this->responsemsg($calbtv,$userphone,$branch);
                                                die;

                                            }

                                        }elseif($this->whtappsesslog->type == "7"){//electricity
                                          
                                            $meter_type =   $this->whtappsesslog->vtu_code == '1' ? 'prepaid' : 'postpaid';
                                            $elec = $this->pay_electricity($userid,$usern,$this->whtappsesslog->vtu_phone,$this->whtappsesslog->amount,$matchphone,$this->whtappsesslog->vtu_network,$meter_type,$text,$branch);
                                           
                                            if($this->sessioncharges > 0){
                                               //companybal
                                           $this->debitcreditCompanyBalance($this->sessioncharges,"debit","whapp",$branch);

                                             $this->LogTransaction($userid,$glwhatsappincacct,$customer->account_type,$this->sessioncharges,$this->sessioncharges,$usern,"whatsapp service charge",$branch);
                                         }

                                            $this->responsemsg($elec,$userphone,$branch);
                                            die;

                                        }
        
                                    }elseif($this->whtappsesslog->transaction_type == "statement"){
                                        
                                        $msg = "Please wait your request is processing...";
                                        $this->responsemsg($msg,$userphone,$branch);
        
                                        $statementdate = explode(" - ",$this->whtappsesslog->statement_date);
                                        $startdate = date("Y-m-d",strtotime($statementdate[0]));
                                        $enddate =  date("Y-m-d",strtotime($statementdate[1]));
        
                                        $setementrespnse = $this->generateSendStatement($userid,$startdate,$enddate,$text,$branch);
        
                                        $this->sessioncharges += $this->whtdta->getsettingskey('whatsapp_session_charge');
        
                                        if($this->sessioncharges > 0){
                                               //companybal
                                           $this->debitcreditCompanyBalance($this->sessioncharges,"debit","whapp",$branch);
                    
                                            $this->LogTransaction($userid,$glwhatsappincacct,$customer->account_type,$this->sessioncharges,$this->sessioncharges,$usern,"whatsapp service charge",$branch);
                                        }

                                        $msg = $setementrespnse;
                                        $this->responsemsg($msg,$userphone,$branch);
                                        die;
                                    }

                                }else{
                                    $msg = "Invalid transaction pin format";
                                    $this->responsemsg($msg,$userphone,$branch);
                                    die;
                                }
                            }
        
                        
                       }//end continue block
                    }
                    
                }else{
                    $msg = "Hi ".$usern." ".$this->whtdta->getsettingskey('company_name')." is not subscribed to this service at the moment";
                    $this->responsemsg($msg,$userphone,$branch);
                    die;
                }

            }else{
                $msg = "Your account not registered to use these service. Please contact support";
                $this->responsemsg($msg,$userphone,$branch);
                die;
            }

        } catch (\Throwable $th) {
            //throw $th;
            $this->logInfo("whatsapp error",throw $th);
            $this->responsemsg("something went wrong\n\n Reply 00 or back to return to Main Menu", $userphone,$branch);
        }
    }//end index block 

    public function getCableVariationcode($cod){

        $this->logInfo("verico",$cod);
        $verarry = $this->banksearharray[$cod];
       $vrdc ="";
       foreach($this->whtappsesslog->cablelist as $key => $data){
           if($key == $verarry){
               $vrdc .= $data[$cod];
           }
           
       }
       
       $this->logInfo("vericode",$vrdc);
       $selectedCableBundle=[];
       
       $getcable = $this->getcableBundlesList($this->whtappsesslog->vtu_network);
       foreach($getcable as $key => $value){
           
           if ($value['variation_code'] == $vrdc) {
               $selectedCableBundle = $value;
               $isDataPlanValid = true;
           }
       }
       

       return ["ccode" => $vrdc, "camount" => $selectedCableBundle['variation_amount']];

     }

    public function getdataVariationcode($cod){

        $this->logInfo("verico",$cod);
         $verarry = $this->banksearharray[$cod];
        $vrdc ="";
        foreach($this->whtappsesslog->datalist as $key => $data){
            if($key == $verarry){
                $vrdc .= $data[$cod];
            }
            
        }
        
        $this->logInfo("vericode",$vrdc);

        $selectedDataBundle=[];
        
        $getbundles = $this->getDataBundlesList($this->whtappsesslog->vtu_network);
        foreach($getbundles['data'] as $key => $value){
            
            if ($value['variation_code'] == $vrdc) {
                $selectedDataBundle = $value;
                $isDataPlanValid = true;
            }
        }

       return ["vcode" => $vrdc, "vamount" =>  $selectedDataBundle['variation_amount']];

    }

    public function SelectCableBundlesList($cble,$num,$tpy){
        $msg ="";

        $getcable = $this->getcableBundlesList($cble);
          $this->logInfo("cable code",$getcable);
          
          $i=0;
             $j=0;
          foreach($getcable as $key => $value){
               $s = $i+1;
              $this->cablelist[] = [$s => $value['variation_code'], 'name' => $value['name']];
              $i++;
          }
          
           $this->whtappsesslog->cablelist = $this->cablelist;
              $this->whtappsesslog->save();
              
                 $this->logInfo("cable code key",$this->whtappsesslog->cablelist);
                
                  foreach($this->whtappsesslog->cablelist as $data){
                      $k = $j+1;
                      $msg .= $k.". ".$data['name']."\n";
                  $j++;
                  }
          
  
          return $msg;
    }

    public function SelectDataBundlesList($netwk,$tpy){

        $msg ="";
        $getbundles = $this->getDataBundlesList($netwk);
        $this->logInfo("data code",$getbundles);
        
        $i=0;
           $j=0;
        foreach($getbundles['data'] as $key => $value){
             $s = $i+1;
            $this->datalist[] = [$s => $value['variation_code'], 'name' => $value['name']];
            $i++;
        }
        
         $this->whtappsesslog->datalist = $this->datalist;
            $this->whtappsesslog->save();
            
               $this->logInfo("data code key",$this->whtappsesslog->datalist);
              
                foreach($this->whtappsesslog->datalist as $data){
                    $k = $j+1;
                    $msg .= $k.". ".$data['name']."\n";
                $j++;
                }
        return $msg;
    }

    public function getcableBundlesList($service_type)
    {
        $endpoint = $this->vtpassurl."service-variations?serviceID=".$service_type;

        $response = $this->vtpassgeturl($endpoint);

        if($response['response_description'] == "000"){
           $msg = $response['content']['varations'];
        }else{
           $msg = 'failed to fetch subcriptions';
        }
        return $msg;
    }

    public function getDataBundlesList($networkProvider)
    {
        $endpoint = $this->vtpassurl."service-variations?serviceID=".$networkProvider;

        $response = $this->vtpassgeturl($endpoint);

        if ($response['response_description'] == "000") {
            return ['status' => true, 'message' => 'Data fetched', 'data' => $response['content']['varations']];
      } else {
          
          return ['status' => false, 'message' => 'Failed to fetch Data bundles'];
      }
        
    }

    public function selectBanks($num,$tpy){
     
        $msg ="";
        $key=[];
       
               if($tpy == ""){
                   
                   $bnks = Bank::select('id','bank_name','bank_code')->inRandomOrder()->take($num)->get();
                   
                   $msg .= "Please select a bank\n\n";
                $i=0;
               foreach($bnks as $bnk){ 
                   $s = $i+1;
                    $msg .= $s.". ".$bnk->bank_name."\n";
                    //array_push($this->bankarry,$bnk->bank_code);
                  $key[] = $bnk->bank_code;
                    $i++;
               }
                   $this->whtappsesslog->bankcode = $key;
                  $this->whtappsesslog->save();
   
               }else{
                   
                       $bnks = Bank::select('id','bank_name','bank_code')->inRandomOrder()->take($num)->get();
                $i=0;
               foreach($bnks as $bnk){ 
                   $s = $i+1;
                    $msg .= $s.". ".$bnk->bank_name."\n";
                      //array_push($this->bankarry,$bnk->bank_code);
                  $key[] = $bnk->bank_code;
                    $i++;
               }
                   $this->whtappsesslog->bankcode = $key;
                  $this->whtappsesslog->save();
   
               }
           
   
           $msg .= "\n 70. Search for bank name \n\n Reply 00 or back to return to Main Menu";
   
         return $msg;
       }

    public function getBanks($bnk){
        $bnkarry = $this->banksearharray[$bnk];
        //$bnk == '1' ? 0 : ($bnk == '15' ? 14 : $bnk);
        
        $this->logInfo("db arry code",$this->whtappsesslog->bankcode);
        
     $bnkcod ="";
       foreach($this->whtappsesslog->bankcode as $key => $value){
             if($key == $bnkarry){
                $bnkcod .= $value;
             }
              
         }
         
         $bnks = Bank::select('bank_code')->where('bank_code',$bnkcod)->first();
        return $bnks->bank_code;
        
    }
    
    public function searchBanks($sbnk){
        $msg = "";
       $key=[];
        $bnkks = Bank::select('bank_code','bank_name')->where('bank_name','like','%'.$sbnk.'%')->take(15)->get();
        
         $msg .= "Please select a bank\n\n";
          $i=0;
          
            foreach($bnkks as $bnk){ 
                $s = $i+1;
                 $msg .= $s.". ".$bnk->bank_name."\n";
                 //array_push($this->bankarry,$bnk->bank_code);
                 $key[] = $bnk->bank_code;
                 $i++;
            }
             $this->whtappsesslog->bankcode = $key;
               $this->whtappsesslog->save();
            
             $msg .= "\n 70. Search for bank name \n\n Reply 00 or back to return to Main Menu";
            return $msg;
    }

    public function gettimeOfDay()
    {
        /* This sets the $time variable to the current hour in the 24 hour clock format */
        $timestamp = time(); // now + 1 hour

        $time = date("H", $timestamp);
        /* Set the $timezone variable to become the current timezone */
        $timezone = date("e");
        /* If the time is less than 1200 hours, show good morning */
        if ($time < "12") {
            return "Good Morning";

        } elseif($time >= "12" && $time < "17") {/* If the time is grater than or equal to 1200 hours, but less than 1700 hours, so good afternoon */

            return "Good Afternoon"; 
            
        }else {
                return "Good Evening";
            }
    }

    public function accountStatus($status){
        $message = "";
        if ($status == "6"){
           $message = 'Your account has been blocked. Please contact support';
          }elseif($status == "5"){
             $message = 'Your account has blocked due to fraudulent attack. Please contact support';
          }elseif($status == "4"){
              $message = 'Your account has been restricted. Please contact support';
          }elseif($status == "2"){
              $message = 'Your account has been closed. Please contact support';
          }elseif($status == "8"){
              $message = 'Your account is Dormant or Inactive. Please contact support';
          }elseif($status == "7"){
              $message = 'Your account is currently being reviewed and will be approved soon';
          }elseif($status == "9"){
            $message = 'These account does not exist';
        }

        return $message;
    }

    public function LogTransaction($id,$glwhatsappincacct,$account_type,$amount,$glamount,$usern,$decs,$branch){
        $trnxid =  $this->generatetrnxref('wa');

        $debitCustomer = $this->DebitCustomerandcompanyGlAcct($id,$amount,$glamount,'40405204','whtaps',$decs,"whatsapp",$usern,$branch);

        $this->create_saving_transaction(null, $id,$branch,$amount,'debit','whatsapp','0',null,null,null,null,
                    $trnxid,$decs,'approved','29','trnsfer','');

        if($glwhatsappincacct->status == '1'){
            $this->gltransaction('withdrawal',$glwhatsappincacct,$amount,null);
        $this->create_saving_transaction_gl(null,$this->glsavingdacct->id,$branch, $amount,'credit',"whatsapp", $trnxid,$this->generatetrnxref('D'),'whatsapp income','approved',$usern,'');
            }

        if($account_type == '1'){//saving acct GL
         
         if($this->glsavingdacct->status == '1'){
         $this->gltransaction('deposit',$this->glsavingdacct,$amount,null);
     $this->create_saving_transaction_gl(null,$this->glsavingdacct->id,$branch, $amount,'debit',"whatsapp", $trnxid,$this->generatetrnxref('D'),'whatsapp customer debited','approved',$usern,'');
         }
         
     }elseif($account_type == '2'){//current acct GL
     
     if($this->glcurrentacct->status == '1'){
         $this->gltransaction('deposit',$this->glcurrentacct,$amount,null);
     $this->create_saving_transaction_gl(null,$this->glcurrentacct->id,$branch, $amount,'debit',"whatsapp", $trnxid,$this->generatetrnxref('D'),'whatsapp customer debited','approved',$usern);
         }
         
     }
    }
    
    public function getwhatsapppSession($whtsessionid){
        $sessn = WhatsappUssdSession::where('sessionid',$whtsessionid)->first();
        return  $sessn;
    }

    public function whatsapppSessionLog($custid,$branch,$stsus,$operation){

    if(!isset($this->whtappsesslog->id)){

        $this->whtappsesslog = new WhatsappUssdSession();
        $this->whtappsesslog->customer_id = $custid;
        $this->whtappsesslog->branch_id = $branch;
        $this->whtappsesslog->sessionid = $this->getsessionid;
        
        }

        $this->whtappsesslog->customer_id = $custid;
        $this->whtappsesslog->branch_id = $branch;
        $this->whtappsesslog->session_time = Carbon::now();
        $this->whtappsesslog->sessionid = $this->getsessionid;
        $this->whtappsesslog->session_status = $stsus;
        $this->whtappsesslog->operation = $operation;
        $this->whtappsesslog->channel_type = "whatsapp";
        $this->whtappsesslog->save();

        return $this->whtappsesslog;
    }

    public function setSession(){
        if(session()->missing('sessionid')){
            $sessiontime = time()."".mt_rand('1111','9999');

        Session::put('sessionid',[
            'sid' => $sessiontime
        ]);

        return $sessiontime;
        }

    }

    public function getSession(){
        $this->getsessionid = session()->has('sessionid') ? session()->get('sessionid')['sid'] : null;
        return $this->getsessionid;
    }


    public function verifyAccount($id,$account_number,$bankcode,$typ,$branch){
      
        
        if(is_numeric($account_number)){

            if($this->Acountno == $account_number){
                
                return "Cannot Transfer to Self\n\n Reply 00 or back to return to Main Menu";
            }
            
            if($typ == "wallet"){
        

                $this->logInfo("verifying wallet account via whatsapp",$account_number);
    
                $cust = Customer::where('acctno',$account_number)->first();
    
                if($cust){
               
    
                    $this->logInfo("verified via whatsapp","");
                    
                    $this->whtappsesslog->account_name = $cust->last_name." ".$cust->first_name;
                    $this->whtappsesslog->save();

                return "You are about to transfer to \n".ucwords($cust->last_name." ".$cust->first_name)."\n\n 1. confirm \n2. cancel\n\n Reply 00 or back to return to Main Menu";
    
                    
                }else{
                    return "Failed to verify Wallet account\n\n Reply 00 or back to return to Main Menu";
                }
    
            }elseif($typ == "bank"){

                $this->logInfo("verifying wallet account via whatsapp",$account_number);

                $verifycheck = $this->AccountLookUpSwitchApi($this->whtdta->getsettingskey('payoption'),$account_number,$bankcode);
                  
                if($verifycheck['status'] == false){
                    
                        return $verifycheck['message'];
                        
                }else{
    
                  $this->logInfo("Bank Account verified", $verifycheck);
                    
                   $this->whtappsesslog->account_name = $verifycheck['data']["accountName"]; 
                   $this->whtappsesslog->save();
    
                   return "Account Verified Successfully \n\n You are about to transfer to ".$verifycheck['data']["accountName"]."\n\n 1. confirm \n 2. cancel";
                 
                }
    
            }
        }else{
            return "Only number is allowed \n\n Reply 00 or back to return to Main Menu";
        }
        
    }

    public function AccountLookUpSwitchApi($payoption,$account_number,$bank_code){

        if($payoption == "1"){
            
            //$assetMatrixController = new AssetMatrixController();

            $bankAccount = ""; //$assetMatrixController->verifyBankAccount($bank_code, $account_number);
    
            $this->logInfo("Asset matrix response",$bankAccount);
    
            if ($bankAccount['status']) {
             
                return [
                        'status' => true,
                        'message' => "Account Verified Successfully",
                        'data' => [
                            "accountName" => $bankAccount["data"]["account_name"],
                            "bankCode" => $bank_code,
                        ]
                    ];
                
            } else {
    
                return ['status' => false, 'message' => "Failed to verify bank account",'code' => '14'];
            }

        }elseif($payoption == "2"){//monify

            $response =  Http::get($this->url."v1/disbursements/account/validate?accountNumber=".$account_number."&bankCode=".$bank_code);

            $this->logInfo('monnify response',$response);
            
            if($response["responseCode"] == '0'){
                $accountName = explode(" ",$response["responseBody"]["accountName"]);
                
                $firstName = count($accountName) < 3 ? $accountName[1] : $accountName[2]." ".$accountName[1];
                $lastName =  $accountName[0];
                    
                return [
                    'status' => true,
                    'message' => 'Bank Account Verified Successfully',
                    'data' => [
                             "accountName" => $firstName." ".$lastName,
                             "bankCode" => $bank_code,
                        ] 
                ];
                
            }else{
                return ['status' => false,'message' => 'Failed to Verify Bank Account'];
            }

        }elseif($payoption == "3"){ //nibsspay

             //$assetMatrixController = new AssetMatrixController();

        $bankAccount ="";// $assetMatrixController->verifyBankAccount($bank_code, $account_number);

        $this->logInfo("nibss response",$bankAccount);

        if ($bankAccount['status']) {
         
            return [
                    'status' => true,
                    'message' => "Account Verified Successfully",
                    'data' => [
                        "accountName" => $bankAccount["data"]["account_name"],
                        "bankCode" => $bank_code,
                    ]
                ];

        } else {

            return ['status' => false, 'message' => "Failed to verify bank account",'code' => '14'];
        }

        }elseif($payoption == "4"){//wireless

            $response =  Http::withHeaders([
                "ApiKey" => $this->wapikey
            ])->post($this->wurl."verify-bank-account",[
                "account_number" => $account_number,
                "bank_code" => $bank_code,
                "show_bvn" => false
            ]);

            $this->logInfo("Bank Account verified", $response);

            if($response["status"] == '00'){
                $accountName = explode(" ",$response["data"]["account_name"]);
   
         $firstName = count($accountName) < 3 ? $accountName[1] : $accountName[2]." ".$accountName[1];
        $lastName =  $accountName[0];
       
           return [
            'status' => true,
           'message' => 'Bank Account Verified Successfully',
           'data' => [
                    "accountName" => $firstName." ".$lastName,
                    "bankCode" => $bank_code,
                 ] 
            ];
       }else{
           return ['status' => false,'message' => 'Failed to Verify Bank Account'];
       }

        }elseif($payoption == "5"){//wema

            

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$this->token
            ])->post($this->wemabaseurl."core/wema/account/query",[
            "accountNumber" => $account_number,
            "beneficiaryBank" => $bank_code
        ]);

                $this->logInfo($response,'wema verify account response');
                
                if($response["status"] == '00'){
                
                     $vdata = [
                        "accountName" => $response["data"]["name"],
                        "bankCode" => $bank_code,
                     ];
    
                    return ['status' => $response["status"],'message' => 'Bank Account Verified Successfully','data' => $vdata];
    
                }else{
    
                    return ['status' => false,'message' => 'Failed to Verify Bank Account','data' => []];
                }
               
        }

    }

    public function WalletTransferAction($userid,$receipient_name,$desaccount,$amount,$transaction_pin,$branch){//wallet to wallet

        global $message;

        $trnxid =  $this->generatetrnxref('wa');

        $description = "wallet transfer";
        
        $user = Customer::select("id","acctno","first_name","last_name","enable_email_alert","email","enable_sms_alert")->where('id',$userid)->first();

        $usern = $user->first_name." ".$user->last_name;

         if($this->Acountno == $desaccount){
            return "Cannot transfer to self\n\n Reply 00 or back to return to Main Menu";               
            }
            
            $compbal = $this->validatecompanybalance($amount,'combal');
            if($compbal["status"] == false){
        
                $this->logInfo("validating company balance",$compbal);
            
                return $compbal["message"]."\n\n Reply 00 or back to return to Main Menu";
               
        }
    
                $chkcres = $this->checkCustomerRestriction($userid,$branch);
                    if($chkcres == true){
                
                        $this->tracktrails(null,$branch,$usern,'customer','Account Restricted','');
                        
                         $this->logInfo("","Customer Account Restricted");
                        
                         return "Your Account Has Been Restricted. Please contact support.\n\n Reply 00 or back to return to Main Menu";
                         
                    }

                    $chklien = $this->checkCustomerLienStatus($userid,$branch);
                        if($chklien['status'] == true && $chklien['lien'] == 2){
                            $this->tracktrails(null,$branch,$usern,'customer','Account has been lien','');
                            
                             $this->logInfo("Account lien",$chklien);
                            
                       return "Your Account Has Been Lien(".$chklien['messages'].")...please contact support\n\n Reply 00 or back to return to Main Menu";
                      
                    }
                        
                        $validateuserpin = $this->validatetrnxpin($transaction_pin,$userid,$branch);
                        if($validateuserpin["status"] == false){
                
                            $this->tracktrails(null,$branch,$usern,'customer',$validateuserpin["message"],'');
                            
                             $this->logInfo("Customer pin validation",$validateuserpin);
                             
                              return $validateuserpin["message"]."\n\n Reply 00 or back to return to Main Menu";
                           
                        }

                        
                    $validateuserbalance = $this->validatecustomerbalance($userid,$amount,$branch);
                    if($validateuserbalance["status"] == false){
            
                        $this->tracktrails(null,$branch,$usern,'customer',$validateuserbalance["message"],'');
                        
                         $this->logInfo("customer balance",$validateuserbalance);
                        
                      return $validateuserbalance["message"]."\n\n Reply 00 or back to return to Main Menu";
                  
                    }
            
                    $validateTransferAmount = $this->validateTransfer($amount,$this->whtdta->getsettingskey('online_transfer'),$userid,$branch);
            
                    if ($validateTransferAmount['status'] == false) {
                        
                         $this->logInfo("online transfer",$validateTransferAmount);
                        
                         return $validateTransferAmount["message"]."\n\n Reply 00 or back to return to Main Menu";
                       
                    }

            $updtdescription = $description."/".$receipient_name."/".$desaccount;
            
             
            $debitCustomer = $this->DebitCustomerandcompanyGlAcct($userid,$amount,$amount,'10733842','w',$updtdescription,"whatsapp",$usern,$branch);
                
            $this->create_saving_transaction(null, $userid,$branch,$amount,'debit','whatsapp','0',null,null,null,null,
            $trnxid,$updtdescription,'approved','2','trnsfer','');
 
             $this->logInfo("debit customer response",$debitCustomer);
            
            if ($debitCustomer["status"]==true) {
             
                //companybal
                $this->debitcreditCompanyBalance($this->whtappsesslog->amount,"debit","combal",$branch);
                    
            // $bank = banks::where('bank_code', $request->bank_code)->first();
           
            $this->updateTransactionAndAddTrnxcharges(null, $userid,$branch,0,'debit',"whatsapp",'0',null,null,null,$trnxid,
            $updtdescription,"charges",'approved','10',$usern,'');
    

            $msg =  "Debit Amt: N".number_format($amount,2)."<br> Desc: ".$updtdescription." <br>Avail Bal: N". number_format($debitCustomer["balance"],2)."<br> Date: ".date("Y-m-d h:ia")."<br>Ref: ".$trnxid;
            
            $smsmsg = "Debit Amt: N".number_format($amount,2)."\n Desc: ".$updtdescription." \n Avail Bal: N".number_format($debitCustomer["balance"],2)."\n Date: ".date("Y-m-d h:ia")."\n Ref: ".$trnxid;
                         
               if($user->enable_sms_alert){
               $this->sendSms($user->phone,$smsmsg,$this->whtdta->getsettingskey('active_sms'),$branch);//send sms
               }
            
            if($user->enable_email_alert){  
         Email::create([
            'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'subject' => ucwords($this->whtdta->getsettingskey('company_name')).' Credit Alert',
                'message' => $msg,
                'recipient' => $user->email,
            ]);

            Mail::send(['html' => 'mails.sendmail'],[
                'msg' => $msg,
                'type' => 'Debit Transaction',
            ],function($mail)use($user){
                $mail->from($this->whtdta->getsettingskey('company_email'),ucwords($this->whtdta->getsettingskey('company_name')));
                 $mail->to($user->email);
                $mail->subject(ucwords($this->whtdta->getsettingskey('company_name')).' Credit Alert');
            });
            }
            
             $this->credit_account_transfer($usern,$amount,$desaccount,$user->acctno,'10733842',$trnxid,$branch);
            
             return "Wallet Transfer Successful \n\nNote: Please delete pin after use.\n Reply 00 or back to return to Main Menu";
        
       
            } else {
                
                //  $this->updateTransactionAndAddTrnxcharges(null, $userid,$branch,0,'debit',"whatsapp",'0',null,null,null,$trnxid,
                //          $updtdescription,"failed Transaction",'failed','10',$usern,'');
                         
                //   $customeracctbal = Saving::where('customer_id',$userid)->first();
                //   $customeracctbal->account_balance += $amount;
                //   $customeracctbal->save();
                  
                //   $this->create_saving_transaction(null,$userid,$branch,$amount,
                //  'credit',"whatsapp",'0',null,null,null,null, $trnxid,'debit reversal','approved','4','trnsfer',$usern);
            
                
 
                return "Wallet Transfer failed \n\nNote: Please delete pin after use.\n Reply 00 or back to return to Main Menu";

            }
        
    //  }else {
    //       return response()->json(["status" => false, 'message' => "Invalid Transaction Reference,Please Reinitiate Transaction"], 400);
    //      }
    }

    public function credit_account_transfer($custname,$amount,$racctno,$acctno,$glacct,$trx,$branch){

        $cust = Customer::where('acctno',$racctno)->first();
        $customeracct2 = Saving::where('customer_id', $cust->id)->first();
        
         $usern = $custname;

         $desc =  "You recieve payment from ".$usern." - ".$acctno;

        $getsetvalue = new Setting();
       
         $customeracct2->account_balance += $amount;
           $customeracct2->save();
        

        $this->create_saving_transaction($cust->id,$cust->id,$branch,$amount,
        'credit',"whatsapp",'0',null,null,null,null,$trx,$desc,'approved','1','trnsfer',$usern);

        
        $glacctc = GeneralLedger::select('id','status','account_balance')->where('gl_code',$glacct)->first();
        
        $this->tracktrails($cust->id,$branch,$usern,'wallet account transfer','deposited to an account','');

if($glacctc->status == '1'){
        $this->gltransaction('withdrawal',$glacctc,$amount,null);

        $this->create_saving_transaction_gl($cust->id,$glacctc->id,$branch,$amount,'debit',"whatsapp",null,$this->generatetrnxref('w'),$desc,'approved',$usern,'');
}

    $this->checkOutstandingCustomerLoan($cust->id,$amount,$branch);//check if customer has an outstanding loan


         $msg =  "Credit Amt: N".number_format($amount,2)."<br> Desc: ".$desc." <br>Avail Bal: N". number_format($customeracct2->account_balance,2)."<br> Date: ".date("Y-m-d h:ia")."<br>Ref: ".$trx;
         
         $smsmsg = "Credit Amt: N".number_format($amount,2)."\n Desc: ".$desc." \n Avail Bal: N".number_format($customeracct2->account_balance,2)."\n Date: ".date("Y-m-d h:ia")."\n Ref: ".$trx;
                         
               if($cust->enable_sms_alert){  
               $this->sendSms($cust->phone,$smsmsg,$getsetvalue->active_sms,$branch);//send sms
               }
         
         if($cust->enable_email_alert){
         Email::create([
            'uuid' => Str::uuid(),
                'user_id' => $cust->id,
                'subject' => ucwords($getsetvalue->company_name).' Credit Alert',
                'message' => $msg,
                'recipient' => $cust->email,
            ]);

            Mail::send(['html' => 'mails.sendmail'],[
                'msg' => $msg,
                'type' => 'Credit Transaction',
            ],function($mail)use($getsetvalue,$cust){
                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                 $mail->to($cust->email);
                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
            });
    }
    
    }

    public function bankTransferAction($userid,$raccountname,$raccountno,$amount,$bnkcode,$bnkname,$tnxpin,$branch){//bank transfer

        $trxref = $this->generatetrnxref('wa');

        global $message;

        
        // $setdata = TransferGateway::where('gateway_option',$getsetvalue->payoption)
        //                             ->where('status','1')->first();

// if($setdata){

    if($this->Acountno == $raccountno){
        return "Cannot transfer to self\n\n Reply 00 or back to return to Main Menu";               
        }

        $cust = Customer::where('id',$userid)->first();

        $usern = $cust->last_name." ".$cust->first_name;

        $desc =  "transfer from ".$usern;
      
        $tcharge = Charge::select('amount')->where('id',$this->whtdta->getsettingskey('transfer_charge'))->first();
        $ocharge = Charge::select('amount')->where('id',$this->whtdta->getsettingskey('othercharges'))->first();
        
        
        if($this->whtdta->getsettingskey('payoption') == "1"){//assetmatrix

            $bankcharger = $this->whtdta->getsettingskey('bankcharge');
            $charge = $tcharge->amount + $ocharge->amount;
            $totalAmount = $amount + $charge;
            $tchargeamt = $tcharge->amount + $bankcharger;

        }elseif($this->whtdta->getsettingskey('payoption') == "2"){//monnify

            $monnifycharge = $this->whtdta->getsettingskey('monnifycharge');

            $totalAmount = $amount + $tcharge->amount + $monnifycharge + $ocharge->amount;
            $monify = $amount + $monnifycharge;
            $tchargeamt = $tcharge->amount + $monnifycharge + $ocharge->amount;

              //verify monnify account balance
          $monfybal = $this->validateMonnifyBalance($this->acctno,$monify);
          //return $monfybal;
          $this->logInfo("monnify balance",$monfybal);
          
          if ($monfybal["status"] == false) {
             $message = $monfybal['message'];
           }

        }elseif($this->whtdta->getsettingskey('payoption') == "3"){//nibss pay

            $bankcharger = $this->whtdta->getsettingskey('monnifycharge');
            $charge = $tcharge->amount + $ocharge->amount;
        $totalAmount = $amount + $charge;
        $tchargeamt = $tcharge->amount + $bankcharger;

        }elseif($this->whtdta->getsettingskey('payoption') == "4"){//wireless

            $wirelesscharge = 15;
            $totalAmount = $amount + $tcharge->amount + $wirelesscharge + $ocharge->amount - 5;
            $wireless = $amount + $wirelesscharge;
        
            $tchargeamt = $tcharge->amount + $ocharge->amount + $wirelesscharge;

              //verify wireless account balance
          $wirelessbal = $this->validateWirelessBalance($this->wurl,$wireless,$this->wapikey);
          //return $monfybal;
          $this->logInfo("wireless balance",$wirelessbal);
          
          if ($wirelessbal["status"] == false) {
             return $wirelessbal['message']."\n\n Reply 00 or back to return to Main Menu";
           }

        }
        

        $compbal = $this->validatecompanybalance($totalAmount,'combal');
        if($compbal["status"] == false){
    
            $this->logInfo("validating company balance",$compbal);
        
            return $compbal['message']."\n\n Reply 00 or back to return to Main Menu";
    }

    $chkcres = $this->checkCustomerRestriction($userid,$branch);
    if($chkcres == true){

        $this->tracktrails($userid,$branch,$usern,'customer','Account Restricted','');
        
        $this->logInfo("","Customer Account Restricted");
        
        return 'Your Account Has Been Restricted. Please contact \n\n Reply 00 or back to return to Main Menu';
    }

    $chklien = $this->checkCustomerLienStatus($userid,$branch);
        if($chklien['status'] == true && $chklien['lien'] == 2){
            
            $this->tracktrails($userid,$branch,$usern,'customer','Account has been lien','');
            
            $this->logInfo("Account lien",$chklien);
            
           return 'Your Account Has Been Lien('.$chklien['messages'].')...please contact support\n\n Reply 00 or back to return to Main Menu';
        }
        
    $validateuserbalance = $this->validatecustomerbalance($userid,$totalAmount,$branch);
    if($validateuserbalance["status"] == false){

        $this->tracktrails($userid,$branch,$usern,'customer',$validateuserbalance["message"],'');
        
        $this->logInfo("customer balance",$validateuserbalance);
        
       return $validateuserbalance["message"]."\n\n Reply 00 or back to return to Main Menu";
    }

    $validateTransferAmount = $this->validateTransfer($amount,$this->whtdta->getsettingskey('online_transfer'),$userid,$branch);
            
    if ($validateTransferAmount['status'] == false) {
        
         $this->logInfo("online transfer",$validateTransferAmount);
        
         return $validateTransferAmount["message"]."\n\n Reply 00 or back to return to Main Menu";
       
    }

    $validateuserpin = $this->validatetrnxpin($tnxpin,$userid,$branch);
    if($validateuserpin["status"] == false){

        $this->tracktrails(null,$branch,$usern,'customer',$validateuserpin["message"],'');
        
        $this->logInfo("Customer pin validation",$validateuserpin);
        
          return $validateuserpin["message"]."\n\n Reply 00 or back to return to Main Menu";
        }


        //initiate transaction
        $this->create_saving_transaction($userid,$userid,$branch,$amount,
        'debit','core','0',null,null,$this->whtdta->getsettingskey('payoption'),null,$trxref,$desc,'pending','2','trnsfer',$usern);
       

            $transaction = SavingsTransaction::where('reference_no',$trxref)->where('amount',$amount)->first();

            if ($transaction) {
                if($transaction->status == "approved" || $transaction->status == "failed"){

                    return "Transaction has already been completed...Please Reinitiate Transaction\n\n Reply 00 or back to return to Main Menu";

                }else{

                   
                  //transfer charges Gl
                  $glaccttrr = GeneralLedger::select('id','status','account_balance')->where('gl_code',$this->whtdta->getsettingskey('glcharges'))->first();
                    
                  if($glaccttrr->status == '1'){
                  $this->gltransaction('withdrawal',$glaccttrr,$tchargeamt,null);
                  $this->create_saving_transaction_gl($userid,$glaccttrr->id,$branch,$tchargeamt,'credit','core',$trxref,$this->generatetrnxref('trnxchrg'),'transfer charges','approved',$usern,'');
                  }
                 
                  //other charges Gl
                  $otherglacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$this->whtdta->getsettingskey('othrchargesgl'))->first();
                  
                  if($otherglacct->status == '1'){
                  $this->gltransaction('withdrawal',$otherglacct,$ocharge->amount,null); 
                  $this->create_saving_transaction_gl($userid,$otherglacct->id,$branch,$ocharge->amount,'credit','core',$trxref,$this->generatetrnxref('otc'),'others charges fees','approved',$usern,'');
                  }
         

        $bank = Bank::where('bank_code', $bnkcode)->first();
 
                        
        if($this->whtdta->getsettingskey('payoption') == "1"){//assetmatrix
            
            $debitCustomer = $this->DebitCustomerandcompanyGlAcct($userid,$totalAmount,$amount,$this->whtdta->getsettingskey('outwardoptiongl'),'py','Bank Transfer via asset matrix payout','core',$usern,$branch);

            $this->logInfo("debit customer response via whatsapp",$debitCustomer);
             
             if($cust->account_type == '1'){//saving acct GL
                 
                 if($this->glsavingdacct->status == '1'){
                 $this->gltransaction('deposit',$this->glsavingdacct,$totalAmount,null);
             $this->create_saving_transaction_gl($userid,$this->glsavingdacct->id,$branch, $totalAmount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern,'');
                 }
                 
             }elseif($cust->account_type == '2'){//current acct GL
                 
                 if($this->glcurrentacct->status == '1'){
                 $this->gltransaction('deposit',$this->glcurrentacct,$totalAmount,null);
             $this->create_saving_transaction_gl($userid,$this->glcurrentacct->id,$branch, $totalAmount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                 }
                 
             }


                    $url= env('ASSETMATRIX_BASE_URL')."banktransfer-payout";
                    
                    $bankTransfer = $this->bankTransferviaPayout($url,$amount,$raccountno,$bnkcode,env('SETTLEMENT_ACCOUNT_USERNAME'),$trxref,$desc);
            
                     //return $bankTransfer;
                     $this->logInfo("bank transfer response log via whatsapp",$bankTransfer);

                  
                     $updtdescription = $desc."/".$raccountname."/".$raccountno."-".$bank->bank_name;
  
                      if ($bankTransfer["status"] == true) {
                     
                        //companybal
                        $this->debitcreditCompanyBalance($this->whtappsesslog->amount,"debit","combal",$branch);
                    
                          $this->updateTransactionAndAddTrnxcharges($userid,$cust->id,$branch,$tchargeamt,'debit','core','0',null,null,null,$trxref,
                          $updtdescription,"charges",'approved','10',$usern,'');
                              
                     
                              $famt = " N".number_format($amount,2);
                              $dbalamt = " N".number_format($debitCustomer['balance'],2);
                              $bdecs1 =  $updtdescription;
          
                              $msg = "Debit Amt: ".$famt."<br> Desc: ".$bdecs1."<br> Avail Bal: ".$dbalamt."<br> Date:" . date('Y-m-d h:ia') . "<br> Ref: " .$trxref;
                              $smsmsg = "Debit Amt: ".$famt."\n Desc: ".$bdecs1." \n Avail Bal: ".$dbalamt."\n Date: ".date("Y-m-d h:ia")."\n Ref: ".$trxref;
                           
                              if($cust->enable_sms_alert){
                              $this->sendSms($cust->phone,$smsmsg,$this->whtdta->getsettingskey('active_sms'),$branch);//send sms
                              }
                              
                          if($cust->enable_email_alert){
                              Email::create([
                                'uuid' => Str::uuid(),
                                  'user_id' => $cust->id,
                                  'subject' => ucwords($this->whtdta->getsettingskey('company_name')).' Debit Alert',
                                  'message' => $msg,
                                  'recipient' => $cust->email,
                              ]);
          
                    //       Mail::send(['html' => 'mails.sendmail'],[ 
                    //           'msg' => $msg,
                    //           'type' => 'Debit Transaction',
                    //       ],function($mail)use($cust){
                    //           $mail->from($this->whtdta->getsettingskey('company_email'),ucwords($this->whtdta->getsettingskey('company_name')));
                    //           $mail->to($cust->email);
                    //       $mail->subject(ucwords($this->whtdta->getsettingskey('company_name')).' Debit Alert');
                    //   });
                          }
                 
                        return "Bank Transfer Successful \n\nNote: Please delete pin after use.\n Reply 00 or back to return to Main Menu";
                     
            }elseif($bankTransfer["status"] == false){
  
                       //FAILED TRANSACTION    
                    //    $this->updateTransactionAndAddTrnxcharges(null,$cust->id,$branch,$tchargeamt,'debit','core','0',null,null,null,$trxref,
                    //    $updtdescription,"charges",'failed','10',$usern,'');
                    
                    $this->tracktrails($userid,$branch,$usern,'customer','Bank Transfer Failed','');
                    
                //   $reverd = $this->ReverseDebitTrnxandcompanyGlAcct($cust->id,$totalAmount,$amount,$trxref,$getsetvalue->outwardoptiongl,'asm','Transaction reversed','core','trnsfer',$usern,'',$branch);
                  
                 
                  //reverse transfer charges Gl
                //   if($glaccttrr->status == '1'){
                //    $this->gltransaction('deposit',$glaccttrr,$tchargeamt,null);
                //   $this->create_saving_transaction_gl($userid,$glaccttrr->id,$branch,$tchargeamt,'debit','core',$trxref,$this->generatetrnxref('trnxchrg'),'reversed transfer charges','approved',$usern,'');
                //   }
                 
                //   //reverse other charges Gl
                //   if($otherglacct->status == '1'){
                //    $this->gltransaction('deposit',$otherglacct,$ocharge->amount,null); 
                //   $this->create_saving_transaction_gl($userid,$otherglacct->id,$branch,$ocharge->amount,'debit','core',$trxref,$this->generatetrnxref('otc'),'reversed others charges fees','approved',$usern,'');
                //   }
          
                //    //reverse saving acct and current acct Gl
                //    if($cust->account_type == '1'){//saving acct GL
                               
                //       if($this->glsavingdacct->status == '1'){
                //   $this->gltransaction('withdrawal',$this->glsavingdacct,$totalAmount,null);
                //   $this->create_saving_transaction_gl($userid,$this->glsavingdacct->id,$branch, $totalAmount,'credit','core',$trxref,$this->generatetrnxref('Cr'),'customer credited','approved',$usern,'');
                //       }
                      
                //   }elseif($cust->account_type == '2'){//current acct GL
                  
                //       if($this->glcurrentacct->status == '1'){
                //       $this->gltransaction('withdrawal',$this->glcurrentacct,$totalAmount,null);
                //   $this->create_saving_transaction_gl($userid,$this->glcurrentacct->id,$branch,$totalAmount,'credit','core',$trxref,$this->generatetrnxref('Cr'),'customer credited','approved',$usern);
                //       }
                //   }
          
                //          $msg = "Credit Amt: N".number_format($totalAmount,2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d h:ia")."<br>Ref: ".$trxref;
                //          $smsmsg = "Credit Amt: N".number_format($totalAmount,2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date: ".date("Y-m-d h:ia")."\n Ref: ".$trxref;
                           
                //          if($cust->enable_sms_alert){
                //          $this->sendSms($cust->phone,$smsmsg,$getsetvalue->active_sms,$branch);//send sms
                //          }
  
                //       if($cust->enable_email_alert){
                //        Email::create([
                //         'uuid' => Str::uuid(),
                //           'user_id' =>  $cust->id,
                //           'subject' => ucwords($getsetvalue->company_name).' Credit Alert',
                //           'message' => $msg,
                //           'recipient' => $cust->email,
                //       ]);
          
                //        Mail::send(['html' => 'mails.sendmail'],[
                //            'msg' => $msg,
                //            'type' => 'Credit Transaction',
                //        ],function($mail)use($getsetvalue,$cust){
                //            $mail->from($getsetvalue->company_email,ucwords($getsetvalue->company_name));
                //              $mail->to($cust->email);
                //            $mail->subject(ucwords($getsetvalue->company_name).' Credit Alert');
                //        });
                //       }
                      
                      return "Bank Transfer Failed \n\nNote: Please delete pin after use.\n Reply 00 or back to return to Main Menu";

                 }

        }elseif($this->whtdta->getsettingskey('payoption') == "4"){//wireless

            $debitCustomer = $this->DebitCustomerandcompanyGlAcct($cust->id,$totalAmount,$wireless,$this->whtdta->getsettingskey('outwardoptiongl'),'wlv','Bank Transfer via wireless','core',$usern,$branch);

            $this->logInfo("debit customer response via whatsapp",$debitCustomer);
             
             if($cust->account_type ==  '1'){//saving acct GL
             
                 if($this->glsavingdacct->status == '1'){
                 $this->gltransaction('deposit',$this->glsavingdacct,$totalAmount,null);
             $this->create_saving_transaction_gl($userid,$this->glsavingdacct->id,$branch,$totalAmount,'debit','core',$trxref,$this->generatetrnxref('D'),'customer debited','approved',$usern,'');
                 }
                 
             }elseif($cust->account_type == '2'){//current acct GL
             
             if($this->glcurrentacct->status == '1'){
                 $this->gltransaction('deposit',$this->glcurrentacct,$totalAmount,null);
             $this->create_saving_transaction_gl($userid,$this->glcurrentacct->id,$branch, $totalAmount,'debit','core',$trxref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                 }
                 
             }

                     //wireless verify transfer
                   $bankTransfer = $this->WirelessTransfer($this->wapikey,$amount,$trxref,$bnkcode,$raccountno,$raccountname,$desc);
                
                    //return $bankTransfer;
                    $this->logInfo("bank transfer response log vai whatsapp",$bankTransfer);

                  
                   $updtdescription = $raccountname."/".$raccountno."-".$bank->bank_name;

                   
               //if ($bankTransfer["status"] == "00") {
                   if($bankTransfer["status"] == "00"){
//companybal
$this->debitcreditCompanyBalance($this->whtappsesslog->amount,"debit","combal",$branch);
                    
                     $this->updateTransactionAndAddTrnxcharges($userid,$cust->id,$branch,$tchargeamt,'debit','core','0',null,null,null,$trxref,
                            $updtdescription,"charges",'approved','10',$usern,$updtdescription);
                       
                    $famt = " N".number_format($totalAmount,2);
                    $dbalamt = " N".number_format($debitCustomer['balance'],2);
                    $bdecs1 =  $updtdescription;

                    $msg = "Debit Amt: ".$famt."<br> Desc: ".$bdecs1."<br> Avail Bal: ".$dbalamt."<br> Date:" . date('Y-m-d h:ia') . "<br> Ref: " . $trxref;
                    $smsmsg = "Debit Amt: ".$famt."\n Desc: ".$bdecs1."\n Avail Bal: ".$dbalamt."\n Date:" . date('Y-m-d h:ia') . "\n Ref: " . $trxref;
                    
                    if($cust->enable_sms_alert){
                    $this->sendSms($cust->phone,$smsmsg,$this->whtdta->getsettingskey('active_sms'),$branch);//send sms
                   }

                    if($cust->enable_email_alert){
                    Email::create([
                        'uuid' => Str::uuid(),
                        'user_id' =>  $cust->id,
                        'subject' => ucwords($this->whtdta->getsettingskey('company_name')).' Debit Alert',
                        'message' => $msg,
                        'recipient' => $cust->email,
                    ]);
           
            //       Mail::send(['html' => 'mails.sendmail'],[
            //            'msg' => $msg,
            //             'type' => 'Debit Transaction',
            //        ],function($mail)use($cust){
            //         $mail->from($this->whtdta->getsettingskey('company_email'),ucwords($this->whtdta->getsettingskey('company_name')));
            //         $mail->to($cust->email);
            //       $mail->subject(ucwords($this->whtdta->getsettingskey('company_name')).' Debit Alert');
            //   });
                   }
                   
                 return "Bank Transfer Successful\n\nNote: Please delete pin after use.\n Reply 00 or back to return to Main Menu";
                       
              }else{
                         //FAILED TRANSACTION    
                        //  $this->updateTransactionAndAddTrnxcharges(null, $cust->id,$branch,$charge,'debit','core','0',null,null,null,$trxref,
                        //  $updtdescription,"charges",'failed','10',$usern,$updtdescription);
                      
                      $this->tracktrails($userid,$branch,$usern,'customer','Transaction Failed','');

                    // $reverd = $this->ReverseDebitTrnxandcompanyGlAcct($cust->id,$totalAmount,$wireless,$trxref,$getsetvalue->outwardoptiongl,'wlv','Transaction reversed','core','trnsfer',$usern,$updtdescription,$branch);
                    
                    //reverse transfer charges Gl
                    // if($glaccttrr->status == '1'){
                    //  $this->gltransaction('deposit',$glaccttrr,$tcharge->amount,null);
                    // $this->create_saving_transaction_gl($userid,$glaccttrr->id,$branch, $tcharge->amount,'debit','core',$trxref,$this->generatetrnxref('trnxchrg'),'reversed transfer charges','approved',$usern,'');
                    // }
                   
                    // //reverse other charges Gl
                    // if($otherglacct->status == '1'){
                    //  $this->gltransaction('deposit',$otherglacct,$ocharge->amount,null); 
                    // $this->create_saving_transaction_gl($userid,$otherglacct->id,$branch, $ocharge->amount,'debit','core',$trxref,$this->generatetrnxref('otc'),'reversed others charges fees','approved',$usern,'');
                    // }
                        
                    //     //reverse saving acct and current acct Gl
                    //  if($cust->account_type ==  '1'){//saving acct GL
                     
                    //     if($this->glsavingdacct->status == '1'){
                    // $this->gltransaction('withdrawal',$this->glsavingdacct,$totalAmount,null);
                    // $this->create_saving_transaction_gl($userid,$this->glsavingdacct->id,$branch,$totalAmount,'credit','core',$trxref,$this->generatetrnxref('D'),'customer debited','approved',$usern,'');
                    //     }
                        
                    // }elseif($cust->account_type == '2'){//current acct GL
                    
                    //     if($this->glcurrentacct->status == '1'){
                    //     $this->gltransaction('withdrawal',$this->glcurrentacct,$totalAmount,null);
                    // $this->create_saving_transaction_gl($userid,$this->glcurrentacct->id,$branch,$totalAmount,'credit','core',$trxref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                    //     }
                    // }
                    
                    //        $msg = "Credit Amt: N".number_format($totalAmount,2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d h:ia")."<br>Ref: ".$trxref;
                    //        $smsmsg = "Credit Amt: N".number_format($totalAmount,2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date: ".date("Y-m-d h:ia")."\n Ref: ".$trxref;
                           
                    //        if($cust->enable_sms_alert){
                    //        $this->sendSms($cust->phone,$smsmsg,$getsetvalue->active_sms,$branch);//send sms
                    //        }

                    //        if($cust->enable_email_alert){
                    //      Email::create([
                    //         'uuid' => Str::uuid(),
                    //         'user_id' =>  $cust->id,
                    //         'subject' => ucwords($getsetvalue->company_name).' Credit Alert',
                    //         'message' => $msg,
                    //         'recipient' => $cust->email,
                    //     ]);
            
                    //      Mail::send(['html' => 'mails.sendmail'],[
                    //          'msg' => $msg,
                    //          'type' => 'Credit Transaction',
                    //      ],function($mail)use($getsetvalue,$cust){
                    //          $mail->from($getsetvalue->company_email,ucwords($getsetvalue->company_name));
                    //            $mail->to($cust->email);
                    //          $mail->subject(ucwords($getsetvalue->company_name).' Credit Alert');
                    //      });
                    //        }
             
                       $message = "Bank Transfer Failed\n\nNote: Please delete pin after use.\n Reply 00 or back to return to Main Menu";
                       
                  }

         }elseif($this->whtdta->getsettingskey('payoption') == "2"){//monnify

            $debitCustomer = $this->DebitCustomerandcompanyGlAcct($userid,$totalAmount,$monify,$this->whtdta->getsettingskey('outwardoptiongl'),'m','Bank Transfer via monnify','core',$usern,$branch);

            $this->logInfo("debit customer response via whatsapp",$debitCustomer);
             
             if($cust->account_type == '1'){//saving acct GL
                 
                 if($this->glsavingdacct->status == '1'){
                 $this->gltransaction('deposit',$this->glsavingdacct,$totalAmount,null);
             $this->create_saving_transaction_gl($userid,$this->glsavingdacct->id,$branch, $totalAmount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern,'');
                 }
                 
             }elseif($cust->account_type == '2'){//current acct GL
                 
                 if($this->glcurrentacct->status == '1'){
                 $this->gltransaction('deposit',$this->glcurrentacct,$totalAmount,null);
             $this->create_saving_transaction_gl($userid,$this->glcurrentacct->id,$branch, $totalAmount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern.'(c)');
                 }
                 
             }

            $turl = $this->url."v2/disbursements/single"; 
            $bankTransfer = $this->monnifyTranfer($turl,$this->apikey,$this->sercetkey,$amount,$trxref,
                                                  $desc,$bnkcode,$raccountno,$this->acctno,$raccountname);

                                                   //return $bankTransfer;
                    $this->logInfo("bank transfer response log via whatsapp",$bankTransfer);

                  
                    $updtdescription = $desc."/".$raccountname."/".$raccountno."-".$bank->bank_name;
 
                    $dacct2 = $raccountname."/".$raccountno."-".$bankTransfer["responseBody"]["destinationBankName"];

                    if($bankTransfer["responseCode"] == "0"){
                        if($bankTransfer["responseBody"]["status"] == "SUCCESS"){

                        //companybal
                        $this->debitcreditCompanyBalance($this->whtappsesslog->amount,"debit","combal",$branch);
                
                            $this->updateTransactionAndAddTrnxcharges($userid,$userid,$branch,$tchargeamt,'debit','core','0',null,null,null,$trxref,
                                   $updtdescription,"charges",'approved','10',$usern,$dacct2);
                                      
                              
                           $famt = " N".number_format($totalAmount,2);
                           $dbalamt = " N".number_format($debitCustomer['balance'],2);
                           $bdecs1 =  $updtdescription;
                            
                           $smsmsg = "Debit Amt: ".$famt."\n Desc: ".$bdecs1." \n Avail Bal: ".$dbalamt."\n Date: ".date("Y-m-d h:ia")."\n Ref: ".$trxref;
                         
                            if($cust->enable_sms_alert){
                            $this->sendSms($cust->phone,$smsmsg,$this->whtdta->getsettingskey('active_sms'),$branch);//send sms
                            }

                           if($cust->enable_email_alert){
                           $msg = "Debit Amt: ".$famt."<br> Desc: ".$bdecs1."<br> Avail Bal: ".$dbalamt."<br> Date:" . date('Y-m-d h:ia') . "<br> Ref: " .$trxref;
                           Email::create([
                            'uuid' => Str::uuid(),
                               'user_id' =>  $cust->id,
                               'subject' => ucwords($this->whtdta->getsettingskey('company_name')).' Debit Alert',
                               'message' => $msg,
                               'recipient' => $cust->email,
                           ]);
                  
                    //      Mail::send(['html' => 'mails.sendmail'],[
                    //           'msg' => $msg,
                    //            'type' => 'Debit Transaction',
                    //       ],function($mail)use($cust){
                    //        $mail->from($this->whtdta->getsettingskey('company_name'),ucwords($this->whtdta->getsettingskey('company_name')));
                    //        $mail->to($cust->email);
                    //      $mail->subject(ucwords($this->whtdta->getsettingskey('company_name')).' Debit Alert');
                    //  });
                    }

                       return "Bank Transfer Successful\n\nNote: Please delete pin after use.\n Reply 00 or back to return to Main Menu";
                              
                     }else{
                                //FAILED TRANSACTION    
                                // $this->updateTransactionAndAddTrnxcharges(null, $userid,$branch,$charge,'debit','core','0',null,null,null,$trxref,
                                // $updtdescription,"charges",'failed','10',$usern,$dacct2);
                             
                             $this->tracktrails($userid,$branch,$usern,'customer','Transaction Failed','');
       
                        //    $reverd = $this->ReverseDebitTrnxandcompanyGlAcct($userid,$totalAmount,$monify,$trxref,$getsetvalue->outwardoptiongl,'m','Transaction reversed','core','trnsfer',$usern,$dacct2,$branch);
                        
                           //reverse transfer charges Gl
                        //    if($glaccttrr->status == '1'){
                        //     $this->gltransaction('deposit',$glaccttrr,$tcharge->amount,null);
                        //    $this->create_saving_transaction_gl($userid,$glaccttrr->id,$branch, $tcharge->amount,'debit','core',$trxref,$this->generatetrnxref('trnxchrg'),'reversed transfer charges','approved',$usern,'');
                        //    }
                          
                        //    //reverse other charges Gl
                        //    if($otherglacct->status == '1'){
                        //     $this->gltransaction('deposit',$otherglacct,$ocharge->amount,null); 
                        //    $this->create_saving_transaction_gl($userid,$otherglacct->id,$branch, $ocharge->amount,'debit','core',$trxref,$this->generatetrnxref('otc'),'reversed others charges fees','approved',$usern,'');
                        //    }
                               
                        //        //reverse saving acct and current acct Gl
                        //     if($cust->account_type == '1'){//saving acct GL
                               
                        //        if($this->glsavingdacct->status == '1'){
                        //    $this->gltransaction('withdrawal',$this->glsavingdacct,$totalAmount,null);
                        //    $this->create_saving_transaction_gl($userid,$this->glsavingdacct->id,$branch, $totalAmount,'credit','core',$trxref,$this->generatetrnxref('D'),'customer debited','approved',$usern,'');
                        //        }
                               
                        //    }elseif($cust->account_type == '2'){//current acct GL
                               
                        //        if($this->glcurrentacct->status == '1'){
                        //        $this->gltransaction('withdrawal',$this->glcurrentacct,$totalAmount,null);
                        //    $this->create_saving_transaction_gl($userid,$this->glcurrentacct->id,$branch, $totalAmount,'credit','core',$trxref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                        //        }
                               
                        //    }
                           
                        //    $smsmsg = "Credit Amt: N".number_format($totalAmount,2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date: ".date("Y-m-d h:ia")."\n Ref: ".$trxref;
                         
                        //     if($cust->enable_sms_alert){
                        //     $this->sendSms($cust->phone,$smsmsg,$getsetvalue->active_sms,$branch);//send sms
                        //     }

                        //    if($cust->enable_email_alert){
                        //           $msg = "Credit Amt: N".number_format($totalAmount,2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d h:ia")."<br>Ref: ".$trxref;
                        //         Email::create([
                        //             'uuid' => Str::uuid(),
                        //            'user_id' =>  $cust->id,
                        //            'subject' => ucwords($getsetvalue->company_name).' Credit Alert',
                        //            'message' => $msg,
                        //            'recipient' => $cust->email,
                        //        ]);
                   
                        //         Mail::send(['html' => 'mails.sendmail'],[
                        //             'msg' => $msg,
                        //             'type' => 'Credit Transaction',
                        //         ],function($mail)use($getsetvalue,$cust){
                        //             $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                        //               $mail->to($cust->email);
                        //             $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
                        //         });
                        //     }

                        return $message = "Bank Transfer Failed\n\nNote: Please delete pin after use.\n Reply 00 or back to return to Main Menu";
                              
                         }
                    }
                
                }elseif($this->whtdta->getsettingskey('payoption') == "3"){//nibss pay
                   return "nibss";
                }
                   
                }
            } else {
                return "Invalid Transaction Reference,Please Reinitiate Transaction\n\n Reply 00 or back to return to Main Menu";
            }

        // }else{
        //     return "invalid gateway operation\n\n Reply 00 or back to return to Main Menu";
        // }
       
    }

    public function generateSendStatement($userid,$startdate,$enddate,$trnxpin,$branch){

        $customer = Customer::findorfail($userid);

        $chkcres = $this->checkCustomerRestriction($userid,$branch);
        if($chkcres == true){
            
            $this->tracktrails(null,$branch,$customer->last_name." ".$customer->first_name,'customer','Account Restricted','');

            return 'Your Account Has Been Restricted. Please contact support';
        }

        $validateuserpin = $this->validatetrnxpin($trnxpin,$userid,$branch);
        if($validateuserpin["status"] == false){
    
            $this->tracktrails(null,$branch,$customer->last_name." ".$customer->first_name,'customer',$validateuserpin["message"],'');
            
            $this->logInfo("Customer pin validation",$validateuserpin);
            
              return $validateuserpin["message"]."\n\n Reply 00 or back to return to Main Menu";
            }


        $getsave = Saving::select('account_balance','savings_product_id')->where('customer_id',$userid)->first();


        $getproname = SavingsProduct::where('id',$getsave->savings_product_id)->first();

$transac = SavingsTransaction::where('customer_id',$userid)
                            ->whereBetween('created_at',[$startdate,$enddate])
                            ->orderBy('created_at','ASC')->get();

$balance = 0;                          
$savtrns = SavingsTransaction::where('customer_id',$userid)->whereDate('created_at','<',$startdate)->orderBy('created_at','ASC')->get();

foreach($savtrns as $key){
    if($key['type']=="deposit" || $key['type']=="investment"  || $key['type']=="dividend" || $key['type']=="interest" ||
    $key['type']=="credit" || $key['type']=="fixed_deposit" || $key['type']=="loan" || $key['type']=="fd_interest" 
    || $key['type']=="inv_int" || $key['type']=="rev_withdrawal" || $key['type'] == 'guarantee_restored'){

    if($key['status'] == 'approved'){
    $balance += $key->amount;
    }else{
    $balance;
    }

    }else{
    if($key->status == 'pending' || $key->status == 'declined'){
    $balance += 0;
    }else{
    $balance -= $key->amount;

    }
 }

$balance;
}
    $body = "Dear ".ucwords($customer->last_name." ".$customer->first_name).",<br> Please find attachment of your account statement below <br> Thank you. <br><br>  Thank you for choosing ".$this->whtdta->getsettingskey('company_name');
  
        $data = [
            'title' => $this->whtdta->getsettingskey('company_name')." Statement",
            'date' => date('d/m/Y'),
            'customer' => $customer,
            'getsave' => $getsave,
            'getproname' => $getproname,
            'transactions' => $transac,
            'custid' => $balance
        ];
        
        $pdf = PDF::loadView("deposit.pdf_statement", $data);

        $filename = time().'_account_statement.pdf';
        $pdfcontent = $pdf->output();
        file_put_contents($filename,$pdfcontent);
       
        $getpdf_file = $filename;

       
        Mail::send(['html' => 'mails.sendmail'],[
            'msg' => $body,
            'type' => "Account Statement",
        ],function($mail)use($customer,$getpdf_file){
            $mail->from($this->whtdta->getsettingskey('company_email'),ucwords($this->whtdta->getsettingskey('company_name')));
             $mail->to($customer->email);
            $mail->subject(ucwords($customer->last_name." ".$customer->first_name)." Account Statement");
            $mail->attach($getpdf_file);
        });
       
        unlink($getpdf_file);

        Email::create([
            'uuid' => Str::uuid(),
            'user_id' => $customer->id,
            'subject' => ucwords($customer->last_name." ".$customer->first_name)." Account Statement",
            'message' => $body,
            'recipient' => $customer->email,
        ]);

        return  "Your account statement has being sent to your email\n\nNote: Please delete pin after use.\n  Reply 00 or back to return to Main Menu";
    }
    
    public function buyDataBundle($id,$user,$amount,$phone_number,$network_provider,$data_plan,$pin,$branch){
        $msg = "";
        $logs=[$user,$phone_number,$network_provider,$pin,$branch];

        $this->logInfo("buy data bundle",$logs);

        $cust = Customer::where('id',$id)->first();
        
        $usern = $user;
        
        $getsetvalue = new Setting();

        $savpd = SavingsProduct::where('id',$cust->account_type)->first();

        $trnxid = $this->vtpassrequestid();

       $compbal = $this->validatecompanybalance($amount,'vas');
       if($compbal["status"] == false){
   
        $this->logInfo("validating company balance",$compbal);
       
       return $compbal['message'];
   }

       $validateuserbalance = $this->validatecustomerbalance($id, $amount,$branch);
        if($validateuserbalance["status"] == false){

            $this->tracktrails(null,$branch,$usern,'customer',$validateuserbalance["message"],'');
            
            $this->logInfo("validating custome balance",$validateuserbalance);
            
            return $validateuserbalance["message"];
        }

        $chkcres = $this->checkCustomerRestriction($id,$branch);
        if($chkcres == true){
            
            $this->tracktrails(null,$branch,$usern,'customer','Account Restricted','');

            return 'Your Account Has Been Restricted. Please contact support';
        }

         $chklien = $this->checkCustomerLienStatus($id,$branch);
            if($chklien['status'] == true && $chklien['lien'] == 2){
                
                $this->tracktrails(null,$branch,$usern,'customer','Account has been lien','');
                
                $this->logInfo("validating lien status",$chklien);
                
             return 'Your Account Has Been Lien('.$chklien['messages'].')...please contact support';
           
            }
            
        $validateuserpin = $this->validatetrnxpin($pin,$id,$branch);
        if($validateuserpin["status"] == false){
            
            $this->tracktrails(null,$branch,$usern,'customer',$validateuserpin["message"],'');
            
            $this->logInfo("validating lcustomer pin",$validateuserpin);
            
           return $validateuserpin['message'];
        }

       
        
       $percentage = $this->getUtilityPercentage();
       $prec = array();
        foreach($percentage as $percent){
            if($percent["service"] == $network_provider){
                $prec = $percent;
            }
        }

        $percentincome = $amount * $prec["value"] / 100;
        $totamount = $amount - $percentincome;
        
         
        $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$this->whtdta->getsettingskey('vtpass_income'))->first();

    if($glacct->status == '1'){

        $this->gltransaction('withdrawal',$glacct, $percentincome,null); 
          $this->create_saving_transaction_gl(null,$glacct->id,$branch,$percentincome,'credit',"whatsapp",null,$this->generatetrnxref('vt'),'vtpass data percentage','approved',$usern,'');
          
        }

        $customerbal = $this->DebitCustomerandcompanyGlAcct($id,$amount,$totamount,$this->whtdta->getsettingskey('vtpass_account'),'vt','Data Bundles Purchased '.$phone_number,"whatsapp",$usern,$branch);

        $this->logInfo("debit customer response", $customerbal);
        
         
             if($cust->account_type == '1'){//saving acct GL
                        
                        if($this->glsavingdacct->status == '1'){
                    $this->gltransaction('deposit',$this->glsavingdacct,$amount,null);
                $this->create_saving_transaction_gl(null,$this->glsavingdacct->id,$branch,$amount,'debit',"whatsapp",null,$this->generatetrnxref('W'),'customer debited','approved',$usern,'');
                        }
                        
                }elseif($cust->account_type == '2'){//current acct GL
                    
                    if($this->glcurrentacct->status == '1'){
                    $this->gltransaction('deposit',$this->glcurrentacct,$amount,null);
                $this->create_saving_transaction_gl(null,$this->glcurrentacct->id,$branch, $amount,'debit',"whatsapp",null,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                    }
                    
                }

        $endpoint = $this->vtpassurl."pay";

        $body = [
            'request_id' => $trnxid,
            'serviceID' => $network_provider,
            'billersCode' => $phone_number,
            'variation_code' => $data_plan,
            'amount' => $amount,
            'phone' => $phone_number,
        ];

        $response = $this->vtpassposturl($endpoint,$body);
        
        $this->logInfo("data bundle response",$response);

        if($response['code'] == "000"){

              //companybal
              $this->debitcreditCompanyBalance($this->whtappsesslog->amount,"debit","vas",$branch);
                     
            $description = "Data Bundles Purchased worth ".$amount." --".$phone_number." -trxid:".$trnxid;

            $this->create_saving_transaction(null,$id,$branch,$amount,'debit',"whatsapp",'0',null,null,null,null,$trnxid,$description,'approved','15','utility',$usern);

            $this->tracktrails(null,$branch,$usern,'customer',$description,'');

           
            $msg = "Debit Amt: N".number_format($amount,2)."<br> Desc: Data Bundles Purchased successfully <br>Avail Bal: N".number_format($customerbal["balance"],2)."<br> Date: ".date("Y-m-d h:ia")."<br>Ref: ".$trnxid;
       
            $smsmsg = "Debit Amt: N".number_format($amount,2)."\n Desc:Data Bundles Purchased successfully \n Avail Bal: N".number_format($customerbal["balance"],2)."\n Date:" . date('Y-m-d h:ia') . "\n Ref: " . $trnxid;
    
         if($cust->enable_sms_alert){
             $this->sendSms($cust->phone,$smsmsg,$this->whtdta->getsettingskey('active_sms'),$branch);//send sms
          }

       if($cust->enable_email_alert){
         Email::create([
            'uuid' => Str::uuid(),
             'user_id' =>  $id,
             'subject' => ucwords($this->whtdta->getsettingskey('company_name')).' Debit Alert',
             'message' => $msg,
             'recipient' => $cust->email,
         ]);

         Mail::send(['html' => 'mails.sendmail'],[
             'msg' => $msg,
             'type' => 'Debit Transaction',
            ],function($mail)use($getsetvalue,$cust){
             $mail->from($this->whtdta->getsettingskey('company_email'),ucwords($this->whtdta->getsettingskey('company_name')));
               $mail->to($cust->email);
             $mail->subject(ucwords($this->whtdta->getsettingskey('company_name')).' Debit Alert');
         });
        }

            $msg = "Data Purchased successfully\n\nNote: Please delete pin after use.\n Reply 00 or back to return to Main Menu";

         }else{

            $this->create_saving_transaction(null,$id,$branch,$amount,
            'debit',"whatsapp",'0',null,null,null,null,$trnxid,'Failed to Purchased Data Bundles','failed','15','utility',$usern);
            
            if($glacct->status == '1'){

             $this->gltransaction('deposit',$glacct,$percentincome,null); 
            $this->create_saving_transaction_gl(null,$glacct->id,$branch, $percentincome,'debit',"whatsapp",null,$this->generatetrnxref('vt'),'vtpass percentage','approved',$usern,'');
           
            }

        //  $reverd = $this->ReverseDebitTrnxandcompanyGlAcct($id,$amount,$totamount,$trnxid,$getsetvalue->getsettingskey('vtpass_account'),'vt','Data Bundles Purchased Transaction reversed',"whatsapp",'utility',$usern,null,$branch);
           
        //   //companybal
       

        //     //reverse saving acct and current acct Gl
        //      if($cust->account_type ==  $savpd->id){//saving acct GL
                
        //         if($this->glsavingdacct->status == '1'){
        //     $this->gltransaction('withdrawal',$this->glsavingdacct,$amount,null);
        //     $this->create_saving_transaction_gl(null,$this->glsavingdacct->id,$branch, $amount,'credit',"whatsapp",null,$this->generatetrnxref('D'),'customer debited','approved',$usern,'');
        //         }
                
        //     }
        //     // elseif($cust->account_type == '2'){//current acct GL
                
        //     //     if($glcurrentacct->status == '1'){
        //     //     $this->gltransaction('withdrawal',$glcurrentacct,$amount,null);
        //     // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$branch, $amount,'credit',"whatsapp",null,$this->generatetrnxref('D'),'customer debited','approved',$usern,'');
        //     //     }
                
        //     // }
         
        //  $this->tracktrails(null,$branch,$usern,'customer','Failed to Purchased Data Bundles','');

        //  $msg = "Credit Amt: N".number_format($amount,2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d h:ia")."<br>Ref: ".$trnxid;
        
        //  $smsmsg = "Credit Amt: N".number_format($amount,2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date:" . date('Y-m-d h:ia') . "\n Ref: " . $trnxid;
    
        //  if($cust->enable_sms_alert){
        //      $this->sendSms($cust->phone,$smsmsg,$getsetvalue->active_sms,$branch);//send sms
        //      }

        // if($cust->enable_email_alert){
        //  Email::create([
        //     'uuid' => Str::uuid(),
        //      'user_id' => $cust->id,
        //      'subject' => ucwords($getsetvalue->company_name).' Credit Alert',
        //      'message' => $msg,
        //      'recipient' => $cust->email,
        //  ]);

        //  Mail::send(['html' => 'mails.sendmail'],[
        //      'msg' => $msg,
        //      'type' => 'Credit Transaction',
        //     ],function($mail)use($getsetvalue,$cust){
        //      $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
        //        $mail->to($cust->email);
        //      $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
        //  });
        // }
        
            $msg = "Failed to Purchased Data Bundle\n\nNote: Please delete pin after use.\n Reply 00 or back to return to Main Menu";
         }

         return $msg;
    }

    public function buyAirtime($id,$user,$amount,$phone_number,$network_provider,$pin,$branch){
       
        $msg = "";
        $logs=[$id,$user,$amount,$phone_number,$network_provider,$pin,$branch];
        
        $this->logInfo("Airtime topup",$logs);
        

        $trnxid = $this->vtpassrequestid();

        $cust = Customer::where('id',$id)->first();
        
        $getsetvalue = new Setting();
        
        $savpd = SavingsProduct::where('id',$cust->account_type)->first();

        $usern = $user;

        $compbal = $this->validatecompanybalance($amount,'vas');
        if($compbal["status"] == false){
    
            $this->logInfo("validating company balance",$compbal);
        
        $msg = $compbal["message"];
    }

        $chkcres = $this->checkCustomerRestriction($id,$branch);
            if($chkcres == true){
    
                $this->tracktrails(null,$branch,$usern,'customer','Account Restricted','');
                
                $this->logInfo("customer account restricted",'');
     
                $msg = 'Your Account Has Been Restricted. Please contact support';
            }
    
            $chklien = $this->checkCustomerLienStatus($id,$branch);
            if($chklien['status'] == true && $chklien['lien'] == 2){
                
                $this->tracktrails(null,$branch,$usern,'customer','Account has been lien','');
                
                $this->logInfo("validating lien status",$chklien);
                
             $msg ='Your Account Has Been Lien('.$chklien['messages'].')...please contact support';
            }
            
            $validateuserpin = $this->validatetrnxpin($pin,$id,$branch);
            if($validateuserpin["status"] == false){
                
                $this->tracktrails(null,$branch,$usern,'customer',$validateuserpin["message"],'');
    
                $this->logInfo("validating customer pin",$validateuserpin);
                    
                $msg = $validateuserpin["message"];
            }
    
            $validateuserbalance = $this->validatecustomerbalance($id,$amount,$branch);
            if($validateuserbalance["status"] == false){
    
                $this->tracktrails(null,$branch,$usern,'customer',$validateuserbalance["message"],'');
                
                $this->logInfo("validating customer balance",$validateuserbalance);
                
                $msg = $validateuserbalance["message"];
            }

            $percentage = $this->getUtilityPercentage();
            //return $percentage;
            $prec = array();
             foreach($percentage as $percent){
                 if($percent["service"] == $network_provider){
                     $prec = $percent;
                 } 
             }
     
            //  return $prec;

             $percentincome = $amount * $prec["value"] / 100;
             $totamount = $amount - $percentincome;
             
             $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$this->whtdta->getsettingskey('vtpass_income'))->first();
             
             if($glacct->status == '1'){

                $this->gltransaction('withdrawal',$glacct,$percentincome,null);
               $this->create_saving_transaction_gl(null,$glacct->id,$branch, $percentincome,'credit',"whatsapp",null,$this->generatetrnxref('vt'),'vtpass airtime percentage','approved',$usern,'');
                
             }
             
            $customerbal = $this->DebitCustomerandcompanyGlAcct($id,$amount,$totamount,$getsetvalue->getsettingskey('vtpass_account'),'vt','Purchase of Airtime --'.$phone_number,"whatsapp",$usern,$branch);
           
            $this->logInfo("debit customer response", $customerbal);
            
             if($cust->account_type ==  '1'){//saving acct GL
                        
                    $this->gltransaction('deposit',$this->glsavingdacct,$amount,null);
                $this->create_saving_transaction_gl(null,$this->glsavingdacct->id,$branch, $amount,'debit',"whatsapp",null,$this->generatetrnxref('D'),'customer debited','approved',$usern,'');
                    
                }elseif($cust->account_type == '2'){//current acct GL
                    
                    $this->gltransaction('deposit',$this->glcurrentacct,$amount,null);
                $this->create_saving_transaction_gl(null,$this->glcurrentacct->id,$branch, $amount,'debit',"whatsapp",null,$this->generatetrnxref('D'),'customer debited','approved',$usern,'');
                    
                }
          

        $endpoint = $this->vtpassurl."pay";

        $body = [
            'request_id' => $trnxid,
            'serviceID' => $network_provider,
            'amount' => $amount,
            'phone' => $phone_number,
        ];

        $response = $this->vtpassposturl($endpoint,$body);

        $this->logInfo("Airtime response",$response);
 
        if($response['code'] == "000"){//success

              //companybal
         $this->debitcreditCompanyBalance($this->whtappsesslog->amount,"debit","vas",$branch);
                     
            $description = "Purchase of Airtime worth ".$amount." --".$phone_number." -trxid:".$response["content"]["transactions"]["transactionId"];

            $this->create_saving_transaction(null, $id,$branch,$amount,'debit',"whatsapp",'0',null,null,null,null,$trnxid,$description,'approved','14','utility',$usern);

            $this->tracktrails(null,$branch,$usern,'customer',$description,'');

             $msg = "Debit Amt: N".number_format($amount,2)."<br> Desc: Airtime Purchased successfully  <br>Avail Bal: N".number_format($customerbal["balance"],2)."<br> Date: ".date("Y-m-d h:ia")."<br>Ref: ".$trnxid;
            
             $smsmsg = "Debit Amt: N".number_format($amount,2)."\n Desc: Airtime Purchased successfully \n Avail Bal: N".number_format($customerbal["balance"],2)."\n Date:" . date('Y-m-d h:ia') . "\n Ref: " . $trnxid;
    
             if($cust->enable_sms_alert){
                 $this->sendSms($cust->phone,$smsmsg,$getsetvalue->active_sms,$branch);//send sms
                 }

            if($cust->enable_email_alert){
             Email::create([
                'uuid' => Str::uuid(),
                 'user_id' => $id,
                 'subject' => ucwords($getsetvalue->company_name).' Debit Alert',
                 'message' => $msg,
                 'recipient' => $cust->email,
             ]);

            Mail::send(['html' => 'mails.sendmail'],[
                'msg' => $msg,
                'type' => 'Debit Transaction',
            ],function($mail)use($getsetvalue,$cust){
                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                 $mail->to($cust->email);
                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
            });
        }
           
           $msg = "Airtime Purchased successfully\n\nNote: Please delete pin after use.\n Reply 00 or back to return to Main Menu";

         }else{

            $this->create_saving_transaction(null, $id,$branch,$amount,
            'debit',"whatsapp",'0',null,null,null,null,$trnxid,'Failed to Purchase Airtime','failed','14','utility',$usern.'(c)');
            
            if($glacct->status == '1'){

                $this->gltransaction('deposit',$glacct,$percentincome,null);
            $this->create_saving_transaction_gl(null,$glacct->id,$branch, $percentincome,'debit',"whatsapp",null,$this->generatetrnxref('vt'),'vtpass airtime percentage reversed','approved',$usern,'');
             
            }

        //  $reverd = $this->ReverseDebitTrnxandcompanyGlAcct($id,$amount,$totamount,$trnxid,$getsetvalue->vtpass_account,'vt','Airtime Purchased Transaction reversed',"whatsapp",'utility',$usern,null,$branch);
                     
       
 
        //      //reverse saving acct and current acct Gl
        //      if($branch->account_type ==  $savpd->id){//saving acct GL
                
        //         if($this->glsavingdacct->status == '1'){
        //     $this->gltransaction('withdrawal',$this->glsavingdacct,$amount,null);
        //     $this->create_saving_transaction_gl(null,$this->glsavingdacct->id,$branch, $amount,'credit',"whatsapp",null,$this->generatetrnxref('D'),'customer debited','approved',$usern,'');
        //         }
                
        //     }
        //     // elseif($branch->account_type == '2'){//current acct GL
                
        //     //     if($glcurrentacct->status == '1'){
        //     //     $this->gltransaction('withdrawal',$glcurrentacct,$amount,null);
        //     // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$branch, $amount,'credit',"whatsapp",null,$this->generatetrnxref('D'),'customer debited','approved',$usern);
        //     //     }
                
        //     // }
                    
        //  $this->tracktrails(null,$branch,$usern,'customer','Failed to Purchased Airtime','');
 
        //  $msg = "Credit Amt: N".number_format($amount,2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d h:ia")."<br>Ref: ".$trnxid;
        
        //  $smsmsg = "Credit Amt: N".number_format($amount,2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date:" . date('Y-m-d h:ia') . "\n Ref: " . $trnxid;
    
        //  if($cust->enable_sms_alert){
        //      $this->sendSms($cust->phone,$smsmsg,$getsetvalue->active_sms,$branch);//send sms
        //      }

        // if($cust->enable_email_alert){
        //  Email::create([
        //     'uuid' => Str::uuid(),
        //      'user_id' => $id,
        //      'subject' => ucwords($getsetvalue->company_name).' Credit Alert',
        //      'message' => $msg,
        //      'recipient' => $cust->email,
        //  ]);

        //  Mail::send(['html' => 'mails.sendmail'],[
        //      'msg' => $msg,
        //      'type' => 'Credit Transaction',
        //     ],function($mail)use($getsetvalue,$cust){
        //      $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
        //        $mail->to($cust->email);
        //      $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
        //  });
        //  }
         
            $msg = "Failed to Purchase Airtime\n\nNote: Please delete pin after use.\n Reply 00 or back to return to Main Menu";
         }

         return $msg;
    }

    //pay cable tv
    public function pay_cable_tv($id,$usern,$service_type,$smartcard_number,$amount,$phone_number,$subcription_plan,$pin,$branch){
       global $msg;
        $trnxid = $this->vtpassrequestid();
        $endpoint = $this->vtpassurl."pay";

        $cust = Customer::where('id',$id)->first();
     //  return $trnxid;
        $getsetvalue = new Setting();
        
        $savpd = SavingsProduct::where('id',$cust->account_type)->first();

        $usern = $usern;

        $this->logInfo("cable subcription",[$id,$usern,$service_type,$smartcard_number,$amount,$phone_number,$subcription_plan,$pin,$branch]);
                
        if($service_type == "showmax"){

            $compbal = $this->validatecompanybalance($amount,'vas');
            if($compbal["status"] == false){
        
                $this->logInfo("validating company balance",$compbal);
            
                 return $compbal["message"];
        }

            $chkcres = $this->checkCustomerRestriction($id,$branch);
            if($chkcres == true){
    
                $this->tracktrails(null,$branch,$usern,'customer','Account Restricted','');
    
                $this->logInfo("Customer Account Restricted",'');
                
               return 'Your Account Has Been Restricted. Please contact support';
            }
            
             $chklien = $this->checkCustomerLienStatus($id,$branch);
            if($chklien['status'] == true && $chklien['lien'] == 2){
                
                $this->tracktrails(null,$branch,$usern,'customer','Account has been lien','');
                
                $this->logInfo("validating lien status",$chklien);
                
             return 'Your Account Has Been Lien('.$chklien['messages'].')...please contact support';
          
            }

        $validateuserpin = $this->validatetrnxpin($pin,$id,$branch);
        if($validateuserpin["status"] == false){
            
            $this->tracktrails(null,$branch,$usern,'customer',$validateuserpin["message"],'');
            
            $this->logInfo("validating customer pin",$validateuserpin);
                
           return $validateuserpin['message'];
        }

        $validateuserbalance = $this->validatecustomerbalance($id,$amount,$branch);
        if($validateuserbalance["status"] == false){

            $this->tracktrails(null,$branch,$usern,'customer',$validateuserbalance["message"],'');
            
            $this->logInfo("validating customer balance",$validateuserbalance);
 
          return $validateuserbalance["message"];

        }
        
        $percentage = $this->getUtilityPercentage();
            $prec = array();
             foreach($percentage as $percent){
                 if($percent["service"] == $service_type){
                     $prec = $percent;
                 }
             }
     
             $percentincome = $amount * $prec["value"] / 100;
             $totamount = $amount - $percentincome;
     
             $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$this->whtdta->getsettingskey('vtpass_income'))->first();
           
             if($glacct->status == '1'){
                
                 $this->gltransaction('withdrawal',$glacct,$percentincome,null); 
               $this->create_saving_transaction_gl(null,$glacct->id,$branch,$percentincome,'credit',"whatsapp",null,$this->generatetrnxref('vt'),'vtpass '.$service_type.' percentage','approved',$usern,'');
              
             }
             
        $customerbal = $this->DebitCustomerandcompanyGlAcct($id,$amount,$totamount,$getsetvalue->getsettingskey('vtpass_account'),'vt','Cable TV Subscription('.$service_type.')',"whatsapp",$usern,$branch);

        $this->logInfo("debit customer response", $customerbal);
        
         if($cust->account_type == '1'){//saving acct GL
         
                 if($this->glsavingdacct->status == '1'){
                        $this->gltransaction('deposit',$this->glsavingdacct,$amount,null);
                    $this->create_saving_transaction_gl(null,$this->glsavingdacct->id,$branch, $amount,'debit',"whatsapp",null,$this->generatetrnxref('W'),'customer debited','approved',$usern,'');
                 }
                 
                    }
                    // elseif($cust->account_type == '2'){//current acct GL
                    
                    //     if($glcurrentacct->status == '1'){
                    //     $this->gltransaction('deposit',$glcurrentacct,$amount,null);
                    // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$branch, $amount,'debit',"whatsapp",null,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                    //     }
                        
                    // }
                
            $showmaxbody = [
                'request_id' => $trnxid,
                'serviceID' => $service_type,
                'billersCode' => $phone_number,
                'variation_code' => $subcription_plan,
                'phone' => $phone_number,
                'amount' => $amount
            ];

            $response = $this->vtpassposturl($endpoint,$showmaxbody);

            $this->logInfo("vtpass showmax response",$response);
                
              //return  $response; 
             if($response['code'] == "000"){

                if(isset($response['content']['error'])){
                    return response()->json(['status' => false,'message' => $response['content']['error']]);
                }

                  //companybal
                  $this->debitcreditCompanyBalance($this->whtappsesslog->amount,"debit","vas",$branch);

                $description = "Cable TV Subscription($service_type) worth ".$amount;

                $this->create_saving_transaction(null,$id,$branch,$amount,'debit',"whatsapp",'0',null,null,null,null,$trnxid,$description,'approved','16','utility',$usern);


                $this->tracktrails(null,$branch,$usern,'customer','Cable TV Subscription Purchased Successfully('.$service_type.')','');

               $emsg =  "Debit Amt: N".number_format($amount,2)."<br> Desc: Cable TV Subscription Successful(".$service_type.")  <br>Avail Bal: N".number_format($customerbal["balance"],2)."<br> Date: ".date("Y-m-d h:ia")."<br>Ref: ".$trnxid;
               
               $smsmsg = "Debit Amt: N".number_format($amount,2)."\n Desc: Cable TV Subscription Successful(".$service_type.") \n Avail Bal: N".number_format($customerbal["balance"],2)."\n Date:" . date('Y-m-d h:ia') . "\n Ref: " . $trnxid;
    
               if($cust->enable_sms_alert){
                   $this->sendSms($cust->phone,$smsmsg,$getsetvalue->active_sms,$branch);//send sms 
                   }

               if($cust->enable_email_alert){
                Email::create([
                    'uuid' => Str::uuid(),
                    'user_id' =>  $id,
                    'subject' => ucwords($getsetvalue->company_name).' Debit Alert',
                    'message' => $emsg,
                    'recipient' => $cust->email,
                ]);

                Mail::send(['html' => 'mails.sendmail'],[
                    'msg' => $emsg,
                    'type' => 'Debit Transaction',
                ],function($mail)use($getsetvalue,$cust){
                    $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                     $mail->to($cust->email);
                    $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
                });
             }
 
                return "Cable TV Subscription Purchased Successfully\n\nNote: Please delete pin after use.\n Reply 00 or back to return to Main Menu";

             }else{

                $this->create_saving_transaction(null,$id,$branch,$amount,
                'debit',"whatsapp",'0',null,null,null,null,$trnxid,'Cable TV Subscription Purchased Failed','failed','16','utility',$usern);

        if($glacct->status == '1'){

            $this->gltransaction('deposit',$glacct,$percentincome,null); 
                $this->create_saving_transaction_gl(null,$glacct->id,$branch, $percentincome,'debit',"whatsapp",null,$this->generatetrnxref('vt'),'vtpass '.$service_type.' percentage reversed','approved',$usern,'');
                
        }
      
        // $reverd = $this->ReverseDebitTrnxandcompanyGlAcct($id,$amount,$totamount,$trnxid,$getsetvalue->vtpass_account,'vt','CableTv Transaction reversed('.$service_type.')',"whatsapp",'utility',$usern,null,$branch);
            
              
 
        //         //reverse saving acct and current acct Gl
        //              if($cust->account_type == '1'){//saving acct GL
                     
        //              if($this->glsavingdacct->status == '1'){
        //             $this->gltransaction('withdrawal',$this->glsavingdacct,$amount,null);
        //             $this->create_saving_transaction_gl(null,$this->glsavingdacct->id,$branch, $amount,'credit',"whatsapp",null,$this->generatetrnxref('D'),'customer debited','approved',$usern,'');
        //             }

        //             }
        //             // elseif($cust->account_type == '2'){//current acct GL
                    
        //             //     if($glcurrentacct->status == '1'){
        //             //     $this->gltransaction('withdrawal',$glcurrentacct,$amount,null);
        //             // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$branch, $amount,'credit',"whatsapp",null,$this->generatetrnxref('D'),'customer debited','approved',$usern,'');
        //             //     }
                        
        //             // }
                    
        //      $this->tracktrails(null,$branch,$usern,'customer','Cable TV Subscription Purchased Failed('.$service_type.')','');

        //        $msg = "Credit Amt: N".number_format($amount,2)."<br> Desc: Debit Transaction Reversal(".$service_type.") <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d h:ia")."<br>Ref: ".$trnxid;
             
        //        $smsmsg = "Credit Amt: N".number_format($amount,2)."\n Desc: Debit Transaction Reversal(".$service_type.") \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date:" . date('Y-m-d h:ia') . "\n Ref: " . $trnxid;
    
        //     if($cust->enable_sms_alert){
        //         $this->sendSms($cust->phone,$smsmsg,$getsetvalue->active_sms,$branch);//send sms
        //         }

        //      if($cust->enable_email_alert){
        //      Email::create([
        //         'uuid' => Str::uuid(),
        //         'user_id' =>  $id,
        //         'subject' => ucwords($getsetvalue->company_name).' Credit Alert',
        //         'message' => $msg,
        //         'recipient' => $cust->email,
        //     ]);

        //      Mail::send(['html' => 'mails.sendmail'],[
        //          'msg' => $msg,
        //          'type' => 'Credit Transaction',
        //      ],function($mail)use($getsetvalue,$cust){
        //          $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
        //            $mail->to($cust->email);
        //          $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
        //      });
             
        //      }
 
                return "Cable TV Subscription Purchased Failed\n\nNote: Please delete pin after use.\n Reply 00 or back to return to Main Menu";
             }


        }elseif($service_type == "dstv" || $service_type == "gotv" || $service_type == "startimes"){

            $compbal = $this->validatecompanybalance($amount,'vas');
            if($compbal["status"] == false){
        
                $this->logInfo("validating company balance",$compbal);
            
           return $compbal["message"];
        }

            $chkcres = $this->checkCustomerRestriction($id,$branch);
            if($chkcres == true){
    
                $this->tracktrails(null,$branch,$usern,'customer','Account Restricted','');
                
                $this->logInfo("Customer Account Restricted",'');
     
               return 'Your Account Has Been Restricted. Please contact support';
            }
    
             $chklien = $this->checkCustomerLienStatus($id,$branch);
            if($chklien['status'] == true && $chklien['lien'] == 2){
                
                $this->tracktrails(null,$branch,$usern,'customer','Account has been lien','');
                
                $this->logInfo("validating lien status",$chklien);
                
             return 'Your Account Has Been Lien('.$chklien['messages'].')...please contact support';
            }
            
            $validateuserpin = $this->validatetrnxpin($pin,$id,$branch);
            if($validateuserpin["status"] == false){
                
                $this->tracktrails(null,$branch,$usern,'customer',$validateuserpin["message"],'');
                
                $this->logInfo("validating customer pin",$validateuserpin);
                
               return $validateuserpin["message"];
            }
    
            $validateuserbalance = $this->validatecustomerbalance($id,$amount,$branch);
            if($validateuserbalance["status"] == false){
    
                $this->tracktrails(null,$branch,$usern,'customer',$validateuserbalance["message"],'');
                
                $this->logInfo("validating Customer balance",$validateuserbalance);
                
                return $validateuserbalance["message"];
            }

            $percentage = $this->getUtilityPercentage();
            $prec = array();
             foreach($percentage as $percent){
                 if($percent["service"] == $service_type){
                     $prec = $percent;
                 }
             }
     
             $percentincome = $amount * $prec["value"] / 100;
             $totamount = $amount - $percentincome;
     
             $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$this->whtdta->getsettingskey('vtpass_income'))->first();
             
             if($glacct->status == '1'){

                $this->gltransaction('withdrawal',$glacct,$percentincome,null); 
               $this->create_saving_transaction_gl(null,$glacct->id,$branch, $percentincome,'credit',"whatsapp",null,$this->generatetrnxref('vt'),'vtpass '.$service_type.' percentage','approved',$usern,'');
              
             }
             
            $customerbal = $this->DebitCustomerandcompanyGlAcct($id,$amount,$totamount,$getsetvalue->getsettingskey('vtpass_account'),'vt','Cable TV Subscription('.$service_type.')',"whatsapp",$usern,$branch);
    
            $this->logInfo("debit customer response", $customerbal);
            
             if($cust->account_type ==  $savpd->id){//saving acct GL
                        
                        if($this->glsavingdacct->status == '1'){
                        $this->gltransaction('deposit',$this->glsavingdacct,$amount,null);
                    $this->create_saving_transaction_gl(null,$this->glsavingdacct->id,$branch,$amount,'debit',"whatsapp",null,$this->generatetrnxref('W'),'customer debited','approved',$usern,'');
                        }
                        
                    }
                    // elseif($cust->account_type == '2'){//current acct GL
                        
                    //     if($glcurrentacct->status == '1'){
                    //     $this->gltransaction('deposit',$glcurrentacct,$amount,null);
                    // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$branch,$amount,'debit',"whatsapp",null,$this->generatetrnxref('W'),'customer debited','approved',$usern,'');
                    //     }
                        
                    // }
        
         
            $cblbody = [
                'request_id' => $trnxid,
                'serviceID' => $service_type,
                'billersCode' => $smartcard_number,
                'variation_code' => $subcription_plan,
                'amount' => $amount,
                'phone' => $phone_number,
                'subscription_type' => 'change',
            ];

            $response = $this->vtpassposturl($endpoint,$cblbody);

            $this->logInfo("vtpass ".$service_type." response",$response);
               //  return $response; 
             if($response["code"] == "000"){

                if(isset($response["content"]["error"])){
                    $msg = $response['content']['error'];
                }

                //companybal
                $this->debitcreditCompanyBalance($this->whtappsesslog->amount,"debit","vas",$branch);
                    

                   $description = "Cable TV Subscription(".$service_type.") worth ".$amount;

                $this->create_saving_transaction(null, $id,$branch,$amount,'debit',"whatsapp",'0',null,null,null,null,$trnxid,$description,'approved','16','utility',$usern);

                $this->tracktrails(null,$branch,$usern,'customer','Cable TV Subscription Purchased Successfully('.$service_type.')','');

                $Emsg =  "Debit Amt: N".number_format($amount,2)."<br> Desc: Cable TV Subscription Successful(".$service_type.")  <br>Avail Bal: N".number_format($customerbal["balance"],2)."<br> Date: ".date("Y-m-d h:ia")."<br>Ref: ".$trnxid;
               
                $smsmsg = "Debit Amt: N".number_format($amount,2)."\n Desc: Cable TV Subscription Successful(".$service_type.") \n Avail Bal: N".number_format($customerbal["balance"],2)."\n Date:" . date('Y-m-d h:ia') . "\n Ref: " . $trnxid;
    
            if($cust->enable_sms_alert){
                $this->sendSms($cust->phone,$smsmsg,$getsetvalue->active_sms,$branch);//send sms
                }

               if($cust->enable_email_alert){
                Email::create([
                    'uuid' => Str::uuid(),
                    'user_id' =>  $id,
                    'subject' => ucwords($getsetvalue->company_name).' Debit Alert',
                    'message' => $Emsg,
                    'recipient' => $cust->email,
                ]);

                Mail::send(['html' => 'mails.sendmail'],[
                    'msg' => $msg,
                    'type' => 'Debit Transaction',
                ],function($mail)use($getsetvalue,$cust){
                    $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                     $mail->to($cust->email);
                    $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
                });
             }
             
                return "Cable TV Subscription Purchased Successfully\n\nNote: Please delete pin after use.\n Reply 00 or back to return to Main Menu";

             }else{
                
                $this->create_saving_transaction(null, $id,$branch,$amount,
                'debit',"whatsapp",'0',null,null,null,null,$trnxid,'Cable TV Subscription Purchased Failed('.$service_type.')','failed','16','utility',$usern);
                
                if($glacct->status == '1'){

                    $this->gltransaction('deposit',$glacct,$percentincome,null); 
                $this->create_saving_transaction_gl(null,$glacct->id,$branch, $percentincome,'debit',"whatsapp",null,$this->generatetrnxref('vt'),'vtpass '.$service_type.' percentage reversed','approved',$usern,'');
                
                }
            
        //      $reverd = $this->ReverseDebitTrnxandcompanyGlAcct($id,$amount,$totamount,$trnxid,$getsetvalue->vtpass_account,'vt','CableTv Transaction reversed('.$service_type.')',"whatsapp",'utility',$usern,null,$branch);
           
 
        //     //reverse saving acct and current acct Gl
        //              if($cust->account_type ==  $savpd->id){//saving acct GL
                     
        //                 if($this->glsavingdacct->status == '1'){
        //             $this->gltransaction('withdrawal',$this->glsavingdacct,$amount,null);
        //             $this->create_saving_transaction_gl(null,$this->glsavingdacct->id,$branch,$amount,'credit',"whatsapp",null,$this->generatetrnxref('D'),'customer debited','approved',$usern,'');
        //                 }
                        
        //             }
        //             // elseif($cust->account_type == '2'){//current acct GL
                        
        //             //     if($glcurrentacct->status == '1'){
        //             //     $this->gltransaction('withdrawal',$glcurrentacct,$amount,null);
        //             // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$branch,$amount,'credit',"whatsapp",null,$this->generatetrnxref('D'),'customer debited','approved',$usern,'');
        //             //     }
                        
        //             // }
                    
        //      $this->tracktrails(null,$branch,$usern,'customer','Cable TV Subscription Purchased Failed('.$service_type.')','');

        //     $msg = "Credit Amt: N".number_format($amount,2)."<br> Desc: Debit Transaction Reversal(".$service_type.") <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d h:ia")."<br>Ref: ".$trnxid;
            
        //     $smsmsg = "Credit Amt: N".number_format($amount,2)."\n Desc: Debit Transaction Reversal(".$service_type.") \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date:" . date('Y-m-d h:ia') . "\n Ref: " . $trnxid;
    
        //     if($cust->enable_sms_alert){
        //         $this->sendSms($cust->phone,$smsmsg,$getsetvalue->active_sms,$branch);//send sms
        //         }

        //     if($cust->enable_email_alert){
        //      Email::create([
        //         'uuid' => Str::uuid(),
        //          'user_id' => $id,
        //          'subject' => ucwords($getsetvalue->company_name).' Credit Alert',
        //          'message' => $msg,
        //          'recipient' => $cust->email,
        //      ]);

    
        //      Mail::send(['html' => 'mails.sendmail'],[
        //          'msg' => $msg,
        //          'type' => 'Credit Transaction',
        //      ],function($mail)use($getsetvalue,$cust){
        //          $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
        //            $mail->to($cust->email);
        //          $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
        //      });
        // }
                return "Cable TV Subscription Purchased Failed\n\nNote: Please delete pin after use.\n Reply 00 or back to return to Main Menu";
             }
        }

    }

    public function verifySmartCard($service_type,$smartcard_number){
        $endpoint = $this->vtpassurl."merchant-verify";

        $body=[
            'billersCode' => $smartcard_number,
            'serviceID' => $service_type
        ];

         $response = $this->vtpassposturl($endpoint,$body);

         $this->logInfo("",$response);


        if (isset($response["code"])) {
            if ($response["code"] == "000") {

                if (isset($response['content']['error'])) {

                    return $response['content']['error']."\n\n Reply 00 or back to return to Main Menu";
                    
                }

                $sbud =  $service_type == "startimes" ? "" : "Current Bouquet: ".trim($response['content']['Current_Bouquet']);

                return "Smart Card Verified Successfully\n\n Name: ".trim($response['content']['Customer_Name'])."\n".$sbud;
            } else {

                return "Failed to verify smart card\n\n Reply 00 or back to return to Main Menu";
            }
        } else {
            return  "Failed to verify smart card\n\n Reply 00 or back to return to Main Menu";
        }
     }

    public function verify_meter_number($service_provider,$meter_number,$meter_type)
    {
        $message ="";
        $this->logInfo("validating meter number",[$service_provider, $meter_number,$meter_type]);
        

        $trnxid = $this->vtpassrequestid();
       
        $endpoint = $this->vtpassurl."merchant-verify";

        $body = [
            'billersCode' => $meter_number,
            'serviceID' => $service_provider,
            'type' => $meter_type,
        ];

        $response = $this->vtpassposturl($endpoint,$body);
        
        $this->logInfo("meter response",$response);
  //return $response;
        if($response['code'] == "000"){

            if(isset($response["content"]["error"])){
            
               return $response["content"]["error"]."\n\n Reply 00 or back to return to Main Menu";
            }

            $meter_number = $service_provider == "abuja-electric" ? $response['content']['MeterNumber'] : $response['content']['Meter_Number'];
            return "Meter Number Verified Successfully \n\n Customer Name: ".trim($response['content']['Customer_Name'])."\n Meter Number: ".$meter_number."\n Address: ".trim($response['content']['Address']);
         }else{
           return "failed to verified meter Number\n\n Reply 00 or back to return to Main Menu";
         }
     
    }

    public function pay_electricity($id,$usernme,$meter_number,$amount,$phone_number,$service_provider,$meter_type,$pin,$branch)
    {
        $message ="";
        $this->logInfo("buy electricity",[$meter_number,$amount,$phone_number,$service_provider,$meter_type,]);
        
        $cust = Customer::where('id',$id)->first();

          $usern = $usernme;

          $getsetvalue = new Setting();
           
        if($amount < "500"){
            return "Invalid Amount Entered... amount must be 500 and above\n\n Reply 00 or back to return to Main Menu";
        }

        $trnxid = $this->vtpassrequestid();
      
        $compbal = $this->validatecompanybalance($amount,'vas');
        if($compbal["status"] == false){
    
            $this->logInfo("validating company balance",$compbal);
        
            return $compbal['message']."\n\n Reply 00 or back to return to Main Menu";
        }

        $chkcres = $this->checkCustomerRestriction($id,$branch);
        if($chkcres == true){
    
            $this->tracktrails(null,$branch,$usern,'customer','Account Restricted','');

            $this->logInfo("Customer Account Restricted",'');
            return "Your Account Has Been Restricted. Please contact support\n\n Reply 00 or back to return to Main Menu";
        }
        
        $chklien = $this->checkCustomerLienStatus($id,$branch);
            if($chklien['status'] == true && $chklien['lien'] == 2){
                
                $this->tracktrails(null,$branch,$usern,'customer','Account has been lien','');
                
                $this->logInfo("validating lien status",$chklien);
                
              return "Your Account Has Been Lien(".$chklien['messages'].")...please contact support\n\n Reply 00 or back to return to Main Menu";
            }

        $validateuserpin = $this->validatetrnxpin($pin,$id,$branch);
        if($validateuserpin["status"] == false){
            
            $this->tracktrails(null,$branch,$usern,'customer',$validateuserpin["message"],'');
            
            $this->logInfo("validating customer pin",$validateuserpin);
    
            return $validateuserpin['message']."\n\n Reply 00 or back to return to Main Menu";
        }

        $validateuserbalance = $this->validatecustomerbalance($id,$amount,$branch);
        if($validateuserbalance["status"] == false){

            $this->tracktrails(null,$branch,$usern,'customer',$validateuserbalance["message"],'');
            
            $this->logInfo("validating custome balance",$validateuserbalance);
            
           return $validateuserbalance['message']."\n\n Reply 00 or back to return to Main Menu";
        }

        $percentage = $this->getUtilityPercentage();
        $prec = array();
         foreach($percentage as $percent){
             if($percent["service"] == $service_provider){
                 $prec = $percent;
             }
         }
 
         $percentincome = $amount * $prec["value"] / 100;
         $totamount = $amount - $percentincome;
      
         $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$this->whtdta->getsettingskey('vtpass_income'))->first();
         
         if($glacct->status == '1'){

             $this->gltransaction('withdrawal',$glacct,$percentincome,null);
           $this->create_saving_transaction_gl(null,$glacct->id,$branch,$percentincome,'credit',"whatsapp",null,$this->generatetrnxref('vt'),'vtpass electricity percentage','approved',$usern,'');
           
         }
         
        $customerbal = $this->DebitCustomerandcompanyGlAcct($id,$amount,$totamount,$getsetvalue->getsettingskey('vtpass_account'),'vt','Purchase of Electricity Unit',"whatsapp",$usern,$branch);

        $this->logInfo("debit customer response", $customerbal);
        
         if($cust->account_type == '1'){//saving acct GL
                        
            if($this->glsavingdacct->status == '1'){
                    $this->gltransaction('deposit',$this->glsavingdacct,$amount,null);
                $this->create_saving_transaction_gl(null,$this->glsavingdacct->id,$branch, $amount,'debit',"whatsapp",null,$this->generatetrnxref('W'),'customer debited','approved',$usern,'');
            }
            
                }
                // elseif($cust->account_type == '2'){//current acct GL
                  
                //   if($glcurrentacct->status == '1'){  
                //     $this->gltransaction('deposit',$glcurrentacct,$amount,null);
                // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$branch, $amount,'debit',"whatsapp",null,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                //   }
                  
                // }
            
        $endpoint = $this->vtpassurl."pay";

        $body = [
            "request_id" => $trnxid,
            "billersCode" => $meter_number,
            "serviceID" => $service_provider,
            "variation_code" => $meter_type,
            "amount" => $amount,
            "phone" => $phone_number
        ];

        $response = $this->vtpassposturl($endpoint,$body); 
        
        $this->logInfo('',$response);
        
    //return $response;
        if($response['code'] == "000"){
            $description =  $meter_type == "prepaid" ? "Purchased Electricity Token: ".$response["purchased_code"] : "Purchased Electricity Worth Amount: ".$amount;
             $unit = $meter_type == "prepaid" ? "Unit: ".$response['units'] : "";

            if ($response['content']['transactions']['status'] == 'pending') {

                return "Transaction is Processing. you will receive a token shortly \n\n Reply 00 or back to return to Main Menu";
                
            } 

            //companybal
            $this->debitcreditCompanyBalance($this->whtappsesslog->amount,"debit","vas",$branch);
                    

            $this->create_saving_transaction(null, $id,$branch,$amount,'debit',"whatsapp",'0',null,null,null,null,
            $trnxid,$description,'approved','17','utility',$usern.'(c)');

            $this->tracktrails(null,$branch,$usern,'customer',$description,'');
    
            $desctn = $meter_type == 'prepaid' ? "Electricity Unit Purchased Successfully<br><br>Token:".$response["purchased_code"]."<br>Units: ".$unit : "";
           
            $msg = "Debit Amt: N".number_format($amount,2)."<br> Desc:".$desctn." <br>Avail Bal: N".number_format($customerbal["balance"])."<br> Date: ".date("Y-m-d h:ia")."<br>Ref: ".$trnxid."<br>".$description;
            
            $smsmsg = "Debit Amt: N".number_format($amount,2)."\n Desc: Electricity Unit Purchased Successfully \n Avail Bal: N".number_format($customerbal["balance"],2)."\n Date:" . date('Y-m-d h:ia') . "\n Ref: " . $trnxid."\n\n".$description;
    
        if($cust->enable_sms_alert){
            $this->sendSms($cust->phone,$smsmsg,$getsetvalue->active_sms,$branch);//send sms
            }

            if($cust->enable_email_alert){
            Email::create([
                'uuid' => Str::uuid(),
                'user_id' => $id,
                'subject' => ucwords($getsetvalue->company_name).' Debit Alert',
                'message' => $msg,
                'recipient' => $cust->email,
            ]);
   
            Mail::send(['html' => 'mails.sendmail'],[
                'msg' => $msg,
                'type' => 'Debit Transaction',
               ],function($mail)use($getsetvalue,$cust){
                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                  $mail->to($cust->email);
                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
            });
        }
        
           return "Electricity Unit Purchased Successfully\n".$response["purchased_code"]."\n".$unit."\n\n Reply 00 or back to return to Main Menu";

         }else{

            
            $this->create_saving_transaction(null,$id,$branch,$amount,
            'debit',"whatsapp",'0',null,null,null,null,$trnxid,'Failed to Purchase Electricity Unit','failed','17','utility',$usern);

if($glacct->status == '1'){

             $this->gltransaction('deposit',$glacct,$percentincome,null); 
            $this->create_saving_transaction_gl(null,$glacct->id,$branch, $percentincome,'debit',"whatsapp",null,$this->generatetrnxref('vt'),'vtpass electricity percentage reversed','approved',$usern,'');
           
}


        //  $reverd = $this->ReverseDebitTrnxandcompanyGlAcct($id,$amount,$totamount,$trnxid,$getsetvalue->vtpass_account,'vt','Electricity Unit Purchased Transaction reversed',"whatsapp",'utility',$usern,null,$branch);
        
          

        //     //reverse saving acct and current acct Gl
        //              if($cust->account_type == '1'){//saving acct GL
                        
        //                 if($this->glsavingdacct->status == '1'){
        //             $this->gltransaction('withdrawal',$this->glsavingdacct,$amount,null);
        //             $this->create_saving_transaction_gl(null,$this->glsavingdacct->id,$branch, $amount,'credit',"whatsapp",null,$this->generatetrnxref('D'),'customer debited','approved',$usern,'');
        //                 }
                        
        //             }
        //             // elseif($cust->account_type == '2'){//current acct GL
                        
        //             //     if($glcurrentacct->status == '1'){
        //             //     $this->gltransaction('withdrawal',$glcurrentacct,$amount,null);
        //             // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$branch,$amount,'credit',"whatsapp",null,$this->generatetrnxref('D'),'customer debited','approved',$usern,'');
        //             //     }
                        
        //             // }
        
        //  $this->tracktrails(null,$branch,$usern,'customer','Failed to Purchase Electricity Unit','');

        //   $msg = "Credit Amt: N".number_format($amount,2)."<br> Desc: Debit Transaction Reversal for Electricity Purchase <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d h:ia")."<br>Ref: ".$trnxid;
        //   $smsmsg = "Credit Amt: N".number_format($amount,2)."\n Desc: Debit Transaction Reversal for Electricity Purchase \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date:" . date('Y-m-d h:ia') . "\n Ref: " . $trnxid;
    
        // if($cust->enable_sms_alert){
        //     $this->sendSms($cust->phone,$smsmsg,$getsetvalue->active_sms,$branch);//send sms
        //     }

        //  if($cust->enable_email_alert){
        //  Email::create([
        //     'uuid' => Str::uuid(),
        //      'user_id' => $id,
        //      'subject' => ucwords($getsetvalue->company_name).' Credit Alert',
        //      'message' => $msg,
        //      'recipient' => $cust->email,
        //  ]);

        //  Mail::send(['html' => 'mails.sendmail'],[
        //      'msg' => $msg,
        //      'type' => 'Credit Transaction',
        //     ],function($mail)use($getsetvalue,$cust){
        //      $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
        //        $mail->to($cust->email);
        //      $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
        //  });
        //  }
         
         return "Failed to Purchase Electricity Unit\n\n Reply 00 or back to return to Main Menu";
         }

    }

    public function validateMonnifyBalance($accountno,$amout){
        $response = [];

        $this->logInfo("check balance Url",$this->url."v2/disbursements/wallet-balance?accountNumber=".$accountno);

        $authbasic = base64_encode($this->apikey.":".$this->sercetkey);
           $checkbalanace = Http::withHeaders([
               "Authorization" => "Basic ".$authbasic
           ])->get($this->url."v2/disbursements/wallet-balance?accountNumber=".$accountno)->json();
           
           $this->logInfo("validating monnify balance",$checkbalanace);
           //return $checkbalanace;
      
           if($checkbalanace["responseBody"]["availableBalance"] < $amout){
                $response = ["status" => false, 'message' => "Switcher Error... Please contact support"];
           }else{
                $response = ["status" => true,'message' => "Amount is Valid",];
           }
           return $response;
   }
   
     public function validateWirelessBalance($url,$amout,$apikey){
    //return $this->url."verification/get-wallet-balance";
     $response = [];
     
     $this->logInfo("check balance Url",$url."verification/get-wallet-balance");

        $checkbalanace = Http::withHeaders([
            "ApiKey" => $apikey
        ])->get($url."verification/get-wallet-balance")->json();
        
        $this->logInfo("validating wireless balance",$checkbalanace);
        //return $checkbalanace;
   
        if($checkbalanace["data"]["balance"] < $amout){
             $response = ["status" => false, 'message' => "Switcher Error... Please contact support"];
        }else{
             $response = ["status" => true,'message' => "Amount is Valid",];
        }
        return $response;
}

public function responsemsg($msg,$phn,$brnhid){//whatsapp response

    $phone_number = $this->whtdta->getsettingskey('whatsapp_phone');
    $username = $this->whtdta->getsettingskey('whatsapp_username');
    $apikey = $this->whtdta->getsettingskey('whatsapp_api_key');

//%7B%22type%22:%22text%22,%22text%22:%22%22%7D

    $curl = curl_init();
 
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.gupshup.io/wa/api/v1/msg',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "channel=whatsapp&source=".$phone_number."&destination=".$phn."&message=".$msg."&src.name=" .$username,
            CURLOPT_HTTPHEADER => array(
                'Cache-Control: no-cache',
                'Content-Type: application/x-www-form-urlencoded',
                'apikey: '.$apikey,
                'cache-control: no-cache'
            ),
        ));
        curl_exec($curl);
        curl_close($curl);
    
    return 0;
}

}//endclass
