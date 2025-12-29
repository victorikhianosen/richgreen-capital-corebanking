@extends('layout.app')
@section('title')
    Edit Account Officer
@endsection
@section('pagetitle')
Edit Account Officer
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('acofficer.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('acofficer.update',['id' => $ed->id])}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Account Users</label>
                        <div class="col-sm-7 controls">
                           <select name="ac_users" class="width-70" autocomplete="off" required id="acuser">
                            <option selected disabled>Select...</option>
                            @foreach ($users as $user)
                                <option value="{{$user->id}}" {{$user->id == $ed->user_id ? "selected" : ""}}>{{$user->last_name." ".$user->first_name}} </option>
                            @endforeach
                           </select>
                           <img src="{{asset('img/loading.gif')}}" id="sttext" style="display: none" alt="loading">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Branch</label>
                        <div class="col-sm-7 controls">
                           <select name="branch" class="width-70" autocomplete="off" required id="acuser">
                            <option selected disabled>Select Branch</option>
                            @foreach ($branches as $branch)
                                <option value="{{$branch->id}}" {{$branch->id == $ed->branch_id ? "selected" : ""}}>{{$branch->branch_name}} </option>
                            @endforeach
                           </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Full Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="full_name" id="fname" autocomplete="off" value="{{$ed->full_name}}" placeholder="Enter Full Name" data-rel="tooltip-right" data-original-title="Enter Full Name">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Email</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="email" id="em" autocomplete="off" value="{{$ed->email}}" placeholder="Enter Email" data-rel="tooltip-right" data-original-title="Enter Full Name">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Gender</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="gender" id="gdn" autocomplete="off" value="{{$ed->gender}}" placeholder="Enter Gender" data-rel="tooltip-right" data-original-title="Enter Full Name">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Phone</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="phone" id="phn" autocomplete="off" value="{{$ed->phone}}" placeholder="Enter Phone" data-rel="tooltip-right" data-original-title="Enter Full Name">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Address</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="address" id="adr" autocomplete="off" value="{{$ed->address}}" placeholder="Enter Address" data-rel="tooltip-right" data-original-title="Enter Full Name">
                        </div>
                      </div>
                      
                        <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Update Record</button>
                              
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
    $("#acuser").change(function(){
      let userid = $("#acuser").val();
      $.ajax({
        url:"{{route('getuserdetails')}}",
        method:"get",
        data:{'uid':userid},
        beforeSend:function(){
          $("#sttext").show();
        },
        success:function(data){
          $("#sttext").hide();
          $("#fname").val(data.name);
          $("#em").val(data.email);
          $("#gdn").val(data.gender);
          $("#phn").val(data.phone);
          $("#adr").val(data.addr);
        },
        error:function(xhr,status,errorThrown){
          alert('An Error Occured... '+errorThrown);
          $("#sttext").hide();
        }
      })
    });
  });
</script>
@endsection