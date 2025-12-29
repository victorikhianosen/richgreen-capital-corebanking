@extends('layout.app')
@section('title')
    Create Collateral Type
@endsection
@section('pagetitle')
Create Collateral Type
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('collatype.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                     <form action="{{route('collatype.store')}}" method="post" onsubmit="thisForm()">
                      @csrf
                      <div class="form-group">
                        <label class="col-sm-12 control-label">Collateral Type Name</label>
                        <div class="col-sm-7">
                          <input type="text" name="name" placeholder="Enter Name" value="{{old('name')}}" required class="col-sm-7 form-control">
                        </div>
                    </div>

                    <div class="form-group form-actions">
                      <div class="col-sm-4"> </div>
                      <div class="col-sm-7" style="margin: 10px 0px">
                        <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit">Save Record</button>
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
    
  });
</script>
@endsection