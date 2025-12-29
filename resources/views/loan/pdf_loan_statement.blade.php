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


<div>
    @inject('getloan', 'App\Http\Controllers\LoanController')

    <h3 class="text-center"><b>{{\App\Models\Setting::where('setting_key','company_name')->first()->setting_value}}</b>
    </h3>

    <h3 class="text-center"><b>{{trans_choice('general.loan',1)}} {{trans_choice('general.statement',1)}}</b></h3>

    <div style="width: 100%;margin-left: auto;font-size:10px;margin-right: auto;border-top: solid thin #2cc3dd;border-bottom: solid thin #2cc3dd;padding-top: 40px;text-transform: capitalize">
         <?php $totinster = $getloan->loan_total_interest($loan->id);?>
        <table style="margin-top: 20px">
            @if ($lprintloanfrom)
                <tr>
                    <td style="width: 30%;margin-right: 20px;float: left">
                       Customer Name: <b>{{$loan->customer->last_name." ".$loan->customer->first_name}}</b><br />
                              Customer Account No: <b>{{$loan->customer->acctno}} </b><br />
                              Loan Account Number: <b>{{$loan->loan_code}} </b>
                    </td>
                </tr>
            @else
           
            <tr>
                <td style="width: 30%;margin-right: 20px;float: left">
                    <b>{{trans_choice('general.date',1)}}:</b>{{date("Y-m-d")}}<br><br>
 					Customer Name:<b>{{$loan->customer->last_name}} {{$loan->customer->first_name}}</b><br />
                      Address: <b>{{$loan->customer->residential_address}} </b><br />
                    Phone No: <b>{{$loan->customer->phone}} </b>
                </td>
                <td style="width: 30%;margin-right: 20px;float: left">
                    <table width="100%">
                        <tr>
                            <td><b> Loan Account Number</b></td>
                            <td>{{$loan->loan_code}}</td>
                        </tr>
                        <tr>
                            <td><b>Released Date</b></td>
                            <td>{{date("d M,Y",strtotime($loan->release_date))}}</td>
                        </tr>
                        <tr>
                            <td><b>Maturity Date</b></td>
                            <td>{{date("d M,Y",strtotime($loan->maturity_date))}}</td>
                        </tr>
                        <tr>
                            <td><b>Repayment cycle</b></td>
                            <td>{{$loan->repayment_cycle}}</td>
                        </tr>
                        <tr>
                            <td><b>Principal</b></td>
                            <td>{{number_format($loan->principal,2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Interest (%)</b></td>
                            <td>{{number_format($loan->interest_rate,2)}}% per{{$loan->interest_period}}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 30%;margin-right: 20px;float: left">
                    <table>
                        <tr>
                          
                            <td><b>Interest </b></td>
                            <td>N{{number_format($totinster,2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Fee</b></td>
                            <td>N{{number_format($getloan->loan_total_fees($loan->id),2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Penalty</b></td>
                            <td>N{{number_format($getloan->loan_total_penalty($loan->id),2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Due</b></td>
                            <td>N{{number_format($getloan->loan_total_due_amount($loan->id),2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Paid</b></td>
                            <td>N{{number_format($getloan->loan_total_paid($loan->id),2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Balance</b></td>
                            <td>N{{number_format($getloan->loan_total_balance($loan->id),2)}}</td>
                        </tr>
                    </table>

                </td>
            </tr>
             @endif
        </table>
    </div>
    <div style="margin-top:30px;margin-left: auto;margin-right: auto;text-transform: capitalize;font-size: 8px;">
          @if (!$lprintloanfrom)
        <h3 class="text-center"><b>{{trans_choice('general.repayment',2)}}</b></h3>
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
