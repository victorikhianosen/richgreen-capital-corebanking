@extends('layout.app')
@section('title')
    Loan List
@endsection
@section('pagetitle')
Loan List
@endsection
@section('content')
<?php
$getsetvalue = new \App\Models\Setting();
?>
@inject('getloan', 'App\Http\Controllers\ReportsController')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    {{-- <div style="text-align: right">
                      <button type="button" class="btn btn-danger btn-sm" onclick="printsection()"><i class="fa fa-print" aria-hidden="true"></i> Print</button>
                    </div> --}}
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-10 col-lg-10 col-sm-12">
                        @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                        <h3>Callover Report For Period: {{date("d M, Y",strtotime($_GET['datefrom']))." To ".date("d M, Y",strtotime($_GET['dateto']))}}</h3>
                     
                        <h4>Status Type: <b>{{$_GET['status']}}</b></h4>
                        @endif
                      </div>
                      </div>
                      <div class="noprint" style="margin-bottom: 15px">
                        <form action="{{route('report.loanlist')}}" method="get" onsubmit="thisForm()">
                          <input type="hidden" name="filter" value="true">
                          <input type="hidden" name="callovertype" value="{{!empty($_GET['callovertype']) ? $_GET['callovertype'] : ''}}">
                          <table class="table table-bordered table-hover table-sm">
                            <thead>
                              <tr>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>
                                  <div class="form-group">
                                    <input type="date" name="datefrom" required id="" class="form-control" value="{{!empty($_GET['datefrom']) ? $_GET['datefrom'] : ''}}">
                                  </div>
                                </td>
                                <td>
                                  <div class="form-group">
                                    <input type="date" name="dateto" required id="" class="form-control" value="{{!empty($_GET['dateto']) ? $_GET['dateto'] : ''}}">
                                  </div>
                                </td>
                                <td>
                                  <div class="form-group">
                                    <select name="status" id="sts" required class="form-control">
                                      <option value="all">All</option>
                                      <option value="declined">Decline</option>
                                      <option value="approved">Approved</option>
                                      <option value="pending">Pending</option>
                                      <option value="aisbursed">Disbursed</option>
                                      <option value="withdrawn">Withdrawn</option>
                                      <option value="written_off">Written off</option>
                                      <option value="closed">Closed</option>
                                      <option value="rescheduled">Rescheduled</option>
                                    </select>
                                  </div>
                                </td>
                                <td>
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Search Records</button>
                                  <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.loanlist')}}'">Reset</button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </form>
                      </div>
                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                      <div class="table-responsive">
                        <table id="datalist" class="table table-bordered table-striped table-condensed table-hover">
                            <thead>
                            <tr style="background-color: #D1F9FF">
                                <th>#</th>
                                <th>Cutomer</th>
                                <th>Principal</th>
                                <th>Released</th>
                                <th>Interest (%)</th>
                                <th>Due</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $principal = 0;
                            $balance = 0;
                            $due = 0;
                            $paid = 0;
                            $i=0;
                            ?>
                            @foreach($data as $key)
                                <?php
                                $principal = $principal + $key->principal;
                                $balance = $balance + $getloan->loan_total_balance($key->id);
                                $paid = $paid + $getloan->loan_total_paid($key->id);
                                ?>
                                <tr>
                                  <td>{{$i+1}}</td>
                                    <td>
                                      @if(!empty($key->customer))
                                      {{$key->customer->first_name}} {{$key->customer->last_name}}
                                   @endif
                                    </td>
                                    <td>{{number_format($key->principal,2)}}</td>
                                    
                                    <td>{{date("d M, Y",strtotime($key->release_date))}}</td>
                                    <td>
                                        {{number_format($key->interest_rate,2)}}%/{{$key->interest_period}}
                                    </td>
                                    <td>
                                        @if($key->override==1)
                                            <?php   $due+= $key->balance; ?>
                                            <s>{{number_format($getloan->loan_total_due_amount($key->id),2)}}</s><br>
                                            {{number_format($key->balance,2)}}
                                        @else
                                            <?php   $due += $getloan->loan_total_due_amount($key->id); ?>
                                            {{number_format($getloan->loan_total_due_amount($key->id),2)}}
                                        @endif
            
                                    </td>
                                    <td>{{number_format($getloan->loan_total_paid($key->id),2)}}</td>
                                    <td>
                                        {{number_format($getloan->loan_total_balance($key->id),2)}}
                                    </td>
                                    <td>
                                        @if($key->maturity_date<date("Y-m-d") && $getloan->loan_total_balance($key->id)>0)
                                            <span class="label label-danger">Past Maturity</span>
                                        @else
                                            @if($key->status=='pending')
                                                <span class="label label-warning">Pending Approval</span>
                                            @endif
                                            @if($key->status=='approved')
                                                <span class="label label-info">Awaiting Disbursement</span>
                                            @endif
                                            @if($key->status=='disbursed')
                                                <span class="label label-info">Active</span>
                                            @endif
                                            @if($key->status=='declined')
                                                <span class="label label-danger">Declined</span>
                                            @endif
                                            @if($key->status=='withdrawn')
                                                <span class="label label-danger">Withdrawn</span>
                                            @endif
                                            @if($key->status=='written_off')
                                                <span class="label label-danger">Written Off</span>
                                            @endif
                                            @if($key->status=='closed')
                                                <span class="label label-success">Closed</span>
                                            @endif
                                            @if($key->status=='pending_reschedule')
                                                <span class="label label-warning">Pending Reschedule</span>
                                            @endif
                                            @if($key->status=='rescheduled')
                                                <span class="label label-info">Rescheduled</span>
                                            @endif
                                        @endif
                                    </td>
                                    
                                </tr>
                                <?php $i++;?>
                            @endforeach
                            <tr>
                                <td></td>
                                <td></td>
                                <td><b>{{number_format($principal,2)}}</b></td>
                                <td></td>
                                <td></td>
                                <td><b>{{number_format($due,2)}}</b></td>
                                <td><b>{{number_format($paid,2)}}</b></td>
                                <td><b>{{number_format($balance,2)}}</b></td>
                                <td></td>
                            </tr>
                            </tbody>
                        </table>
            
                    </div>
                      @else
                      <div class="alert alert-info">Please select a date range and click on search record button</div>
                  @endif
                  </div>
                </div>
                <!-- Panel Widget --> 
              </div>
              <!-- col-md-12 --> 
            </div>
            <!-- row -->
  </div>
@endsection
@section('scripts')
<script type="text/javascript">
  $(document).ready(function(){
    $("#datalist").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
@endsection