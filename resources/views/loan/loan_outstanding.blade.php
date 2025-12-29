@extends('layout.app')
@section('title')
    Outstanding Loans
@endsection
@section('pagetitle')
Outstanding Loans
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
                        <div style="text-align: end">
                        
                        </div>
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
                                    <tr style="background-color: #D1F9FF">
                                        <th>Sn</th>
                                           <th>Account Name</th>
                                            <th>Loan Code</th>
                                            <th>Amount</th>
                                       </tr>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;
                                ?>
                              
                                @foreach($outsanding as $key)
                                <tr>
                                    <td>{{ $i+1 }}</td>  
                                    <td>{{!is_null($key->customer->business_name) ? ucwords($key->customer->business_name) : ucwords($key->customer->first_name." ".$key->customer->last_name)}}</td>
                                    <td>{{$key->loan->loan_code}} </td>
                                    <td>{{number_format($key->amount,2)}}</td>

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

   
@endsection
@section('scripts')
<script>
    function fundbranch(ur,rt){
        $("#myModal").modal('show');
        $("#fundwlet").attr('action',ur);
        $("#rate").val(rt);
      }
  </script>
    <script type="text/javascript">
  $(document).ready(function(){
     let aud = $("#acoff").dataTable({
      'pageLength':25,
      'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
    });


    $("#fundwlet").submit(function(e){
      e.preventDefault();
      $.ajax({
        url: $("#fundwlet").attr('action'),
        method: 'post',
        data: $("#fundwlet").serialize(),
        beforeSend:function(){
          $("#btnssubmit").text('Please wait...');
          $("#btnssubmit").attr('disabled',true);
        },
        success:function(data){
          if(data.status == 'success'){
            $("#btnssubmit").text('Update');
          $("#btnssubmit").attr('disabled',false);
          toastr.success(data.msg);
          $("#fundwlet")[0].reset();
          window.location.reload();
          }else{
            toastr.error(data.msg);
             $("#btnssubmit").text('Update');
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
          $("#btnssubmit").text('Update');
          $("#btnssubmit").attr('disabled',false);
          return false;
        }
      });
    });
  });
</script>
@endsection
