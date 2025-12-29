@extends('layout.app')
@section('title')
    {{!empty($_GET['status']) ? ucwords(str_replace('_','',$_GET['status'])).' Loans' : 'All Loans'}}   
@endsection
@section('pagetitle')
{{!empty($_GET['status']) ? ucwords(str_replace('_','',$_GET['status'])).' Loans' : 'All Loans'}}   
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

                            <div class="row">
                                <div class="form-group col-sm-5 controls">
                                <select class="width-90 form-control" onchange="window.location.href=this.value" autocomplete="off">
                                    <option {{empty($_GET['fx_filter']) ? "selected disabled" : ""}}>Filter Loan FX Exchange</option>
                                  <option value="{{route('loan.index')}}?fx_filter=Null" {{!empty($_GET['fx_filter']) && $_GET['fx_filter'] == "Null"  ? "selected" : "" }}>Naira</option>
                                    @foreach ($exrate as $item)
                                        <option value="{{route('loan.index')}}?fx_filter={{$item->id}}" {{!empty($_GET['fx_filter']) && $_GET['fx_filter'] == $item->id  ? "selected" : "" }}>{{$item->currency}}</option>
                                    @endforeach
                                </select>
                            </div>
                          
                            <div class="col-sm-7">
                              <a href="{{ route('ld.export') }}{{!empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : ''}}" class="btn btn-primary btn-sm"><span class="menu-icon"> <i class="fa fa-file-excel-o"></i> </span> Export Excel</a>
                              @can('create loans')
                              <a href="{{route('loan.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Loan</a>
                              @endcan

                             </div>
                          </div>

                         
                         </div>
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                  @include('includes.success')
                    </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Loan Acct No</th>
                                    <th>Name</th>
                                    <th>Acct No</th>
                                    <th>Phone</th>
                                     <th>District</th>
                                    <th>Principal ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                     <th>Interest ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    <th>Released</th>
                                    <th>Maturity</th>
                                    <th>Officer</th>
                                    <th>Principal Paid ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    <th>Principal Unpaid ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    <th>Interest Paid ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    <th>Interest Unpaid ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    <th>Total Due({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    <th>Paid Amount ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    <th>Balance({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    <th>Branch</th>
                                    <th>Loan Product</th>
                                     <th>Loan Equity</th>
                                     <th>Description </th>
                                      <th>Loan Purpose</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>

                                @inject('getloan', 'App\Http\Controllers\LoanController')

                                @foreach ($loans as $item)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{$item->loan_code}}</td>
                                    <td><a href="{{route('customer.view',['id' => $item->customer->id])}}">{{ucwords($item->customer->last_name." ".$item->customer->first_name)}}</a></td>
                                    <td><a href="{{route('saving.transaction.details',['id' => $item->customer->id])}}">{{$item->customer->acctno}}</a></td>
                                    <td>{{$item->customer->phone}}</td>
                                    <td>{{$item->customer->state}}</td>
                                    <td>{{number_format($item->principal)}}</td>
                                    <td>{{number_format($getloan->loan_total_interest($item->id))}}</td>
                                    <td>{{date("d-m-Y",strtotime($item->release_date))}}</td>
                                    <td>{{date("d-m-Y",strtotime($item->maturity_date))}}</td>
                                    <td>{{!is_null($item->accountofficer) ? $item->accountofficer->full_name : "N/A"}}</td>
                                    <td>{{number_format($getloan->loan_paid_item($item->id))}}</td>
                                    <td>
                                      @php
                                          $unpaid_pricp = $item->principal - $getloan->loan_paid_item($item->id); 
                                      @endphp
                                      {{ number_format($unpaid_pricp,2)}}
                                    </td>

                                    <td>{{number_format($getloan->loan_interest_paid_item($item->id))}}</td>

                                     <td>
                                      @php
                                        $unpaid_intr = $getloan->loan_total_interest($item->id) - $getloan->loan_interest_paid_item($item->id); 
                                      @endphp
                                      {{ number_format($unpaid_intr,2)}}
                                    </td>

                                    <td>
                                        <a href="{{route('loan.show',['id' => $item->id])}}">
                                            @if($item->override)
                                                <s>{{number_format($getloan->loan_total_due_amount($item->id))}}</s><br>
                                                {{number_format($item->balance,2)}}
                                            @else
                                                {{number_format($getloan->loan_total_due_amount($item->id))}}
                                            @endif
                                          </a>
                                    </td>
                                    <td>{{number_format($getloan->loan_total_paid($item->id))}}</td>
                                    <td>{{number_format($getloan->loan_total_balance($item->id))}}</td>
                                    <td>{{!is_null($item->branch) ? $item->branch->branch_name : "N/A"}}</td>
                                    <td><span class="text-info">{{ $item->loan_product->name }} </span></td>
                                    <td>{{number_format($item->equity)}}</td>
                                    <td>{{$item->description}}</td>
                                    <td>{{$item->purpose}}</td>
                                    <td>
                                    
                                        @if($item->status == 'pending')
                                             
                                               <span class="label label-warning">Pending Approval</span> 
                                            
                                              @elseif($item->status == 'approved')
                                              
                                                  <span class="label label-info">Awaiting Disbursement</span>
                                              
                                             @elseif($item->status == 'disbursed')
                                             
                                              <span class="label label-success">Active</span>
                                            
                                             @elseif($item->status == 'declined')
                                             
                                                 <span class="label label-danger">Declined</span>
                                             
                                             @elseif($item->status == 'withdrawn')
                                             
                                                 <span class="label label-danger">Withdrawn</span>
                                            
                                             @elseif($item->status == 'written_off')
                                             
                                                 <span class="label label-danger">Written Off</span>
                                            
                                             @elseif($item->status == 'closed')
                                             
                                                 <span class="badge vd_bg-black">Closed</span>
                                             
                                             @elseif($item->status == 'pending_reschedule')
                                             
                                                 <span class="label label-warning">Pending Reschedule </span>
                                            
                                             @elseif($item->status == 'rescheduled')
                                             
                                                 <span class="label label-info">Rescheduled</span>
                                        @elseif($item->maturity_date < date("Y-m-d") && $getloan->loan_total_balance($item->id) > 0)
                                        <span class="label label-danger">Lost</span>      
                                            
                                        @else
                                             {{ucwords($item->provision_type)}}
                                        @endif
                                    </td>
                                   
                                    <td>
                                        <div class="btn-group">
                                          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"> Action <i class="fa fa-caret-down prepend-icon"></i> </button>
                                          <ul class="dropdown-menu" role="menu">
                                            @can('view loans')
                                            <li>
                                                <a href="{{route('loan.show',['id' => $item->id])}}">Details</a>
                                              </li>
                                            @endcan
                                           @if($item->status != 'closed')
                                            @can('update loans')
                                                <li>
                                              <a href="{{route('loan.edit',['id' => $item->id])}}">Edit</a>
                                            </li>
                                            @endcan
                                            
                                            @can('delete loans')
                                            <li>
                                              <a href="{{route('loan.delete',['id' => $item->id])}}" onclick="return confirm('are you sure you want to delete these record')">Delete</a>
                                            </li>
                                            @endcan
                                            @endif
                                        </ul>
                                      </div>
                                        </td>
                                </tr>
                                <?php $i++?>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row justify-content-center">
                      {{$loans->appends(request()->query())->links()}}
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
             'paging': false,
      'lengthChange': false,
      'searching': false,
      'ordering': false,
    'info': false,
    // 'pageLength':25,
    // 'dom': 'Bfrtip',
    //   buttons: [ 'copy', 'csv', 'print','pdf']
    });
  });
</script>
@endsection
