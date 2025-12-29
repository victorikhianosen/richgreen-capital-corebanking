@extends('layout.app')
@section('title')
    Loan Classification
@endsection
@section('pagetitle')
Loan Classification
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
                        <h3>Loan Classification for period: {{date("d M, Y",strtotime($_GET['datefrom']))." To ".date("d M, Y",strtotime($_GET['dateto']))}}</h3>
                        @endif
                      </div>
                      </div>
                    <div  style="margin-bottom: 15px">
                      <form action="{{route('report.loanclasfi')}}" method="get" onsubmit="thisForm()">
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
                                <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.loanclasfi')}}'">Reset</button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </form>
                    </div>
                    <div id="printdiv">
                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                      <div class="table-responsive">
                        <table id="lclafsfi" class="table table-bordered table-striped table-condensed table-hover">
                            <thead>
                            <tr style="background-color: #D1F9FF">
                              <th>#</th>
                                <th>Customer</th>
                                <th>Principal</th>
                                <th>Classification</th>
                                <th>Arrears</th>
                                <th>Provision (%)</th>
                                <th>Provided Amount</th>
                                <th>Balance</th>
                                <th>Day</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $principal = 0;
                            $balance = 0;
                            $due = 0;
                            $paid = 0;
                            $provided_amount = 0;
                            $arrears = 0;
                            $i=0;
                            ?>
                            @foreach($data as $key)
                                <?php
                                $principal = $principal + $key->principal;
                                $arrears = $arrears + $key->principal + $getloan->loan_total_interest($key->id);
                                $balance = $balance + $getloan->loan_total_balance($key->id);
                                $paid = $paid + $getloan->loan_total_paid($key->id);
                                if ($key->maturity_date > date("Y-m-d")) {
                                    $classification = "Performing";
                                    $provision_rate = \App\Models\ProvisionRate::find(1)->rate;
                                    $provision = $provision_rate * $getloan->loan_total_balance($key->id) / 100;
                                    $provided_amount = $provided_amount + $provision;
                                    $days = 0;
                                } else {
                                    $days = date_diff(date_create($key->maturity_date), date_create(date("Y-m-d")))->days;
                                    if ($days > 30 && $days < 61) {
                                        $classification = "Pass & Watch";
                                        $provision_rate = \App\Models\ProvisionRate::find(2)->rate;
                                        $provision = $provision_rate * $getloan->loan_total_balance($key->id) / 100;
                                        $provided_amount = $provided_amount + $provision;
                                    } elseif ($days > 60 && $days < 91) {
                                        $classification = "Substandard";
                                        $provision_rate = \App\Models\ProvisionRate::find(3)->rate;
                                        $provision = $provision_rate * $getloan->loan_total_balance($key->id) / 100;
                                        $provided_amount = $provided_amount + $provision;
                                    } elseif ($days > 90 && $days < 181) {
                                        $classification = "Doubtful";
                                        $provision_rate = \App\Models\ProvisionRate::find(4)->rate;
                                        $provision = $provision_rate * $getloan->loan_total_balance($key->id) / 100;
                                        $provided_amount = $provided_amount + $provision;
                                    } elseif ($days > 180) {
                                        $classification = "Lost";
                                        $provision_rate = \App\Models\ProvisionRate::find(5)->rate;
                                        $provision = $provision_rate * $getloan->loan_total_balance($key->id) / 100;
                                        $provided_amount = $provided_amount + $provision;
                                    }
                                }
                                ?>
                                <tr>
                                  <td>{{$i+1}}</td>
                                    <td>
                                        @if(!empty($key->customer))
                                           {{$key->customer->first_name}} {{$key->customer->last_name}}
                                        @endif
                                    </td>
                                    <td>{{number_format($key->principal,2)}}</td>

                                    <td>{{$classification}}</td>
                                    <td>{{number_format($key->principal + $getloan->loan_total_interest($key->id),2)}}</td>

                                    <td>{{$provision_rate}}</td>

                                    <td>{{number_format($provision,2)}}</td>
                                    <td>
                                        @if($key->override==1)
                                            <?php   $due += $key->balance; ?>
                                            <s>{{number_format($getloan->loan_total_due_amount($key->id),2)}}</s><br>
                                            {{number_format($key->balance,2)}}
                                        @else
                                            <?php   $due += $getloan->loan_total_due_amount($key->id); ?>
                                            {{number_format($getloan->loan_total_due_amount($key->id),2)}}
                                        @endif
            
                                    </td>
                                    <td>{{$days}}</td>
                                </tr>
                                <?php $i++;?>
                            @endforeach
                            <tr>
                                <td></td>
                                <td></td>
                                <td><b>{{number_format($principal,2)}} </b></td>
                                <td></td>
                                <td><b>{{number_format($arrears,2)}}</b></td>
                                <td></td>
                                <td><b>{{number_format($provided_amount,2)}}</b></td>
            
                                <td><b>{{number_format($balance,2)}}</b></td>
                                <td></td>
                            </tr>
                            </tbody>
                        </table>
            
                    </div>
                      @else
                      <div class="alert alert-info">Please Select a date range and click on generate report</div>
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
    $("#lclafsfi").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
<script>
   function printsection() {
    document.getElementById("noprint").style.display='none';
  var divContents = document.getElementById("printdiv").innerHTML;
  var a = window.open('', '', 'height=500, width=500');
  a.document.write('<html>');
  a.document.write('<body >');
  a.document.write(divContents);
  a.document.write('</body></html>');
  a.document.close();
  a.print();
  }
</script>
@endsection