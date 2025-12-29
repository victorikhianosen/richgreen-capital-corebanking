@extends('layout.app')
@section('title')
    Cash Flow Report
@endsection
@section('pagetitle')
Cash Flow Report
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                        <div style="text-align: right">
                      <button type="button" class="btn btn-danger btn-sm" onclick="printsection()"><i class="fa fa-print" aria-hidden="true"></i> Print</button>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-10 col-lg-10 col-sm-12">
                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                      <h3>Cash Flow Report For Period: {{date("d M, Y",strtotime($_GET['datefrom']))." To ".date("d M, Y",strtotime($_GET['dateto']))}}</h3>
                   
                      @endif
                    </div>
                    </div>
                    <div style="margin-bottom: 15px">
                      <form action="{{route('report.cashflow')}}" method="get" onsubmit="thisForm()">
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
                                <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Search Records</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.cashflow')}}'">Reset</button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </form>
                    </div>
                    @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                    <div class="table-responsive"  id="printdiv">
                      <table id="cashflow" border="1" class="table table-bordered table-striped table-condensed table-hover">
                        <thead>
                          <tr style="background-color: #F2F8FF">
                            <th></th>
                            <th style="text-align:right"><b>Balance </b></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="text-primary" colspan="2" rowspan="1"><b><h4>Receipt From Customers</h4></b></td>
                        </tr>
                        <tr>
                            <td><b>Capital</b></td>
                            <td style="text-align:right">{{number_format($capital,2)}}</td>
                        </tr>
                        <tr>
                            <td>
                                <b>Loan Principal Repayment</b>
                            </td>
                            <td style="text-align:right">{{number_format($principal_paid,2)}}</td>
                        </tr>
                        <tr>
                            <td>
                                <b>Loan Interest Repayment</b>
                            </td>
                            <td style="text-align:right">{{number_format($interest_paid,2)}}</td>
                        </tr>
                        <tr>
                            <td>
                                <b>Loan Penalty Repayment</b>
                            </td>
                            <td style="text-align:right">{{number_format($penalty_paid,2)}}</td>
                        </tr>
                        <tr>
                            <td>
                                <b>Loan Fee Repayment</b>
                            </td>
                            <td style="text-align:right">{{number_format($fees_paid,2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Saving Deposit</b></td>
                            <td style="text-align:right">{{number_format($deposits,2)}}</td>
                        </tr>
                         <tr>
                            <td><b>Saving Deposit (Reversed)</b></td>
                            <td style="text-align:right">{{number_format($rev_deposits,2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Fixed Deposit</b></td>
                            <td style="text-align:right">{{number_format($fixed_deposit,2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Fixed Deposit (Reversed)</b></td>
                            <td style="text-align:right">{{number_format($rev_fixed_deposit,2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Investment </b></td>
                            <td style="text-align:right">{{number_format($investment,2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Other Income</b></td>
                            <td style="text-align:right">{{number_format($other_income,2)}}</a></td>
                        </tr>
                        <tr>
                            <td style="border-bottom:1px solid #000000;background-color:#428BCA;color:#fff">
                                <b>Total Receipt (A)</b></td>
                            <td style="text-align:right; border-bottom:1px solid #000000;background-color:#428BCA;color:#fff"
                                class="text-bold">{{number_format($total_receipts,2)}}</td>
                        </tr>
                        <tr>
                            <td class="text-info" colspan="2" rowspan="1"><b><h4>Payment To Customer</h4></b></td>
                        </tr>
                        <tr>
                            <td><b>Expenses</b></td>
                            <td style="text-align:right">{{number_format($expenses,2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Staff Payroll</b></td>
                            <td style="text-align:right">{{number_format($payroll,2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Loan Released Principal</b></td>
                            <td style="text-align:right">{{number_format($principal,2)}}</td>
                        </tr>
                        <tr>
                            <td><b>Saving Withdrawal</b></td>
                            <td style="text-align:right">{{number_format($withdrawals,2)}}</td>
                        </tr>
                         <tr>
                            <td><b>Saving Withdrawal (Reversed)</b></td>
                            <td style="text-align:right"> {{number_format($rev_withdrawals,2)}}</td>
                        </tr>
                        <tr>
                            <td style="border-bottom:1px solid #000000;background-color:#31708F;color:#fff">
                                <b>Total Payment (B)</b></td>
                            <td style="text-align:right;background-color:#31708F;color:#fff">
                                ({{number_format($total_payments,2)}})
                            </td>
                        </tr>
                        <tr>
                            <td style="background-color:#8f3131;color:#fff">
                                <b>Total Cash Balance(A) - (B)</b></td>
                            <td style="text-align:right;background-color:#8f3131;color:#fff"><b>{{number_format($cash_balance,2)}}</b></td>
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
//   $(document).ready(function(){
//     $("#cashflow").dataTable({
//     'pageLength':25,
//     'dom': 'Bfrtip',
//       buttons: [ 'copy', 'csv', 'print','pdf']
//   });
//   });
</script>
<script>
  function printsection() {
    // document.getElementById("noprint").style.display='none';
  var divContents = document.getElementById("printdiv").innerHTML;
  var a = window.open('', '', 'height=500, width=500');
  a.document.write('<html>');
  a.document.write('<body>@if (!empty($_GET["filter"]) && $_GET["filter"] == true)<h3>Cash Flow Report For Period: {{date("d M, Y",strtotime($_GET["datefrom"]))." To ".date("d M, Y",strtotime($_GET["dateto"]))}}</h3> @endif');
  a.document.write(divContents);
  a.document.write('</body></html>');
  a.document.close();
  a.print();
  }
</script>
@endsection