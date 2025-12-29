@extends('layout.app')
@section('title')
    Capital
@endsection
@section('pagetitle')
Manage Capitals
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
                             @can('create capital')
                           <a href="{{route('capital.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Capital</a>
                           @endcan
                        </div>
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                  @include('includes.success')
                    </div>
                    </div>
                    <div class="table-responsive">
                      <h5 class="text-center">Percentage / 100 x Total Capital Amount = Total Shared Amount</h5>
                        <table class="table table-striped table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Name</th>
                                    <th>Percentage(%)</th>
                                    <th>Total Capital Amount</th>
                                    <th>Total Shared Amount(from %)</th>
                                    <th></th>
                                 </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;
                                 $tper = 0;
                                 $tcap = 0;
                                 $tshare = 0;
                                ?>
                                @foreach ($capitals as $item)
                                <?php 
                                      $tot = $item->percentage / 100 * $getsetvalue->getsettingskey('company_capital');
                                      $tper += $item->percentage;
                                      $tshare += $tot;
                                    ?>
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($item->share_holder_name)}}</td>
                                    <td>{{$item->percentage}}%</td>
                                    <td>{{number_format($getsetvalue->getsettingskey('company_capital'),2)}}</td>
                                    <td>{{number_format($tot,2)}}</td>
                                    <td>
                                         @can('update capital')
                                        <a href="{{route('capital.edit',['id' => $item->id])}}" class="btn menu-icon vd_bd-blue vd_blue btn-sm"><i class="fa fa-pencil"></i> </a>
                                       @endcan
                                        @can('delete capital')
                                        <a href="{{route('capital.delete',['id' => $item->id])}}" class="btn menu-icon vd_bd-red vd_red btn-sm" onclick="return confirm('Are you sure you want to delete the record')"><i class="fa fa-times"></i> </a>
                                    @endcan
                                    </td>
                                </tr>
                                <?php $i++?>
                                @endforeach
                                <tr>
                                  <td></td>
                                  <td></td>
                                  <td><b>{{$tper}}%</b></td>
                                  <td><b>{{number_format($getsetvalue->getsettingskey('company_capital'),2)}}</b></td>
                                  <td><b>{{number_format($getsetvalue->getsettingskey('company_capital'),2)}}</b></td>
                                  <td></td>
                              </tr>
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
    $("#acoff").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
@endsection
