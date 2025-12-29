@extends('layout.app')
@section('title')
    Edit Fixed Deposit Product
@endsection
@section('pagetitle')
Edit Fixed Deposit Product
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
                    <form class="form-horizontal"  action="{{route('update.fdproduct',['id' => $ed->id])}}" method="post" role="form" id="submitinveproduct" onsubmit="thisForm()">
                      @csrf
                      
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Product Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="product_name" autofocus required value="{{$ed->name}}" autocomplete="off" placeholder="Enter Product Name">
                        </div>
                      </div>
                      <hr>
                      <h5 class="text-danger">Principal Amount:</h5><br>
                      
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Minimum Principal</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" name="minimum_principal" id="brnd" value="{{$ed->minimum_principal}}">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Default Principal</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" name="default_principal"  value="{{$ed->default_principal}}" >
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Maximum Principal</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" name="maximum_principal"  value="{{$ed->maximum_principal}}">
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Interest Rating: </h5><br>

                       <div class="form-group">
                        <label class="col-sm-4 control-label">Interest Method</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" required="required" id="interest_method" name="interest_method">
                                <option value="flat_rate" {{$ed->interest_method == 'upfront' ? 'selected' : ''}}>Upfront</option>
                                <option value="monthly" {{$ed->interest_method == 'monthly' ? 'selected' : ''}}>Monthly</option>
                                <option value="rollover" {{$ed->interest_method == 'rollover' ? 'selected' : ''}}>RollOver</option>
                            </select>
                        </div>
                      </div>

                      
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Default  Interest %</label>
                        <div class="col-sm-7 controls">
                            <input class="form-control width-70 touchspin" placeholder="" name="default_interest_rate" type="number" step=".01" autocomplete="off" value="{{$ed->default_interest_rate}}">
                            
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Minimum Interest %</label>
                        <div class="col-sm-7 controls">
                                <input class="form-control width-70 touchspin" placeholder="" name="minimum_interest_rate" type="number" step=".01" autocomplete="off" value="{{$ed->minimum_interest_rate}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Maximum Interest %</label>
                        <div class="col-sm-7 controls">
                                <input class="form-control width-70 touchspin" placeholder="" name="maximum_interest_rate" type="number" step=".01" autocomplete="off" value="{{$ed->maximum_interest_rate}}">
                            </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label"> Interest Period</label>
                        <div class="col-sm-7 controls">
                                <select class="form-control width-70" required="required" id="inputDefaultInterestPeriod" name="interest_period">
                                    <option selected="selected" disabled>Select</option>
                                    <option value="month" {{$ed->interest_period == "month" ? "selected" : ""}}>Per Month</option>
                                    <option value="year" {{$ed->interest_period == "year" ? "selected" : ""}}>Per Year</option>
                                </select>
                        </div>
                      </div>

                 

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Default Duration</label>
                        <div class="col-sm-7 controls">
                            <div class="col-sm-3">
                                <input class="width-70 form-control" required="required" name="default_duration" type="number" id="default_duration"  value="{{$ed->default_duration}}">
                            </div>
                            <div class="col-sm-6">
                                <select class="form-control width-90" required="required" id="inputMaxInterestPeriod" name="default_duration_type">
                                    <option value="month" {{$ed->default_loan_duration_type == "month" ? "selected" : ""}}>Month(s)</option>
                                    <option value="year" {{$ed->default_loan_duration_type == "year" ? "selected" : ""}}>Year(s)</option>
                                </select>
                            </div>
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Interest Payments: </h5><br>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Interest Payments</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" required="required" id="" name="repayment_cycle">
                                <option value="monthly" {{$ed->repayment_cycle == "monthly" ? "selected" : ""}}>Monthly</option>
                                <option value="quarterly" {{$ed->repayment_cycle == "quarterly" ? "selected" : ""}}>Quarterly</option>
                                <option value="semi_annual" {{$ed->repayment_cycle == "semi_annual" ? "selected" : ""}}>Semi-Annual</option>
                                <option value="annual" {{$ed->repayment_cycle == "annual" ? "selected" : ""}}>Annually</option>
                            </select>                       
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
    $("#ro").select2();
    $("#edsib").select2();
    let cherpymatuy = $("#enable_after_maturity_date_penalty");
    let cherpypen = $("#enable_late_repayment_penalty");

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

   $("#enable_after_maturity_date_penalty").change(function(){
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
          $("#btnssubmit").text('Update Record');
          $("#btnssubmit").attr('disabled',false);
          toastr.success(data.msg);
          window.location.href=data.redirect;
        }else{
          $("#btnssubmit").text('Update Record');
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
        $("#btnssubmit").text('Update Record');
        $("#btnssubmit").attr('disabled',false);
          return false;
      }
    });
   });
  });
</script>
@endsection