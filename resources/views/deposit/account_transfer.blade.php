@extends('layout.app')
@section('title')
    Account Transfer
@endsection
@section('pagetitle')
Account Transfer
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
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('savings.accounttransfer')}}" method="post" role="form" id="trxtransfer" onsubmit="thisForm()">
                      @csrf
                        <div class="row">
                            <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                                <h4>Account to Debit</h4><hr>
                      <div class="form-group" style="padding: 0 6px;">
                        <label>Customer Account Number</label>
                        <input class="form-control width-70" required="required" name="acno_one" type="text" id="acno1" autocomplete="off" value="{{old('acno_one')}}">
                        <img src="{{asset('img/loading.gif')}}" id="sttext" style="display: none" alt="loading">  
                    </div>
                      <div id="cbl" style="display: none; margin:10px 2">
                        <p>Customer Name: <span class="acnme"  style="font-weight: 700"></span></p>
                        <p>Customer Account Number: <span class="acnum"  style="font-weight: 700"></span></p>
                        <p>Customer Account Balance: <span class="acbal"  style="font-weight: 700"></span></p>
                      </div>

                      <div class="form-group" style="padding: 0 6px;">
                        <label>Amount</label>
                          <input class="form-control width-70" autocomplete="off" step="any" required="required" name="amount" type="number" id="amount" value="{{old('amount')}}">
                      </div>
                      
                      <div class="form-group" style="padding: 0 6px;">
                        <label>Transfer Decription (Optional)</label>
                        <textarea name="description" class="form-control width-70" id="" cols="10" rows="3"></textarea>
                      </div>
                      
                      <input type="hidden" name="dbit" value="debit">
                      <input type="hidden" name="customerid" class="custm" autocomplete="off" value="">
                      <input type="hidden" name="tran_type" value="withdrawal">
                      <input type="hidden" name="tran_initial" value="w">

                            </div>

                            <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                                <h4>Account to Credit</h4><hr>
                                <div class="form-group" style="padding: 0 6px;">
                                    <label>Customer Account Number</label>
                                    <input class="form-control width-70" required="required" name="acno_two" type="text" id="acno2" autocomplete="off" value="{{old('acno_two')}}">
                                    <img src="{{asset('img/loading.gif')}}" id="sttext2" style="display: none" alt="loading">
                                </div>
                                  <div id="cbl2" style="display: none; margin:10px 2">
                                    <p>Customer Name: <span class="acnme2"  style="font-weight: 700"></span></p>
                                    <p>Customer Account Number: <span class="acnum2"  style="font-weight: 700"></span></p>
                                    <p>Customer Account Balance: <span class="acbal2"  style="font-weight: 700"></span></p>
                                  </div>
            
                                  {{-- <div class="form-group" style="padding: 0 6px;">
                                    <label>Amount</label>
                                      <input class="form-control width-70" required="required" name="amount" type="number" id="amount" value="{{old('amount')}}">
                                  </div> --}}
                                  <input type="hidden" name="tran_type2" value="deposit">
                                  <input type="hidden" name="dbit2" value="credit">
                                  <input type="hidden" name="tran_initial2" value="d">
                                  <input type="hidden" name="customerid2" class="custm2" autocomplete="off" value="">
                            </div>
                        </div>
                        <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Transfer</button>
                              
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
//credit account
    $("#acno2").keyup(function(){
      let acnoval = $("#acno2").val();
     if(acnoval.length == 10){
        $.ajax({
        url:"{{route('savings.accounts.details')}}",
        method:"get",
        data:{'acno':acnoval},
        beforeSend:function(){
          $("#sttext2").show();
        },
        success:function(data){
        if(data.status === '0'){
            $("#sttext").hide();
            toastr.error(data.msg);
            return false;
          }else{
          $("#sttext2").hide();
          $("#cbl2").show();
          $(".acnme2").text(data.name).addClass('text-primary');
          $(".acnum2").text(data.acnum).addClass('text-primary');
          $(".acbal2").text(data.bal).addClass('text-primary');
          $(".custm2").val(data.custmerid);
          toastr.success(data.msg);
          }
        },
        error:function(xhr,status,errorThrown){
          toastr.error('An Error Occured... '+errorThrown);
          $("#sttext2").hide();
          return false;
        }
      })
     }else if(acnoval == ""){
      toastr.error('account number field is empty');
        return false;
     }
    });

    
    $("#trxtransfer").submit(function(e){
      e.preventDefault();
      $.ajax({
        url: $("#trxtransfer").attr('action'),
        method: 'post',
        data: $("#trxtransfer").serialize(),
        beforeSend:function(){
          $("#btnssubmit").text('Please wait...');
          $("#btnssubmit").attr('disabled',true);
        },
        success:function(data){
          if(data.status == 'success'){
            $("#btnssubmit").text('Transfer');
          $("#btnssubmit").attr('disabled',false);
          toastr.success(data.msg);
           window.location.reload();
          }
           else{
            toastr.error(data.msg);
             $("#btnssubmit").text('Transfer');
           $("#btnssubmit").attr('disabled',false);
             return false;
           }
        },
        error:function(xhr,status,errorThrown){
          toastr.error('Error '+errorThrown);
          $("#btnssubmit").text('Transfer');
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
@endsection