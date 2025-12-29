@extends('layout.app')
@section('title')
    Loan Details
@endsection
@section('pagetitle')
Loan Details
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('loan.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                       @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <?php
                    $getsetvalue = new \App\Models\Setting();
                   ?>
                    @inject('getloan', 'App\Http\Controllers\LoanController')

                    <div class="row">
                        <div class="col-md-4 col-lg-4 col-sm-12">
                            <div class="row">
                                <div class="col-md-4 col-lg-4 col-sm-4">
                                    @if(!empty($loan->customer->photo))
                                   <a href="{{asset($loan->customer->photo)}}" class="fancybox"> <img
                                        class="img-responsive"
                                        width="90"
                                        height="90"
                                        src="{{asset($loan->customer->photo)}}"
                                        alt="customer photo"/></a>
                                @else
                                    <img class="img-circle"
                                        src="{{asset('img/avater.webp')}}"
                                        alt="customer photo"/>
                                @endif
                                </div>
                                <div class="col-md-8 col-lg-8 col-sm-12" style="text-align:left;">
                                    <p style="font-size:13px;font-weight:700; color:#000000">
                                        Name: {{ucwords($loan->customer->title." ".$loan->customer->last_name." ".$loan->customer->first_name)}}
                                </p>
                                    <p style="font-size:13px;font-weight:700; color:#000000">
                                       Account Number: {{$loan->customer->acctno}} 
                                    </p>
                                    @can('edit customer')
                                        <a href="{{route('customer.edit',['id' => $loan->customer->id])}}" class="btn btn-info btn-sm">Edit Customer</a>
                                    @endcan
                                    <p style="font-size:13px;font-weight:700; color:#000000">Business name: {{$loan->customer->business_name}}</p>
                                    <p style="font-size:13px;font-weight:700; color:#000000">Occupation: {{$loan->customer->working_status}}</p>
                                   <p style="font-size:13px;font-weight:700; color:#000000">Gender: {{$loan->customer->gender}}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-12">
                            <p style="font-size:13px;font-weight:700; color:#000000">Phone: {{$loan->customer->phone}}<br>
                                <a href="{{route('customers.sms.create',['id' => $loan->customer->id])}}?sendsms=true" class="btn btn-danger btn-sm">Send Sms</a>
                            </p>
                            <p style="font-size:13px;font-weight:700; color:#000000">Email: {{$loan->customer->email}}<br>
                             <a href="{{route('customers.emails.create',['id' => $loan->customer->id])}}?sendmail=true" class="btn btn-danger btn-sm">Send Email</a>
                            </p>
                            <p style="font-size:13px;font-weight:700; color:#000000">Address: {{$loan->customer->residential_address}}</p>
                            <p style="font-size:13px;font-weight:700; color:#000000">State: {{ucwords($loan->customer->state)}}</p>
                            <p style="font-size:13px;font-weight:700; color:#000000">LGA: {{ucwords($loan->customer->state_lga)}}</p>
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-12">
                            <?php 
                            $cusacoffier = DB::table('customers')->select('accountofficer_id')->where('id',$loan->customer_id)->first();
                            $accountofficer = DB::table('accountofficers')->select('full_name')->where('id',$cusacoffier->accountofficer_id)->first();
                           ?>
                           <p style="font-size:13px;font-weight:700; color:#000000">Account Officer: {{!is_null($accountofficer) ? $accountofficer->full_name : "N/A"}}</p>
                           <p style="font-size:13px;font-weight:700; color:#000000">Loan Officer: {{!is_null($loan->accountofficer) ? $loan->accountofficer->full_name : "N/A"}}</p>
                           
                        </div>
                    </div>
                    <div style="text-align: end">
                    <div class="btn-group">
                            <button type="button" class="btn vd_btn vd_bg-red dropdown-toggle" data-toggle="dropdown"> Loan Statement<i class="fa fa-caret-down prepend-icon"></i> </button>
                            <ul class="dropdown-menu" role="menu">
                            <li><a href="{{route('print.loan.statement',['id' => $loan->customer->id])}}?loanid={{$loan->id}}" target="_blank">Print Statement</a></li>
                            <li><a href="{{route('download.loan.statement',['id' => $loan->customer->id])}}?loanid={{$loan->id}}" target="_blank">Download Statement</a></li>
                            <li><a href="{{route('email.loan.statement',['id' => $loan->customer->id])}}">Email Statement</a></li>
                            </ul>
                        </div> 
                      </div> 
                        <hr>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm table-bordered table-condensed table-hover">
                                    <thead>
                                        <tr style="background-color: #D1F9FF">
                                            <th>Loan Acct No</th>
                                            <th>Released</th>
                                            <th>Maturity</th>
                                            <th>Repayment</th>
                                            <th>Principal ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                            <th>Interest (%)</th>
                                            <th>Interest ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                            <th>Fee ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                            <th>Penalty ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                            <th>Due</th>
                                            <th>Paid ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                            <th>Balance ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody> 
                                            <tr>
                                                <td>{{$loan->loan_code}}</td>
                                                <td>{{date("d-m-Y",strtotime($loan->release_date))}}</td>
                                                <td>{{date("d-m-Y",strtotime($loan->maturity_date))}}</td>
                                                <td>{{str_replace("_"," ",$loan->repayment_cycle)}}</td>
                                                <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($loan->principal,2)}}</td>
                                                <td>{{number_format($loan->interest_rate)}}% / {{$loan->interest_period}}</td>
                                                <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_interest($loan->id),2)}}</td>
                                                <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_fees($loan->id),2)}}</td>
                                                <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_penalty($loan->id),2)}}</td>
                                                <td>
                                                    @if($loan->override==1)
                                                        <s>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_due_amount($loan->id),2)}}</s><br>
                                                        {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($loan->balance,2)}}
                                                    @else
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_due_amount($loan->id),2)}}
                                                    @endif
                                                    <br>
                                                    <small>
                                                        <a href="javascript:void(0)" onclick="openoveride()">Override</a>
                                                    </small>
                                                </td>
                                                <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_paid($loan->id),2)}}</td>
                                                <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_balance($loan->id),2)}}</td>
                                                <td>
                                                    @if($loan->status == 'pending')
                                             
                                               <span class="label label-warning">Pending Approval</span> 
                                            
                                              @elseif($loan->status == 'approved')
                                              
                                                  <span class="label label-info">Awaiting Disbursement</span>
                                              
                                             @elseif($loan->status == 'disbursed')
                                             
                                              <span class="label label-success">Active</span>
                                            
                                             @elseif($loan->status == 'declined')
                                             
                                                 <span class="label label-danger">Declined</span>
                                             
                                             @elseif($loan->status == 'withdrawn')
                                             
                                                 <span class="label label-danger">Withdrawn</span>
                                            
                                             @elseif($loan->status == 'written_off')
                                             
                                                 <span class="label label-danger">Written Off</span>
                                            
                                             @elseif($loan->status == 'closed')
                                             
                                                 <span class="badge vd_bg-black">Closed</span>
                                             
                                             @elseif($loan->status == 'pending_reschedule')
                                             
                                                 <span class="label label-warning">Pending Reschedule </span>
                                            
                                             @elseif($loan->status == 'rescheduled')
                                             
                                                 <span class="label label-info">Rescheduled</span>
                                                 
                                                     @elseif($loan->maturity_date < date("Y-m-d") && $getloan->loan_total_balance($loan->id) > 0)
                                                         <span class="label label-danger">Lost</span> 
                                             @else
                                                    {{ucwords($loan->provision_type)}}
                                                @endif
                                                </td>
                                            </tr>
                                            </tbody>
                                </table>
                            </div>
                        <hr>
                        
                      <ul class="nav nav-pills">
                        <li class="active"><a href="#loan_terms" data-toggle="tab">Loan Terms</a></li>

                      @if($loan->status=="disbursed" || $loan->status=="closed" || $loan->status=="withdrawn" || $loan->status=="written_off" || $loan->status=="rescheduled" )

                        <li class=""><a href="#repayments" data-toggle="tab">Repayment</a></li>

                        <li><a href="#loan_schedule" data-toggle="tab">Loan Schedule</a></li>

                        <li class=""><a href="#pending_dues" data-toggle="tab">Pending Due</a></li>
                     @endif
                     
                       <li class=""><a href="#loan_collateral" data-toggle="tab">Loan Collateral</a></li>

                    <li class=""><a href="#loan_files" data-toggle="tab">Loan File</a></li>

                    <li class=""><a href="#loan_comments" data-toggle="tab">Loan Comment</a></li>

                      </ul>

                      <div class="tab-content  mgbt-xs-20">

                        @if($loan->status=="disbursed" || $loan->status=="closed" || $loan->status=="withdrawn" || $loan->status=="written_off" || $loan->status=="rescheduled")
                        
                        <div class="tab-pane" id="repayments">
                           
                           <div style="margin: 10px 0px;">
                                @if($loan->status == 'disbursed')
                            @can('create repayments')
                            <a class="btn btn-default btn-sm" href="{{route('repay.create')}}">Add Repayment</a>
                            @endcan
                             @endif
                           </div>
                          
                            
                            <div class="table-responsive">
                                <table id="data-table" class="table table-bordered table-condensed table-hover">
                                      <thead>
                                      <tr style="background-color: #D1F9FF" role="row">
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Balance</th>
                                    </tr>
                                      </thead>
                                      <tbody>
                                        <?php 
                                           $totinster = $getloan->loan_total_interest($loan->id);
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

                        <div class="tab-pane" id="loan_schedule">
                             <div style="margin: 15px 5px">
                                 @if($loan->status != 'closed')
                                <a href="{{route('schedule.edit',['id' => $loan->id])}}" class="btn btn-info btn-sm">Edit Schedule</a>
                                    @endif
                                <a href="{{route('schedule.print',['id' => $loan->id])}}" class="btn btn-primary btn-sm" target="_blank">Print Statement</a>
    
                                <a href="{{route('schedule.downloadpdf',['id' => $loan->id])}}" class="btn btn-danger btn-sm" target="_blank">Download PDF</a>
    
                                @can('create communication')
                                <a href="{{route('schedule.loanemail',['id' => $loan->id])}}" class="btn btn-success btn-sm">Email Schedule</a>
                                @endcan
                             </div>
                            {{-- <div class="row">
                                <div class="col-sm-12 pull-right">
                                    <div class="btn-group" style="margin: 5px 0px">
                                        <button type="button" class="btn btn-default dropdown-toggle"
                                                data-toggle="dropdown" aria-expanded="false">Loan Schedule
                                                <i class="fa fa-caret-down prepend-icon"></i></button>

                                        <ul class="dropdown-menu" role="menu">
                                            <li></li>
                                                
                                            <li></li>
                                                
                                                <li></li>
                                               
                                               
                                        </ul>
                                    </div>
                                </div>
                               
                            </div> --}}
                                <div class="table-responsive">
                                    <table class="table table-bordered table-condensed table-hover">
                                        <tbody>
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
                                                <b>Fee</b>
                                            </th>
                                            <th style="text-align:right;">
                                                <b>Penalty</b>
                                            </th>
                                            <th style="text-align:right;">
                                                <b>Due</b>
                                            </th>
                                            <th style="text-align:right;">
                                                Total Due
                                            </th>
                                            <th style="text-align:right;">
                                               Paid
                                            </th>
                                            <th style="text-align:right;">
                                                Pending Due
                                            </th>
                                            <th style="text-align:right;">
                                                Principal Balance Owed
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
                                            <td></td>
                                            <td></td>
                                            <td style="text-align:right;">
                                                {{number_format(\App\Models\LoanSchedule::where('loan_id',
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

                                        $getrepamt = \App\Models\LoanRepayment::where('loan_id',$loan->id)->where('due_date',$schedule->due_date)->where('status','1')->sum('amount')
                                        ?> 
                                        <tr class="@if((($schedule->principal+$schedule->interest+$schedule->fees+$schedule->penalty) - $getrepamt)<=0) success @endif">
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
                                                {{number_format($schedule->fees,2)}}
                                            </td>
                                            <td style="text-align:right">
                                                {{number_format($schedule->penalty,2)}}
                                            </td>
                                            <td style="text-align:right; font-weight:bold">
                                                {{number_format(($schedule->principal+$schedule->interest+$schedule->fees+$schedule->penalty),2)}}
                                            </td>
                                            <td style="text-align:right;">
                                                {{number_format($total_due,2)}}
                                            </td>
                                            <td style="text-align:right;">
                                                {{number_format($getrepamt,2)}}
                                            </td>
                                            <td style="text-align:right;">
                                                <?php
                                                $gettotal = ($schedule->principal+$schedule->interest+$schedule->fees+$schedule->penalty)- $getrepamt;    
                                                ?>
                                                {{number_format($gettotal,2)}}
                                            </td>
                                            <td style="text-align:right;">
                                                {{number_format($principal_balance,2)}}
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
                                                {{number_format($getloan->loan_total_principal($loan->id),2)}}
                                            </td>
                                            <td style="text-align:right;">
                                                {{number_format($getloan->loan_total_interest($loan->id),2)}}
                                            </td>
                                            <td style="text-align:right;">
                                                {{number_format($getloan->loan_total_fees($loan->id),2)}}
                                            </td>
                                            <td style="text-align:right;">
                                                {{number_format($getloan->loan_total_penalty($loan->id),2)}}
                                            </td>
                                            <td style="text-align:right; font-weight:bold">
                                                {{number_format($getloan->loan_total_due_amount($loan->id),2)}}
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                        </div>

                        <div class="tab-pane" id="pending_dues">
                            <div class="tab_content">
                                <p>Pending Due</p>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-condensed table-hover">
                                            <tbody>
                                            <tr style="background-color: #F2F8FF">
                                                <th width="200">
                                                    <b>based on loan term:</b>
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
                                                    <b>Total</b>
                                                </th>
                                            </tr>
                                            <tr>
                                                <td class="text-bold bg-red">
                                                    Total Due
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_principal($loan->id),2)}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_interest($loan->id),2)}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_fees($loan->id),2)}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_penalty($loan->id),2)}}
                                                </td>
                                                <td style="text-align:right; font-weight:bold">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_due_amount($loan->id),2)}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-bold bg-green">
                                                    Total Paid
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_paid_item($loan->id,'principal'),2)}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_paid_item($loan->id,'interest'),2)}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_paid_item($loan->id,'fees'),2)}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_paid_item($loan->id,'penalty'),2)}}
                                                </td>
                                                <td style="text-align:right; font-weight:bold">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_paid($loan->id),2)}}
                                                </td>
                                            </tr>  
                                            <tr>
                                                <td class="text-bold bg-gray">
                                                    Balance
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(($getloan->loan_total_principal($loan->id)-$getloan->loan_paid_item($loan->id,'principal')),2)}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(($getloan->loan_total_interest($loan->id)-$getloan->loan_paid_item($loan->id,'interest')),2)}}

                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(($getloan->loan_total_fees($loan->id)-$getloan->loan_paid_item($loan->id,'fees')),2)}}

                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(($getloan->loan_total_penalty($loan->id)-$getloan->loan_paid_item($loan->id,'penalty')),2)}}

                                                </td>
                                                <td style="text-align:right; font-weight:bold">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(($getloan->loan_total_due_amount($loan->id)-$getloan->loan_total_paid($loan->id)),2)}}

                                                </td>
                                            </tr>
                                            <tr style="background-color: #F2F8FF">
                                                <td colspan="6">
                                                    <br><br><b>based on loan schedule:</b></td>
                                            </tr>
                                            <tr>
                                                <td class="text-bold bg-red">
                                                    Due Till {{$getloan->determine_due_date($loan->id,date("Y-m-d"))}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_principal($loan->id,$getloan->determine_due_date($loan->id,date("Y-m-d"))),2)}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_interest($loan->id,$getloan->determine_due_date($loan->id,date("Y-m-d"))),2)}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_fees($loan->id,$getloan->determine_due_date($loan->id,date("Y-m-d"))),2)}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_penalty($loan->id,$getloan->determine_due_date($loan->id,date("Y-m-d"))),2)}}
                                                </td>
                                                <td style="text-align:right; font-weight:bold">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_due_amount($loan->id,$getloan->determine_due_date($loan->id,date("Y-m-d"))),2)}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-bold bg-green">
                                                    Paid Till {{$getloan->determine_due_date($loan->id,date("Y-m-d"))}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_paid_item($loan->id,'principal',$getloan->determine_due_date($loan->id,date("Y-m-d"))),2)}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_paid_item($loan->id,'interest',$getloan->determine_due_date($loan->id,date("Y-m-d"))),2)}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_paid_item($loan->id,'fees',$getloan->determine_due_date($loan->id,date("Y-m-d"))),2)}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_paid_item($loan->id,'penalty',$getloan->determine_due_date($loan->id,date("Y-m-d"))),2)}}
                                                </td>
                                                <td style="text-align:right; font-weight:bold">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_paid($loan->id,$getloan->determine_due_date($loan->id,date("Y-m-d"))),2)}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-bold bg-gray">
                                                    Balance Till {{$getloan->determine_due_date($loan->id,date("Y-m-d"))}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(($getloan->loan_total_principal($loan->id,$getloan->determine_due_date($loan->id,date("Y-m-d")))-$getloan->loan_paid_item($loan->id,'principal',$getloan->determine_due_date($loan->id,date("Y-m-d")))),2)}}
                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(($getloan->loan_total_interest($loan->id,$getloan->determine_due_date($loan->id,date("Y-m-d")))-$getloan->loan_paid_item($loan->id,'interest',$getloan->determine_due_date($loan->id,date("Y-m-d")))),2)}}

                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(($getloan->loan_total_fees($loan->id,$getloan->determine_due_date($loan->id,date("Y-m-d")))- $getloan->loan_paid_item($loan->id,'fees',$getloan->determine_due_date($loan->id,date("Y-m-d")))),2)}}

                                                </td>
                                                <td style="text-align:right">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(($getloan->loan_total_penalty($loan->id,$getloan->determine_due_date($loan->id,date("Y-m-d")))-$getloan->loan_paid_item($loan->id,'penalty',$getloan->determine_due_date($loan->id,date("Y-m-d")))),2)}}

                                                </td>
                                                <td style="text-align:right; font-weight:bold">
                                                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(($getloan->loan_total_due_amount($loan->id,$getloan->determine_due_date($loan->id,date("Y-m-d")))-$getloan->loan_total_paid($loan->id,$getloan->determine_due_date($loan->id,date("Y-m-d")))),2)}}

                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                            </div>
                        </div>
                        @endif

                        <div class="tab-pane active" id="loan_terms">
                            <div class="row" style="margin: 8px 0px">
                                <div class="col-sm-4">
                                    @if($loan->status=='pending')
                                        <div class="col-sm-6 col-md-6 col-lg-6">
                                            @can('approve loans')
                                                <button type="button" class="btn btn-success btn-sm"
                                                        data-toggle="modal"
                                                        data-target="#approveLoan">Approve by MD</button>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                        data-toggle="modal"
                                                        data-target="#declineLoan">Decline</button>
                                            @endcan
                                        </div>
                                    @endif
                                    @if($loan->status=='declined')
                                        <div class="col-sm-3">
                                            @can('approve loans')
                                                <button type="button" class="btn btn-success btn-sm"
                                                        data-toggle="modal"
                                                        data-target="#approveLoan">Approve</button>
                                            @endcan
                                        </div>
                                    @endif
                                    @if($loan->status=='approved')
                                        <div class="col-sm-4">
                                            @can('disburse loans')
                                                <button type="button" class="btn btn-success btn-sm"
                                                        data-toggle="modal"
                                                        data-target="#disburseLoan">Disburse by Operation units</button>
                                                <a type="button" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to nndo Approval')" href="{{route('loan.unapprove',['id' => $loan->id])}}">Undo Approval</a>
                                            @endcan
                                        </div>
                                    @endif
                                    @if($loan->status=='written_off')
                                        <div class="col-sm-3">
                                            @can('loans written off')
                                                <a type="button" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to undo Write Off')" href="{{route('loan.unwrite_off',['id' => $loan->id])}}">Undo Write Off</a>
                                            @endcan
                                        </div>
                                    @endif
                                    @if($loan->status=='withdrawn')
                                        <div class="col-sm-3">
                                            @can('loans withdrawn')
                                                <a type="button" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to undo Withdrawal?')" href="{{route('loan.unwithdraw',['id' => $loan->id])}}">Undo Withdrawal</a>
                                            @endcan
                                        </div>
                                    @endif
                                    @if($loan->status=='disbursed')
                                        <div class="col-sm-4">
                                         
                                                <div class="col-md-6 col-sm-12 col-lg-6">
                                                       @can('disburse loans')
                                                    <a type="button" class="btn btn-danger btn-sm"
                                                       href="{{route('loan.undisburse',['id' => $loan->id])}}" onclick="return confirm('Are you sure you want to undo Disbursement?')" title="undo disbursement">Undo Disbursement</a>
                                                @endcan
 
                                            </div>
                                        </div>
                                    @endif
    
                                    {{-- @if($loan->status=="disbursed" || $loan->status=="closed" || $loan->status=="withdrawn" || $loan->status=="written_off" || $loan->status=="rescheduled" )
                                       
                                            <div class="btn-group">
                                                <button type="button" class="btn vd_btn vd_bg-blue vd_white dropdown-toggle btn-sm"
                                                        data-toggle="dropdown"
                                                        aria-expanded="false">Loan Schedule
                                                    <span class="fa fa-caret-down"></span></button>
                                                <ul class="dropdown-menu" role="menu">
    
                                                    <li>
                                                        <a href="{{url('loan/'.$loan->id.'/loan_statement/print')}}"
                                                           target="_blank">Print Statement</a>
                                                    </li>
    
                                                    <li>
                                                        <a href="{{url('loan/'.$loan->id.'/loan_statement/pdf')}}"
                                                           target="_blank">Download in pdf</a>
                                                    </li>
                                                    @can('create communication')
                                                        <li>
                                                            <a href="{{url('loan/'.$loan->id.'/loan_statement/email')}}"
                                                            >Email Statement</a>
                                                        </li>
                                                @endcan
                                                
    
                                                </ul>
                                            </div>
                                    @endif --}}
                                </div>
    
                                 <div class="col-sm-4 pull-right">
                                        <div class="btn-group-horizontal">
                                            <a type="button" class="btn btn-info btn-sm"
                                               href="{{route('print.offer',['id' => $loan->id])}}"
                                               target="_blank">{{$loan->customer->last_name}} Offer Letter </a>
                                               @if($loan->status != 'closed')
                                            @can('update loans')
                                                <a type="button" class="btn vd_btn vd_bg-googleplus vd_white btn-sm"
                                                   href="{{route('schedule.edit',['id' => $loan->id])}}">Edit Schedule</a>
                                            @endcan
                                            @endif
                                              @can('close loans')
                                                    @if($loan->status != 'closed')
                                                    <a href="{{route('loan.close',['id' => $loan->id])}}" class="btn vd_btn vd_bg-googleplus vd_white btn-sm" onclick="return confirm('Are you sure you want to close this loan?')">Close Loan</a>
                                                    @endif
                                             @endcan

                                             <div class="btn-group">
                                                <button type="button" class="btn bg-primary btn-sm dropdown-toggle margin"
                                                        data-toggle="dropdown"
                                                        aria-expanded="false">More Action
                                                    <span class="fa fa-caret-down"></span></button>
                                                <ul class="dropdown-menu" role="menu">
                                                    @can('loans withdrawn')
                                                        <li>
                                                            <a href="#" class=""
                                                               data-toggle="modal"
                                                               data-target="#withdrawLoan">Withdaw Loan</a>
                                                        </li>
                                                    @endcan
                                                    @can('loans written off')
                                                        <li>
                                                            <a href="#" class=""
                                                               data-toggle="modal"
                                                               data-target="#writeoffLoan">Write Off Loan</a>
                                                        </li>
                                                    @endcan
                                                    @can('loans rescheduled')
                                                        <li>
                                                            <a href="#"
                                                               class=""
                                                               data-toggle="modal"
                                                               data-target="#rescheduleLoan">Reschedule Loan</a>
                                                        </li>
                                                    @endcan
                                                     @can('close loans')
                                                        @if($loan->status != 'closed')
                                                            <li>
                                                                <a href="{{route('loan.close',['id' => $loan->id])}}" onclick="return confirm('Are you sure you want to close this loan?')" class="">Close Loan</a>
                                                            </li>
                                                        @endif
                                                    @endcan
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
    
                                <div class="col-sm-4 pull-right">
                                      @if($loan->schedules->count() < 0)
                                     
                                    <div class="btn-group-horizontal">
                                         @if($loan->status != 'closed')
                                        @can('update loans')
                                            <a type="button" class="btn btn-info btn-sm"
                                               href="{{route('loan.edit',['id' => $loan->id])}}">Edit Loan</a>
                                        @endcan
                                        <!-- DELETE LOAN -->
                                        @can('delete loans')
                                            <a type="button" class="btn btn-danger btn-sm deleteLoan"
                                               href="{{route('loan.delete',['id' => $loan->id])}}">Delete Loan</a>
                                        @endcan
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
    
                            <div class="table-responsive">
                                <table class="table table-condensed">
                                    <tbody>
                                    <tr>
                                        <td>
                                            <b>Loan Status</b>
                                        </td>
                                        <td>
                                       
                                        @if($loan->status == 'pending')
                                             
                                               <span class="label label-warning">Pending Approval</span> 
                                            
                                              @elseif($loan->status == 'approved')
                                              
                                                  <span class="label label-info">Awaiting Disbursement</span>
                                              
                                             @elseif($loan->status == 'disbursed')
                                             
                                              <span class="label label-success">Active</span>
                                            
                                             @elseif($loan->status == 'declined')
                                             
                                                 <span class="label label-danger">Declined</span>
                                             
                                             @elseif($loan->status == 'withdrawn')
                                             
                                                 <span class="label label-danger">Withdrawn</span>
                                            
                                             @elseif($loan->status == 'written_off')
                                             
                                                 <span class="label label-danger">Written Off</span>
                                            
                                             @elseif($loan->status == 'closed')
                                             
                                                 <span class="badge vd_bg-black">Closed</span>
                                             
                                             @elseif($loan->status == 'pending_reschedule')
                                             
                                                 <span class="label label-warning">Pending Reschedule </span>
                                            
                                             @elseif($loan->status == 'rescheduled')
                                             
                                                 <span class="label label-info">Rescheduled</span>
                                                 
                                                     @elseif($loan->maturity_date < date("Y-m-d") && $getloan->loan_total_balance($loan->id) > 0)
                                        <span class="label label-danger">Lost</span> 
                                    
                                             @else
                                             {{ucwords($loan->provision_type)}}
                                        @endif
                                        </td>
                                    </tr>
                                    <tr>
    
                                        <td width="200">
                                            <b>Loan Acct No</b>
                                        </td>
                                        <td>{{$loan->loan_code}}</td>
    
                                    </tr>
                                     <td width="200">
                                            <b>Loan Officer</b>
                                        </td>
                                        <td>{{!is_null($loan->accountofficer) ? $loan->accountofficer->full_name : "N/A"}}</td>
    
                                    </tr>
                                    <tr>
                                        <td>
                                            <b>Loan Product</b>
                                        </td>
                                        <td>
                                            @if(!empty($loan->loan_product))
                                                {{$loan->loan_product->name}}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="bg-primary">
                                            loan term
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>Posted By</b></td>
                                        <td>
                                           {{!is_null($loan->user) ? $loan->user->last_name." ".$loan->user->first_name: ''}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>Approved By</b></td>
                                        <td>
                                           {{!is_null($loan->loan_approved) ? $loan->loan_approved->last_name." ".$loan->loan_approved->first_name: ''}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>Loan Disbursed By</b></td>
                                        <td>
                                           {{!is_null($loan->loan_disbursed) ? $loan->loan_disbursed->last_name." ".$loan->loan_disbursed->first_name: ''}}
                                        </td>
                                    </tr>
                                    @if ($loan->status == 'closed')
                                         <tr>
                                        <td><b>Loan Closed By</b></td>
                                        <td>
                                           {{!is_null($loan->loan_closed) ? $loan->loan_closed->last_name." ".$loan->loan_closed->first_name: ''}}
                                        </td>
                                    </tr>
                                    @endif
                                   
                                    <tr>
                                        <td><b>Disbursement</b></td>
                                        <td>
                                           {{!is_null($loan->disbursed_by) ? $loan->disbursed_by : ''}}
                                        </td>
                                    </tr>
                                    <tr>
    
                                        <td>
                                            <b>Principal Amount</b>
                                        </td>
                                        <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($loan->principal,2)}}</td>
    
                                    </tr>
                                    <tr>
    
                                        <td>
                                            <b>Loan release date</b>
                                        </td>
                                        <td>{{date("d M, Y",strtotime($loan->release_date))}}</td>
    
                                    </tr>
                                    <tr>
                                        <td>
                                            <b>Loan interest method</b>
                                        </td>
                                        <td>
                                           {{str_replace("_"," ",$loan->interest_method)}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <b>Loan interest</b>
                                        </td>
                                        <td>{{$loan->interest_rate}}% / {{$loan->interest_period}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <b>Loan duration</b>
                                        </td>
                                        <td>{{$loan->loan_duration}} {{$loan->loan_duration_type}}s
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>Repayment cycle</b></td>
                                        <td>
                                           {{str_replace("_"," ",$loan->repayment_cycle)}}
                                        </td>
                                    </tr>
    
                                    <tr>
                                        <td><b>Number of repayment</b></td>
                                        <td>
                                            {{\App\Models\LoanSchedule::where('loan_id',$loan->id)->count()}}
                                        </td>
                                    </tr>
                                    {{-- <tr>
                                        <td><b>Decimal Place</b></td>
                                        <td>
                                            @if($loan->decimal_places=='round_off_to_two_decimal')
                                                Round off to two decimal
                                            @endif
                                            @if($loan->decimal_places=='round_off_to_integer')
                                                Round off to integer
                                            @endif
                                        </td>
                                    </tr> --}}
                                    <tr>
                                        <td>
                                            <b>First repayment date</b>
                                        </td>
                                        <td>{{date("d M, Y",strtotime($loan->first_payment_date))}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="bg-info disabled">
                                            System Generated Penalties
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"
                                            class="bg-primary disabled">Late repayment penalty
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-red" colspan="2">
                                            @if($loan->loan_product->enable_late_repayment_penalty==1)
                                                <table class="table">
                                                    <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Type</th>
                                                        <th>Value</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                         <b>{{str_replace("_"," ",$loan->loan_product->late_repayment_penalty_calculate)}}</b>
                                                        </td>
                                                        <td>{{$loan->loan_product->late_repayment_penalty_type}}</td>
                                                        <td>{{$loan->loan_product->late_repayment_penalty_amount}}</td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            @else
                                                <b>Late repayment disabled</b>
                                            @endif
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <br><br>
                                <table class="table table-bordered table-hover">
                                    <tbody>
                                    <tr>
                                        <td colspan="2"
                                            class="bg-primary disabled">After maturity date penalty
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-danger" colspan="2">
                                            @if($loan->loan_product->enable_after_maturity_date_penalty==1)
                                                <table class="table">
                                                    <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Type</th>
                                                        <th>Value</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                           <b>{{str_replace("_"," ",$loan->loan_product->after_maturity_date_penalty_calculate)}}</b>
                                                        </td>
                                                        <td>{{$loan->loan_product->after_maturity_date_penalty_type}}</td>
                                                        <td>{{$loan->loan_product->after_maturity_date_penalty_amount}}</td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            @else
                                                <b>After maturity date disabled</b>
                                            @endif
    
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-primary disabled">
                                            description
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            @if(!empty($loan->description))
                                                {{$loan->description}}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane" id="loan_collateral">
                            <div style="margin:10px 0px">
                                @can('create collateral')
                                    <a type="button" class="btn btn-primary btn-sm"
                                       href="{{route('colla.create')}}?return_url={{URL::current()}}&customerid={{$loan->customer->id}}&loanid={{$loan->id}}">Add Collateral</a>
                                @endcan
                            </div>
                            
                                <div class="table-responsive">
                                    <table id="data-table" class="table table-bordered table-condensed table-hover">
                                        <thead>
                                        <tr style="background-color: #D1F9FF">
                                            <th>Type</th>
                                            <th>Name</th>
                                            <th>Value</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($loan->collaterals as $key)
                                            <tr>
                                                <td>
                                                    {{ucwords($key->collateraltype->name)}}
                                                </td>
                                                <td>{{ ucwords($key->name)}}</td>
                                                <td>{{ number_format($key->value) }}</td>
                                                <td>
                                                    {{ucwords(str_replace("_"," ",$key->status))}}
                                                </td>
                                                <td>{{ $key->date }}</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button"
                                                                class="btn vd_btn vd_bg-linkedin btn-sm dropdown-toggle"
                                                                data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span>
                                                            <span class="sr-only">Toggle Dropdown</span>
                                                        </button>
                                                        <ul class="dropdown-menu" role="menu">
                                                            @can('view collateral')
                                                                <li><a href="{{route('colla.show',['id' => $key->id])}}?return_url={{URL::current()}}"> Details </a></li>
                                                            @endcan
                                                            @can('update collateral')
                                                                <li><a href="{{route('colla.edit',['id' => $key->id])}}?return_url={{URL::current()}}&customerid={{$loan->customer->id}}&loanid={{$loan->id}}"><i
                                                                                class="fa fa-edit"></i>Edit</a></li>
                                                            @endcan
                                                            @can('delete collateral')
                                                                <li>
                                                                    <a href="{{route('colla.delete',['id' => $key->id])}}"
                                                                       class="delete" onclick="return confirm('Are you sure you want to delete these record');"><i
                                                                                class="fa fa-trash"></i>Delete</a></li>
                                                            @endcan
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                        </div>

                        <div class="tab-pane" id="loan_files">
                            <p>To add new loan files or remove existing files, pls click the <b>Loan Terms</b> tab and
                                then
                                <b>Edit Loan</b>.</p>
                            <ul class="" style="font-size:12px; padding-left:10px;list-style:none;">
                                 <li class="unstyled_list">
                                     <a href="{{!is_null($loan->files) ? asset($loan->files) : 'javascript:void(0)'}}" target="_blank" download>Download / View File</a>
                                     </li>
                            </ul>
                        </div>
                        <div class="tab-pane" id="loan_comments">
                            <div class="tab_content">
                                <div class="btn-group-horizontal">
                                    <a type="button" class="btn btn-default btn-sm"
                                       href="{{route('comment.create')}}?return_url={{URL::current()}}&loanid={{$loan->id}}">Add Comment</a>
                                </div>
                                <br>
    
                                <div class="row">
                                    @foreach($loan->comments as $comment)
                                        <div class="bg-default">
                                            <!-- User image -->
                                            <img src="{{asset('img/avater.webp')}}"
                                                 class="img-circle" width="80" height="80" alt="User Image">
    
                                            <div class="col-md-12 col-lg-12 col-sm-12 ">
                                    <span class="username">
                                        @if(!empty(\App\Models\User::find($comment->user_id)))
                                            {{ucfirst(\App\Models\User::find($comment->user_id)->first_name)}} {{ucfirst(\App\Models\User::find($comment->user_id)->last_name)}}
                                        @endif
    
                                        <span class="text-muted pull-right">
                                            {{date("d M, Y",strtotime($comment->created_at)) ." at ". date("h:ia",strtotime($comment->created_at))}}
                                        </span>
                                    </span><!-- /.username -->
                                              <p>  {!! $comment->notes !!}</p>
                                                <span class="text-muted pull-right">
                <div class="btn-group-horizontal">
                    <a type="button" class="btn btn-primary btn-sm"
                       href="{{route('comment.edit',['id' => $comment->id])}}?return_url={{URL::current()}}&loanid={{$loan->id}}">Edit</a>
                       <a type="button" class="btn btn-danger btn-sm"
                            href="{{route('comment.delete',['id' => $comment->id])}}" onclick="return confirm('Are you sure you want to delete these record');">delete</a>
                </div>
                                    </span>
                                            </div>
                                            <!-- /.comment-text -->
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                      </div>
                  </div>
                </div>
                <!-- Panel Widget --> 
              </div>
              <!-- col-md-12 --> 
            </div>
            <!-- row -->
  </div> <!-- container end -->
  

  <div class="modal fade" id="approveLoan">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header vd_bg-blue vd_white">
                <button type="button" class="close vd_white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Approve Loan</h4>
            </div>
            <form action="{{route('loan.approve',['id' => $loan->id])}}" method="post" onsubmit="thisForm()">
                @csrf
           <div class="modal-body">
                <div class="form-group">
                    <label for="">Approved Date</label>
                    <input type="date" name="approved_date" class="form-control" id="apdt" required value="{{old('approved_date')}}">
                </div>
                <div class="form-group">
                    <label for="">Approved Amount</label>
                    <input type="number" name="approved_amount" class="form-control" id="apamt" required value="{{old('approved_amount')?? $loan->principal}}">
                </div>
                <div class="form-group">
                    <label for="">Notes</label>
                    <textarea name="approved_notes" id="apnt" class="form-control" cols="10" rows="3">{{old('approved_notes')}}</textarea>
                </div>
            </div>
            <div class="modal-footer  background-login">
                <button type="submit"  class="btn btn-success btn-sm" id="btnssubmit">Approve</button>
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            </div>
        </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div class="modal fade" id="disburseLoan">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header vd_bg-blue vd_white">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Disburse Loan</h4>
            </div>
            <form action="{{route('loan.disburse',['id' => $loan->id])}}" method="post" onsubmit="thisForm()">
                @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Disbursed Date</label>
                    <input type="date" name="disbursed_date" class="form-control" required value="{{$loan->release_date}}">
                </div>
                <div class="form-group">
                    <label>First Payment Date</label>
                    <input type="date" name="first_payment_date" required class="form-control" value="{{$loan->first_payment_date}}">
                </div>
                <div class="form-group">
                    <label>Disbursed By</label>
                    <select name="disbursed_by" class="form-control" onchange="if(this.value='transfer'){document.getElementById('shwtrns').style.display='block'}else{document.getElementById('shwtrns').style.display='none'}" required id="loan_disbursed_by">
                        <option value="cash">Cash</option>
                        <!--<option value="transfer">Transfer</option>-->
                    </select>
                </div>
                 <div id="shwtrns" style="display:none">
                <div class="form-group">
                    <label>Banks</label>
                    <select name="bank" class="form-control width-100" id="bank">
                        <option selected disabled>Select Bank</option>
                        @foreach ($banks as $bank)
                            <option value="{{$bank->bank_code}}">{{$bank->bank_name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Account Number</label>
                    <input type="number" pattern="0-9"  name="account_number" id="acno2" class="form-control" value="">
                     <img src="{{asset('img/loading.gif')}}" id="sttext" style="display: none;float:right" alt="loading">  
                </div>
                <input type="hidden" name="recipient_name" id="recpname" value="">
                 <p>Account Name: <span class="acnme"></span></p>
                 <p>Account Number: <span class="acnum"></span></p>
               </div>
                <div class="form-group">
                    <label for="">Notes</label>
                    <textarea name="disbursed_notes" id="apnt" class="form-control" cols="10" rows="3">{{old('disbursed_notes')}}</textarea>
                </div>
                
            </div>
            <div class="modal-footer background-login">
                <button type="submit" class="btn btn-success btn-sm" id="btnssubmit">Save</button>
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="declineLoan">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header vd_bg-blue vd_white">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Decline Loan</h4>
            </div>
            <form action="{{route('loan.decline',['id' => $loan->id])}}" method="post" onsubmit="thisForm()">
                @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="">Declined Date</label>
                    <input type="date" name="declined_date" class="form-control" required id="declined_date" value="{{old('declined_date')}}">
                </div>
                <div class="form-group">
                    <label for="">Notes</label>
                    <textarea name="declined_notes" id="declined_notes" cols="10" rows="3" required>{{old('declined_notes')}}</textarea>
                </div>
            </div>
            <div class="modal-footer background-login">
                <button type="submit" class="btn btn-success btn-sm" id="btnssubmit">Save</button>
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="writeoffLoan">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header vd_bg-blue vd_white">
                <button type="button" class="close vd_white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Write Off Loan</h4>
            </div>
            <form action="{{route('loan.write_off',['id' => $loan->id])}}" method="post" onsubmit="thisForm()">
                @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="written_off_date" class="form-control" required id="written_off_date" value="{{old('written_off_date')}}">
                </div>
                <div class="form-group">
                    <label for="">Notes</label>
                    <textarea name="written_off_notes" id="declined_notes" cols="10" rows="3" required>{{old('written_off_notes')}}</textarea>
                </div>
            </div>
            <div class="modal-footer background-login">
                <button type="submit" class="btn btn-success btn-sm" id="btnssubmit">Save</button>
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="withdrawLoan">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header vd_bg-blue vd_white">
                <button type="button" class="close vd_white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Withdraw Loan</h4>
            </div>
            <form action="{{route('loan.withdraw',['id' => $loan->id])}}" method="post" onsubmit="thisForm()">
                @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="withdrawn_date" class="form-control" required id="withdrawn_date" value="{{old('withdrawn_date')}}">
                </div>
                <div class="form-group">
                    <label for="">Notes</label>
                    <textarea name="withdrawn_notes" id="declined_notes" cols="10" rows="3" required>{{old('withdrawn_notes')}}</textarea>
                </div>
            </div>
            <div class="modal-footer background-login">
                <button type="submit" class="btn btn-success btn-sm" id="btnssubmit">Save</button>
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="overridebal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header vd_bg-blue vd_white">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">*</span></button>
                <h4 class="modal-title">Override</h4>
            </div>
            <form action="{{route('loan.override',['id' => $loan->id])}}" method="post" onsubmit="thisForm()">
          @csrf 
                <div class="modal-body">
                <input type="hidden" name="override" id="override" value="1">
                
               <div class="form-group">
                <label>Manual Loan Due Amount</label>
                <input type="number" name="balance" autocomplete="off" id="balance" required value="{{$loan->balance}}">
               </div>
            </div>
            <div class="modal-footer background-login">
                <button type="submit" class="btn btn-success btn-sm" id="btnssubmit">Save</button>
                <button type="button" class="btn default"
                        data-dismiss="modal">Close</button>
            </div>
        </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="rescheduleLoan">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header vd_bg-blue vd_white">
                <button type="button" class="close vd_white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Reschedule Loan</h4>
            </div>
            <form action="{{route('loan.reschedule',['id' => $loan->id])}}" method="get" onsubmit="thisForm()">
            <div class="modal-body">
                <div class="form-group">
                    <label>Reschedule On</label>
                    <select name="type" class="form-control" required id="loan_disbursed_by">
                        <option value="1">Outstanding (Principal + Interest)</option>
                        <option value="2">Outstanding (Principal + Interest + Fees)</option>
                        <option value="3">Outstanding Total Amount</option>
                    </select>
                </div>
                <input type="hidden" name="return_url" value="{{URL::current()}}">
                
            </div>
            <div class="modal-footer background-login">
                <button type="submit" class="btn btn-success btn-sm" id="btnssubmit">Save</button>
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endsection
@section('scripts')
<script>
    function openoveride(){
        $("#overridebal").modal('show');
    }
</script>
<script>
  $(document).ready(function(){
    $("#ro").select2();
    $("#sibo").select2();

    $("#acno2").keyup(function(){
      let acnoval = $("#acno2").val();
      let bank = $("#bank").val();
      
     if(acnoval.length == 10){
        $.ajax({
        url:"{{route('verifybnkacct')}}",
        method:"get",
        data:{'account_number':acnoval,'bank_code':bank},
        beforeSend:function(){
          $("#sttext").show();
        },
        success:function(data){
          if(data.status == false){
            $("#sttext").hide();
            toastr.error(data.message);
            return false;
          }else{
            $("#sttext").hide();
          $("#cbl").show();
          $(".acnme").text(data.data.first_name+" "+data.data.last_name).addClass('text-success');
          $(".acnum").text(acnoval).addClass('text-success');
          $("#recpname").val(data.data.first_name+" "+data.data.last_name);
          toastr.success(data.message);
          }
        },
        error:function(xhr,status,errorThrown){
          toastr.error('An Error Occured... '+errorThrown);
          $("#sttext").hide();
          return false;
        }
      })
    }else if(acnoval == "" || bank == ""){
        toastr.error('Account number or bank is empty');
        return false;
     }
     
    });

   
  });
</script>
@endsection