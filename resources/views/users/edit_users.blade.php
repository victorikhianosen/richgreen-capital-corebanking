@extends('layout.app')
@section('title')
    Edit User
@endsection
@section('pagetitle')
Edit User
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('user.all')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('user.update',['id' => $ed->id])}}" method="post" role="form" id="editusers" enctype="multipart/form-data">
                      @csrf
                      
                      <div class="form-group">
                        <label class="col-sm-2 control-label">First Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="first_name" id="fname" value="{{$ed->first_name}}" autocomplete="off" placeholder="Enter First Name" data-rel="tooltip-right" data-original-title="Enter First Name">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Last Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="last_name" id="fname" value="{{$ed->last_name}}" autocomplete="off" placeholder="Enter Last Name" data-rel="tooltip-right" data-original-title="Enter Last Name">
                        </div>
                      </div>
                        <div class="form-group">
                        <label class="col-sm-2 control-label">Username</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="username" id="uname" value="{{$ed->username}}" autocomplete="off" placeholder="Enter User Name" data-rel="tooltip-right" data-original-title="Enter User Name">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Email</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="email" id="em" value="{{$ed->email}}" autocomplete="off" placeholder="Enter Email" data-rel="tooltip-right" data-original-title="Enter Full Name">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Gender</label>
                        <div class="col-sm-7 controls">
                            <select class="width-70" name="gender"  id="gr" autocomplete="off">
                                <option selected disabled>Select...</option>
                                <option value="male"  {{$ed->gender == 'male' ? 'selected' : ''}}>Male</option>
                                <option value="female" {{$ed->gender == 'female' ? 'selected' : ''}}>Female</option>
                            </select>
                        </div>
                      </div>
                      
                         @if (Auth::user()->account_type == "system")
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Account Type</label>
                        <div class="col-sm-7 controls">
                            <select class="width-70" name="account_type"  id="" autocomplete="off">
                                <option selected disabled>Select...</option>
                                <option value="system"  {{$ed->account_type == 'system' ? 'selected' : ''}}>System Admin</option>
                                <option value="user" {{$ed->account_type == 'user' ? 'selected' : ''}}>User</option>
                            </select>
                        </div>
                      </div>
                      @endif
                      
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Phone</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="phone" id="phn" value="{{$ed->phone}}" autocomplete="off" placeholder="Enter Phone" data-rel="tooltip-right" data-original-title="Enter Full Name">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Address</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="address" id="adr" value="{{$ed->address}}" autocomplete="off" placeholder="Enter Address" data-rel="tooltip-right" data-original-title="Enter Full Name">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-2 control-label">Role</label>
                        <div class="col-sm-7 controls">
                            <select class="width-70" name="role"  id="gr" autocomplete="off">
                                <option selected disabled>Select Role...</option>
                                @foreach ($roles as $role)
                                   <option value="{{$role->name}}" {{$ed->roles->contains('name', $role->name) ? "selected" : ""}}>{{$role->name}}</option>
                            @endforeach
                           
                            </select>
                        </div>
                      </div>
                      
                      <!--    <div class="form-group">-->
                      <!--  <label class="col-sm-2 control-label">Signature</label>-->
                      <!--  <div class="col-sm-7 controls">-->
                      <!--    <input class="width-70" type="file" name="signature" id="adr"  accept=".jpg,.jpeg,.png">-->
                      <!--  </div>-->
                      <!--</div>-->
                      
                        <div class="form-group form-actions">
                            <div class="col-sm-3"> </div>
                            <div class="col-sm-8">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Update Record</button>
                              @can('reset users password')
                              <button class="btn vd_btn vd_bg-red vd_white" type="button" onclick="restuserPassword('{{route('user.resetadusrepass',['id' => $ed->id])}}')"><i class="icon-ok"></i>Reset Password</button>
                              @endcan
                            </div>
                          </div>
                    </form>
                  </div>
                </div>
                <!-- Panel Widget --> 
              </div>
              <!-- col-md-12 --> 
            </div>
            <!-- row -->
  </div>
@endsection
@section('scripts')
<script>
  $(document).ready(function(){
    $("#editusers").submit(function(e){
      e.preventDefault();
      $.ajax({
        url: $("#editusers").attr('action'),
        method: 'post',
        data: $("#editusers").serialize(),
        beforeSend:function(){
          $("#btnssubmit").text('Please wait...');
          $("#btnssubmit").attr('disabled',true);
        },
        success:function(data){
          if(data.status == 'success'){
            $("#btnssubmit").text('Update Record');
          $("#btnssubmit").attr('disabled',false);
            toastr.success(data.msg);
            window.location.reload();
          }else{
            toastr.error(data.msg);
             $("#btnssubmit").text('Update Record');
           $("#btnssubmit").attr('disabled',false);
             return false;
           }
        },
        error:function(xhr,status,errorThrown){
          let err = '';
          $.each(xhr.responseJSON.errors, function (key, value) {
                err += value;
            });
            toastr.error(err);
          $("#btnssubmit").text('Update Record');
          $("#btnssubmit").attr('disabled',false);
          return false;
        }
      });
    });
  });
</script>
<script>
    function restuserPassword(url){
      $.ajax({
          url: url,
          method: 'get',
          beforeSend:function(){
            $(".loader").css('visibility','visible');
          $(".loadingtext").text('Please Wait...');
          },
          success:function(data){
            if(data.status == 'success'){
              $(".loader").css('visibility','hidden');
                toastr.success(data.msg);
               //window.location.reload();
            }else{
                $(".loader").css('visibility','hidden');
                toastr.error("An Error Occurred");
               return false;
             }
          },
          error:function(xhr,status,errorThrown){
            $(".loader").css('visibility','hidden');
                toastr.error('Error '+errorThrown);
            return false;
          }
        });
    }
</script>
@endsection