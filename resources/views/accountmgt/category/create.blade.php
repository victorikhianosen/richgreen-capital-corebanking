@extends('layout.app')
@section('title')
    Create Account Category
@endsection
@section('pagetitle')
Create Account Category
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('ac.category.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('ac.category.store')}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70 form-control" type="text" autofocus name="name" id="fname" autocomplete="off" placeholder="Enter Category Name" value="{{old('name')}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Account Type</label>
                        <div class="col-sm-7 controls">
                           <select name="account_type" class="width-70 form-control" autocomplete="off" required id="acuser">
                            <option selected disabled>Select Type</option>
                            @foreach ($actyps as $typ)
                                <option value="{{$typ->name}}">{{ucwords($typ->name)}}</option>
                            @endforeach
                           </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Description</label>
                        <div class="col-sm-7 controls">
                            <textarea name="description" class="width-70 form-control" id="" cols="10" rows="3">{{old('description')}}</textarea>
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
  
  });
</script>
@endsection