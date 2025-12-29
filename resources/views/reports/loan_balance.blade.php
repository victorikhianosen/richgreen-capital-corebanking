@extends('layout.app')
@section('title')
    Loan Balance
@endsection
@section('pagetitle')
Loan Balance
@endsection
@section('content')
  <div class="container">
    <?php
    $getsetvalue = new \App\Models\Setting();
   ?>
    @inject('getloan', 'App\Http\Controllers\ReportsController')
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-10 col-lg-10 col-sm-12">
                        @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                        <h3>Loan Balance for period: {{date("d M, Y",strtotime($_GET['datefrom']))." To ".date("d M, Y",strtotime($_GET['dateto']))}}</h3>
                        @endif
                      </div>
                      </div>
                    <div  style="margin-bottom: 15px">
                      <form action="{{route('report.loanbal')}}" method="get" onsubmit="thisForm()">
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
                                <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.loanbal')}}'">Reset</button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </form>
                    </div>

                    <div id="printdiv">
                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                  <div class="table-responsive">
               <table id="lbal" class="table table-bordered table-striped table-condensed table-hover table-sm">
                <?php
                $principal = 0;
                $balance = 0;
                $due = 0;
                $paid = 0;
                $interest_paid = 0;
                $principal_paid = 0;
                $fees = 0;
                $penalty = 0;
                $i=0;
                ?>
                <thead>
                <tr style="background-color: #D1F9FF">
                    <th>#</th>
                    <th>Customer</th>
                    <th>Principal({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                    <th>Released</th>
                    <th>Interest (%)</th>
                    <th>Due</th>
                    <th>Paid({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                    {{-- <th>Principal Paid({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                    <th>Interest Paid({{$getsetvalue->getsettingskey('currency_symbol')}})</th> --}}
                    <th>Fee</th>
                    <th>Penalty</th>
                    <th>Balance({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                    
                </tr>
                </thead>
                <tbody>
                @foreach($data as $key)
                    <?php
                    $principal += $key->principal;
                    $balance += $getloan->loan_total_balance($key->id);
                    $paid += $getloan->loan_total_paid($key->id);
                    // $interest_paid +=  $getloan->loan_paid_item($key->id, 'interest');
                    // $principal_paid += $getloan->loan_paid_item($key->id,'principal');
                    $fees += $getloan->loan_total_fees($key->id);
                    $penalty = $penalty + $getloan->loan_total_penalty($key->id);
                    ?>
                    <tr>
                      <td>{{ $i+1}}</td>
                        <td>
                            @if(!empty($key->customer))
                               {{$key->customer->first_name}} {{$key->customer->last_name}}
                            @endif
                        </td>
                        
                        <td>{{number_format($key->principal,2)}}</td>
                        <td>{{date("d M, Y",strtotime($key->release_date))}}</td>
                        <td>
                            {{number_format($key->interest_rate,2)}}%/{{ucwords($key->interest_period)}}
                        </td>
                        <td>
                            @if($key->override==1)
                                <?php $due += $key->balance; ?>
                                <s>{{number_format($getloan->loan_total_due_amount($key->id),2)}}</s><br>
                                {{number_format($key->balance,2)}}
                            @else
                                <?php   $due += $getloan->loan_total_due_amount($key->id); ?>
                                {{number_format($getloan->loan_total_due_amount($key->id),2)}}
                            @endif

                        </td>
                        <td>{{number_format($getloan->loan_total_paid($key->id),2)}}</td>

                        {{-- <td>{{number_format($getloan->loan_paid_item($key->id,'principal'),2)}} </td>

                        <td>{{number_format($getloan->loan_paid_item($key->id,'interest'),2)}}</td> --}}

                        <td>{{number_format($getloan->loan_total_fees($key->id),2)}} </td>

                        <td>{{number_format($getloan->loan_total_penalty($key->id),2)}}</td>

                        <td>{{number_format($getloan->loan_total_balance($key->id),2)}}</td>
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
                  {{-- <td><b>{{number_format($principal_paid,2)}}</b></td>
                  <td><b>{{number_format($interest_paid,2)}}</b></td> --}}
                  <td><b>{{number_format($fees,2)}}</b></td>
                  <td><b>{{number_format($penalty,2)}}</b></td>
                  <td><b>{{number_format($balance,2)}}</b></td>
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
  </div>
@endsection
@section('scripts')
<script type="text/javascript">
  $(document).ready(function(){
    $("#lbal").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf'],
  });
  });
</script>
@endsection