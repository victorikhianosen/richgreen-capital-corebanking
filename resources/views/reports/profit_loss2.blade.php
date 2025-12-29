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
    @inject('getdata', 'App\Http\Controllers\ReportsController')

  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: right">
                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                      <button type="button" class="btn btn-info btn-sm" onclick="exporttoexcel()"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export To Excel</button>
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
                        <input type="hidden" name="prfltype" value="2">
                        <table class="table table-bordered table-hover table-sm">
                          <thead>
                            <tr>
                              <th>From Date</th>
                              <th>To Date</th>
                              <th>Display Zero Balance</th>
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
                                     <select name="zbalance" class="form-control" autocomplete="off">
                                          <option value="0" selected>No</option>
                                         <option value="1" {{!empty($_GET['zbalance']) && $_GET['zbalance'] == "1" ? "selected" : ''}}>Yes</option>
                                     </select>
                                </div>
                              </td>
                              <td>
                                <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Generate Report</button>
                                <button type="button" class="btn btn-info btn-sm" onclick="toggleForeignCurrency()">Apply Foriegn Currency</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.profitloss')}}?prfltype=2'">Reset</button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                           @php
                            $shwry = !empty($_GET['dollar']) || !empty($_GET['pounds']) || !empty($_GET['euro']) || !empty($_GET['Caddollar']) ? 'block' : 'none';
                        $filtercuury = !empty($_GET['dollar']) || !empty($_GET['pounds']) || !empty($_GET['euro']) || !empty($_GET['Caddollar']) ? '1' : '0';
                      @endphp
                        <div class="row" style="display: {{$shwry}}" id="shwfcurry">
                          <div class="col-md-3 col-lg-3 col-sm-12">
                            <div class="form-group">
                               <label for="dollar">Dollar Rate</label>
                               <input type="number" name="dollar" id="dollar" autocomplete="off" class="form-control" value="{{!empty($_GET['dollar']) ? $_GET['dollar'] : ''}}" placeholder="Dollar Rate">
                            </div>
                          </div>
                          <div class="col-md-3 col-lg-3 col-sm-12">
                            <div class="form-group">
                               <label for="pounds">Pounds Rate</label>
                               <input type="number" name="pounds" id="pounds" autocomplete="off" class="form-control" value="{{!empty($_GET['pounds']) ? $_GET['pounds'] : ''}}" placeholder="Pounds Rate">
                            </div>
                          </div>
                          <div class="col-md-3 col-lg-3 col-sm-12">
                            <div class="form-group">
                               <label for="euro">Euro Rate</label>
                               <input type="number" name="euro" id="euro" autocomplete="off" class="form-control" value="{{!empty($_GET['euro']) ? $_GET['euro'] : ''}}" placeholder="Euro Rate">
                            </div>
                          </div>
                          <div class="col-md-3 col-lg-3 col-sm-12">
                            <div class="form-group">
                               <label for="caddollar">Canadian Dollar Rate</label>
                               <input type="number" name="Caddollar" id="caddollar" autocomplete="off" class="form-control" value="{{!empty($_GET['Caddollar']) ? $_GET['Caddollar'] : ''}}" placeholder="Canadian Dollar Rate">
                            </div>
                          </div>
                        </div>
                      </form>
                    </div>
                    <div id="printdiv">
                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                     
                          <table id="profloss" class="table table-bordered table-hover" style="background: #FFF;">
                            <thead>
                                <tr>
                                    <th><b>Description</b></th>
                                    <th><b>GL Code</b></th>
                                    <th><b>Amount({{$getsetvalue->getsettingskey('currency_symbol')}})</b></th>
                                </tr>
                            </thead>
                              <tbody>
                                <?php 
                                $currencyRates = [
                                    '1' => floatval(request()->get('dollar', 0)),
                                    '2' => floatval(request()->get('pounds', 0)),
                                    '3' => floatval(request()->get('euro', 0)),
                                    '4' => floatval(request()->get('Caddollar', 0)),
                                ];

                                $inaccate = DB::table('account_categories')->where('type','income')->get();
                                $expaccate = DB::table('account_categories')->where('type','expense')->get();
                                ?>
                                <tr>
                                    <td><b>INCOME</b></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                             
                                           <?php
                                            $inbal = 0;
                                            $expbal = 0;
                                            $tbal = 0;
                                            ?>
                                            @foreach ($inaccate as $item)
                                           
                                            <?php 
                                            if(request()->zbalance == '1'){
                                                $incgls = DB::table('general_ledgers')->select('id','gl_code','gl_name','currency_id')->where('account_category_id',$item->id)
                                                                                        ->where('status','1')
                                                                                      ->get();
                                            }else{
                                               $incgls = DB::table('general_ledgers')->select('id','gl_code','gl_name','currency_id')->where('account_category_id',$item->id)
                                                                                    ->where('status','1')
                                                                                    ->where('account_balance','!=','0')->get();
                                            }
                                            ?>

                                            @if (count($incgls) > 0)
                                                 <tr style="padding:10px">
                                                <td class="text-primary" style="font-weight:bold; margin-top:5px;margin-bottom:10px; padding:8px">{{ucwords($item->name)}}</td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                            @endif

                                             @foreach ($incgls as $glitem)
                                               <tr>
                                                <td><span style="margin-left:15px">{{ucwords($glitem->gl_name)}}</span> </td>
                                                <td>{{$glitem->gl_code}}</td>
                                                <td align="right">
                                                    <?php 
                                                        $incrtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glitem->id)
                                                                                                            ->where('type','credit')
                                                                                                            ->where('status','approved')
                                                                                                         ->whereBetween('created_at',[request()->datefrom, request()->dateto])
                                                                                                         ->sum('amount');
                                                                                                         
                                                        $indbtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glitem->id)
                                                                                                          ->where('type','debit')
                                                                                                          ->where('status','approved')
                                                                                                         ->whereBetween('created_at',[request()->datefrom, request()->dateto])
                                                                                                         ->sum('amount');
                                                      //  $intrnx = $incrtrnx - $indbtrnx;
                                                      // $inbal += $intrnx;
                                                     $currencyid = $glitem->currency_id ?: '';
                                                       // $currency = $getdata->GetcurrencyExchge($currencyid);
                                                        $rate = !empty($currencyid) ? $currencyRates[$currencyid] : 1;

                                                        $intrtrnx = $incrtrnx - $indbtrnx;
                                                        $convertedIncamt = $filtercuury == '1' ? $intrtrnx * $rate : $intrtrnx;
                                                         $inbal += $convertedIncamt;
                                                    ?>
                                                    <span style="text-align:right"><b>{{number_format($convertedIncamt,2)}}</b></span>
                                                </td>
                                              </tr>
                                             @endforeach
                                            @endforeach
                                            <tr class="bg-primary" style="font-weight:bold; margin-top:20px">
                                            <td>Total Income</td>
                                            <td></td>
                                            <td align="right"><b style="font-weight:bold;text-align:right">{{number_format($inbal,2)}}</b></td>
                                          </tr>
                                   
                                            <tr>
                                                <td><b>EXPENSES</b> </td>
                                                <td></td>
                                                <td></td>
                                        </tr>
                                
                                            @foreach ($expaccate as $item)
                                           
                                          <?php 
                                          if(request()->zbalance == '1'){
                                              $exacco = DB::table('general_ledgers')->select('id','gl_code','gl_name','currency_id')
                                                                                    ->where('status','1')
                                                                                    ->where('account_category_id',$item->id)
                                                                                    ->get();
                                          }else{
                                             $exacco = DB::table('general_ledgers')->select('id','gl_code','gl_name','currency_id')
                                                                                  ->where('account_category_id',$item->id)
                                                                                    ->where('status','1')
                                                                                  ->where('account_balance','!=','0')->get();
                                          }
                                          ?>

                                          @if (count($exacco) > 0)
                                               <tr>
                                                <td class="text-danger" style="font-weight:bold; margin-top:5px;margin-bottom:10px; padding:8px">{{ucwords($item->name)}}</td>
                                             <td></td>
                                             <td></td>
                                            </tr>
                                          @endif

                                             @foreach ($exacco as $glexpitem)
                                             <tr>
                                                <td><span style="margin-left:15px">{{ucwords($glexpitem->gl_name)}}</span></td>
                                                <td>{{$glexpitem->gl_code}}</td>
                                                <td align="right">
                                                    <?php 
                                                        $expcrtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glexpitem->id)
                                                                                                            ->where('type','credit')
                                                                                                            ->where('status','approved')
                                                                                                         ->whereBetween('created_at',[request()->datefrom, request()->dateto])
                                                                                                         ->sum('amount');

                                                        $expdbtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glexpitem->id)
                                                                                                            ->where('type','debit')
                                                                                                            ->where('status','approved')
                                                                                                         ->whereBetween('created_at',[request()->datefrom, request()->dateto])
                                                                                                         ->sum('amount');
                                                    
                                                          $currencyid = $glexpitem->currency_id ?: '';
                                                        //$currency = $getdata->GetcurrencyExchge($currencyid);
                                                        $rate = !empty($currencyid) ? $currencyRates[$currencyid] : 1;

                                                            $exptrnx = $expdbtrnx - $expcrtrnx;
                                                            $covertedExpamt = $filtercuury == '1' ? $exptrnx * $rate : $exptrnx;
                                                           $expbal += $covertedExpamt;
                                                    ?>
                                                   <span style="text-align:right"> <b>{{number_format($covertedExpamt,2)}}</b></span>
                                                    
                                                </td>
                                            </tr>
                                             @endforeach
                                            @endforeach
                                            <tr class="bg-danger" style="font-weight:bold">
                                            <td>Total Expenses</td>
                                            <td></td>
                                            <td align="right"><b style="font-weight:bold;text-align:right">{{number_format($expbal,2)}}</b></td>
                                          </tr>
                                  
                              
                              <tr style="background-color:#000000; color:#fff">
                                <?php 
                                  $tbal = (float)$inbal - (float)$expbal;
                                 ?>
                                <td>Surplus / Deficit</td>
                                <td></td>
                                <td align="right"><b style="font-weight:bold;text-align:right">{{number_format($tbal,2)}}</b></td>
                              </tr>
                              </tbody>
                          </table>
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
  
  function exporttoexcel(){
      $("#profloss").table2excel({
    exclude: ".excludeThisClass",
    name: "Profit_And_Loss_Report",
    filename: "Profit_And_Loss_Report.xls", // do include extension
    preserveColors: false // set to true if you want background colors and font colors preserved
});
  }
</script>
 <script>
    function toggleForeignCurrency() {
        const el = document.getElementById('shwfcurry');
        el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
    }
</script>
@endsection