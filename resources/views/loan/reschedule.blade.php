@extends('layout.app')
@section('title')
    Reschedule Loan
@endsection
@section('pagetitle')
Reschedule Loan
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{!empty($_GET['return_url']) ? url($_GET['return_url']) : ""}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                     <form action="{{route('loan.reschedule.store',['id' => $loan->id])}}" method="post" onsubmit="thisForm()">
                      @csrf
                      @if(!empty($_GET['return_url']))
                      <input type="hidden" value="{{$_GET['return_url']}}" name="return_url">
                  @endif
                  <input type="hidden" value="{{$loan->customer->id}}" name="customerid">

                      <div class="form-group">
                        <label>Loan Product</label>
                        <select name="loan_product_id" class="form-control" required id="loan_product">
                            <option>Select a Loan Product</option>
                                @foreach ($loanprod as $item)
                                   <option value="{{$item->id}}" {{$item->id == $loan->loan_product->id ? 'selected' : ''}}>{{$item->name}}</option> 
                                @endforeach
                        </select>
                    </div>
                    <hr>
            <p class="bg-danger">Loan Term Required Field:</p>

            <div class="form-group">
                <label for="principal">Principal Amount</label>
                <input type="text" name="principal" class="form-control width-70" required value="{{$principal}}">
              </div>

                <hr>
                <p class="text-red"><b>Duration:</b></p>
    
                <div class="form-group">
                    <label for="loan_duration">Loan Duration</label>
                     <div class="row">
                      <div class="col-md-6 col-sm-12 col-lg-6">
                        <input type="number" name="loan_duration" id="loan_duration width-90" required value="{{$loan->loan_product->default_loan_duration}}">     
                    </div>    
                      <div class="col-md-6 col-sm-12 col-lg-6">
                        <select class="form-control width-90" name="loan_duration_type" required="required" id="period">
                            <option value="day" {{$loan->loan_product->default_loan_duration_type == "day" ? "selected" : ""}}>Day(s)</option>
                            <option value="week" {{$loan->loan_product->default_loan_duration_type == "week" ? "selected" : ""}}>Week(s)</option>
                            <option value="month" {{$loan->loan_product->default_loan_duration_type == "month" ? "selected" : ""}}>Month(s)</option>
                            <option value="year" {{$loan->loan_product->default_loan_duration_type == "year" ? "selected" : ""}}>Year(s)</option>
                        </select>
                    </div>    
                    </div>               
                    
                </div>
                <hr>
                <p class="text-red"><b>Repayment:</b></p>
    
                <div class="form-group">
                    <label for="">Repayment Cycle</label>
                    <select class="form-control width-70"  required="required" id="" name="repayment_cycle">
                        <option value="daily" {{$loan->loan_product->repayment_cycle == "daily" ? "selected" : ""}}>Daily</option>
                        <option value="weekly" {{$loan->loan_product->repayment_cycle == "weekly" ? "selected" : ""}}>Weekly</option>
                        <option value="monthly" {{$loan->loan_product->repayment_cycle == "monthly" ? "selected" : ""}}>Monthly</option>
                        <option value="bi_monthly" {{$loan->loan_product->repayment_cycle == "bi_monthly" ? "selected" : ""}}>Bimonthly</option>
                        <option value="quarterly" {{$loan->loan_product->repayment_cycle == "quarterly" ? "selected" : ""}}>Quarterly</option>
                        <option value="semi_annual" {{$loan->loan_product->repayment_cycle == "semi_annual" ? "selected" : ""}}>Semi-Annual</option>
                        <option value="annually" {{$loan->loan_product->repayment_cycle == "annual" ? "selected" : ""}}>Annually</option>
                    </select>
                </div>
                <hr>
    
                <div class="form-group">
                    <label>Release Date</label>
                    <input class="width-70 form-control" type="date" name="release_date" required  id="dsdate" value="{{old('release_date')}}">
                </div>
    
                <p>First Payment Date (Optional)</p>
    
                <div class="form-group">
                    <label for="">First Repayment Date</label>
                    <input class="width-70 form-control" type="date" name="first_payment_date" id="frdate" value="{{old('first_payment_date')}}">
                </div>
    
                <hr>
                <p class="text-red"><b>Interest:</b></p><br>
                <div class="form-group">
                    <label for="">Interest Method</label>
               
                    <select class="form-control width-70" required="required" id="interest_method" name="interest_method">
                        <option value="flat_rate" {{$loan->loan_product->interest_method == "flat_rate" ? "selected" : ""}}>Flat Rate</option>
                        <option value="declining_balance_equal_installments" {{$loan->loan_product->interest_method == "declining_balance_equal_installments" ? "selected" : ""}}>Declining Balance-Equal Installments</option>
                        <option value="declining_balance_equal_principal" {{$loan->loan_product->interest_method == "declining_balance_equal_principal" ? "selected" : ""}}>Declining Balance-Equal principal</option>
                        <option value="interest_only" {{$loan->loan_product->interest_method == "interest_only" ? "selected" : ""}}>Interest only</option>
                    </select>
                </div>
    
                <div class="form-group">
                    <label>Loan Interest %</label>
                    <div class="row">
                        <div class="col-sm-3">
                            <input class="width-70 form-control" name="interest_rate" id="linterest" required="required" value="{{$loan->loan_product->default_interest_rate}}"  type="number" >
                        </div>
                        <div class="col-sm-6">
                            <select class="form-control width-90" name="interest_period" required="required" id="period">
                                <option value="day" {{$loan->loan_product->interest_period == "day" ? "selected" : ""}}>Day(s)</option>
                                <option value="week" {{$loan->loan_product->interest_period == "week" ? "selected" : ""}}>Week(s)</option>
                                <option value="month" {{$loan->loan_product->interest_period == "month" ? "selected" : ""}}>Month(s)</option>
                                <option value="year" {{$loan->loan_product->interest_period == "year" ? "selected" : ""}}>Year(s)</option></select>
                        </div>
                    </div>
                </div>
    
                <p>If you override interest figure, system will use this figure when calculating interest for the schedule</p>
                <div class="form-group">
                    <label for="">Override Interest</label>
                    <select class="form-control width-70" id="override_interest" name="override_interest" onchange="if(this.value == '1'){document.getElementById('overrideDiv').style.display='block';}else{document.getElementById('overrideDiv').style.display='none';}">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select> 
                </div>
                <div class="form-group" id="overrideDiv" style="display: none;">
                    <label>Override Interest Amount</label>
                      <input class="width-70 form-control" type="number" name="override_interest_amount"  value="{{old('override_interest_amount')}}">
                  </div>
    
                  <div class="form-group">
                    <label>Grace on interest charged</label>
                    <input class="width-70 form-control" type="number" name="grace_on_interest_charged" placeholder="Enter number of days"  value="{{$loan->loan_product->grace_on_interest_charged}}">
                </div>
               
                @if (count($loanfees)>0)
                <hr>
                <h5 class="text-danger">Fees: </h5><br>
    
                @foreach ($loanfees as $item)
                <?php
                   $getloanfeemeta = DB::table('loan_fee_metas')->where('loan_fee_id',$item->id)
                                                                 ->where('parent_id',$loan->id)->first();
                ?>
                <input type="hidden" name="loanfees[]" id="loanfees" value="{{$item->id}}">
                <input type="hidden" name="loan_fees_type[]" id="" value="{{$item->loan_fee_type}}">
                <div class="form-group">
                  <label for="loan_fees_1" class="col-sm-3 control-label">{{ucwords($item->name)}}&nbsp;{{$item->loan_fee_type == 'percentage' ? '(%)' : ''}}</label>
                  <div class="col-md-3">
                      <input class="form-control touchspin" name="loan_fees_amount[]" type="number" value="{{$getloanfeemeta->value}}">
                  </div>
                  <div class="col-sm-5">
                      <select class="form-control" required="required" id="" name="loan_fees_schedule[]">
                       <option value="distribute_fees_evenly" {{$getloanfeemeta->loan_fees_schedule == "distribute_fees_evenly" ? "selected" : ""}}>Distribute Fees Evenly</option>
                       <option value="charge_fees_on_first_payment" {{$getloanfeemeta->loan_fees_schedule == "charge_fees_on_first_payment" ? "selected" : ""}}>Charge Fees on first payment</option>
                       <option value="charge_fees_on_last_payment" {{$getloanfeemeta->loan_fees_schedule == "charge_fees_on_last_payment" ? "selected" : ""}}>Charge fees on last payment</option>
                      </select>
                  </div>
              </div>
                @endforeach
                @endif
                <hr><br><br>
                <div class="form-group">
                    <label>Description(Optional)</label>
                        <textarea class="form-control width-70" id="editor" rows="5" name="description" cols="50"></textarea>
                  </div>
                  <div class="form-group">
                    <label>Loan Files(doc, pdf, image)</label>
                      <input class="width-70" autocomplete="off"  name="files" type="file" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx">
                  </div>
                          <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7" style="margin: 10px 0px">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit">Reschedule Record</button>
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