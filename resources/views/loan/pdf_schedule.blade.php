<style>

    .table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
        display: table;
    }

    .text-left {
        text-align: left;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .text-justify {
        text-align: justify;
    }

    .pull-right {
        float: right !important;
    }
    .page-break {
    page-break-after: always;
}
</style>

<?php
 $getsetvalue = new \App\Models\Setting();
?>
@inject('getloan', 'App\Http\Controllers\LoanController')
<div>
    <h3 class="text-center"><img src="{{asset($getsetvalue->getsettingskey('company_logo'))}}" class="img-responsive" width="120" alt="logo"></h3>

    <h3 class="text-center"><b>{{$getsetvalue->getsettingskey('company_name')}}</b>
    </h3>

    <h3 class="text-center"><b>Loan Repayment Schedule</b></h3>

    <div style="width: 100%;margin-left: auto;font-size:10px;margin-right: auto;border-top: solid thin #2cc3dd;border-bottom: solid thin #2cc3dd;padding-top: 40px;text-transform: capitalize">
        <table style="margin-top: 20px">
            <tr>
                <td style="width: 30%;margin-right: 20px;float: left">
                    <b>Date:</b>{{date("Y-m-d")}}<br><br>
 					Customer Name:<b>{{$loan->customer->first_name}} {{$loan->customer->last_name}}</b><br />
                     Address <b>{{$loan->customer->residential_address}} </b><br />
                     Phone No: <b>{{$loan->customer->phone}} </b>
                </td>
                <td style="width: 30%;margin-right: 20px;float: left">
                    <table width="100%">
                        <tr>
                            <td><b>Loan</b></td>
                            <td>{{$loan->loan_code}}</td>
                        </tr>
                        <tr>
                            <td><b>Released Date</b></td>
                            <td>{{date("d M, Y",strtotime($loan->release_date))}}</td>
                        </tr>
                        <tr>
                            <td><b>Maturity Date</b></td>
                            <td>{{date("d M, Y",strtotime($loan->maturity_date))}}</td>
                        </tr>
                        <tr>
                            <td><b>Repayment Cycle</b></td>
                            <td>{{$loan->repayment_cycle}}</td>
                        </tr>
                        <tr>
                            <td><b>Principal</b></td>
                            <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($loan->principal,2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Interest (%)</b></td>
                            <td>{{number_format($loan->interest_rate,2)}}
                                % / {{$loan->interest_period}}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 30%;margin-right: 20px;float: left">
                    <table>
                        <tr>
                            <td><b>Interest</b></td>
                            <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_interest($loan->id),2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Fee</b></td>
                            <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_fees($loan->id),2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Penalty</b></td>
                            <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_penalty($loan->id),2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Due</b></td>
                            <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_due_amount($loan->id),2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Paid</b></td>
                            <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_paid($loan->id),2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Balance</b></td>
                            <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_balance($loan->id),2)}}</td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>
    </div>
    <div style="margin-top:30px;margin-left: auto;margin-right: auto;text-transform: capitalize;font-size: 8px;">
        <table border="1" class="table ">
            <thead>
            <tr>
                <th>
                    <b>#</b>
                </th>
                <th>
                    <b>Date</b>
                </th>
                <th>
                    <b>Description</b>
                </th>
                <th style="text-align:right;">
                    <b>Principal</b>
                </th>
                <th style="text-align:right;">
                    <b>Interest</b>
                </th>
                <th style="text-align:right;">
                    <b>Fee</b>
                </th>
                <th style="text-align:right;">
                    <b>Penalty</b>
                </th>
                <th style="text-align:right;">
                    <b>Due</b>
                </th>
                <th style="text-align:right;">
                    <b>Total Due</b>
                </th>
                <th style="text-align:right;">
                    <b>Principal Balance Owed</b>
                </th>
            </tr>
            </thead>
            <tbody>

            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align:right;">
                    {{$getsetvalue->getsettingskey('company_currency')." ".number_format(\App\Models\LoanSchedule::where('loan_id',
                    $loan->id)->sum('principal'),2)}}
                </td>
            </tr>
            <?php
            $count = 1;
            $total_due = 0;
            $principal_balance = \App\Models\LoanSchedule::where('loan_id',
                    $loan->id)->sum('principal');
            foreach ($schedules as $schedule) {
            $principal_balance = $principal_balance - $schedule->principal;
            if ($count == 1) {
                $total_due = ($schedule->principal + $schedule->interest + $schedule->fees + $schedule->penalty);

            } else {
                $total_due = $total_due + ($schedule->principal + $schedule->interest + $schedule->fees + $schedule->penalty);
            }
            ?>
            <tr>
                <td>
                    {{$count}}
                </td>
                <td>
                    {{$schedule->due_date}}
                </td>
                <td>
                    {{$schedule->description}}
                </td>
                <td style="text-align:right">
                    {{$getsetvalue->getsettingskey('company_currency')." ".number_format($schedule->principal,2)}}
                </td>
                <td style="text-align:right">
                    {{$getsetvalue->getsettingskey('company_currency')." ".number_format($schedule->interest,2)}}
                </td>
                <td style="text-align:right">
                    {{$getsetvalue->getsettingskey('company_currency')." ".number_format($schedule->fees,2)}}
                </td>
                <td style="text-align:right">
                    {{$getsetvalue->getsettingskey('company_currency')." ".number_format($schedule->penalty,2)}}
                </td>
                <td style="text-align:right; font-weight:bold">
                    {{$getsetvalue->getsettingskey('company_currency')." ".number_format(($schedule->principal+$schedule->interest+$schedule->fees+$schedule->penalty),2)}}
                </td>
                <td style="text-align:right;">
                    {{$getsetvalue->getsettingskey('company_currency')." ".number_format($total_due,2)}}
                </td>
                <td style="text-align:right;">
                    {{$getsetvalue->getsettingskey('company_currency')." ".number_format($principal_balance,2)}}
                </td>
            </tr>
            <?php
            $count++;
            }
            ?>
            <tr>
                <td></td>
                <td></td>
                <td style="font-weight:bold">Total Due</td>
                <td style="text-align:right;">
                    {{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_principal($loan->id),2)}}
                </td>
                <td style="text-align:right;">
                    {{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_interest($loan->id),2)}}
                </td>
                <td style="text-align:right;">
                    {{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_fees($loan->id),2)}}
                </td>
                <td style="text-align:right;">
                    {{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_penalty($loan->id),2)}}
                </td>
                <td style="text-align:right; font-weight:bold">
                    {{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_due_amount($loan->id),2)}}
                </td>
                <td></td>
                <td></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
