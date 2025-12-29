@extends('layout.app')
@section('title')
    Manage Loan Product
@endsection
@section('pagetitle')
Manage Loan Product
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                           <a href="{{route('loan.product.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Loan Products</a>
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
                                    <th>Product Name</th>
                                    <th>Default Principal</th>
                                    <th>Minimum Principal</th>
                                    <th>Maximum Principal</th>
                                    <th></th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;
                                   $getsetvalue = new \App\Models\Setting();
                                 ?>
                                @foreach ($getproducts as $item)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($item->name)}}</td>
                                    <td align="right">{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($item->default_principal)}}</td>
                                    <td align="right">{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($item->minimum_principal)}}</td>
                                    <td align="right">{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($item->maximum_principal)}}</td>
                                    <td>
                                        
                                        <a href="{{route('loan.product.edit',['id' => $item->id])}}" class="btn vd_btn vd_bg-blue btn-sm">Edit</a>
                                        @if (Auth::user()->roles()->first()->name == "super admin" || Auth::user()->roles()->first()->name == "admin" || Auth::user()->roles()->first()->name == "managing director")
                                             <a href="{{route('loan.product.delete',['id' => $item->id])}}" class="btn vd_btn vd_bg-red btn-sm" onclick="return confirm('are you sure you want to delete these record')">Delete</a>
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
  });
</script>
@endsection
