@extends('layout.app')
@section('title')
    Customer Details
@endsection
@section('pagetitle')
Customer Details
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('customer.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <?php
                    $getsetvalue = new \App\Models\Setting();
                   ?>
                    @inject('getloan', 'App\Http\Controllers\CustomersController')
                  <div class="panel-body">
                    <div style="text-align: end;margin:15px 10;width:100%">
                      @can('edit customer')
                      <a href="{{route('customer.edit',['id' => $cutoms->id])}}" class="btn vd_btn vd_bg-blue"><span class="menu-icon"> <i class="fa fa-edit"></i> </span> Edit Profile</a>
                       @endcan
                      <a href="javascript:void(0)" class="btn btn-info" data-toggle="modal" data-target="#myModalloans"><span class="menu-icon"><i class="fa fa-briefcase"></i></span> View Loan</a>
                      <a href="javascript:void(0)" class="btn btn-primary" data-toggle="modal" data-target="#myModalloanrepay"><span class="menu-icon"><i class="fa fa-money"></i> </span> View Loan Repayment</a>
                      <a href="{{route('loan.create')}}?customerid={{$cutoms->id}}" class="btn btn-success"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Loan</a>
                      <div class="btn-group">
                        <button type="button" class="btn vd_btn vd_bg-red dropdown-toggle" data-toggle="dropdown"> Customer Statement<i class="fa fa-caret-down prepend-icon"></i> </button>
                        <ul class="dropdown-menu" role="menu">
                          <li><a href="{{route('customer.printstatement',['id' => $cutoms->id])}}" target="_blank">Print Statement</a></li>
                          <li><a href="{{route('customer.pdfdownloadstatement',['id' => $cutoms->id])}}" target="_blank">Download PDF Statement</a></li>
                          <li><a href="{{route('email.loan.statement',['id' => $cutoms->id])}}">Email Statement</a></li>
                        </ul>
                      </div> 
                    </div> 

                    <div style="text-align: end;margin:10px 0;">
                        <img src="{{asset($cutoms->photo)}}"  class="img-responsive" width="100" height="100" alt="image">
                     </div>
                    <div style="float:right;display:flex;margin:10px 0;">
                        
                       <h4>Daily Limit: {{!empty($cutoms->exrate) ? $cutoms->exrate->currency_symbol : "N"}}{{number_format($cutoms->transfer_limit,2)}}|</h4> <h4>Bal: {{!empty($cutoms->exrate) ? $cutoms->exrate->currency_symbol : "N"}}{{number_format($balance->account_balance,2)}}</h4>
                          
                     </div>
                     <table class="table table-bordered table-striped">
                        <tr><td>Full Name</td><td>{{$cutoms->title." ".$cutoms->last_name." ".$cutoms->first_name}}</td></tr>
                        <tr><td>Username</td><td>{{$cutoms->username}}</td></tr>
                        <tr><td>Email</td><td>{{$cutoms->email}}</td></tr>
                        <tr><td>Phone</td><td>{{$cutoms->phone}}</td></tr>
                        <tr><td>Gender</td><td>{{$cutoms->gender}}</td></tr>
                        <tr><td>Address</td><td>{{$cutoms->residential_address}}</td></tr>
                        <tr><td>DOB</td><td>{{$cutoms->dob}}</td></tr>
                        <tr><td>Religion</td><td>{{$cutoms->religion}}</td></tr>
                        <tr><td>Marital Status</td><td>{{$cutoms->marital_status}}</td></tr>
                        <tr><td>Country</td><td>{{$cutoms->country}}</td></tr>
                        <tr><td>State</td><td>{{$cutoms->state}}</td></tr>
                        <tr><td>Local Govt Area</td><td>{{$cutoms->state_lga}}</td></tr>
                        <tr><td>Next of Kin</td><td>{{$cutoms->next_kin}}</td></tr>
                        <tr><td>Next of Kin Adress</td><td>{{!empty($cutoms->kin_address) ? $cutoms->kin_address : 'N/A'}}</td></tr>
                        <tr><td>Next of Kin Phone</td><td>{{!empty($cutoms->kin_phone) ? $cutoms->kin_phone : 'N/A'}}</td></tr>
                        <tr><td>Next of Kin Relationship</td><td>{{!empty($cutoms->kin_relate) ? $cutoms->kin_relate : 'N/A'}}</td></tr>
                        <tr><td>Occupation</td><td>{{$cutoms->occupation}}</td></tr>
                        <tr><td>Means of Identification</td><td>{{$cutoms->means_of_id}}</td></tr>
                        <tr><td>Uploaded Identification</td><td>@if (!is_null($cutoms->upload_id))
                          <a href="{{asset($cutoms->upload_id)}}"><img src="{{asset($cutoms->upload_id)}}" width="80" height="80" alt=""></a>
                        @else
                            N/A
                        @endif</td></tr>
                        <tr><td>Business Name</td><td>{{!is_null($cutoms->business_name) ? $cutoms->business_name : 'N/A'}}</td></tr>
                        <tr><td>Working Status</td><td>{{$cutoms->working_status}}</td></tr>
                         <?php 
                           $actyp = \App\Models\SavingsProduct::select('name')->where('id',$cutoms->account_type)->first();
                         ?>
                        <tr><td>Account Type</td><td>{{$actyp->name}}</td></tr>
                        <tr><td>Account Number</td><td>{{$cutoms->acctno}}</td></tr>
                        <tr><td>Bank Verification Number(BVN)</td><td>{{$cutoms->bvn}}</td></tr>
                        <tr><td>Reference Account</td><td>{{$cutoms->refacct}}</td></tr>
                        <tr><td>Date Registered</td><td>{{date("d-M Y",strtotime($cutoms->reg_date))}}</td></tr>
                        <tr><td>Account Officer</td><td>{{!is_null($cutoms->accountofficer) ? $cutoms->accountofficer->full_name : 'N/A'}}</td></tr>
                        <tr><td>Signature</td><td><a href="{{asset($cutoms->signature)}}"><img src="{{asset($cutoms->signature)}}" width="80" height="80" alt=""></a></td></tr>
                     </table>
                </div>
                </div>
                <!-- Panel Widget --> 
              </div>
              <!-- col-md-12 --> 
            </div>
            <!-- row -->
  </div>


<!-- Modal view loans -->
<div class="modal fade" id="myModalloans" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header vd_bg-blue vd_white">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h4 class="modal-title" id="myModalLabel">Loans</h4>
      </div>
      <div class="modal-body"> 
        <div class="table-responsive">
          <table id="acoff" class="table table-bordered table-condensed table-hover">
              <thead>
              <tr style="background-color: #D1F9FF">
                  <th>S/N</th>
                  <th>Principal({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                  <th>Released</th>
                  <th>Interest(%)</th>
                  <th>Due({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                  <th>Paid({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                  <th>Balance({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                  <th>Purpose</th>
                  <th>Status</th>
                  <th>Action</th>
              </tr>
              </thead>
              <tbody>
                <?php $i=0;?>
              @foreach($cutoms->loans as $key)
                  <tr>

                      <td>{{$i+1}}</td>
                      <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($key->principal,2)}}</td>
                      <td>{{date("d M, Y",strtotime($key->release_date))}}</td>
                      <td>
                          {{number_format($key->interest_rate,2)}}%/{{$key->interest_period}}
                      </td>
                      <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_due_amount($key->id),2)}}</td>
                      <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_paid($key->id),2)}}</td>
                      <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loan_total_balance($key->id),2)}}</td>
                      <td>{{$key->purpose}}</td>
                      <td>
                          @if($key->maturity_date<date("Y-m-d") && $getloan->loan_total_balance($key->id)>0)
                              <span class="label label-danger">Past Maturity</span>
                          @else
                              @if($key->status=='pending')
                                  <span class="label label-warning">Pending Approval</span>
                              @endif
                              @if($key->status=='approved')
                                  <span class="label label-primary">Awaiting Disbursement</span>
                              @endif
                              @if($key->status=='disbursed')
                                  <span class="label label-success">Active</span>
                              @endif
                              @if($key->status=='declined')
                                  <span class="label label-danger">Declined</span>
                              @endif
                              @if($key->status=='withdrawn')
                                  <span class="label label-danger">Withdrawn</span>
                              @endif
                              @if($key->status=='written_off')
                                  <span class="label label-danger">Written Off</span>
                              @endif
                              @if($key->status=='closed')
                                  <span class="label label-success">Closed</span>
                              @endif
                              @if($key->status=='pending_reschedule')
                                  <span class="label label-warning">Pending Reschedule</span>
                              @endif
                              @if($key->status=='rescheduled')
                                  <span class="label label-info">Rescheduled</span>
                              @endif
                          @endif
                      </td>
                      <td>
                          <div class="btn-group">
                              <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                      data-toggle="dropdown" aria-expanded="false">Action <i class="fa fa-caret-down prepend-icon"></i>
                                  <span class="sr-only">Toggle Dropdown</span>
                              </button>
                              <ul class="dropdown-menu" role="menu">
                                @can('view loans')
                                <li>
                                    <a href="{{route('loan.show',['id' => $key->id])}}">Details</a>
                                  </li>
                                @endcan
                               
                                @can('update loans')
                                    <li>
                                  <a href="{{route('loan.edit',['id' => $key->id])}}">Edit</a>
                                </li>
                                @endcan
                                
                                @can('delete loans')
                                <li>
                                  <a href="{{route('loan.delete',['id' => $key->id])}}" onclick="return confirm('are you sure you want to delete these record')">Delete</a>
                                </li>
                                @endcan
                              </ul>
                          </div>
                      </td>
                  </tr>
                  <?php $i++;?>
              @endforeach
              </tbody>
          </table>
      </div>
      </div>
      
    </div>
    <!-- /.modal-content --> 
  </div>
  <!-- /.modal-dialog --> 
</div>
<!-- /.modal --> 

<!-- Modal view loan repayments -->
<div class="modal fade" id="myModalloanrepay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header vd_bg-blue vd_white">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h4 class="modal-title" id="myModalLabel">Loan Repayments</h4>
      </div>
      <div class="modal-body"> 
        <div class="box-body table-responsive">
          <table id="view-repayments"
                 class="table table-bordered table-condensed table-hover dataTable no-footer">
              <thead>
              <tr style="background-color: #D1F9FF" role="row">
                <th>Collection Date</th>
                <th>Collected By</th>
                <th>Amount</th>
                <th>Action</th>
                <th>Receipt</th>
              </tr>
              </thead>
              <tbody>
              @foreach($cutoms->repayments as $item)


                  <tr>
                      <td>{{date("d M, Y",strtotime($item->collection_date))}}</td>
                      <td>{{!empty($item->user) ? ucwords($item->user->last_name)." ".ucwords($item->user->first_name) : "N/A"}}</td>
                      <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($item->amount)}}</td>
                      <td>
                        @can('update repayments')
                        <a href="{{route('repay.edit',['id' => $item->id])}}" class="text-info">Edit</a>
                   @endcan
                   @can('delete repayments')
                       |  <a href="{{route('repay.delete',['id' => $item->id])}}" class="text-danger" onclick="return confirm('Are you sure you want to delete these record')">Delete</a>
                   @endcan
                      </td>
                      <td>
                        <a href="{{route('repay.print',['id' => $item->id])}}?loanid={{$item->loan_id}}" class="btn vd_btn vd_bg-twitter btn-sm" target="_blank">Print</a>
                        <a href="{{route('repay.pdf',['id' => $item->id])}}?loanid={{$item->loan_id}}" class="btn vd_btn vd_bg-red btn-sm" target="_blank">PDF</a>
                      </td>
                  </tr>
              @endforeach
              </tbody>
          </table>
      </div>
      </div>
      
    </div>
    <!-- /.modal-content --> 
  </div>
  <!-- /.modal-dialog --> 
</div>
<!-- /.modal --> 
@endsection
@section('scripts')
<script>
  $(document).ready(function(){
    $("#acoff").dataTable({
      'pageLength':25,
      'dom': 'Bfrtip',
        buttons: [ 'copy', 'csv', 'print','pdf']
    });
  });
</script>
@endsection