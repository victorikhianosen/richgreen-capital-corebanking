@extends('layout.app')
<?php $rv = !empty($_GET['fxrevtype']) ? $_GET['fxrevtype'] : $_GET['rvtype'];?>
@section('title')
Fx {{ucfirst($rv)}} Reversal
@endsection
@section('pagetitle')
 Fx {{ucfirst($rv)}} Reversal
@endsection
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
                          @include('includes.errors')
                        </div>
                        </div>
                      <div class="noprint" style="margin-bottom: 15px">
                        <form action="#" method="get" onsubmit="thisForm()">
                          <input type="hidden" name="filter" value="true">
                          <input type="hidden" name="rvtype" value="{{$rv}}">
                          <table class="table table-bordered table-hover table-sm">
                            <thead>
                              <tr>
                                <th>Reference No</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>
                                  <div class="form-group">
                                    <input type="text" name="reference" required id="" placeholder="Reference No" value="{{!empty($_GET['reference']) ? $_GET['reference'] : ""}}" class="form-control">
                                  </div>
                                </td>
                                
                                <td>
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Search Records</button>
                                  <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('fx_reversal')}}?fxrevtype={{$rv}}'">Reset</button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </form>
                      </div>
                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                        <form action="{{route('fx_reversalstore')}}" method="post" id="revform">
                            @csrf
                            <div class="table-responsive">
                                <table id="researh" class="table table-bordered table-striped table-condensed table-hover table-sm">
                                    <tbody>
                                      <tr><td>Customer</td><td>{{!is_null($rfx->customer) ? $rfx->customer : "N/A"}}</td></tr>
                                      <tr><td>Naira Amount</td><td>{{number_format($rfx->naria_amount,2)}}</td></tr>
                                      <tr><td>Foreign Amount</td><td>{{number_format($rfx->foreign_amount,2)}}</td></tr>
                                     <tr><td>Purchased Rate</td><td>{{number_format($rfx->purchase_exchange_rate,2)}}</td></tr>
                                      @if ($rv=="sales")
                                      <tr><td>Sold Rate</td><td>{{number_format($rfx->sales_exchange_rate,2)}}</td></tr>
                                      <tr><td>Sales Margin</td><td>{{number_format($rfx->sales_margin,2)}}</td></tr>
                                      <tr><td>Beneficiary</td><td>{{ucwords($rfx->beneficiary)}}</td></tr>
                                      <tr><td>Beneficiary Bank</td><td>{{ucwords($rfx->beneficiary_bank)}}</td></tr>
                                      @endif
                                      <tr><td>Payment Mode</td><td>{{$rfx->payment_mode}}</td></tr>
                                      <tr><td>Description</td><td><p>{{$rfx->description}}</p></td></tr>
                                      <tr><td>Transaction Date</td><td>{{date("d-m-Y",strtotime($rfx->tranx_date))}}</td></tr>
                                    </tbody>
                                </table>
                    
                            </div>
                             @if ($rfx->payment_mode == "cash" || $rfx->payment_mode == "bank")
                              
                                <input type="hidden" name="glacct1" value="{{$records[0]['general_ledger_id']}}">
                                <input type="hidden" name="glacct2" value="{{$records[1]['general_ledger_id']}}">
                              
                             @elseif ($rfx->payment_mode == "customer")
                                  <input type="hidden" name="customerid" value="{{$records[0]->custid}}">
                                  <input type="hidden" name="glacct1" value="{{$records[0]->glid}}">
                             @endif
                             <input type="hidden" name="refere" value="{{!empty($_GET['reference']) ? $_GET['reference'] : ""}}">
                            <input type="hidden" name="amount" value="{{$rfx->naria_amount}}">
                            <input type="hidden" name="revstype" value="{{$rv}}">
                            <div class="form-group form-actions">
                                <div class="col-sm-4"> </div>
                                <div class="col-sm-7">
                                  <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Reverse {{ucfirst($rv)}}</button>
                                  
                                </div>
                              </div>
                        </form>
                      @else
                      @if (!empty($_GET['error']) && $_GET['error'] == '1')
                      <div class="alert alert-danger">Reference Not Found</div>
                     @else
                          <div class="alert alert-info">Please Enter Slip or Reference No and click on search record button</div>
                      @endif
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
//     $("#researh").dataTable({
//     'pageLength':25,
//     'dom': 'Bfrtip',
//       buttons: [ 'copy', 'csv', 'print','pdf']
//   });

$("#revform").submit(function(e){
    e.preventDefault();
    $.ajax({
      url: $("#revform").attr('action'),
      method: "post",
      data: $("#revform").serialize(),
      beforeSend:function(){
        $(".loader").css('visibility','visible');
          $(".loadingtext").text('Please wait...');
      },
      success:function(data){
        if(data.status == 'success'){
            $(".loader").css('visibility','hidden');
          toastr.success(data.msg);
           window.location.href="{{route('fx_reversal')}}?fxrevtype={{$rv}}";
          }else{
            toastr.error(data.msg);
            $(".loader").css('visibility','hidden');
           return false;
           }
        },
        error:function(xhr,status,errorThrown){
            $(".loader").css('visibility','hidden');
            toastr.error('Error '+errorThrown);
          return false;
        }
      });
  });

  });
</script>
@endsection