@extends('layout.app')
@section('title')
    Edit Loan Product
@endsection
@section('pagetitle')
Edit Loan Product
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
                    <form class="form-horizontal"  action="{{route('loan.product.update',['id' => $ed->id])}}" method="post" role="form"  onsubmit="thisForm()">
                      @csrf
                      
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Product Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="product_name" autofocus required value="{{$ed->name}}" autocomplete="off" placeholder="Enter Product Name">
                        </div>
                      </div>

                       <div class="form-group">
                        <label class="col-sm-4 control-label">product GL Type</label>
                        <div class="col-sm-7 controls">
                          <select class="form-control width-70 selgl" autocomplete="off" name="product_gl_type">
                            <option disabled selected>--Select--</option>
                          @foreach ($asstsgl as $item)
                               <option value="{{$item->gl_code}}" {{$ed->gl_code == $item->gl_code ? 'selected' : ''}}>{{ucwords($item->gl_name)." [".$item->gl_code."]"}}</option>
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
                               <option value="{{$item->gl_code}}" {{$ed->interest_gl == $item->gl_code ? 'selected' : ''}}>{{ucwords($item->gl_name)." [".$item->gl_code."]"}}</option>
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
                               <option value="{{$item->gl_code}}" {{$ed->incomefee_gl == $item->gl_code ? 'selected' : ''}}>{{ucwords($item->gl_name)." [".$item->gl_code."]"}}</option>
                            @endforeach
                          </select>
                        </div>
                    </div>

                      <hr>
                      <h5 class="text-danger">Principal Amount:</h5><br>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Disbursed By (select multiple)</label>
                        <div class="col-sm-7 controls">
                            <select multiple="" class="form-control width-70" autocomplete="off"  id="edsib" size="7" name="disburseby[]" data-placeholder="select disbursement type">
                                <option value="cheque" {{ in_array("cheque", (array)$ed->loan_disbursed_by) ? "selected" : ""}}>Cheque</option>
                                <option value="cash" {{ in_array("cash", (array)$ed->loan_disbursed_by) ? "selected" : ""}}>Cash</option>
                                <option value="transfer" {{ in_array("transfer", (array)$ed->loan_disbursed_by) ? "selected" : ""}}>Transfer </option>
                              </select>
                        </div>
                      </div>
                      
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
                                <option value="flat_rate" {{$ed->interest_method == 'flat_rate' ? 'selected' : ''}}>Flat Rate</option>
                                <option value="declining_balance_equal_installments" {{$ed->interest_method == 'declining_balance_equal_installments' ? 'selected' : ''}}>Declining Balance-Equal Installments</option>
                                <option value="declining_balance_equal_principal" {{$ed->interest_method == 'declining_balance_equal_principal' ? 'selected' : ''}}>Declining Balance-Equal principal</option>
                                <option value="interest_only" {{$ed->interest_method == 'interest_only' ? 'selected' : ''}}>Interest only</option>
                            </select>
                        </div>
                      </div>

                      
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Default Loan Interest %</label>
                        <div class="col-sm-7 controls">
                            <input class="form-control width-70 touchspin" placeholder="" name="default_interest_rate" type="number" step=".01" autocomplete="off" value="{{$ed->default_interest_rate}}">
                            
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Minimum Loan Interest %</label>
                        <div class="col-sm-7 controls">
                                <input class="form-control width-70 touchspin" placeholder="" name="minimum_interest_rate" type="number" step=".01" autocomplete="off" value="{{$ed->minimum_interest_rate}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Maximum Loan Interest %</label>
                        <div class="col-sm-7 controls">
                                <input class="form-control width-70 touchspin" placeholder="" name="maximum_interest_rate" type="number" step=".01" autocomplete="off" value="{{$ed->maximum_interest_rate}}">
                            </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Loan Interest Period</label>
                        <div class="col-sm-7 controls">
                                <select class="form-control width-70" required="required" id="inputDefaultInterestPeriod" name="interest_period">
                                    <option selected="selected" disabled>Select</option>
                                    <option value="day" {{$ed->interest_period == "day" ? "selected" : ""}}>Per Day</option>
                                    <option value="week" {{$ed->interest_period == "week" ? "selected" : ""}}>Per Week</option>
                                    <option value="month" {{$ed->interest_period == "month" ? "selected" : ""}}>Per Month</option>
                                    <option value="year" {{$ed->interest_period == "year" ? "selected" : ""}}>Per Year</option>
                                </select>
                        </div>
                      </div>

                      <p>If you override interest figure, system will use this figure when calculating interest for the schedule</p>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Override Interest</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" id="override_interest" name="override_interest" onchange="if(this.value == '1'){document.getElementById('overrideDiv').style.display='block';}else{document.getElementById('overrideDiv').style.display='none';}">
                                <option value="0" {{$ed->override_interest == "0" ? "selected" : ""}}>No</option>
                                <option value="1" {{$ed->override_interest == "1" ? "selected" : ""}}>Yes</option>
                            </select>                       
                             </div>
                      </div>
                      <div class="form-group" id="overrideDiv" style="display: none;">
                        <label class="col-sm-4 control-label">Override Interest Amount</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" name="override_interest_amount"  value="{{$ed->override_interest_amount}}">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Default Loan Duration</label>
                        <div class="col-sm-7 controls">
                            <div class="col-sm-3">
                                <input class="width-70 form-control" required="required" name="default_loan_duration" type="number" id="default_loan_duration"  value="{{$ed->default_loan_duration}}">
                            </div>
                            <div class="col-sm-6">
                                <select class="form-control width-90" required="required" id="inputMaxInterestPeriod" name="default_loan_duration_type">
                                    <option value="day" {{$ed->default_loan_duration_type == "day" ? "selected" : ""}}>Day(s)</option>
                                    <option value="week" {{$ed->default_loan_duration_type == "week" ? "selected" : ""}}>Week(s)</option>
                                    <option value="month" {{$ed->default_loan_duration_type == "month" ? "selected" : ""}}>Month(s)</option>
                                    <option value="year" {{$ed->default_loan_duration_type == "year" ? "selected" : ""}}>Year(s)</option>
                                </select>
                            </div>
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Repayments: </h5><br>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Repayment Cycle</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" required="required" id="" name="repayment_cycle">
                                <option value="daily" {{$ed->repayment_cycle == "daily" ? "selected" : ""}}>Daily</option>
                                <option value="weekly" {{$ed->repayment_cycle == "weekly" ? "selected" : ""}}>Weekly</option>
                                <option value="monthly" {{$ed->repayment_cycle == "monthly" ? "selected" : ""}}>Monthly</option>
                                <option value="bi_monthly" {{$ed->repayment_cycle == "bi_monthly" ? "selected" : ""}}>Bimonthly</option>
                                <option value="quarterly" {{$ed->repayment_cycle == "quarterly" ? "selected" : ""}}>Quarterly</option>
                                <option value="semi_annual" {{$ed->repayment_cycle == "semi_annual" ? "selected" : ""}}>Semi-Annual</option>
                                <option value="annual" {{$ed->repayment_cycle == "annual" ? "selected" : ""}}>Annually</option>
                            </select>                       
                             </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Repayment Order (select multiple)</label>
                        <div class="col-sm-7 controls">
                            <select multiple="" class="form-control width-70" autocomplete="off"  id="ro" size="7" name="repayment_order[]" data-placeholder="select Repayment Order">
                                <option value="fees" {{in_array("fees", (array)$ed->repayment_order) ? "selected" : ""}}>Fees</option>
                                <option value="interest" {{in_array("interest", (array)$ed->repayment_order) ? "selected" : ""}}>Interest</option>
                                <option value="principal" {{in_array("principal", (array)$ed->repayment_order) ? "selected" : ""}}>Principal</option>                     
                                <option value="penalty" {{in_array("penalty", (array)$ed->repayment_order) ? "selected" : ""}}>Penalty</option>
                              </select>  
                              </div>
                      </div>

                      @if (count($loanfees)>0)
                     <hr>
                     <h5 class="text-danger">Fees: </h5><br>

                     @foreach ($loanfees as $item)
                     <?php
                        $getloanfeemeta = DB::table('loan_fee_metas')->where('loan_fee_id',$item->id)
                                                                      ->where('parent_id',$ed->id)->first();
                     ?>
                     <input type="hidden" name="loanfees[]" id="loanfees" value="{{$item->id}}">
                    <div class="form-group">
                       <label for="loan_fees_1" class="col-sm-3 control-label">{{ucwords($item->name)}}&nbsp;{{$item->loan_fee_type == 'percentage' ? '(%)' : ''}}</label>
                       <div class="col-md-3">
                           <input class="form-control touchspin" name="loan_fees_amount[]" step="any" type="number" value="{{!empty($getloanfeemeta->value) ? $getloanfeemeta->value : ""}}">
                       </div>
                       <div class="col-sm-5">
                           <select class="form-control" required="required" id="" name="loan_fees_schedule[]">
                            <option value="distribute_fees_evenly" {{!empty($getloanfeemeta->loan_fees_schedule) && $getloanfeemeta->loan_fees_schedule == "distribute_fees_evenly" ? "selected" : ""}}>Distribute Fees Evenly</option>
                            <option value="charge_fees_on_first_payment" {{!empty($getloanfeemeta->loan_fees_schedule) && $getloanfeemeta->loan_fees_schedule == "charge_fees_on_first_payment" ? "selected" : ""}}>Charge Fees on first payment</option>
                            <option value="charge_fees_on_last_payment" {{!empty($getloanfeemeta->loan_fees_schedule) && $getloanfeemeta->loan_fees_schedule == "charge_fees_on_last_payment" ? "selected" : ""}}>Charge fees on last payment</option>
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
                          <input class="width-70" type="number" name="grace_on_interest_charged" placeholder="Enter number of days"  value="{{$ed->grace_on_interest_charged}}">
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
                                       {{$ed->enable_late_repayment_penalty == '1' ? 'checked="checked"' : ''}}>
                                <b>Enable Late Repayment Penalty?</b>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label"></label>
                        <div class="col-sm-7 controls">
                          <div class="vd_radio radio-success">
                            <input type="radio" value="percentage" {{$ed->late_repayment_penalty_type == "percentage" ? "checked='checked'" : ""}} autocomplete="off" name="late_repayment_penalty_type" id="yes2">
                            <label for="yes2">I want Penalty to be percentage % based</label>
                          </div>
                          <div class="vd_radio radio-success">
                            <input type="radio"  value="fixed" {{$ed->late_repayment_penalty_type == "fixed" ? "checked='checked'" : ""}} name="late_repayment_penalty_type" autocomplete="off" id="no2">
                            <label for="no2">I want Penalty to be a fixed amount</label>
                          </div>
                        </div>
                      </div>
                    
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Calculate Penalty on</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" id="late_repayment_penalty_calculate" name="late_repayment_penalty_calculate">
                                <option selected="selected" disabled>Select...</option>
                                <option value="overdue_principal" {{$ed->late_repayment_penalty_calculate == "overdue_principal" ? "selected" : ""}}>Overdue Principal Amount</option>
                                <option value="overdue_principal_interest" {{$ed->late_repayment_penalty_calculate == "overdue_principal_interest" ? "selected" : ""}}>Overdue Principal Amount + Overdue Interest</option>
                                <option value="overdue_principal_interest_fees" {{$ed->late_repayment_penalty_calculate == "overdue_principal_interest_fees" ? "selected" : ""}}>Overdue Principal Amount + Overdue Interest + Overdue Fees</option>
                                <option value="total_overdue" {{$ed->late_repayment_penalty_calculate == "total_overdue" ? "selected" : ""}}>Total Overdue Amount</option>
                            </select>             
                           </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Penalty Amount</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" id="late_repayment_penalty_amount" name="late_repayment_penalty_amount"  value="{{$ed->late_repayment_penalty_amount}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Grace Periods (Optional)</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" id="late_repayment_penalty_grace_period" placeholder="Enter number of days" name="late_repayment_penalty_grace_period"  value="{{$ed->late_repayment_penalty_grace_period}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">If penalty on Late Repayments is <u>recurring</u>, enter the number of days (Optional)</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" id="late_repayment_penalty_recurring" placeholder="Enter number of days" name="late_repayment_penalty_recurring"  value="{{$ed->late_repayment_penalty_recurring}}">
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">After Maturity Date Penalty:</h5><br>
                      <div class="form-group">
                        <div class="checkbox col-sm-10">
                            <label>
                                <input type="checkbox" name="enable_after_maturity_date_penalty"
                                id="enable_after_maturity_date_penalty"
                                       value="1" {{$ed->enable_after_maturity_date_penalty == '1' ? 'checked' : ''}}>
                                <b>Enable After Maturity Date Penalty?</b>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label"></label>
                        <div class="col-sm-7 controls">
                          <div class="vd_radio radio-success">
                            <input type="radio" value="percentage" autocomplete="off" {{$ed->after_maturity_date_penalty_type == "percentage" ? "checked" : ""}} name="after_maturity_date_penalty_type" id="yes4">
                            <label for="yes4">I want Penalty to be percentage % based</label>
                          </div>
                          <div class="vd_radio radio-success">
                            <input type="radio"  value="fixed" name="after_maturity_date_penalty_type" {{$ed->after_maturity_date_penalty_type == "fixed" ? "checked" : ""}} autocomplete="off" id="no4">
                            <label for="no4">I want Penalty to be a fixed amount</label>
                          </div>

                        </div>
                      </div>
                    
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Calculate Penalty on</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" id="after_maturity_date_penalty_calculate" name="after_maturity_date_penalty_calculate">
                                <option selected="selected" disabled>Select...</option>
                                <option value="overdue_principal" {{$ed->after_maturity_date_penalty_calculate == "overdue_principal" ? "selected" : ""}}>Overdue Principal Amount</option>
                                <option value="overdue_principal_interest" {{$ed->after_maturity_date_penalty_calculate == "overdue_principal_interest" ? "selected" : ""}}>Overdue Principal Amount + Overdue Interest</option>
                                <option value="overdue_principal_interest_fees" {{$ed->after_maturity_date_penalty_calculate == "overdue_principal_interest_fees" ? "selected" : ""}}>Overdue Principal Amount + Overdue Interest + Overdue Fees</option>
                                <option value="total_overdue" {{$ed->after_maturity_date_penalty_calculate == "total_overdue" ? "selected" : ""}}>Total Overdue Amount</option>
                            </select>             
                           </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Penalty Amount</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" id="after_maturity_date_penalty_amount" name="after_maturity_date_penalty_amount"  value="{{$ed->after_maturity_date_penalty_amount}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Grace Periods (Optional)</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" id="after_maturity_date_penalty_grace_period" placeholder="Enter number of days" name="after_maturity_date_penalty_grace_period"  value="{{$ed->after_maturity_date_penalty_grace_period}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">If penalty on Late Repayments is <u>recurring</u>, enter the number of days (Optional)</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" id="after_maturity_date_penalty_recurring" placeholder="Enter number of days" name="after_maturity_date_penalty_recurring"  value="{{$ed->after_maturity_date_penalty_recurring}}">
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
    $(".selgl").select2();
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

   $("#submitloanproduct").submit(function(e){
    e.preventDefault();
    $.ajax({
      url: $("#submitloanproduct").attr('action'),
      method:'post',
      data:$("#submitloanproduct").serialize(),
      beforeSend:function(){
        $("#btnssubmit").text('Please wait...');
      },
      success:function(data){
        if(data.status == '1'){
          $("#btnssubmit").text('Save Record');
          alert(data.msg);
          window.location.href=data.redirect;
        }else{
          $("#btnssubmit").text('Save Record');
          alert(data.msg);
          return false;
        }
      },
      error:function(xhr){
        $("#btnssubmit").text('Save Record');
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