<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    
</head>
<body>
    <?php
    $getsetvalue = new \App\Models\Setting();
   ?>
    @inject('getloan', 'App\Http\Controllers\ReportsController')
    <div class="print">
        <div style="text-align: center">
          <h3 class="text-center"><strong>{{ucwords("balance sheet report")}}</strong></h3>
          <p class="text-center"><b>{{ucwords($getsetvalue->getsettingskey('company_name'))}}</b></p>
          <p class="text-center"><b>As At: {{date("d M, Y",strtotime($_GET['dateto']))}}</b></p>
          <p class="text-center"><b>Generated On {{date('d M, Y')." at ".date('h:i:s')}}</b></p>
        </div>
        <div class="table-responsive">
          <table border="2" class="table table-striped table-bordered table-condensed" style="width: 100%">
              <thead>
                  <tr>
                      <th align="left"><b>Account</b></th>
                      <th align="right">{{$getsetvalue->getsettingskey('currency_symbol')}}</th>
                  </tr>
              </thead>    
              <tbody>
                  <tr style="background-color: #f2f2f2">
                    <td colspan="2">ASSETS</td>
                  </tr>
                  <tr>
                    <td class="text-primary"><b>Current Assets</b></td>
                  <td></td>
                  </tr>
                  <tr>
                    <td><b>Loan Outstanding</b></td>
                    <td align="right"></td>
                </tr>
                <tr>
                    <td><b>Current Loan</b></td>
                    <td align="right">{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loans_total_due($_GET['datefrom'],$_GET['dateto']),2)}}</td>
                </tr>
                <tr>
                    <td><b>Past Due</b></td>
                    <td align="right"></td>
                </tr>
                <tr>
                    <td><b>Restructured</b></td>
                    <td align="right"></td>
                </tr>
                <tr>
                    <td><b>Loans Outstanding (Gross)</b></td>
                    <td align="right">
                      {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loans_total_due($_GET['datefrom'],$_GET['dateto']),2)}}
                    </td>
                </tr>
                <tr>
                    <td><b>Loan Loss Reserve</b></td>
                    <td align="right"></td>
                </tr>
                <tr>
                    <td><b>Net Loans Outstanding</b></td>
                    <td align="right">
                      {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loans_total_due($_GET['datefrom'],$_GET['dateto']),2)}}
                    </td>
                </tr>
                  <tr style="background-color: #000000; color:#fff">
                    <td>Total Current Assets</td>
                    <td>
                      {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loans_total_due($_GET['datefrom'],$_GET['dateto']),2)}}
                    </td>
                </tr>
                <tr>
                  <td class="text-primary"><b>Investments</b></td>
                <td></td>
                </tr>
                <?php $investments = 0; ?>
                @foreach (\App\Models\AssetType::where('type','investment')->get() as $key)
                <tr>
                    <td>{{$key->name}}</td>
                    <td>
                      {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->asset_type_valuation($key->id,$_GET['datefrom']),2)}}
                    </td>
                </tr>
                <?php
                        $investments += $getloan->asset_type_valuation($key->id,$_GET['datefrom']) 
                        ?> 
                @endforeach
                <tr style="background-color: #000000; color:#fff">
                  <td>Total Investment</td>
                  <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($investments,2)}}</td>
              </tr>

              <tr>
              <td class="text-primary"><b>Fixed Assets</b></td>
              <td></td>
              </tr>
              <?php $fixed = 0 ?>
              @foreach(\App\Models\AssetType::where('type','fixed')->get() as $key)
              <tr>
                  <td>{{$key->name}}</td>
                  <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->asset_type_valuation($key->id,$_GET['datefrom']),2)}}</td>
              </tr>
              <?php 
              $fixed += $getloan->asset_type_valuation($key->id,$_GET['datefrom'])
              ?>
              @endforeach
              <tr style="background-color: #000000; color:#fff">
                <td><b>Total Fixed Asset</b></td>
                <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($fixed),2}}</td>
            </tr>
            <tr>
              <td class="text-primary"><b>Intangible Assets</b></td>
              <td></td>
              </tr>
              <?php $intangible = 0; ?>
              @foreach(\App\Models\AssetType::where('type','intangible')->get() as $key)
              <tr>
                  <td>{{$key->name}}</td>
                  <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->asset_type_valuation($key->id,$_GET['datefrom']),2)}}</td>
              </tr>
              <?php 
                 $intangible += $getloan->asset_type_valuation($key->id,$_GET['datefrom'])
              ?>
              @endforeach
              <tr style="background-color: #000000; color:#fff">
                <td>Total Intangible Assets</td>
                <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($intangible,2)}}</td>
            </tr>

            <tr>
              <td class="text-primary"><b>Other Assets</b></td>
              <td></td>
              </tr>
              <?php $other = 0; ?>
              @foreach(\App\Models\AssetType::where('type','other')->get() as $key)
              <tr>
                  <td>{{$key->name}}</td>
                  <td>
                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->asset_type_valuation($key->id,$_GET['datefrom']),2)}}
                  </td>
              </tr>
              <?php 
                 $other += $getloan->asset_type_valuation($key->id,$_GET['datefrom'])
              ?>
              @endforeach
              <tr>
                <td><b>Total Other Assets</b></td>
                <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($other,2)}}</td>
            </tr>
            <tr style="background-color: #1779b6; color:#000">
              <?php 
                $ttoasset = $other+$fixed+$intangible+$investments+$getloan->loans_total_due($_GET['datefrom'],$_GET['dateto']);
                ?>
              <td><b>Total Assets</b></td>
              <td>{{number_format($ttoasset,2)}}</td>
          </tr>
          <tr style="background-color: #f2f2f2">
            <td colspan="2"><b>LIABILITY AND EQUITY</b></td>
          </tr>
          <?php
              $savings = $getloan->total_savings_deposits();
              ?>
                  <tr style="background-color: #63d0d6; color:#000000">
                    <td><b>Liability</b></td>
                  <td></td>
                  </tr>
                  <tr>
                      <td><b>Balance Savings</b></td>
                      <td>{{number_format($savings,2)}}</td>
                  </tr>
                  <tr>
                      <td><b>Withholding Tax</b></td>
                      <td>{{number_format($getloan->total_wht($_GET['datefrom'],$_GET['dateto']),2)}}</td>
                  </tr>
                  <tr>
                      <td><b>Account Payable</b></td>
                      <td></td>
                  </tr>
                  <tr>
                      <td><b>Wages Payable</b></td>
                      <td></td>
                  </tr>
                  <tr>
                      <td><b>Short Term Borrowings</b></td>
                      <td></td>
                  </tr>
                  <tr>
                      <td><b>Long Term Borrowings (Commercial rate)</b></td>
                      <td></td>
                  </tr>
                  <tr>
                      <td><b>Long Term Debt (concessional rate)</b></td>
                      <td></td>
                  </tr>
                  <tr>
                      <td><b>Other Accrued Expenses Payable</b></td>
                      <td></td>
                  </tr>
                  <tr>
                      <td><b>Income Taxes Payable</b></td>
                      <td></td>
                  </tr>
                  <tr>
                      <td><b>Restricted Revenue</b></td>
                      <td></td>
                  </tr>
                  
                  <tr style="background-color: #63d0d6; color:#000000">
                    <td>Total Liability</td>
                    <td>{{$getsetvalue->getsettingskey('currency_symbol')." ".number_format($savings,2)}}</td>
                </tr>
                <tr style="background-color: #3b3b90; color:#fff">
                  <td><b>Equity</b></td>
                <td></td>
                </tr>
                <tr>
                    <td><b>Loan Fund Capital</b></td>
                    <td>
                      <?php
                      $ltot = $other+$fixed+$intangible+$investments+$getloan->loans_total_due($_GET['datefrom'],$_GET['dateto'])-$savings;
                      ?>
                      {{$getsetvalue->getsettingskey('currency_symbol')." ".number_format($ltot)}}
                    </td>
                </tr>
                <tr>
                  <td><b>Retained Net Surplus/(Deficit) prior years</b></td>
                  <td></td>
              </tr>
                <tr>
                  <td><b>Net Surplus/(Deficit) current year</b></td>
                  <td></td>
              </tr>
                <tr style="background-color: #3b3b90; color:#fff">
                  <td>Total Equity</td>
                  <?php 
                  $toteqy = $other+$fixed+$intangible+$investments+$getloan->loans_total_due($_GET['datefrom'],$_GET['dateto'])-$savings 
                  ?>
                  <td>{{$getsetvalue->getsettingskey('currency_symbol')." ".number_format($toteqy,2)}}</td>
              </tr>
              
              
            <tr style="background-color: #1779b6; color:#000">
              <td>Total Liability And Equity</td>
              <td>
                {{number_format($other+$fixed+$intangible+$investments+$getloan->loans_total_due($_GET['datefrom'],$_GET['dateto']),2)}}
              </td>
          </tr>
              </tbody>
          </table>
      </div>
      </div>
      <script>
         window.onload = function () {
        window.print();
    }
      </script>
</body>
</html>