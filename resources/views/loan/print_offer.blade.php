
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Print Offer Letter</title>
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('assets/dist/css/AdminLTE.min.css') }}">
    <style type="text/css" media="print">
        @page {
            size: auto;   /* auto is the initial value */
            margin: 0mm;  /* this affects the margin in the printer settings */
        }

        html {
            background-color: #FFFFFF;
            margin: 0px; /* this affects the margin on the html before sending to printer */
        }

        body {
            margin: 10mm 10mm 10mm 10mm; /* margin you want for the content */
        }
    </style>
    <style>
    thead {
        display: table-header-group;
    }

    .table {
        border-collapse: collapse !important;
    }

    .table td,
    .table th {
        background-color: #fff !important;
    }

    .table-bordered th,
    .table-bordered td {
    }

    th {
        text-align: left;
    }
    .table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
    }

    .table > thead > tr > th,
    .table > tbody > tr > th,
    .table > tfoot > tr > th,
    .table > thead > tr > td,
    .table > tbody > tr > td,
    .table > tfoot > tr > td {
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
    }

    .table > thead > tr > th {
        vertical-align: bottom;
    }

    .table > caption + thead > tr:first-child > th,
    .table > colgroup + thead > tr:first-child > th,
    .table > thead:first-child > tr:first-child > th,
    .table > caption + thead > tr:first-child > td,
    .table > colgroup + thead > tr:first-child > td,
    .table > thead:first-child > tr:first-child > td {
        border-top: 0;
    }

    .table > tbody + tbody {
    }

    .table .table {
        background-color: #fff;
    }

    .table-condensed > thead > tr > th,
    .table-condensed > tbody > tr > th,
    .table-condensed > tfoot > tr > th,
    .table-condensed > thead > tr > td,
    .table-condensed > tbody > tr > td,
    .table-condensed > tfoot > tr > td {
        padding: 5px;
    }

    .table-bordered {
        border: 1px solid #ddd;
    }

    .table-bordered > thead > tr > th,
    .table-bordered > tbody > tr > th,
    .table-bordered > tfoot > tr > th,
    .table-bordered > thead > tr > td,
    .table-bordered > tbody > tr > td,
    .table-bordered > tfoot > tr > td {
        border: 1px solid #ddd;
    }

    .table-bordered > thead > tr > th,
    .table-bordered > thead > tr > td {
        border-bottom-width: 2px;
    }

    .table-striped > tbody > tr:nth-of-type(odd) {
        background-color: #f9f9f9;
    }

    .table-hover > tbody > tr:hover {
        background-color: #f5f5f5;
    }

    table col[class*="col-"] {
        position: static;
        display: table-column;
        float: none;
    }

    table td[class*="col-"],
    table th[class*="col-"] {
        position: static;
        display: table-cell;
        float: none;
    }

    .table > thead > tr > td.active,
    .table > tbody > tr > td.active,
    .table > tfoot > tr > td.active,
    .table > thead > tr > th.active,
    .table > tbody > tr > th.active,
    .table > tfoot > tr > th.active,
    .table > thead > tr.active > td,
    .table > tbody > tr.active > td,
    .table > tfoot > tr.active > td,
    .table > thead > tr.active > th,
    .table > tbody > tr.active > th,
    .table > tfoot > tr.active > th {
        background-color: #f5f5f5;
    }

    .table-hover > tbody > tr > td.active:hover,
    .table-hover > tbody > tr > th.active:hover,
    .table-hover > tbody > tr.active:hover > td,
    .table-hover > tbody > tr:hover > .active,
    .table-hover > tbody > tr.active:hover > th {
        background-color: #e8e8e8;
    }

    .table > thead > tr > td.success,
    .table > tbody > tr > td.success,
    .table > tfoot > tr > td.success,
    .table > thead > tr > th.success,
    .table > tbody > tr > th.success,
    .table > tfoot > tr > th.success,
    .table > thead > tr.success > td,
    .table > tbody > tr.success > td,
    .table > tfoot > tr.success > td,
    .table > thead > tr.success > th,
    .table > tbody > tr.success > th,
    .table > tfoot > tr.success > th {
        background-color: #dff0d8;
    }

    .table-hover > tbody > tr > td.success:hover,
    .table-hover > tbody > tr > th.success:hover,
    .table-hover > tbody > tr.success:hover > td,
    .table-hover > tbody > tr:hover > .success,
    .table-hover > tbody > tr.success:hover > th {
        background-color: #d0e9c6;
    }

    .table > thead > tr > td.info,
    .table > tbody > tr > td.info,
    .table > tfoot > tr > td.info,
    .table > thead > tr > th.info,
    .table > tbody > tr > th.info,
    .table > tfoot > tr > th.info,
    .table > thead > tr.info > td,
    .table > tbody > tr.info > td,
    .table > tfoot > tr.info > td,
    .table > thead > tr.info > th,
    .table > tbody > tr.info > th,
    .table > tfoot > tr.info > th {
        background-color: #d9edf7;
    }

    .table-hover > tbody > tr > td.info:hover,
    .table-hover > tbody > tr > th.info:hover,
    .table-hover > tbody > tr.info:hover > td,
    .table-hover > tbody > tr:hover > .info,
    .table-hover > tbody > tr.info:hover > th {
        background-color: #c4e3f3;
    }

    .table > thead > tr > td.warning,
    .table > tbody > tr > td.warning,
    .table > tfoot > tr > td.warning,
    .table > thead > tr > th.warning,
    .table > tbody > tr > th.warning,
    .table > tfoot > tr > th.warning,
    .table > thead > tr.warning > td,
    .table > tbody > tr.warning > td,
    .table > tfoot > tr.warning > td,
    .table > thead > tr.warning > th,
    .table > tbody > tr.warning > th,
    .table > tfoot > tr.warning > th {
        background-color: #fcf8e3;
    }

    .table-hover > tbody > tr > td.warning:hover,
    .table-hover > tbody > tr > th.warning:hover,
    .table-hover > tbody > tr.warning:hover > td,
    .table-hover > tbody > tr:hover > .warning,
    .table-hover > tbody > tr.warning:hover > th {
        background-color: #faf2cc;
    }

    .table > thead > tr > td.danger,
    .table > tbody > tr > td.danger,
    .table > tfoot > tr > td.danger,
    .table > thead > tr > th.danger,
    .table > tbody > tr > th.danger,
    .table > tfoot > tr > th.danger,
    .table > thead > tr.danger > td,
    .table > tbody > tr.danger > td,
    .table > tfoot > tr.danger > td,
    .table > thead > tr.danger > th,
    .table > tbody > tr.danger > th,
    .table > tfoot > tr.danger > th {
        background-color: #f2dede;
    }

    .table-hover > tbody > tr > td.danger:hover,
    .table-hover > tbody > tr > th.danger:hover,
    .table-hover > tbody > tr.danger:hover > td,
    .table-hover > tbody > tr:hover > .danger,
    .table-hover > tbody > tr.danger:hover > th {
        background-color: #ebcccc;
    }

    .table-responsive {
        min-height: .01%;
        overflow-x: auto;
    }

    .row {
        margin-right: -15px;
        margin-left: -15px;
        clear: both;
    }

    .col-md-6 {
        width: 50%;
        position: relative;
        min-height: 1px;
        padding-right: 15px;
        padding-left: 15px;
    }

    .well {
        min-height: 20px;
        padding: 19px;
        margin-bottom: 20px;
        background-color: #f5f5f5;
        border: 1px solid #e3e3e3;
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .05);
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, .05);
    }

    tbody:before, tbody:after {
        display: none;
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
</style>
</head>
<body>
    <?php
 $getsetvalue = new \App\Models\Setting();
?>
@inject('getloan', 'App\Http\Controllers\LoanController')
<div>
    <div class="text-center">
         <p><img src="{{asset($getsetvalue->getsettingskey('company_logo'))}}" width="120" alt="logo"> </p>
        <p>{{$getsetvalue->getsettingskey('company_name')}}</p>
        <p>{{$getsetvalue->getsettingskey('company_email')}}, {{$getsetvalue->getsettingskey('company_phone')}}</p>
        <p>{{$getsetvalue->getsettingskey('company_address')}}</p>
    </div>
<br>
 <div class="row invoice-info">
            <div class="col-sm-6">
                <address style="margin-left: 15px">
                     <strong>Generated Date: </strong> &nbsp; &nbsp; &nbsp;  &nbsp; {{date("Y-m-d")}}<br><br>
        <strong> Customer Name: </strong>&nbsp;&nbsp;&nbsp; &nbsp;{{$loan->customer->first_name}} {{$loan->customer->last_name}}<br />
          <strong>Address :</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; {{$loan->customer->residential_address}} <br />
          <strong> Phone No: </strong> &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{{$loan->customer->phone}}<br>
            <b>Loan Code:</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; {{$loan->loan_code}}<br>
                </address>
            </div>
        </div>
        Dear Sir/Ma,
        <br>
    <h4 class="text-center">
 <b>Offer Letter For {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($loan->principal,2)}} Term Loan Facility </span> </b>
    </h4>
 <div class="row invoice-info">
            <div class="col-sm-9 text-center">
               <p>Further to your application for a Credit Facility, {{$getsetvalue->getsettingskey('company_name')}} is pleased to offer <b>{{$loan->customer->first_name}} {{$loan->customer->last_name}} </b> a <b>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($loan->principal,2)}} </b> term loan facility under the following terms and conditions:</p>
            </div>
        </div>
    <div style="width: 950px;height: 164px;margin-left: auto;margin-right: auto;border-top: solid thin rgba(2, 180, 209, 0.44);border-bottom: solid thin rgba(2, 180, 209, 0.44);padding: 20px;text-transform: capitalize">
    
        <div style="width: 300px;margin-right: 20px;float: left">
         
          <strong>Loan Purpose :</strong> {{$loan->purpose}} <br /><br />
         <?php $totmgmtfeeval = 0;?>
          @foreach ($loanfees as $fees)
          <?php
           $totmgmtfee = DB::table('loan_fee_metas')->select('value')->where('parent_id',$loan->id)
                                                    ->where('loan_fee_id',$fees->id)
                                                    ->whereDate('created_at',date("Y-m-d",strtotime($loan->created_at)))->sum('value');
          ?>
          @foreach ($fees->loanfeemetas as $item)
             <strong>{{$fees->name}}: </strong> {{$item->value}}% Upfront <br><br> 
             <?php $totmgmtfeeval += $item->value;
             break;
             ?>
          @endforeach
          @endforeach
        <strong>Total Fee: </strong> {{$totmgmtfeeval}}% Upfront <br><br>
        <strong> Transfer Charge: </strong>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($charges->amount)}}<br />
       
         
        </div>
        <div style="width: 250px;margin-right: 30px;float: left">
           <b>Loan Duration</b><span class="pull-right" style="">{{$loan->loan_duration}} {{$loan->loan_duration_type}}s</span><br><br>

<b>Disbursed Date</b><span class="pull-right">{{date('d M, Y',strtotime($loan->release_date))}}</span><br><br>
<b>Maturity Date</b><span class="pull-right">{{date('d M, Y',strtotime($loan->maturity_date))}}</span><br><br>
<b>Repayment Cycle</b><span class="pull-right" style="">{{$loan->repayment_cycle}}</span><br><br>
        </div>
     <div style="width: 300px;float: left">
             <b>Loan Principal</b><span class="pull-right" style="">{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($loan->principal,2)}}</span><br><br>
            <b>Interest Rate</b><span class="pull-right" style="">{{number_format($loan->interest_rate,2)}}% per {{$loan->interest_period}}</span><br><br>
 <b>Interest </b><span class="pull-right">{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_interest($loan->id),2)}}</span><br><br>
  {{-- <b>{{trans_choice('general.fee',2)}}</b><span class="pull-right">N{{number_format($getloan->loan_total_fees($loan->id),2)}}</span><br><br>
<b>{{trans_choice('general.penalty',1)}}</b><span class="pull-right">N{{number_format($getloan->loan_total_penalty($loan->id),2)}}</span><br><br>  
<b>{{trans_choice('general.paid',1)}}</b><span class="pull-right" style="">N{{number_format($getloan->loan_total_paid($loan->id),2)}}</span><br><br>
    <b>{{trans_choice('general.balance',1)}}</b><span class="pull-right"
                                style="">N{{number_format(\App\Helpers\GeneralHelper::loan_total_balance($loan->id),2)}}</span><br><br> --}}
<b>Total Due</b><span class="pull-right"  style="">{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_due_amount($loan->id),2)}}</span><br><br>

        </div>
    </div>
  
    <div style="width: 1000px;margin-top:5px;margin-left: auto;margin-right: auto;padding: 20px;text-transform: capitalize; font-size: 20px">
    <p>  <strong>Repayment plan</strong> </p>
        <table class="table table-condensed table-bordered table-striped">
            <tbody>
            <tr style="background-color: #F2F8FF">
                <th>
                    <b>S/N</b>
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
                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(\App\Models\LoanSchedule::where('loan_id',
                    $loan->id)->sum('principal'),2)}}
                </td>
            </tr>
            <?php
            $count = 0;
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
                    {{$count+1}}
                </td>
                <td>
                    {{$schedule->due_date}}
                </td>
                <td>
                    {{$schedule->description}}
                </td>
                <td style="text-align:right">
                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($schedule->principal,2)}}
                </td>
                <td style="text-align:right">
                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($schedule->interest,2)}}
                </td>
                <td style="text-align:right">
                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($schedule->fees,2)}}
                </td>
                <td style="text-align:right">
                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($schedule->penalty,2)}}
                </td>
                <td style="text-align:right; font-weight:bold">
                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(($schedule->principal+$schedule->interest+$schedule->fees+$schedule->penalty),2)}}
                </td>
                <td style="text-align:right;">
                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($total_due,2)}}
                </td>
                <td style="text-align:right;">
                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($principal_balance,2)}}
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
                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_principal($loan->id),2)}}
                </td>
                <td style="text-align:right;">
                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_interest($loan->id),2)}}
                </td>
                <td style="text-align:right;">
                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_fees($loan->id),2)}}
                </td>
                <td style="text-align:right;">
                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_penalty($loan->id),2)}}
                </td>
                <td style="text-align:right; font-weight:bold">
                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_due_amount($loan->id),2)}}
                </td>
                <td>.</td>
                <td>.</td>
            </tr>
            </tbody>
        </table>
    </div>
     
 <div style="width: 1000px;margin-top:5px;margin-left: auto;margin-right: auto;padding: 20px; font-size: 18px">
       <p>  <b>Other Conditions and Warranties </b></p>    

<li align="justify">1.  Timely repayment of the facility as specified in this offer letter will enable you get increase in future loans from the Company.</li> <br>
<li align="justify">2.  Note that this facility is repayable on demand.</li><br>
<li align="justify">3.  In the event of failure by the borrower to pay any due installment on this facility, interest shall be calculated on the unpaid installments(s) at the company’s default rate currently 3% flat per month and every recovery cost incurred by the company would be borne by the customers.</li><br><br><br> <br>
<li align="justify">4.  A non-repayment of any due installment amounts to a default on the entire facility agreement and such default entails the company to call in the facility and take steps to realize the collaterals, call in the guarantees, Repossess assets  purchased /leased with funds and take such further steps as it may think fit to recover its funds. </li><br>
<li align="justify">5.  The company shall be at liberty to review the rates applicable to the facility in line with prevailing money market condition from time to time and such review shall be deemed acceptable to the borrower where the facility is not fully repaid immediately. </li><br>
<li align="justify">6.  All legal, statutory, regulatory and other expenses that may arise in the execution of this facility or in enforcing the terms and conditions in respect of same shall be borne by the account of the borrower. </li><br>
<li align="justify">7.  No failure or delay by the company in executing any remedy, power or right above shall operate as a waiver or impairment thereof nor shall it affect or impair any such remedies powers or right of any such subsequent default.</li><br>
<li align="justify">8.  The company reserves the right to alter, amend and vary the terms on which this offer is made without recourse to you.</li><br>
<li align="justify">9.  If the above terms and conditions are acceptable to you, kindly endorse the two copies of this letter across a N50.00 stamp as legal binding and return the acknowledgement copy to the company.</li><br>
<li align="justify">10. We are pleased to have been given the opportunity to be of service to you, we look forward to having a mutually beneficial relationship in the future </li><br> 
                     
Yours Faithfully, <br>
For: {{ucwords($getsetvalue->getsettingskey('company_name'))}}<br><br><br><br>



 <img src="{{asset('img/sign.jpeg')}}" width="80" height="80"> <br>           
Authorised Signatory  <br><br>                  

OFFER OF ACCEPTANCE<br><br><br>

Offer accepted for and on behalf of the within named “Customer”, <strong> {{$loan->customer->first_name}} {{$loan->customer->last_name}}</strong> <br><br>


Customer’s Name: ____________________________________________________________ <br><br>

Designation: ________________________________________________________<br><br>


Signature: _________________________________ Date: ____________________________

    </div>

</div>

<script>
    window.onload = function () {
        window.print();
    }
</script>
</body>
</html>
