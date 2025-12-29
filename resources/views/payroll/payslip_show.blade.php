@extends('layout.app')
@section('title')
    Payslips
@endsection
@section('pagetitle')
{{ucwords($payrol->employee_name)}} Payslips
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                      <a href="{{route('payroll.index')}}" class="btn btn-danger btn-sm"> < Back To Payroll List</a>
                   </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                        <div class="col-md-7 col-lg-7 col-sm-12">
                          @include('includes.errors')
                      @include('includes.success')
                        </div>
                        </div>
                    
                     @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                     
                         <div class="table-responsive">
                        <table class="table table-condensed table-bordered table-striped" id="payrl">
                          <thead>
                            <tr>
                              <th  class="text-uppercase">Sn</th>
                              <th class="text-uppercase">Name</th>
                              <th class="text-uppercase">Basic</th>
                               <th class="text-uppercase">Gross Pay</th>
                               <th class="text-uppercase">Total Deduction</th>
                               <th class="text-uppercase">Net Pay</th>
                               <th class="text-uppercase">Period</th>
                               <td></td>
                            </tr>
                          </thead>
                          <tbody>
                              <?php $i=0;?>
                            @foreach ($payslips as $item)
                              <tr>
                                <td>{{$i+1}}</td>
                                <td>{{$item->payroll->employee_name}}</td>
                                <td>{{number_format($item->paymentstructure->basic,2)}}</td>
                                <td>{{number_format($item->paymentstructure->gross_pay,2)}}</td>
                                <td>{{number_format($item->paymentstructure->deduction,2)}}</td>
                                <td>{{number_format($item->paymentstructure->net_pay,2)}}</td>
                                <?php 
                                 $dateobj = DateTime::createFromFormat('!m',$item->month);
                                    $monthName = $dateobj->format('F');
                                ?>
                                <td>{{$monthName." ".$item->year}}</td>
                                <td>
                                    <a href="{{route('payslip.send')}}?emailtype=single&payid={{$item->id}}&month={{$item->month}}&yr={{$item->year}}" class="btn btn-danger btn-sm">Email Payslip</a>
                                    <a href="{{route('payslip.print.pdf')}}?payid={{$item->id}}month={{$item->month}}&yr={{$item->year}}" class="btn btn-primary btn-sm" target="_blank">Print Payslip</a>
                                </td>
                              </tr>
                               <?php $i++;?>
                            @endforeach
                          </tbody>
                        </table>
                    </div>
                     @else
                         <div class="alert alert-info">Please select parameters and click the show record button</div>
                     @endif
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
      'pagelength': 25
    });
  });
</script>
@endsection