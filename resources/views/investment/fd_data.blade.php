@extends('layout.app')
@section('title')
    Fixed Deposit Details
@endsection
@section('pagetitle')
    Fixed Deposit Details
@endsection
@section('content')
    <div class="container">
        <div class="row" id="advanced-input">
            <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                            <a href="{{ route('manage.fd') }}" class="btn btn-danger"><span class="menu-icon"> <i
                                        class="fa fa-angle-left"></i> </span> Back</a>
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
                        @inject('getloan', 'App\Http\Controllers\InvestmentController')

                        <div class="row">
                            <div class="col-md-4 col-lg-4 col-sm-12">
                                <div class="row">
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        @if (!empty($fxd->customer->photo))
                                            <a href="{{ asset($fxd->customer->photo) }}" class="fancybox"> <img
                                                    class="img-responsive" width="90" height="90"
                                                    src="{{ asset($fxd->customer->photo) }}" alt="customer photo" /></a>
                                        @else
                                            <img class="img-circle" src="{{ asset('img/avater.webp') }}"
                                                alt="customer photo" />
                                        @endif
                                    </div>
                                    <div class="col-md-8 col-lg-8 col-sm-12" style="text-align:left;">
                                        <p style="font-size:13px;font-weight:700; color:#000000">
                                            Name:
                                            {{ ucwords($fxd->customer->title . ' ' . $fxd->customer->last_name . ' ' . $fxd->customer->first_name) }}
                                        </p>
                                        <p style="font-size:13px;font-weight:700; color:#000000">
                                            Account Number: {{ $fxd->customer->acctno }}
                                        </p>
                                        @can('edit customer')
                                            <a href="{{ route('customer.edit', ['id' => $fxd->customer->id]) }}"
                                                class="btn btn-info btn-sm">Edit Customer</a>
                                        @endcan
                                        <p style="font-size:13px;font-weight:700; color:#000000">Business name:
                                            {{ $fxd->customer->business_name }}</p>
                                        <p style="font-size:13px;font-weight:700; color:#000000">Occupation:
                                            {{ $fxd->customer->working_status }}</p>
                                        <p style="font-size:13px;font-weight:700; color:#000000">Gender:
                                            {{ $fxd->customer->gender }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-4 col-sm-12">
                                <p style="font-size:13px;font-weight:700; color:#000000">Phone:
                                    {{ $fxd->customer->phone }}<br>
                                    <a href="{{ route('customers.sms.create', ['id' => $fxd->customer->id]) }}?sendsms=true"
                                        class="btn btn-danger btn-sm">Send Sms</a>
                                </p>
                                <p style="font-size:13px;font-weight:700; color:#000000">Email:
                                    {{ $fxd->customer->email }}<br>
                                    <a href="{{ route('customers.emails.create', ['id' => $fxd->customer->id]) }}?sendmail=true"
                                        class="btn btn-danger btn-sm">Send Email</a>
                                </p>
                                <p style="font-size:13px;font-weight:700; color:#000000">Address:
                                    {{ $fxd->customer->residential_address }}</p>
                                <p style="font-size:13px;font-weight:700; color:#000000">State:
                                    {{ ucwords($fxd->customer->state) }}</p>
                                <p style="font-size:13px;font-weight:700; color:#000000">LGA:
                                    {{ ucwords($fxd->customer->state_lga) }}</p>
                            </div>
                            <div class="col-md-4 col-lg-4 col-sm-12">

                                <?php
                                $cusacoffier = DB::table('customers')->select('accountofficer_id')->where('id', $fxd->customer_id)->first();
                                $accountofficer = DB::table('accountofficers')->select('full_name')->where('id', $cusacoffier->accountofficer_id)->first();
                                $currcy = DB::table('exchangerates')->select('currency_symbol')->where('id', $fxd->customer->exchangerate_id)->first();
                                ?>

                                <p style="font-size:13px;font-weight:700; color:#000000">Account Officer:
                                    {{ !is_null($accountofficer) ? $accountofficer->full_name : 'N/A' }}</p>
                                <p style="font-size:13px;font-weight:700; color:#000000">Investment Officer:
                                    {{ !is_null($fxd->accountofficer) ? $fxd->accountofficer->full_name : 'N/A' }}</p>

                            </div>
                        </div>

                        <hr>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm table-bordered table-condensed table-hover">
                                <thead>
                                    <tr style="background-color: #D1F9FF">
                                        <th>Investment Code</th>
                                        <th>Released</th>
                                        <th>Maturity</th>
                                        <th>Payment</th>
                                        <th>Principal
                                            ({{ !empty($currcy) ? $currcy->currency_symbol : $getsetvalue->getsettingskey('currency_symbol') }})
                                        </th>
                                        <th>Interest (%)</th>
                                        <th>Interest
                                            ({{ !empty($currcy) ? $currcy->currency_symbol : $getsetvalue->getsettingskey('currency_symbol') }})
                                        </th>
                                        <th>Paid
                                            ({{ !empty($currcy) ? $currcy->currency_symbol : $getsetvalue->getsettingskey('currency_symbol') }})
                                        </th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $fxd->fixed_deposit_code }}</td>
                                        <td>{{ date('d-m-Y', strtotime($fxd->release_date)) }}</td>
                                        <td>{{ date('d-m-Y', strtotime($fxd->maturity_date)) }}</td>
                                        <td>{{ str_replace('_', ' ', $fxd->payment_cycle) }}</td>
                                        <td>{{ !empty($currcy) ? $currcy->currency_symbol : $getsetvalue->getsettingskey('currency_symbol') }}{{ number_format($fxd->principal, 2) }}
                                        </td>
                                        <td>{{ $fxd->interest_rate }}% / {{ $fxd->interest_period }}</td>
                                        <td>{{ !empty($currcy) ? $currcy->currency_symbol : $getsetvalue->getsettingskey('currency_symbol') }}{{ number_format($getloan->investment_total_interest($fxd->id), 2) }}
                                        </td>

                                        <td>{{ !empty($currcy) ? $currcy->currency_symbol : $getsetvalue->getsettingskey('currency_symbol') }}{{ number_format($getloan->investment_total_paid($fxd->id), 2) }}
                                        </td>
                                        <td>

                                            @if ($fxd->status == 'pending')
                                                <span class="badge vd_bg-yellow">Pending Approval</span>
                                            @endif
                                            @if ($fxd->status == 'approved')
                                                <span class="badge vd_bg-green">Active</span>
                                            @endif

                                            @if ($fxd->status == 'declined')
                                                <span class="badge vd_bg-red">Declined</span>
                                            @endif
                                            @if ($fxd->status == 'closed')
                                                <span class="badge vd_bg-black">Closed</span>
                                            @endif

                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <hr>

                        <ul class="nav nav-pills">
                            <li class="active"><a href="#loan_terms" data-toggle="tab">Investment Terms</a></li>

                            @if ($fxd->status == 'approved' || $fxd->status == 'closed')
                                <li class=""><a href="#repayments" data-toggle="tab">Repayment</a></li>

                                <li><a href="#loan_schedule" data-toggle="tab">Investment Schedule</a></li>
                            @endif
                        </ul>

                        <div class="tab-content  mgbt-xs-20">

                            @if ($fxd->status == 'approved' || $fxd->status == 'closed')
                                <div class="tab-pane" id="repayments">
                                    <div style="margin: 10px 0px;">
                                        @can('fixed deposit liquidation')
                                            <a class="btn btn-default btn-sm" href="{{ route('liqfd') }}">Liquidate
                                                Investment</a>
                                        @endcan
                                        @if ($fxd->interest_method == 'monthly')
                                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                                data-target="#sche">Manual interest Payment</button>
                                        @endif
                                    </div>

                                    <div class="table-responsive">
                                        <table id="data-table" class="table table-bordered table-condensed table-hover">
                                            <thead>
                                                <tr style="background-color: #D1F9FF" role="row">
                                                    <th>Collection Date</th>
                                                    <th>Customer Name</th>
                                                    <th>Collected By</th>
                                                    <th>Amount</th>
                                                    <!--<th>Action</th>-->
                                                    <!--<th>Receipt</th>-->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($payments as $key)
                                                    <tr>
                                                        <td>{{ date('d M, Y', strtotime($key->collection_date)) }}</td>
                                                        <td>{{ ucwords($key->customer->last_name) . ' ' . ucwords($key->customer->first_name) }}
                                                        </td>
                                                        <td>{{ !empty($key->user) ? ucwords($key->user->last_name . ' ' . $key->user->first_name) : 'N/A' }}
                                                        </td>

                                                        <td>{{ !empty($currcy) ? $currcy->currency_symbol : $getsetvalue->getsettingskey('currency_symbol') }}{{ number_format($key->amount) }}
                                                        </td>
                                                        <!--<td>-->
                                                        <!--  @can('update repayments')
        -->
                                                            <!--  <a href="{{ route('repay.edit', ['id' => $key->id]) }}" class="text-info">Edit</a>-->
                                                            <!--
    @endcan-->
                                                        <!--  @can('delete repayments')
        -->
                                                            <!--      |  <a href="{{ route('repay.delete', ['id' => $key->id]) }}" class="text-danger" onclick="return confirm('Are you sure you want to delete these record')">Delete</a>-->
                                                            <!--
    @endcan-->
                                                        <!--<-->
                                                        <!--<td>-->
                                                        <!--  <a href="{{ route('repay.print', ['id' => $key->id]) }}?loanid={{ $key->loan_id }}" class="btn vd_btn vd_bg-twitter btn-sm" target="_blank"><i class="fa fa-print"></i> Print</a>-->
                                                        <!--  <a href="{{ route('repay.pdf', ['id' => $key->id]) }}?loanid={{ $key->loan_id }}" class="btn vd_btn vd_bg-red btn-sm" target="_blank"><i class="fa fa-file"></i> PDF</a>-->

                                                        <!--   @can('create communication')
        -->
                                                            <!--        <a type="button" class="btn btn-default btn-sm"><i class="fa fa-envelope"></i> Mail</a>-->
                                                            <!--
    @endcan -->
                                                        <!--</td>-->
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane" id="loan_schedule">
                                    <div style="margin: 15px 5px">
                                        <a href="{{ route('schedulefd.edit', ['id' => $fxd->id]) }}"
                                            class="btn btn-info btn-sm">Edit Schedule</a>

                                        <a href="{{ route('printfd.schedule', ['id' => $fxd->id]) }}"
                                            class="btn btn-primary btn-sm" target="_blank">Print Schedule</a>

                                        <a href="{{ route('schedulefd.downloadpdf', ['id' => $fxd->id]) }}"
                                            class="btn btn-danger btn-sm" target="_blank">Download PDF</a>

                                        @can('create communication')
                                            <a href="javascript:void(0)"
                                                data-href="{{ route('schedule.fdemail', ['id' => $fxd->id]) }}"
                                                id="emschde" class="btn btn-success btn-sm">Email Schedule</a>
                                        @endcan
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-condensed table-hover">
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
                                                        <b>{{ $fxd->interest_method == 'rollover' ? 'Compound' : 'Simple' }}
                                                            Rollover Interest</b>
                                                    </th>
                                                    <th style="text-align:right;">
                                                        Total Interest
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
                                                <tr
                                                    style="{{ $schedule->closed == '1' ? 'background-color:#f67; color:#fff;' : '' }}">
                                                    <td>
                                                        {{ $count + 1 }}
                                                    </td>
                                                    <td>
                                                        {{ date('d-m-Y', strtotime($schedule->due_date)) }}
                                                    </td>
                                                    <td>
                                                        {{ $schedule->description }}
                                                    </td>
                                                    <td style="text-align:right">
                                                        {{ number_format($schedule->principal, 2) }}
                                                    </td>
                                                    <td style="text-align:right">
                                                        {{ number_format($schedule->interest, 2) }}
                                                    </td>
                                                    <td style="text-align:right">
                                                        {{ number_format($schedule->rollover, 2) }}
                                                    </td>
                                                    <td style="text-align:right; font-weight:bold">
                                                        {{ number_format($schedule->total_interest, 2) }}
                                                    </td>
                                                    <td style="text-align:right; font-weight:bold">
                                                        {{ number_format($schedule->total_due, 2) }}
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
                                                        {{ number_format($totinterest, 2) }}
                                                    </td>
                                                    <td style="text-align:right;font-weight:bold">
                                                        {{ number_format($totrollover, 2) }}
                                                    </td>
                                                    <td style="text-align:right;font-weight:bold">
                                                        {{ number_format($totinterest + $totrollover, 2) }}
                                                    </td>

                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <div class="tab-pane active" id="loan_terms">
                                <div class="row" style="margin: 8px 0px">
                                    <div class="col-sm-4">
                                        @if ($fxd->status == 'pending')
                                            <div class="col-sm-12 col-md-8 col-lg-8">
                                                @can('approve fixed deposit')
                                                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal"
                                                        data-target="#approveLoan">Approve</button>

                                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                                        data-target="#declineLoan">Decline</button>
                                                @endcan
                                            </div>
                                        @endif

                                    </div>

                                    <div class="col-sm-8 pull-right">
                                        @if ($fxd->status == 'approved')
                                            {{-- <a type="button" class="btn vd_btn vd_bg-googleplus vd_white btn-sm"
                                                href="javascript:void(0)"
                                                data-href="{{ route('fdemail.offer', ['id' => $fxd->id]) }}"
                                                id="eminvest" title="Email Investment Letter">Email Investment Letter</a> --}}

                                            <a class="btn vd_btn vd_bg-googleplus vd_white btn-sm"
                                                href="{{ route('fdemail.offer', ['id' => $fxd->id]) }}"

                                                id="" title="Email Investment Letter">Email Investment Letter</a>



                                            <a href="{{ route('printfd.offer', ['id' => $fxd->id]) }}" target="_blank"
                                                class="btn vd_btn vd_bg-blue vd_white btn-sm"
                                                title="Print Investment Letter">
                                                {{ $fxd->customer->last_name }} Investment Letter
                                            </a>


                                            {{-- <a type="button" class="btn vd_btn vd_bg-blue vd_white btn-sm"
                                         data-toggle="modal"

                                        data-target="#signature" title="Print Investment Letter">{{$fxd->customer->last_name}} Investment Letter </a> --}}
                                        @endif

                                        @if ($fxd->status != 'closed')
                                            @can('edit fixed deposit')
                                                <a type="button" class="btn btn-info btn-sm"
                                                    href="{{ route('edit.fd', ['id' => $fxd->id]) }}"
                                                    title="Edit investment">Edit Investment</a>
                                            @endcan

                                            @can('delete fixed deposit')
                                                <a type="button"
                                                    class="btn vd_btn vd_bg-googleplus vd_white btn-sm deleteLoan"
                                                    href="javascript:void(0)"
                                                    data-href="{{ route('delete.fd', ['id' => $fxd->id]) }}"
                                                    data-id="{{ $fxd->id }}" id="deletere"
                                                    title="Delete investment">Delete Investment</a>
                                            @endcan
                                        @endif
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-condensed">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <b>Investment Status</b>
                                                </td>
                                                <td>
                                                    @if ($fxd->status == 'pending')
                                                        <span class="badge vd_bg-yellow">Pending Approval</span>
                                                    @endif
                                                    @if ($fxd->status == 'approved')
                                                        <span class="badge vd_bg-green">Active</span>
                                                    @endif

                                                    @if ($fxd->status == 'declined')
                                                        <span class="badge vd_bg-red">Declined</span>
                                                    @endif
                                                    @if ($fxd->status == 'closed')
                                                        <span class="badge vd_bg-black">Closed</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>

                                                <td width="200">
                                                    <b>Investment Code</b>
                                                </td>
                                                <td>{{ $fxd->fixed_deposit_code }}</td>

                                            </tr>
                                            <td width="200">
                                                <b>Investment Officer</b>
                                            </td>
                                            <td>{{ !is_null($fxd->accountofficer) ? $fxd->accountofficer->full_name : 'N/A' }}
                                            </td>

                                            </tr>
                                            <tr>
                                                <td>
                                                    <b>Fixed Deposit Product</b>
                                                </td>
                                                <td>
                                                    @if (!empty($fxd->fixed_deposit_product_id))
                                                        {{ $fxd->fixed_deposit_product->name }}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="bg-primary">
                                                    Investment term
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b>Posted By</b></td>
                                                <td>
                                                    {{ !is_null($fxd->user) ? $fxd->user->last_name . ' ' . $fxd->user->first_name : ($fxd->system_approve == '1' ? 'system' : '') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b>Approved By</b></td>
                                                <td>
                                                    {{ !is_null($fxd->fd_approved) ? $fxd->fd_approved->last_name . ' ' . $fxd->fd_approved->first_name : ($fxd->system_approve == '1' ? 'system' : '') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <b>Withholding Tax</b>
                                                </td>
                                                <td>{{ $fxd->enable_withholding_tax == '1' ? 'Yes' : 'No' }}</td>
                                            </tr>
                                            <tr>

                                                <td>
                                                    <b>Principal Amount</b>
                                                </td>
                                                <td>{{ !empty($currcy) ? $currcy->currency_symbol : $getsetvalue->getsettingskey('currency_symbol') }}{{ number_format($fxd->principal, 2) }}
                                                </td>

                                            </tr>
                                            <tr>

                                                <td>
                                                    <b>release date</b>
                                                </td>
                                                <td>{{ date('d M, Y', strtotime($fxd->release_date)) }}</td>

                                            </tr>
                                            <tr>
                                                <td>
                                                    <b>interest method</b>
                                                </td>
                                                <td>
                                                    {{ str_replace('_', ' ', $fxd->interest_method) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <b>Investment interest</b>
                                                </td>
                                                <td>{{ $fxd->interest_rate }}% / {{ $fxd->interest_period }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <b>Investment duration</b>
                                                </td>
                                                <td>{{ $fxd->duration }} {{ $fxd->duration_type }}s
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b>Payment cycle</b></td>
                                                <td>
                                                    {{ str_replace('_', ' ', $fxd->payment_cycle) }}
                                                </td>
                                            </tr>


                                            <tr>
                                                <td>
                                                    <b>First Payment date</b>
                                                </td>
                                                <td>{{ date('d M, Y', strtotime($fxd->first_payment_date)) }}</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <b>Date Created</b>
                                                </td>
                                                <td>{{ date('d M, Y h:ia', strtotime($fxd->created_at)) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"></td>
                                            </tr>


                                        </tbody>
                                    </table>

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
                    <h4 class="modal-title">Approve Investment</h4>
                </div>
                <form action="{{ route('fd.approve', ['id' => $fxd->id]) }}" method="post" id="approveinvet"
                    onsubmit="thisForm()">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="">Approved Date</label>
                            <input type="date" name="approved_date" class="form-control" id="apdt" readonly
                                required value="{{ $fxd->release_date }}">
                        </div>
                        <div class="form-group">
                            <label for="">Approved Amount</label>
                            <input type="number" name="approved_amount" class="form-control" id="apamt" readonly
                                value="{{ old('approved_amount') ?? $fxd->principal }}">
                        </div>
                        <div class="form-group">
                            <label for="">Notes</label>
                            <textarea name="approved_notes" id="apnt" class="form-control" cols="10" rows="3">{{ old('approved_notes') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer  background-login">
                        <button type="submit" class="btn btn-success btn-sm" id="btnssubmit">Approve</button>
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
                    <h4 class="modal-title">Decline Investment</h4>
                </div>
                <form action="{{ route('fd.decline', ['id' => $fxd->id]) }}" method="post" id="declineinvet"
                    onsubmit="thisForm()">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="">Declined Date</label>
                            <input type="date" name="declined_date" class="form-control" required id="declined_date"
                                value="{{ old('declined_date') }}">
                        </div>
                        <div class="form-group">
                            <label for="">Notes</label>
                            <textarea name="declined_notes" id="declined_notes" cols="10" rows="3" required>{{ old('declined_notes') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer background-login">
                        <button type="submit" class="btn btn-success btn-sm" id="dbtnssubmit">Decline</button>
                        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    {{-- <div class="modal fade" id="signature">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header vd_bg-blue vd_white">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Who To Sign</h4>
            </div>
            <form action="#" method="post">
            <div class="modal-body">
                <div class="form-group">
                    <label for="">Signature One</label>
                    <input type="text"  name="signatureOne" id="signatureOne" class="form-control" placeholder="Enter Name" required value="">
                </div>
                <div class="form-group">
                    <label for="">Signature Two</label>
                    <input type="text"  name="signatureTwo" id="signatureTwo" class="form-control" placeholder="Enter Name" required value="">
                </div>

            </div>
            <div class="modal-footer background-login">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="printinv('{{route('printfd.offer',['id' => $fxd->id])}}')" target="_blank">Proceed</button>

            </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div> --}}


    <div class="modal fade" id="sche">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header vd_bg-blue vd_white">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Manual Interest Payment</h4>
                </div>

                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-stripe">
                            <thead>
                                <tr>
                                    <th>
                                        <b>Sn</b>
                                    </th>
                                    <th>
                                        <b>Date</b>
                                    </th>
                                    <th>
                                        <b>Principal</b>
                                    </th>
                                    <th>
                                        <b>Interest</b>
                                    </th>
                                    <th>
                                        <b>{{ $fxd->interest_method == 'rollover' ? 'Compound' : 'Simple' }} Rollover
                                            Interest</b>
                                    </th>
                                    <th>
                                        Total Interest
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $j = 0; ?>
                                @foreach ($manualschdelu as $item)
                                    <tr>
                                        <td>{{ $j + 1 }}</td>
                                        <td>{{ date('d-m-Y', strtotime($item->due_date)) }}</td>
                                        <td>
                                            {{ number_format($item->principal, 2) }}
                                        </td>
                                        <td>
                                            {{ number_format($item->interest, 2) }}
                                        </td>
                                        <td>
                                            {{ number_format($item->rollover, 2) }}
                                        </td>
                                        <td style="font-weight:bold">
                                            {{ number_format($item->total_interest, 2) }}
                                        </td>
                                        <td>
                                            <a href="javascript:void(0)"
                                                onclick="manualrepay('{{ route('manaul_repayment') }}','{{ $item->id }}')"
                                                class="btn btn-danger btn-sm">Pay</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
@endsection
@section('scripts')
    <script>
        function openoveride() {
            $("#overridebal").modal('show');
        }

        function printinv(url) {
            signo = document.getElementById('signatureOne').value;
            signt = document.getElementById('signatureTwo').value;
            if (signo == "" && signt == "") {
                toastr.error('Please Enter Name of Signatory');
            } else {
                window.open(url + '?signone=' + signo + '&signtwo=' + signt, '_blank');
            }
        }

        function manualrepay(url, scheid) {
            $("#sche").modal('hide');

            $.ajax({
                url: url + "?schdelid=" + scheid,
                method: 'get',
                beforeSend: function() {
                    $(".loader").css('visibility', 'visible');
                    $(".loadingtext").text('Please Wait...');
                },
                success: function(data) {
                    if (data.status == 'success') {
                        $(".loader").css('visibility', 'hidden');
                        toastr.success(data.msg);
                        window.location.reload();
                    } else {
                        toastr.error(data.msg);
                        $(".loader").css('visibility', 'hidden');
                        return false;
                    }
                },
                error: function(xhr, status, errorThrown) {
                    $(".loader").css('visibility', 'hidden');
                    toastr.error('Error ' + errorThrown);
                    return false;
                }
            });
        }
    </script>
    <script>
        $(document).ready(function() {
            $("#ro").select2();
            $("#sibo").select2();

            $("#acno2").keyup(function() {
                let acnoval = $("#acno2").val();
                let bank = $("#bank").val();

                if (acnoval.length == 10) {
                    $.ajax({
                        url: "{{ route('verifybnkacct') }}",
                        method: "get",
                        data: {
                            'account_number': acnoval,
                            'bank_code': bank
                        },
                        beforeSend: function() {
                            $("#sttext").show();
                        },
                        success: function(data) {
                            if (data.status == false) {
                                $("#sttext").hide();
                                toastr.error(data.message);
                                return false;
                            } else {
                                $("#sttext").hide();
                                $("#cbl").show();
                                $(".acnme").text(data.data.first_name + " " + data.data
                                    .last_name).addClass('text-success');
                                $(".acnum").text(acnoval).addClass('text-success');
                                $("#recpname").val(data.data.first_name + " " + data.data
                                    .last_name);
                                toastr.success(data.message);
                            }
                        },
                        error: function(xhr, status, errorThrown) {
                            toastr.error('An Error Occured... ' + errorThrown);
                            $("#sttext").hide();
                            return false;
                        }
                    })
                } else if (acnoval == "" || bank == "") {
                    toastr.error('Account number or bank is empty');
                    return false;
                }

            });

            //approve
            $("#approveinvet").submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: $("#approveinvet").attr('action'),
                    method: 'post',
                    data: $("#approveinvet").serialize(),
                    beforeSend: function() {
                        $("#btnssubmit").text('Please wait...');
                        $("#btnssubmit").attr('disabled', true);
                    },
                    success: function(data) {
                        if (data.status == 'success') {
                            toastr.success(data.msg);
                            $("#btnssubmit").text('Approve');
                            $("#btnssubmit").attr('disabled', false);
                            window.location.reload();
                        } else {
                            toastr.error(data.msg);
                            $("#btnssubmit").text('Approve');
                            $("#btnssubmit").attr('disabled', false);
                            return false;
                        }
                    },
                    error: function(xhr, status, errorThrown) {
                        toastr.error('Error ' + errorThrown);
                        $("#btnssubmit").text('Approve');
                        $("#btnssubmit").attr('disabled', false);
                        return false;
                    }
                });
            });

            //decline
            $("#declineinvet").submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: $("#declineinvet").attr('action'),
                    method: 'post',
                    data: $("#declineinvet").serialize(),
                    beforeSend: function() {
                        $("#dbtnssubmit").text('Please wait...');
                        $("#dbtnssubmit").attr('disabled', true);
                    },
                    success: function(data) {
                        if (data.status == 'success') {
                            toastr.success(data.msg);
                            $("#dbtnssubmit").text('Decline');
                            $("#dbtnssubmit").attr('disabled', false);
                            window.location.reload();
                        } else {
                            toastr.error(data.msg);
                            $("#dbtnssubmit").text('Decline');
                            $("#dbtnssubmit").attr('disabled', false);
                            return false;
                        }
                    },
                    error: function(xhr, status, errorThrown) {
                        toastr.error('Error ' + errorThrown);
                        $("#dbtnssubmit").text('Decline');
                        $("#dbtnssubmit").attr('disabled', false);
                        return false;
                    }
                });
            });

            //email schedule
            $("#emschde").click(function(e) {
                let url = $("#emschde").data('href');

                $.ajax({
                    url: url,
                    method: 'get',
                    beforeSend: function() {
                        $(".loader").css('visibility', 'visible');
                        $(".loadingtext").text('Please Wait...');
                    },
                    success: function(data) {
                        if (data.status == 'success') {
                            $(".loader").css('visibility', 'hidden');
                            toastr.success(data.msg);
                        } else {
                            toastr.error(data.msg);
                            $(".loader").css('visibility', 'hidden');
                            return false;
                        }
                    },
                    error: function(xhr, status, errorThrown) {
                        $(".loader").css('visibility', 'hidden');
                        toastr.error('Error ' + errorThrown);
                        return false;
                    }
                });


            });

            //email investment letter
            $("#eminvest").click(function(e) {
                let url = $("#eminvest").data('href');

                $.ajax({
                    url: url,
                    method: 'get',
                    beforeSend: function() {
                        $(".loader").css('visibility', 'visible');
                        $(".loadingtext").text('Please Wait...');
                    },
                    success: function(data) {
                        if (data.status == 'success') {
                            $(".loader").css('visibility', 'hidden');
                            toastr.success(data.msg);

                        } else {
                            toastr.error(data.msg);
                            $(".loader").css('visibility', 'hidden');
                            return false;
                        }
                    },
                    error: function(xhr, status, errorThrown) {
                        $(".loader").css('visibility', 'hidden');
                        toastr.error('Error ' + errorThrown);
                        return false;
                    }
                });

            });

            //delete investment
            $("#deletere").click(function(e) {
                let url = $("#deletere").data('href');

                if (confirm('Are you sure you want to delete these record')) {
                    $.ajax({
                        url: url,
                        method: 'get',
                        beforeSend: function() {
                            $(".loader").css('visibility', 'visible');
                            $(".loadingtext").text('Deleting...');
                        },
                        success: function(data) {
                            if (data.status == 'success') {
                                $(".loader").css('visibility', 'hidden');
                                toastr.success(data.msg);
                                window.location.href = "{{ route('manage.fd') }}";
                            } else {
                                toastr.error(data.msg);
                                $(".loader").css('visibility', 'hidden');
                                return false;
                            }
                        },
                        error: function(xhr, status, errorThrown) {
                            $(".loader").css('visibility', 'hidden');
                            toastr.error('Error ' + errorThrown);
                            return false;
                        }
                    });
                }
            });

        });
    </script>
@endsection
