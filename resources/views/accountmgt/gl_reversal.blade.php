@extends('layout.app')
@section('title')
    GL Transaction Reversal
@endsection
@section('pagetitle')
GL Transaction Reversal
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
                    <form class="form-horizontal"  action="{{route('glreversal.posting')}}" method="post" id="trxsubmit" role="form" onsubmit="thisForm()">
                      @csrf
                        <div class="row">
                      
                            <div class="col-md-6 col-lg-6 col-sm-12">
                                <div class="form-group" style="padding: 0 6px;">
                                    <label>GL Transaction Type</label>
                                   <select class="form-control width-70" required="" onchange="revtypedata(this.value,this.options[this.selectedIndex].textContent)" autocomplete="off" id="trnsxtype" name="type">
                                    <option value="">Select Reversal Type</option>
                                    <option value="cgl">Customer to GL Reversal</option>
                                    <option value="glc">GL to Customer Reversal</option>
                                    <option value="gltogl">GL To GL Reversal</option></select>
                               
                                </div>

                                <div class="col-md-12 col-lg-12 col-sm-12">
                                  <div id="rvcbl" class="col-md-12 col-lg-12 col-sm-12  clearfix" style="display: none; margin:5px 0"> 
                                      <p>Amount: <span class="rvamout" style="font-weight: 700"></span></p>
                                      <p>Transaction Type: <span class="trxtype" style="font-weight: 700"></span></p>
                                      <p>Transaction Date: <span class="trxdate" style="font-weight: 700"></span></p>
                                    </div>
                
                                    <input class="form-control width-70" autocomplete="off"  name="amount" type="hidden" id="rvamount" value="">
                                    <input type="hidden" name="options" id="trntpye" value="">
                                    <input type="hidden" name="gldger_id" id="glcde" value="">
                                    <input type="hidden" name="gldger_id2" id="glcde2" value="">
                                    <input type="hidden" name="customerid" class="rvcustm" autocomplete="off" value="">
                              </div>
                            </div> 

                    <div class="col-md-6 col-lg-6 col-sm-12">
                      <div class="form-group col-sm-11 controls" style="padding: 0 6px;">
                        <label>Reference Number</label>
                        <div class="input-group">
                          <input class="" autocomplete="off" required="required" name="reference" placeholder="Enter slip or reference number" type="text" id="rvslip" value="{{old('slipno')}}">
                          <span class="input-group-addon" id="checkslip" style="cursor: pointer;">Check</span>
                        </div>
                          <img src="{{asset('img/loading.gif')}}" id="rvsttext" style="display: none" alt="loading">  
                      </div>

                      <div class="form-group" style="padding: 0 6px;">
                        <label>Transaction Description (Optional)</label>
                        <textarea name="description" class="form-control width-70" id="" cols="10" rows="3"></textarea>
                      </div>
                          </div>
                      
                        </div>

                      
                        <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" disabled id="btnssubmit"><i class="icon-ok"></i>Reverse Transaction</button>
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
      let txtyp = $("#trnsxtype").val();
      let txrtype = "";
      e.preventDefault();
      $.ajax({
        url: '{{route("gl.checkref")}}',
        method: 'get',
        data: {'reference':slipno,'txntype':txtyp},
        beforeSend:function(){
          $("#rvsttext").show();
        },
        success:function(data){
          $("#rvsttext").hide();
          $("#rvcbl").show();
          $("#btnssubmit").attr('disabled',false);
          if(data.status == '1'){
              if(data.ttpe == "cgl"){
                txrtype = "Customer To GL";
              }else if(data.ttpe == "glc"){
                txrtype = "GL To Customer";
              }else{
                txrtype = "GL To GL";
              }
          $(".rvamout").text(Number(data.amount).toLocaleString('en')).addClass('text-success');
          $("#rvamount").val(data.amount);
          $(".trxtype").text(txrtype).addClass('text-success');
          $(".trxdate").text(data.txrdate).addClass('text-success');
          $(".rvcustm").val(data.custmerid);
          $("#glcde").val(data.glid);
          $("#glcde2").val(data.glid2);
          $("#typetyp").val(data.ttpe);
          $(".shwrev").show();
          toastr.success(data.msg);
          }else{
            toastr.error(data.msg);
            $("#rvsttext").hide();
          $("#rvcbl").hide();
          $(".shwrev").hide();
            return false;  
          }
        },
        error:function(xhr,status,errorThrown){
          toastr.error('Error '+errorThrown);
          $("#rvsttext").hide();
          $("#rvcbl").hide();
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
          document.getElementById('trntpye').value =value;
          
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