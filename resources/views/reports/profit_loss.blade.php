@extends('layout.app')
@section('title')
    Profit / Loss
@endsection
@section('pagetitle')
Profit / Loss
@endsection
@section('content')
<?php
$getsetvalue = new \App\Models\Setting();
?>
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: right">
                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                      <button type="button" class="btn btn-danger btn-sm" onclick="printsection()"><i class="fa fa-print" aria-hidden="true"></i> Print</button>
                      @endif
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-10 col-lg-10 col-sm-12">
                        @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                        <h3>Profit / Loss for period: {{date("d M, Y",strtotime($_GET['datefrom']))." To ".date("d M, Y",strtotime($_GET['dateto']))}}</h3>
                        @endif
                      </div>
                      </div>
                    <div  style="margin-bottom: 15px">
                      <form action="{{route('report.profitloss')}}" method="get" onsubmit="thisForm()">
                        <input type="hidden" name="filter" value="true">
                        <input type="hidden" name="prfltype" value="1">
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
                                <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Generate Report</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.profitloss')}}?prfltype=1'">Reset</button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </form>
                    </div>
                    <div id="printdiv">
                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                      <div class="row">
                        <div class="col-md-4 col-lg-4 col-sm-12">
                          <table id="profitloss" class="table table-bordered table-hover " style="background: #FFF;">
                              <tbody>
                              <tr style="background: #CCC;">
                                  <td style="font-weight:bold">Profit / Loss Statement</td>
                                  <td align="right" style="font-weight:bold">Balance</td>
                              </tr>
                              <tr><td></td></tr>
                              <tr class="bg-primary">
                                  <td style="font-weight:bold" colspan="2">Operating Profit (P)</td>
                                  {{-- <td style="font-weight:bold"></td> --}}
                              </tr>
                              <tr>
                                  <td>Loan Interest Repayment </td>
                                  <td align="right">{{number_format($interest_paid,2)}}</td>
                              </tr>
                              <tr>
                                  <td>Loan Fee Repayment</td>
                                  <td align="right">{{number_format($fees_paid,2)}}</td>
                              </tr>
                              <tr>
                                  <td>Loan Penalty Repayment</td>
                                  <td align="right">{{number_format($penalty_paid,2)}}</td>
                              </tr>
                             <tr>
                                  <td>Bank Fee/Charges</td>
                                  <td align="right">{{number_format($bank_fees,2)}}</td>
                              </tr>
                               <tr>
                                  <td>Form Fee</td>
                                  <td align="right">{{number_format($form_fees,2)}}</td>
                              </tr>
                               <tr>
                                  <td>Processing Fee</td>
                                  <td align="right">{{number_format($process_fees,2)}} </td>
                              </tr> 
                              <tr>
                                  <td>Esusu Charges</td>
                                  <td align="right">{{number_format($esusu,2)}}</a> </td>
                              </tr>
                              <tr>
                                  <td>Monthly Charge</td>
                              <td align="right">{{number_format($monthly_charge,2)}}</td>
                              </tr>
                               <tr>
                                  <td>Transfer Charge</td>
                              <td align="right">{{number_format($transfer_charge,2)}}</a> </td>
                              </tr>
                              <tr>
                                  <td>Other Income</td>
                                  <td align="right">{{number_format($other_income,2)}} </td>
                              </tr>
                              <tr class="bg-primary">
                                  <td><b>Total Operating Profit</b></td>
                                  <td align="right"><b>{{number_format($operating_profit,2)}}</b></td>
                              </tr>
                              <tr><td></td></tr>
              
                              <tr class="bg-danger">
                                  <th colspan="2">Operating Expense (E)</th>
                              </tr>
                              <tr>
                                  <td>Staff Payroll</td>
                                  <td align="right">{{number_format($payroll,2)}}</td>
                              </tr>
                              <tr>
                                  <td>Expense</td>
                                  <td align="right">{{number_format($expenses,2)}}</td>
                              </tr>
                               <tr>
                                  <td>Saving Interest *</td>
                                  <td align="right">{{number_format($other_expenses,2)}}</td>
                              </tr>
                               <tr>
                                  <td>Fixed Deposit Interest *</td>
                                  <td align="right">{{number_format($fd_interest_expense,2)}}</td>
                              </tr>
                              <tr>
                                  <td>Investment Interest *</td>
                                  <td align="right">{{number_format($inv_interest_expense,2)}}</td>
                              </tr>
                               <tr class="bg-danger">
                                  <td><b>Total Operating Expenses</b></td>
                                  <td align="right"><b>{{number_format($operating_expenses,2)}}</b></td>
                              </tr>
                              <tr><td></td></tr>
              
                               <tr class="bg-green">
                                  <td style="font-weight:bold">Gross Profit (G) = P - E</td>
                                  <td style="font-weight:bold" align="right">{{number_format($gross_profit,2)}}</td>
                              </tr>
                              <tr><td></td></tr>
                              <tr class="bg-danger">
                                  <td style="font-weight:bold">Other Expense (O)</td>
                                  <td></td>
                              </tr>
              
                              <tr>
                                  <td>Default Loan *(Written Off Loan)</td>
                                  <td align="right">{{number_format($loan_default,2)}}</td>
                              </tr>
                               <tr class="bg-danger">
                                  <td><b>Total Other Expenses</b></td>
                                  <td align="right"><b>{{number_format($loan_default,2)}}</b></td>
                              </tr>
                              <tr><td></td></tr>
                              <tr><td></td></tr>
                              <tr class="bg-purple">
                                  <td style="font-weight:bold">Net Income({{$getsetvalue->getsettingskey('currency_symbol')}}) = G - O
                                  </td>
                                  <td style="font-weight:bold" align="right">{{number_format($net_profit,2)}}</td>
                              </tr>
                              </tbody>
                          </table>
                          <p><b>Default Loans *</b> is loans(principal amount - repayments made) that have been marked as default.</p>
                      </div>

                      <div class="col-md-8 col-lg-8 col-sm-12">
                        <!-- AREA CHART -->
                        <!-- LINE CHART -->
                        <div class="panel widget">
                            <div class="panel-heading vd_bg-grey">
                                <h3 class="panel-title">Monthly Net Income</h3>
            
                            </div>
                            <div class="panel-body">
                                <div id="netIncomeChart" style="height: 250px;">
                                </div>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->
                        <div class="panel widget">
                            <div class="panel-heading vd_bg-grey">
                                <h3 class="panel-title">
                                  Operating Profit/ Operating Expense
                                </h3>
                            </div>
                            <div class="panel-body">
                                <div class="chart" id="operatingProfit" style="height: 350px;">
                                </div>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->
            
                        <!-- LINE CHART -->
                        <div class="panel widget">
                            <div class="panel-heading vd_bg-grey">
                                <h3 class="panel-title">Other Expense</h3>
                            </div>
                            <div class="panel-body">
                                <div class="chart" id="otherExpensesChart" style="height: 250px;">
                                </div>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->
                    </div>
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
<script>
  Morris.Bar({
  element: 'netIncomeChart',
  data: [
    {!! empty($incomedata) ? '' : $incomedata !!}
  ],
  xkey: 'month',
  ykeys: ['amount'],
  labels: ['amount'],
  barColors: ["#112f94","#ff0000"]
});
</script>
<script>
Morris.Bar({
  element: 'operatingProfit',
  data: [
    {!! empty($operating_profit_data) ? '' : $operating_profit_data!!}
  ],
  xkey: 'month',
  ykeys: ['profit','expenses'],
  labels: ['profit','expenses'],
  barColors: ["#00a65a","#000000"]
});
</script>
<script>
Morris.Bar({
  element: 'otherExpensesChart',
  data: [
    {!! empty($other_expenses_data) ? '' : $other_expenses_data!!}
  ],
  xkey: 'month',
  ykeys: ['expenses'],
  labels: ['expenses'],
  barColors: ["#119485","#ff0000"]
});
</script>
<script>
   function printsection() {
    //document.getElementById("noprint").style.display='none';
  var divContents = document.getElementById("printdiv").innerHTML;
  var a = window.open('', '', 'height=500, width=500');
  a.document.write('<html>');
  a.document.write('<body>  @if (!empty($_GET["filter"]) && $_GET["filter"] == true)<h3>Profit / Loss for period: {{date("d M, Y",strtotime($_GET["datefrom"]))." To ".date("d M, Y",strtotime($_GET["dateto"]))}}</h3>@endif');
  a.document.write(divContents);
  a.document.write('</body></html>');
  a.document.close();
  a.print();
  }

  
</script>
@endsection