@extends('layout.app')
@section('title')
    Manage Payroll
@endsection
@section('pagetitle')
Manage Payroll
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                      @can('create payroll')
                      <a href="{{route('payslip.generate')}}" class="btn btn-danger btn-sm">Generate Payslips</a>
                      <a href="{{route('payment.structure')}}" class="btn btn-primary btn-sm">Manage Payment Structure</a>
                      <a href="{{route('payroll.create')}}" class="btn btn-default btn-sm"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add New Payroll</a>
                      @endcan
                   </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                     <div class="table-responsive">
                      <table class="table table-condensed table-bordered table-striped" id="payrl">
                        <thead>
                          <tr>
                            <th>Employee Name</th>
                            <th>Email</th>
                            <th>Designation</th>
                            <th>Payment Method</th>
                            <th>Bank</th>
                            <th>Net Pay</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($payrolls as $item)
                              <tr>
                                <td>{{ucwords($item->employee_name)}}</td>
                                <td>{{$item->email}}</td>
                                <td>{{ucwords($item->designation)}}</td>
                                <td>{{ucwords($item->payment_method)}}</td>
                                <td>{{ucwords($item->bank_name)}}</td>
                                <td>
                                  @foreach ($item->payment_structures as $netpy)
                                      {{number_format($netpy->net_pay,2)}}
                                  @endforeach
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"> Action <i class="fa fa-caret-down prepend-icon"></i> </button>
                                    <ul class="dropdown-menu" role="menu">
                                      @can('view payslip')
                                      <li>
                                          <a href="{{route('payslip.view')}}?filter=true&payid={{$item->id}}">View Payslips</a>
                                        </li>
                                      @endcan
                                     
                                      @can('update payroll')
                                          <li>
                                        <a href="{{route('payroll.edit',['id' => $item->id])}}">Edit</a>
                                      </li>
                                      @endcan
                                      
                                      @can('delete payroll')
                                      <li>
                                        <a href="{{route('payroll.delete',['id' => $item->id])}}" onclick="return confirm('are you sure you want to delete these record')">Delete</a>
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
<script>
  $(document).ready(function(){
    $("#payrl").dataTable({
      'pagelength': 25,
      'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
    });
  });
</script>
@endsection