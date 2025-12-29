@extends('layout.app')
@section('title')
    Create Fixed Deposit
@endsection
@section('pagetitle')
Create Fixed Deposit
@endsection
<?php
 $getsetvalue = new \App\Models\Setting();
?>
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('manage.fd')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12" id="error">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <?php 
                    $cusacct = !empty($_GET['customerid']) ? $_GET['customerid'] : '';
                    ?>
                    <form class="form-horizontal"  action="{{route('store.fd')}}" method="post" role="form" id="submitfd" enctype="multipart/form-data" onsubmit="thisForm()">
                      @csrf
                      
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Fixed Deposit Product</label>
                        <div class="col-sm-7 controls">
                            <select name="fd_product" required id="fdproduct" class="form-control width-70">
                                <option selected disabled>Select a Fixed Deposit Product</option>
                                @foreach ($fdprod as $item)
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
                      <input type="hidden"  id="acbal" name="balance" autocomplete="off" value="">


                      <div class="form-group">
                        <label class="col-sm-4 control-label">Principal Amount</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70 princi" type="number" step="any" name="principal" id="principal" required value="{{old('principal')}}" autocomplete="off" placeholder="Enter principal Amount">
                        <input type="hidden" id="maxprincipal" name="maxprincipal" value="">
                        <input type="hidden" id="dfprincipal" value="">
                        <input type="hidden" name="minprincipal" id="minprincipal" value="">
                        </div>
                      </div>
                        
                        <div class="form-group">
                        <label class="col-sm-4 control-label"></label>
                        <div class="col-sm-7 controls">
                        <div class="vd_checkbox checkbox-success">
                          <input type="checkbox" name="auto_book_investment" value="1" id="checkbox-1">
                          <label for="checkbox-1">Auto Book Fixed Deposit</label>
                        </div>
                      </div>
                      </div>
                      
                      <hr>
                      <h5 class="text-danger">Investment Duration: </h5><br>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Duration</label>
                        <div class="col-sm-7 controls">
                            <div class="col-sm-3">
                                <input class="width-70 form-control" name="duration" id="lduration" required="required"  type="number">
                            </div>
                            <div class="col-sm-6">
                                <select class="form-control width-90" name="duration_type" required="required" id="ldurationtyp">
                                    <option value="month">Month(s)</option>
                                    <option value="year">Year(s)</option></select>
                            </div>
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Payment: </h5><br>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Payment Cycle</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70"  required="required" id="" name="payment_cycle">
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="semi_annual">Semi-Annual</option>
                                <option value="annually">Annually</option>
                            </select>                       
                             </div>
                      </div>

                      <div class="form-group" >
                        <label class="col-sm-4 control-label">Expected Disbursement Date</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="date" name="release_date" required="required"  id="dsdate" value="{{old('release_date')}}">
                        </div>
                      </div>
                      <div class="form-group" >
                        <label class="col-sm-4 control-label">First Repayment Date</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="date" name="first_payment_date" id="frdate" required="required" value="{{old('first_payment_date')}}">
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Interest Rating: </h5><br>

                       <div class="form-group">
                        <label class="col-sm-4 control-label">Interest Method</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70" required="required" id="interest_method" name="interest_method">
                              <option selected disabled>Select method</option>
                                <option value="upfront">UpFront</option>
                                <option value="monthly">Monthly</option>
                                <option value="simple_rollover">Simple Rollover</option>
                                <option value="rollover">Compound Rollover</option>
                            </select>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Interest %</label>

                        <div class="col-sm-7 controls">
                            <div class="col-sm-3">
                                <input class="width-70 form-control" name="interest_rate" id="linterest" required="required" value="{{old('interest_rate')}}" step=".01"  type="number" >
                            </div>
                            <div class="col-sm-6">
                                <select class="form-control width-90" name="interest_period" required="required" id="period">
                                    <option value="month">Month(s)</option>
                                    <option value="year">Year(s)</option></select>
                            </div>
                        </div>
                      </div>

                      <div class="form-group" >
                        <label class="col-sm-4 control-label">Apply Withholding Tax</label>
                        <div class="col-sm-7 controls">
                          <select class="form-control width-70" name="enable_withholding_tax" required="required" onchange="if(this.value == '1'){document.getElementById('shewithld').style.display='block';}else{document.getElementById('shewithld').style.display='none'}">
                            <option selected disabled>Select...</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        </div>
                      </div>
                      <div class="form-group" id="shewithld"  style="display: none;">
                        <label class="col-sm-4 control-label">Withholding Tax Interest(%)</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" step="0.01"  name="withholding_tax" value="{{$getsetvalue->getsettingskey('withholdingtax')}}">
                        </div>
                      </div>

                      <hr>
                     <h5 class="text-danger">Account Officer: </h5><br>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Account Officer</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control select2 width-70" required="required" name="officer">
                                <option disabled selected="selected">Select Account Officer</option>
                                @foreach ($getofficers as $item)
                                <option value="{{$item->id}}">{{ucwords($item->full_name)}}</option>
                                @endforeach
                            </select>
                        </div>
                      </div>
                     
                       <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit"  id="btnssubmit">Book Fixed Deposit</button>
                              
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
//     function submitloan(){
//         let prinpal = $(".princi").val();
//         let maxamt = $(".maxprincipal").val();
//         let minamt = $(".minprincipal").val();
        
//         if(document.getElementById('acno1').value == ""){
//           toastr.error('Please enter customer account number to continue');
//          }else if(parseInt(prinpal) > parseInt(maxamt)){
//             let mxnm =  Number(maxamt).toLocaleString('en');
//             toastr.error('Maximum Principal amount exceeded '+mxnm);
//         }else if(parseInt(prinpal) < parseInt(minamt)){
//             let minm =  Number(minamt).toLocaleString('en');
//             toastr.error('Minimum Principal amount allowed '+minm);
//         }else if(document.getElementById('acbal').value <= 0 || document.getElementById('acbal').value < parseInt(prinpal)){
//           toastr.error('insuffient balance...please credit customer account to continue');
//         }else{
//           $("#btnssubmit").attr('disabled',true);
//           $("#btnssubmit").text('Please Wait...');
//             document.getElementById('submitloan').submit();
//         }
//     }
</script>
<script>
  $(document).ready(function(){
    $("#ro").select2();
    $("#sibo").select2();

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
    
    $("#fdproduct").change(function(){
       let proidval =  $("#fdproduct").val();
        $.ajax({
        url:"{{route('fd.products.details')}}",
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
          $("#minprincipal").val(data.minprincipal);
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


    $("#submitfd").submit(function(e){
      e.preventDefault();
      $.ajax({
        url: $("#submitfd").attr('action'),
        method: 'post',
        data: $("#submitfd").serialize(),
        beforeSend:function(){
          $("#btnssubmit").text('Please wait...');
          $("#btnssubmit").attr('disabled',true);
        },
        success:function(data){
          if(data.status == 'success'){
            $("#btnssubmit").text('Book Fixed Deposit');
          $("#btnssubmit").attr('disabled',false);
          toastr.success(data.msg);
          $("#submitfd")[0].reset();
          }else{
            toastr.error(data.msg);
             $("#btnssubmit").text('Book Fixed Deposit');
           $("#btnssubmit").attr('disabled',false);
             return false;
           }
        },
        error:function(xhr,status,errorThrown){
          let err = '';
          $.each(xhr.responseJSON.errors, function (key, value) {
                err += value;
            });
            toastr.error(err);
          $("#btnssubmit").text('Book Fixed Deposit');
          $("#btnssubmit").attr('disabled',false);
          return false;
        }
      });
    });

  });
</script>
@endsection