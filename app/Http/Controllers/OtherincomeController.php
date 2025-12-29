<?php

namespace App\Http\Controllers;

use App\Http\Traites\AuditTraite;
use App\Models\OtherIncome;
use App\Models\OtherIncomeType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtherincomeController extends Controller
{
 
    use AuditTraite;
    public function __construct()
    {
       $this->middleware('auth'); 
    }

    public function index(){
        return view('otherincome.index')->with('incomes',OtherIncome::orderBy('created_at','DESC')->get());
    }

    public function create(){
        return view('otherincome.create')->with('incmtypes',OtherIncomeType::all());
    }

    public function store(Request $r) {
        $this->validate($r,[
            'income_types' => ['required','string'],
            'amount' => ['required','string'],
            'date' => ['required','string'],
        ]);
        $icm = OtherIncome::create([
            'user_id' => Auth::user()->id,
            'other_income_type_id' => $r->income_types,
            'branch_id' => Auth::user()->branch_id,
            'amount' => $r->amount,
            'income_date' => $r->date,        
            'notes' => $r->note,
        ]);

        if($r->hasFile('files')){
            $filename = $r->file('files');
            $newfilename = time()."_".$filename->getClientOriginalName();
            $filename->move('uploads',$newfilename);

            OtherIncome::where('id', $icm->id)->update([
                'files' => 'uploads/'.$newfilename
            ]);
        }

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,Auth::user()->branch_id,$usern,'other income','created new other income record');

        return redirect()->route('income.index')->with('success','Record Created');
    }

    public function edit($id){
        return view('otherincome.edit')->with('incmtypes',OtherIncomeType::all())
                                ->with('incms', OtherIncome::findorfail($id));
    }

    public function update(Request $r, $id){
        $this->validate($r,[
            'income_types' => ['required','string'],
            'amount' => ['required','string'],
            'date' => ['required','string'],
        ]);

        $upincm = OtherIncome::findorfail($id);
        
        if($r->hasFile('file')){
            if(file_exists($upincm->file)){
                unlink($upincm->file);
            }
            $filename = $r->file('file');
            $newfilename = time()."_".$filename->getClientOriginalName();
            $filename->move('uploads',$newfilename);
            $upincm->files = 'uploads/'.$newfilename;
        }

        $upincm->update([
            'other_income_type_id' => $r->income_types,
            'amount' => $r->amount,
            'income_date' => $r->date,        
            'notes' => $r->note,
        ]);

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,Auth::user()->branch_id,$usern,'other income','updated an other income record');

        return redirect()->route('income.index')->with('success','Record Updated');

    }

    public function delete($id){
       $delincm =  OtherIncome::findorfail($id);
        if(file_exists($delincm->files)){
            unlink($delincm->files);
        }
        $delincm->delete();
        
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,Auth::user()->branch_id,$usern,'other income','deleted other income record');

        return redirect()->route('income.index')->with('success','Record Deleted');
    }

    //expenses type
    public function manage_income_type(){
        return view('otherincome.manage_income_type')->with('incmtyps',OtherIncomeType::orderBy('created_at','DESC')->get());
    }

    public function create_income_type(){
        return view('otherincome.add_income_type');
    }

    public function store_income_type(Request $r){
        $this->validate($r,[
            'name' => ['required','string'],
        ]);

       OtherIncomeType::create([
            'name' => $r->name,
        ]);
       
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,Auth::user()->branch_id,$usern,'other income type','created new other income type record');
       
        return redirect()->route('incometyp.index')->with('success','Record Created');

    }

    public function edit_income_type($id){
        
        return view('otherincome.edit_income_type')->with('incmtyped',OtherIncomeType::findorfail($id));
    }

    public function update_income_type(Request $r, $id){
        $this->validate($r,[
            'name' => ['required','string'],
        ]);

       OtherIncomeType::findorfail($id)->update([
            'name' => $r->name,
        ]);
       
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,Auth::user()->branch_id,$usern,'other income type','updated other income type record');
       
        return redirect()->route('incometyp.index')->with('success','Record Updated');
    }

    public function delete_income_type($id){
       OtherIncomeType::findorfail($id)->delete();

       $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,Auth::user()->branch_id,$usern,'other income type','deleted an other income type record');
        
        return redirect()->route('incometyp.index')->with('success','Record Deleted');
    }
}
