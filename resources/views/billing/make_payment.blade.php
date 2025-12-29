@extends('layout.app')
@section('title')
    Make New Subcription
@endsection
@section('pagetitle')
Make New Subcription
@endsection
<?php
        $getsetvalue = new \App\Models\Setting();
        
$tref = substr(mt_rand('0',time()),'0','7');
 ?>
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                       
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                         @include('includes.success')
                    </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Subcription</th>
                                    <th>Price</th>
                                    <th></th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($plans as $item)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($item->package_name)}}</td>
                                    <td>{{number_format($item->price,2)}}</td>
                                    <td>
                                        <a href="#" onclick="makpay('{{$item->package_name}}','{{$item->price}}','{{$item->vat}}','{{$item->duration}}')" class="btn vd_btn vd_bg-twitter btn-sm">Pay</a>
                                    </td>
                                </tr>
                                <?php $i++?>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                  </div>
                </div>
                <!-- Panel Widget --> 
              </div>
              <!-- col-md-12 --> 
            </div>
            <!-- row -->
  </div>


  <!-- Modal -->
 <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header vd_bg-blue vd_white">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h4 class="modal-title" id="myModalLabel">Billing Information</h4>
      </div>
      <div class="modal-body"> 
        <form class="form-horizontal" action="{{route('storepayment')}}" method="post" id="submitbilling">
          @csrf
          <div class="form-group">
            <label class="col-sm-3 control-label">Package Name</label>
            <div class="col-sm-8 controls">
              <h5 class="pknm" style="margin-top: 10px"></h5> 
              <input class="width-70" type="hidden" name="package_name" id="pakname" value="" autocomplete="off" placeholder="Package Name">
            </div>
            </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Duration</label>
            <div class="col-sm-8 controls">
              <h5 class="dru" style="margin-top: 10px"></h5>
              <input class="width-70" type="hidden" name="duration"  id="dura" value="" autocomplete="off" placeholder="Package Name">
            </div>
            </div>

              <div class="form-group">
                  <label class="col-sm-3 control-label">Price</label>
                  <div class="col-sm-8 controls">
                    <h5 class="pr" style="margin-top: 10px"></h5>
                    <input class="width-70" type="hidden" name="price" id="price" value="" autocomplete="off" placeholder="Price">
                  </div>
                </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Vat</label>
            <div class="col-sm-8 controls">
              <h5 class="vtpt" style="margin-top: 10px"></h5>
              <input class="width-70" type="hidden" step="0.01" name="vat" id="vt" value="" autocomplete="off" placeholder="Vat">
            </div>
            </div>

          
          <div class="form-group">
            <label class="col-sm-3 control-label">Total Amount Payable</label>
            <div class="col-sm-8 controls">
              <h5 class="tpr" style="margin-top: 10px"></h5>
              <input class="width-70" type="hidden" name="total" id="tprice" value="" autocomplete="off" placeholder="Price">
            </div>
            </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">GL Account to Credit</label>
            <div class="col-sm-8 controls">  
              <span>(Account must be a cash balance or due from banks)</span>
               <select name="glaccount"  id="glcode"  class="width-70 glcr form-control" style="width: 100%">
                <option selected disabled>Select Account</option>
                @foreach ($gls as $item)
                  @foreach (DB::table('account_categories')->where('id',$item->account_category_id)->get() as $acitem)
                  <option value="{{$item->gl_code}}">{{ucwords($item->gl_name)." [".$item->gl_code." - ".ucwords($acitem->name)."]"}}</option>
                  @endforeach
                @endforeach
               </select>
              <img src="{{asset('img/loading.gif')}}" id="sttext" style="display: none" alt="loading">  

            </div>
            </div>
            <input type="hidden" id="pky" value="{{$getsetvalue->getsettingskey('gateway_pub_key')}}">
            <input type="hidden" name="paymentref" value="{{"RX1_".$tref}}">
        </form>
      
      </div>
      <div class="modal-footer background-login">
        <button type="button" class="btn vd_btn vd_bg-grey" data-dismiss="modal">Close</button>
        <button type="button" class="btn vd_btn vd_bg-green pflut" {{$activesub && $activesub->warning_date <= \Carbon\Carbon::now()->toDateString() ? "" : "disabled" }} id="cmbtnssubmit" style="display:none" onclick="payWithflutterwave()">Proceed To Payment</button>
      </div>
    </div>
    <!-- /.modal-content --> 
  </div>
  <!-- /.modal-dialog --> 
</div>
<!-- /.modal --> 

@endsection
@section('scripts')
<script>
  function makpay(pknm,pr,vt,dr){
        $("#myModal").modal('show');
        $(".pknm").text(pknm);
        $("#pakname").val(pknm);
        $(".dru").text(dr+" Day(s)");
        $("#dura").val(dr);
        $("#vt").val(vt);
        $(".pr").text(Number(pr).toLocaleString('en'));
        $("#price").val(pr);

        let amt = parseInt(pr)/100 * parseFloat(vt);
        let toamt = parseInt(pr) + parseInt(amt);
        $(".vtpt").text(vt+"% ("+Number(amt).toLocaleString('en')+")");
        $(".tpr").text(Number(toamt).toLocaleString('en'));
        $("#tprice").val(toamt);
      }
</script>

<script>
  function payWithflutterwave(){
    $("#pstatus").show();
    $(".pflut").attr('disabled',true); 
         
      FlutterwaveCheckout({
    public_key: document.getElementById('pky').value,
    tx_ref: "RX1_{{$tref}}",
    amount: document.getElementById("tprice").value,
    currency: "NGN",
    country: "NG",
    payment_options: "card, banktransfer, ussd",
   
    customer: {
      email: "{{$getsetvalue->getsettingskey('company_email')}}",
      phone_number: "{{$getsetvalue->getsettingskey('company_name')}}",
      name:"{{$getsetvalue->getsettingskey('company_phone')}}",
    },
    callback: function (data) {
      let trns = data.transaction_id;
      const form = document.getElementById("submitbilling");
       form.submit();
      console.log(data);
    },
    onclose: function() {
      // close modal
      $("#pstatus").hide();
    $(".pflut").attr('disabled',false); 
    },
    customizations: {
      title: "",
      description: "subcription payment",
      logo: "",
    },
  });
    
  }//flutterwave
</script>

    <script type="text/javascript">
  $(document).ready(function(){
    $(".glcr").select2({
             dropdownParent: $('#myModal')
         });

    $("#acoff").dataTable({'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });

  $("#glcode").change(function(){
      let glcodeval = $("#glcode").val();
      let amount = $("#tprice").val();
     if(glcodeval.length == 8){
        $.ajax({
        url:"{{route('checkaccount')}}",
        method:"get",
        data:{'glcodeval':glcodeval,'amount':amount},
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
          $("#cmbtnssubmit").show();
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
      toastr.error('GL account field is empty');
        return false;
     }
     
    });
  });
</script>
@endsection
