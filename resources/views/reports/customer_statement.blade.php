@extends('layout.app')
@section('title')
    Generate Customer Statement
@endsection
@section('pagetitle')
Generate Customer Statement
@endsection
@section('content')
  <div class="container">
    <?php
    $getsetvalue = new \App\Models\Setting();
   ?>
    @inject('getloan', 'App\Http\Controllers\ReportsController')
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-10 col-lg-10 col-sm-12">
                        @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                        <h3>Customer Statement For Period: {{date("d M, Y",strtotime($_GET['datefrom']))." To ".date("d M, Y",strtotime($_GET['dateto']))}}</h3>
                        <h3>Account No: <b>{{$_GET['acctno']}}</b></h3>
                        <h3>Opening Balance: <b>{{number_format($custid,2)}}</b></h3>
                        @endif
                      </div>
                      </div>
                      <div class="noprint" style="margin-bottom: 15px">
                        <form action="{{route('report.customerstatement')}}" method="get" onsubmit="thisForm()">
                          <input type="hidden" name="filter" value="true">
                          <table class="table table-bordered table-hover table-sm">
                            <thead>
                              <tr>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th>Account No</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>
                                  <div class="form-group">
                                    <input type="date" name="datefrom" required id="" class="form-control" value="{{!empty($_GET['datefrom']) ? $_GET['datefrom'] : ''}}">
                                  </div>
                                </td>
                                <td>
                                  <div class="form-group">
                                    <input type="date" name="dateto" required id="" class="form-control" value="{{!empty($_GET['dateto']) ? $_GET['dateto'] : ''}}">
                                  </div>
                                </td>
                                <td>
                                  <div class="form-group">
                                    <input type="number" name="acctno" required placeholder="Enter Account No" class="form-control" value="{{!empty($_GET['acctno']) ? $_GET['acctno'] : ''}}">
                                  </div>
                                </td>
                                
                                <td>
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Generate Report</button>
                                  <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.customerstatement')}}'">Reset</button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </form>
                      </div>
                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                      <div class="box-body table-responsive no-padding">
                        <table id="custmer" class="table table-bordered table-striped table-condensed table-hover table-sm">
                            <thead>
                            <tr style="background-color: #D1F9FF">
                                 <th>Sn</th>
                                    <th>Transaction Date</th>
                                    <th>Posting Date</th>
                                <th>Description</th>
                                <th>Reference</th>
                                     <th>Posted by</th>
                                     <th>Transaction</th>
                                     <th>Status</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th>Balance ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                    {{-- <th>Action</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                 <?php
                                  $i = 0; 
                                 $balance = $custid;
                                 ?>
                                 
                            @foreach($data as $key)
                                @if($key->status == 'pending' || $key->status == 'approved' || $key->status == 'failed')
                                <tr>
                                     <td>{{ $i+1 }}</td>  
                                        <td>{{date("d-m-Y",strtotime($key->created_at))." at ".date("h:ia",strtotime($key->created_at))}}</td>
                                        <td>{{date("d-m-Y",strtotime($key->created_at))}} </td>
                                         <td>{!!!is_null($key->notes) ? $key->notes : "N/A"!!}</td>
                                         <td>{{$key->reference_no}} </td>
                                          <td>
                                        @if(!empty($key->user))
                                        {{$key->user->first_name}} {{$key->user->last_name}}
                                        @endif
                                    </td>
                                    <td>
                                      @if($key->type =="deposit")
                                        <span class="label label-success">Deposit</span>
                                      @endif
                                       @if($key->type =="fixed_deposit")
                                        <span class="label label-success">Fixed Deposit</span>
                                      @endif
                                       @if($key->type =="repayment")
                                   <a class="label label-warning">Loan Repayment</a>
                                  @endif
                                   @if($key->type =="credit")
                                   <a class="label label-success">Credit</a>
                                  @endif
                                   @if($key->type =="debit")
                                   <a class="label label-danger">Debit</a>
                                  @endif
                                      @if($key->type =="investment")
                                        <span class="label label-primary">Investment</span>
                                      @endif
                                       @if($key->type=="withdrawal")
                                        <span class="label label-danger">Withdrawal</span>
                                      @endif
                                       {{-- @if($key->type=="esusu")
                                        <span class="label label-info">Esusu Charge</span>
                                      @endif --}}
                                      @if($key->type=="monthly_charge")
                                        <span class="label label-primary">Monthly Charge</span>
                                      @endif
                                       @if($key->type=="esusu" || $key->type=="transfer_charge")
                                   <span class="label label-primary">Transfer Charge</span>
                                  @endif
                                      @if($key->type=="bank_fees")
                                          <span class="label label-info">Bank Fee</span>
                                      @endif
                                      @if($key->type=="dividend")
                                          <span class="label label-warning">Dividend</span>
                                      @endif
                                      @if($key->type=="interest")
                                       <span class="label label-warning">Interest</span>
                                      @endif
                                      @if($key->type=="inv_int")
                                       <span class="label label-warning">Inv. interest</span>
                                      @endif
                                       @if($key->type=="fd_interest")
                                       <span class="label label-warning">FD Interest</span>
                                      @endif
                                       @if($key->type=="form_fees")
                                       <span class="label label-danger">Loan Form </span>
                                  @endif
                                   @if($key->type=="process_fees")
                                       <span class="label label-danger">Process Fee</span>
                                  @endif
                                       @if($key->type=="loan")
                                      <span class="label label-success">Loan Disbursed</span>
                                  @endif 
                                  @if($key->type=="wht")
                                      <span class="label label-info">Withholding Tax</span>
                                  @endif
                                       @if($key->type=="rev_withdrawal")
                                       <span class="label label-info">Withdrawal Reversed</span>
                                      @endif 
                                       @if($key->type=="rev_fixed_deposit")
                                       <span class="label label-info">Fixed Deposit Reversed</span>
                                      @endif 
                                      
                                       @if($key->type=="rev_deposit")
                                      <span class="label label-info">Deposit Reversed</span>
                                      @endif 
                                  </td>

                                  <td>
                                       <a class="label {{$key->status == 'approved' ? 'label-success' : ($key->status == 'pending' ? 'label-warning' : 'label-danger' )}}">
                                           {{$key->status == 'approved' ? 'Successful' : ($key->status == 'pending' ? 'Pending' : ucfirst($key->status) )}}
                                       </a> 
                                  </td>

                                 @if($key->type=="deposit" || $key->type=="credit" || $key->type=="dividend" || $key->type=="interest" || $key->type=="fixed_deposit" || $key->type=="loan" || $key->type=="fd_interest" || $key->type=="rev_withdrawal" || $key->type == 'guarantee_restored' && $key->status == 'approved')
                                    
                                  @if($key->status == 'approved')
                                         <?php $balance += $key->amount;?>
                                    <td>
            
                                        </td>
                                        <td>
                                            {{number_format($key->amount,2)}}
                                        </td>
                                        @else
                                          <?php $balance;?>
                                    <td>
            
                                        </td>
                                        <td>
                                            {{number_format($key->amount,2)}}
                                        </td>
                                      @endif
                             @else
                              @if($key->status == 'pending' || $key->status == 'declined')
                                     <?php $balance += 0;?>
                                      <td>
                                            {{number_format($key->amount,2)}}
                                        </td>
                                        <td>
                                            
                                        </td>
                                      @else
                             <?php $balance -= $key->amount;?>
                                 <td>
                                     {{number_format($key->amount,2)}}
                                 </td>
                                 <td>
                                 </td>
                             @endif
                             @endif
                                         <td>
                                        <b>{{number_format($balance,2)}}</b>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                                @endif
                                
                                
                            @endforeach
                          
                            </tbody>
                        </table>
            
                    </div>
                      @else
                      <div class="alert alert-info">Please select a date range,enter customer account numner  and click on generate report button</div>
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
<script type="text/javascript">
  $(document).ready(function(){
    $("#custmer").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
  //  buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
<script>
  //  function printsection() {
  //   document.getElementById("noprint").style.display='none';
  // var divContents = document.getElementById("printdiv").innerHTML;
  // var a = window.open('', '', 'height=500, width=500');
  // a.document.write('<html>');
  // a.document.write('<body >');
  // a.document.write(divContents);
  // a.document.write('</body></html>');
  // a.document.close();
  // a.print();
  // }
</script>
@endsection