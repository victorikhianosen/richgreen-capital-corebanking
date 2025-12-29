<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Loan Statement</title>
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
   
    <div>
        <?php
        $getsetvalue = new \App\Models\Setting();
       ?>
        @inject('getloan', 'App\Http\Controllers\LoanController')
    
        <h3 class="text-center"><img src="{{asset($getsetvalue->getsettingskey('company_logo'))}}" class="img-responsive" width="120" alt="logo"></h3>

                                         
        <div style="width: 1080px;height: 44px;margin-left: auto;margin-right: auto;border-top: solid thin rgba(2, 180, 209, 0.44);border-bottom: solid thin rgba(2, 180, 209, 0.44);padding: 20px;text-transform: capitalize">
    <div style="width: 300px;margin-right: 20px;float: left">
                <b>{{$getsetvalue->getsettingskey('company_name')}} Office Address</b> <br />
              {{$getsetvalue->getsettingskey('company_address')}}
    </div>
            <div style="width: 350px;margin-right: 40px;float: left">
               <b> E-Mail: </b> <br />{{$getsetvalue->getsettingskey('company_email')}}<br><br>
            </div>
            <div style="width: 300px;float: left">
                <b>Phone No: </b> <br />{{$getsetvalue->getsettingskey('company_phone')}}<br>
            </div>
        </div>
        
         <h3 class="text-center"><b>Loan Statement</b></h3>
    
    <div style="width: 980px;height: 224px;margin-left: auto;margin-right: auto;border-top: solid thin rgba(2, 180, 209, 0.44);border-bottom: solid thin rgba(2, 180, 209, 0.44);padding: 20px;text-transform: capitalize">
               <?php $totinster = $getloan->loan_total_interest($loan->id); ?>
        @if ($lprintloanfrom)
                <div style="width: 300px;margin-right: 20px;float: left">
                       Customer Name: <b>{{$loan->customer->last_name." ".$loan->customer->first_name}}</b><br />
                    Customer Account No: <b>{{$loan->customer->acctno}} </b><br />
                    Loan Account Number: <b>{{$loan->loan_code}} </b>
                </div>
            @else    
            
            <div style="width: 300px;margin-right: 20px;float: left">
                            <b>Date: </b>{{date("Y-m-d")}}<br><br>
                        Customer Name: <b>{{$loan->customer->last_name}} {{$loan->customer->first_name}}</b><br />
                        Address: <b>{{$loan->customer->residential_address}} </b><br />
                        Phone No: <b>{{$loan->customer->phone}} </b>
                </div>

            <div style="width: 300px;margin-right: 40px;float: left">
                <b>Loan Account Number</b><span class="pull-right">{{$loan->loan_code}}</span><br><br>
                <b>Released Date</b><span class="pull-right">{{date("d M,Y",strtotime($loan->release_date))}}</span><br><br>
                <b>Mturity Date</b><span class="pull-right">{{date("d M,Y",strtotime($loan->maturity_date))}}</span><br><br>
                <b>Repayment cycle</b><span class="pull-right" style="">{{$loan->repayment_cycle}}</span><br><br>
                <b>Principal</b><span class="pull-right" style="">{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($loan->principal,2)}}</span><br><br>
                <b>Interest (%)</b><span class="pull-right" style="">{{number_format($loan->interest_rate,2)}}
                    % per {{$loan->interest_period}}</span><br><br>
            </div>
            <div style="width: 300px;float: left">
             
                <b>Interest</b><span
                        class="pull-right">{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($totinster,2)}}</span><br><br>
                <b>Fee</b><span
                        class="pull-right">{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_fees($loan->id),2)}}</span><br><br>
                <b>Penalty</b><span
                        class="pull-right">{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_penalty($loan->id),2)}}</span><br><br>
                <b>Due</b><span class="pull-right"
                                style="">{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_due_amount($loan->id),2)}}</span><br><br>
                <b>Paid</b><span class="pull-right"
                                 style="">{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_paid($loan->id),2)}}</span><br><br>
                <b>Balance</b><span class="pull-right"
                                    style="">{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_balance($loan->id),2)}}</span><br><br>
            </div>
             @endif
        </div>
        <div style="width: 980px;margin-top:30px;margin-left: auto;margin-right: auto;padding: 20px;text-transform: capitalize">
            @if (!$lprintloanfrom)
            <h3 class="text-center"><b>Repayment</b></h3>
           @endif
                <table class="table table-condensed table-bordered table-striped">
                    <thead>
                        <tr style="background-color: #F2F8FF">
                            <th>Date</th>
                            <th>Description</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <?php 
                        $i=0;
                        $totpricpla = $loan->principal + $totinster;
                    $hasDebit = \App\Models\LoanRepayment::where('type','debit')->exists();
                    $balance = $hasDebit ? 0 : $totpricpla;
                        ?>
                       @if (!$hasDebit)
                        <tr>
                            <td>{{ date('d-m-Y H:ia', strtotime($loan->created_at)) }}</td>
                            <td>Loan Disbursed</td>
                            <td>{{ number_format($totpricpla, 2) }}</td>
                            <td></td>
                            <td>{{ number_format($balance, 2) }}</td>
                        </tr>
                       @endif
                    @foreach($payments as $key)
                        <tr>
                            @if ($hasDebit)
                               <td>{{date('d-m-Y H:ia',strtotime($key->created_at))}}</td>
                               <td>
                                   {{$key->notes}}
                               </td>
                                 @if ($key->type == 'debit')
                                    <?php $balance += $key->amount;?>
                                    <td>{{number_format($key->amount,2)}}</td> 
                                    <td> </td>  
                                 @else
                                    <?php $balance -= $key->amount;?>
                                    <td> </td> 
                                    <td>{{number_format($key->amount,2)}}</td> 
                                 @endif
                              
                                @else

                                <td>{{date('d-m-Y H:ia',strtotime($key->created_at))}}</td>
                                    <td></td>

                                    @if ($key->type == 'credit')
                                    <?php $balance -= $key->amount;?>
                                        <td> </td> 
                                        <td>{{number_format($key->amount,2)}}</td>  
                                    @else
                                    <?php $balance += $totpricpla;?>
                                    <td>
                                        {{number_format($totpricpla,2)}}
                                    </td> 
                                     <td> </td>
                                    @endif
                                @endif
                            <td>{{number_format($balance,2)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
         
        </div>
    </div>
    
    <script>
        window.onload = function () {
            window.print();
        }
    </script>
</body>
</html>