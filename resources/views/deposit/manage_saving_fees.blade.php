@extends('layout.app')
@section('title')
    Savings Fees   
@endsection
@section('pagetitle')
Savings Fees   
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                          @can('manage savings fees')
                           <a href="{{route('savings.fee.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Savings Fees</a>
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
                                    <th>Interest Posting Frequency</th>
                                    <th>Amount</th>
                                    <th></th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($sprods as $item)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($item->name)}}</td>
                                    <td>
                                      @if ($item->fees_posting == '8')
                                      One-time for new savings accounts only on account opening date
                                      @else
                                      Every {{$item->fees_posting }} Month
                                      @endif
                                    </td>
                                    <td>{{ucwords($item->amount)}}</td>
                                    <td>
                                      <div class="btn-group">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"> Action <i class="fa fa-caret-down prepend-icon"></i> </button>
                                        <ul class="dropdown-menu" role="menu">
                                          @can('manage savings fees')
                                          <li>
                                            <a href="{{route('savings.fee.edit',['id' => $item->id])}}">Edit</a>
                                          </li>
                                          @endcan
                                          @can('manage savings fees')
                                          <li>
                                            <a href="{{route('savings.fee.delete',['id' => $item->id])}}" onclick="return confirm('are you sure you want to delete these record')">Delete</a>
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
