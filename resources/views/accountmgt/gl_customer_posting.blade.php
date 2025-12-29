@extends('layout.app')
@section('title')
    General Ledger Customer Posting
@endsection
@section('pagetitle')
@if (!empty($_GET['options']) && $_GET['options'] == "glc")
General Ledger to Customer Posting
@elseif(!empty($_GET['options']) && $_GET['options'] == "cgl")
Customer to General Ledger Posting
@elseif(!empty($_GET['options']) && $_GET['options'] == "gltogl")
GL to GL Posting
@endif

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
                    <form class="form-horizontal"  action="{{route('gl.make_transactions')}}" method="post" role="form" id="trxtransfer" onsubmit="thisForm()">
                      @csrf
                        <div class="row">
                            @if (!empty($_GET['options']) && $_GET['options'] == "glc")

                            <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                              <h4>Account to Debit</h4><hr>
                    <div class="form-group" style="padding: 0 6px;">
                      <label>General Ledger Code</label>
                      <input class="form-control width-70" required="required" name="gl_code" type="number" id="glcode" autocomplete="off" value="{{old('gl_code')}}">
                      <img src="{{asset('img/loading.gif')}}" id="sttext" style="display: none" alt="loading">  
                  </div>
                  <div id="glbl" style="display: none; margin:10px 2px">
                    <p>General Ledger Name: <span class="glname"  style="font-weight: 700"></span></p>
                    <p>General Ledger Code: <span class="glcode"  style="font-weight: 700"></span></p>
                    <p>General Ledger Balance: <span class="glbal"  style="font-weight: 700"></span></p>
                  </div>

                    <div class="form-group" style="padding: 0 6px;">
                      <label>Amount</label>
                        <input class="form-control width-70" autocomplete="off" step="any" required="required" name="amount" type="number" id="amount" value="{{old('amount')}}">
                    </div>
                    <div class="form-group" style="padding: 0 6px;">
                      <label>Transfer Description (Optional)</label>
                      <textarea name="description" class="form-control width-70" id="" cols="10" rows="3"></textarea>
                    </div>
                    
                    <input type="hidden" name="dbit" value="debit">
                    <input type="hidden" name="gldger_id" id="glid" value="">

                          </div>

                          <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                              <h4>Account to Credit</h4><hr>
                              <div class="form-group" style="padding: 0 6px;">
                                  <label>Customer Account Number</label>
                                  <input class="form-control width-70" required="required" name="account_number" type="number" id="acno2" autocomplete="off" value="{{old('acno_two')}}">
                                  <img src="{{asset('img/loading.gif')}}" id="sttext2" style="display: none" alt="loading">
                              </div>
                                <div id="cbl2" style="display: none; margin:10px 2px">
                                  <p>Customer Name: <span class="acnme2"  style="font-weight: 700"></span></p>
                                  <p>Customer Account Number: <span class="acnum2"  style="font-weight: 700"></span></p>
                                  <p>Customer Account Balance: <span class="acbal2"  style="font-weight: 700"></span></p>
                                </div>
          
                                {{-- <div class="form-group" style="padding: 0 6px;">
                                  <label>Amount</label>
                                    <input class="form-control width-70" required="required" step="any" name="amount" type="number" id="amount" value="{{old('amount')}}">
                                </div> --}}
                                <input type="hidden" name="options" value="{{$_GET['options']}}">
                                <input type="hidden" name="customerid" class="custm2" autocomplete="off" value="">
                                <input type="hidden" name="dbit2" value="credit">
                               
                          </div>
                            @endif

                            <!-- customer to gl -->
                            @if (!empty($_GET['options']) && $_GET['options'] == "cgl")

                            <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                              <h4>Account to Debit</h4><hr>
                              <div class="form-group" style="padding: 0 6px;">
                                  <label>Customer Account Number</label>
                                  <input class="form-control width-70" required="required" name="account_number" type="number" id="acno2" autocomplete="off" value="{{old('acno_two')}}">
                                  <img src="{{asset('img/loading.gif')}}" id="sttext2" style="display: none" alt="loading">
                              </div>
                                <div id="cbl2" style="display: none; margin:10px 2px">
                                  <p>Customer Name: <span class="acnme2"  style="font-weight: 700"></span></p>
                                  <p>Customer Account Number: <span class="acnum2"  style="font-weight: 700"></span></p>
                                  <p>Customer Account Balance: <span class="acbal2"  style="font-weight: 700"></span></p>
                                </div>

                                <div class="form-group" style="padding: 0 6px;">
                                  <label>Amount</label>
                                    <input class="form-control width-70" autocomplete="off" step="any" required="required" name="amount" type="number" id="amount" value="{{old('amount')}}">
                                </div>

                                <div class="form-group" style="padding: 0 6px;">
                                  <label>Transfer Description (Optional)</label>
                                  <textarea name="description" class="form-control width-70" id="" cols="10" rows="3"></textarea>
                                </div>
          
                                <input type="hidden" name="options" value="{{$_GET['options']}}">
                                <input type="hidden" name="customerid" class="custm2" autocomplete="off" value="">
                                <input type="hidden" name="dbit" value="debit">
                               
                          </div>

                            <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                              <h4>Account to Credit</h4> <hr>
                    <div class="form-group" style="padding: 0 6px;">
                      <label>General Ledger Code</label>
                      <input class="form-control width-70" required="required" name="gl_code2" type="number" id="glcode" autocomplete="off" value="{{old('gl_code')}}">
                      <img src="{{asset('img/loading.gif')}}" id="sttext" style="display: none" alt="loading">  
                  </div>
                    <div id="glbl" style="display: none; margin:10px 2px">
                      <p>General Ledger Name: <span class="glname"  style="font-weight: 700"></span></p>
                      <p>General Ledger Code: <span class="glcode"  style="font-weight: 700"></span></p>
                       <p>General Ledger Balance: <span class="glbal"  style="font-weight: 700"></span></p>
                    </div>
                    
                           <input type="hidden" name="dbit2" value="credit">
                           <input type="hidden" name="gldger_id" id="glid" value="">

                          </div>

                            @endif

                            @if (!empty($_GET['options']) && $_GET['options'] == "gltogl")

                            <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                              <h4>Account to Debit (GL to add amount)</h4><hr>
                              <div class="form-group" style="padding: 0 6px;">
                                <label>General Ledger Code</label>
                                <input class="form-control width-70" required="required" name="gl_code" type="number" id="glcode" autocomplete="off" value="{{old('gl_code')}}">
                                <img src="{{asset('img/loading.gif')}}" id="sttext" style="display: none" alt="loading">  
                            </div>
                            <div id="glbl" style="display: none; margin:10px 2px">
                              <p>General Ledger Name: <span class="glname"  style="font-weight: 700"></span></p>
                              <p>General Ledger Code: <span class="glcode"  style="font-weight: 700"></span></p>
                              <p>General Ledger Balance: <span class="glbal"  style="font-weight: 700"></span></p>
                            </div>

                                <div class="form-group" style="padding: 0 6px;">
                                  <label>Amount</label>
                                    <input class="form-control width-70" autocomplete="off" step="any" required="required" name="amount" type="number" id="amount" value="{{old('amount')}}">
                                </div>

                                <div class="form-group" style="padding: 0 6px;">
                                  <label>Transfer Description (Optional)</label>
                                  <textarea name="description" class="form-control width-70" id="" cols="10" rows="3"></textarea>
                                </div>
          
                                <input type="hidden" name="options" value="{{$_GET['options']}}">
                                <input type="hidden" name="gldger_id" id="glid" autocomplete="off" value="">
                                <input type="hidden" name="dbit" value="debit">
                               
                          </div>

                            <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                              <h4>Account to Credit (GL to substract amount)</h4> <hr>
                    <div class="form-group" style="padding: 0 6px;">
                      <label>General Ledger Code</label>
                      <input class="form-control width-70" required="required" name="gl_code2" type="number" id="glcode2" autocomplete="off" value="{{old('gl_code')}}">
                      <img src="{{asset('img/loading.gif')}}" id="sttext2" style="display: none" alt="loading">  
                  </div>
                    <div id="glbl2" style="display: none; margin:10px 2px">
                      <p>General Ledger Name: <span class="glname2"  style="font-weight: 700"></span></p>
                      <p>General Ledger Code: <span class="glcode2"  style="font-weight: 700"></span></p>
                    <p>General Ledger Balance: <span class="glbal2"  style="font-weight: 700"></span></p> 
                    </div>
                    
                           <input type="hidden" name="dbit2" value="credit">
                           <input type="hidden" name="gldger_id2" id="glid2" value="">

                          </div>

                            @endif
                        </div>
                        <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnubmit"><i class="icon-ok"></i>Transfer</button>
                              
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
    $(".glsect").select2();
    //credit account
    $("#glcode").keyup(function(){
      let glcodeval = $("#glcode").val();
     if(glcodeval.length == 8){
        $.ajax({
        url:"{{route('gl.getcode')}}",
        method:"get",
        data:{'glcodeval':glcodeval},
        beforeSend:function(){
          $("#sttext").show();
        },
        success:function(data){
          if(data.status === '0'){
            $("#sttext").hide();
            toastr.error('invalid or inactive general legder number');
            return false;
          }else{
            $("#sttext").hide();
          $("#glbl").show();
          $(".glname").text(data.name).addClass('text-success');
          $(".glcode").text(data.glcode).addClass('text-success');
          $(".glbal").text(data.bal).addClass('text-success');
          $("#glid").val(data.glid);
          }
        },
        error:function(xhr,status,errorThrown){
          toastr.error('An Error Occured... '+errorThrown);
          $("#sttext").hide();
          return false;
        }
      })
    }else if(glcodeval == ""){
      toastr.error('general legder number field is empty');
        return false;
     }
     
    });

    //credit account
    $("#glcode2").keyup(function(){
      let glcodeval = $("#glcode2").val();
     if(glcodeval.length == 8){
        $.ajax({
        url:"{{route('gl.getcode')}}",
        method:"get",
        data:{'glcodeval':glcodeval},
        beforeSend:function(){
          $("#sttext2").show();
        },
        success:function(data){
          if(data.status === '0'){
            $("#sttext").hide();
            toastr.error('invalid or inactive general legder number');
            return false;
          }else{
            $("#sttext2").hide();
          $("#glbl2").show();
          $(".glname2").text(data.name).addClass('text-primary');
          $(".glcode2").text(data.glcode).addClass('text-primary');
          $(".glbal2").text(data.bal).addClass('text-primary');
          $("#glid2").val(data.glid);
          }
        },
        error:function(xhr,status,errorThrown){
          toastr.error('An Error Occured... '+errorThrown);
          $("#sttext2").hide();
          return false;
        }
      })
    }else if(glcodeval == ""){
      toastr.error('general legder number field is empty');
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
            $("#sttext2").hide();
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
          $("#btnubmit").text('Please wait...');
          $("#btnubmit").attr('disabled',true);
        },
        success:function(data){
          if(data.status == 'success'){
            $("#btnubmit").text('Transfer');
          $("#btnubmit").attr('disabled',false);
          toastr.success(data.msg);
           window.location.reload();
          }else{
            toastr.error(data.msg);
            $("#btnubmit").text('Transfer');
          $("#btnubmit").attr('disabled',false);
            return false;
          }
        },
        error:function(xhr,status,errorThrown){
          toastr.error('Error '+errorThrown);
          $("#btnubmit").text('Transfer');
          $("#btnubmit").attr('disabled',false);
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