@extends('layout.app')
@section('title')
    Change Password
@endsection
@section('pagetitle')
Change Password
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading vd_bg-grey">
                    <h3 class="panel-title"> <span class="menu-icon"> <i class="fa fa-key"></i> </span>Change Password</h3>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal" id="changepass" action="{{route('update.changepass')}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Password</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="password" name="password" required placeholder="Enter Password" data-toggle="tooltip" data-placement="top" data-original-title="First Name">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Comfirm Password</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="password" name="password_confirmation" placeholder="Confirm Password" data-rel="tooltip-right" data-original-title="Last Name">
                        </div>
                      </div>
                     <input type="hidden" name="id" id="uid" value="{{Auth::user()->id}}">
                        <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Change Password</button>
                              
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
<script>
  $(document)
</script>