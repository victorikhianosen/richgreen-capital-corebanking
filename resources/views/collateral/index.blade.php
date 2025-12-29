@extends('layout.app')
@section('title')
    Collateral
@endsection
@section('pagetitle')
Collateral
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <div class="table-responsive">
                        <table id="acoff" class="table table-bordered table-condensed table-hover">
                            <thead>
                            <tr style="background-color: #D1F9FF">
                                <th>Collateral Type</th>
                                <th>Name</th>
                                <th>Customer</th>
                                <th>Loan Code</th>
                                <th>Value</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($data as $key)
                                <tr>
                                    <td>{{ucwords($key->collateraltype->name)}}</td>
                                    <td>{{ ucwords($key->name) }}</td>
                                    <td>
                                       <a href="{{route('customer.view',['id' => $key->customer_id])}}"> {{$key->customer->first_name}} {{$key->customer->last_name}}</a>
                                    </td>
                                    <td><a href="{{route('loan.show',['id' => $key->loan_id])}}">{{!empty($key->loan) ? $key->loan->loan_code : "N/A" }}</a></td>
                                    
                                    <td>{{ number_format($key->value,2) }}</td>

                                    <td>{{str_replace("_"," ",$key->status)}}</td>
                                    
                                    <td>{{ date("d M, Y",strtotime($key->date)) }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                                    data-toggle="dropdown" aria-expanded="false">Action <i class="fa fa-caret-down prepend-icon"></i>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                @can('view collateral')
                                                    <li><a href="{{route('colla.show',['id' => $key->id])}}"><i class="fa fa-eye"></i> Details</a></li>
                                                @endcan
                                                @can('update collateral')
                                                    <li><a href="{{route('colla.edit',['id' => $key->id])}}"><i
                                                                    class="fa fa-edit"></i> Edit</a>
                                                    </li>
                                                @endcan
                                                @can('delete collateral')
                                                    <li><a href="{{route('colla.delete',['id' => $key->id])}}"
                                                           class="delete" onclick="return confirm('Are you sure you want to delete these record');"><i
                                                                    class="fa fa-trash"></i>Delete </a>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
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
      $("#acoff").dataTable({
      'pageLength':25,
      'dom': 'Bfrtip',
        buttons: [ 'copy', 'csv', 'print','pdf']
    });
    });
  </script>
@endsection