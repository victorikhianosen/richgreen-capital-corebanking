@extends('layout.app')
@section('title')
    Create Loan
@endsection
@section('pagetitle')
Create Loan
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
                    <?php 
                    $cusacct = !empty($_GET['customerid']) ? $_GET['customerid'] : '';
                    ?>
                    <form class="form-horizontal"  action="{{route('loan.store')}}" method="post" role="form" id="submitloan" enctype="multipart/form-data" onsubmit="thisForm()">
                      @csrf
                      
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Loan Product</label>
                        <div class="col-sm-7 controls">
                            <select name="loan_product" required id="loanproduct" class="form-control width-70">
                                <option selected disabled>Select a Loan Product</option>
                                @foreach ($loanprod as $item)
                                   <option value="{{$item->id}}">{{$item->name}}</option> 
                                @endforeach
                            </select>
                               <img src="{{asset('img/loading.gif')}}" id="lpdttext" style="display: none" alt="loading">  
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Customers Account Number</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" id="acno1" name="acno" required value="{{isset($customer) ?  $customer->acctno : old('acno')}}" autocomplete="off" placeholder="Enter Account Number">
                          <img src="{{asset('img/loading.gif')}}" id="sttext" style="display: none" alt="loading">  
                        
                          <div id="cbl" style="display: none; margin:10px 2px">
                            <p>Customer Name: <span class="acnme"  style="font-weight: 700"></span></p>
                            <p>Customer Account Number: <span class="acnum"  style="font-weight: 700"></span></p>
                            <p>Customer Account Balance: <span class="acbal"  style="font-weight: 700"></span></p>
                          </div>
                        </div>
                      </div>
                      
                      <input type="hidden" name="customerid" class="custm" autocomplete="off" value="">
                      <input type="hidden"  id="acbal" autocomplete="off" value="">


                      <div class="form-group">
                        <label class="col-sm-4 control-label">Principal Amount</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70 princi" type="number" name="principal" id="principal" required value="{{old('principal')}}" autocomplete="off" placeholder="Enter principal Amount">
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
                                   <option value="{{$item->id}}">{{$item->sector}}</option> 
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
                                <input class="width-70 form-control" name="loan_duration" id="lduration" required="required"  type="number">
                            </div>
                            <div class="col-sm-6">
                                <select class="form-control width-90" name="loan_duration_type" required="required" id="ldurationtyp">
                                    <option value="day">Day(s)</option>
                                    <option value="week">Week(s)</option>
                                    <option value="month">Month(s)</option>
                                    <option value="year">Year(s)</option></select>
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
                          <input class="width-70" type="number" name="equity" required value="{{old('equity')}}" autocomplete="off" placeholder="Enter Loan Equity">
                        </div>
                      </div>
                        
                    <div class="form-group offset-4 col-md-5 col-lg-5 col-sm-12 controls">
                        <label>Loan Purpose</label>
                        <div>
                          <input class="width-70" type="text" name="purpose" required value="{{old('purpose')}}" autocomplete="off" placeholder="Enter Loan Purpose">
                        </div>
                      </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Repayments: </h5><br>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Repayment Cycle</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70"  required="required" id="" name="repayment_cycle">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="bi_monthly">Bimonthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="semi_annual">Semi-Annual</option>
                                <option value="annually">Annually</option>
                            </select>                       
                             </div>
                      </div>

                      <div class="form-group" >
                        <label class="col-sm-4 control-label">Expected Disbursement Date</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="date" name="release_date" required  id="dsdate" value="{{old('release_date')}}">
                        </div>
                      </div>
                      <div class="form-group" >
                        <label class="col-sm-4 control-label">First Repayment Date</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="date" name="first_payment_date" id="frdate" value="{{old('first_payment_date')}}">
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Interest Rating: </h5><br>

                       <div class="form-group">
                        <label class="col-sm-4 control-label">Interest Method</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" required="required" id="interest_method" name="interest_method">
                                <option value="flat_rate">Flat Rate</option>
                                 <option value="declining_balance_equal_principal">Reducing Balance</option>
                                <option value="declining_balance_equal_installments">Declining Balance-Equal Installments</option>
                                <option value="interest_only">Interest only</option>
                            </select>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Loan Interest %</label>

                        <div class="col-sm-7 controls">
                            <div class="col-sm-3">
                                <input class="width-70 form-control" name="interest_rate" id="linterest" required="required" value="{{old('interest_rate')}}" step=".01"  type="number" >
                            </div>
                            <div class="col-sm-6">
                                <select class="form-control width-90" name="interest_period" required="required" id="period">
                                    <option value="day">Day(s)</option>
                                    <option value="week">Week(s)</option>
                                    <option value="month">Month(s)</option>
                                    <option value="year">Year(s)</option></select>
                            </div>
                        </div>
                      </div>

                      <p>If you override interest figure, system will use this figure when calculating interest for the schedule</p>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Override Interest</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" id="override_interest" name="override_interest" onchange="if(this.value == '1'){document.getElementById('overrideDiv').style.display='block';}else{document.getElementById('overrideDiv').style.display='none';}">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
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
                          <input class="width-70" type="number" name="grace_on_interest_charged" placeholder="Enter number of days"  value="{{old('grace_on_interest_charged')?? '0'}}">
                        </div>
                      </div>

                      @if (count($loanfees)>0)
                     <hr>
                     <h5 class="text-danger">Fees: </h5><br>

                     @foreach ($loanfees as $item)
                     <input type="hidden" name="loanfees[]" id="loanfees" value="{{$item->id}}">
                     <input type="hidden" name="loan_fees_type[]" id="" value="{{$item->loan_fee_type}}">
                     <div class="form-group">
                       <label for="loan_fees_1" class="col-sm-3 control-label">{{ucwords($item->name)}}&nbsp;{{$item->loan_fee_type == 'percentage' ? '(%)' : ''}}</label>
                       <div class="col-md-3">
                           <input class="form-control touchspin" step="any" name="loan_fees_amount[]" type="number">
                       </div>
                       <div class="col-sm-5">
                           {{-- <select class="form-control" required="required" id="" name="loan_fees_schedule[]">
                             <option value="distribute_fees_evenly">Distribute Fees Evenly</option>
                             <option value="charge_fees_on_first_payment">Charge Fees on first payment</option>
                             <option value="charge_fees_on_last_payment">Charge fees on last payment</option>
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
                                <option disabled selected="selected">Select Account Officer</option>
                                @foreach ($getofficers as $item)
                                <option value="{{$item->id}}">{{ucwords($item->full_name)}}</option>
                                @endforeach
                            </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Correspondent Bank Details (Optional)</label>
                        <div class="col-sm-7 controls">
                            <textarea class="form-control width-7-" id="editor" rows="5" name="description" cols="50"></textarea>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Loan Files(doc, pdf, image)</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" autocomplete="off"  name="files" type="file" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx">
                        </div>
                      </div>
                      
                      
                       <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="button" onclick="submitloan()" id="btnssubmit">Save Record</button>
                              
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
        }else{
          $("#btnssubmit").attr('disabled',true);
          $("#btnssubmit").text('Please Wait...');
            document.getElementById('submitloan').submit();
        }

        // else if(document.getElementById('acbal').value <= 0 ){
        //      toastr.error('insuffient balance...please credit customer account to continue');
        // }
    }
</script>
<script>
  $(document).ready(function(){
    $("#ro").select2();
    $("#sibo").select2();
    $("#sector").select2();

    @if(!empty($_GET['customerid']))
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
          toastr.error('An Error Occured... '+errorThrown);
          $("#sttext").hide();
          return false;
        }
      })
    }else if(acnoval == ""){
      toastr.error('account number field is empty');
        return false;
     }
    @endif

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
            toastr.error('data.msg');
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
    })
  });
</script>
@endsection