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
@inject('getloan', 'App\Http\Controllers\InvestmentController')
<div>
    <h3 class="text-center"><img src="{{asset($getsetvalue->getsettingskey('company_logo'))}}" class="img-responsive" width="120" alt="logo"></h3>

    <h3 class="text-center"><b>{{$getsetvalue->getsettingskey('company_name')}}</b>
    </h3>

    <h3 class="text-center"><b>Payment Schedule</b></h3>

    <div style="width: 100%;margin-left: auto;font-size:10px;margin-right: auto;border-top: solid thin #2cc3dd;border-bottom: solid thin #2cc3dd;padding-top: 40px;text-transform: capitalize">
        <table style="margin-top: 20px">
            <tr>
                <td style="width: 30%;margin-right: 20px;float: left">
                    <b>Date:</b>{{date("Y-m-d")}}<br><br>
 					Customer Name:<b>{{$fd->customer->first_name}} {{$fd->customer->last_name}}</b><br />
                     Address <b>{{$fd->customer->residential_address}} </b><br />
                     Phone No: <b>{{$fd->customer->phone}} </b>
                </td>
                <td style="width: 30%;margin-right: 20px;float: left">
                    <table width="100%">
                        <tr>
                            <td><b>Investment Code</b></td>
                            <td>{{$fd->fixed_deposit_code}}</td>
                        </tr>
                        <tr>
                            <td><b>Released Date</b></td>
                            <td>{{date("d M, Y",strtotime($fd->release_date))}}</td>
                        </tr>
                        <tr>
                            <td><b>Maturity Date</b></td>
                            <td>{{date("d M, Y",strtotime($fd->maturity_date))}}</td>
                        </tr>
                        <tr>
                            <td><b>Payment Cycle</b></td>
                            <td>{{$fd->payment_cycle}}</td>
                        </tr>
                        <tr>
                            <td><b>Principal</b></td>
                            <td>{{$getsetvalue->getsettingskey('company_currency')." ".number_format($fd->principal,2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Interest (%)</b></td>
                            <td>{{number_format($fd->interest_rate,2)}}
                                % / {{$fd->interest_period}}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 30%;margin-right: 20px;float: left">
                    <table>
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
        </table>
    </div>
    <div style="margin-top:30px;margin-left: auto;margin-right: auto;text-transform: capitalize;font-size: 8px;">
        <table border="1" class="table">
            <thead>
                <tr style="background-color: #F2F8FF">
                    <th style="width: 10px">
                        <b>Sn</b>
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
                        <b>Rollover Interest</b>
                    </th>
                    <th style="text-align:right;">
                       Total Interest Due
                     </th>
                    <th style="text-align:right;">
                       Total Due
                     </th>
                    
                </tr>
            </thead>
            <tbody>
                
                <?php
                $count = 0;
                $total_due = 0;
                $totinterest = 0;
                $totrollover = 0;
                $totinterestdue = 0;

                foreach ($schedules as $schedule) {
                if ($count == 1) {
                    $total_due = ($schedule->principal + $schedule->interest);

                } else {
                    $total_due = $total_due + ($schedule->principal + $schedule->interest);
                }
                $totinterest += $schedule->interest;
                $totrollover += $schedule->rollover;
                $totinterestdue += $schedule->total_interest;
                

                $getrepamt = \App\Models\InvestmetRepayment::where('fixed_deposit_id',$fxd->id)->where('due_date',$schedule->due_date)->sum('amount')
                ?> 
                <tr class="@if((($schedule->principal+$schedule->interest) - $getrepamt)<=0) success @endif">
                    <td>
                        {{$count+1}}
                    </td>
                    <td>
                        {{date("d-m-Y",strtotime($schedule->due_date))}}
                    </td>
                    <td>
                        {{$schedule->description}}
                    </td>
                    <td style="text-align:right">
                        {{number_format($schedule->principal,2)}}
                    </td>
                    <td style="text-align:right">
                        {{number_format($schedule->interest,2)}}
                    </td>
                    <td style="text-align:right">
                        {{number_format($schedule->rollover,2)}}
                    </td>
                    <td style="text-align:right; font-weight:bold">
                        {{number_format(($schedule->total_interest),2)}}
                    </td>
                    <td style="text-align:right; font-weight:bold">
                        {{number_format(($schedule->total_due),2)}}
                    </td>
                    
                </tr>
                <?php
                $count++;
                }
                ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td style="font-weight:bold">Total</td>
                    <td style="text-align:right;">
                    </td>
                    <td style="text-align:right;font-weight:bold">
                        {{number_format($totinterest,2)}}
                    </td>
                    <td style="text-align:right;font-weight:bold">
                        {{number_format($totrollover,2)}}
                    </td>
                    <td style="text-align:right;font-weight:bold">
                        {{number_format($totinterestdue,2)}}
                    </td>
                 
                </tr>
                </tbody>
        </table>
    </div>
</div>
