@extends('layout.app')
@section('title')
    Profile
@endsection
@section('pagetitle')
Profile
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading vd_bg-grey">
                    <h3 class="panel-title"> <span class="menu-icon"> <i class="fa fa-user"></i> </span>Edit Profile</h3>
                  </div>
                  <div class="panel-body">
                    
                    <div class="row">
                      <div class="col-md-7 col-lg-7 col-sm-12">
                        @include('includes.errors')
                    @include('includes.success')
                      </div>
                      </div>
                      
                    <form class="form-horizontal" method="post" action="{{route('update.profile')}}" role="form" onsubmit="thisForm()">
                      @csrf
                     <input type="hidden" name="id" id="uid" value="{{Auth::user()->id}}">
                      <div class="form-group">
                        <label class="col-sm-2 control-label">First Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="firstname" placeholder="First Name" data-toggle="tooltip" data-placement="top" data-original-title="First Name" value="{{Auth::user()->first_name}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Last Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="lastname" placeholder="Last Name" data-rel="tooltip-right" data-original-title="Last Name" value="{{Auth::user()->last_name}}">
                        </div>
                      </div>
                      
                       <div class="form-group">
                        <label class="col-sm-2 control-label">Username</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="username" id="uname" value="{{Auth::user()->username}}" autocomplete="off" placeholder="Enter User Name" data-rel="tooltip-right" data-original-title="Enter User Name">
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Email</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="email" placeholder="Email" data-rel="tooltip-right" data-original-title="Email" value="{{Auth::user()->email}}" >
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Gender</label>
                        <div class="col-sm-7 controls">
                          <select name="gender" class="width-70" id="">
                            <option selected disabled>Select...</option>
                            <option value="male" {{Auth::user()->gender == "male" ? "selected" : ""}}>Male</option>
                            <option value="female" {{Auth::user()->gender == "female" ? "selected" : ""}}>Female</option>
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Address</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="address" placeholder="Address" data-rel="tooltip-right" data-original-title="Address" value="{{Auth::user()->address}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Phone</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="phone" placeholder="Phone" data-rel="tooltip-right" data-original-title="Phone" value="{{Auth::user()->phone}}">
                        </div>
                      </div>
                      
                        <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Update</button>
                              
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