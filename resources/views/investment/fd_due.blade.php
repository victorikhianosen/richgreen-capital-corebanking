@extends('layout.app')
@section('title')
  Fixed Deposit Due  
@endsection
@section('pagetitle')
Fixed Deposit Due 
@endsection

<?php
 $getsetvalue = new \App\Models\Setting();
?>
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                           @can('dashboard fixed deposit')
                            <a href="{{route('manage.fd')}}" class="btn btn-default"><span class="menu-icon"> </span>All Fixed Deposit</a>
                            @endcan
                         </div>
                      </div>
                  <div class="panel-body">
                    <div class="noprint" style="margin-bottom: 15px">
                      <form action="{{route('due.fd')}}" method="get" onsubmit="thisForm()">
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
                                  <input type="date" name="datefrom" id="" class="form-control" value="{{!empty($_GET['datefrom']) ? $_GET['datefrom'] : ''}}">
                                </div>
                              </td>
                              <td>
                                <div class="form-group">
                                  <input type="date" name="dateto" id="" class="form-control"  value="{{!empty($_GET['dateto']) ? $_GET['dateto'] : ''}}">
                                </div>
                              </td>
                              <td>
                                <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Search</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('due.fd')}}'">Reset</button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </form>
                    </div>
                    @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                    <div class="table-responsive">
                        <table class="table table-striped table-sm table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Interest Method</th>
                                    <th>WithHolding Tax</th>
                                    <th>Amount ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                     <th>Payment Date</th>
                                    <th>Payment Method</th>
                                    <th>Posted By</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;
                                $amt =0;
                                ?>

                                @inject('getloan', 'App\Http\Controllers\InvestmentController')

                                @foreach ($fixdus as $item)
                                    <?php 
                                    $amt += $item->total_interest;
                                    $withhdtax = $item->fixed_deposit->withholding_tax / 100 * $item->total_interest;
                                    ?>
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{$item->fixed_deposit->fixed_deposit_code}}</td>
                                    <td><a href="{{route('customer.view',['id' => $item->customer->id])}}">{{ucwords($item->customer->last_name." ".$item->customer->first_name)}}</a></td>
                                    <td>{{ucwords(str_replace("_"," ",$item->fixed_deposit->interest_method))}}</td>
                                    <td>
                                        @if($item->fixed_deposit->enable_withholding_tax == 1)
                                           {{number_format($withhdtax,2)}}
                                        @else
                                         N/A
                                        @endif
                                    </td>
                                    <td>{{number_format($item->total_interest,2)}}</td>
                                    <td>{{!is_null($item->payment_date) ? date("d-m-Y",strtotime($item->payment_date)) : "N/A"}}</td>
                                    <td>{{!is_null($item->payment_method) ? ucwords($item->payment_method) : "N/A"}}</td>
                                    <td>{{!is_null($item->posted_by) ? ucwords($item->posted_by) : "N/A"}}</td>
                                    <td><span class="label {{$item->closed == '1' ? 'label-danger' : 'label-warning' }}">{{$item->closed == '1' ? 'paid' : 'pending'}}</span></td>
                                    <td>{{date("d-m-Y",strtotime($item->due_date))}}</td>
                                </tr>
                                <?php $i++?>
                                @endforeach
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td align="right"><b>Total Amount</b></td>
                                    <td align="left"><b>{{number_format($amt)}}</b></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">Please Select a date range and click on search</div>
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
    $("#acoff").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });

  

  });
</script>
@endsection
