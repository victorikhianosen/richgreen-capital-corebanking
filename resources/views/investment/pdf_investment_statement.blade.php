<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Investment Statement</title>
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
       @inject('getloan', 'App\Http\Controllers\InvestmentController')
    <div>
        <h3 class="text-center"><img src="{{asset($getsetvalue->getsettingskey('company_logo'))}}" class="img-responsive" width="120" alt="logo"></h3>
        <h3 class="text-center"><b>Investment Statement</b></h3>
    
        <div style="width: 100%;margin:0px auto;font-size:10px;border-top: solid thin #2cc3dd;border-bottom: solid thin #2cc3dd;padding-top: 40px;text-transform: capitalize">
            <table style="margin-top: 20px width:100%">
               <tbody>
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
                                <td><b>Investment Code:</b></td>
                                <td>{{$fd->fixed_deposit_code}}</td>
                            </tr>
                            <tr>
                                <td><b>Released Date:</b></td>
                                <td>{{$fd->release_date}}</td>
                            </tr>
                            <tr>
                                <td><b>Maturity Date:</b></td>
                                <td>{{$fd->maturity_date}}</td>
                            </tr>
                            <tr>
                                <td><b>Payment Cycle:</b></td>
                                <td>{{$fd->payment_cycle}}</td>
                            </tr>
                            <tr>
                                <td><b>Principal:</b></td>
                                <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($fd->principal,2)}}</td>
                            </tr>
                            <tr>
                                <td><b>Interest (%):</b></td>
                                <td>{{number_format($fd->interest_rate,2)}}
                                    % per {{$fd->interest_period}}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%;margin-right: 20px;" align="left">
                        <table style="width: 100%">
                            <tr>
                                <td><b>Interest</b></td>
                                <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->investment_total_interest($fd->id),2)}}</td>
                            </tr>
                           
                            <tr>
                                <td><b>Total Interest</b></td>
                                <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($getloan->investment_total_paid($fd->id),2)}}</td>
                            </tr>
                          
                        </table>
    
                    </td>
                </tr>
               </tbody>
            </table>
        </div>
        <div style="margin-top:30px;margin-left: auto;margin-right: auto;text-transform: capitalize;font-size: 8px;">
            <h3 class="text-center"><b>Payments</b></h3>
            @if(count($fd->repayments)>0)
                <table border="1" class="table table-condensed">
                    <thead>
                    <tr>
                        <th>Collection Date</th>
                        <th>Customer Name</th>
                        <th>Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($fd->repayments as $key)
                        <tr>
                            <td>{{date("d M, Y",strtotime($key->collection_date))}}</td>
                            <td>
                                {{$key->customer->first_name}} {{$key->customer->last_name}}
                            </td>
                            
                            <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($key->amount,2)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <h5>No Payment made</h5>
            @endif
        </div>
    </div>
    
</body>
</html>