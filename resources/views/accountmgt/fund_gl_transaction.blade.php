@extends('layout.app')
@section('title')
    Fund General Ledger
@endsection
@section('pagetitle')
Fund General Ledger
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
                    {{-- <div style="text-align: end">
                       <a href="{{route('gl.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div> --}}
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12" id="sher">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('gl.credit')}}" method="post" role="form" id="submittranx" onsubmit="thisForm()">
                      @csrf
                      
                     <div class="row">
                        <div class="col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                            <h4>Account to Debit</h4><hr>
                  <div class="form-group" style="padding: 0 6px;">
                    <label>Account Type</label>
                    <select name="account_type" class="width-70 form-control" autocomplete="off" required id="actyp">
                      <option selected disabled>Select Type</option>
                      @foreach ($actyps as $typ)
                          <option value="{{$typ->name}}">{{ucwords($typ->name)}}</option>
                      @endforeach
                     </select>
                    <img src="{{asset('img/loading.gif')}}" id="acttext" style="display: none" alt="loading">  
                </div>
                  <div class="form-group gla1" style="padding: 0 6px;display:none">
                    <label>General Ledger Accounts</label><br>
                    <select name="glcode" class="width-70 form-control glsect" style="width:70%" autocomplete="off" required id="dibgl">
                     
                     </select>

                     <div id="gldetail" style="display: none">
                      <p>General Ledger Name: <span class="glname"  style="font-weight: 700"></span></p>
                  <p>General Ledger Code: <span class="glcode"  style="font-weight: 700"></span></p>
                  <p>General Ledger Balance: <span class="glbal"  style="font-weight: 700"></span></p>
                     </div>
                </div>
              
                <div class="form-group" style="padding: 0 6px;">
                    <label>Fund GL Account From</label>
                    <select name="account" class="width-70 form-control" autocomplete="off" required onchange="document.getElementById('funds').value=this.options[this.selectedIndex].getAttribute('data-funds');">
                      <option selected disabled>Select Funds</option>
                      <option value="{{$getsetvalue->getsettingskey('bank_fund')}}" data-funds="bank funds">Bank Funds({{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getsetvalue->getsettingskey('bank_fund'))}})</option>
                      <option value="{{$getsetvalue->getsettingskey('company_capital')}}" data-funds="capital">Capital({{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getsetvalue->getsettingskey('company_capital'))}})</option>
                     </select>
                     <input type="hidden" name="funds" id="funds" value="">
                    
                </div>

                  <div class="form-group" style="padding: 0 6px;">
                    <label>Amount</label>
                      <input class="form-control width-70" autocomplete="off" step="any" required="required" name="amount" type="number" id="amount" value="{{old('amount')}}">
                  </div>
                  <div class="form-group" style="padding: 0 6px;">
                    <label>Transfer Description (Optional)</label>
                    <textarea name="description" class="form-control width-70" id="" cols="10" rows="3"></textarea>
                  </div>
                  
                        </div>
                        </div>
                                            
                        <div class="form-group form-actions">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnubmit"><i class="icon-ok"></i>Transfer</button>
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

    $("#submittranx").submit(function(e){
      e.preventDefault();
      $.ajax({
        url: $("#submittranx").attr('action'),
        method: 'post',
        data: $("#submittranx").serialize(),
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
            let err = '<div class="alert alert-danger">\
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="icon-cross"></i></button>'; 
            $.each(xhr.responseJSON.errors,function(key,value){
               err += '<strong><i class="fa fa-exclamation"></i> '+value+'</strong>';
            });
            err += '</div>';
            $("#sher").append(err);
          $("#btnubmit").text('Transfer');
          $("#btnubmit").attr('disabled',false);
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