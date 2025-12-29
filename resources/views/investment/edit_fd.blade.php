@extends('layout.app')
@section('title')
    Edit Fixed Deposit
@endsection
@section('pagetitle')
Edit Fixed Deposit
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
                    <div class="col-md-12 col-lg-12 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('update.fd',['id' => $edl->id])}}" method="post" role="form" id="submitfd" enctype="multipart/form-data" onsubmit="thisForm()">
                      @csrf
                      
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Fixed Deposit Product</label>
                        <div class="col-sm-7 controls">
                            <select name="fd_product" required id="fdproduct" class="form-control width-70">
                                <option selected disabled>Select a Fixed Deposit Product</option>
                                @foreach ($fdprod as $item)
                                   <option value="{{$item->id}}" {{$edl->fixed_deposit_product_id == $item->id ? 'selected' : ''}} data-max="{{$item->maximum_principal}}" data-def="{{$item->default_principal}}">{{$item->name}}</option> 
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
                      <input type="hidden"  id="acbal" name="balance" autocomplete="off" value="">


                      <div class="form-group">
                        <label class="col-sm-4 control-label">Principal Amount</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70 princi" type="number" step="any" name="principal" id="{{empty($edl->principal) ? 'principal' : ''}}" required value="{{$edl->principal}}" autocomplete="off" placeholder="Enter principal Amount">
                          <input type="hidden" id="maxprincipal" name="maxprincipal" value="">
                          <input type="hidden" id="dfprincipal" value="">
                          <input type="hidden" name="minprincipal" id="minprincipal" value="">
                        </div>
                      </div>
                            
                               <div class="form-group">
                        <label class="col-sm-4 control-label"></label>
                        <div class="col-sm-7 controls">
                        <div class="vd_checkbox checkbox-success">
                          <input type="checkbox" name="auto_book_investment" {{$edl->auto_book_investment == 1 ? "checked" : ""}} value="1" id="checkbox-1">
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
                                <input class="width-70 form-control" name="duration" id="lduration" required="required" value="{{$edl->duration}}"  type="number" >
                            </div>
                            <div class="col-sm-6">
                                <select class="form-control width-90" name="duration_type" required="required" id="ldurationtyp">
                                    <option value="month" {{$edl->duration_type == "month" ? "selected" : ""}}>Month(s)</option>
                                    <option value="year" {{$edl->duration_type == "year" ? "selected" : ""}}>Year(s)</option>
                                </select>
                            </div>
                        </div>
                      </div>

                      <hr>
                      <h5 class="text-danger">Payments: </h5><br>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Payment Cycle</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control width-70"  required="required" id="" name="payment_cycle">
                                <option value="monthly" {{$edl->payment_cycle == "monthly" ? "selected" : ""}}>Monthly</option>
                                <option value="quarterly" {{$edl->payment_cycle == "quarterly" ? "selected" : ""}}>Quarterly</option>
                                <option value="semi_annual" {{$edl->payment_cycle == "semi-annual" ? "selected" : ""}}>Semi-Annual</option>
                                <option value="annually" {{$edl->payment_cycle == "annually" ? "selected" : ""}}>Annually</option>
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
                                <option value="upfront" {{$edl->interest_method == 'upfront' ? 'selected' : ''}}>UpFront</option>
                                <option value="monthly" {{$edl->interest_method == 'monthly' ? 'selected' : ''}}>Monthly</option>
                                <option value="simple_rollover" {{$edl->interest_method == 'simple_rollover' ? 'selected' : ''}}>Simple Rollover</option>
                                <option value="rollover" {{$edl->interest_method == 'rollover' ? 'selected' : ''}}>Compound Rollover</option>
                            </select>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Investment Interest %</label>

                        <div class="col-sm-7 controls">
                            <div class="col-sm-3">
                                <input class="width-70 form-control" name="interest_rate" id="linterest" required="required"  type="number" step=".01" value="{{$edl->interest_rate}}">
                            </div>
                            <div class="col-sm-6">
                                <select class="form-control width-90" name="interest_period" required="required" id="period">
                                    <option value="month" {{$edl->interest_period == "month" ? "selected" : ""}}>Month(s)</option>
                                    <option value="year" {{$edl->interest_period == "year" ? "selected" : ""}}>Year(s)</option>
                                </select>
                            </div>
                        </div>
                      </div>
                      
                      <div class="form-group" >
                        <label class="col-sm-4 control-label">Apply Withholding Tax</label>
                        <div class="col-sm-7 controls">
                          <select class="form-control width-70"  name="enable_withholding_tax" required="required" onchange="if(this.value == '1'){document.getElementById('shewithld').style.display='block';}else{document.getElementById('shewithld').style.display='none'}">
                            <option selected disabled>Select...</option>
                            <option value="1" {{$edl->enable_withholding_tax == "1" ? "selected" : ""}}>Yes</option>
                            <option value="0" {{$edl->enable_withholding_tax == "0" ? "selected" : ""}}>No</option>
                        </select>
                        </div>
                      </div>
                      <div class="form-group" id="shewithld" style="display: {{$edl->enable_withholding_tax == "1" ? 'block' : 'none'}};">
                        <label class="col-sm-4 control-label">Withholding Tax Interest(%)</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" step="0.01" name="withholding_tax" value="{{!is_null($edl->withholding_tax) ? $edl->withholding_tax : $getsetvalue->getsettingskey('withholdingtax')}}">
                        </div>
                      </div>
                     

                     <hr>
                     <h5 class="text-danger">Account Officer: </h5><br>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Account Officer</label>
                        <div class="col-sm-7 controls">
                            <select class="form-control select2 width-70" required="required" name="officer">
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
                     
                       <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit">Update Fixed Deposit</button>
                              
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
     
    //   let proidval =  $("#fdproduct").val();
    //     $.ajax({
    //     url:"{{route('fd.products.details')}}",
    //     method:"get",
    //     data:{'proidval':proidval},
    //     beforeSend:function(){
    //       $("#lpdttext").show();
    //     },
    //     success:function(data){
    //       if(data.status === '0'){
    //         $("#lpdttext").hide();
    //         toastr.error(data.msg);
    //         return false;
    //       }else{
    //         $("#lpdttext").hide();
    //       $("#principal").val(data.principal);
    //       $("#dfprincipal").val(data.principal);
    //       $("#maxprincipal").val(data.maxprincipal);
    //       $("#minprincipal").val(data.minprincipal);
    //       $("#lduration").val(data.duration);
    //       $("#linterest").val(data.interestrate);
          
    //       let p = document.getElementById("period");
    //       let dtyp = document.getElementById("ldurationtyp");
    //       let im = document.getElementById("interest_method");
          
    //       for(i=0; i < p.length; i++){
    //           if(p.options[i].value == data.interestperiod){
    //               p.options[i].selected = true;
    //           }
    //       }
    //       for(i=0; i < dtyp.length; i++){
    //           if(dtyp.options[i].value == data.durtype){
    //               dtyp.options[i].selected = true;
    //           }
    //       }
    //       for(i=0; i < im.length; i++){
    //           if(im.options[i].value == data.interestmethod){
    //               im.options[i].selected = true;
    //           }
    //       }
          
    //       }
    //     },
    //     error:function(xhr,status,errorThrown){
    //       toastr.error('An Error Occured... '+errorThrown);
    //       $("#lpdttext").hide();
    //       return false;
    //     }
    //   });

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
            toastr.error(data.msg);
            return false;
          }else{
            $("#lpdttext").hide();
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
            $("#btnssubmit").text('Update Fixed Deposit');
          $("#btnssubmit").attr('disabled',false);
          toastr.success(data.msg);
          window.location.href="{{route('manage.fd')}}";
          }else{
            toastr.error(data.msg);
             $("#btnssubmit").text('Update Fixed Deposit');
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
          $("#btnssubmit").text('Update Fixed Deposit');
          $("#btnssubmit").attr('disabled',false);
          return false;
        }
      });
    });

});
</script>
@endsection