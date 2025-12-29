@extends('layout.app')
@section('title')
    {{!empty($_GET['trx_type']) ? ucwords($_GET['trx_type']) : ''}} Transaction
@endsection
@section('pagetitle')
{{!empty($_GET['trx_type']) ? ucwords($_GET['trx_type']) : ''}} Transaction
@endsection
@section('content')
<?php
 $getsetvalue = new \App\Models\Setting();
?>
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('savings.store.transactions')}}" method="post" id="trxsubmit" role="form" onsubmit="thisForm()">
                      @csrf
                        <div class="row">
                          @if ($_GET['trx_type'] == 'reversal' ?? "")
                            
                    <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                      <div class="form-group col-sm-11 controls" style="padding: 0 6px;">
                        <label>Slip or Reference Number</label>
                        <div class="input-group">
                          <input class="" autocomplete="off" required="required" name="slipno" placeholder="Enter slip or reference number" type="text" id="rvslip" value="{{old('slipno')}}">
                          <span class="input-group-addon" id="checkslip" style="cursor: pointer;">Check</span>
                        </div>
                          <img src="{{asset('img/loading.gif')}}" id="rvsttext" style="display: none" alt="loading">  
                      </div>
                    <div id="rvcbl" class="col-md-12 col-lg-12 col-sm-12   clearfix" style="display: none; margin:10px 0">
                      <p>Customer Name: <span class="rvacnme" style="font-weight: 700"></span></p>
                      <p>Customer Account Number: <span class="rvacnum" style="font-weight: 700"></span></p>
                      <p>Amount: <span class="rvamout" style="font-weight: 700"></span></p>
                      <p>Transaction Type: <span class="trxtype" style="font-weight: 700"></span></p>
                      <p>Transaction Date: <span class="trxdate" style="font-weight: 700"></span></p>
                    </div>

                    <input class="form-control width-70" autocomplete="off" name="slipno" type="hidden" id="slipn" value="">
                    <input class="form-control width-70" autocomplete="off"  name="amount" type="hidden" id="rvamount" value="">
                    <input class="form-control width-70" autocomplete="off" name="revtyp" id="trxtyp" type="hidden" value="">
                    <input type="hidden" name="tran_type" value="{{!empty($_GET['trx_type']) ? $_GET['trx_type'] : ''}}">
                    <input type="hidden" name="tran_initial" id="itial" value="{{!empty($_GET['initial']) ? $_GET['initial'] : ''}}">
                    <input type="hidden" name="customerid" class="rvcustm" autocomplete="off" value="">
                          </div>
                          
                     <div class="col-md-6 col-lg-6 col-sm-12 shwrev" style="border: 1px solid #f4f4f4; display:none">
                            <div class="form-group" style="padding: 0 6px;">
                                <label>Transaction Type</label>
                               @if ($_GET['trx_type'] == 'reversal')
                               <select class="form-control width-70" required="" autocomplete="off" id="type" name="type" onchange="revtypedata(this.value,this.options[this.selectedIndex].getAttribute('data-id'))">
                                <option value="">Select Reversal Type</option>
                                <option value="rev_withdrawal" data-id="rw" data-type='withdrawal' data-name="debit">Withdrawal Reversal</option>
                                {{-- <option value="rev_fixed_deposit" data-id="rfd" data-type='fixed deposit'>Fixed Deposit Reversal</option> --}}
                                {{-- <option value="rev_interest" data-id="ri" data-type='interest reversal'>Interest Reversal</option> --}}
                                <option value="rev_deposit" data-id="rd" data-type='deposit' data-name="credit">Deposit Reversal</option></select>
                               @endif
                            </div>

                            <div class="form-group" style="padding: 0 6px;">
                              <label>Transaction Decription (Optional)</label>
                              <textarea name="description" class="form-control width-70" id="" cols="10" rows="3"></textarea>
                            </div>
                        </div>

                          @elseif($_GET['trx_type'] == 'withdrawal' || $_GET['trx_type'] == 'deposit')

                          <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                            <h4>{{$_GET['trx_type'] == 'withdrawal' ? 'Account to Debit' : ($_GET['trx_type'] == 'deposit' ? 'Account to Credit' : '')}}</h4><hr>
                  <div class="form-group" style="padding: 0 6px;">
                    <label>Customer Account Number</label>
                    <input class="form-control width-70" required="required" name="acno_one" type="text" id="acno1" autocomplete="off" value="{{old('acno_one')}}">
                    <img src="{{asset('img/loading.gif')}}" id="sttext" style="display: none" alt="loading">  
                </div>
                  <div id="cbl" style="display: none; margin:10px 2">
                    <p>Customer Name: <span class="acnme" style="font-weight: 700"></span></p>
                    <p>Customer Account Number: <span class="acnum" style="font-weight: 700"></span></p>
                    <p>Customer Account Balance: <span class="acbal" style="font-weight: 700"></span></p>
                  </div>

                  <div class="form-group" style="padding: 0 6px;">
                    <label>Amount</label>
                      <input class="form-control width-70" autocomplete="off" required="required" step="0.01" name="amount" type="number" id="amount" value="{{old('amount')}}">
                  </div>
                  
                  
                  <input type="hidden" name="tran_type" value="{{!empty($_GET['trx_type']) ? $_GET['trx_type'] : ''}}">
                  <input type="hidden" name="tran_initial" id="itial" value="{{!empty($_GET['initial']) ? $_GET['initial'] : ''}}">
                  <input type="hidden" name="customerid" class="custm" autocomplete="off" value="">
                     <input type="hidden" name="glcode" class="" autocomplete="off" value="{{$getsetvalue->getsettingskey('till_account')}}">
                        </div>

                        <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                            <div class="form-group" style="padding: 0 6px;">
                                <label>Transaction Type</label>
                                <input class="form-control width-70" type="{{!empty($_GET['trx_type']) && $_GET['trx_type'] == 'reversal' ? 'hidden' : 'text'}}" readonly value="{{!empty($_GET['trx_type']) ? ucwords($_GET['trx_type']) : ''}}">
                              </div>

                            <div class="form-group" style="padding: 0 6px;">
                              <label>Slip Number</label>
                                <input class="form-control width-70" autocomplete="off" required="required" name="slipno" type="number" id="slip" value="{{old('slipno')}}">
                            </div>

                            <div class="form-group" style="padding: 0 6px;">
                              <label>Transaction Decription (Optional)</label>
                              <textarea name="description" class="form-control width-70" id="" cols="10" rows="3"></textarea>
                            </div>
                        </div>

                        @elseif($_GET['trx_type'] == 'charge posting')

                        <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                <div class="form-group" style="padding: 0 6px;">
                  <label>Customer Account Number</label>
                  <input class="form-control width-70" required="required" name="acno_one" type="text" id="acno1" autocomplete="off" value="{{old('acno_one')}}">
                  <img src="{{asset('img/loading.gif')}}" id="sttext" style="display: none" alt="loading">  
              </div>
                <div id="cbl" style="display: none; margin:10px 2">
                  <p>Customer Name: <span class="acnme" style="font-weight: 700"></span></p>
                  <p>Customer Account Number: <span class="acnum" style="font-weight: 700"></span></p>
                  <p>Customer Account Balance: <span class="acbal" style="font-weight: 700"></span></p>
                </div>

                <div class="form-group" style="padding: 0 6px;">
                  <label>Amount</label>
                    <input class="form-control width-70" autocomplete="off"  type="number" readonly id="amount" value="">
                    <input class="form-control width-70" autocomplete="off"  name="amount" type="hidden" id="amount2" value="">
                </div>
                
                
                <input type="hidden" name="tran_type" value="{{!empty($_GET['trx_type']) ? $_GET['trx_type'] : ''}}">
                <input type="hidden" name="tran_initial" id="itial" value="{{!empty($_GET['initial']) ? $_GET['initial'] : ''}}">
                <input type="hidden" name="crgcustomerid" class="custm" autocomplete="off" value="">
                      </div>

                      <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                          <div class="form-group" style="padding: 0 6px;">
                              <label>Charge Type</label>
                              <select name="charge_type" class="width-70 form-control" autocomplete="off" onchange="if(this.value != ''){document.getElementById('amount').value=this.options[this.selectedIndex].getAttribute('data-amount');document.getElementById('amount2').value=this.options[this.selectedIndex].getAttribute('data-amount')}">
                                <option value="">Select Charge Type...</option>
                                @foreach ($charges as $item)
                                <option value="{{strtolower(str_replace(" ","_",$item->chargename))}}" data-amount="{{$item->amount}}">{{$item->chargename}}</option>
                                @endforeach
                              </select>
                            </div>


                          <div class="form-group" style="padding: 0 6px;">
                            <label>Transaction Decription (Optional)</label>
                            <textarea name="description" class="form-control width-70" id="" cols="10" rows="3"></textarea>
                          </div>
                      </div>
                          @endif
                        </div>
                        
                        <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Save Transaction</button>
                              
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
    //credit account
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
          toastr.success(data.msg);
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

    $("#trxsubmit").submit(function(e){
      e.preventDefault();
    
      $.ajax({
        url: $("#trxsubmit").attr('action'),
        method: 'post',
        data: $("#trxsubmit").serialize(),
        beforeSend:function(){
          $("#btnssubmit").text('Please wait...');
          $("#btnssubmit").attr('disabled',true);
        },
        success:function(data){
          if(data.status == 'success'){
            $("#btnssubmit").text('Save Transaction');
          $("#btnssubmit").attr('disabled',false);
          toastr.success(data.msg);
           window.location.reload();
          }else{
            toastr.error(data.msg);
            $("#btnssubmit").text('Save Transaction');
          $("#btnssubmit").attr('disabled',false);
            return false;
          }
        },
        error:function(xhr,status,errorThrown){
          toastr.error('Error '+errorThrown);
          $("#btnssubmit").text('Save Transaction');
          $("#btnssubmit").attr('disabled',false);
          //window.location.reload();
          return false;
        }
      });
    });
     
    $("#checkslip").click(function(e){
      let slipno = $("#rvslip").val();
      e.preventDefault();
      $.ajax({
        url: '{{route("savings.checkslipnumber")}}',
        method: 'get',
        data: {'slipno':slipno},
        beforeSend:function(){
          $("#rvsttext").show();
        },
        success:function(data){
          $("#rvsttext").hide();
          $("#rvcbl").show();
          if(data.status == '1'){
            $(".rvacnme").text(data.name).addClass('text-success');
          $(".rvacnum").text(data.acnum).addClass('text-success');
          $(".rvamout").text(Number(data.amount).toLocaleString('en')).addClass('text-success');
          $("#rvamount").val(data.amount);
          $(".trxtype").text(data.txrtype).addClass('text-success');
          $("#trxtyp").val(data.txrtype);
          $(".trxdate").text(data.txrdate).addClass('text-success');
          $(".rvcustm").val(data.custmerid);
          $("#slipn").val(slipno);
          $(".shwrev").show();
          toastr.success(data.msg);
          $("#btnssubmit").text('Save Transaction');
          $("#btnssubmit").attr('disabled',false);

          }else if(data.status == '2'){
            toastr.error(data.msg);
            $("#rvsttext").hide();
          $("#rvcbl").hide();
          $(".shwrev").hide();
            $("#btnssubmit").text('Save Transaction');
          $("#btnssubmit").attr('disabled',false);
            return false;
          }else{
            toastr.error(data.msg);
            $("#rvsttext").hide();
          $("#rvcbl").hide();
          $(".shwrev").hide();
            $("#btnssubmit").text('Save Transaction');
          $("#btnssubmit").attr('disabled',false);
            return false;  
          }
        },
        error:function(xhr,status,errorThrown){
          toastr.error('Error '+errorThrown);
          $("#rvsttext").hide();
          $("#rvcbl").hide();
          $("#btnssubmit").text('Save Transaction');
          $("#btnssubmit").attr('disabled',false);
          return false;
        }
      });
    });
    
        $("textarea").keydown(function(e){
if (e.keyCode == 13 && !e.shiftKey)
{
  // prevent default behavior
  e.preventDefault();
  return false;
  }
});

  });
</script>
<script>
 function revtypedata(value,dtid){
     //alert(value+" "+dtid);
     if(value != ''){
         document.getElementById('itial').value =dtid;
         
         if(dtid =="rw"){
             if(document.getElementById('trxtyp').value == "withdrawal" || document.getElementById('trxtyp').value == "debit"){
                 document.getElementById('btnssubmit').style.display='block';return true;
             }else{
                 alert(`Sorry these is a ${document.getElementById('trxtyp').value} transaction`);
                document.getElementById('btnssubmit').style.display='none';return false;
               }
         }else if(dtid =="rd"){
               if(document.getElementById('trxtyp').value == "deposit" || document.getElementById('trxtyp').value == "credit"){
                  document.getElementById('btnssubmit').style.display='block';return true;
             }else{
                  alert(`Sorry these is a ${document.getElementById('trxtyp').value} transaction`);
                document.getElementById('btnssubmit').style.display='none';return false;
               }
         }
    
         
     }else{
         alert('Please select a reversal type');
         
     }
 }
</script>
@endsection