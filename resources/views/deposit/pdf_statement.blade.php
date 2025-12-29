<style>
    .table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
        display: table;
    }
    .text-left {
        text-align: left;
    }
    .text-right {
        text-align: right;
    }
    .text-center {
        text-align: center;
    }
    .text-justify {
        text-align: justify;
    }
    .pull-right {
        float: right !important;
    }
   
.page-break {
    page-break-after: always;
}

</style>

<div>
    <?php
    $getsetvalue = new \App\Models\Setting();
   ?>
    @inject('getloan', 'App\Http\Controllers\DepositmgmtController')
<div class="text-center">
    @if($customer->section == "rich green masters" || $customer->section == "rich green capital")
    <p><img src="{{asset('img/0001LOGO.png')}}" style="width:90%" alt="logo"> </p>
    @else 
        <h3 class="text-center"><img src="{{asset('img/asset_matrix_logo.png')}}" class="img-responsive" width="120" alt="logo"></h3>
        <p>info@assetmatrixmfb.com, 08033197469 <br> 68 Herbert Macaulay Way By Kano Street Busstop, Oyingbo, Lagos, Nigeria</p>
       <p><strong>Account Statement </strong> </p>
      @endif
</div>
  <div class="text-left" style="font-size: 9px;">
        <strong>Generated Date: </strong>{{date("Y-m-d")}}<br>
        <strong>Statement Period: </strong>{{!empty($_GET["fromdate"]) ? date("d-m-Y",strtotime($_GET["fromdate"])) : ""}} - {{!empty($_GET["fromdate"]) ? date("d-m-Y",strtotime($_GET["todate"])) : ""}}<br>
        <strong>Opening Balance: </strong>{{number_format($custid,2)}}
  </div>
    
 <div class="text-left" style="margin-bottom:10px;font-size: 9px;">
     <address>
       <b> Account No:</b> {{$customer->acctno}}<br>
       <b> Account Type:</b> {{$getproname->name }}<br>
       <b> Customer Name:</b> {{ $customer->first_name }} {{ $customer->last_name }}<br>
        <b> Address </b>{{ $customer->residential_address }}<br>
       <b> Phone No: </b>{{ $customer->phone }}
    </address>
</div>
    
    <div style="margin-top:30px;margin-left: auto;margin-right: auto;text-transform: capitalize;font-size: 9px;">
        <table class="table table-striped table-bordered table-condensed table-sm">
            <thead>
                <tr>
                    <th style="text-align:left">Sn</th>
                    <th style="text-align:left"><b>Date</b></th>
                    <th style="text-align:left"><b>Description</b></th>
                    <th style="text-align:left"><b>Tranx Type</b></th>
                    <!--<th><b>Status</b></th>-->
                    <th style="text-align:left"><b>Debit</b></th>
                    <th style="text-align:left"><b>Credit</b></th>
                    <th style="text-align:left"><b>Balance({{!empty($customer->exrate) ? $customer->exrate->currency_symbol : $getsetvalue->getsettingskey('currency_symbol')}})</b></th>
                    </tr>
            </thead>
            <tbody>
                        
                        <?php $i = 0; 
                         $balance = $custid;
                        ?>
                        @foreach($transactions as $key)
                            @if($key->status == 'pending' || $key->status == 'approved')
                            <tr>
                            <td>{{ $i+1 }}</td>  
                                <td>
                                {{date("d M, Y",strtotime($key->created_at))}} 
                              </td>
                             
                                <td>
                                    {!!$key->notes!!}
                                </td>
                                  <td>
                               @if($key->type=="esusu" || $key->type=="transfer_charge")
                                   Transfer Charge
                                  @else
                                  {{str_replace("_"," ",$key->type)}}
                                @endif
                                </td>
                                 <!--<td>-->
                                 <!--     {{$key->status == 'approved' ? 'Successful' : ($key->status == 'pending' ? 'Pending' : ucfirst($key->status) )}}-->
                                   
                                 <!--   </td>-->
                                 @if($key['type']=="deposit" || $key['type']=="investment"  || $key['type']=="dividend" || $key['type']=="interest" ||
                                  $key['type']=="credit" || $key['type']=="fixed_deposit" || $key['type']=="loan" || $key['type']=="fd_interest" 
                                  || $key['type']=="inv_int" || $key['type']=="rev_withdrawal" || $key['type'] == 'guarantee_restored')
                                  
                                   @if($key['status'] == 'approved')
                                    <?php $balance += $key->amount;?>
                                    <td>
       
                                   </td>
                                   <td>
                                       {{number_format($key->amount,2)}}
                                   </td>
                                   @else
                                     <?php $balance;?>
                               <td>
       
                                   </td>
                                   <td>
                                       {{number_format($key->amount,2)}}
                                   </td>
                                 @endif
                                 
                                @else
                                     @if($key->status == 'pending' || $key->status == 'declined')
                                     <?php $balance += 0;?>
                                      <td >
                                            {{number_format($key->amount,2)}}
                                        </td>
                                        <td>
                                            
                                        </td>
                                      @else
                                    <?php $balance -= $key->amount;?>

                                        <td>
                                            {{number_format($key->amount,2)}}
                                        </td>
                                        <td>
                                        </td>
                                    @endif
                                @endif
                                <td>
                                <b>{{number_format($balance,2)}}</b>
                                </td>
                            </tr>
                            <?php $i++; ?>
                            @endif
                        @endforeach
                        </tbody>
        </table>
    </div>
</div>
