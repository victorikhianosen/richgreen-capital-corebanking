@extends('layout.app')
@section('title')
    Edit Capital
@endsection
@section('pagetitle')
Edit Capital
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('capital.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('capital.update',['id' => $ed->id])}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Share Holders Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70 form-control" type="text" autofocus name="share_holder_name" id="fname" autocomplete="off" placeholder="Enter Category Name" value="{{$ed->share_holder_name}}">
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Percentage</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70 form-control" type="number" name="percentage" step=".01" id="amt" autocomplete="off" placeholder="Enter Percentage" value="{{$ed->percentage}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Description</label>
                        <div class="col-sm-7 controls">
                            <textarea name="description" class="width-70 form-control" id="" cols="10" rows="3">{{$ed->notes}}</textarea>
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
  
  });
</script>
@endsection