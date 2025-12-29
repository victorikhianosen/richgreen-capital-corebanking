<?php

namespace App\Http\Controllers;

use App\Http\Traites\AuditTraite;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    use AuditTraite;
    public function __construct()
    {
       $this->middleware('auth'); 
    }

    public function index(){
        return view('branches.index')->with('getbranches',Branch::orderBy('created_at','DESC')->get());
    }

    public function create(){
        return view('branches.create')->with('branches',Branch::all());
    }
   
    public function store(Request $r){
        $this->validate($r,[
            'branch_name' => ['required','string'],
            'address' => ['required','string'],
        ]);

        Branch::firstOrCreate([
            'branch_name' => $r->branch_name,
            'address' => $r->address
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'branch','created a new branch');

        return redirect()->route('branch.index')->with('success','Record Created');
    }

    public function edit($id){
        return view('branches.edit')->with('ed',Branch::findorfail($id));
    }

    public function update(Request $r,$id){
        $this->validate($r,[
            'branch_name' => ['required','string'],
            'address' => ['required','string'],
        ]);
        
        Branch::findorfail($id)->update([
            'branch_name' => $r->branch_name,
            'address' => $r->address
        ]);

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;


        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'branch','updated a branch');
        
        return redirect()->route('branch.index')->with('success','Record Updated');
    }

    public function delete($id){
        Branch::findorfail($id)->delete();

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,Auth::user()->branch_id,$usern,'branch','deleted a branch');

        return redirect()->back()->with('success','Record Deleted');
    }

    public function branch_assign_user($id){
        return view('branches.assign_branch')->with('users',User::all())
                                            ->with('brid',$id);
                                              
                                            
    }

    public function branch_assign_user_save(Request $r){
        $this->validate($r,[
            'ac_users.*' => ['required','string']
        ]);

        foreach($r->ac_users as $u){
           $check = DB::table('branch_users')->where('branch_id',$r->brnid)->where('user_id',$u)->first();
           if(empty($check)){
            DB::table('branch_users')->insert([
                'branch_id' => $r->brnid,
                'user_id' => $u
            ]);

            User::findorfail($u)->update([
                'branch_id' => $r->brnid,
            ]);
           }
        }

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'branch','assigned a user to branch');

        return redirect()->back()->with('success','User Assigned to Branch Successfully');
    }

    public function branch_showuser($id){
        $allbrnchusr = DB::table('branch_users')->join('users','users.id','branch_users.user_id')
                                                ->select('users.id As uid','users.first_name','users.last_name')
                                                ->where('branch_users.branch_id',$id)
                                                ->get();

        return view('branches.user_assigned_branch')->with('allbranchusers',$allbrnchusr)
                                                    ->with('branches',Branch::all())
                                                    ->with('b', Branch::findorfail($id));
    }

    public function move_user_branch(Request $r){
        DB::table('branch_users')->where('user_id',$r->userid)->update([
            'branch_id' => $r->branch_id,
        ]);
        User::findorfail($r->userid)->update([
            'branch_id' => $r->branch_id,
        ]);

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'branch','moved a user to branch');

        return redirect()->back()->with('success','User Moved to Branch Successfully');
    }
}
