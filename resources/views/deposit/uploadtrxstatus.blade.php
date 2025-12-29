@extends('layout.app')
@section('title')
    Uploaded Transaction Details For @if(!empty($_GET['type']) && $_GET['type'] == 'glc')
    General Ledger  To Customer      
    @elseif(!empty($_GET['type']) && $_GET['type'] == 'cgl')
    Customer To General Ledger  
     @elseif(!empty($_GET['type']) && $_GET['type'] == 'gltogl')
     GL To GL  
     @else 
     Customers
     @endif
@endsection
@section('pagetitle')
Uploaded Transaction Details For @if(!empty($_GET['type']) && $_GET['type'] == 'glc')
General Ledger  To Customer      
@elseif(!empty($_GET['type']) && $_GET['type'] == 'cgl')
Customer To General Ledger  
 @elseif(!empty($_GET['type']) && $_GET['type'] == 'gltogl')
 GL To GL 
 @else 
 Customers
 @endif
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                          @if(!empty($_GET['type']) && $_GET['type'] == 'glc')
                          <a href="{{route('ac.category.batchupload')}}?type=glc" class="btn btn-danger"><span class="menu-icon"></span> Back</a>      
                          @elseif(!empty($_GET['type']) && $_GET['type'] == 'cgl')
                          <a href="{{route('ac.category.batchupload')}}?type=cgl" class="btn btn-danger"><span class="menu-icon"></span> Back</a>
                          @elseif(!empty($_GET['type']) && $_GET['type'] == 'gltogl')
                          <a href="{{route('ac.category.batchupload')}}?type=gltogl" class="btn btn-danger"><span class="menu-icon"></span> Back</a>
                          @else
                          <a href="{{route('uploadtrxpg')}}" class="btn btn-danger"><span class="menu-icon"></span> Back</a>
                          @endif
                          </div>
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                  @include('includes.success')
                    </div>
                    </div>
                    <div class="noprint" style="margin-bottom: 15px">
                        <form action="{{route('viewuploadstatus')}}" method="get" onsubmit="thisForm()">
                          <input type="hidden" name="uploadfilter" value="true">
                          <input type="hidden" name="type" value="{{!empty($_GET['type']) ? $_GET['type'] : ''}}">
                          
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
                                    <input type="date" name="datefrom" id="" class="form-control">
                                  </div>
                                </td>
                                <td>
                                  <div class="form-group">
                                    <input type="date" name="dateto" id="" class="form-control">
                                  </div>
                                </td>
                                <td>
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Search Transactions</button>
                                  <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('viewuploadstatus')}}'">Reset</button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                      
                        </form>
                      </div>
                    <form action="{{route('viewuploadstatus')}}" method="post" id="changeuploadstaus">
                      <input type="hidden" name="updatesata" value="true">
                      <input type="hidden" name="type" value="{{!empty($_GET['type']) ? $_GET['type'] : ''}}">
                        <div class="table-responsive">
                            @if (!empty($_GET['type']) && $_GET['type'] == 'glc') <!-- GL to customer-->
                           
                            <table class="table table-striped" id="acoff">
                              <thead>
                                  <tr>
                                      <th>Sn</th>
                                      <th>GL Name</th>
                                      <th>GL Code</th>
                                      <th>Customer Name</th>
                                      <th>Account No</th>
                                      <th>Transaction Type</th>
                                      <th>Amount</th>
                                      <th>GL Balance</th>
                                      <th>trx status</th>
                                      <th>Reason</th>
                                      <th>Date</th>
                                  </tr>
                              </thead>    
                              <tbody>
                                  <?php $i=0;
                                   $getcurr = !empty($_GET['uploadstatus']) && $_GET['uploadstatus'] == "current" ? "1" : "0";
                                  ?>
                                  @foreach (\App\Models\Upload_transaction_status::where('gl_type',$_GET['type'])
                                                                                  ->where('upload_status',$getcurr)->get() as $item)
                                  <tr>
                                      <input type="hidden" name="uplid[]" value="{{$item->id}}">
                                      <td>{{$i+1}}</td>
                                      <td>{{!empty($item->general_ledger) ? ucwords($item->general_ledger->gl_name) : "N/A"}}</td>
                                      <td>{{!empty($item->general_ledger) ? $item->general_ledger->gl_code : "N/A"}}</td>
                                      <td>{{!empty($item->customer) ? ucwords($item->customer->last_name." ".$item->customer->first_name) : "N/A"}}</td>
                                      <td>{{!empty($item->customer) ?  $item->customer->acctno  : "N/A"}}</td>
                                      <td>{{$item->trx_type}}</td>
                                      <td>{{!empty($item->amount) ? number_format($item->amount,2) : 'N/A'}}</td>
                                      <td>{{number_format($item->balance,2)}}</td>
                                      <td><span class="badge {{$item->trx_status == '1' ? 'vd_bg-green' : 'vd_bg-red'}}">{{$item->trx_status == '1' ? 'success' : 'failed'}}</span></td>
                                      <td>{{$item->reason}}</td>
                                      <td>{{Date('d M Y',strtotime($item->created_at))." - ".Date('h:ia',strtotime($item->created_at))}}</td>
                                  </tr>
                                  <?php $i++?>
                                  @endforeach
                              </tbody>
                          </table>

                            @elseif(!empty($_GET['type']) && $_GET['type'] == "cgl") <!--Customer to GL-->
                            <table class="table table-striped" id="acoff">
                              <thead>
                                  <tr>
                                      <th>Sn</th>
                                      <th>Name</th>
                                      <th>Account No</th>
                                      <th>GL Name</th>
                                      <th>GL Code</th>
                                      <th>Transaction Type</th>
                                      <th>Amount</th>
                                      <th>Customer Balance</th>
                                      <th>trx status</th>
                                      <th>Reason</th>
                                      <th>Date</th>
                                  </tr>
                              </thead>    
                              <tbody>
                                  <?php $i=0;
                                  $getcurr = !empty($_GET['uploadstatus']) && $_GET['uploadstatus'] == "current" ? "1" : "0";
                                  ?>
                                  @foreach (\App\Models\Upload_transaction_status::where('gl_type',$_GET['type'])
                                                                              ->where('upload_status',$getcurr)->get() as $item)
                                 
                                  <tr>
                                      
                                      <input type="hidden" name="uplid[]" value="{{$item->id}}">
                                      <td>{{$i+1}}</td>
                                     <td>{{!empty($item->customer) ? ucwords($item->customer->last_name." ".$item->customer->first_name) : "N/A"}}</td>
                                       <td>{{!empty($item->customer) ?  $item->customer->acctno  : "N/A"}}</td>
                                       <td>{{!empty($item->general_ledger) ? ucwords($item->general_ledger->gl_name) : "N/A"}}</td>
                                      <td>{{!empty($item->general_ledger) ? $item->general_ledger->gl_code : "N/A"}}</td>
                                      <td>{{$item->trx_type}}</td>
                                      <td>{{!empty($item->amount) ? number_format($item->amount,2) : 'N/A'}}</td>
                                      <td>{{number_format($item->balance,2)}}</td>
                                      <td><span class="badge {{$item->trx_status == '1' ? 'vd_bg-green' : 'vd_bg-red'}}">{{$item->trx_status == '1' ? 'success' : 'failed'}}</span></td>
                                      <td>{{$item->reason}}</td>
                                      <td>{{Date('d M Y',strtotime($item->created_at))." - ".Date('h:ia',strtotime($item->created_at))}}</td>
                                  </tr>
                                  <?php $i++?>
                                  @endforeach
                              </tbody>
                          </table>

                            @elseif(!empty($_GET['type']) && $_GET['type'] == "gltogl")  <!--GL to GL-->

                            <table class="table table-striped" id="acoff">
                                <thead>
                                    <tr>
                                        <th>Sn</th>
                                        <th>GL Account Name</th>
                                        <th>GL Account Name</th>
                                        <th>Transaction Type</th>
                                        <th>Amount</th>
                                        <th>Balance</th>
                                        <th>trx status</th>
                                        <th>Reason</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>    
                                <tbody>
                                  <?php $i=0;
                                  $uplatst = \App\Models\Upload_transaction_status::first();
                                   $getcurr = !empty($_GET['uploadstatus']) && $_GET['uploadstatus'] == "current" ? "1" : "0";
                                  ?>
                                  @foreach (\App\Models\Upload_transaction_status::where('gl_type',$_GET['type'])
                                                                              ->where('upload_status',$getcurr)->get() as $item)
                                  <tr>
                                     
                                      <input type="hidden" name="uplid[]" value="{{$item->id}}">
                                      <td>{{$i+1}}</td>
                                      <td>{{ucwords($uplatst->getgeneralledger($item->customer_id))}}</td>
                                      <td>{{ucwords($uplatst->getgeneralledger($item->general_ledger_id))}}</td>
                                      <td>{{$item->trx_type}}</td>
                                      <td>{{!empty($item->amount) ? number_format($item->amount,2) : 'N/A'}}</td>
                                      <td>{{number_format($item->balance,2)}}</td>
                                      <td><span class="badge {{$item->trx_status == '1' ? 'vd_bg-green' : 'vd_bg-red'}}">{{$item->trx_status == '1' ? 'success' : 'failed'}}</span></td>
                                      <td>{{$item->reason}}</td>
                                      <td>{{Date('d M Y',strtotime($item->created_at))." - ".Date('h:ia',strtotime($item->created_at))}}</td>
                                  </tr>
                                  <?php $i++?>
                                  @endforeach
                              </tbody>
                          </table>

                            @else
                              @if (count($uploadstatus) > 0)
                              <table class="table table-striped" id="acoff">
                                <thead>
                                    <tr>
                                        <th>Sn</th>
                                        <th>Name</th>
                                        <th>Account No</th>
                                        <th>Transaction Type</th>
                                        <th>Amount</th>
                                        <th>Balance</th>
                                        <th>trx status</th>
                                        <th>Reason</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>    
                                <tbody>
                                    <?php $i=0;?>
                                    @foreach ($uploadstatus as $item)
                                    <tr>
                                        <input type="hidden" name="uplid[]" value="{{$item->id}}">
                                        <td>{{$i+1}}</td>
                                        <td>{{!empty($item->customer) ? ucwords($item->customer->last_name." ".$item->customer->first_name) : "N/A"}}</td>
                                        <td>{{!empty($item->customer) ? $item->customer->acctno : "N/A"}}</td>
                                        <td>{{$item->trx_type}}</td>
                                        <td>{{!empty($item->amount) ? number_format($item->amount,2) : 'N/A'}}</td>
                                        <td>{{number_format($item->balance,2)}}</td>
                                        <td><span class="badge {{$item->trx_status == '1' ? 'vd_bg-green' : 'vd_bg-red'}}">{{$item->trx_status == '1' ? 'success' : 'failed'}}</span></td>
                                        <td>{{$item->reason}}</td>
                                        <td>{{Date('d M Y',strtotime($item->created_at))." - ".Date('h:ia',strtotime($item->created_at))}}</td>
                                    </tr>
                                    <?php $i++?>
                                    @endforeach
                                </tbody>
                            </table>
                              @else
                                  <div class="alert alert-info">No Uploaded Transactions</div>
                              @endif
                           
                            @endif
                        </div>
                    </form>
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
    $("#acoff").dataTable({'pageLength':50,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
   });

  });

  
</script>
@endsection
