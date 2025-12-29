@extends('layout.app')
@section('title')
    {{!empty($_GET['status']) ? ucwords(str_replace('_','',$_GET['status'])).' Fixed Deposits' : 'All Fixed Deposits'}}   
@endsection
@section('pagetitle')
{{!empty($_GET['status']) ? ucwords(str_replace('_','',$_GET['status'])).' Fixed Deposits' : 'All Fixed Deposits'}}   
@endsection

<?php
 $getsetvalue = new \App\Models\Setting();
 
 $getcbo = !empty($_GET['fx_filter']) && $_GET['fx_filter'] != "Null" ?  \App\Models\Exchangerate::select('currency_symbol')->where('id',$_GET['fx_filter'])->first() : "";

 $stat = !empty($_GET['status']) ? "&status=".$_GET['status'] : "";
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
                                    <option {{empty($_GET['fx_filter']) ? "selected disabled" : ""}}>Filter FD FX Exchange</option>
                                  <option value="{{route('manage.fd')}}?fx_filter=Null{{$stat}}" {{!empty($_GET['fx_filter']) && $_GET['fx_filter'] == "Null"  ? "selected" : "" }}>Naira</option>
                                    @foreach ($exrate as $item)
                                        <option value="{{route('manage.fd')}}?fx_filter={{$item->id}}{{$stat}}" {{!empty($_GET['fx_filter']) && $_GET['fx_filter'] == $item->id  ? "selected" : "" }}>{{$item->currency}}</option>
                                    @endforeach
                                </select>
                            </div>
                          
                            <div class="col-sm-7">
                              <a href="{{ route('fd.export') }}{{!empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : ''}}" class="btn btn-primary btn-sm"><span class="menu-icon"> <i class="fa fa-file-excel-o"></i> </span> Export Excel</a>
                                @can('create fixed deposit')
                                <a href="{{route('create.fd')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Fixed Deposit</a>
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
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Account No</th>
                                    <th>Phone</th>
                                     <th>District</th>
                                  <th>Principal ({{empty($getcbo) ? "N" : $getcbo->currency_symbol}})</th>
                                     <th>Interest ({{empty($getcbo) ? "N" : $getcbo->currency_symbol}})</th>
                                     <th>Interest Method</th>
                                        <th>Withholding Tax</th>
                                    <th>Released</th>
                                    <th>Maturity</th>
                                    <th>Officer</th>
                                    <th>Fd Product</th>
                                    <th>Posted By</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>

                                @inject('getloan', 'App\Http\Controllers\InvestmentController')

                                @foreach ($fixds as $item)
                                <tr id="d{{$item->id}}">
                                    <td>{{$i+1}}</td>
                                    <td>{{$item->fixed_deposit_code}}</td>
                                    <td>
                                      @isset($item->customer)
                                         <a href="{{route('customer.view',['id' => $item->customer->id])}}">{{ucwords($item->customer->last_name." ".$item->customer->first_name)}}</a>
                                         @else
                                         N/A
                                     @endisset
                                    </td>
                                    <td>
                                      @isset($item->customer)
                                      <a href="{{route('saving.transaction.details',['id' => $item->customer->id])}}" title="view statement">{{$item->customer->acctno}}</a>
                                      @else
                                      N/A
                                  @endisset
                                    </td>
                                    <td>{{$item->customer->phone ?? "N/A"}}</td>
                                    <td>{{$item->customer->state ?? "N/A"}}</td>
                                    <td>{{number_format($item->principal)}}</td>
                                    <td>{{number_format($getloan->investment_total_interest($item->id))}}</td>
                                    <td>{{ucfirst($item->interest_method)}}</td>
                                     <td>{{$item->enable_withholding_tax == '1' ? $item->withholding_tax : '0'}}%</td>
                                    <td>{{date("d-m-Y",strtotime($item->release_date))}}</td>
                                    <td>{{date("d-m-Y",strtotime($item->maturity_date))}}</td>
                                    <td>{{optional($item->accountofficer)->full_name ?? "N/A" }}</td>
                                    
                                    <td>
                                      @isset($item->fixed_deposit_product)
                                      <a href="{{route('show.fd',['id' => $item->id])}}"> <span class="text-info">{{ $item->fixed_deposit_product->name }} </span> </a>
                                      @else
                                      N/A
                                  @endisset
                                    </td>
                                    <td>
                                       {{!is_null($item->user) ? $item->user->last_name." ".$item->user->first_name : ($item->system_approve == '1' ? 'system' : '')}}
                                    </td>
                                    <td>
                                      @switch($item->status)
                                      @case('pending')
                                          <a href="{{ route('show.fd', ['id' => $item->id]) }}">
                                              <span class="badge vd_bg-yellow">Pending Approval</span>
                                          </a>
                                          @break
                                      @case('approved')
                                          <span class="badge vd_bg-green">Active</span>
                                          @break
                                      @case('declined')
                                          <span class="badge vd_bg-red">Declined</span>
                                          @break
                                      @case('closed')
                                          <span class="badge vd_bg-black">Closed</span>
                                          @break
                                  @endswitch
                                               {{-- @if($item->status == 'pending')
                                                 <a href="{{route('show.fd',['id' => $item->id])}}">   <span class="badge vd_bg-yellow">Pending Approval</span> </a>
                                               @endif
                                                @if($item->status == 'approved')
                                                <span class="badge vd_bg-green">Active</span>
                                                @endif
                                              
                                               @if($item->status == 'declined')
                                                   <span class="badge vd_bg-red">Declined</span>
                                               @endif
                                              
                                               @if($item->status == 'closed')
                                                   <span class="badge vd_bg-black">Closed</span>
                                               @endif
                                               
                                            --}}
                                    </td>
                                   
                                    <td>
                                        <div class="btn-group">
                                          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"> Action <i class="fa fa-caret-down prepend-icon"></i> </button>
                                          <ul class="dropdown-menu" role="menu">
                                            @can('view fixed deposit')
                                            <li>
                                                <a href="{{route('show.fd',['id' => $item->id])}}">Details</a>
                                              </li>
                                            @endcan
                                            @if($item->status != 'closed')
                                            @can('edit fixed deposit')
                                                <li>
                                              <a href="{{route('edit.fd',['id' => $item->id])}}">Edit</a>
                                            </li>
                                            @endcan
                                            
                                            @can('delete fixed deposit')
                                            <li>
                                              <a href="javascript:void(0)" onclick="deleterecord('{{route('delete.fd',['id' => $item->id])}}','{{$item->id}}')">Delete</a>
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
                      {{$fixds->appends(request()->query())->links()}}
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

<script type="text/javascript">

  function deleterecord(url,ids){
    if(confirm('Are you sure you want to delete these record')){
        $.ajax({
        url: url,
        method: 'get',
        beforeSend:function(){
          $(".loader").css('visibility','visible');
          $(".loadingtext").text('Deleting...');
        },
        success:function(data){
          if(data.status == 'success'){
            $(".loader").css('visibility','hidden');
          toastr.success(data.msg);
          $("#d"+ids).remove();
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
      }  
  }
</script>
@endsection
