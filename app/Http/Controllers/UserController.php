<?php

namespace App\Http\Controllers;

use App\Http\Traites\AuditTraite;
use App\Http\Traites\UserTraite;
use App\Models\Bank;
use App\Models\Email;
use App\Models\Permission;
use App\Models\Setting;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    use AuditTraite;
    use UserTraite;
    public function __construct()
    {
       $this->middleware('auth'); 
    }

    public function manage_users(){
        if(Auth::user()->account_type == 'system'){
            $users = User::with('roles')->get(); 
        }else{
            $users = User::with('roles')->where('account_type','!=','system')->get();
        }
        return view('users.manage_users')->with('users', $users);
    } 

    public function user_create(){
         if(Auth::user()->roles()->first()->name == 'super admin'){
            $role =  Role::all();
        }else{
            $role = Role::where('name','!=','super admin')->get();
        }
        return view('users.add_users')->with('roles',$role);
    }

    public function user_store(Request $r){
        $this->logInfo("creating system user",$r->all());

        $this->validate($r,[
            'first_name' => ['required','string'],
            'last_name' => ['required','string'],
            'email' => ['required','string','email','unique:users'],
            'gender' => ['required','string'],
            'phone' => ['required','string'],
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

       $user = User::create([
            'first_name' => $r->first_name,
            'last_name' => $r->last_name,
            'username' => $r->username,
            'email' => $r->email,
            'gender' => $r->gender,
            'phone' => $r->phone,
            'address' => $r->address,
            'password' => Hash::make($r->password),
            'account_type' => !empty($r->account_type) ? $r->account_type : 'user'
        ]);

        $user->assignRole($r->role);

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'user','created new user account');

        return ['status' => 'success', 'msg' => 'Record Created'];
    }

    public function user_edit($id){
         if(Auth::user()->roles()->first()->name == 'super admin'){
            $role =  Role::all();
        }else{
            $role = Role::where('name','!=','super admin')->get();
        }
        return view('users.edit_users')->with('ed',User::findorfail($id))
                                        ->with('roles',$role);
    }

    public function user_deactivate($id){
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        User::where('id',$id)->update([
            'status' => '0'
        ]);

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'user','user account deactivated');

         return ['status' => 'success', 'msg' => 'User Deactivated'];
    }

    public function user_activate($id){
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        User::where('id',$id)->update([
            'status' => '1'
        ]);

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'user','user account activated');

         return ['status' => 'success', 'msg' => 'User Activated'];
    }

    public function user_update(Request $r,$id){
        $this->logInfo("updating system user",$r->all());

        $this->validate($r,[
            'first_name' => ['required','string'],
            'last_name' => ['required','string'],
            'email' => ['required','string','email'],
            'gender' => ['required','string'],
            'phone' => ['required','string'],
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

            
        $user = User::findorfail($id);
        $user->update([
            'first_name' => $r->first_name,
            'last_name' => $r->last_name,
            'username' => $r->username,
            'email' => $r->email,
            'gender' => $r->gender,
            'phone' => $r->phone,
            'address' => $r->address,
            'account_type' => !empty($r->account_type) ? $r->account_type : 'user'
            ]);

        $user->syncRoles($r->role);
        

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'user','user account updated');
        
        
         return ['status' => 'success', 'msg' => 'Record Updated'];
    }
    
    public function reset_aduserpass($id){
        $getsetvalue = new Setting();

        $users = User::findorfail($id);

        $passw= mt_rand('11111111','99999999');
         $users->password = Hash::make($passw);
         $users->save();

         $msg = 'Your new Login password: '.$passw.'<br> Please make changes to password after login.';
         
           Email::create([
            'user_id' =>  $id,
            'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Password Reset',
            'message' => $msg,
            'recipient' => $users->email,
        ]);
        
         Mail::send(['html' => 'mails.sendmail'],[
            'msg' => $msg,
            'type' => 'Password Reset'
        ],function($mail)use($getsetvalue,$users){
            $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
              $mail->to($users->email);
            $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Password Reset');
        });

        return ['status' => 'success','msg' => 'Password Reset Successfully'];
    }
    
    public function user_delete($id){
          $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        User::findorfail($id)->delete();

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'user','user account deleted');

         return ['status' => 'success', 'msg' => 'Record Deleted'];
       }
       
    public function role_add_permission($id){
        return view('users.roles.add_permission');
        //->with('pr',Role::findorfail($id))
                                                //->with('permissions',Permission::all());
    }

    public function role_assign_permission(Request $r){
        $this->logInfo("assign roles and premission",$r->all());

        $this->validate($r,[
            'permissions.*' => ['required','string']
        ]);

        // Role::where('id',$r->roleid)->update([
        //     'permissions' => $r->permissions,
        // ]);
         return ['status' => 'success', 'msg' => 'Permission(s) Added'];
    }

   public function allbanks(){
     return view('users.bank')->with('banks',Bank::orderBy('bank_name','ASC')->get());
   }

   public function add_update_banks(Request $r){
    $this->logInfo("adding and updating of banks to system",$r->all());

    $this->validate($r,[
        'bank_name' => ['required','string'],
        'bank_code' => ['required','string']
    ]);

    $pathLocation = "uploads";
    if($r->type == "create"){

        $bk = Bank::Create([
        'bank_name' => $r->bank_name,
        'bank_code' => $r->bank_code
        ]);

        if($r->hasFile('bank_logo')){
            $bank = Bank::where('id',$bk->id)->first();

            $banklogo = $r->file('bank_logo');
            $newbanklogo = time()."_".$banklogo->getClientOriginalName();
            $banklogo->move($pathLocation,$newbanklogo);

            $bank->bank_logo = $pathLocation.'/'.$newbanklogo;
             $bank->save();
            }
           

        return ['status' => 'success','msg' => 'Bank Created Successfully'];

    }elseif($r->type == "update"){

        
        $bank = Bank::where('id',$r->id)->first();

        if($r->hasFile('bank_logo')){
            if(file_exists($bank->bank_logo)){
                unlink($bank->bank_logo);
            }
            $banklogo = $r->file('bank_logo');
            $newbanklogo = time()."_".$banklogo->getClientOriginalName();
            $banklogo->move($pathLocation,$newbanklogo);
            $bank->bank_logo = $pathLocation.'/'.$newbanklogo;
        }

        $bank->bank_name = $r->bank_name;
        $bank->bank_code = $r->bank_code;
        $bank->save();
      

        return ['status' => 'success','msg' => 'Bank Updated Successfully'];
    }

  }

  public function delete_bank($id){
    $bnk = Bank::findorfail($id);
    if(file_exists($bnk->bank_logo)){
        unlink($bnk->bank_logo);
    }
    $bnk->delete();
    return['status' => 'success','msg' => 'Bank Deleted Successfully'];
  }

  public function user_resetqr($id){
        
    $getuser =   User::findorfail($id);

        $getuser->is_2fa_enable = null;
        $getuser->save();

        return ['status' => 'success','msg' => '2FA Code reset Successfully'];
    
  }
}//endclass
