<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Client Statement</title>
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
</head>
<body>
    <?php
        $getsetvalue = new \App\Models\Setting();
       ?>
        @inject('getloan', 'App\Http\Controllers\LoanController')
    <div>
        <h3 class="text-center"><img src="{{asset($getsetvalue->getsettingskey('company_logo'))}}" class="img-responsive" width="120" alt="logo"></h3>
        <h3 class="text-center"><b>Loan Statement</b></h3>
    
        <div style="width: 100%;margin:0px auto;font-size:10px;border-top: solid thin #2cc3dd;border-bottom: solid thin #2cc3dd;padding-top: 40px;text-transform: capitalize">
         
            <?php $totinster = $getloan->loan_total_interest($loans->id);?>

            <table style="margin-top: 20px width:100%">
               <tbody>
                    @if ($lprintloanfrom)
                    <tr>
                        <td style="width: 30%;margin-right: 20px;float: left">
                        Customer Name: <b>{{$loans->customer->last_name." ".$loans->customer->first_name}}</b><br />
                                Customer Account No: <b>{{$loans->customer->acctno}} </b><br />
                                Loan Account Number: <b>{{$loans->loan_code}} </b>
                        </td>
                    </tr>
                 @else

                <tr>
                    <td style="width: 35%;margin-right: 25px;" align="left">
                        <b>Date: </b>{{date("Y-m-d")}}<br><br>
                         Customer Name:<b>{{$custm->first_name}} {{$custm->last_name}}</b><br />
                     Address <b>{{$custm->residential_address}} </b><br />
               Phone No: <b>{{$custm->phone}} </b>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%;margin-right: 25px;margin-top:20" align="left">
                        <table style="width: 100%">
                            <tr>
                                <td><b>Loan Code:</b></td>
                                <td>{{$loans->loan_code}}</td>
                            </tr>
                            <tr>
                                <td><b>Released Date:</b></td>
                                <td>{{$loans->release_date}}</td>
                            </tr>
                            <tr>
                                <td><b>Maturity Date:</b></td>
                                <td>{{$loans->maturity_date}}</td>
                            </tr>
                            <tr>
                                <td><b>Repayment Cycle:</b></td>
                                <td>{{$loans->repayment_cycle}}</td>
                            </tr>
                            <tr>
                                <td><b>Principal:</b></td>
                                <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($loans->principal,2)}}</td>
                            </tr>
                            <tr>
                                <td><b>Interest (%):</b></td>
                                <td>{{number_format($loans->interest_rate,2)}}% per {{$loans->interest_period}}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%;margin-right: 20px;" align="left">
                        <table style="width: 100%">
                            <tr>
                                
                                <td><b>Interest: </b></td>
                                <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($totinster,2)}}</td>
                            </tr>
                            <tr>
                                <td><b>Fees:</b></td>
                                <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_fees($loans->id),2)}}</td>
                            </tr>
                            <tr>
                                <td><b>Penalty:</b></td>
                                <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_penalty($loans->id),2)}}</td>
                            </tr>
                            <tr>
                                <td><b>Due:</b></td>
                                <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_due_amount($loans->id),2)}}</td>
                            </tr>
                            <tr>
                                <td><b>Paid:</b></td>
                                <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_paid($loans->id),2)}}</td>
                            </tr>
                            <tr>
                                <td><b>Balance:</b></td>
                                <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_balance($loans->id),2)}}</td>
                            </tr>
                        </table>
    
                    </td>
                </tr>
               </tbody>
               @endif
            </table>
        </div>
        <div style="margin-top:30px;margin-left: auto;margin-right: auto;text-transform: capitalize;font-size: 8px;">
           @if (!$lprintloanfrom)
            <h3 class="text-center"><b>Repayments</b></h3>
            @endif
            <table class="table table-condensed table-bordered table-striped">
                <tbody>
                <tr style="background-color: #F2F8FF">
                    <th>Date</th>
                    <th>Description</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Balance</th>
                </tr>
                <tbody>
                    
                    <?php 
                    $i=0;
                    $totpricpla = $loans->principal + $totinster;
                    $hasDebit = \App\Models\LoanRepayment::where('type','debit')->exists();
                    $balance = $hasDebit ? 0 : $totpricpla;
                    ?>
                   @if (!$hasDebit)
                    <tr>
                        <td>{{ date('d-m-Y H:ia', strtotime($loans->created_at)) }}</td>
                        <td>Loan Disbursed</td>
                        <td>{{ number_format( $totpricpla, 2) }}</td>
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
    
</body>
</html>