@extends('layout.app')
@section('title')
    Call Over Transaction Report
@endsection
@section('pagetitle')
Call Over Transaction Report
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
                      <h3>Callover Report For Period: {{date("d M, Y",strtotime($_GET['datefrom']))." To ".date("d M, Y",strtotime($_GET['dateto']))}}</h3>
                      @endif
                    </div>
                    </div>
                    <div class="noprint" style="margin-bottom: 15px">
                      <form action="{{route('report.callover')}}" method="get" onsubmit="thisForm()">
                        <input type="hidden" name="filter" value="true">
                        <input type="hidden" name="callovertype" value="{{!empty($_GET['callovertype']) ? $_GET['callovertype'] : ''}}">
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
                                  @if (!empty($_GET['callovertype']) && $_GET['callovertype'] == '2')
                                  <select name="status" id="sts" required class="form-control">
                                    <option>Select Approval Status</option>
                                    <option value="all" {{!empty($_GET['status']) && $_GET['status'] == "all"  ? "selected" : ""}}>All</option>
                                    <option value="approved" {{!empty($_GET['status']) && $_GET['status'] == "approved" ? "selected"  : ""}}>Approved</option>
                                    <option value="pending" {{!empty($_GET['status']) && $_GET['status'] == "pending" ? "selected"  : ""}}>Pending</option>
                                    <option value="declined" {{!empty($_GET['status']) && $_GET['status'] == "declined" ? "selected"  : ""}}>Decline</option>
                                  </select>
                                  </div>
                                      <div class="form-group">
                                          <div class="vd_checkbox checkbox-success">
                                          <input type="checkbox" value="1" name="gl" id="checkboxs" {{!empty($_GET['gl']) && $_GET['gl'] == "1" ? "checked" : ""}} autocomplete="off">
                                          <label for="checkboxs">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;General Ledger only</label>
                                        </div>
                                      </div>
                                  @endif
                                  
                                      @if (!empty($_GET['callovertype']) && $_GET['callovertype'] == '1')
                                    <div class="form-group">
                                      <select name="type" id="type" required class="form-control">
                                        <option>Select Call-Over Status</option>
                                        <option value="all" {{ !empty($_GET['type']) && $_GET['type'] == "all" ? "selected" : ""}}>All</option>
                                        <option value="deposit" {{!empty($_GET['type']) && $_GET['type'] == "deposit" ? "selected" : ""}}>Deposit/Credit</option>
                                        <!--<option value="fixed_deposit">Fixed Deposit</option>-->
                                        <!--<option value="investment">Investment</option>-->
                                        <!--<option value="inv_int">Investment Interest</option>-->
                                        <option value="rev_deposit" {{!empty($_GET['type']) && $_GET['type']  == "rev_deposit" ? "selected" : ""}}>Deposit Reversal</option>
                                        <option value="bank_fees" {{!empty($_GET['type']) && $_GET['type'] == "bank_fees" ? "selected" : ""}}>Bank Fees</option>
                                        <option value="esusu" {{!empty($_GET['type']) && $_GET['type'] == "esusu"  ? "selected" : ""}}>Esusu Charge</option>
                                        <option value="monthly_charge" {{!empty($_GET['type']) && $_GET['type'] == "monthly_charge" ? "selected" : ""}}>Monthly Charge</option>
                                        <option value="loan" {{!empty($_GET['type']) && $_GET['type'] == "loan" ? "selected" : ""}}>Loan Disburse</option>
                                        <option value="process_fee" {{!empty($_GET['type']) && $_GET['type'] == "process_fee" ? "selected" : ""}}>Management Fee</option>
                                        <option value="form_fees" {{!empty($_GET['type']) && $_GET['type'] == "form_fees" ? "selected" : ""}}>Loan Form</option>
                                        <option value="repayment" {{!empty($_GET['type']) && $_GET['type'] == "repayment" ? "selected" : ""}}>Loan Repayment</option>
                                        <option value="dividend" {{!empty($_GET['type']) && $_GET['type'] == "dividend" ? "selected" : ""}}>Dividend</option>
                                        <option value="interest" {{!empty($_GET['type']) && $_GET['type'] == "interest" ? "selected" : ""}}>Interest</option>
                                        <option value="fd_interest" {{!empty($_GET['type']) && $_GET['type'] == "fd_interest" ? "selected" : ""}}>Fixed Deposit Interest</option>
                                        <option value="withdrawal" {{!empty($_GET['type']) && $_GET['type'] == "withdrawal" ? "selected" : ""}}>Withdrawal/Debit</option>
                                        <option value="rev_withdrawal" {{!empty($_GET['type']) && $_GET['type'] == "rev_withdrawal" ? "selected" : ""}}>Reversal Withdrawal</option>
                                        <option value="guarantee" {{!empty($_GET['type']) && $_GET['type'] == "guarantee" ? "selected" : ""}}>Guarantee</option>
                                        <option value="guarantee_restored" {{!empty($_GET['type']) && $_GET['type'] == "guarantee_restored" ? "selected" : ""}}>Guarantee Restored</option>
                                        <option value="transfer_charge" {{!empty($_GET['type']) && $_GET['type'] == "transfer_charge" ? "selected" : ""}}>Transfer Charge</option>
                                      </select>
                                       </div>
                                  @endif
                               
                              </td>
                              <td>
                                <button type="submit" class="btn btn-success btn-sm" onclick="if(document.getElementById('type').value=""){return alert('please select status type');}elseif(document.getElementById('sts').value=""){return alert('please select status type');}" id="btnsetsubmit">Search Records</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.callover')}}?callovertype={{$_GET['callovertype']}}'">Reset</button>
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
                                  <th>Reference</th>
                                   <th>Slip No</th>
                                   <th>Posted by</th>
                                   @if(empty($_GET['gl']))
                                   <th>Approved by</th>
                                   @endif
                                   <th>Transaction</th>
                                  <th>Debit</th>
                                  <th>Credit</th>
                                  <th>Posting Date</th>
                                  <th>Tranx Date</th>
                                  {{-- <th>Action</th> --}}
                              </tr>
                          </thead>
                          <tbody>
                               <?php $i = 0; ?>
                          @foreach($data as $key)
                           <?php 
                             $glnme = App\Models\GeneralLedger::where('id',$key->general_ledger_id)->first();
                           ?> 
                              <tr>
                                   <td>{{ $i+1 }}</td>  
                                  <td>
                                      @if(!empty($key->customer))
                                          {{$key->customer->first_name}} {{$key->customer->last_name}}
                                          @else
                                          {{$glnme->gl_name}}
                                      @endif
                                  </td>
          
                                   <td>{{ !is_null($key->customer) ? $key->customer->acctno : (!empty($glnme) ? $glnme->gl_code : "N/A")}}</td>
                                  
                                      
                                       <td>{{$key->reference_no}} </td>
                                        <td>{{$key->slip}} </td>
                                        <td>
                                            {{!is_null($key->initiated_by) ? $key->initiated_by : "N/A"}}
                                      </td>
                                       @if(empty($_GET['gl']))
                                        <td>
                                            {{!is_null($key->approve_by) ? $key->approve_by : "N/A"}}
                                      </td>
                                       @endif
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
                                @if($key['type']=="deposit" || $key['type']=="investment"  || $key['type']=="dividend" || $key['type']=="interest" ||
                                  $key['type']=="credit" || $key['type']=="fixed_deposit" || $key['type']=="loan" || $key['type']=="fd_interest" 
                                  || $key['type']=="inv_int" || $key['type']=="rev_withdrawal" || $key['type'] == 'guarantee_restored')
                                          <td>
          
                                          </td>
                                          <td>
                                              {{number_format($key->amount,2)}}
                                          </td>
                                      @else
                                          <td>
                                              {{number_format($key->amount,2)}}
                                          </td>
                                          <td>
                                          </td>
                                      @endif
                                   <td>{{Date('d M Y',strtotime($key->created_at))}} </td>
                                  <td>{{Date('d M Y',strtotime($key->created_at))." - ".Date('h:ia',strtotime($key->created_at))}}</td>
                                      
                              </tr>
                              <?php $i++; ?>
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