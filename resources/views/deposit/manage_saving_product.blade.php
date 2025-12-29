@extends('layout.app')
@section('title')
    Savings Products   
@endsection
@section('pagetitle')
Savings Products   
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                          @can('create savings')
                           <a href="{{route('savings.product.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Savings Product</a>
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
                        <table class="table table-striped table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Product Name</th>
                                    <th>Product No</th>
                                    <th>Interest Rate Per Annum(%)</th>
                                    <th>Interest Posting Frequency</th>
                                    <th>Minimum Balance</th>
                                    <th></th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($sprods as $item)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($item->name)}}</td>
                                    <td>{{ucwords($item->product_number)}}</td>
                                    <td>{{ucwords($item->interest_rate)}}</td>
                                    <td>{{ucwords($item->interest_posting).' days'}}</td>
                                    <td>{{ucwords($item->minimum_balance)}}</td>
                                    <td>
                                      <div class="btn-group">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"> Action <i class="fa fa-caret-down prepend-icon"></i> </button>
                                        <ul class="dropdown-menu" role="menu">
                                          @can('edit savings')
                                          <li>
                                            <a href="{{route('savings.product.edit',['id' => $item->id])}}">Edit</a>
                                          </li>
                                          @endcan
                                          @can('delete savings')
                                          <li>
                                            <a href="{{route('savings.product.delete',['id' => $item->id])}}" onclick="return confirm('are you sure you want to delete these record')">Delete</a>
                                          </li>
                                          @endcan
                                      </ul>
                                    </div>
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
