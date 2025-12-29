@extends('layout.app')
@section('title')
    {{!empty($_GET['status']) ? ucwords(str_replace('_','',$_GET['status'])).' Loans' : ' Loans'}}   
@endsection
@section('pagetitle')
{{!empty($_GET['status']) ? ucwords(str_replace('_','',$_GET['status'])).' Loans' : ' Search Loans'}}   
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
                            @can('create loans')
                            <a href="{{route('loan.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Loan</a>
                            @endcan
                         </div>
                      </div>
                  <div class="panel-body">
                    <div class="noprint" style="margin-bottom: 15px">
                        <form action="{{route('loan.search')}}" method="get" onsubmit="thisForm()">
                          <input type="hidden" name="filter" value="true">
                          <table class="table table-bordered table-hover table-sm">
                            <thead>
                              <tr>
                                <th>Customer Name / Loan Code</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                              
                                <td>
                                  <div class="form-group">
                                    <input type="text" name="londetails" required id="" class="form-control" style="w" value="{{!empty($_GET['londetails']) ? $_GET['londetails'] : ''}}">
                                  </div>
                                </td>
                                                          
                                 <td>
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Search</button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </form>
                      </div>
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
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                     <th>District</th>
                                    <th>Principal ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                     <th>Interest ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    <th>Released</th>
                                    <th>Maturity</th>
                                    <th>Officer</th>
                                    <th>Principal Paid ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    <th>Interest Paid ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
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
                                @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                                <?php $i=0;?>

                                @inject('getloan', 'App\Http\Controllers\LoanController')

                                @foreach ($loans as $item)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{$item->loan_code}}</td>
                                    <td><a href="{{route('customer.view',['id' => $item->customer->id])}}">{{ucwords($item->customer->last_name." ".$item->customer->first_name)}}</a></td>
                                    <td>{{$item->customer->phone}}</td>
                                    <td>{{$item->customer->state}}</td>
                                    <td>{{number_format($item->principal)}}</td>
                                    <td>{{number_format($getloan->loan_total_interest($item->id))}}</td>
                                    <td>{{date("d-m-Y",strtotime($item->release_date))}}</td>
                                    <td>{{date("d-m-Y",strtotime($item->maturity_date))}}</td>
                                    <td>{{!is_null($item->accountofficer) ? $item->accountofficer->full_name : "N/A"}}</td>
                                    <td>{{number_format($getloan->loan_paid_item($item->id))}}</td>
                                    <td>{{number_format($getloan->loan_interest_paid_item($item->id))}}</td>
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
                                         @if($item->maturity_date < date("Y-m-d") && $getloan->loan_total_balance($item->id) > 0)
                                        <span class="label label-danger">Lost</span> 
                                    
                                        @elseif($item->status == 'pending')
                                             
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
