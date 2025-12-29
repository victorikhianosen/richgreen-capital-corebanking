@extends('layout.app')
@section('title')
    Posting Approval
@endsection
@section('pagetitle')
Posting Approval
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
                    <div class="col-md-10 col-lg-10 col-sm-12">
                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                      <h3>Approval Posting For Period: {{date("d M, Y",strtotime($_GET['datefrom']))." To ".date("d M, Y",strtotime($_GET['dateto']))}}</h3>
                   
                      <h4>Status Type: <b>{{!empty($_GET['type']) ? $_GET['type'] : $_GET['status']}}</b></h4>
                      @endif
                    </div>
                    </div>
                    <div class="noprint" style="margin-bottom: 15px">
                      <form action="{{route('report.postingapp')}}" method="get" onsubmit="thisForm()">
                        <input type="hidden" name="filter" value="true">
                        <table class="table table-bordered table-hover table-sm">
                          <thead>
                            <tr>
                              <th>From Date</th>
                              <th>To Date</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>
                                <div class="form-group">
                                  <input type="date" name="datefrom" required id="" class="form-control" value={{!empty($_GET['datefrom']) ? $_GET['datefrom'] : ""}}>
                                </div>
                              </td>
                              <td>
                                <div class="form-group">
                                  <input type="date" name="dateto" required id="" class="form-control" value={{!empty($_GET['dateto']) ? $_GET['dateto'] : ""}}>
                                </div>
                              </td>
                              <td>
                                <div class="form-group">
                                  <select name="status" id="sts" required class="form-control">
                                    <option>Select Approval Status</option>
                                    <option value="all"  {{!empty($_GET['status']) && $_GET['status'] == "all" ? "selected" : ""}}>All</option>
                                    <option value="declined" {{!empty($_GET['status']) && $_GET['status'] == "declined" ? "selected" : ""}}>Declined</option>
                                    <option value="approved" {{!empty($_GET['status']) && $_GET['status'] == "decline" ? "selected" : ""}}>Approved</option>
                                    <!--<option value="pending" {{!empty($_GET['status']) && $_GET['status'] == "pending" ? "selected" : ""}}>Pending</option>-->
                                  </select>
                                 
                                </div>
                              </td>
                              <td>
                                <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Search Records</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.postingapp')}}'">Reset</button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </form>
                    </div>
                    @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                    <div class="table-responsive">
                      <table id="callover" class="table table-bordered table-striped table-condensed table-hover">
                          <thead>
                          <tr style="background-color: #D1F9FF">
                                  <th>Sn</th>
                                  <th>Account Name</th>
                                  <th>Account No</th>
                                  <th>Transaction Date</th>
                                  <th>Posting Date</th>
                                  <th>Reference</th>
                                   <th>Slip No</th>
                                   <th>Posted by</th>
                                   <th>Transaction</th>
                                  <th>Debit</th>
                                  <th>Credit</th>
                                  {{-- <th>Action</th> --}}
                              </tr>
                          </thead>
                          <tbody>
                               <?php $i = 0; ?>
                          @foreach($data as $key)
                              <tr>
                                   <td>{{ $i+1 }}</td>  
                                  <td>
                                      @if(!empty($key->customer))
                                          {{$key->customer->first_name}} {{$key->customer->last_name}}
                                      @endif
                                  </td>
          
                                   <td>{{ $key->customer->acctno }}</td>
                                  <td>{{Date('d M Y',strtotime($key->created_at))." - ".Date('h:ia',strtotime($key->created_at))}}</td>
                                      <td>{{Date('d M Y',strtotime($key->created_at))}} </td>
                                       <td>{{$key->reference_no}} </td>
                                        <td>{{$key->slip}} </td>
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
                                       <a class="label label-primary">Credit</a>
                                      @endif
                                       @if($key->type =="debit")
                                       <a class="label label-primary">Debit</a>
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
                                           <span class="label label-warning">Inv. interest'</span>
                                          @endif
                                           @if($key->type=="fd_interest")
                                           <span class="label label-warning">FD ?Interest</span>
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
                                 @if($key->type=="deposit" || $key->type=="fixed_deposit" || $key->type=="dividend" || $key->type == 'inv_int' || $key->type == 'investment'|| $key->type=="fd_interest" || $key->type=="loan" || $key->type=="rev_withdrawal" || $key->type=="interest")
                                          <td style="text-align:right">
          
                                          </td>
                                          <td style="text-align:right">
                                              {{number_format($key->amount,2)}}
                                          </td>
                                      @else
                                          <td style="text-align:right">
                                              {{number_format($key->amount,2)}}
                                          </td>
                                          <td style="text-align:right">
                                          </td>
                                      @endif
                                      {{-- <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary btn-xs dropdown-toggle"
                                                    data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                @if($key->status=='pending')
                                                    @can('posting approved')
                                                        <li><a href="">Approve Trx</a></li>
                                                    @endcan
                                                @endif
                                  
                                            </ul>
                                        </div>
                                    </td> --}}
                                      <?php $i++; ?>
                              </tr>
                          @endforeach
                         
                          </tbody>
                      </table>
          
                  </div>
                  @else
                  <div class="alert alert-info">Please select a date range and click on search record button</div>
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
    $("#callover").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
@endsection