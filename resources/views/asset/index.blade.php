@extends('layout.app')
@section('title')
    Manage Assets
@endsection
@section('pagetitle')
Manage Assets
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                           <a href="{{route('assets.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Assets</a>
                        </div>
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                  @include('includes.success')
                    </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Asset Type</th>
                                    <th>Date Purchased</th>
                                    <th>Price Purchased</th>
                                    <th>Replacement Value</th>
                                    <th>Serial No</th>
                                    <th>Brought From</th>
                                    <th></th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($assets as $asset)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($asset->assettype->name)}}</td>
                                    <td>{{date('d/m/Y',strtotime($asset->purchase_date))}}</td>
                                    <td>{{number_format($asset->purchase_price)}}</td>
                                    <td>{{number_format($asset->replacement_value)}}</td>
                                    <td>{{$asset->initial."-".$asset->serial_number}}</td>
                                    <td>{{$asset->bought_from}}</td>
                                    <td style="width: 15%">
                                        <a href="{{route('assets.edit',['id' => $asset->id])}}" class="btn vd_btn vd_bg-blue btn-sm">Edit</a>
                                        <a href="{{route('assets.delete',['id' => $asset->id])}}" class="btn vd_btn vd_bg-red btn-sm" onclick="return confirm('are you sure you want to delete these record')">Delete</a>
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
  });
</script>
@endsection
