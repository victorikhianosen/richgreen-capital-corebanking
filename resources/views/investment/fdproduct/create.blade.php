@extends('layout.app')
@section('title')
    Create Fixed Deposit Product
@endsection
@section('pagetitle')
Create Fixed Deposit Product
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('manage.fdproduct')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('store.fdproduct')}}" method="post" id="submitinveproduct" onsubmit="thisForm()" role="form">
                      @csrf
                      
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Product Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="product_name" autofocus required value="{{old('product_name')}}" autocomplete="off" placeholder="Enter Product Name">
                        </div>
                      </div>

                       <div class="form-group">
                        <label class="col-sm-4 control-label">product GL Type</label>
                        <div class="col-sm-7 controls">
                          <select class="form-control width-70 selgl" autocomplete="off" name="product_gl_type">
                            <option disabled selected>--Select--</option>
                          @foreach ($asstsgl as $item)
                               <option value="{{$item->gl_code}}">{{ucwords($item->gl_name)." [".$item->gl_code."]"}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Interest GL Type</label>
                        <div class="col-sm-7 controls">
                          <select class="form-control width-70 selgl" autocomplete="off" name="interest_glcode">
                            <option disabled selected>--Select--</option>
                             @foreach ($incomegl as $item)
                               <option value="{{$item->gl_code}}">{{ucwords($item->gl_name)." [".$item->gl_code."]"}}</option>
                            @endforeach
                          </select>
                        </div>
                    </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Income Fee GL Type</label>
                        <div class="col-sm-7 controls">
                          <select class="form-control width-70 selgl" autocomplete="off" name="incomefee_glcode">
                            <option disabled selected>--Select--</option>
                            @foreach ($incomegl as $item)
                               <option value="{{$item->gl_code}}">{{ucwords($item->gl_name)." [".$item->gl_code."]"}}</option>
                            @endforeach
                          </select>
                        </div>
                    </div>
                    
                      <hr>
                      <h5 class="text-danger">Principal Amount:</h5><br>
                      
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Minimum Principal</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" name="minimum_principal" id="brnd" value="{{old('minimum_principal')}}">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Default Principal</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" name="default_principal"  value="{{old('default_principal')}}" >
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Maximum Principal</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" name="maximum_principal"  value="{{old('maximum_principal')}}">
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Interest Rating: </h5><br>

                       <div class="form-group">
                        <label class="col-sm-4 control-label">Interest Method</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" required="required" id="interest_method" name="interest_method">
                                <option value="upfront">Upfront</option>
                                <option value="monthly">Monthly</option>
                                <option value="rollover">RollOver</option>
                            </select>
                        </div>
                      </div>

                      
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Default Interest %</label>
                        <div class="col-sm-7 controls">
                            <input class="form-control width-70 touchspin" placeholder="" name="default_interest_rate" step=".01" type="number" autocomplete="off" value="0">
                            
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Minimum Interest %</label>
                        <div class="col-sm-7 controls"> step=".01"
                                <input class="form-control width-70 touchspin" placeholder="" name="minimum_interest_rate" step=".01" type="number" autocomplete="off" value="0">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Maximum Interest %</label>
                        <div class="col-sm-7 controls">
                                <input class="form-control width-70 touchspin" placeholder="" name="maximum_interest_rate" step=".01" type="number" autocomplete="off" value="0">
                            </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Interest Period</label>
                        <div class="col-sm-7 controls">
                                <select class="form-control width-70" required="required" id="inputDefaultInterestPeriod" name="interest_period">
                                    <option selected="selected" disabled>Select</option>
                                    <option value="month">Per Month</option>
                                    <option value="year">Per Year</option>
                                </select>
                        </div>
                      </div>


                      <div class="form-group">
                        <label class="col-sm-4 control-label">Default Duration</label>
                        <div class="col-sm-7 controls">
                            <div class="col-sm-3">
                                <input class="width-70 form-control" required="required" name="default_duration" type="number" id="default_duration"  value="{{old('default_duration')}}">
                            </div>
                            <div class="col-sm-6">
                                <select class="form-control width-90" required="required" id="inputMaxInterestPeriod" name="default_duration_type">
                                    <option value="month">Month(s)</option>
                                    <option value="year">Year(s)</option></select>
                            </div>
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Interest Payments: </h5><br>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Interest Payments</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" required="required" id="" name="repayment_cycle">
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="semi_annual">Semi-Annual</option>
                                <option value="annual">Annually</option>
                            </select>                       
                             </div>
                      </div>
                      
                       <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
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
    $("#ro").select2();
    $("#sibo").select2();

    $('#yes4').attr('disabled', true);
        $('#no4').attr('disabled', true);
        $('#after_maturity_date_penalty_type_percentage').attr('disabled', true);
        $('#after_maturity_date_penalty_type_fixed').attr('disabled', true);
        $('#after_maturity_date_penalty_calculate').attr('disabled', true).removeAttr('required');
        $('#after_maturity_date_penalty_amount').attr('disabled', true);
        $('#after_maturity_date_penalty_grace_period').attr('disabled', true);
        $('#after_maturity_date_penalty_recurring').attr('disabled', true);

        $('#yes2').attr('disabled', true);
        $('#no2').attr('disabled', true);
        $('#late_repayment_penalty_type_fixed').attr('disabled', true);
        $('#late_repayment_penalty_calculate').attr('disabled', true).removeAttr('required');
        $('#late_repayment_penalty_amount').attr('disabled', true);
        $('#late_repayment_penalty_grace_period').attr('disabled', true);
        $('#late_repayment_penalty_recurring').attr('disabled', true);

   $("#enable_after_maturity_date_penalty").change(function(){
      let cherpymatuy = $("#enable_after_maturity_date_penalty");
      if(cherpymatuy.is(':checked')){
        $('#yes4').attr('disabled', false);
        $('#no4').attr('disabled', false);
        $('#after_maturity_date_penalty_type_percentage').removeAttr('disabled', false);
        $('#after_maturity_date_penalty_type_fixed').removeAttr('disabled', false);
        $('#after_maturity_date_penalty_calculate').removeAttr('disabled', false).attr('required');
        $('#after_maturity_date_penalty_amount').removeAttr('disabled', false);
        $('#after_maturity_date_penalty_grace_period').removeAttr('disabled', false);
        $('#after_maturity_date_penalty_recurring').removeAttr('disabled', false);
      }else{
        $('#yes4').attr('disabled', true);
        $('#no4').attr('disabled', true);
        $('#after_maturity_date_penalty_type_percentage').attr('disabled', true);
        $('#after_maturity_date_penalty_type_fixed').attr('disabled', true);
        $('#after_maturity_date_penalty_calculate').attr('disabled', true).removeAttr('required');
        $('#after_maturity_date_penalty_amount').attr('disabled', true);
        $('#after_maturity_date_penalty_grace_period').attr('disabled', true);
        $('#after_maturity_date_penalty_recurring').attr('disabled', true);
      }
   });

   $("#enable_late_repayment_penalty").change(function(){
      let cherpypen = $("#enable_late_repayment_penalty");
      if(cherpypen.is(':checked')){
        $('#yes2').attr('disabled', false);
        $('#no2').attr('disabled', false);
        $('#late_repayment_penalty_type_fixed').attr('disabled', false);
        $('#late_repayment_penalty_calculate').attr('disabled', false).attr('required');
        $('#late_repayment_penalty_amount').attr('disabled', false);
        $('#late_repayment_penalty_grace_period').attr('disabled', false);
        $('#late_repayment_penalty_recurring').attr('disabled', false);
      }else{
        $('#yes2').attr('disabled', true);
        $('#no2').attr('disabled', true);
        $('#late_repayment_penalty_type_fixed').attr('disabled', true);
        $('#late_repayment_penalty_calculate').attr('disabled', true).removeAttr('required');
        $('#late_repayment_penalty_amount').attr('disabled', true);
        $('#late_repayment_penalty_grace_period').attr('disabled', true);
        $('#late_repayment_penalty_recurring').attr('disabled', true);
      }
   });

   $("#submitinveproduct").submit(function(e){
    e.preventDefault();
    $.ajax({
      url: $("#submitinveproduct").attr('action'),
      method:'post',
      data:$("#submitinveproduct").serialize(),
      beforeSend:function(){
        $("#btnssubmit").text('Please wait...');
        $("#btnssubmit").attr('disabled',true);
      },
      success:function(data){
        if(data.status == 'success'){
          $("#btnssubmit").text('Save Record');
          $("#btnssubmit").attr('disabled',false);
          toastr.success(data.msg);
          window.location.href=data.redirect;
        }else{
          $("#btnssubmit").text('Save Record');
          $("#btnssubmit").attr('disabled',false);
          toastr.error(data.msg);
          return false;
        }
      },
      error:function(xhr){
        let err ="";
          $.each(xhr.responseJSON.errors, function(key,value){
            err += value;
          });
        toastr.error(err);
        $("#btnssubmit").text('Save Record');
        $("#btnssubmit").attr('disabled',false);
          return false;
      }
    });
   });
  });
</script>
@endsection