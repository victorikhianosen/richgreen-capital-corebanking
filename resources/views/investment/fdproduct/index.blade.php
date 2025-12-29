@extends('layout.app')
@section('title')
    Manage Fixed Deposit Product
@endsection
@section('pagetitle')
Manage Fixed Deposit Product
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
                           <a href="{{route('create.fdproduct')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Fixed Deposit Products</a>
                        </div>
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                       @include('includes.success')
                    </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-condensed table-hover table-sm" id="acoff">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Product Name</th>
                                    <th>Interest(%)</th>
                                    <th>Interest Method</th>
                                    <th>Default Principal({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    <th>Minimum Principal({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    <th>Maximum Principal({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    <th></th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;
                                  
                                 ?>
                                @foreach ($fixproducts as $item)
                                <tr id="d{{$item->id}}">
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($item->name)}}</td>
                                    <td>{{ucwords($item->default_interest_rate)}}</td>
                                    <td>{{ucwords(str_replace("_"," ",$item->interest_method))}}</td>
                                    <td align="right">{{number_format($item->default_principal)}}</td>
                                    <td align="right">{{number_format($item->minimum_principal)}}</td>
                                    <td align="right">{{number_format($item->maximum_principal)}}</td>
                                    <td style="width:10%">
                                        
                                        <a href="{{route('edit.fdproduct',['id' => $item->id])}}" class="btn menu-icon vd_bd-blue vd_blue btn-sm"><i class="fa fa-pencil"></i></a>
                                        
                                        @if (Auth::user()->roles()->first()->name == "super admin" || Auth::user()->roles()->first()->name == "admin" || Auth::user()->roles()->first()->name == "managing director")
                                             <a href="javascript:void(0)" data-href="{{route('delete.fdproduct',['id' => $item->id])}}" data-id="{{$item->id}}" id="deletere" class="btn menu-icon vd_bd-red vd_red btn-sm"><i class="fa fa-times"></i></a>
                                        @endif

                                      
                                    </td>
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

   
@endsection
@section('scripts')
    <script type="text/javascript">
  $(document).ready(function(){
    $("#acoff").dataTable({'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });

  $("#deletere").click(function(e){
      let url = $("#deletere").data('href');
      let ids = $("#deletere").data('id');
      
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
    });
    
  });
</script>
@endsection
