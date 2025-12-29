@extends('layout.app')
@section('title')
    Savings Transactions   
@endsection
@section('pagetitle')
Savings Transactions   
@endsection
@section('content')
  <div class="container">
    <?php 
                $getsetvalue = new \App\Models\Setting();
            ?>
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                         @include('includes.success')
                         <form action="{{route('savings.transaction')}}" method="get" onsubmit="thisForm()">
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
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Generate Record</button>
                                  <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('savings.transaction')}}'">Reset</button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </form>
                    </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Customer Name</th>
                                    <th>Account No</th>
                                    <th>Transaction Type</th>
                                    <th>Slip No</th>
                                    <th>Reference</th>
                                    <th>Amount ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    <th>Debit ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    <th>Credit ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    <th>Transaction Date</th>
                                    {{-- <th></th> --}}
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($strans as $item)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($item->customer->last_name." ".$item->customer->first_name)}}</td>
                                    <td>{{$item->customer->acctno}}</td>
                                    <td>{{$item->type}}</td>
                                    <td>{{$item->slip}}</td>
                                    <td>{{$item->reference_no}}</td>
                                    <td>{{number_format($item->amount)}}</td>
                                    @if($item->type=="deposit" || $item->type=="credit" || $item->type=="dividend" || $item->type=="interest" || $item->type=="rev_withdrawal")
                                    <td style="text-align:right">
    
                                    </td>
                                    <td style="text-align:right">
                                        {{number_format($item->amount,2)}}
                                    </td>
                                @else
                                    <td style="text-align:right">
                                        {{number_format($item->amount,2)}}
                                    </td>
                                    <td style="text-align:right">
    
                                    </td>
                                @endif
                                    <td align="right">{{Date('d M Y',strtotime($item->created_at))." - ".Date('h:ia',strtotime($item->created_at))}}</td>
                                      {{-- <td>
                                         @if($item->type == "esusu" || $item->type == "transfer_charges" || $item->type == "monthly_charge" || 
                                    $item->type == "form_fees" || $item->type == "process_fees"  || $item->type == "withdrawal" || $item->type == 'rev_deposit')

                                      <div class="btn-group">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"> Action <i class="fa fa-caret-down prepend-icon"></i> </button>
                                        <ul class="dropdown-menu" role="menu">
                                          <li>
                                            <a href="javascript:void(0)" onclick="edittran('{{$item->id}}','{{$item->type}}')">Edit</a>
                                          </li>
                                          @can('delete savings transaction')
                                          <li>
                                            <a href="{{route('savings.transaction.delete',['id' => $item->id])}}" onclick="return confirm('are you sure you want to delete these record')">Delete</a>
                                          </li>
                                          @endcan
                                      </ul>
                                    </div>
                                      </td> --}}
                                </tr>
                                <?php $i++?>
                                @endforeach
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
 <div class="modal fade" id="mytranModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header vd_bg-blue vd_white">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h4 class="modal-title" id="myModalLabel">Edit</h4>
      </div>
      <div class="modal-body"> 
        <form class="form-horizontal" action="{{route('savings.transaction.update')}}" method="post" id="edtrn">
          @csrf
          <div class="form-group">
            <label class="col-sm-4 control-label">Transaction Type</label>
            <div class="col-sm-12 controls">
              <select class="form-control" required="" id="type" name="type">
                <option value="deposit" selected="selected">Deposit</option>
                <option value="withdrawal">Withdrawal</option>
                <option value="bank_fees">Bank Fees</option>
                <option value="sms">SMS</option>
                <option value="interest">Interest</option>
                <option value="repayment">Repayment</option>
                <option value="dividend">Dividend</option>
              </select>
            </div>
          <input type="hidden" name="trnid" id="trnid" value="">
        </form>
      
      </div>
      <div class="modal-footer background-login">
        <button type="button" class="btn vd_btn vd_bg-grey" data-dismiss="modal">Close</button>
        <button type="button" class="btn vd_btn vd_bg-green" id="btnssubmit" onclick="document.getElementById('edtrn').submit()">Save changes</button>
      </div>
    </div>
    <!-- /.modal-content --> 
  </div>
  <!-- /.modal-dialog --> 
</div>
<!-- /.modal --> 
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
