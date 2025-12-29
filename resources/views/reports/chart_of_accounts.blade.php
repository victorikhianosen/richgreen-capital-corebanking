@extends('layout.app')
@section('title')
    Chart Of Accounts Report
@endsection
@section('pagetitle')
Chart Of Accounts Report
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
                  <div class="panel-body" id="printdiv">
                    <div id="noprint" style="margin-bottom: 15px">
                        <form action="{{route('report.chartaccounts')}}" method="get" onsubmit="thisForm()">
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
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Generate Report</button>
                                  <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.chartaccounts')}}'">Reset</button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </form>
                      </div>
                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                      <div class="text-right" id="noprint">
                        <button type="button" class="btn btn-danger btn-sm" onclick="printsection()"><i class="fa fa-print" aria-hidden="true"></i> Print</button>                      </div>
                      <div class="text-center">
                        <h3><img src="{{asset($getsetvalue->getsettingskey('company_logo'))}}" width="120" alt="logo"></h3>
                        <p><b>{{ucwords($getsetvalue->getsettingskey('company_name'))}}</b></p>
                        <p>Chart Of Accounts Report</p>
                      </div>
                      <div class="box-body table-responsive no-padding">
                        <table  class="table table-bordered table-striped table-condensed table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>GL Code</th>
                                     <th>Description</th>
                                    <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totexp=0;
                                    $totlib=0;
                                    $totincom=0;
                                    $totcap=0;
                                     $totasset=0;
                                    ?>
                                     @if (count($data) > 0)
                                     <tr>
                                        <td><b>1000000</b></td>
                                        <td><b>Asset</b></td>
                                        <td></td>
                                    </tr>
                                        @foreach ($data as $item)
                                        
                                        <tr>
                                            <td>{{$item->gl_code}}</td> 
                                            <td>{{ ucwords($item->gl_name)}}</td> 
                                            <td>
                                                <?php 
                                                $debit = \App\Models\SavingsTransactionGL::where('general_ledger_id',$item->id)->where('type','debit')->sum('amount');
                                                $credit = \App\Models\SavingsTransactionGL::where('general_ledger_id',$item->id)->where('type','credit')->sum('amount');
                                                $eval = (int)$debit - (int)$credit;
                                                $totasset += $eval;?>
                                                <b>{{number_format($eval, 2)}}</b>
                                            </td> 
                                         </tr> 
                                        @endforeach 
                                    <tr>
                                        <th></th>
                                        <th><b>Total Asset</b></th>
                                        <th><b>{{number_format($totasset, 2)}}</b></th>
                                    </tr> 
                                     @endif

                                     @if (count($datalib)>0)
                                     <tr>
                                        <td><b>2000000</b></td>
                                        <td><b>Liability</b></td>
                                        <td></td>
                                    </tr>
                                        @foreach ($datalib as $item)
                                        
                                        <tr>
                                            <td>{{$item->gl_code}}</td> 
                                            <td>{{ ucwords($item->gl_name)}}</td> 
                                            <td>
                                                <?php 
                                                $debit = \App\Models\SavingsTransactionGL::where('general_ledger_id',$item->id)->where('type','debit')->sum('amount');
                                                $credit = \App\Models\SavingsTransactionGL::where('general_ledger_id',$item->id)->where('type','credit')->sum('amount');
                                                $eval = (int)$credit - (int)$debit;
                                                $totlib += $eval;?>
                                                <b>{{number_format($eval, 2)}}</b>
                                            </td> 
                                         </tr> 
                                        @endforeach 
                                    <tr>
                                        <th></th>
                                        <th><b>Total Liability</b></th>
                                        <th><b>{{number_format($totlib, 2)}}</b></th>
                                    </tr> 
                                     @endif
                                    
                                     @if (count($datacap)>0)
                                     <tr>
                                        <td><b>3000000</b></td>
                                        <td><b>Capital</b></td>
                                        <td></td>
                                    </tr>
                                        @foreach ($datacap as $item)
                                        
                                        <tr>
                                            <td>{{$item->gl_code}}</td> 
                                            <td>{{ ucwords($item->gl_name)}}</td> 
                                            <td>
                                               <?php 
                                                $debit = \App\Models\SavingsTransactionGL::where('general_ledger_id',$item->id)->where('type','debit')->sum('amount');
                                                $credit = \App\Models\SavingsTransactionGL::where('general_ledger_id',$item->id)->where('type','credit')->sum('amount');
                                                $eval = (int)$credit - (int)$debit;
                                                $totcap += $eval;?>
                                                <b>{{number_format($eval, 2)}}</b>
                                            </td> 
                                         </tr> 
                                        @endforeach 
                                    <tr>
                                        <th></th>
                                        <th><b>Total Capital</b></th>
                                        <th><b>{{number_format($totcap, 2)}}</b></th>
                                    </tr> 
                                     @endif

                                     @if (count($dataincom)>0)
                                     <tr>
                                        <td><b>4000000</b></td>
                                        <td><b>Income</b></td>
                                        <td></td>
                                    </tr>
                                        @foreach ($dataincom as $item)
                                        
                                        <tr>
                                            <td>{{$item->gl_code}}</td> 
                                            <td>{{ ucwords($item->gl_name)}}</td> 
                                            <td>
                                                <?php 
                                                //$debit = \App\Models\SavingsTransactionGL::where('general_ledger_id',$item->id)->where('type','debit')->sum('amount');
                                                $credit = \App\Models\SavingsTransactionGL::where('general_ledger_id',$item->id)->where('type','credit')->sum('amount');
                                               
                                                $totincom += $credit;?>
                                                <b>{{number_format($credit, 2)}}</b>
                                            </td> 
                                         </tr> 
                                        @endforeach 
                                    <tr>
                                        <th></th>
                                        <th><b>Total Income</b></th>
                                        <th><b>{{number_format($totincom, 2)}}</b></th>
                                    </tr> 
                                     @endif

                                     @if (count($dataexp)>0)
                                     <tr>
                                        <td><b>5000000</b></td>
                                        <td><b>Expense</b></td>
                                        <td></td>
                                    </tr>
                                        @foreach ($dataexp as $item)
                                        
                                        <tr>
                                            <td>{{$item->gl_code}}</td> 
                                            <td>{{ ucwords($item->gl_name)}}</td> 
                                            <td>
                                              <?php 
                                                $debit = \App\Models\SavingsTransactionGL::where('general_ledger_id',$item->id)->where('type','debit')->sum('amount');
                                               // $credit = \App\Models\SavingsTransactionGL::where('general_ledger_id',$item->id)->where('type','credit')->sum('amount');
                                                //$eval = (int)$credit - (int)$debit;
                                                $totexp += $debit;?>
                                                <b>{{number_format($debit, 2)}}</b>
                                            </td> 
                                         </tr> 
                                        @endforeach 
                                    <tr>
                                        <th></th>
                                        <th><b>Total Expense</b></th>
                                        <th><b>{{number_format($totexp, 2)}}</b></th>
                                    </tr> 
                                     @endif
                                    </tbody>
                        </table>
            
                    </div>
                      @else
                      <div class="alert alert-info">Please select a date range then click on generate report button</div>
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
    $("#custmer").dataTable({
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