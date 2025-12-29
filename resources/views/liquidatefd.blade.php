@extends('layout.app')
@section('title')
Fixed Deposits Liquidation  
@endsection
@section('pagetitle')
Fixed Deposits Liquidation  
@endsection

<?php
 $getsetvalue = new \App\Models\Setting();
?>
 @inject('getloan', 'App\Http\Controllers\InvestmentController')

@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                  @include('includes.success')
                  @include('includes.errors')
                    </div>
                    </div>

                    <div style="display: flex; justify-content:center;margin-bottom:10px">
                      <div class="col-md-6 col-lg-6 col-sm-12">
                          <form>
                            <div class="form-group">
                              <label>Fixed deposit Accounts</label>
                               <select class="form-control" id="fixd" onchange="if(this.value!=0){window.location.href='{{route('liqfd')}}?fdcode='+this.value}else{document.getElementById('txtfd').textContent='Please select an account'}">
                                <option value="0">Select A Fixed Deposit Account</option> 
                                @foreach ($fxds as $item)
                                     <option value="{{$item->fixed_deposit_code}}" {{!empty($_GET['fdcode']) && $_GET['fdcode'] == $item->fixed_deposit_code ? "selected" : ""}}>{{ucwords($item->customer->last_name." ".$item->customer->first_name)}}&nbsp;[{{$item->fixed_deposit_code}}]</option>
                                 @endforeach
                              </select>
                            </div>
                          </form>
                          <p id="txtfd" style="color: orangered"></p>
                      </div>
                    </div>
                    
                    @if (!empty($_GET['fdcode']))
                    
                    <?php 
                      $amounttopay = 0;
                      $prevdate = "";
                      $incal = "";
                      // if($fxdcd->interest_method == "upfront"){

                      //   echo "<div class='alert alert-info'>Sorry fixed deposit with an ".$fxdcd->interest_method." interest method can not be liquidated</div>";

                      // }
                    ?>

                      {{-- @if ($fxdcd->interest_method == "monthly" || $fxdcd->interest_method == "rollover" || $fxdcd->interest_method == "simple_rollover") --}}
                          
                    <h4>Fixed Deposit Investment Details</h4>
                    <div class="table-responsive">
                      <table class="table table-striped table-sm table-bordered table-condensed table-hover">    
                          <tbody>
                            <tr>
  
                              <td width="200">
                                  <b>Investment Code</b>
                              </td>
                              <td>{{$fxdcd->fixed_deposit_code}}</td>

                          </tr>
                           <td width="200">
                                  <b>Investment Officer</b>
                              </td>
                              <td>{{!is_null($fxdcd->accountofficer) ? $fxdcd->accountofficer->full_name : "N/A"}}</td>

                          </tr>
                          <tr>
                              <td>
                                  <b>Fixed Deposit Product</b>
                              </td>
                              <td>
                                  @if(!empty($fxdcd->fixed_deposit_product_id))
                                      {{ucwords($fxdcd->fixed_deposit_product->name)}}
                                  @endif
                              </td>
                          </tr>
                          
                          <tr>

                              <td>
                                  <b>Principal Amount</b>
                              </td>
                              <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($fxdcd->principal,2)}}</td>

                          </tr>
                         
                          <tr>
                              <td>
                                  <b>interest method</b>
                              </td>
                              <td>
                                 {{str_replace("_"," ",ucwords($fxdcd->interest_method))}}
                              </td>
                          </tr>
                          <tr>
                              <td>
                                  <b>Maturity Date</b>
                              </td>
                              <td>
                                 {{date("d M Y",strtotime($fxdcd->maturity_date))}}
                              </td>
                          </tr>
                          <tr>
                              <td>
                                  <b>Investment interest</b>
                              </td>
                              <td>{{number_format($fxdcd->interest_rate)}}% / {{ucwords($fxdcd->interest_period)}}
                              </td>
                          </tr>
                          <tr>
                              <td>
                                  <b>Investment duration</b>
                              </td>
                              <td>{{$fxdcd->duration}} {{ucwords($fxdcd->duration_type)}}s
                              </td>
                          </tr>
                          <tr>
                              <td><b>Payment cycle</b></td>
                              <td>
                                 {{str_replace("_"," ",ucfirst($fxdcd->payment_cycle))}}
                              </td>
                          </tr>
                          <tr>
                            <td><b>Approved By</b></td>
                            <td>
                               {{!is_null($fxdcd->fd_approved) ? ucwords($fxdcd->fd_approved->last_name." ".$fxdcd->fd_approved->first_name) : ''}}
                            </td>
                        </tr>
                       
                          </tbody>
                      </table>
                  </div>

                  @if ($fxdcd->interest_method != "upfront")
                  <h5>Investment interest will be charge {{$getsetvalue->getsettingskey('withholdingtax')}}% withholding tax and {{$getsetvalue->getsettingskey('fdcharge')}}% penalty charge before maturity</h5>
                 @endif

                  <form action="{{route('fd.liqutae')}}" method="post" id="liquateacct" onsubmit="thisForm()">
                    @csrf
                  @if ($fxdcd->interest_method == "upfront")

                    @if ($fxdcd->maturity_date >= date('Y-m-d'))

                    <input type="hidden" name="principal" id="" value="{{$fxdcd->principal}}">
                    <input type="hidden" name="customerid" id="" value="{{$fxdcd->customer_id}}">
                    <input type="hidden" name="fxdid" id="" value="{{$fxdcd->id}}">
                    <input type="hidden" name="liqoption" id="" value="upfront">
                    
                    <div class="form-group form-actions">
                      <div class="col-sm-4"> </div>
                      <div class="col-sm-7">
                    <button class="btn vd_btn vd_bg-green vd_white" type="submit"  id="btnssubmit">Liquidate Investment</button>
                  </div>
                  </div>
                    @else
                        <div class='alert alert-info'>Sorry this upfront fixed deposit can not be liquidated</div>
                    @endif

                  @else

                  <div class="table-responsive">
                    <table class="table table-striped table-sm table-bordered table-condensed table-hover"> 
                     <thead>
                      <tr>
                        <th>Interest</th>
                        <th>Total Interest</th>
                        <th>Due Date</th>
                        <th>Status</th>
                      </tr>
                     </thead>
                      <tbody>
                        @foreach ($schedules as $item)
                            <tr>
                              <td>{{$fxdcd->interest_method == "simple_rollover" ? $item->rollover : $item->interest}}</td>
                              <td>{{$item->total_interest}}</td>
                              <td>{{date("d-m-Y",strtotime($item->due_date))}}</td>
                             <td>
                               @if ($item->closed == '0')
                                   <span class="label label-success">Active</span>
                               @else
                               <span class="label label-danger">Closed</span>
                               @endif
                             </td>
                            </tr>
                        @endforeach
                    </tbody>
                  </table>
                     
                  </div>

                  <?php 
                  $rolv= 0;
                  if($fxdcd->interest_method == "simple_rollover"){
                    $getschedules = DB::table('investment_schedules')->where('fixed_deposit_id',$fxdcd->id)
                                                                 ->where('closed','0')
                                                                ->whereMonth('due_date', '=', \Carbon\Carbon::now()->format('m'))->get();
                   foreach($getschedules as $item){
                    $rolv += $item->rollover;
                     
                      if($fxdcd->maturity_date < \Carbon\Carbon::now()){
                        $prvdt = \Carbon\Carbon::parse($item->due_date)->subDays(30)->format('d-m-Y');
                         $getdays = (strtotime(date("d-m-Y")) - strtotime($prvdt))/86400;

                         $incal = $rolv/30 * $getdays;
                       
                      }else{
                        $incal = "";
                      }
                     }
                    
                  }else{
                         $getschedules = DB::table('investment_schedules')->where('fixed_deposit_id',$fxdcd->id)
                                                                 ->where('closed','0')
                                                                ->whereMonth('due_date', '=', \Carbon\Carbon::now()->format('m'))->get();
                     foreach($getschedules as $item){
                      $prvdt = \Carbon\Carbon::parse($item->due_date)->subDays(30)->format('d-m-Y');
                    $getdays = (strtotime(date("d-m-Y")) - strtotime($prvdt))/86400;

                      $incal = $item->total_interest/30 * $getdays;
                     }
                  }
              
                    ?> 

                <div class="form-group">
                  <input type="hidden" name="interest" id="" value="{{!empty($incal) ? $incal : '0'}}">
                  <input type="hidden" name="wthtax" id="" value="{{$fxdcd->withholding_tax}}">
                  <input type="hidden" name="fdcharge" id="" value="{{$getsetvalue->getsettingskey('fdcharge')}}">
                  <input type="hidden" name="principal" id="" value="{{$fxdcd->principal}}">
                  <input type="hidden" name="customerid" id="" value="{{$fxdcd->customer_id}}">
                  <input type="hidden" name="fxdid" id="" value="{{$fxdcd->id}}">
                  <input type="hidden" name="liqoption" id="" value="msr">

                  <div class="col-sm-7 controls">
                    <div class="vd_checkbox checkbox-success">
                      <input type="checkbox" name="enable_fdcharge" value="1" id="checkbox-1">
                      <label for="checkbox-1">Enable Fixed Deposit Charge </label>
                    </div>
                  </div>

                </div>

                <div class="form-group form-actions">
                  <div class="col-sm-4"> </div>
                  <div class="col-sm-7">
                <button class="btn vd_btn vd_bg-green vd_white" type="submit"  id="btnssubmit">Liquidate Investment</button>
              </div>
              </div>

                  @endif
              
                </form>

                {{-- @endif --}}
                @endif

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
  function edittran(id,typ){
    $("#mytranModal").modal('show');
    $("#trnid").val(id);
    let x = document.getElementById('type');
    for(i=0; i<x.length; i++){
      if(x.options[i].value == typ){
        x.options[i].selected = true;
      }
    }
  }
</script>
    <script type="text/javascript">
  $(document).ready(function(){
      $("#fixd").select2();
      
    $("#acoff").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });

  $("#liquateacct").submit(function(e){
      e.preventDefault();
      $.ajax({
        url: $("#liquateacct").attr('action'),
        method: 'post',
        data: $("#liquateacct").serialize(),
        beforeSend:function(){
          $("#btnssubmit").text('Please wait...');
          $("#btnssubmit").attr('disabled',true);
        },
        success:function(data){
          if(data.status == 'success'){
            $("#btnssubmit").text('Liquidate Account');
          $("#btnssubmit").attr('disabled',false);
          toastr.success(data.msg);
           window.location.href='{{route("liqfd")}}';
          }else{
            toastr.error(data.msg);
             $("#btnssubmit").text('Liquidate Account');
           $("#btnssubmit").attr('disabled',false);
             return false;
           }
        },
        error:function(xhr,status,errorThrown){
          toastr.error('Error '+errorThrown);
          $("#btnssubmit").text('Liquidate Account');
          $("#btnssubmit").attr('disabled',false);
          return false;
        }
      });
    });
  });
  
</script>
@endsection
