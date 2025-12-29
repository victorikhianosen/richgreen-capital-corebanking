@extends('layout.app')
@section('title')
    Wallet
@endsection
@section('pagetitle')
Wallet
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
                        <div style="text-align: end">
                            <a href="javascript:void(0)" onclick="fundbranch()" class="btn vd_btn vd_bg-blue btn-sm">Fund wallet</a>

                        </div>
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                  @include('includes.success')
                    </div>
                    </div>
                    <div class="noprint" style="margin-bottom: 15px">
                        <form action="{{route('wallet')}}" method="get" onsubmit="thisForm()">
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
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Generate</button>
                                  <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('wallet')}}'">Reset</button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </form>
                      </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr>
                                    <tr style="background-color: #D1F9FF">
                                        <th>Sn</th>
                                           <th>Transaction Date</th>
                                            <th>Reference</th>
                                            <th>Amount</th>
                                            <th>Posted by</th>
                                           <th>Balance ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    
                                       </tr>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;
                                $balance = 0;
                                ?>
                                @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                                
                                @foreach($data as $key)
                                <?php $balance += $key->amount;?>
                                <tr>
                                    <td>{{ $i+1 }}</td>  
                                    <td>{{date("d-m-Y",strtotime($key->created_at))." at ".date("h:ia",strtotime($key->created_at))}}</td>
                                   
                                    <td>{{$key->reference_no}} </td>
                                    <td>{{number_format($key->amount,2)}}</td>
                                    <td>
                                    {{$key->approve_by}}
                                    </td>
                                    <td>{{number_format($balance,2)}}</td>
                                </tr>
                                <?php $i++?>
                                @endforeach
                                @else
                                @foreach($data as $key)
                                <?php $balance += $key->amount;?>
                                <tr>
                                    <td>{{ $i+1 }}</td>  
                                    <td>{{date("d-m-Y",strtotime($key->created_at))." ".date("h:ia",strtotime($key->created_at))}}</td>
                                   
                                    <td>{{$key->reference_no}} </td>
                                    <td>{{number_format($key->amount,2)}}</td>
                                    <td>
                                        {{$key->approve_by}}
                                    </td>
                                    <td>{{number_format($balance,2)}}</td>
                                </tr>
                                <?php $i++?>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                  </div>
                </div>
                <!-- Panel Widget --> 
              </div>
              <!-- col-md-12 --> 
            </div>
            <!-- row -->
  </div>

   <!-- Modal -->
 <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header vd_bg-blue vd_white">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
          <h4 class="modal-title" id="myModalLabel">Fund Wallet</h4>
        </div>
        <div class="modal-body"> 
          <form class="form-horizontal" action="{{route('walletfund')}}" method="post" id="fundwlet">
            @csrf
            <div class="form-group">
              <label class="col-sm-4 control-label">Amount</label>
              <div class="col-sm-12 controls">
               <input type="number" name="amount" class="form-control" required id="">
              </div>
              </div>
          
        
        </div>
        <div class="modal-footer background-login">
          <button type="button" class="btn vd_btn vd_bg-grey" data-dismiss="modal">Close</button>
          <button type="submit" class="btn vd_btn vd_bg-green" id="btnssubmit">Fund Wallet</button>
        </div>
        </form>
      </div>
      <!-- /.modal-content --> 
    </div>
    <!-- /.modal-dialog --> 
  </div>
  <!-- /.modal --> 
@endsection
@section('scripts')
<script>
    function fundbranch(){
        $("#myModal").modal('show');
      }
  </script>
    <script type="text/javascript">
  $(document).ready(function(){
     let aud = $("#acoff").dataTable({
      'pageLength':25,
      'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
    });


    $("#fundwlet").submit(function(e){
      e.preventDefault();
      $.ajax({
        url: $("#fundwlet").attr('action'),
        method: 'post',
        data: $("#fundwlet").serialize(),
        beforeSend:function(){
          $("#btnssubmit").text('Please wait...');
          $("#btnssubmit").attr('disabled',true);
        },
        success:function(data){
          if(data.status == 'success'){
            $("#btnssubmit").text('Fund Wallet');
          $("#btnssubmit").attr('disabled',false);
          toastr.success(data.msg);
          $("#fundwlet")[0].reset();
          window.location.reload();
          }else{
            toastr.error(data.msg);
             $("#btnssubmit").text('Fund Wallet');
           $("#btnssubmit").attr('disabled',false);
             return false;
           }
        },
        error:function(xhr,status,errorThrown){
          let err = '';
          $.each(xhr.responseJSON.errors, function (key, value) {
                err += value;
            });
            toastr.error(err);
          $("#btnssubmit").text('Fund Wallet');
          $("#btnssubmit").attr('disabled',false);
          return false;
        }
      });
    });
  });
</script>
@endsection
