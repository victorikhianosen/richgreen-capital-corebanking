@extends('layout.app')
@section('title')
    Create Permissions
@endsection
@section('pagetitle')
Create Permissions
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                      <a href="{{route('permissions.all')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                   </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal" id="changepass" action="{{route('permissions.store')}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Permission</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="permission" value="{{old('permission')}}" required placeholder="Enter Permission" data-toggle="tooltip" data-placement="top" data-original-title="First Name">
                        </div>
                      </div>
                      
                      {{-- <div class="form-group">
                        <label class="col-sm-2 control-label">Description</label>
                        <div class="col-sm-7 controls">
                            <textarea name="description" class="width-70" id="" cols="5" rows="3">{{old('description')}}</textarea>
                        </div>
                      </div> --}}

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
<script>
  $(document)
</script>