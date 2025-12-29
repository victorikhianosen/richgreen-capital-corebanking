@extends('layout.app')
@section('title')
    Create Repayments
@endsection
@section('pagetitle')
Create Repayments
@endsection
@section('content')
  <div class="container">
    <?php 
    $getsetvalue = new \App\Models\Setting();
    ?>
    @inject('getloan', 'App\Http\Controllers\LoanController')

    <div class="row" id="advanced-input">
              <div class="col-md-12">
               
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('repay.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div> 
                    <div style="display: flex; justify-content:center;margin-bottom:10px">
                      <div class="col-md-6 col-lg-6 col-sm-12">
                          <form>
                            <div class="form-group">
                              <label>Loan Customer & Amount</label>
                               <select class="form-control select2" id="fixd" onchange="if(this.value!=0){window.location.href='{{route('repay.create')}}?lcode='+this.value}else{document.getElementById('txtfd').textContent='Please select an account'}">
                                <option value="0">Select A Loan Customer</option> 
                                @foreach ($loans as $loan)
                                <option value="{{$loan->id}}">{{$loan->customer->last_name." ".$loan->customer->first_name.' [Code: '.$loan->loan_code.', Due: '.number_format($getloan->loan_total_balance($loan->id)).']'}}</option>
                                @endforeach
                              </select>
                            </div>
                          </form>
                          <p id="txtfd" style="color: orangered"></p>
                      </div>
                    </div>

                    @if (!empty($_GET['lcode']))
                                             
                    <h4>Loan Details</h4>
                    <div class="table-responsive">
                      <table class="table table-striped table-sm table-bordered table-condensed table-hover">    
                          <tbody>
                            <tr>
  
                              <td width="200">
                                  <b>Loan Code</b>
                              </td>
                              <td>{{$lcd->loan_code}}</td>

                          </tr>
                           <tr>
  
                              <td width="200">
                                  <b>Customer Name</b>
                              </td>
                              <td>{{$lcd->customer->last_name." ".$lcd->customer->first_name}}</td>

                          </tr>
                           <td width="200">
                                  <b>Investment Officer</b>
                              </td>
                              <td>{{!is_null($lcd->accountofficer) ? $lcd->accountofficer->full_name : "N/A"}}</td>

                          </tr>
                          <tr>
                              <td>
                                  <b>Loan Product</b>
                              </td>
                              <td>
                                  @if(!empty($lcd->loan_product))
                                      {{ucwords($lcd->loan_product->name)}}
                                  @endif
                              </td>
                          </tr>
                          
                          <tr>

                              <td>
                                  <b>Loan Amount</b>
                              </td>
                              <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($lcd->principal,2)}}</td>

                          </tr>
                         
                          <tr>
                              <td>
                                  <b>interest method</b>
                              </td>
                              <td>
                                 {{str_replace("_"," ",ucwords($lcd->interest_method))}}
                              </td>
                          </tr>
                          <tr>
                              <td>
                                  <b>Loan interest</b>
                              </td>
                              <td>{{number_format($lcd->interest_rate)}}% / {{ucwords($lcd->interest_period)}}
                              </td>
                          </tr>
                          <tr>
                              <td>
                                  <b>Loan duration</b>
                              </td>
                              <td>{{$lcd->loan_duration}} {{ucwords($lcd->loan_duration_type)."(s)"}}
                              </td>
                          </tr>
                          <tr>
                              <td><b>Repayment cycle</b></td>
                              <td>
                                 {{str_replace("_"," ",ucfirst($lcd->repayment_cycle))}}
                              </td>
                          </tr>
                          <tr>
                            <td><b>Disbursed by</b></td>
                            <td>
                               {{!is_null($lcd->loan_disbursed) ? ucwords($lcd->loan_disbursed->last_name." ".$lcd->loan_disbursed->first_name) : ''}}
                            </td>
                        </tr>
                          <tr>
                            <td><b>Approved By</b></td>
                            <td>
                               {{!is_null($lcd->loan_approved) ? ucwords($lcd->loan_approved->last_name." ".$lcd->loan_approved->first_name) : ''}}
                            </td>
                        </tr>
                       
                          </tbody>
                      </table>
                  </div>
                  <h4>Loan Schedule Details</h4>
                      <div class="table-responsive">
                        <table class="table table-bordered table-condensed table-hover">
                            <tbody>
                            <tr style="background-color: #F2F8FF">
                                <th style="width: 10px">
                                    <b>Sn</b>
                                </th>
                                <th>
                                    <b>Date</b>
                                </th>
                                <th>
                                    <b>Description</b>
                                </th>
                                <th style="text-align:right;">
                                    <b>Principal</b>
                                </th>
                                <th style="text-align:right;">
                                    <b>Interest</b>
                                </th>
                                <th style="text-align:right;">
                                    <b>Fee</b>
                                </th>
                                <th style="text-align:right;">
                                    <b>Penalty</b>
                                </th>
                                <th style="text-align:right;">
                                    <b>Due</b>
                                </th>
                                <th style="text-align:right;">
                                    Total Due
                                </th>
                                <th style="text-align:right;">
                                   Paid
                                </th>
                                <th style="text-align:right;">
                                    Pending Due
                                </th>
                                <th style="text-align:right;">
                                   Status
                                </th>
                                <td></td>
                            </tr>
                            
                            <?php
                             $totpr = 0;
                            $totint = 0;
                            $totfee = 0;
                             $totpen = 0;
                            $count = 0;
                            $total_due = 0;
                            $principal_balance = \App\Models\LoanSchedule::where('loan_id',
                                $lcd->id)->sum('principal');

                            foreach ($schedules as $schedule) {
                            $principal_balance = $principal_balance - $schedule->principal;
                            if ($count == 1) {
                                $total_due = ($schedule->principal + $schedule->interest + $schedule->fees + $schedule->penalty);

                            } else {
                                $total_due = $total_due + ($schedule->principal + $schedule->interest + $schedule->fees + $schedule->penalty);
                            }

                            $getrepamt = \App\Models\LoanRepayment::where('loan_id',$lcd->id)->where('due_date',$schedule->due_date)->sum('amount')
                            ?> 
                            <tr class="@if((($schedule->principal+$schedule->interest+$schedule->fees+$schedule->penalty) - $getrepamt)<=0) success @endif">
                                <td>
                                    {{$count+1}}
                                </td>
                                <td>
                                    {{date("d-m-Y",strtotime($schedule->due_date))}}
                                </td>
                                <td>
                                    {{$schedule->description}}
                                </td>
                                <td style="text-align:right">
                                    <?php
                                     $totpr += $schedule->principal;
                                    ?>
                                    {{number_format($schedule->principal,2)}}
                                </td>
                                <td style="text-align:right">
                                      <?php
                                    $totint += $schedule->interest;
                                 ?>
                                    {{number_format($schedule->interest,2)}}
                                </td>
                                <td style="text-align:right">
                                        <?php
                                  $totfee += $schedule->fees;
                               ?>
                                    {{number_format($schedule->fees,2)}}
                                </td>
                                <td style="text-align:right">
                                      <?php
                                  $totpen += $schedule->penalty;
                               ?>
                                    {{number_format($schedule->penalty,2)}}
                                </td>
                                <td style="text-align:right; font-weight:bold">
                                    {{number_format(($schedule->principal+$schedule->interest+$schedule->fees+$schedule->penalty),2)}}
                                </td>
                                <td style="text-align:right;">
                                    {{number_format($total_due,2)}}
                                </td>
                                <td style="text-align:right;">
                                    {{number_format($getrepamt,2)}}
                                </td>
                                <td style="text-align:right;">
                                    <?php
                                    $gettotal = ($schedule->principal+$schedule->interest+$schedule->fees+$schedule->penalty)- $getrepamt;    
                                    ?>
                                    {{number_format($gettotal,2)}}
                                </td>
                                <td style="text-align:right;">
                                  @if ($schedule->closed == '0')
                                  <span class="label label-success">Active</span>
                                  @else
                                  <span class="label label-danger">Closed</span>
                                  @endif
                                </td>
                                <td>
                                  @if ($schedule->closed == '0')
                                  <a href="javascript:void(0)" onclick="openmkpay('{{$schedule->principal}}','{{$schedule->interest}}','{{$schedule->fees}}','{{$schedule->id}}','{{$schedule->due_date}}')" class="btn vd_btn vd_bg-green vd_white btn-sm">Make Payment</a>
                                  @endif
                                </td>
                            </tr>
                            <?php
                            $count++;
                            }
                            ?>
                            <tr>
                                <td></td>
                                <td></td>
                                <td style="font-weight:bold">Total Due</td>
                                    <td style="text-align:right;">
                                    {{number_format($totpr,2)}}
                                </td>
                                <td style="text-align:right;">
                                  {{number_format($totint,2)}}
                                </td>
                                <td style="text-align:right;">
                                  {{number_format($totfee,2)}}
                                </td>
                                <td style="text-align:right;">
                                    {{number_format($totfee,2)}}
                                </td>
                                <td style="text-align:right; font-weight:bold">
                                    {{number_format($totpr + $totint + $totfee,2)}}
                                </td>
                            </tr>
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

  <div class="modal fade" id="reLoan">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header vd_bg-blue vd_white">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Loan Payment</h4>
            </div>
            <?php 
            $customeracct = \App\Models\Saving::select('account_balance')->where('customer_id',$lcd->customer_id)->first();
            $outloan = \App\Models\OutstandingLoan::select('amount')->where('loan_id',$lcd->id)->first();
           ?>
            <form class="form-horizontal"  action="{{route('repay.store')}}" method="post" role="form" id="mkrepaymeny">
              @csrf
            <div class="modal-body">
               <div class="container">

                <div class="form-group">
                  <label for="">Principal</label>
                  <input type="number" name="principal" readonly class="form-control" onkeyup="payble()" required id="prinpl" value="">
              </div>
              <div class="form-group">
                  <label for="">Interest</label>
                  <input type="number" name="interest" readonly class="form-control" required id="intr" value="">
              </div>
              <div class="form-group">
                  <label for="">Loan Fee</label>
                  <input type="number" name="fee"  readonly class="form-control" required id="fee" value="">
              </div>
              <div class="form-group">
                  <label for="">Outstanding Loan ({{!empty($outloan->amount) ? number_format($outloan->amount,2) : '0'}})</label>
                  <input type="number" name="outstanding_loan"  readonly class="form-control" required id="outsnd" value="{{!empty($outloan->amount) ? $outloan->amount : '0'}}">
              </div>
               </div>

              <h4>Amount Payable: <span class="amtpyble"></span></h4>
              <h4>Customer Balance: {{number_format($customeracct->account_balance,2)}}</h4>
            <input type="hidden" name="customerid" autocomplete="off" id="cusid" class="form-control" value="{{$lcd->customer_id}}">
            <input type="hidden" name="loanid" autocomplete="off" class="form-control" value="{{$lcd->id}}">
            <input type="hidden" name="schduleid" autocomplete="off" id="schdid" class="form-control" value="">
            <input type="hidden" name="duedate" autocomplete="off" id="dtd" class="form-control" value="">
            <input type="hidden" name="payable" autocomplete="off" id="payable" class="form-control" value="">

            </div>
            
            <div class="modal-footer background-login">
                <button type="submit" class="btn btn-success btn-sm" id="dbtnssubmit">Continue</button>
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endif
@endsection
@section('scripts')
<script>
  function openmkpay(pr,inr,fe,sid,dt){
    $("#reLoan").modal('show');
    $("#intr").val(inr);
    $("#prinpl").val(pr);
    $("#payable").val(pr);
    $("#fee").val(fe);
    $("#schdid").val(sid);
    $("#dtd").val(dt);

    let outsndpy = $("#outsnd").val();

    let totl = parseFloat(pr) + parseFloat(inr) + parseFloat(fe) + parseFloat(outsndpy);
    $(".amtpyble").text(Number(totl).toLocaleString('en'));
  }

  function payble(){
    let prnc = $("#prinpl").val();
    let intert = $("#intr").val();
   let fe = $("#fee").val();

   let outsndpy = $("#outsnd").val();

   let totl = parseFloat(prnc) + parseFloat(intert) + parseFloat(fe) + parseFloat(outsndpy);
    $(".amtpyble").text(Number(totl).toLocaleString('en'));
  }
</script>

<script>
  $(document).ready(function(){
    $(".select2").select2();
   
    $("#mkrepaymeny").submit(function(e){
     e.preventDefault();
      $.ajax({
        url:$("#mkrepaymeny").attr('action'),
        method:"post",
        data:$("#mkrepaymeny").serialize(),
        beforeSend:function(){
          $("#dbtnssubmit").text('please wait...');
          $("#dbtnssubmit").attr('disabled',true);
        },
        success:function(data){
          if(data.status === 'success'){
            $("#dbtnssubmit").text('Continue');
          $("#dbtnssubmit").attr('disabled',false);
            toastr.success(data.msg);
            $("#reLoan").modal('hide');
              window.location.reload();
          }else{
            $("#dbtnssubmit").text('Continue');
          $("#dbtnssubmit").attr('disabled',false);
            toastr.error(data.msg);
            return false;
          }
        },
        error:function(xhr,status,errorThrown){
          $("#dbtnssubmit").text('Continue');
          $("#dbtnssubmit").attr('disabled',false);
          toastr.error('An Error Occured... '+errorThrown);
          return false;
        }
      })
    });

    $("#addnewrow").click(function(){
            let exp = new Date().getTime();
            let trow = '<tr id="ttrow'+exp+'"><td><select class="form-control width-100 select2'+exp+'"  autocomplete="off" id="loanid'+exp+'" name="loanid[]" onchange="getcustomerloan(this.value,'+exp+')"> <option selected disable>Select...</option>@foreach ($loans as $loan)<option value="{{$loan->id}}">{{$loan->customer->last_name." ".$loan->customer->first_name."[Code: ".$loan->loan_code.", Due: ".number_format($getloan->loan_total_balance($loan->id))."]"}}</option>@endforeach</select><br><small id="sttext'+exp+'"></small></td>\
                <td><input type="text" readonly id="accno'+exp+'" class="form-control" autocomplete="off" value=""><input type="hidden" name="customerid[]" autocomplete="off" id="cusid'+exp+'" class="form-control" value=""></td>\
               <td><input type="text" readonly id="accbal'+exp+'" class="form-control" autocomplete="off" value=""></td>\
               <td><input class="form-control amoutn" id="inputRepaymentAmount1" onkeyup="updatesum()" autocomplete="off" required="required" name="repayment_amount[]" type="number"></td>\
               <td><input class="form-control datepickers"  autocomplete="off" name="repayment_collected_date[]" type="date"></td>\
               <td><span id="removerow'+exp+'" class="btn btn-danger btn-sm" style="cursor:pointer;font-weight:bold;">&times</span></td>\
               </tr>';

               $("#addrow").prepend(trow);
               
               

               $("#removerow"+exp).click(function(){
                $("#ttrow"+exp).remove();
                let inputTotalAmount = 0;
                let getallrepamoutn = document.querySelectorAll(".amoutn");

              for ( var i = 0; i<getallrepamoutn.length; i++){
                var prepaymet = getallrepamoutn[i];
                inputTotalAmount += parseFloat(prepaymet.value);
              }
              document.getElementById("inputTotalAmount").textContent = Number(inputTotalAmount).toLocaleString('en');
               });
          $(".select2"+exp).select2();
        });
  });
</script>
<script>
  function getcustomerloan(id,tm){
    $.ajax({
        url:"{{route('getuserloandetails')}}",
        method:"get",
        data:{'loanid':id},
        beforeSend:function(){
          $("#sttext"+tm).text('please wait...').addClass('text-danger');
        },
        success:function(data){
          $("#sttext"+tm).text("");
          if(data.status === '0'){
            toastr.error('No account number Found');
            return false;
          }else{
          $("#accno"+tm).val(data.acnum);
          $("#accbal"+tm).val(data.bal);
          $("#cusid"+tm).val(data.custmerid);
          }
        },
        error:function(xhr,status,errorThrown){
          $("#sttext"+tm).text("");
          toastr.error('An Error Occured... '+errorThrown);
          return false;
        }
      });
  }

  function updatesum(){
    let inputTotalAmount = 0;
    let getallrepamoutn = document.querySelectorAll(".amoutn");

      for ( var i = 0; i<getallrepamoutn.length; i++){
        var prepaymet = getallrepamoutn[i];
        inputTotalAmount += parseFloat(prepaymet.value);
      }
      document.getElementById("inputTotalAmount").textContent = Number(inputTotalAmount).toLocaleString('en');
  }
</script>
@endsection