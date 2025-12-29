@extends('layout.app')
@section('title')
    Loan Transactions
@endsection
@section('pagetitle')
Loan Transactions
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
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-10 col-lg-10 col-sm-12">
                        @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                        <h3>Loan Transactions for period: {{date("d M, Y",strtotime($_GET['datefrom']))." To ".date("d M, Y",strtotime($_GET['dateto']))}}</h3>
                        @endif
                      </div>
                      </div>
                    <div class="noprint" style="margin-bottom: 15px">
                      <form action="{{route('report.loantrx')}}" method="get" onsubmit="thisForm()">
                        <input type="hidden" name="filter" value="true">
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
                                <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Search Report</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.loantrx')}}'">Reset</button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </form>
                    </div>
                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                      <div class="table-responsive">

                        <table id="loantrx" class="table table-bordered table-striped table-condensed table-hover">
                            <thead>
                            <tr style="background-color: #D1F9FF" role="row">
                                <th> S/N </th>
                                <th> Collection Date</th>
                                <th>Loan Code</th>
                                <th>Customer</th>
                                <th>Posted By</th>
                                <th>Method</th>
                                <th>Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                                 <?php $number = 0;
                                 $totamount=0;
                                 ?>
                            @foreach($data as $key)
                                <?php $totamount += $key->amount; ?>
                                <tr>
                                     <td>{{ $number+1 }}</td> 
                                    <td>{{date("d M,Y",strtotime($key->collection_date))}}</td>
                                   <td>{{!empty($key->loan) ? $key->loan->loan_code : "N/A"}}</td>
                                     
                                    <td>
                                        @if(!empty($key->customer))
                                            {{$key->customer->first_name}} {{$key->customer->last_name}}
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($key->user))
                                            {{$key->user->first_name}} {{$key->user->last_name}}
                                        @endif
                                    </td>
                                    <td>{{$key->repayment_method}}</td>
                                    <td>{{number_format($key->amount,2)}}</td>

                                </tr>
                                <?php $number++; ?>
                            @endforeach
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>
                                  <b>{{number_format($totamount,2)}}</b>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                      @else
                      <div class="alert alert-info">Please Select a date range and click on Search report</div>
                  @endif
                    </div>
                  </div>
                </div>
                <!-- Panel Widget --> 
              </div>
              <!-- col-md-12 --> 
            </div>
            <!-- row -->
  
@endsection
@section('scripts')
<script type="text/javascript">
  $(document).ready(function(){
    $("#loantrx").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
@endsection