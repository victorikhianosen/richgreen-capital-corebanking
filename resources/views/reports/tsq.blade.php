@extends('layout.app')
@section('title')
    View Transaction Reference
@endsection
@section('pagetitle')
View Transaction Reference
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
                     
                      </div>
                      <div class="noprint" style="margin-bottom: 15px">
                        <form action="{{route('gettsqrecord')}}" method="post" id="querytranx">
                        @csrf
                          <table class="table table-bordered table-hover table-sm">
                            <thead>
                              <tr>
                                <th>Transaction Reference</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>
                                  <div class="form-group">
                                    <input type="text" name="reference" required id="" placeholder="Reference No" class="form-control">
                                  </div>
                                </td>
                                
                                <td>
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Query Transaction</button>
                                  <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('tsq')}}'">Reset</button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </form>
                      </div>
                        
                      <div class="row" style="display: block">
                        <div class="col-md-6 col-lg-6 col-sm-12" id="shtsq">
                                
                        </div>
                      </div>
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
<script type="text/javascript">
  $(document).ready(function(){
    $("#querytranx").submit(function(e){
      e.preventDefault();
      $.ajax({
        url: $("#querytranx").attr('action'),
        method: 'post',
        data: $("#querytranx").serialize(),
        beforeSend:function(){
          $("#btnsetsubmit").text('Please wait...');
          $("#btnsetsubmit").attr('disabled',true);
        },
        success:function(data){
          if(data.status == 'success'){
            $("#btnsetsubmit").text('Query Transaction');
          $("#btnsetsubmit").attr('disabled',false);
          toastr.success(data.msg);
           if(data.ptype == "1"){
            $("#shtsq").html('<h5>Status:  <span id="staus" class="badge vd_bg-black">'+data.data.transaction_remark+'</span></h5>\
                                <h5>Amount:  <span id="amut">'+Number(data.data.amount).toLocaleString('en')+'</span></h5>\
                                <h5>Reference:  <span id="ref">'+data.data.transaction_reference+'</span></h5>\
                                <h5>Description:  <span id="dexc">'+data.data.description+'</span></h5>\
                                <h5>Date:  <span id="dte">'+data.data.transaction_date+'</span></h5>');
            
           }else if(data.ptype == "2"){
            $("#staus").text();
            $("#amut").text(Number(data.data.amount).toLocaleString(en));
            $("#ref").text();
            $("#dexc").text();
            $("#dte").text();
           }else if(data.ptype == "4"){
                 $("#shtsq").html('<h5>Status:  <span id="staus" class="badge vd_bg-green">'+data.data.status+'</span></h5>\
                                <h5>Description:  <span id="dexc">'+data.data.message+'</span></h5>');
           }
          }else{
            toastr.error(data.msg);
             $("#btnsetsubmit").text('Query Transaction');
           $("#btnsetsubmit").attr('disabled',false);
             return false;
           }
        },
        error:function(xhr,status,errorThrown){
            toastr.error('Error... '+errorThrown);
          $("#btnsetsubmit").text('Query Transaction');
          $("#btnsetsubmit").attr('disabled',false);
          return false;
        }
      });
    });

  });
</script>
@endsection