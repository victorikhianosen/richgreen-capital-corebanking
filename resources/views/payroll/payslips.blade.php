@extends('layout.app')
@section('title')
    Payslips
@endsection
@section('pagetitle')
Payslips
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
                    <div class="noprint" style="margin-bottom: 15px">
                        <form action="{{route('payslips')}}" method="get" onsubmit="thisForm()">
                          <input type="hidden" name="filter" value="true">
                          <table class="table table-bordered table-hover table-sm">
                            <thead>
                              <tr>
                                <th colspan="2">Payment Period</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>
                                  <div class="form-group">
                                    <?php 
                                    $mntno = date('m');
                                        ?>
                                    <select name="month"  id="month" title="select month" style="cursor:pointer" required class="form-control">
                                        <option selected disabled>Select Month</option>
                                        <option value="01" {{$mntno == "01" ? "selected" : ""}}>JANUARY</option>
                                        <option value="02" {{$mntno == "02" ? "selected" : ""}}>FEBRUARY</option>
                                        <option value="03" {{$mntno == "03" ? "selected" : ""}}>MARCH</option>
                                        <option value="04" {{$mntno == "04" ? "selected" : ""}}>APRIL</option>
                                        <option value="05" {{$mntno == "05" ? "selected" : ""}}>MAY</option>
                                        <option value="06" {{$mntno == "06" ? "selected" : ""}}>JUNE</option>
                                        <option value="07" {{$mntno == "07" ? "selected" : ""}}>JULY</option>
                                        <option value="08" {{$mntno == "08" ? "selected" : ""}}>AUGUST</option>
                                        <option value="09" {{$mntno == "09" ? "selected" : ""}}>SEPTEMBER</option>
                                        <option value="10" {{$mntno == "10" ? "selected" : ""}}>OCTOBER</option>
                                        <option value="11" {{$mntno == "11" ? "selected" : ""}}>NOVEMBER</option>
                                        <option value="12" {{$mntno == "12" ? "selected" : ""}}>DECEMBER</option>
                                    </select>
                                  </div>
                                </td>
                                <td>
                                  <div class="form-group">
                                    <input type="text" name="year" id="year" placeholder="Enter Year" required class="form-control" value="{{date("Y")}}">                                  </div>
                                </td>
                                <td>
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Show Records</button>
                                  <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('payslips')}}'">Reset</button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </form>
                      </div>

                      <div style="padding:5px; margin:5px 3px; background-color:rgb(226, 226, 221);border-left:2px solid #17A2B8">
                        <h6 class="text-uppercase">Payslips</h6> 
                    </div>
                     @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                     <form action="{{route('payslip.send')}}" method="post">
                        @csrf
                        <input type="hidden" name="emailtype" value="multiple">
                        <div style="text-align: end">
                        <button type="submit" title="Send Payslip via Email" class="btn btn-success btn-sm" style="margin: 10px 0px"><i class="fa fa-envelop"></i> Email Multiple Payslips</button>
                        </div>
                        
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
                               <td></td>
                            </tr>
                          </thead>
                          <tbody>
                              <?php $i=0;?>
                            @foreach ($payslips as $item)
                              <tr>
                                <input type="hidden" name="payslipid[]" value="{{$item->id}}">
                                <td>{{$i+1}}</td>
                                <td>{{$item->payroll->employee_name}}</td>
                                <td>{{number_format($item->paymentstructure->basic,2)}}</td>
                                <td>{{number_format($item->paymentstructure->gross_pay,2)}}</td>
                                <td>{{number_format($item->paymentstructure->deduction,2)}}</td>
                                <td>{{number_format($item->paymentstructure->net_pay,2)}}</td>

                                <td>
                                    <a href="{{route('payslip.send')}}?emailtype=single&payid={{$item->id}}&month={{$_GET['month']}}&yr={{$_GET['year']}}" class="btn btn-danger btn-sm">Email Payslip</a>
                                    <a href="{{route('payslip.print.pdf')}}?payid={{$item->id}}month={{$_GET['month']}}&yr={{$_GET['year']}}" class="btn btn-primary btn-sm" target="_blank">Print Payslip</a>
                                </td>
                              </tr>
                               <?php $i++;?>
                            @endforeach
                          </tbody>
                        </table>
                    </div>
                    <input type="hidden" name="month"  value="{{$_GET['month']}}">
                        <input type="hidden" name="yr"  value="{{$_GET['year']}}">
                    </form>
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
      'pagelength': 25,
      'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
    });
  });
</script>
@endsection