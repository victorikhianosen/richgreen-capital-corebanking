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
</style>

<?php 
$getsetvalue = new \App\Models\Setting();
?>
@inject('getloan', 'App\Http\Controllers\RepaymentController')

<div>
    <h3 class="text-center"><img src="{{asset($getsetvalue->getsettingskey('company_logo'))}}" class="img-responsive" width="120" alt="logo"></h3>

    <h3 class="text-center"><b>{{$getsetvalue->getsettingskey('company_name')}}</b></h3>

    <h3 class="text-center"><b>Repayment Receipt</b></h3>

    <div style="width: 100%;margin-left: auto;font-size:10px;margin-right: auto;border-top: solid thin #2cc3dd;border-bottom: solid thin #2cc3dd;padding-top: 40px;text-transform: capitalize">
        <table style="margin-top: 20px">
            <tr>
                <td style="width: 30%;margin-right: 20px;float: left">
                    <b>Date: </b>{{date("Y-m-d")}}<br><br>
                    <b>{{$loan->customer->title}}. {{$loan->customer->first_name}} {{$loan->customer->last_name}}</b>
                </td>
                <td style="width: 30%;margin-right: 20px;float: left">
                    <table width="100%">
                        <tr>
                            <td><b>Loan Code: </b></td>
                            <td>{{$loan->loan_code}}</td>
                        </tr>
                        <tr>
                            <td><b>Released Date: </b></td>
                            <td>{{date("d M, Y",strtotime($loan->release_date))}}</td>
                        </tr>
                        <tr>
                            <td><b>Maturity Date: </b></td>
                            <td>{{date("d M, Y",strtotime($loan->maturity_date))}}</td>
                        </tr>
                        <tr>
                            <td><b>Repayment Cycle: </b></td>
                            <td>{{$loan->repayment_cycle}}</td>
                        </tr>
                        <tr>
                            <td><b>Principal: </b></td>
                            <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($loan->principal,2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Interest(%): </b></td>
                            <td>{{round($loan->interest_rate,2)}}
                                %/{{$loan->interest_period}}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 30%;margin-right: 20px;float: left">
                    <table>
                        <tr>
                            <td><b>Interest: </b></td>
                            <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_interest($loan->id),2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Fee: </b></td>
                            <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_fees($loan->id),2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Penalty: </b></td>
                            <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_penalty($loan->id),2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Due: </b></td>
                            <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_due_amount($loan->id),2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Paid: </b></td>
                            <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_paid($loan->id),2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Balance: </b></td>
                            <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->loan_total_balance($loan->id),2)}}</td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>
    </div>
    <div style="margin-top:30px;margin-left: auto;margin-right: auto;text-transform: capitalize;font-size: 8px;">
        <table class="table">
            <tr>
                <td><h2><b>Payment Received:</b></h2></td>
                <td class="text-right"><h2>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($repayment->amount,2)}}</h2></td>
            </tr>
            <tr>
                <td><h2><b>Collection Date:</b></h2></td>
                <td class="text-right"><h2>{{date('d M,Y',strtotime($repayment->collection_date))}}</h2></td>
            </tr>
            <tr>
                <td><h2><b>Collected By:</b></h2></td>
                <td class="text-right"><h2>{{$repayment->user->first_name}} {{$repayment->user->last_name}}</h2></td>
            </tr>
        </table>
        <p></p>
        <hr>
    </div>
</div>

