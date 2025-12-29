@extends('layout.app')
@section('title')
    Edit Loan
@endsection
@section('pagetitle')
Edit Loan
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('loan.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('loan.update',['id' => $edl->id])}}" method="post" role="form" id="submitloan" enctype="multipart/form-data" onsubmit="thisForm()">
                      @csrf
                      
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Loan Product</label>
                        <div class="col-sm-7 controls">
                            <select name="loan_product" required id="loanproduct" class="form-control width-70">
                                <option selected disabled>Select a Loan Product</option>
                                @foreach ($loanprod as $item)
                                   <option value="{{$item->id}}" {{$edl->loan_product_id == $item->id ? 'selected' : ''}} data-max="{{$item->maximum_principal}}" data-def="{{$item->default_principal}}">{{$item->name}}</option> 
                                @endforeach
                            </select>
                             <img src="{{asset('img/loading.gif')}}" id="lpdttext" style="display: none" alt="loading">  
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Customers Account Number</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" id="acno1" name="acno" required value="{{$edl->customer->acctno}}" autocomplete="off" placeholder="Enter Account Number">
                          <img src="{{asset('img/loading.gif')}}" id="sttext" style="display: none" alt="loading">  
                        </div>
                      </div>
                      <div id="cbl" style="display: none; margin:10px 2px;text-align:center">
                        <p>Customer Name: <span class="acnme"  style="font-weight: 700"></span></p>
                        <p>Customer Account Number: <span class="acnum"  style="font-weight: 700"></span></p>
                        <p>Customer Account Balance: <span class="acbal"  style="font-weight: 700"></span></p>
                      </div>
                      <input type="hidden" name="customerid" class="custm" autocomplete="off" value="">
                      <input type="hidden"  id="acbal" autocomplete="off" value="">


                      <div class="form-group">
                        <label class="col-sm-4 control-label">Principal Amount</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70 princi" type="number" name="principal" id="{{empty($edl->principal) ? 'principal' : ''}}" required value="{{$edl->principal}}" autocomplete="off" placeholder="Enter principal Amount">
                        <input type="hidden" id="maxprincipal" class="maxprincipal" value="">
                        <input type="hidden" id="dfprincipal" value="">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Loan Sector</label>
                        <div class="col-sm-7 controls">
                            <select name="sector" required id="sector" class="form-control width-70">
                                <option selected disabled>Select a Loan Sector</option>
                                @foreach ($sectors as $item)
                                   <option value="{{$item->id}}" {{$edl->sector_id == $item->id ? 'selected' : ''}}>{{$item->sector}}</option> 
                                @endforeach
                            </select>
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Duration: </h5><br>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Loan Duration</label>
                        <div class="col-sm-7 controls">
                            <div class="col-sm-3">
                                <input class="width-70 form-control" name="loan_duration" id="lduration" required="required" value="{{$edl->loan_duration}}"  type="number" >
                            </div>
                            <div class="col-sm-6">
                                <select class="form-control width-90" name="loan_duration_type" required="required" id="ldurationtyp">
                                    <option value="day" {{$edl->loan_duration_type == "day" ? "selected" : ""}}>Day(s)</option>
                                    <option value="week" {{$edl->loan_duration_type == "week" ? "selected" : ""}}>Week(s)</option>
                                    <option value="month" {{$edl->loan_duration_type == "month" ? "selected" : ""}}>Month(s)</option>
                                    <option value="year" {{$edl->loan_duration_type == "year" ? "selected" : ""}}>Year(s)</option>
                                </select>
                            </div>
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Equity & Purpose: </h5><br>

                      <div class="row">
                        <div class="col-md-2 col-lg-2 col-sm-12"></div>
                            <div class="form-group offset-4 col-md-5 col-lg-5 col-sm-12 controls">
                        <label>Loan Equity Contribution</label>
                        <div>
                          <input class="width-70" type="text" name="equity" required value="{{$edl->equity}}" autocomplete="off" placeholder="Enter Loan Equity">
                        </div>
                      </div>
                        
                    <div class="form-group offset-4 col-md-5 col-lg-5 col-sm-12 controls">
                        <label>Loan Purpose</label>
                        <div>
                          <input class="width-70" type="text" name="purpose" required value="{{$edl->purpose}}" autocomplete="off" placeholder="Enter Loan Purpose">
                        </div>
                      </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Repayments: </h5><br>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Repayment Cycle</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70"  required="required" id="" name="repayment_cycle">
                                <option value="daily" {{$edl->repayment_cycle == "daily" ? "selected" : ""}}>Daily</option>
                                <option value="weekly" {{$edl->repayment_cycle == "weekly" ? "selected" : ""}}>Weekly</option>
                                <option value="monthly" {{$edl->repayment_cycle == "monthly" ? "selected" : ""}}>Monthly</option>
                                <option value="bi_monthly" {{$edl->repayment_cycle == "bimonthly" ? "selected" : ""}}>Bimonthly</option>
                                <option value="quarterly" {{$edl->repayment_cycle == "quarterly" ? "selected" : ""}}>Quarterly</option>
                                <option value="semi_annual" {{$edl->repayment_cycle == "semi-annual" ? "selected" : ""}}>Semi-Annual</option>
                                <option value="annually" {{$edl->repayment_cycle == "annually" ? "selected" : ""}}>Annually</option>
                            </select>                       
                             </div>
                      </div>

                      <div class="form-group" >
                        <label class="col-sm-4 control-label">Expected Disbursement Date</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="date" name="release_date"  id="dsdate" value="{{$edl->release_date}}">
                        </div>
                      </div>
                      <div class="form-group" >
                        <label class="col-sm-4 control-label">First Repayment Date</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="date" name="first_payment_date" id="frdate" value="{{$edl->first_payment_date}}">
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Interest Rating: </h5><br>

                       <div class="form-group">
                        <label class="col-sm-4 control-label">Interest Method</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" required="required" id="interest_method" name="interest_method">
                                <option value="flat_rate" {{$edl->interest_method == 'flat_rate' ? 'selected' : ''}}>Flat Rate</option>
                                <option value="declining_balance_equal_principal" {{$edl->interest_method == 'declining_balance_equal_principal' ? 'selected' : ''}}>Reducing Balance</option>
                                <option value="declining_balance_equal_installments" {{$edl->interest_method == 'declining_balance_equal_installments' ? 'selected' : ''}}>Declining Balance-Equal Installments</option>
                                <option value="interest_only" {{$edl->interest_method == 'interest_only' ? 'selected' : ''}}>Interest only</option>
                            </select>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Loan Interest %</label>

                        <div class="col-sm-7 controls">
                            <div class="col-sm-3">
                                <input class="width-70 form-control" name="interest_rate" id="linterest" required="required"  type="number" step=".01" value="{{$edl->interest_rate}}">
                            </div>
                            <div class="col-sm-6">
                                <select class="form-control width-90" name="interest_period" required="required" id="period">
                                    <option value="day" {{$edl->interest_period == "day" ? "selected" : ""}}>Day(s)</option>
                                    <option value="week" {{$edl->interest_period == "week" ? "selected" : ""}}>Week(s)</option>
                                    <option value="month" {{$edl->interest_period == "month" ? "selected" : ""}}>Month(s)</option>
                                    <option value="year" {{$edl->interest_period == "year" ? "selected" : ""}}>Year(s)</option>
                                </select>
                            </div>
                        </div>
                      </div>

                      <p>If you override interest figure, system will use this figure when calculating interest for the schedule</p>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Override Interest</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" id="override_interest" name="override_interest" onchange="if(this.value == '1'){document.getElementById('overrideDiv').style.display='block';}else{document.getElementById('overrideDiv').style.display='none';}">
                                <option value="0" {{$edl->override_interest == "0" ? "selected" : ""}}>No</option>
                                <option value="1" {{$edl->override_interest == "1" ? "selected" : ""}}>Yes</option>
                            </select>                       
                             </div>
                      </div>
                      <div class="form-group" id="overrideDiv" style="display: none;">
                        <label class="col-sm-4 control-label">Override Interest Amount</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" name="override_interest_amount"  value="{{old('override_interest_amount')}}">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Grace on interest charged</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" name="grace_on_interest_charged" placeholder="Enter number of days"  value="{{$edl->grace_on_interest_charged}}">
                        </div>
                      </div>

                      @if (count($loanfees)>0)
                     <hr>
                     <h5 class="text-danger">Fees: </h5><br>

                     @foreach ($loanfees as $item)
                     <?php
                        $getloanfeemeta = DB::table('loan_fee_metas')->where('loan_fee_id',$item->id)
                                                                      ->where('parent_id',$edl->id)->first();
                     ?>
                     <input type="hidden" name="loanfees[]" id="loanfees" value="{{$item->id}}">
                     <div class="form-group">
                       <label for="loan_fees_1" class="col-sm-3 control-label">{{ucwords($item->name)}}&nbsp;{{$item->loan_fee_type == 'percentage' ? '(%)' : ''}}</label>
                       <div class="col-md-3">
                           <input class="form-control touchspin" step="any" name="loan_fees_amount[]" type="number" value="{{!empty($getloanfeemeta->value) ? $getloanfeemeta->value : ""}}">
                       </div>
                       <div class="col-sm-5">
                           {{-- <select class="form-control" required="required" id="" name="loan_fees_schedule[]">
                            <option value="distribute_fees_evenly" {{!empty($getloanfeemeta->loan_fees_schedule) && $getloanfeemeta->loan_fees_schedule == "distribute_fees_evenly" ? "selected" : ""}}>Distribute Fees Evenly</option>
                            <option value="charge_fees_on_first_payment" {{!empty($getloanfeemeta->loan_fees_schedule) && $getloanfeemeta->loan_fees_schedule == "charge_fees_on_first_payment" ? "selected" : ""}}>Charge Fees on first payment</option>
                            <option value="charge_fees_on_last_payment" {{!empty($getloanfeemeta->loan_fees_schedule) && $getloanfeemeta->loan_fees_schedule == "charge_fees_on_last_payment" ? "selected" : ""}}>Charge fees on last payment</option>
                           </select> --}}
                       </div>
                   </div>
                     @endforeach
                     @endif

                     <hr>
                     <h5 class="text-danger">Account Officer: </h5><br>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Account Officer</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control select2" required="required" name="officer">
                                <option disabled selected>Select Account Officer</option>
                                @foreach ($getofficers as $item)
                                <option value="{{$item->id}}"
                                  @if ($edl->accountofficer_id == $item->id)
                                      selected
                                  @endif
                                  >{{ucwords($item->full_name)}}</option>
                                @endforeach
                            </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Correspondent Bank Details (Optional)</label>
                        <div class="col-sm-7 controls">
                            <textarea class="form-control width-7-" id="editor" rows="5" name="description" cols="50">{{$edl->description}}</textarea>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Loan Files(doc, pdf, image)</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" autocomplete="off" name="files" type="file" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx">
                        </div>
                      </div>
                      
                      
                       <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="button" onclick="submitloan()"  id="btnssubmit">Save Record</button>
                              
                            </div>
                          </div>
                    </form>
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
    function submitloan(){
      let prinpal = $(".princi").val();
        let maxamt = $(".maxprincipal").val();
        
        if(document.getElementById('acno1').value == ""){
             toastr.error('Please enter customer account number to continue');
         }else if(parseInt(prinpal) > parseInt(maxamt)){
            let mxnm =  Number(maxamt).toLocaleString('en');
             toastr.error('Maximum Principal amount exceeded '+mxnm);
        }else if(document.getElementById('acbal').value <= 0 || document.getElementById('acbal').value < parseInt(prinpal)){
             toastr.error('insuffient balance...please credit customer account to continue');
        }else{
          $("#btnssubmit").attr('disabled',true);
          $("#btnssubmit").text('Please Wait...');
            document.getElementById('submitloan').submit();
        }
    }
</script>
<script>
  $(document).ready(function(){
    $("#ro").select2();
    $("#sibo").select2();
    
     let acnoval = $("#acno1").val();
     if(acnoval.length == 10){
        $.ajax({
        url:"{{route('savings.accounts.details')}}",
        method:"get",
        data:{'acno':acnoval},
        beforeSend:function(){
          $("#sttext").show();
        },
        success:function(data){
          if(data.status === '0'){
            $("#sttext").hide();
            toastr.error(data.msg);
            return false;
          }else{
            $("#sttext").hide();
          $("#cbl").show();
          $(".acnme").text(data.name).addClass('text-success');
          $(".acnum").text(data.acnum).addClass('text-success');
          $(".acbal").text(data.bal).addClass('text-success');
          $(".custm").val(data.custmerid);
          $("#acbal").val(data.bal);
          }
        },
        error:function(xhr,status,errorThrown){
          toastr.error('An Error Occured... '+errorThrown);
          $("#sttext").hide();
          return false;
        }
      })
    }else if(acnoval == ""){
      toastr.error('account number field is empty');
        return false;
     }
     
      let proidval =  $("#loanproduct").val();
        $.ajax({
        url:"{{route('loan.products.details')}}",
        method:"get",
        data:{'proidval':proidval},
        beforeSend:function(){
          $("#lpdttext").show();
        },
        success:function(data){
          if(data.status === '0'){
            $("#lpdttext").hide();
            toastr.error(data.msg);
            return false;
          }else{
            $("#lpdttext").hide();
          $("#principal").val(data.principal);
          $("#dfprincipal").val(data.principal);
          $("#maxprincipal").val(data.maxprincipal);
          $("#lduration").val(data.duration);
          $("#linterest").val(data.interestrate);
          
          let p = document.getElementById("period");
          let dtyp = document.getElementById("ldurationtyp");
          let im = document.getElementById("interest_method");
          
          for(i=0; i < p.length; i++){
              if(p.options[i].value == data.interestperiod){
                  p.options[i].selected = true;
              }
          }
          for(i=0; i < dtyp.length; i++){
              if(dtyp.options[i].value == data.durtype){
                  dtyp.options[i].selected = true;
              }
          }
          for(i=0; i < im.length; i++){
              if(im.options[i].value == data.interestmethod){
                  im.options[i].selected = true;
              }
          }
          
          }
        },
        error:function(xhr,status,errorThrown){
          toastr.error('An Error Occured... '+errorThrown);
          $("#lpdttext").hide();
          return false;
        }
      });

    $("#acno1").keyup(function(){
      let acnoval = $("#acno1").val();
     if(acnoval.length == 10){
        $.ajax({
        url:"{{route('savings.accounts.details')}}",
        method:"get",
        data:{'acno':acnoval},
        beforeSend:function(){
          $("#sttext").show();
        },
        success:function(data){
          if(data.status === '0'){
            $("#sttext").hide();
            toastr.error('invalid account number');
            return false;
          }else{
            $("#sttext").hide();
          $("#cbl").show();
          $(".acnme").text(data.name).addClass('text-success');
          $(".acnum").text(data.acnum).addClass('text-success');
          $(".acbal").text(data.bal).addClass('text-success');
          $(".custm").val(data.custmerid);
          $("#acbal").val(data.bal);
          }
        },
        error:function(xhr,status,errorThrown){
          alert('An Error Occured... '+errorThrown);
          $("#sttext").hide();
          return false;
        }
      })
    }else if(acnoval == ""){
      toastr.error('account number field is empty');
        return false;
     }
     
  });
  
  $("#loanproduct").change(function(){
       let proidval =  $("#loanproduct").val();
        $.ajax({
        url:"{{route('loan.products.details')}}",
        method:"get",
        data:{'proidval':proidval},
        beforeSend:function(){
          $("#lpdttext").show();
        },
        success:function(data){
          if(data.status === '0'){
            $("#lpdttext").hide();
            toastr.error(data.msg);
            return false;
          }else{
            $("#lpdttext").hide();
          $("#principal").val(data.principal);
          $("#dfprincipal").val(data.principal);
          $("#maxprincipal").val(data.maxprincipal);
          $("#lduration").val(data.duration);
          $("#linterest").val(data.interestrate);
          
          let p = document.getElementById("period");
          let dtyp = document.getElementById("ldurationtyp");
          let im = document.getElementById("interest_method");
          
          for(i=0; i < p.length; i++){
              if(p.options[i].value == data.interestperiod){
                  p.options[i].selected = true;
              }
          }
          for(i=0; i < dtyp.length; i++){
              if(dtyp.options[i].value == data.durtype){
                  dtyp.options[i].selected = true;
              }
          }
          for(i=0; i < im.length; i++){
              if(im.options[i].value == data.interestmethod){
                  im.options[i].selected = true;
              }
          }
          
          }
        },
        error:function(xhr,status,errorThrown){
          toastr.error('An Error Occured... '+errorThrown);
          $("#lpdttext").hide();
          return false;
        }
      })
    });
});
</script>
@endsection