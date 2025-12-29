@extends('layout.app')
@section('title')
    View Transaction Reference
@endsection
@section('pagetitle')
View Transaction Reference
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
                        <h3>Slip or reference No: <b>{{$_GET['slipno']}}</b></h3>
                     
                        @endif
                      </div>
                      </div>
                      <div class="noprint" style="margin-bottom: 15px">
                        <form action="{{route('report.refsearch')}}" method="get" onsubmit="thisForm()">
                          <input type="hidden" name="filter" value="true">
                          <table class="table table-bordered table-hover table-sm">
                            <thead>
                              <tr>
                                <th>Slip or Reference No</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>
                                  <div class="form-group">
                                    <input type="text" name="slipno" required id="" placeholder="Slip or Reference No" class="form-control">
                                  </div>
                                </td>
                                
                                <td>
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Search Records</button>
                                  <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.refsearch')}}'">Reset</button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </form>
                      </div>
                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                      <div class="table-responsive">
                        <table id="researh" class="table table-bordered table-striped table-condensed table-hover table-sm">
                            <thead>
                            <tr style="background-color: #D1F9FF">
                                    <th>Sn</th>
                                    <th>Account Name</th>
                                    <th>Account No</th>
                                    <th>Transaction Date</th>
                                    <th>Reference</th>
                                    <th>Maturation date</th>
                                     <th>Interest (%)</th>
                                     <th>Slip No</th>
                                     <th>Posted by</th>
                                     <th>Transaction</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                 <?php $i = 0; ?>
                            @foreach($data as $key)
                                <tr>
                                     <td>{{ $i+1 }}</td>  
                                    <td>
                                        @if(!empty($key->customer))
                                        {{ucwords($key->customer->first_name." ".$key->customer->last_name)}}
                                        @endif
                                    </td>
            
                                     <td>{{ $key->customer->acctno }}</td>
                                    
                                    <td>{{date("d-m-Y",strtotime($key->created_at))}} </td> 
                                         <td>{{$key->reference_no}} </td>
                                         <td>{{!is_null($key->maturation_date) ? date("d-m-Y",strtotime($key->maturation_date)) : "N/A"}}</td>
                                         <td>{{!is_null($key->cust_int) ? $key->cust_int : "N/A"}} </td>
                                          <td>{{$key->slip}} </td>
                                          <td>
                                        @if(!empty($key->user))
                                            {{ucwords($key->user->first_name." ".$key->user->last_name)}}
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
                                  <td>
                                    <a href="javascript:void(0)" onclick="viewtranxdetails('{{route('tranxdetails')}}?ref={{$key->reference_no}}')" title="View Details" class="btn menu-icon vd_bd-blue vd_blue btn-sm"><i class="fa fa-eye"></i></a>
                                          {{--    <div class="btn-group">
                                                <button type="button" class="btn btn-primary btn-xs dropdown-toggle"
                                                        data-toggle="dropdown" aria-expanded="false">
                                                    {{ trans('general.choose') }} <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                    @if(Sentinel::hasAccess('savings.transactions.update'))
                                                        <li><a href="{{url('saving/'.$key->savings_id.'/savings_transaction/'.$key->id.'/edit')}}"><i
                                                                        class="fa fa-edit"></i> {{ trans('general.edit') }} </a>
                                                        </li>
                                                    @endif
                                                    @if(Sentinel::hasAccess('savings.transactions.delete'))
                                                        <li><a href="{{url('saving/'.$key->savings_id.'/savings_transaction/'.$key->id.'/delete')}}"
                                                               class="delete"><i
                                                                        class="fa fa-trash"></i> {{ trans('general.delete') }} </a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>--}}
                                        </td> 
                                        <?php $i++; ?>
                                </tr>
                            @endforeach
                           
                            </tbody>
                        </table>
            
                    </div>
                      @else
                      <div class="alert alert-info">Please Enter Slip or Reference No and click on search record button</div>
                  @endif
                  </div>
                </div>
                <!-- Panel Widget --> 
              </div>
              <!-- col-md-12 --> 
            </div>
            <!-- row -->
  </div>

  <!-- Modal -->
 <div class="modal fade" id="sdmyModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header vd_bg-blue vd_white">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h4 class="modal-title" id="myModalLabel">Ledger Details</h4>
      </div>
      <div class="modal-body"> 
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-condensed table-hover table-sm">
            <thead>
              <tr>
                <th>Account Name</th>
                <th>Account No</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Description</th>
              </tr>
            </thead>
              <tbody id="legdgerdetails">
              </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer background-login">
        <button type="button" class="btn vd_btn vd_bg-grey" data-dismiss="modal">Close</button>
      </div>
    
    </div>
    <!-- /.modal-content --> 
  </div>
  <!-- /.modal-dialog --> 
</div>
@endsection
@section('scripts')
<script type="text/javascript">
  $(document).ready(function(){
    $("#researh").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>

<script>

function viewtranxdetails(durl){
  let tabdata = "";
  $.ajax({
    url: durl,
    method: "get",
    beforeSend:function(){
      $(".loader").css('visibility','visible');
        $(".loadingtext").text('Please wait...');
    },
    success:function(data){
      if(data.status == 'success'){
          $(".loader").css('visibility','hidden');
        toastr.success(data.msg);
        $("#sdmyModal").modal("show");
       
            $("#legdgerdetails").html(data.data);
            console.log(tabdata);       
         
        }else{
          toastr.error(data.msg);
          $(".loader").css('visibility','hidden');
         return false;
         }
      },
      error:function(xhr,status,errorThrown){
          $(".loader").css('visibility','hidden');
          toastr.error('Error '+errorThrown);
        return false;
      }
    });

}

</script>
@endsection