@extends('layout.app')
@section('title')
    {{!empty($_GET['status']) ? ucwords(str_replace('_','',$_GET['status'])).' Fixed Deposits' : ' Fixed Deposits'}}   
@endsection
@section('pagetitle')
{{!empty($_GET['status']) ? ucwords(str_replace('_','',$_GET['status'])).' Fixed Deposits' : 'Search Fixed Deposits'}}   
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
                      
                      </div>
                  <div class="panel-body">

                    <div class="noprint" style="margin-bottom: 15px">
                        <form action="{{route('fd.search')}}" method="get" onsubmit="thisForm()">
                          <input type="hidden" name="filter" value="true">
                          <table class="table table-bordered table-hover table-sm">
                            <thead>
                              <tr>
                                <th>Customer Name / Investment Code</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                              
                                <td>
                                  <div class="form-group">
                                    <input type="text" name="invdetails" required id="" class="form-control" style="w" value="{{!empty($_GET['csdetails']) ? $_GET['csdetails'] : ''}}">
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
                                    <th>Account No</th>
                                    <th>Phone</th>
                                     <th>District</th>
                                  <th>Principal ({{empty($getcbo) ? "N" : $getcbo->currency_symbol}})</th>
                                     <th>Interest ({{empty($getcbo) ? "N" : $getcbo->currency_symbol}})</th>
                                     <th>Interest Method</th>
                                    <th>Released</th>
                                    <th>Maturity</th>
                                    <th>Officer</th>
                                    <th>Fd Product</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>    
                            <tbody>
                                @if (!empty($_GET['filter']) && $_GET['filter'] == true)
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
