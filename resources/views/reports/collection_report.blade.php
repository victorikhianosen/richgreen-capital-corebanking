@extends('layout.app')
@section('title')
    Collection Report
@endsection
@section('pagetitle')
Collection Report
@endsection
@section('content')
<?php
$getsetvalue = new \App\Models\Setting();
?>
@inject('getloan', 'App\Http\Controllers\ReportsController')

  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <p>Please note that <b>Total Loans</b> may not equal <b>Principal Loans</b> + <b>Interest Loans</b> + <b>Penalty
                  Loans</b> + <b>Fees Loans</b> if you have overriden the total due amount of any loan.</p>
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: right">
                      <button type="button" class="btn btn-danger btn-sm" onclick="printsection()"><i class="fa fa-print" aria-hidden="true"></i> Print</button>
                    </div>
                  </div>
                  <div class="panel-body" id="printdiv">
                    <div class="row">
                      <div class="col-md-10 col-lg-10 col-sm-12">
                        @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                        <h3>Collection Report for period: <span>{{date("d M, Y",strtotime($_GET['datefrom']))." To ".date("d M, Y",strtotime($_GET['dateto']))}}</span></h3>
                        @endif
                      </div>
                      </div>
                      <div id="noprint" style="margin-bottom: 15px">
                        <form action="{{route('report.collreport')}}" method="get" onsubmit="thisForm()">
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
                                    <input type="date" name="datefrom" required id="" class="form-control">
                                  </div>
                                </td>
                                <td>
                                  <div class="form-group">
                                    <input type="date" name="dateto" required id="" class="form-control">
                                  </div>
                                </td>
                                <td>
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Generate Report</button>
                                  <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.collreport')}}'">Reset</button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </form>
                      </div>

                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                      <div class="row">
                        <div class="col-md-2 col-lg-2">
                          <div class="panel widget">
                            <div class="panel-heading" style="background-color:#ff0000;color:#fff">
                              <h3 class="panel-title">Principal Due</h3>
                            </div>
                            <div class="panel-body">
                              {{$getsetvalue->getsettingskey('currency_symbol')." ".number_format($getloan->loans_total_due_item('principal',$_GET['datefrom'],$_GET['dateto']),2)}}
                            </div>
                          </div>
                        </div>

                        <div class="col-md-2 col-lg-2">
                          <div class="panel widget">
                            <div class="panel-heading" style="background-color:#ff0000;color:#fff">
                              <h3 class="panel-title">Interest Due</h3>
                            </div>
                            <div class="panel-body">
                              {{$getsetvalue->getsettingskey('currency_symbol')." ".number_format($getloan->loans_total_due_item('interest',$_GET['datefrom'],$_GET['dateto']),2)}}
                            </div>
                          </div>
                        </div>

                        <div class="col-md-2 col-lg-2">
                          <div class="panel widget">
                            <div class="panel-heading" style="background-color:#ff0000;color:#fff">
                              <h3 class="panel-title">Penalty Due</h3>
                            </div>
                            <div class="panel-body">
                              {{$getsetvalue->getsettingskey('currency_symbol')." ".number_format($getloan->loans_total_due_item('penalty',$_GET['datefrom'],$_GET['dateto']),2)}}
                            </div>
                          </div>
                        </div>

                        <div class="col-md-2 col-lg-2">
                          <div class="panel widget">
                            <div class="panel-heading" style="background-color:#ff0000;color:#fff">
                              <h3 class="panel-title">Fees Due</h3>
                            </div>
                            <div class="panel-body">
                              {{$getsetvalue->getsettingskey('currency_symbol')." ".number_format($getloan->loans_total_due_item('fees',$_GET['datefrom'],$_GET['dateto']),2)}}
                            </div>
                          </div>
                        </div>

                        <div class="col-md-4 col-lg-4">
                          <div class="panel widget">
                            <div class="panel-heading" style="background-color:#ff0000;color:#fff">
                              <h3 class="panel-title">Total Loans Due</h3>
                            </div>
                            <div class="panel-body">
                              {{$getsetvalue->getsettingskey('currency_symbol')." ".number_format($getloan->loans_total_due($_GET['datefrom'],$_GET['dateto']),2)}}
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-2 col-lg-2">
                          <div class="panel widget">
                            <div class="panel-heading" style="background-color:#00a65a;color:#fff">
                              <h3 class="panel-title">Principal Paid</h3>
                            </div>
                            <div class="panel-body">
                              {{$getsetvalue->getsettingskey('currency_symbol')." ".number_format($getloan->loans_total_paid_item('principal',$_GET['datefrom'],$_GET['dateto']),2)}}
                            </div>
                          </div>
                        </div>

                        <div class="col-md-2 col-lg-2">
                          <div class="panel widget">
                            <div class="panel-heading" style="background-color:#00a65a;color:#fff">
                              <h3 class="panel-title">Interest Paid</h3>
                            </div>
                            <div class="panel-body">
                              {{$getsetvalue->getsettingskey('currency_symbol')." ".number_format($getloan->loans_total_paid_item('interest',$_GET['datefrom'],$_GET['dateto']),2)}}
                            </div>
                          </div>
                        </div>

                        <div class="col-md-2 col-lg-2">
                          <div class="panel widget">
                            <div class="panel-heading" style="background-color:#00a65a;color:#fff">
                              <h3 class="panel-title">Penalty Paid</h3>
                            </div>
                            <div class="panel-body">
                                {{$getsetvalue->getsettingskey('currency_symbol')." ".number_format($getloan->loans_total_paid_item('penalty',$_GET['datefrom'],$_GET['dateto']),2)}}
                            </div>
                          </div>
                        </div>

                        <div class="col-md-2 col-lg-2">
                          <div class="panel widget">
                            <div class="panel-heading" style="background-color:#00a65a;color:#fff">
                              <h3 class="panel-title">Fees Paid</h3>
                            </div>
                            <div class="panel-body">
                                {{$getsetvalue->getsettingskey('currency_symbol')." ".number_format($getloan->loans_total_paid_item('fees',$_GET['datefrom'],$_GET['dateto']),2)}}
                            </div>
                          </div>
                        </div>

                        <div class="col-md-4 col-lg-4">
                          <div class="panel widget">
                            <div class="panel-heading" style="background-color:#00a65a;color:#fff">
                              <h3 class="panel-title">Total Paid</h3>
                            </div>
                            <div class="panel-body">
                              {{$getsetvalue->getsettingskey('currency_symbol')." ".number_format($getloan->loans_total_paid($_GET['datefrom'],$_GET['dateto']),2)}}
                            </div>
                          </div>
                        </div>
                      </div>

                      <h4>Monthly </h4>
                      <h3>
                        <span style="color: #ff0000">Due Amount</span>
                          /
                        <span style="color: #00a65a">Paid Amount</span>
                     </h3>

                     <div id="chart" style="width: 100% height:400px;margin-top:20px"></div>

                     @else
                     <div class="alert alert-info">Please Select a date range and click on generate report</div>
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
<script>
    Morris.Bar({
	  element: 'chart',
	  data: [
      {!!$collections!!}
    ],
	  xkey: 'month',
	  ykeys: ['paid','due'],
	  labels: ['paid','due'],
	  barColors: ["#00a65a","#ff0000"]
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