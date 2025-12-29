@extends('layout.app')
@section('title')
    Create Loan Product
@endsection
@section('pagetitle')
Create Loan Product
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('loan.product.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('loan.product.store')}}" method="post" onsubmit="thisForm()" role="form">
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
                        <label class="col-sm-4 control-label">Disbursed By (select multiple)</label>
                        <div class="col-sm-7 controls">
                          <select multiple="" class="form-control width-70 selgl" autocomplete="off"  id="sibo" size="7" name="disburseby[]" data-placeholder="select disbursement type">
                            <option value="cheque">Cheque</option>
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer </option>
                          </select>
                        </div>
                      </div>
                      
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
                                <option value="flat_rate">Flat Rate</option>
                                <option value="declining_balance_equal_installments">Declining Balance-Equal Installments</option>
                                <option value="declining_balance_equal_principal">Declining Balance-Equal principal</option>
                                <option value="interest_only">Interest only</option>
                            </select>
                        </div>
                      </div>

                      
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Default Loan Interest %</label>
                        <div class="col-sm-7 controls">
                            <input class="form-control width-70 touchspin" placeholder="" name="default_interest_rate" step=".01" type="number" autocomplete="off" value="0">
                            
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Minimum Loan Interest %</label>
                        <div class="col-sm-7 controls"> step=".01"
                                <input class="form-control width-70 touchspin" placeholder="" name="minimum_interest_rate" step=".01" type="number" autocomplete="off" value="0">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Maximum Loan Interest %</label>
                        <div class="col-sm-7 controls">
                                <input class="form-control width-70 touchspin" placeholder="" name="maximum_interest_rate" step=".01" type="number" autocomplete="off" value="0">
                            </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Loan Interest Period</label>
                        <div class="col-sm-7 controls">
                                <select class="form-control width-70" required="required" id="inputDefaultInterestPeriod" name="interest_period">
                                    <option selected="selected" disabled>Select</option>
                                    <option value="day">Per Day</option>
                                    <option value="week">Per Week</option>
                                    <option value="month">Per Month</option>
                                    <option value="year">Per Year</option>
                                </select>
                        </div>
                      </div>

                      <p>If you override interest figure, system will use this figure when calculating interest for the schedule</p>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Override Interest</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" id="override_interest" name="override_interest" onchange="if(this.value == '1'){document.getElementById('overrideDiv').style.display='block';}else{document.getElementById('overrideDiv').style.display='none';}">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>                       
                             </div>
                      </div>
                      <div class="form-group" id="overrideDiv" style="display: none;">
                        <label class="col-sm-4 control-label">Override Interest Amount</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" name="override_interest_amount"  value="{{old('override_interest_amount')}}">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Default Loan Duration</label>
                        <div class="col-sm-7 controls">
                            <div class="col-sm-3">
                                <input class="width-70 form-control" required="required" name="default_loan_duration" type="number" id="default_loan_duration"  value="{{old('default_loan_duration')}}">
                            </div>
                            <div class="col-sm-6">
                                <select class="form-control width-90" required="required" id="inputMaxInterestPeriod" name="default_loan_duration_type">
                                    <option value="day">Day(s)</option>
                                    <option value="week">Week(s)</option>
                                    <option value="month">Month(s)</option>
                                    <option value="year">Year(s)</option></select>
                            </div>
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Repayments: </h5><br>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Repayment Cycle</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" required="required" id="" name="repayment_cycle">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="bi_monthly">Bimonthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="semi_annual">Semi-Annual</option>
                                <option value="annual">Annually</option>
                            </select>                       
                             </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Repayment Order (select multiple)</label>
                        <div class="col-sm-7 controls">
                            <select multiple="" class="form-control width-70" autocomplete="off"  id="ro" size="7" name="repayment_order[]" data-placeholder="select Repayment Order">
                                <option value="fees">Fees</option>
                                <option value="interest">Interest</option>
                                <option value="principal">Principal</option>
                                <option value="penalty">Penalty</option>
                              </select>                       
                             </div>
                      </div>

                     @if (count($loanfees)>0)
                     <hr>
                     <h5 class="text-danger">Fees: </h5><br>

                     @foreach ($loanfees as $item)
                     <input type="hidden" name="loanfees[]" id="loanfees" value="{{$item->id}}">
                     <div class="form-group">
                       <label for="loan_fees_1" class="col-sm-3 control-label">{{ucwords($item->name)}}</label>
                       <div class="col-md-3">
                           <input class="form-control touchspin" step="any" name="loan_fees_amount[]" type="number">
                       </div>
                       <div class="col-sm-5">
                           <select class="form-control" required="required" id="" name="loan_fees_schedule[]">
                             <option value="distribute_fees_evenly">Distribute Fees Evenly</option>
                             <option value="charge_fees_on_first_payment">Charge Fees on first payment</option>
                             <option value="charge_fees_on_last_payment">Charge fees on last payment</option>
                           </select>
                       </div>
                   </div>
                     @endforeach
                     @endif

                      <hr>
                      <h5 class="text-danger">Grace Periods: </h5><br>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Grace on interest charged</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" name="grace_on_interest_charged" placeholder="Enter number of days"  value="{{old('grace_on_interest_charged')}}">
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Late Repayment Penalty: </h5><br>

                      <div class="form-group">
                        <div class="checkbox col-sm-10">
                            <label>
                                <input type="checkbox" name="enable_late_repayment_penalty"
                                       id="enable_late_repayment_penalty"
                                       value="1"
                                        >
                                <b>Enable Late Repayment Penalty?</b>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label"></label>
                        <div class="col-sm-7 controls">
                          <div class="vd_radio radio-success">
                            <input type="radio" value="percentage" checked="checked" autocomplete="off" name="late_repayment_penalty_type" id="yes2">
                            <label for="yes2">I want Penalty to be percentage % based</label>
                          </div>
                          <div class="vd_radio radio-success">
                            <input type="radio"  value="fixed" name="late_repayment_penalty_type" autocomplete="off" id="no2">
                            <label for="no2">I want Penalty to be a fixed amount</label>
                          </div>

                        </div>
                      </div>
                    
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Calculate Penalty on</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" id="late_repayment_penalty_calculate" name="late_repayment_penalty_calculate">
                                <option selected="selected" disabled>Select...</option>
                                <option value="overdue_principal">Overdue Principal Amount</option>
                                <option value="overdue_principal_interest">Overdue Principal Amount + Overdue Interest</option>
                                <option value="overdue_principal_interest_fees">Overdue Principal Amount + Overdue Interest + Overdue Fees</option>
                                <option value="total_overdue">Total Overdue Amount</option></select>             
                           </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Penalty Amount</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" id="late_repayment_penalty_amount" name="late_repayment_penalty_amount"  value="{{old('late_repayment_penalty_amount')}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Grace Periods (Optional)</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" id="late_repayment_penalty_grace_period" placeholder="Enter number of days" name="late_repayment_penalty_grace_period"  value="{{old('late_repayment_penalty_grace_period')}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">If penalty on Late Repayments is <u>recurring</u>, enter the number of days (Optional)</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" id="late_repayment_penalty_recurring" placeholder="Enter number of days" name="late_repayment_penalty_recurring"  value="{{old('late_repayment_penalty_recurring')}}">
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">After Maturity Date Penalty:</h5><br>
                      <div class="form-group">
                        <div class="checkbox col-sm-10">
                            <label>
                                <input type="checkbox" name="enable_after_maturity_date_penalty"
                                id="enable_after_maturity_date_penalty"
                                       value="1">
                                <b>Enable After Maturity Date Penalty?</b>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label"></label>
                        <div class="col-sm-7 controls">
                          <div class="vd_radio radio-success">
                            <input type="radio" value="percentage" checked="checked" autocomplete="off" name="after_maturity_date_penalty_type" id="yes4">
                            <label for="yes4">I want Penalty to be percentage % based</label>
                          </div>
                          <div class="vd_radio radio-success">
                            <input type="radio"  value="fixed" name="after_maturity_date_penalty_type" autocomplete="off" id="no4">
                            <label for="no4">I want Penalty to be a fixed amount</label>
                          </div>

                        </div>
                      </div>
                    
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Calculate Penalty on</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" id="after_maturity_date_penalty_calculate" name="after_maturity_date_penalty_calculate">
                                <option selected="selected" disabled>Select...</option>
                                <option value="overdue_principal">Overdue Principal Amount</option>
                                <option value="overdue_principal_interest">Overdue Principal Amount + Overdue Interest</option>
                                <option value="overdue_principal_interest_fees">Overdue Principal Amount + Overdue Interest + Overdue Fees</option>
                                <option value="total_overdue">Total Overdue Amount</option></select>             
                           </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Penalty Amount</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" id="after_maturity_date_penalty_amount" name="after_maturity_date_penalty_amount"  value="{{old('after_maturity_date_penalty_amount')}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Grace Periods (Optional)</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" id="after_maturity_date_penalty_grace_period" placeholder="Enter number of days" name="after_maturity_date_penalty_grace_period"  value="{{old('after_maturity_date_penalty_grace_period')}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">If penalty on Late Repayments is <u>recurring</u>, enter the number of days (Optional)</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" id="after_maturity_date_penalty_recurring" placeholder="Enter number of days" name="after_maturity_date_penalty_recurring"  value="{{old('after_maturity_date_penalty_recurring')}}">
                        </div>
                      </div>
                      {{-- <div class="form-group">
                        <label class="col-sm-2 control-label">Maximum Principal</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" name="maximum_principal"  value="{{old('maximum_principal')}}">
                        </div>
                      </div> --}}
                      
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
    $(".selgl").select2();
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

   $("#submitloanproduct").submit(function(e){
    e.preventDefault();
    $.ajax({
      url: $("#submitloanproduct").attr('action'),
      method:'post',
      data:$("#submitloanproduct").serialize(),
      beforeSend:function(){
        $("#btnssubmit").text('Please wait...');
        $("#btnssubmit").attr('disabled',true);
      },
      success:function(data){
        if(data.status == '1'){
          $("#btnssubmit").text('Save Record');
          $("#btnssubmit").attr('disabled',false);
          alert(data.msg);
          window.location.href=data.redirect;
        }else{
          $("#btnssubmit").text('Save Record');
          $("#btnssubmit").attr('disabled',false);
          alert(data.msg);
          return false;
        }
      },
      error:function(xhr){
        $("#btnssubmit").text('Save Record');
        $("#btnssubmit").attr('disabled',false);
        alert(
          $.each(xhr.responseJSON.errors, function(key,value){
            value
          })
        );
          return false;
      }
    });
   });
  });
</script>
@endsection