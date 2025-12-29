@extends('layout.app')
@section('title')
    Vault/Till Posting
@endsection
@section('pagetitle')
@if (!empty($_GET['options']) && $_GET['options'] == "vtp")
Vault To Till Posting 
@elseif(!empty($_GET['options']) && $_GET['options'] == "tvp")
Till To Vault Posting
@elseif(!empty($_GET['options']) && $_GET['options'] == "tcp")
Till To Customer Posting(Deposit)
@elseif(!empty($_GET['options']) && $_GET['options'] == "ctp")
Customer To Till Posting(withdrawal)
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
                    <?php 
                    $getsetvalue = new App\Models\Setting();

                    $glacct = \App\Models\GeneralLedger::select('account_balance')->where('gl_code',$getsetvalue->getsettingskey('till_account'))->first();
                    $vglacct = \App\Models\GeneralLedger::select('account_balance')->where('gl_code',$getsetvalue->getsettingskey('vault_account'))->first();
                    
                    ?>
                    <form class="form-horizontal"  action="{{route('make_vault_transactions')}}" method="post" role="form" id="trxtransfer" onsubmit="thisForm()">
                      @csrf
                        <div class="row">
                            <!-- vault to till posting -->
                            @if (!empty($_GET['options']) && $_GET['options'] == "vtp")

                            <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                              <h4>Account to Debit</h4><hr>
                    <div class="form-group" style="padding: 0 6px;">
                      <label>Account Type</label>
                      <select name="account_type" class="width-70 form-control" autocomplete="off" required id="actyp">
                        <option selected disabled>Select Type</option>
                        @foreach ($actyps as $typ)
                            <option value="{{$typ->name}}" {{$typ->name == "asset" ? "selected" : ""}}>{{ucwords($typ->name)}}</option>
                        @endforeach
                       </select>
                      {{-- <img src="{{asset('img/loading.gif')}}" id="acttext" style="display: none" alt="loading">   --}}
                  </div>

                  <div class="gla1"  style="padding: 0 6px;">
                    <div class="form-group">
                      {{-- <label>General Ledger Accounts</label>
                      <select name="glcode" class="width-70 form-control glsect" style="width: 70%" autocomplete="off" required id="dibgl">
                       
                       </select> style="display: none"--}}

                       <div id="gldetail">
                        <p>General Ledger Name: <span class="glname text-success"  style="font-weight: 700">Vault Account</span></p>
                    <p>General Ledger Code: <span class="glcode text-success"  style="font-weight: 700">{{$getsetvalue->getsettingskey('vault_account')}}</span></p>
                    <p>General Ledger Balance: <span class="glbal text-success"  style="font-weight: 700">{{number_format($vglacct->account_balance,2)}}</span></p>
                       </div>
                  </div>
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
                    <input type="hidden" name="gltype" id="gltype" value="Vault Account">
                          </div>

                          <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                              <div id="vtcrd">
                                <h4>Account to Credit</h4><hr>
                                <div class="form-group" style="padding: 0 6px;">
                                  <label>Account Type</label>
                                  <select name="account_type2" class="width-70 form-control" autocomplete="off" required id="actyp2">
                                    <option selected disabled>Select Type</option>
                                    @foreach ($actyps as $typ)
                                        <option value="{{$typ->name}}" {{$typ->name == "asset" ? "selected" : ""}}>{{ucwords($typ->name)}}</option>
                                    @endforeach
                                   </select>
                                  {{-- <img src="{{asset('img/loading.gif')}}" id="acttext2" style="display: none" alt="loading">   --}}
                              </div>

                              <div class="form-group" style="padding: 0 6px;">
                                {{-- <label>General Ledger Accounts</label>
                                <select name="glcode2" class="width-70 form-control glsect2" style="width:70%" autocomplete="off" required id="crdgl">
                                 
                                 </select>
                              </div> --}}
                               
                                <div id="gl2" style="margin:10px 2px">
                                  <p>General Ledger Name: <span class="glname2 text-danger"  style="font-weight: 700">Teller Till Account</span></p>
                                  <p>General Ledger Code: <span class="glcode2 text-danger"  style="font-weight: 700">{{$getsetvalue->getsettingskey('till_account')}}</span></p>
                                  <p>General Ledger Balance: <span class="glbal2 text-danger"  style="font-weight: 700">{{number_format($glacct->account_balance,2)}}</span></p>
                                </div>
                              </div>
          
                                {{-- <div class="form-group" style="padding: 0 6px;">
                                  <label>Amount</label>
                                    <input class="form-control width-70" required="required" step="any" name="amount" type="number" id="amount" value="{{old('amount')}}">
                                </div> --}}
                                <input type="hidden" name="glcode2" value="{{$getsetvalue->getsettingskey('till_account')}}">
                                <input type="hidden" name="glcode" value="{{$getsetvalue->getsettingskey('vault_account')}}">
                                <input type="hidden" name="options" value="{{$_GET['options']}}">
                                <input type="hidden" name="dbit2" value="credit">
                               
                          </div>
                            @endif

                            <!-- till to vault posting -->
                            @if (!empty($_GET['options']) && $_GET['options'] == "tvp")

                            <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                              <h4>Account to Debit</h4><hr>
                              <div class="form-group" style="padding: 0 6px;">
                                <label>Account Type</label>
                                <select name="account_type3" class="width-70 form-control" autocomplete="off" required id="actyp">
                                  <option selected disabled>Select Type</option>
                                  @foreach ($actyps as $typ)
                                      <option value="{{$typ->name}}"  {{$typ->name == "asset" ? "selected" : ""}}>{{ucwords($typ->name)}}</option>
                                  @endforeach
                                 </select>
                                {{-- <img src="{{asset('img/loading.gif')}}" id="acttext" style="display: none" alt="loading">   --}}
                            </div>
                              <div class="form-group gla1" style="padding: 0 6px;">
                                {{-- <label>General Ledger Accounts</label>
                                <select  class="width-70 form-control glsect" style="width: 70%" autocomplete="off" required id="dibgl">
                                 
                                 </select> --}}
                              <div id="gldetail">
                                <p>General Ledger Name: <span class="glname text-success"  style="font-weight: 700">Teller Till Account</span></p>
                                <p>General Ledger Code: <span class="glcode text-success"  style="font-weight: 700">{{$getsetvalue->getsettingskey('till_account')}}</span></p>
                                <p>General Ledger Balance: <span class="glbal text-success"  style="font-weight: 700">{{number_format($glacct->account_balance,2)}}</span></p>
                              </div>  
                            </div>
                            <input type="hidden" name="dbit" value="debit">
                            <input type="hidden" name="glcode3" value="{{$getsetvalue->getsettingskey('till_account')}}">
                            
                              <div class="form-group" style="padding: 0 6px;">
                                <label>Amount</label>
                                  <input class="form-control width-70" autocomplete="off" step="any" required="required" name="amount" type="number" id="amount" value="{{old('amount')}}">
                              </div>
                              <div class="form-group" style="padding: 0 6px;">
                                <label>Transfer Description (Optional)</label>
                                <textarea name="description" class="form-control width-70" id="" cols="10" rows="3"></textarea>
                              </div>
                              
                               
                          </div>

                            <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                              <div id="vtcrd">
                                <h4>Account to Credit</h4><hr>
                                <div class="form-group" style="padding: 0 6px;">
                                  <label>Account Type</label>
                                  <select name="account_type4" class="width-70 form-control" autocomplete="off" required id="actyp2">
                                    <option selected disabled>Select Type</option>
                                    @foreach ($actyps as $typ)
                                        <option value="{{$typ->name}}" {{$typ->name == "asset" ? "selected" : ""}}>{{ucwords($typ->name)}}</option>
                                    @endforeach
                                   </select>
                                  {{-- <img src="{{asset('img/loading.gif')}}" id="acttext2" style="display: none" alt="loading">   --}}
                              </div>
                              {{-- <div class="form-group" style="padding: 0 6px;">
                                <label>General Ledger Accounts</label>
                                <select name="glcode4" class="width-70 form-control glsect2" style="width: 70%" autocomplete="off" required id="crdgl">
                                  
                                 </select>
                              </div> --}}
                                <div id="gl2" style=" margin:10px 2px">
                                  <p>General Ledger Name: <span class="glname2 text-danger"  style="font-weight: 700">Vault Account</span></p>
                                 <p>General Ledger Code: <span class="glcode2 text-danger"  style="font-weight: 700">{{$getsetvalue->getsettingskey('vault_account')}}</span></p>
                                  <p>General Ledger Balance: <span class="glbal2 text-danger"  style="font-weight: 700">{{number_format($vglacct->account_balance,2)}}</span></p>
                                </div>
                              </div>
          
                                {{-- <div class="form-group" style="padding: 0 6px;">
                                  <label>Amount</label>
                                    <input class="form-control width-70" required="required" step="any" name="amount" type="number" id="amount" value="{{old('amount')}}">
                                </div> --}}
                                <input type="hidden" name="options" value="{{$_GET['options']}}">
                                <input type="hidden" name="glcode4" value="{{$getsetvalue->getsettingskey('vault_account')}}">
                                <input type="hidden" name="dbit2" value="credit">
                                <input type="hidden" name="gltype" id="gltype2" value="Vault Account">

                          </div>

                        
                          @endif
                         <!-- vault/till to customer -->
                         @if (!empty($_GET['options']) && $_GET['options'] == "tcp")

                         <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                          <h4>Account to Debit</h4><hr>
                 <div class="form-group" style="padding: 0 6px;">
                      <label>Account Type</label>
                      <select name="account_type5" class="width-70 form-control" autocomplete="off" required id="actyp">
                        <option selected disabled>Select Type</option>
                        @foreach ($actyps as $typ)
                            <option value="{{$typ->name}}" {{$typ->name == "asset" ? "selected" : ""}}>{{ucwords($typ->name)}}</option>
                        @endforeach
                       </select>
                      {{-- <img src="{{asset('img/loading.gif')}}" id="acttext" style="display: none" alt="loading">   --}}
                  </div>
                    <div class="form-group gla1" style="padding: 0 6px;">
                      {{-- <label>General Ledger Accounts</label>
                      <select name="glcode5" class="width-70 form-control glsect" style="width: 70%" autocomplete="off" required id="dibgl">
                        
                       </select> --}}

                       <div id="gldetail">
                        <input type="hidden" name="glcode5" value="{{$getsetvalue->getsettingskey('till_account')}}">
                        <p>General Ledger Name: <span class="glname text-success"  style="font-weight: 700">Teller Till Account</span></p>
                        <p>General Ledger Code: <span class="glcode text-success"  style="font-weight: 700">{{$getsetvalue->getsettingskey('till_account')}}</span></p>
                        <p>General Ledger Balance: <span class="glbal text-success"  style="font-weight: 700">{{number_format($glacct->account_balance,2)}}</span></p>
                       </div>
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
                {{-- <input type="hidden" name="gltype" id="gltype" value=""> --}}

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
      
                            <input type="hidden" name="options" value="{{$_GET['options']}}">
                            <input type="hidden" name="customerid" class="custm2" autocomplete="off" value="">
                            <input type="hidden" name="dbit2" value="credit">
                           
                      </div>
                            @endif

                            @if (!empty($_GET['options']) && $_GET['options'] == "ctp")

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
                
                <input type="hidden" name="dbit" value="debit">
                <input type="hidden" name="customerid" class="custm2" autocomplete="off" value="">
               
                      </div>

                      <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                          <h4>Account to Credit</h4><hr>
                          
                            <div class="form-group" style="padding: 0 6px;">
                              <label>Account Type</label>
                              <select name="account_type6" class="width-70 form-control" autocomplete="off" required id="actyp">
                                <option selected disabled>Select Type</option>
                                @foreach ($actyps as $typ)
                                    <option value="{{$typ->name}}" {{$typ->name == "asset" ? "selected" : ""}}>{{ucwords($typ->name)}}</option>
                                @endforeach
                               </select>
                              {{-- <img src="{{asset('img/loading.gif')}}" id="acttext" style="display: none" alt="loading">   --}}
                          </div>
                            <div class="form-group gla1" style="padding: 0 6px;">
                              {{-- <label>General Ledger Accounts</label>
                              <select name="glcode6" class="width-70 form-control glsect" style="width: 70%" autocomplete="off" required id="dibgl">
                                
                               </select>--}}

                               <div id="gldetail">
                                <input type="hidden" name="glcode6" value="{{$getsetvalue->getsettingskey('till_account')}}">
                                <p>General Ledger Name: <span class="glname text-danger"  style="font-weight: 700">Teller Till Account</span></p>
                                <p>General Ledger Code: <span class="glcode text-danger"  style="font-weight: 700">{{$getsetvalue->getsettingskey('till_account')}}</span></p>
                                <p>General Ledger Balance: <span class="glbal text-danger"  style="font-weight: 700">{{number_format($glacct->account_balance,2)}}</span></p>
                               </div>
                          </div>
                          
                          <input type="hidden" name="options" value="{{$_GET['options']}}">
                          <input type="hidden" name="dbit2" value="credit">
                          {{-- <input type="hidden" name="gltype" id="gltype" value=""> --}}
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
    $(".glsect2").select2();
    //debit account
    $("#actyp").change(function(){
      let actypval = $("#actyp").val();
        $.ajax({
        url:"{{route('gl.getcode')}}",
        method:"get",
        data:{'actypval':actypval,'glactyp':'1'},
        beforeSend:function(){
          $("#acttext").show();
        },
        success:function(data){
          $("#acttext").hide();
          $(".gla1").show();
          $("#vtcrd").show();
          $("#dibgl").html(data);
        },
        error:function(xhr,status,errorThrown){
          toastr.error('An Error Occured... '+errorThrown);
          $("#acttext").hide();
          return false;
        }
      })
 
    });

    //credit account type
    $("#actyp2").change(function(){
      let actypval = $("#actyp2").val();
        $.ajax({
        url:"{{route('gl.getcode')}}",
        method:"get",
        data:{'actypval':actypval,'glactyp':'1'},
        beforeSend:function(){
          $("#acttext2").show();
        },
        success:function(data){
          $("#acttext2").hide();
          $(".gla1").show();
          $("#crdgl").html(data);
        },
        error:function(xhr,status,errorThrown){
          toastr.error('An Error Occured... '+errorThrown);
          $("#acttext2").hide();
          return false;
        }
      })
 
    });

    //gl code to debit
    $("#dibgl").change(function(){
      let glval = $("#dibgl").val();
        $.ajax({
        url:"{{route('gl.getcode')}}",
        method:"get",
        data:{'glval':glval,'vault':'1'},
        beforeSend:function(){
          $("#acttext").show();
        },
        success:function(data){
          if(data.status === '0'){
            $("#acttext").hide();
            toastr.error('account type not found');
            return false;
          }else{
            $("#acttext").hide();
          $("#gldetail").show();
          $(".glname").text(data.name).addClass('text-success');
          $(".glcode").text(data.glcode).addClass('text-success');
          $(".glbal").text(data.bal).addClass('text-success');
          $("#gltype").val(data.name);
          }
        },
        error:function(xhr,status,errorThrown){
          toastr.error('An Error Occured... '+errorThrown);
          $("#acttext").hide();
          return false;
        }
      })
 
    });

    $("#crdgl").change(function(){
      let glval = $("#crdgl").val();
        $.ajax({
        url:"{{route('gl.getcode')}}",
        method:"get",
        data:{'glval':glval,'vault':'1'},
        beforeSend:function(){
          $("#acttext2").show();
        },
        success:function(data){
          if(data.status === '0'){
            $("#acttext2").hide();
            toastr.error('account type not found');
            return false;
          }else{
            $("#acttext2").hide();
          $("#gl2").show();
          $(".glname2").text(data.name).addClass('text-success');
          $(".glcode2").text(data.glcode).addClass('text-success');
          $(".glbal2").text(data.bal).addClass('text-success');
          $("#gltype2").val(data.name);
          }
        },
        error:function(xhr,status,errorThrown){
          toastr.error('An Error Occured... '+errorThrown);
          $("#acttext2").hide();
          return false;
        }
      })
 
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
            // alert(data.msg);
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
  });
</script>
@endsection