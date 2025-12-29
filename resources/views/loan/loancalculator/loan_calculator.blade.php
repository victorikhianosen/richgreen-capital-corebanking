@extends('layout.app')
@section('title')
    Calculate Loan Expectation
@endsection
@section('pagetitle')
Calculate Loan Expectation
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    {{-- <div style="text-align: end">
                       <a href="{{route('loan.fee.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div> --}}
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal" action="{{route('calculate-show')}}"   method="post" role="form" onsubmit="thisForm()">
                     @csrf
                        <hr>
                        <h5 class="text-danger">Principal Amount: </h5><br>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Principal Amount</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" name="principal"  autofocus required autocomplete="off" placeholder="Enter Loan Principal Amount">
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Duration: </h5><br>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Loan Duration</label>
                        <div class="col-sm-7 controls">
                            <div class="col-sm-3">
                                <input class="width-70 form-control" name="loan_duration" id="lduration" required="required"  type="number" >
                            </div>
                            <div class="col-sm-6">
                                <select class="form-control width-90" name="loan_duration_type" required="required" id="period">
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
                            <select class="form-control width-70"  required="required" id="" name="repayment_cycle">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="bi_monthly">Bimonthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="semi_annual">Semi-Annual</option>
                                <option value="annually">Annually</option>
                            </select>                       
                             </div>
                      </div>

                      <div class="form-group" >
                        <label class="col-sm-4 control-label">Expected Disbursement Date</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="date" name="release_date"  id="dsdate" value="{{date('m/d/Y')}}">
                        </div>
                      </div>
                      <div class="form-group" >
                        <label class="col-sm-4 control-label">First Repayment Date</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="date" name="first_payment_date" id="frdate">
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
                        <label class="col-sm-4 control-label">Loan Interest %</label>

                        <div class="col-sm-7 controls">
                            <div class="col-sm-3">
                                <input class="width-70 form-control" name="interest_rate" id="linterest" required="required" step=".01"  type="number" >
                            </div>
                            <div class="col-sm-6">
                                <select class="form-control width-90" name="interest_period" required="required" id="period">
                                    <option value="day">Day(s)</option>
                                    <option value="week">Week(s)</option>
                                    <option value="month">Month(s)</option>
                                    <option value="year">Year(s)</option></select>
                            </div>
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
                           <input class="form-control touchspin" name="loan_fees_amount[]" type="number">
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
                      {{-- <div class="form-group" >
                        <label class="col-sm-4 control-label">Grace on interest charged(Enter number of payments before interest is charged)</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" id="grace" value="0">
                        </div>
                      </div> --}}

                       <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit">Calculate</button>
                              
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
  });
</script>
@endsection