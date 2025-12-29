@extends('layout.app')
@section('title')
    Create Branch
@endsection
@section('pagetitle')
Create Branch
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('branch.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('branch.store')}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Branch Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="branch_name" id="brnd" value="{{old('branch_name')}}" autocomplete="off" placeholder="Enter Branch Name" data-rel="tooltip-right" data-original-title="Enter branch name">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Address</label>
                        <div class="col-sm-7 controls">
                          <textarea name="address" id="addss" class="width-70" cols="8" rows="2">{{old('address')}}</textarea>
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