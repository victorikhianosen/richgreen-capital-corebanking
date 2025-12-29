<?php

namespace App\Http\Controllers;

use App\Http\Traites\AuditTraite;
use App\Http\Traites\UserTraite;
use App\Models\Customer;
use App\Models\Email;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    use AuditTraite;
     use UserTraite;
    public function __construct()
    {
       $this->middleware('auth'); 
    }
    
    public function manage_mail(){
        return view('communicate.manage_emails')->with('emails',Email::orderBy('created_at','DESC')->get());
    }

    
    public function create_mail($id=null){
        $getemail = "";
       if(!is_null($id) && request()->sendmail == true){
            $getemail = Email::select('recipient')->where('id',$id)->first(); 
            return view('communicate.create_emails')->with('remail', $getemail->recipient)
                                               ->with('cusem',Customer::select('email')->where('status','1')->get());
        }else{
            return view('communicate.create_emails') ->with('cusem',Customer::select('email')->where('status','1')->get());
        }
    }

 public function create_sms($id=null){
        $getemail = "";
       if(!is_null($id) && request()->sendsms == true){
        $cusromers = Customer::select('first_name','last_name','phone')
                    ->where('id',$id)
                    ->where('status','1')->first();

            return view('communicate.create_sms')->with('cusms',$cusromers);
        }else{
            return view('communicate.create_sms') ->with('cusms',Customer::select('first_name','last_name','phone')->where('status','1')->get());
        }
    }
    
    public function view_mail($id){
        return view('communicate.show_emails')->with('ems',Email::findorfail($id));
    }

    public function sendmail(Request $r){
        $this->validate($r,[
            'subject' => ['required','string']
        ]);
        $getsetvalue = new Setting();
        
        if($r->selectall == "1"){
            
            foreach ($r->mail_to as $evalue) {
                Mail::send(['html' => 'mails.sendmail'],[
                    'msg' => $r->message,
                    'type' => ""
                ],function($mail)use($evalue,$r,$getsetvalue){
                    $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                     $mail->to($evalue);
                    $mail->subject($r->subject);
                });
    
                Email::create([
                    'user_id' => $r->userid,
                    'branch_id' => $r->branchid,
                    'subject' => $r->subject,
                    'message' => $r->message,
                    'recipient' => $evalue,
                ]);
            }
            
        }else{
                Mail::send(['html' => 'mails.sendmail'],[
                    'msg' => $r->message,
                    'type' => ""
                ],function($mail)use($r,$getsetvalue){
                    $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                     $mail->to($r->mail);
                    $mail->subject($r->subject);
                });
    
                Email::create([
                    'user_id' => $r->userid,
                    'branch_id' => $r->branchid,
                    'subject' => $r->subject,
                    'message' => $r->message,
                    'recipient' => $r->mail,
                ]);
            
        }
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'communication','sent email message');

        return redirect()->route('emails.index')->with('success','Message Sent');
    }

    public function delete_mail($id){
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        Email::findorfail($id)->delete();

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'communication','deleted email message');
        return redirect()->back()->with('success','Message Deleted');
    }
    
    public function sendSms(Request $r){
        $getsetvalue = new Setting();

        if($r->selectall == "1"){

            $this->validate($r,[
                'message' => ['required','string','min:3','max:160'],
            ]);

            $smsgs = Customer::select('id','phone')->get();

            foreach($smsgs as $sms){
                $this->sendSms($sms->phone,$r->message,$getsetvalue->getsettingskey('active_sms'));//send sms
            }

        }else{

            if($r->sendsms == true){
                $this->sendSms($r->phone,$r->message,$getsetvalue->getsettingskey('active_sms'));//send sms
            }else{
                $this->validate($r,[
                    'phones' => ['required'],
                    'message' => ['required','string','min:3','max:160'],
                ]);

                foreach($r->phones as $sms){
                    $this->sendSms($sms,$r->message,$getsetvalue->getsettingskey('active_sms'));//send sms
                }  
                
            }
            

        }

        Email::create([
            'user_id' => $r->userid,
            'branch_id' => $r->branchid,
            'subject' => "sms",
            'message' => $r->message,
            'recipient' => null,
        ]);
        
        return ['status' => 'success','msg' => 'SMS Message Sent', 'uredirect' => route('emails.index')];
    }
}//end class
