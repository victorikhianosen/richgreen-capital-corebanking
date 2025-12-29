@extends('layout.app')
@section('title')
    Add Permissions
@endsection
@section('pagetitle')
Add Permissions
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                      <a href="{{route('roles')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                   </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal" id="changepass" action="{{route('roles.assignpermission')}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Role</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="role" readonly value="{{$pr->name}}" required placeholder="Enter Role" data-toggle="tooltip" data-placement="top" data-original-title="First Name">
                        </div>
                      </div>
                      <input type="hidden" name="roleid" value="{{$pr->id}}">
                      <div class="form-group">
                        
                        <label class="col-sm-2 control-label">Permissions</label>
                        <div class="col-sm-7 controls">
                            <select class="width-70" name="permissions[]" multiple data-placeholder="Select Permissions..."  id="pmr" autocomplete="off">
                                @foreach ($permissions as $item)
                                   <option value="{{$item->name}}">{{ucwords($item->name)}}</option>
                                @endforeach
                            </select>
                            
                        </div>
                      </div>
                        <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Save Record</button>
                              
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
$("#pmr").select2();
  });
</script>
@endsection