<?php

namespace App\Http\Controllers;

use App\Http\Traites\AuditTraite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class PermissionsController extends Controller
{
    use AuditTraite;
    public function __construct()
    {
       $this->middleware('auth'); 
    }
    
    public function manage_permission(){
        
        return view('users.permission.manage_permission')->with('permissions',Permission::orderBy('name','ASC')->get());
    }
    public function permission_create(){
        return view('users.permission.create');
    }

    public function permission_store(Request $r){
        $this->validate($r,[
            'permission' => ['required','string'],
            ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        Permission::firstOrCreate([
            'name' => strtolower($r->permission),
        ]);

        //back()
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'user/permission','permission created');

        return redirect()->route('permissions.all')->with('success', 'Record Created');
    }

    public function permission_edit($id){
        return view('users.permission.edit')->with('ped',Permission::findorfail($id));
    }

    public function permission_update(Request $r,$id){
        $this->validate($r,[
            'permission' => ['required','string'],
            ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        Permission::findorfail($id)->update([
            'name' => strtolower($r->permission),
        ]);

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'user/permission','permission updated');

        return redirect()->route('permissions.all')->with('success', 'Record Updated');
    }

    public function permission_delete($id){
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        Permission::findorfail($id)->delete();

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'user/permission','permission deleted');

        return redirect()->back()->with('success','Record Deleted');
    }
}
