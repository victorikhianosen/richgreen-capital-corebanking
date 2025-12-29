@extends('layout.app')
@section('title')
    Loan Calculate Result
@endsection
@section('pagetitle')
Loan Calculate Result
@endsection
@section('content')
  <div class="container" id="fullwidth">
    <div class="row" id="advanced-input">
              <div class="col-md-12 col-lg-12">
                <div class="panel widget">
                  <div class="panel-heading noprint">
                    <div style="text-align: end">
                       <a href="{{route('lcalcu')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <?php
                      $getsetvalue = new \App\Models\Setting();
                    //determine interest rate
                    $interest_rate = 0;
                    if ($request->repayment_cycle == 'annually') {
                        //return the interest per year
                        if ($request->interest_period == 'year') {
                            $interest_rate = $request->interest_rate;
                        }
                        if ($request->interest_period == 'month') {
                            $interest_rate = $request->interest_rate * 12;
                        }
                        if ($request->interest_period == 'week') {
                            $interest_rate = $request->interest_rate * 52;
                        }
                        if ($request->interest_period == 'day') {
                            $interest_rate = $request->interest_rate * 365;
                        }
                    }
                    if ($request->repayment_cycle == 'semi_annually') {
                        //return the interest per semi annually
                        if ($request->interest_period == 'year') {
                            $interest_rate = $request->interest_rate / 2;
                        }
                        if ($request->interest_period == 'month') {
                            $interest_rate = $request->interest_rate * 6;
                        }
                        if ($request->interest_period == 'week') {
                            $interest_rate = $request->interest_rate * 26;
                        }
                        if ($request->interest_period == 'day') {
                            $interest_rate = $request->interest_rate * 182.5;
                        }
                    }
                    if ($request->repayment_cycle == 'quarterly') {
                        //return the interest per quaterly
                
                        if ($request->interest_period == 'year') {
                            $interest_rate = $request->interest_rate / 4;
                        }
                        if ($request->interest_period == 'month') {
                            $interest_rate = $request->interest_rate * 3;
                        }
                        if ($request->interest_period == 'week') {
                            $interest_rate = $request->interest_rate * 13;
                        }
                        if ($request->interest_period == 'day') {
                            $interest_rate = $request->interest_rate * 91.25;
                        }
                    }
                    if ($request->repayment_cycle == 'bi_monthly') {
                        //return the interest per bi-monthly
                        if ($request->interest_period == 'year') {
                            $interest_rate = $request->interest_rate / 6;
                        }
                        if ($request->interest_period == 'month') {
                            $interest_rate = $request->interest_rate * 2;
                        }
                        if ($request->interest_period == 'week') {
                            $interest_rate = $request->interest_rate * 8.67;
                        }
                        if ($request->interest_period == 'day') {
                            $interest_rate = $request->interest_rate * 58.67;
                        }
                
                    }
                
                    if ($request->repayment_cycle == 'monthly') {
                        //return the interest per monthly
                
                        if ($request->interest_period == 'year') {
                            $interest_rate = $request->interest_rate / 12;
                        }
                        if ($request->interest_period == 'month') {
                            $interest_rate = $request->interest_rate * 1;
                        }
                        if ($request->interest_period == 'week') {
                            $interest_rate = $request->interest_rate * 4.33;
                        }
                        if ($request->interest_period == 'day') {
                            $interest_rate = $request->interest_rate * 30.4;
                        }
                    }
                    if ($request->repayment_cycle == 'weekly') {
                        //return the interest per weekly
                
                        if ($request->interest_period == 'year') {
                            $interest_rate = $request->interest_rate / 52;
                        }
                        if ($request->interest_period == 'month') {
                            $interest_rate = $request->interest_rate / 4;
                        }
                        if ($request->interest_period == 'week') {
                            $interest_rate = $request->interest_rate;
                        }
                        if ($request->interest_period == 'day') {
                            $interest_rate = $request->interest_rate * 7;
                        }
                    }
                    if ($request->repayment_cycle == 'daily') {
                        //return the interest per day
                
                        if ($request->interest_period == 'year') {
                            $interest_rate = $request->interest_rate / 365;
                        }
                        if ($request->interest_period == 'month') {
                            $interest_rate = $request->interest_rate / 30.4;
                        }
                        if ($request->interest_period == 'week') {
                            $interest_rate = $request->interest_rate / 7.02;
                        }
                        if ($request->interest_period == 'day') {
                            $interest_rate = $request->interest_rate * 1;
                        }
                    }
                    $interest_rate = $interest_rate / 100;
                    $period = 0;
                    if ($request->repayment_cycle == 'annually') {
                        if ($request->loan_duration_type == 'year') {
                            $period = ceil($request->loan_duration);
                        }
                        if ($request->loan_duration_type == 'month') {
                            $period = ceil($request->loan_duration * 12);
                        }
                        if ($request->loan_duration_type == 'week') {
                            $period = ceil($request->loan_duration * 52);
                        }
                        if ($request->loan_duration_type == 'day') {
                            $period = ceil($request->loan_duration * 365);
                        }
                    }
                    if ($request->repayment_cycle == 'semi_annually') {
                        if ($request->loan_duration_type == 'year') {
                            $period = ceil($request->loan_duration * 2);
                        }
                        if ($request->loan_duration_type == 'month') {
                            $period = ceil($request->loan_duration * 6);
                        }
                        if ($request->loan_duration_type == 'week') {
                            $period = ceil($request->loan_duration * 26);
                        }
                        if ($request->loan_duration_type == 'day') {
                            $period = ceil($request->loan_duration * 182.5);
                        }
                    }
                    if ($request->repayment_cycle == 'quarterly') {
                        if ($request->loan_duration_type == 'year') {
                            $period = ceil($request->loan_duration);
                        }
                        if ($request->loan_duration_type == 'month') {
                            $period = ceil($request->loan_duration * 12);
                        }
                        if ($request->loan_duration_type == 'week') {
                            $period = ceil($request->loan_duration * 52);
                        }
                        if ($request->loan_duration_type == 'day') {
                            $period = ceil($request->loan_duration * 365);
                        }
                    }
                    if ($request->repayment_cycle == 'bi_monthly') {
                
                    }
                
                    if ($request->repayment_cycle == 'monthly') {
                        if ($request->loan_duration_type == 'year') {
                            $period = ceil($request->loan_duration * 12);
                        }
                        if ($request->loan_duration_type == 'month') {
                            $period = ceil($request->loan_duration);
                        }
                        if ($request->loan_duration_type == 'week') {
                            $period = ceil($request->loan_duration * 4.3);
                        }
                        if ($request->loan_duration_type == 'day') {
                            $period = ceil($request->loan_duration * 30.4);
                        }
                    }
                    if ($request->repayment_cycle == 'weekly') {
                        if ($request->loan_duration_type == 'year') {
                            $period = ceil($request->loan_duration * 52);
                        }
                        if ($request->loan_duration_type == 'month') {
                            $period = ceil($request->loan_duration * 4);
                        }
                        if ($request->loan_duration_type == 'week') {
                            $period = ceil($request->loan_duration * 1);
                        }
                        if ($request->loan_duration_type == 'day') {
                            $period = ceil($request->loan_duration * 7);
                        }
                    }
                    if ($request->repayment_cycle == 'daily') {
                        if ($request->loan_duration_type == 'year') {
                            $period = ceil($request->loan_duration * 365);
                        }
                        if ($request->loan_duration_type == 'month') {
                            $period = ceil($request->loan_duration * 30.42);
                        }
                        if ($request->loan_duration_type == 'week') {
                            $period = ceil($request->loan_duration * 7.02);
                        }
                        if ($request->loan_duration_type == 'day') {
                            $period = ceil($request->loan_duration);
                        }
                    }
                    ?>
<?php 
 $fees_distribute = 0;
        $fees_first_payment = 0;
        $fees_payment = 0;

    foreach($request->loanfees as $key => $loanfee){
        if ($request->loan_fees_schedule[$key] == 'distribute_fees_evenly') {
          $fees_payment = $fees_payment + ($request->loan_fees_amount[$key] * $request->principal / 100);
        }
        if ($request->loan_fees_schedule[$key] == 'charge_fees_on_first_payment') {
            $fees_payment = $fees_payment + ($request->loan_fees_amount[$key] * $request->principal / 100);
        }
        if ($request->loan_fees_schedule[$key] == 'charge_fees_on_last_payment') {
            $fees_payment = $fees_payment + ($request->loan_fees_amount[$key] * $request->principal / 100);
        }

    }
?>
<div class="table-responsive no-padding">
    <table id="" class="table table-bordered table-condensed table-hover">
        <thead>
        <tr style="background-color: #D1F9FF">
            <th>Released</th>
            <th>Maturity</th>
            <th>Repayment Method</th>
            <th>Principal</th>
            <th>Interest(%)</th>
            <th>Interest</th>
            <th>Fee</th>
            <th>Due</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{date("M d, Y",strtotime($request->release_date))}}</td>
            <td id="due_date"></td>
            <td id="repayment">
                @if($request->repayment_cycle=='daily')
                daily
                @endif
                @if($request->repayment_cycle=='weekly')
                weekly
                @endif
                @if($request->repayment_cycle=='monthly')
                monthly
                @endif
                @if($request->repayment_cycle=='bi_monthly')
                bi_monthly
                @endif
                @if($request->repayment_cycle=='quarterly')
                quarterly
                @endif
                @if($request->repayment_cycle=='semi_annual')
                semi_annually
                @endif
                @if($request->repayment_cycle=='annually')
                    annual
                @endif
            </td>
            <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($request->principal)}}</td>
            <td>  {{$request->interest_rate."%"}} / {{$request->interest_period}}</td>
            <td id="interest"></td>
            <td id="fees">{{number_format($fees_payment,2)}}</td>
            <td id="due"></td>
        </tr>
        </tbody>
    </table>
</div>

<h4>Loan Breakdown</h4><hr>
<form action="{{route('calculate-print')}}" method="post" target="_blank">
    @csrf
<div class="table-responsive no-padding">

    <table class="table  table-bordered table-condensed table-hover">
        <thead>
        <tr style="background-color: #080808b6; color: #fff">
            <th>#</th>
            <th>Due Date</th>
            <th>Principal Amount</th>
            <th></th>
            <th>Interest Amount</th>
            <th></th>
            <th>Fee Amount</th>
            <th></th>
            <th>Penalty Amount</th>
            <th></th>
            <th>Due Amount</th>
            <th>Outstanding Balance</th>
            <th>Description</th>
        </tr>
        </thead>
        <tbody>
        <input type="hidden" name="count" class="form-control"
               value="{{$period}}">
        <?php
        $count = 0;
        $principal_balance = $request->principal;
        $balance = $principal_balance;
        $total_principal = 0;
        $total_interest = 0;
        $total_due = 0;
        $total_fees = 0;
        $next_payment = $request->first_payment_date;
        $due = ($interest_rate * $principal_balance * pow((1 + $interest_rate),
                                $period)) / (pow((1 + $interest_rate),
                                $period) - 1);
        for ($i = 1; $i <= $period; $i++) {
        if ($request->interest_method == 'declining_balance_equal_installments') {
            if ($request->decimal_places == 'round_off_to_two_decimal') {
                //determine if we have grace period for interest

                $interest = round(($interest_rate * $balance), 2);
                $principal = round(($due - $interest), 2);
                if ($request->grace_on_interest_charged >= $i) {
                    $interest = 0;
                } else {
                    $interest = round($interest, 2);
                }
                $due = round($due, 2);
                //determine next balance
                $balance = round(($balance - $principal), 2);
                $principal_balance = round($balance, 2);
            } else {
                //determine if we have grace period for interest

                $interest = round(($interest_rate * $balance));
                $principal = round(($due - $interest));
                if ($request->grace_on_interest_charged >= $i) {
                    $interest = 0;
                } else {
                    $interest = round($interest);
                }
                $due = round($due);
                //determine next balance
                $balance = round(($balance - $principal));
                $principal_balance = round($balance);
            }


        }
        //reducing balance equal principle
        if ($request->interest_method == 'declining_balance_equal_principal') {
            $principal = $request->principal / $period;
            if ($request->decimal_places == 'round_off_to_two_decimal') {

                $interest = round(($interest_rate * $balance), 2);
                $principal = round($principal, 2);
                if ($request->grace_on_interest_charged >= $i) {
                    $interest = 0;
                } else {
                    $interest = round($interest, 2);
                }
                $due = round(($principal + $interest), 2);
                //determine next balance
                $balance = round(($balance - $principal), 2);
                $principal_balance = round($balance, 2);
            } else {

                $principal = round(($principal));

                $interest = round(($interest_rate * $balance));
                if ($request->grace_on_interest_charged >= $i) {
                    $interest = 0;
                } else {
                    $interest = round($interest);
                }
                $due = round($principal + $interest);
                //determine next balance
                $balance = round(($balance - $principal));
                $principal_balance = round($balance);
            }

        }
        //flat  method
        if ($request->interest_method == 'flat_rate') {
            $principal = $request->principal / $period;
            if ($request->decimal_places == 'round_off_to_two_decimal') {
                $interest = round(($interest_rate * $request->principal), 2);
                $principal = round(($principal), 2);
                if ($request->grace_on_interest_charged >= $i) {
                    $interest = 0;
                } else {
                    $interest = round($interest, 2);
                }
                $principal = round(($principal), 2);
                $due = round(($principal + $interest), 2);
                //determine next balance
                $balance = round(($balance - $principal), 2);
                $principal_balance = round($balance, 2);
            } else {
                $interest = round(($interest_rate * $request->principal));
                if ($request->grace_on_interest_charged >= $i) {
                    $interest = 0;
                } else {
                    $interest = round($interest);
                }
                $principal = round($principal);
                $due = round($principal + $interest);
                //determine next balance
                $balance = round(($balance - $principal));
                $principal_balance = round($balance);
            }
        }
        //interest only method
        if ($request->interest_method == 'interest_only') {
            if ($i == $period) {
                $principal = $request->principal;
            } else {
                $principal = 0;
            }
            if ($request->decimal_places == 'round_off_to_two_decimal') {
                $interest = round(($interest_rate * $request->principal), 2);
                if ($request->grace_on_interest_charged >= $i) {
                    $interest = 0;
                } else {
                    $interest = round($interest, 2);
                }
                $principal = round(($principal), 2);
                $due = round(($principal + $interest), 2);
                //determine next balance
                $balance = round(($balance - $principal), 2);
                $principal_balance = round($balance, 2);
            } else {
                $interest = round(($interest_rate * $request->principal));
                if ($request->grace_on_interest_charged >= $i) {
                    $interest = 0;
                } else {
                    $interest = round($interest);
                }
                $principal = round($principal);
                $due = round($principal + $interest);
                //determine next balance
                $balance = round(($balance - $principal));
                $principal_balance = round($balance);
            }
        }
        $due_date = $next_payment;
        ?>
        <tr>
            <td>
                {{$count+1}}<input type="hidden" name="collection_idArray[{{$count}}]" class="form-control"
                                   id="inputCollectionId" value="{{$count}}">
            </td>
            <?php $getduedate = $due_date;?>
            <td>
                <input type="date" name="due_date[{{$count}}]" id="due_date{{$count}}" onchange="document.getElementById('due_date').textContent=this.lastIndexOf(this.value)"  value="{{$due_date}}">
            </td>
            <td>
                <input type="text" name="principal[{{$count}}]" id="principal{{$count}}" onkeyup="updatesum()" value="{{round($principal,2)}}">
            </td>
            <td>+</td>
            <td>
                <input type="text" name="interest[{{$count}}]" onkeyup="updatesum()" id="interest{{$count}}" value="{{round($interest,2)}}">
            </td>
            <td>+</td>
            <td>
                <?php
                    $tfee = $fees_payment / $period;
                    ?>
                 <input type="text" name="fees[{{$count}}]" onkeyup="updatesum()" id="fees{{$count}}" value="{{$tfee}}">
            </td>
            <td>+</td>
            <td>
                <input type="text" name="penalty[{{$count}}]" onkeyup="updatesum()" id="penalty{{$count}}" value="0">
            </td>
            <td>=</td>
            <td>
                <input type="text" name="due[{{$count}}]" id="due{{$count}}" readonly value="{{round(($principal+$interest+$tfee),2)}}">
            </td>
            <td>
                <input type="text" name="principal_balance[{{$count}}]" readonly id="principal_balance{{$count}}" value="{{round($principal_balance,2)}}">
            </td>
            <td>
                <input type="text" name="description[{{$count}}]" placeholder="Repayment"  id="description{{$count}}">
            </td>
        </tr>
        <?php
        //determine next due date
        if ($request->repayment_cycle == 'daily') {
            $next_payment = date_format(date_add(date_create($next_payment),
                    date_interval_create_from_date_string('1 days')),
                    'Y-m-d');
            $due_date = $next_payment;
        }
        if ($request->repayment_cycle == 'weekly') {
            $next_payment = date_format(date_add(date_create($next_payment),
                    date_interval_create_from_date_string('1 weeks')),
                    'Y-m-d');
            $due_date = $next_payment;
        }
        if ($request->repayment_cycle == 'monthly') {
            $next_payment = date_format(date_add(date_create($next_payment),
                    date_interval_create_from_date_string('1 months')),
                    'Y-m-d');
            $due_date = $next_payment;
        }
        if ($request->repayment_cycle == 'bi_monthly') {
            $next_payment = date_format(date_add(date_create($request->first_payment_date),
                    date_interval_create_from_date_string($period . ' months')),
                    'Y-m-d');
            $due_date = $next_payment;
        }
        if ($request->repayment_cycle == 'quarterly') {
            $next_payment = date_format(date_add(date_create($next_payment),
                    date_interval_create_from_date_string('2 months')),
                    'Y-m-d');
            $due_date = $next_payment;
        }
        if ($request->repayment_cycle == 'semi_annually') {
            $next_payment = date_format(date_add(date_create($next_payment),
                    date_interval_create_from_date_string('6 months')),
                    'Y-m-d');
            $due_date = $next_payment;
        }
        if ($request->repayment_cycle == 'yearly') {
            $next_payment = date_format(date_add(date_create($next_payment),
                    date_interval_create_from_date_string('1 years')),
                    'Y-m-d');
            $due_date = $next_payment;
        }
        if ($i == $period) {
            $principal_balance = round($balance);
        }
        $total_principal = $total_principal + $principal;
        $total_interest = $interest + $total_interest;
        $total_fees += $tfee;
        
        $count++;
        }
        $total_due = $total_interest + $total_principal + $total_fees;
        ?>
        <tr>
            <td>
            </td>
            <td>
                <input type="text" class="form-control" value="Total" readonly="">
            </td>
            <td>
                <input type="text" name="principalTotal" class="form-control"
                       id="principalTotal"
                       value="{{round($total_principal,2)}}"
                       readonly="">
            </td>
            <td>+</td>
            <td>
                <input type="text" name="interestTotal" class="form-control"
                       id="interestTotal"
                       value="{{round($total_interest,2)}}"
                       readonly="">
            </td>
            <td>+</td>
            <td>
                <input type="text" name="feesTotal" class="form-control"
                       id="feesTotal"
                       value="{{$total_fees}}"
                       readonly="">
            </td>
            <td>+</td>
            <td>
                <input type="text" name="penaltyTotal" class="form-control"
                       id="penaltyTotal"
                       value="0"
                       readonly="">
            </td>
            <td>=</td>
            <td>
                <input type="text" name="inputTotalDueAmountTotal" class="form-control"
                       id="inputTotalDueAmountTotal"
                       value="{{round($total_due,2)}}"
                       readonly="">
            </td>
            <td>
            </td>
        </tr>
        </tbody>
    </table>
    
        <input type="hidden" name="release_date" value="{{$request->release_date}}">
        <input type="hidden" name="maturity_date" id="matydt" value="{{\Carbon\Carbon::create($request->first_payment_date)->toFormattedDateString()}}">
        <input type="hidden" name="repayment_cycle" id="repayment_cycle" value="{{$request->repayment_cycle}}">
        <input type="hidden" name="interest_rate" id="interest_rate" value="{{$request->interest_rate}}">
        <input type="hidden" name="release_date" value="{{$request->release_date}}">
        <input type="hidden" name="interest_period" value="{{$request->interest_period}}">
        <input type="hidden" name="first_payment_date" value="{{$request->first_payment_date}}">
        <input type="hidden" name="principal_amount" value="{{$request->principal}}">
        <input type="hidden" name="total_interest" id="total_interest_field" value="">
        <input type="hidden" name="total_due" id="total_due_field" value="">

        <div class="row margin">
            <input  class="btn vd_btn vd_bg-blue vd_white" type="submit" name="pdf" value="Download as PDF">
            <input  class="btn vd_btn vd_bg-green vd_white" type="submit" name="print"  value="Print">
        </div>
     </form>
    
    
</div>


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

<script type="text/javascript">
    function updatesum() {
        var principalTotal = 0;
        var interestTotal = 0;
        var feesTotal = 0;
        var penaltyTotal = 0;
        var inputTotalDueAmountTotal = 0;

        for (var i = 0; i < '{{$period}}'; i++) {
            var principal = document.getElementById("principal" + i).value;
            var interest = document.getElementById("interest" + i).value;
            var fees = document.getElementById("fees" + i).value;
            var penalty = document.getElementById("penalty" + i).value;

            if (principal == "")
                principal = 0;
            if (interest == "")
                interest = 0;
            if (fees == "")
                fees = 0;
            if (penalty == "")
                penalty = 0;

            var totaldue = parseFloat(principal) + parseFloat(interest) + parseFloat(fees) + parseFloat(penalty);
            document.getElementById("due" + i).value = Math.floor(totaldue * 100) / 100;

            principalTotal = parseFloat(principalTotal) + parseFloat(principal) * 100;
            interestTotal = parseFloat(interestTotal) + parseFloat(interest) * 100;
            feesTotal = parseFloat(feesTotal) + parseFloat(fees) * 100;
            penaltyTotal = parseFloat(penaltyTotal) + parseFloat(penalty) * 100;

            inputTotalDueAmountTotal = parseFloat(inputTotalDueAmountTotal) + parseFloat(totaldue) * 100;
        }
        document.getElementById("principalTotal").value = Math.floor(principalTotal * 100) / 10000;
        document.getElementById("interestTotal").value = Math.floor(interestTotal * 100) / 10000;
        document.getElementById("feesTotal").value = Math.floor(feesTotal * 100) / 10000;
        document.getElementById("penaltyTotal").value = Math.floor(penaltyTotal * 100) / 10000;
        document.getElementById("inputTotalDueAmountTotal").value = Math.floor(inputTotalDueAmountTotal * 100) / 10000;

        var total_principal_amount = 0;
        var pending_balance = 0;
        var principalTotal = document.getElementById("principalTotal").value;
        for (var i = 0; i < '{{$period}}'; i++) {
            var principal = document.getElementById("principal" + i).value;
            total_principal_amount = (parseFloat(total_principal_amount) + parseFloat(principal));
            pending_balance = parseFloat(principalTotal) - parseFloat(total_principal_amount);
            document.getElementById("principal_balance" + i).value = Math.ceil(pending_balance * 100) / 100;
        }

    }
</script>
<script>
  $(document).ready(function(){
    $('#interest').text("{!!$getsetvalue->getsettingskey('currency_symbol')."".number_format($total_interest) !!}");
    $('#due').text("{!!$getsetvalue->getsettingskey('currency_symbol')."".number_format($total_due) !!}");
    $('#due_date').text("{!!$getduedate!!}");
    $('#total_interest_field').val("{!! $total_interest !!}");
    $('#total_due_field').val("{!! $total_due !!}");
    $('#repayment_cycle').val($('#repayment').text());
    $('#matydt').val($('#due_date').text());
  });
</script>
@endsection