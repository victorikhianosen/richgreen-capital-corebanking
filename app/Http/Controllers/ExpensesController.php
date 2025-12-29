<?php

namespace App\Http\Controllers;

use App\Http\Traites\AuditTraite;
use App\Http\Traites\SavingTraite;
use App\Http\Traites\UserTraite;
use App\Models\Expenses;
use App\Models\ExpenseType;
use App\Models\GeneralLedger;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpensesController extends Controller
{
    use AuditTraite;
    use SavingTraite;
    use UserTraite;
    public function __construct()
    {
       $this->middleware('auth'); 
    }

    public function index(){
        return view('expenses.index')->with('expenses',Expenses::orderBy('created_at','DESC')->get());
    }

    public function create(){
        return view('expenses.create')->with('exptypes',ExpenseType::all())
                                        ->with('expns',GeneralLedger::where('gl_type','expense')->where('status','1')->get())
                                        ->with('crgls',GeneralLedger::whereNot('gl_type','expense')->where('status','1')->get());
    }

    public function store(Request $r) {
        $this->logInfo("expenses",$r->all());
        
        $this->validate($r,[
            'expense_types' => ['required','string'],
            'amount' => ['required','string','gt:0'],
            'expensegl' => ['required','string'],
            'creditgl' => ['required','string'],
        ]);
        
        $getsetvalue = new Setting();
        
          $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;
                $usern = Auth::user()->last_name." ".Auth::user()->first_name;

        $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$r->expensegl)->lockForUpdate()->first();//expense GL
        $glacctitll = GeneralLedger::select('id','status','gl_type','account_balance')->where('gl_code',$r->creditgl)->lockForUpdate()->first();
        
        if($glacctitll->gl_type == "liability" || $glacctitll->gl_type == "capital" || $glacctitll->gl_type == "income"){

            if($glacctitll->account_balance >= $r->amount){

                $this->create_saving_transaction_gl(null,$glacctitll->id,null,$r->amount,'credit','core',null,$this->generatetrnxref('exp'),'expenses','approved',$usern);
                $this->gltransaction('withdrawal',$glacctitll,$r->amount,null);

            }else{
                return ['status' => false,'msg' => 'insufficient GL Balance'];
            }
        }else{
            $this->create_saving_transaction_gl(null,$glacctitll->id,null,$r->amount,'credit','core',null,$this->generatetrnxref('exp'),'expenses','approved',$usern);
            $this->gltransaction('deposit',$glacctitll,$r->amount,null);
        }
        
    
        $exp = Expenses::create([
            'user_id' => Auth::user()->id,
            'expense_type_id' => $r->expense_types,
            'branch_id' => $branch,
            'amount' => $r->amount,
            'expslip' => '2346',
            'date' => $r->date,
            'expense_account' => $r->expensegl,
            'credit_account' => $r->creditgl,      
            'note' => $r->note,
            'branch' => $branch
        ]);

         // 'recurring' => $r->recurring,
            // 'recur_frequency' => $r->recurring == '1' ? $r->recurring_frequency : null,
            // 'recur_start_date' => $r->recurring == '1' ? $r->recurring_start : null,
            // 'recur_end_date' => $r->recurring == '1' ? $r->recurring_end : null,
            // 'recur_next_date' => $r->recurring == '1' ? Carbon::now()->addDays($r->recurring_frequency) : null,
            // 'recur_type' => $r->recurring == '1' ? $r->recurring_type : null,   
        // if($r->hasFile('files')){
        //     $filename = $r->file('files');
        //     $newfilename = time()."_".$filename->getClientOriginalName();
        //     $filename->move('uploads',$newfilename);

        //     Expenses::where('id', $exp->id)->update([
        //         'file' => 'uploads/'.$newfilename
        //     ]);
        // }
  
       $this->create_saving_transaction_gl(null,$glacct->id,null,$r->amount,'debit','core',null,$this->generatetrnxref('exp'),'expenses','approved',$usern);
       $this->gltransaction('withdrawal',$glacct,$r->amount,null);
           
        
        $this->tracktrails(Auth::user()->id,$branch,$usern,'expenses','created new expense record');

        
           
        return ['status' => 'success','msg' => 'Record Created','uredirct' => route('expenses.index')];
      
    }

    public function edit($id){
        return view('expenses.edit')->with('exptypes',ExpenseType::all())
                                ->with('exp', Expenses::findorfail($id));
    }

    public function update(Request $r, $id){
                $this->logInfo("expenses update",$r->all());

        $this->validate($r,[
            'expense_types' => ['required','string'],
            'amount' => ['required','string','gt:0'],
            'slip_no' => ['required','string'],
            'recurring' => ['required','string'],
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $getsetvalue = new Setting();

        $upepx = Expenses::findorfail($id);
        
        if($r->hasFile('file')){
            if(file_exists($upepx->file)){
                unlink($upepx->file);
            }
            $filename = $r->file('file');
            $newfilename = time()."_".$filename->getClientOriginalName();
            $filename->move('uploads',$newfilename);
            $upepx->file = 'uploads/'.$newfilename;
        }

        $upepx->update([
            'expense_type_id' => $r->expense_types,
            'amount' => $r->amount,
            'expslip' => $r->slip_no,
            'date' => $r->date,
            'recurring' => $r->recurring,
            'recur_frequency' => $r->recurring == '1' ? $r->recurring_frequency : null,
            'recur_start_date' => $r->recurring == '1' ? $r->recurring_start : null,
            'recur_end_date' => $r->recurring == '1' ? $r->recurring_end : null,
            'recur_next_date' => $r->recurring == '1' ? Carbon::now()->addDays($r->recurring_frequency) : null,
            'recur_type' => $r->recurring == '1' ? $r->recurring_type : null,         
            'note' => $r->note,
            'branch' => $branch
        ]);
        
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;

        $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','50814096')->first();//expense
        $glacctitll = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('till_account'))->first();

         
       $this->create_saving_transaction_gl(null,$glacct->id,null,$r->current_amount,'credit','core',null,$this->generatetrnxref('exp'),'expenses','approved',$usern);
       $this->gltransaction('deposit',$glacct,$r->current_amount,null);

        $this->tracktrails(Auth::user()->id,$branch,$usern,'expenses','updated an expense record');

 $this->create_saving_transaction_gl(null,$glacctitll->id,null,$r->current_amount,'debit','core',null,$this->generatetrnxref('exp'),'expenses','approved',$usern);
         $this->gltransaction('withdrawal',$glacctitll,$r->current_amount,null);
           
           $this->updateExpenseGl($branch,$usern,$r->amount);
        return redirect()->route('expenses.index')->with('success','Record Updated');

    }

   public function updateExpenseGl($branch,$usern,$amount){
    $getsetvalue = new Setting();
    
        $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','50814096')->first();//expense GL
        $glacctitll = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('till_account'))->first();

         
       $this->create_saving_transaction_gl(null,$glacct->id,null,$amount,'debit','core',null,$this->generatetrnxref('exp'),'expenses','approved',$usern);
       $this->gltransaction('withdrawal',$glacct,$amount,null);
       
         $this->tracktrails(Auth::user()->id,$branch,$usern,'expenses','created new expense record');

        $this->create_saving_transaction_gl(null,$glacctitll->id,null,$amount,'credit','core',null,$this->generatetrnxref('exp'),'expenses','approved',$usern);
         $this->gltransaction('deposit',$glacctitll,$amount,null);
   }
   
    public function delete($id){
    //             $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

    //   $delexp =  Expenses::findorfail($id);
    //     if(file_exists($delexp->file)){
    //         unlink($delexp->file);
    //     }
    //     $delexp->delete();
        
    //     $usern = Auth::user()->last_name." ".Auth::user()->first_name;
    //     $this->tracktrails(Auth::user()->id,$branch,$usern,'expenses','deleted expense record');

        return redirect()->route('expenses.index')->with('success','Record Deleted');
    }

    //expenses type
    public function manage_expense_type(){
        return view('expenses.manage_expense_type')->with('extyps',ExpenseType::orderBy('created_at','DESC')->get());
    }

    public function create_expense_type(){
        return view('expenses.add_expense_type');
    }

    public function store_expense_type(Request $r){
        $this->validate($r,[
            'name' => ['required','string'],
            'category' => ['required','string'],
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

       ExpenseType::create([
        'expcat' => $r->category,
            'name' => $r->name,
        ]);
       
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'expense type','created new expense type record');
       
        return redirect()->route('expensestyp.index')->with('success','Record Created');

    }

    public function edit_expense_type($id){
        
        return view('expenses.edit_expense_type')->with('exptyped',ExpenseType::findorfail($id));
    }

    public function update_expense_type(Request $r, $id){
        $this->validate($r,[
            'name' => ['required','string'],
            'category' => ['required','string'],
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

       ExpenseType::findorfail($id)->update([
            'name' => $r->name,
            'expcat' => $r->category,
        ]);
       
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'expense type','updated expense type record');
       
        return redirect()->route('expensestyp.index')->with('success','Record Updated');
    }

    public function delete_expense_type($id){
       ExpenseType::findorfail($id)->delete();

       $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

       $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'expense type','deleted an expense type record');
        
        return redirect()->route('expensestyp.index')->with('success','Record Deleted');
    }
}
