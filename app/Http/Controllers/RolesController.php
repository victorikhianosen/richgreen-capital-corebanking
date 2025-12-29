<?php

namespace App\Http\Controllers;

use App\Http\Traites\AuditTraite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesController extends Controller
{
    use AuditTraite;
    public function __construct()
    {
       $this->middleware('auth'); 
    }
    
    public function manage_roles(){
        if(Auth::user()->roles()->first()->name == 'super admin'){
            $role = Role::with('permissions')->get();
        }else{
            $role = Role::with('permissions')->where('name','!=','super admin')->get();
        }
        return view('users.roles.manage_roles')->with('roles',$role);
    }

    public function role_create(){
        return view('users.roles.create')->with('permissions',Permission::orderBy('name','ASC')->get());
    }

    public function role_store(Request $r){
        $this->validate($r,[
            'role' => ['required','string'],
            'permissions.*' => ['required','string']
        ]);

        $chek = Role::where('name',$r->role)->first();
         if($chek){
              return redirect()->back()->with('error', 'role already exist');
         }else{
             
              $role = Role::create([
                 'name' => $r->role,                    
             ]);

        $role->syncPermissions($r->permissions);

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,Auth::user()->branch_id,$usern,'user role','created new user role/permissions');
        return redirect()->route('roles')->with('success', 'Record Created');
        
         }
       
    }


    public function role_edit($id){
        $rolepermission = DB::table('role_has_permissions')->where('role_has_permissions.role_id',$id)
                              ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
                              ->all();

        return view('users.roles.edit')->with('permissions',Permission::orderBy('name','ASC')->get())
                                        ->with('rolepermissions',$rolepermission)
                                       ->with('ed',Role::findorfail($id));
    }

    public function role_update(Request $r, $id){
        $this->validate($r,[
            'role' => ['required','string'],
            'permissions.*' => ['required','string']
        ]);

        $role = Role::findorfail($id);
        $role->update([
            'name' => $r->role,
        ]);

        //$r->permissions
        $role->syncPermissions($r->permissions);

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,Auth::user()->branch_id,$usern,'user role','Updated user role/premissions');

        return redirect()->route('roles')->with('success', 'Record Updated');

    }
}
