@extends('layout.app')
@section('title')
    Customers Transaction Details
@endsection
@section('pagetitle')
Customers Transaction Details
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                        <a href="{{route('savings.cutomers.balance')}}" class="btn btn-danger btn-sm"><span class="menu-icon"> </span>Back</a>
                     </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-offset-4 col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <?php
                    $getsetvalue = new \App\Models\Setting();
                    ?>
                     @inject('getloan', 'App\Http\Controllers\DepositmgmtController')
 
                     <div class="row">
                         <div class="col-md-4 col-lg-4 col-sm-12">
                             <div class="row">
                                 <div class="col-md-4 col-lg-4 col-sm-12">
                                     @if(!empty($customer->photo))
                                    <a href="{{asset($customer->photo)}}" class="fancybox"> <img
                                         class="img-responsive"
                                         width="90"
                                         height="90"
                                         src="{{asset($customer->photo)}}"
                                         alt="customer photo"/></a>
                                 @else
                                     <img class="img-circle"
                                         src="{{asset('img/avater.webp')}}"
                                         alt="customer photo"/>
                                 @endif
                                 </div>
                                 <div class="col-md-8 col-lg-8 col-sm-12" style="text-align:left;">
                                     <p style="font-size:13px;font-weight:700; color:#000000">
                                         Name: {{ucwords($customer->title." ".$customer->last_name." ".$customer->first_name)}}
                                 </p>
                                     <p style="font-size:13px;font-weight:700; color:#000000">
                                        Account Number: {{$customer->acctno}} 
                                     </p>
                                     @can('edit customer')
                                         <a href="{{route('customer.edit',['id' => $customer->id])}}" class="btn btn-info btn-sm">Edit Customer</a>
                                     @endcan
                                     <p style="font-size:13px;font-weight:700; color:#000000">Business name: {{$customer->business_name}}</p>
                                     <p style="font-size:13px;font-weight:700; color:#000000">Occupation: {{$customer->working_status}}</p>
                                    <p style="font-size:13px;font-weight:700; color:#000000">Gender: {{$customer->gender}}</p>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-4 col-lg-4 col-sm-12">
                             <p style="font-size:13px;font-weight:700; color:#000000">Phone: {{$customer->phone}}<br>
                                 <a href="javascript:void(0)" class="btn btn-danger btn-sm">Send Sms</a>
                             </p>
                             <p style="font-size:13px;font-weight:700; color:#000000">Email: {{$customer->email}}<br>
                              <a href="{{route('customers.emails.create',['id' => $customer->id])}}?sendmail=true" class="btn btn-danger btn-sm">Send Email</a>
                             </p>
                             <p style="font-size:13px;font-weight:700; color:#000000">Address: {{$customer->residential_address}}</p>
                             <p style="font-size:13px;font-weight:700; color:#000000">State: {{ucwords($customer->state)}}</p>
                             <p style="font-size:13px;font-weight:700; color:#000000">LGA: {{ucwords($customer->state_lga)}}</p>
                         </div>
                         <div class="col-md-4 col-lg-4 col-sm-12">
                             
                            <p style="font-size:13px;font-weight:700; color:#000000">Account Officer: {{!is_null($customer->accountofficer) ? $customer->accountofficer->full_name : "N/A"}}</p>
                            
                         </div>
                     </div>
                     <div style="text-align: end">
                            @can('print-download statement')
                                 <div class="btn-group">
                             <button type="button" class="btn vd_btn vd_bg-red dropdown-toggle" data-toggle="dropdown">Customer Statement<i class="fa fa-caret-down prepend-icon"></i> </button>
                             <ul class="dropdown-menu" role="menu">
                             <li><a href="javascript:void(0)" onclick="opendaterangeModal('{{route('saving.print_statement',['id' => $customer->id])}}')">Print Statement</a></li>
                             <li><a href="javascript:void(0)" onclick="opendaterangeModal('{{route('saving.pdf_statement',['id' => $customer->id])}}')">Download PDF Statement</a></li>
                             {{-- <li><a href="{{route('email.loan.statement',['id' => $loan->customer->id])}}">Email Statement</a></li> --}}
                             </ul>
                            </div> 
                            @endcan
                       </div> 
                         <hr>

                         <div class="box-body table-responsive no-padding">
                          <table id="acoff" class="table table-bordered table-striped table-condensed table-hover">
                                <thead>
                            <tr style="background-color: #FFF8F2">
                                <th><b>Sn</b></th>
                                <th><b>Transaction Date</b></th>
                                <th><b>Transaction</b></th>
                                <th><b>Description</b></th>
                                <th><b>Reference</b></th>
                                <th><b>Slip No</b></th>
                                <th><b>Status</b></th>
                                <th><b>Posted By</b></th>
                                <th style="text-align:right"><b>Debit({{!empty($customer->exrate) ? $customer->exrate->currency_symbol : $getsetvalue->getsettingskey('currency_symbol')}})</b></th>
                                <th style="text-align:right"><b>Credit({{!empty($customer->exrate) ? $customer->exrate->currency_symbol : $getsetvalue->getsettingskey('currency_symbol')}})</th>
                                <th style="text-align:right"><b>Balance({{!empty($customer->exrate) ? $customer->exrate->currency_symbol : $getsetvalue->getsettingskey('currency_symbol')}})</b></th>
                                {{-- <th style="text-align:center">Actions</th> --}}
                            </tr>
                        </thead>
                             <tbody>
                                 <?php $i=0; 
                                  $balance =0;
                                 ?>
                            @foreach($transactions as $key)
                                @if($key->status == 'pending' || $key->status == 'approved' || $key->status == 'failed')
                                    <tr>
                                    <td>{{ $i+1 }}</td>  
                                    <td>{{date("d M, Y",strtotime($key->created_at))." - ".date("h:ia",strtotime($key->created_at))}}</td> 
                                    
                                    <td> 
                                        @include('includes.trnx_type')            
                                    </td> 
                                                
                                      <td>
                                        {!!$key->notes!!}
                                    </td>
                                   <td>
                                        {{$key->reference_no}}
                                    </td>
                                     <td>
                                        {{$key->slip}}
                                    </td>
                                     <td>
                                       <a class="label {{$key->status == 'approved' ? 'label-success' : ($key->status == 'pending' ? 'label-warning' : 'label-danger' )}}">
                                           {{$key->status == 'approved' ? 'Successful' : ($key->status == 'pending' ? 'Pending' : $key->status )}}
                                       </a> 
                                    </td>
                                    <td>
                                        @if(!empty($key['user']))
                                        <a class="bg-navy blue btn-xs ">  
                                            {{$key->user->first_name." ".$key->user->last_name}}
                                            </a>
                                        @endif
                                    </td>
                                    @if($key->type=="deposit" || $key->type=="credit" || $key->type=="dividend" || $key->type=="interest" || $key->type=="fixed_deposit" || $key->type=="loan" || $key->type=="fd_interest" || $key->type=="rev_withdrawal" || $key->type == 'guarantee_restored')
                                        @if($key->status == 'approved')
                                         <?php $balance += $key->amount;?>
                                    <td style="text-align:right">
            
                                        </td>
                                        <td style="text-align:right">
                                            {{number_format($key->amount,2)}}
                                        </td>
                                        @else
                                          <?php $balance;?>
                                    <td style="text-align:right">
            
                                        </td>
                                        <td style="text-align:right">
                                            {{number_format($key->amount,2)}}
                                        </td>
                                      @endif
                                    @else
                                    @if($key->status == 'pending' || $key->status == 'declined')
                                     <?php $balance += 0;?>
                                      <td style="text-align:right">
                                            {{number_format($key->amount,2)}}
                                        </td>
                                        <td style="text-align:right">
                                            
                                        </td>
                                      @else
                                    <?php $balance -= $key->amount;?>

                                        <td style="text-align:right">
                                            {{number_format($key->amount,2)}}
                                        </td>
                                        <td style="text-align:right">
                                        </td>
                                    @endif
                                    @endif
                                    <td style="text-align:right">
                                        <b>{{number_format($balance,2)}}</b>
                                    </td>
                                    
                                </tr>
                              <?php $i++; ?>
                               @endif
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
  </div><!-- Modal -->
  <div class="modal fade" id="myprtrModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
   <div class="modal-dialog">
     <div class="modal-content">
       <div class="modal-header vd_bg-blue vd_white">
         <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
         <h4 class="modal-title" id="myModalLabel">Select Date Range</h4>
       </div>
       <form class="form-horizontal" action="" method="get" target="_blank" id="statemet">
       <div class="modal-body"> 
           <div class="row" style="width: 100%; margin: 0px 5px;">
            <div class="form-group col-sm-6 col-md-6 col-lg-6">
                <label>From Date</label>   
                <input type="date" name="fromdate" class="form-control">
              </div>
            <div class="form-group col-sm-6 col-md-6 col-lg-6">
                <label>To Date</label>   
                <input type="date" name="todate" class="form-control">
              </div>
           </div>
        </div>
       <div class="modal-footer background-login">
         <button type="button" class="btn vd_btn vd_bg-red" data-dismiss="modal">Close</button>
         <button type="submit" class="btn vd_btn vd_bg-green" id="btnssubmit">Search Record</button>
       </div>
       
    </form>
     </div>
     <!-- /.modal-content --> 
   </div>
   <!-- /.modal-dialog --> 
 </div>
 <!-- /.modal --> 



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

<script>
    function opendaterangeModal(url){
        $("#myprtrModal").modal('show');
        $("#statemet").attr('action',url);
    }
</script>
@endsection