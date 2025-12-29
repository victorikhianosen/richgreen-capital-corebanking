<?php

namespace App\Http\Controllers;

use App\Http\Traites\AuditTraite;
use App\Models\PaymentStructure;
use App\Models\Payroll;
use App\Models\PayrollTemplate;
use App\Models\Payslip;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PayrollController extends Controller
{
    use AuditTraite;
    public function __construct()
    {
       $this->middleware('auth'); 
    }

    public function index(){
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        return view('payroll.index')->with('payrolls',Payroll::orderBy('created_at','DESC')->get());    
    }

    public function create(){
       return view('payroll.create')->with('banks',DB::table('banks')->select('bank_name')->get());
    }

    public function store(Request $r){
        $this->validate($r,[
            'employee_name' => ['required','string'],
            'designation' => ['required','string'],
            'payment_method' => ['required','string'],
            'bank_name' => ['required','string'],
            'account_number' => ['required','string'],
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        Payroll::create([
            'user_id' => Auth::user()->id,
            'branch_id' => $branch,
            'employee_name' => $r->employee_name,
            'email' => $r->employee_email,
            'designation' => $r->designation,
            'payment_method' => $r->payment_method,
            'bank_name' => $r->bank_name,
            'account_number' => $r->account_number
        ]);
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'payroll','Created payroll record');

        return redirect()->route('payroll.index')->with('success','Record Created');
    }

    public function edit($id){
          return view('payroll.edit')->with('ed',Payroll::findorfail($id))
                                    ->with('banks',DB::table('banks')->select('bank_name')->get());
    }

    public function update(Request $r,$id){
        $this->validate($r,[
            'employee_name' => ['required','string'],
            'designation' => ['required','string'],
            'payment_method' => ['required','string'],
            'bank_name' => ['required','string'],
            'account_number' => ['required','string'],
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

            $updatpayroll = Payroll::findorfail($id);
            $updatpayroll->update([
                'user_id' => Auth::user()->id,
                'branch_id' => $branch,
                'employee_name' => $r->employee_name,
                'email' => $r->employee_email,
                'designation' => $r->designation,
                'payment_method' => $r->payment_method,
                'bank_name' => $r->bank_name,
                'account_number' => $r->account_number
            ]);
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'payroll','Updated payroll record');
       
        return redirect()->route('payroll.index')->with('success','Record Updated');
    }

    public function delete($id){
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;
        Payroll::findorfail($id)->delete();

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'payroll','Deleted payroll');

        return redirect()->back()->with('success','Payroll Deleted');
    }

    public function create_template(){
        return view('payroll.payroll_template')->with('adddata',PayrollTemplate::where('position','1')->get())
                                             ->with('deddata',PayrollTemplate::where('position','2')->get());
    }

    public function store_template(Request $r){
        if($r->methodtype == "store"){
            $this->validate($r,[
                'name' => ['required','string'],
                'position' => ['required','string'],
                'template_status' => ['required','string'],
            ]);

            PayrollTemplate::create([
                'name' => $r->name,
                'position' => $r->position,
                'status' => $r->template_status
            ]);
            return redirect()->back()->with('success','Record Added');

        }elseif($r->methodtype == "edit"){
            PayrollTemplate::where('id',$r->pid)->update([
                'name' => $r->name,
                'position' => $r->position,
                'status' => $r->template_status
            ]);

            return redirect()->back()->with('success','Record Updated');
        }
    }

    public function delete_template($id){
        PayrollTemplate::findorfail($id)->delete();
        return redirect()->back()->with('success','Record Deleted');
    }

    //payment structure
    public function payment_structure(){
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

       return view('payroll.payroll_structure')->with('payrolls',Payroll::orderBy('created_at','DESC')->get())
                                                ->with('paddit',PayrollTemplate::where('position','1')->get())
                                                ->with('pdeduc',PayrollTemplate::where('position','2')->get())
                                                ->with('pstrus', PaymentStructure::all());
    }
  
    public function payment_structure_store(Request $r){
        $this->validate($r,[
            'basic_salary.*' => ['required','numeric'],
            'other_allowances.*' => ['required','numeric'],
            'paye.*' => ['required','numeric'],
            'other_deductions.*' => ['required','numeric'],
        ]);
        
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        foreach($r->staffid as $key => $staffid){
            $checkstid = PaymentStructure::where('payroll_id',$staffid)->first();
            if(empty($checkstid)){
                PaymentStructure::create([
                    'payroll_id' => $staffid,
                    'branch_id' => $branch,
                    'basic' => $r->basic_salary[$key],
                    'other_allowance' => $r->other_allowances[$key],
                    'gross_pay' => $r->gross_pay[$key],
                    'paye' => $r->paye_percent[$key],
                    'paye_percent' => $r->paye[$key],
                    'other_deduction' => $r->other_deductions[$key],
                    'deduction' => $r->deduction[$key],
                    'net_pay' => $r->netpay[$key]
                ]);
            }else{
                PaymentStructure::where('id',$checkstid->id)->update([
                    'basic' => $r->basic_salary[$key],
                    'other_allowance' => $r->other_allowances[$key],
                    'gross_pay' => $r->gross_pay[$key],
                    'paye' => $r->paye_percent[$key],
                    'paye_percent' => $r->paye[$key],
                    'other_deduction' => $r->other_deductions[$key],
                    'deduction' => $r->deduction[$key],
                    'net_pay' => $r->netpay[$key]
                ]);
            }
        }

        return redirect()->back()->with('success','Payment Structure Created');
    }

    public function payment_structure_edit($id){
        return view('payroll.payment_structure_edit')->with('ed',PaymentStructure::findorfail($id));
    }

    public function payment_structure_update(Request $r,$id){
        $this->validate($r,[
            'basic_salary' => ['required','numeric'],
            'other_allowances' => ['required','numeric'],
            'paye' => ['required','numeric'],
            'other_deductions' => ['required','numeric'],
        ]);

        PaymentStructure::where('id',$id)->update([
            'basic' => $r->basic_salary,
            'other_allowance' => $r->other_allowances,
            'gross_pay' => $r->gross_pay,
            'paye' => $r->paye_percent,
            'paye_percent' => $r->paye,
            'other_deduction' => $r->other_deductions,
            'deduction' => $r->deduction,
            'net_pay' => $r->netpay
        ]);

        return redirect()->route('payment.structure')->with('success','Payment Structure Updated');

    }

    public function payslips(){
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;
        return view('payroll.payslips')->with('payslips',Payslip::orderBy('created_at','DESC')->get());
    }

    public function payslips_send_mail(Request $r){
        $getsetvalue = new Setting();
        
        $dateObj   = DateTime::createFromFormat('!m', $r->month);
        $monthName = $dateObj->format('F');
        
        if($r->emailtype == 'multiple'){
            foreach($r->payslipid as $payslipid){
                $payslips = Payslip::findorfail($payslipid);
                $data = [
                    'title' => $getsetvalue->getsettingskey('company_name')." Payslip",
                    'date' => date('m/d/Y'),
                    'payslips' => $payslips,
                    'monthName' => $monthName
                    ];
                
                $pdf = Pdf::loadView("payroll.pdf_print_payslip", $data);
                //$content = $pdf->download()->getOriginalContent();
                $filename = time().'_payslip.pdf';
                $pdfcontent = $pdf->output();
                file_put_contents($filename,$pdfcontent);
               
                $getpdf_file = $filename;
                //(ucfirst($borrower->title)." ".$borrower->first_name." ".$borrower->last_name." - Client Statement.pdf");
        
                if(!empty($payslips->payroll->email)){
                    Mail::send(['html' => 'mails.sendmail'],[
                        'msg' => "Hello ".$payslips->payroll->employee_name."<br> Below is your payslip for the month of ".$monthName
                    ],function($mail)use($payslips,$monthName,$getsetvalue,$getpdf_file){
                        $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                         $mail->to($payslips->payroll->email);
                        $mail->subject($monthName." payslip");
                        $mail->attach($getpdf_file);
                    });
                }
                
               } 
               
        return redirect()->back()->with('success', 'Payslip Sent');

        }elseif($r->emailtype == 'single'){
            $payslips = Payslip::findorfail($r->payid);
                $data = [
                    'title' => $getsetvalue->getsettingskey('company_name')." Payslip",
                    'date' => date('m/d/Y'),
                    'payslips' => $payslips,
                    'monthName' => $monthName
                    ];
                
                $pdf = Pdf::loadView("payroll.pdf_print_payslip", $data);
                //$content = $pdf->download()->getOriginalContent();
                $filename = time().'_payslip.pdf';
                $pdfcontent = $pdf->output();
                file_put_contents($filename,$pdfcontent);
               
                $getpdf_file = $filename;
                //(ucfirst($borrower->title)." ".$borrower->first_name." ".$borrower->last_name." - Client Statement.pdf");
        
                if(!empty($payslips->payroll->email)){
                    Mail::send(['html' => 'mails.sendmail'],[
                        'msg' => "Hello ".$payslips->payroll->employee_name."<br> Below is your payslip for the month of ".$monthName
                    ],function($mail)use($payslips,$monthName,$getsetvalue,$getpdf_file){
                        $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                         $mail->to($payslips->payroll->email);
                        $mail->subject($monthName." payslip");
                        $mail->attach($getpdf_file);
                    });
                }

                return redirect()->back()->with('success', 'Payslip Sent');
        }else{
            return redirect()->back()->with('success', 'Payslip Not Found');
        }
       
    }

    public function payslip_generate(){
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        return view('payroll.generate_payslip')->with('pstrus', PaymentStructure::all());
    }

    public function payslip_save(Request $r){
        $mth = "";
        $branch = session()->has('branchid') ? session()->get('branchid') : null;

       foreach($r->staffid as $key => $staffid){
            $checkslip = Payslip::where('payroll_id',$staffid)
                                ->where('month',$r->month)
                                ->where('year',$r->year)->first();
            if(empty($checkslip)){
                Payslip::create([
                    'payroll_id' => $staffid,
                    'payment_structure_id' => $r->paystruid[$key],
                    'branch_id' => $branch,
                    'month' => $r->month,
                    'year' => $r->year
                ]);
            }else{
                return redirect()->route('payslip.generate')->with('error','Payslip already recorded for '.date("M Y",strtotime('01-'.$r->month.'-'.$r->year)));
            }
        }
        
        return redirect()->route('payslip.generate')->with('success','Payslip created for '.date("M Y",strtotime('01-'.$r->month.'-'.$r->year)));
    }

    public function payslip_view(){
        $payslips = Payslip::where('payroll_id',request()->payid)->get();
        $payrol = Payroll::findorfail(request()->payid);
        return view('payroll.payslip_show')->with('payslips',$payslips)
                                          ->with('payrol',$payrol);
    }

    public function pdf_print_payslip(){
        $payslips = Payslip::findorfail(request()->payid);
// dd($payslips);
        $dateobj = DateTime::createFromFormat('!m',$payslips->month);
        $monthName = $dateobj->format('F');

        return view('payroll.pdf_print_payslip')->with('payslips',$payslips)
                                                ->with('monthName',$monthName);
    }
}//endclass
