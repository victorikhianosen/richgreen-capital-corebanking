@extends('layout.app')
@section('title')
    FX Purchase
@endsection
@section('pagetitle')
FX Purchase
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('managefx.purchase')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('fx_purchase.store')}}" method="post" role="form" id="fxpurchaseform">
                      @csrf
                      
                      <div class="container">
                        <div class="row">
                          <div class="col-md-12 col-lg-12 col-sm-12">
                            <div class="form-group">
                              <label>Transaction Date</label>
                                <input class="form-control width-40" type="date" required name="transaction_date" id="fname" autocomplete="off"  value="{{old('transaction_date')}}">
                            </div>
                          </div>
                            <div class="col-md-5 col-lg-5 col-sm-12">
    
                                <div class="form-group">
                                    <label>Currency</label>
                                      <select name="currency" required class="form-control" id="" onchange="document.getElementById('purchaser').value=this.value;document.getElementById('curncy').textContent=this.options[this.selectedIndex].getAttribute('data-name');document.getElementById('exrate').value=this.options[this.selectedIndex].getAttribute('data-id');document.getElementById('dbacct').textContent=this.options[this.selectedIndex].getAttribute('data-name')">
                                        <option selected disabled>Select Currency</option>
                                        @foreach ($currency as $item)
                                            <option value="{{$item->currency_rate}}" data-id="{{$item->id}}" data-name="{{$item->currency}}">{{ucwords($item->currency)}}</option>
                                        @endforeach
                                    </select>
                                       <input type="hidden" name="exrate" id="exrate" value="">
                                  </div>
    
                                <div class="form-group">
                                    <label>Exchange Rate</label>
                                      <input class="form-control" type="number" required name="exhchange_rate" onkeyup="calculatesales()" id="purchaser" autocomplete="off" placeholder="Enter Purchase Rate" value="{{old('purchase_rate')}}">
                                  </div>
    
                                  <div class="form-group">
                                    <label>Naira Value</label>
                                      <input class="form-control" type="text" readonly name="naira_amount" id="naravalue" autocomplete="off" value="{{old('naria_amount')}}">
                                  </div>

                                  <div class="form-group">
                                    <p>Amount Payable</p>
                                    <h3 id="amtpayable" class="text-info"></h3>
                                  </div>

                            </div>
    
                                <div class="col-md-1 col-lg-1"></div>
    
                            <div class="col-md-5 col-lg-5 col-sm-12">
    
                                <div class="form-group">
                                    <label>Account Officer</label>
                                        <select name="account_officer" class="form-control" id="rofficer">
                                            <option selected disabled>Select Account Officer</option>
                                            @foreach ($getofficers as $item)
                                                <option value="{{$item->id}}">{{ucwords($item->full_name)}}</option>
                                            @endforeach
                                        </select>
                                  </div>
    
                                <div class="form-group">
                                    <label>Amount (<span id="curncy" class="text-danger" style="text-transform: uppercase"></span>)</label>
                                      <input class="form-control" type="number" onkeyup="calculatesales()"  name="amount" id="amount" autocomplete="off" placeholder="Enter Amount" value="{{old('amount')}}">
                                  </div>
    
                                <div class="form-group">
                                    <label>Fees Amount(NGN)</label>
                                      <input class="form-control" type="number" required name="fees"  autocomplete="off" placeholder="Enter Fees Amount" value="0">
                                  </div>
                                  
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" id="descriptn" placeholder="Optional" cols="5" rows="2"></textarea>
                                  </div>
                            </div>
                          </div>

                          <hr>
                          <h3>Payments</h3>
                          <div class="row">
                            <div class="col-md-5 col-lg-5 col-sm-12">

                                <div class="form-group">
                                    <label>Authoriser</label>
                                        <select name="authoriser" class="form-control" id="authriz">
                                            <option selected disabled>Select Authoriser</option>
                                            @foreach ($users as $user)
                                                <option value="{{$user->id}}">{{ucwords($user->last_name." ".$user->first_name)}}</option>
                                            @endforeach
                                        </select>
                                  </div>

                            </div>
    
                            <div class="col-md-1 col-lg-1"></div>
    
                            <div class="col-md-5 col-lg-5 col-sm-12">
                                <div class="form-group">
                                    <label>Payment Mode</label>
                                        <select name="payment_mode" class="form-control" id=""
                                         onchange="paymentmod(this.value)">
                                            <option selected disabled>Select Mode</option>
                                            <option value="cash">Pay By Cash</option>
                                            <option value="bank">Pay By Bank</option>
                                            <option value="customer">Credit Customer</option>
                                        </select>
                                  </div>
                                  <div class="form-group">
                                    <label>Recieve Currency From Account (<span id="dbacct" class="text-danger" style="text-transform: uppercase"></span>)</label>
                                    <select name="gldebit" class="form-control" id="gldb">
                                        <option selected disabled>Select Account</option>
                                        @foreach ($generalledgers as $dbitem)
                                        <option value="{{$dbitem->id}}">{{ucwords($dbitem->gl_name)}}</option>
                                        @endforeach
                                    </select>
                                  </div>
    
                                  <div class="form-group">
                                    <label>Pay Naira From Account(NGN)</label>
                                    <div id="pgl">
                                        <select name="glcredit" class="form-control width-100" id="glcr">
                                            <option selected disabled>Select Account</option>
                                            @foreach ($generalledgers as $critem)
                                                 <option value="{{$critem->id}}">{{ucwords($critem->gl_name)}}</option>
                                            @endforeach
                                        </select>
                                    </div>
    
                                    <div id="pcustomer" style="display: none">
                                        <select name="customeid" class="form-control width-100" id="cusrt" style="width:100% !important">
                                            <option selected disabled>Select Customer</option>
                                            @foreach ($customers as $item)
                                                 <option value="{{$item->id}}">{{ucwords($item->last_name." ".$item->first_name)}}</option>
                                            @endforeach
                                        </select>
                                      </div>
                                  </div>
    
                                  <div class="form-group">
                                    <label>Amount</label>
                                      <input class="form-control" type="number" readonly  id="totamontt" autocomplete="off" placeholder="Enter Amount" value="{{old('amount')}}">
                                  </div>
                            </div>
                          </div>
    
                          <input class="width-70 form-control" type="hidden" name="fxtype"  value="purchase">
                            <div class="form-group form-actions">
                                <div class="col-sm-4"> </div>
                                <div class="col-sm-7">
                                  <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Save Record</button>
                                  
                                </div>
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
    $("#cusrt").select2();
  $("#gldb").select2();
  $("#glcr").select2();
  $("#authriz").select2();
  $("#rofficer").select2();

  $("#fxpurchaseform").submit(function(e){
    e.preventDefault();
    $.ajax({
      url: $("#fxpurchaseform").attr('action'),
      method: "post",
      data: $("#fxpurchaseform").serialize(),
      beforeSend:function(){
        $("#btnssubmit").text('Please wait...');
          $("#btnssubmit").attr('disabled',true);
      },
      success:function(data){
        if(data.status == 'success'){
            $("#btnssubmit").text('Save Record');
          $("#btnssubmit").attr('disabled',false);
          toastr.success(data.msg);
           window.location.href="{{route('managefx.purchase')}}";
          }else{
            toastr.error(data.msg);
             $("#btnssubmit").text('Save Record');
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
          $("#btnssubmit").text('Save Record');
          $("#btnssubmit").attr('disabled',false);
          return false;
        }
      });
  });

  });
</script>
<script>
  function paymentmod(value){
      if(value == "customer"){
          document.getElementById('pcustomer').style.display='block';
          document.getElementById('pgl').style.display='none';
       }else{
          document.getElementById('pcustomer').style.display='none';
          document.getElementById('pgl').style.display='block';
       }
  }

  function calculatesales(){
     let purchrate = document.getElementById('purchaser').value;
     let amount = document.getElementById('amount').value;
     let payable = document.getElementById('amtpayable');
     let nariavalue = document.getElementById('naravalue');
     let totnavalue = document.getElementById('totamontt');
     let descptn = document.getElementById('descriptn');
     let curncytype = document.getElementById('curncy').textContent;

    let prate = parseFloat(purchrate) * parseFloat(amount);
    payable.textContent = Number(prate).toLocaleString('en');
     nariavalue.value = prate;
     totnavalue.value = prate;
     descptn.value = "@"+purchrate+"/"+amount+"("+curncytype+")";
  }
</script>

@endsection