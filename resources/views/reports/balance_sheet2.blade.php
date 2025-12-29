@extends('layout.app')
@section('title')
    Balance Sheet
@endsection
@section('pagetitle')
Balance Sheet
@endsection
@section('content')
  <div class="container" style="@media print{width:100% !important;}">
    <?php
    $getsetvalue = new \App\Models\Setting();
   ?>
    @inject('getdata', 'App\Http\Controllers\ReportsController')

    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                           {{-- <a href="{{route('branch.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Branch</a> --}}
                        </div>
                      </div>
                  <div class="panel-body">
                    <div class="noprint" style="margin-bottom: 15px">
                      <form action="{{route('report.balancesheet')}}" method="get" onsubmit="thisForm()">
                        <input type="hidden" name="filter" value="true">
                        <input type="hidden" name="bsheettyp" value="2">
                        <table class="table table-bordered table-hover table-sm">
                          <thead>
                            <tr>
                              {{-- <th>From Date</th> --}}
                              <th>Date</th>
                              <th>Display Zero Balance</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              {{-- <td>
                                <div class="form-group">
                                  <input type="date" name="datefrom" required id="" class="form-control" value="{{!empty($_GET['datefrom']) ? $_GET['datefrom'] : ''}}">
                                </div>
                              </td> --}}
                              <td>
                                <div class="form-group">
                                  <input type="date" name="dateto" required id="" class="form-control" value="{{!empty($_GET['dateto']) ? $_GET['dateto'] : date('Y-m-d')}}">
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
                                <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.balancesheet')}}?bsheettyp=2'">Reset</button>
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
                    
                      <div class="text-right noprint">
                        <button type="button" class="btn btn-info btn-sm" onclick="exporttoexcel()"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export To Excel</button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="printsection()"><i class="fa fa-print" aria-hidden="true"></i> Print</button>
                      </div>
                      <div id="printdiv">
                        <div class="text-center">
                          <h3><strong>{{ucwords("balance sheet report")}}</strong></h3>
                          <p><b>{{ucwords($getsetvalue->getsettingskey('company_name'))}}</b></p>

                          @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                          <p><b>As At: {{date("d M, Y",strtotime($_GET['dateto']))}}</b></p>
                          @endif
                       
                          <p><b>Generated On {{date('d M, Y')." at ".date('h:i:s')}}</b></p>
                        </div>
                        <div class="table-responsive">
                          <table id="balansheet" class="table table-striped table-bordered table-condensed" style="width: 100%">
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


                                $ataccate = DB::table('account_categories')->where('type','asset')->get();
                                $libaccate = DB::table('account_categories')->where('type','liability')->get();
                                $cpaccate = DB::table('account_categories')->where('type','capital')->get();
                                
                                 $inaccate = DB::table('account_categories')->where('type','income')->get();
                                $expaccate = DB::table('account_categories')->where('type','expense')->get();
                                
                                $asstbal = 0;
                                $libal = 0;
                                $capbal = 0;
                                $tbal = 0;
                                
                                 $expbal = 0; 
                                $intrbal = 0;

                                $firsttrnx = DB::table('savings_transaction_g_l_s')->orderBy('created_at','asc')->first();
                                $datefrom = $firsttrnx->created_at ?? date('Y-m-d');
                                ?>  
                                <tr>
                                    <td><b>ASSETS</b></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                            @foreach ($ataccate as $item)
                                            
                                            
                                            <?php 
                                            if(request()->zbalance == '1'){
                                                $asstgls = DB::table('general_ledgers')->select('id','gl_name','gl_code','currency_id')->where('account_category_id',$item->id)
                                                                                        ->where('status','1')
                                                                                      ->get();
                                            }else{
                                               $asstgls = DB::table('general_ledgers')->select('id','gl_name','gl_code','currency_id')->where('account_category_id',$item->id)
                                                                                    ->where('status','1')
                                                                                    ->where('account_balance','!=','0')->get();
                                            }
                                            ?>

                                            @if (count($asstgls) > 0)
                                                <tr style="padding:10px">
                                                <td class="text-primary" style="font-weight:bold; margin-top:5px;margin-bottom:10px; padding:8px">{{ucwords($item->name)}}</td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                            @endif

                                       @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                                            
                                              @foreach ($asstgls as $glitem)
                                               <tr>
                                                <td><span style="margin-left:15px">{{ucwords($glitem->gl_name)}}</span> </td>
                                                <td><span style="margin-left:15px">{{ucwords($glitem->gl_code)}}</span> </td>
                                                <td align="right">
                                                    <?php 
                                                        $ascrtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glitem->id)
                                                                                                             ->where('type','credit')
                                                                                                             ->where('status','approved')
                                                                                                         ->whereBetween('created_at',[$datefrom, request()->dateto])
                                                                                                         ->sum('amount');
                                                                                                         
                                                        $asdbtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glitem->id)
                                                                                                             ->where('type','debit')
                                                                                                             ->where('status','approved')
                                                                                                         ->whereBetween('created_at',[$datefrom, request()->dateto])
                                                                                                         ->sum('amount');

                                                         $currencyid = $glitem->currency_id ?: '';
                                                        //$currency = $getdata->GetcurrencyExchge($currencyid);
                                                        $rate = !empty($currencyid) ? $currencyRates[$currencyid] : 1;

                                                        $astrnx = $asdbtrnx - $ascrtrnx;
                                                        $convertedAsstamt = $filtercuury == '1' ? $astrnx * $rate : $astrnx;
                                                      $asstbal += $convertedAsstamt;
                                                    ?>
                                                    <span style="text-align:right"><b>{{number_format($convertedAsstamt,2)}}</b></span>
                                                </td>
                                              </tr>
                                             @endforeach

                                             @else

                                             @foreach ($asstgls as $glitem)
                                               <tr>
                                                <td><span style="margin-left:15px">{{ucwords($glitem->gl_name)}}</span> </td>
                                                <td><span style="margin-left:15px">{{ucwords($glitem->gl_code)}}</span> </td>
                                                <td align="right">
                                                    <?php 
                                                        $ascrttrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glitem->id)
                                                                                                             ->where('type','credit')
                                                                                                             ->where('status','approved')
                                                                                                         ->sum('amount');
                                                                                                         
                                                        $asdbttrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glitem->id)
                                                                                                             ->where('type','debit')
                                                                                                             ->where('status','approved')
                                                                                                         ->sum('amount');
                                                        $currencyid = $glitem->currency_id ?: '';
                                                        //$currency = $getdata->GetcurrencyExchge($currencyid);
                                                        $rate = !empty($currencyid) ? $currencyRates[$currencyid] : 1;

                                                        $astrnx = $asdbttrnx - $ascrttrnx;
                                                        $convertedAsstamt = $filtercuury == '1' ? $astrnx * $rate : $astrnx;
                                                      $asstbal += $convertedAsstamt;
                                                    ?>
                                                    <span style="text-align:right"><b>{{number_format($convertedAsstamt,2)}}</b></span>
                                                </td>
                                              </tr>
                                             @endforeach

                                             @endif

                                            @endforeach
                                            <tr>
                                            <td><h5 style="font-weight:bold;text-align:left">Total Assets</h5></td>
                                            <td></td>
                                            <td align="right"><b style="font-weight:bold;text-align:right">{{number_format($asstbal,2)}}</b></td>
                                          </tr>
                                   
                                          <tr>
                                            <td><b>LIABILITIES</b></td>
                                            <td></td>
                                            <td></td>
                                          </tr>
                                                    @foreach ($libaccate as $item)
                                                   
                                                    <?php 
                                                    if(request()->zbalance == '1'){
                                                        $ligls = DB::table('general_ledgers')->select('id','gl_name','gl_code','currency_id')->where('account_category_id',$item->id)
                                                                                                ->where('status','1')
                                                                                              ->get();
                                                    }else{
                                                       $ligls = DB::table('general_ledgers')->select('id','gl_name','gl_code','currency_id')->where('account_category_id',$item->id)
                                                                                            ->where('status','1')
                                                                                            ->where('account_balance','!=','0')->get();
                                                    }
                                                    ?>

                                                    @if (count($ligls) > 0)
                                                         <tr style="padding:10px">
                                                        <td class="text-warning" style="font-weight:bold; margin-top:5px;margin-bottom:10px; padding:8px">{{ucwords($item->name)}}</td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                    @endif

                                                  @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                                                    
                                                       @foreach ($ligls as $gllbitem)
                                                       <tr>
                                                        <td><span style="margin-left:15px">{{ucwords($gllbitem->gl_name)}}</span> </td>
                                                        <td><span style="margin-left:15px">{{ucwords($gllbitem->gl_code)}}</span> </td>
                                                        <td align="right">
                                                            <?php 
                                                                $lbcrtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$gllbitem->id)
                                                                                                                     ->where('type','credit')
                                                                                                                     ->where('status','approved')
                                                                                                                 ->whereBetween('created_at',[$datefrom, request()->dateto])
                                                                                                                 ->sum('amount');
                                                                                                                 
                                                                $lbdbtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$gllbitem->id)
                                                                                                                     ->where('type','debit')
                                                                                                                     ->where('status','approved')
                                                                                                                 ->whereBetween('created_at',[$datefrom, request()->dateto])
                                                                                                                 ->sum('amount');
                                                         $currencyid = $gllbitem->currency_id ?: '';
                                                        // $currency = $getdata->GetcurrencyExchge($currencyid);
                                                        $rate = !empty($currencyid) ? $currencyRates[$currencyid] : 1;

                                                            $lbtrnx = $lbcrtrnx - $lbdbtrnx;
                                                            $convertedLbamt = $filtercuury == '1' ? $lbtrnx * $rate : $lbtrnx;
                                                            $libal += $convertedLbamt;
                                                            ?>
                                                            <span style="text-align:right"><b>{{number_format($convertedLbamt,2)}}</b></span>
                                                        </td>
                                                      </tr>
                                                     @endforeach

                                                     @else

                                                     @foreach ($ligls as $gllbitem)
                                                     <tr>
                                                      <td><span style="margin-left:15px">{{ucwords($gllbitem->gl_name)}}</span> </td>
                                                      <td><span style="margin-left:15px">{{ucwords($gllbitem->gl_code)}}</span> </td>
                                                      <td align="right">
                                                          <?php 
                                                              $lbcrtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$gllbitem->id)
                                                                                                                   ->where('type','credit')
                                                                                                                   ->where('status','approved')
                                                                                                               ->sum('amount');
                                                                                                               
                                                              $lbdbtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$gllbitem->id)
                                                                                                                   ->where('type','debit')
                                                                                                                   ->where('status','approved')
                                                                                                               ->sum('amount');


                                                        $currencyid = $gllbitem->currency_id ?: '';
                                                        // $currency = $getdata->GetcurrencyExchge($currencyid);
                                                        $rate = !empty($currencyid) ? $currencyRates[$currencyid] : 1;

                                                          $lbtrnx = $lbcrtrnx - $lbdbtrnx;
                                                          $convertedLbamt = $filtercuury == '1' ? $lbtrnx * $rate : $lbtrnx;
                                                          $libal += $convertedLbamt;
                                                          ?>
                                                          <span style="text-align:right"><b>{{number_format($convertedLbamt,2)}}</b></span>
                                                      </td>
                                                    </tr>
                                                   @endforeach

                                                     @endif
                                                    @endforeach
                                                    <tr>
                                                    <td><h5 style="font-weight:bold;text-align:left">Total Liabilities</h5></td>
                                                    <td></td>
                                                    <td align="right"><b style="font-weight:bold;text-align:right">{{number_format($libal,2)}}</b></td>
                                                  </tr>

                                                  <tr>
                                                    <?php 
                                                      $tbal = (float)$asstbal - (float)$libal;
                                                     ?>
                                                    <td><h4 style="font-weight:bold;text-align:left">Net Assets</h4></td>
                                                    <td></td>
                                                    <td align="right"><b style="font-weight:bold;text-align:right">{{number_format($tbal,2)}}</b></td>
                                                  </tr>

                                            <tr>
                                                <td><b>CAPITAL</b> </td>
                                                <td></td>
                                                <td></td>
                                             </tr>
                                
                                            @foreach ($cpaccate as $item)
                                           
                                          <?php 
                                          if(request()->zbalance == '1'){
                                              $cpacco = DB::table('general_ledgers')->select('id','gl_name','gl_code','currency_id')->where('status','1')->where('account_category_id',$item->id)
                                                                                    ->get();
                                          }else{
                                             $cpacco = DB::table('general_ledgers')->select('id','gl_name','gl_code','currency_id')->where('account_category_id',$item->id)
                                                                                    ->where('status','1')
                                                                                  ->where('account_balance','!=','0')->get();
                                          }
                                          ?>

                                          @if (count($cpacco) > 0)
                                               <tr>
                                                <td class="text-danger" style="font-weight:bold; margin-top:5px;margin-bottom:10px; padding:8px">{{ucwords($item->name)}}</td>
                                             <td></td>
                                             <td></td>
                                            </tr>
                                          @endif

                                    @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                                             @foreach ($cpacco as $glcpitem)
                                             <tr>
                                                <td><span style="margin-left:15px">{{ucwords($glcpitem->gl_name)}}</span></td>
                                                <td><span style="margin-left:15px">{{ucwords($glcpitem->gl_code)}}</span></td>
                                                <td align="right">
                                                    <?php 
                                                        $cpcrtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glcpitem->id)
                                                                                                        ->where('type','credit')
                                                                                                        ->where('status','approved')
                                                                                                         ->whereBetween('created_at',[$datefrom, request()->dateto])
                                                                                                         ->sum('amount');
                                                                                                         
                                                        $cpdbtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glcpitem->id)
                                                                                                        ->where('type','debit')
                                                                                                        ->where('status','approved')
                                                                                                         ->whereBetween('created_at',[$datefrom, request()->dateto])
                                                                                                         ->sum('amount');


                                                     $currencyid = $glcpitem->currency_id ?: '';
                                                    //     $currency = $getdata->GetcurrencyExchge($currencyid);
                                                        $rate = !empty($currencyid) ? $currencyRates[$currencyid] : 1;

                                                    $cptrnx = $cpcrtrnx - $cpdbtrnx;
                                                    $convertedCamt = $filtercuury == '1' ? $cptrnx * $rate : $cptrnx;
                                                     $capbal += $convertedCamt;
                                                    ?>
                                                   <span style="text-align:right"> <b>{{number_format($convertedCamt,2)}}</b></span>
                                                    
                                                </td>
                                            </tr>
                                             @endforeach

                                             @else

                                             @foreach ($cpacco as $glcpitem)
                                             <tr>
                                                <td><span style="margin-left:15px">{{ucwords($glcpitem->gl_name)}}</span></td>
                                                <td><span style="margin-left:15px">{{ucwords($glcpitem->gl_code)}}</span></td>
                                                <td align="right">
                                                    <?php 
                                                        $cpcrtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glcpitem->id)
                                                                                                        ->where('type','credit')
                                                                                                        ->where('status','approved')
                                                                                                         ->sum('amount');
                                                                                                         
                                                        $cpdbtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glcpitem->id)
                                                                                                        ->where('type','debit')
                                                                                                        ->where('status','approved')
                                                                                                         ->sum('amount');


                                                       $currencyid = $glcpitem->currency_id ?? '';
                                                      //   $currency = $getdata->GetcurrencyExchge($currencyid);
                                                        $rate = !empty($currencyid) ? $currencyRates[$currencyid] : 1;

                                                    $cptrnx = $cpcrtrnx - $cpdbtrnx;
                                                    $convertedCamt = $filtercuury == '1' ? $cptrnx * $rate : $cptrnx;
                                                     $capbal += $convertedCamt;
                                                    ?>
                                                   <span style="text-align:right"> <b>{{number_format($convertedCamt,2)}}</b></span>
                                                    
                                                </td>
                                            </tr>
                                             @endforeach

                                             @endif
                                            @endforeach
                                            
                                            
                                            {{-- profit and loss interest --}}
                                            @foreach ($inaccate as $item)
                                                <?php 
                                                    if(request()->zbalance == '1'){
                                                        $intracco = DB::table('general_ledgers')->select('id','gl_name','gl_code','currency_id')->where('status','1')->where('account_category_id',$item->id)
                                                                                              ->get();
                                                    }else{
                                                      $intracco = DB::table('general_ledgers')->select('id','gl_name','gl_code','currency_id')->where('account_category_id',$item->id)
                                                                                              ->where('status','1')
                                                                                            ->where('account_balance','!=','0')->get();
                                                    }
                                                ?>
                                                 @if (!empty($_GET['filter']) && $_GET['filter'] == true)

                                                 @foreach ($intracco as $glintritem)
                                                        <?php 
                                                        $intrcrtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glintritem->id)
                                                                                                        ->where('type','credit')
                                                                                                        ->where('status','approved')
                                                                                                          ->whereBetween('created_at',[$datefrom, request()->dateto])
                                                                                                          ->sum('amount');
                                                                                                          
                                                        $intrdbtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glintritem->id)
                                                                                                        ->where('type','debit')
                                                                                                        ->where('status','approved')
                                                                                                          ->whereBetween('created_at',[$datefrom, request()->dateto])
                                                                                                          ->sum('amount');
                                                    // $intrtrnx = $intrcrtrnx - $intrdbtrnx;
                                                    //   $intrbal += $intrtrnx;

                                                       $currencyid = $glintritem->currency_id ?: '';
                                                      //   $currency = $getdata->GetcurrencyExchge($currencyid);
                                                        $rate = !empty($currencyid) ? $currencyRates[$currencyid] : 1;

                                                        $intrtrnx = $intrcrtrnx - $intrdbtrnx;
                                                        $convertedIncamt = $filtercuury == '1' ? $intrtrnx * $rate : $intrtrnx;
                                                        $intrbal += $convertedIncamt;
                                                    ?>
                                                 @endforeach

                                                 @else

                                                 @foreach ($intracco as $glintritem)
                                                        <?php 
                                                        $intrcrtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glintritem->id)
                                                                                                        ->where('type','credit')
                                                                                                        ->where('status','approved')
                                                                                                          ->sum('amount');
                                                                                                          
                                                        $intrdbtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glintritem->id)
                                                                                                        ->where('type','debit')
                                                                                                        ->where('status','approved')
                                                                                                          ->sum('amount');
                                                    // $intrtrnx = $intrcrtrnx - $intrdbtrnx;
                                                    //   $intrbal += $intrtrnx;

                                                       $currencyid = $glintritem->currency_id ?: '';
                                                      //   $currency = $getdata->GetcurrencyExchge($currencyid);
                                                        $rate = !empty($currencyid) ? $currencyRates[$currencyid] : 1;

                                                        $intrtrnx = $intrcrtrnx - $intrdbtrnx;
                                                        $convertedIncamt = $filtercuury == '1' ? $intrtrnx * $rate : $intrtrnx;
                                                        $intrbal += $convertedIncamt;
                                                    ?>
                                                 @endforeach  

                                                 @endif
                                            @endforeach

                                               {{-- profit and loss expense --}}
                                               @foreach ($expaccate as $item)
                                               <?php 
                                                   if(request()->zbalance == '1'){
                                                       $expacco = DB::table('general_ledgers')->select('id','gl_name','gl_code','currency_id')->where('status','1')->where('account_category_id',$item->id)
                                                                                             ->get();
                                                   }else{
                                                     $expacco = DB::table('general_ledgers')->select('id','gl_name','gl_code','currency_id')->where('account_category_id',$item->id)
                                                                                             ->where('status','1')
                                                                                           ->where('account_balance','!=','0')->get();
                                                   }
                                               ?>
                                                @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                                                   
                                                @foreach ($expacco as $glexpitem)
                                                      <?php 
                                                      $expcrtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glexpitem->id)
                                                                                                      ->where('type','credit')
                                                                                                      ->where('status','approved')
                                                                                                        ->whereBetween('created_at',[$datefrom, request()->dateto])
                                                                                                        ->sum('amount');
                                                                                                        
                                                      $expdbtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glexpitem->id)
                                                                                                      ->where('type','debit')
                                                                                                      ->where('status','approved')
                                                                                                        ->whereBetween('created_at',[$datefrom, request()->dateto])
                                                                                                        ->sum('amount');
                                                  // $exptrnx = $expdbtrnx - $expcrtrnx;
                                                  //   $expbal += $exptrnx;

                                                     $currencyid = $glexpitem->currency_id ?: '';
                                                    //     $currency = $getdata->GetcurrencyExchge($currencyid);
                                                        $rate = !empty($currencyid) ? $currencyRates[$currencyid] : 1;

                                                            $exptrnx = $expdbtrnx - $expcrtrnx;
                                                            $covertedExpamt = $filtercuury == '1' ? $exptrnx * $rate : $exptrnx;
                                                            $expbal += $covertedExpamt;
                                                  ?>
                                                @endforeach

                                                @else

                                                @foreach ($expacco as $glexpitem)
                                                      <?php 
                                                      $expcrtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glexpitem->id)
                                                                                                      ->where('type','credit')
                                                                                                      ->where('status','approved')
                                                                                                        ->sum('amount');
                                                                                                        
                                                      $expdbtrnx = DB::table('savings_transaction_g_l_s')->where('general_ledger_id',$glexpitem->id)
                                                                                                      ->where('type','debit')
                                                                                                      ->where('status','approved')
                                                                                                        ->sum('amount');
                                                  // $exptrnx = $expdbtrnx - $expcrtrnx;
                                                  //   $expbal += $exptrnx;

                                                  
                                                      $currencyid = $glexpitem->currency_id ?: '';
                                                    //     $currency = $getdata->GetcurrencyExchge($currencyid);
                                                        $rate = !empty($currencyid) ? $currencyRates[$currencyid] : 1;

                                                            $exptrnx = $expdbtrnx - $expcrtrnx;
                                                            $covertedExpamt = $filtercuury == '1' ? $exptrnx * $rate : $exptrnx;
                                                            $expbal += $covertedExpamt;
                                                  ?>
                                                @endforeach

                                                @endif
                                           @endforeach
                                            <tr>
                                              <?php 
                                              $unauditbal = (float)$intrbal - (float)$expbal;
                                             ?>
                                            <td><h5 style="font-weight:bold;text-align:left">Unaudited profit/loss to date</h5></td>
                                            <td></td>
                                            <td align="right"><b style="font-weight:bold;text-align:right">{{number_format($unauditbal,2)}}</b></td>
                                          </tr>
                                            <tr>
                                            <tr>
                                            <td><h5 style="font-weight:bold;text-align:left">Total Capital</h5></td>
                                            <td></td>
                                            <td align="right"><b style="font-weight:bold;text-align:right">{{number_format($capbal,2)}}</b></td>
                                          </tr>
                                      
                              </tbody>
                          </table>
                      </div>
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
   a.document.write('<body>  @if (!empty($_GET["filter"]) && $_GET["filter"] == true)<h3>BalanceSheet for the period: {{date("d M, Y",strtotime($datefrom))." To ".date("d M, Y",strtotime($_GET["dateto"]))}}</h3>@else <h3>BalanceSheet</h3>@endif');
   a.document.write(divContents);
   a.document.write('</body></html>');
   a.document.close();
   a.print();
   }
   
   function exporttoexcel(){
       $("#balansheet").table2excel({
     exclude: ".excludeThisClass",
     name: "Balance_Sheet_Report",
     filename: "Balance_Sheet_Report.xls", // do include extension
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
