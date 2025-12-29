<?php

namespace App\Http\Controllers;

use App\Http\Traites\AuditTraite;
use App\Http\Traites\UserTraite;
use App\Models\Accountofficer;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountofficerController extends Controller
{
    use AuditTraite;
     use UserTraite;

    public function __construct()
    {
       $this->middleware('auth');
    }


    public function index(){
        return view('account_officers.index')->with('getofficers',Accountofficer::orderBy('created_at','DESC')->get());
    }

    public function create(){
         $user = User::whereHas('roles', function ($query) {
             return $query->where('name','!=', 'super admin');
        })->get();

       // $user->hasRole('super admin');
        return view('account_officers.create')->with('branches',Branch::all())
                                            ->with('users',$user);
    }

    public function store(Request $r){
        $this->logInfo("creating acct officer",$r->all());

        $this->validate($r,[
            'full_name' => ['required','string'],
            'ac_users' => ['required','string'],
            'branch' => ['required','string'],
            'gender' => ['required','string'],
            'phone' => ['required','string'],
            'address' => ['required','string'],
        ]);

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        Accountofficer::firstOrCreate([
            'user_id' => $r->ac_users,
            'branch_id' => $r->branch,
            'full_name' => $r->full_name,
            'email' => $r->email,
            'gender' => $r->gender,
            'phone' => $r->phone,
            'address' => $r->address
        ]);

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'account officer','created a new account officer');

        return redirect()->route('acofficer.index')->with('success','Record Created');
    }

    public function edit($id){
        return view('account_officers.edit')->with('ed',Accountofficer::findorfail($id))
                                            ->with('branches',Branch::all())
                                            ->with('users',User::all());
    }

    public function update(Request $r,$id){
        $this->logInfo("update acct officer",$r->all());

        $this->validate($r,[
            'full_name' => ['required','string'],
            'ac_users' => ['required','string'],
            'branch' => ['required','string'],
            'gender' => ['required','string'],
            'phone' => ['required','string'],
            'address' => ['required','string'],
        ]);

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        Accountofficer::findorfail($id)->update([
            'user_id' => $r->ac_users,
            'branch_id' => $r->branch,
            'full_name' => $r->full_name,
            'email' => $r->email,
            'gender' => $r->gender,
            'phone' => $r->phone,
            'address' => $r->address
        ]);

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'account officer','updated an account officer');

        return redirect()->route('acofficer.index')->with('success','Record Updated');
    }

    public function delete($id){
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        Accountofficer::findorfail($id)->delete();
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'account officer','deleted an account officer');

        return redirect()->back()->with('success','Record Deleted');
    }
}
