@extends('layout.app')
@section('title')
    Sector
@endsection
@section('pagetitle')
Sector 
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
                          @if (Auth::user()->account_type == "system")
                            <a href="javascript:void(0)" onclick="fundbranch('{{route('loan.sector.updatecreate')}}','','','Create')" class="btn vd_btn vd_bg-green btn-sm">Create</a>
                            @endif
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
                                           <th>Sectors</th>
                                            <th>Total Loans</th>
                                            <th></th>
                                       </tr>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;
                                ?>
                              
                                @foreach($sectors as $key)
                                <tr>
                                    <td>{{ $i+1 }}</td>  
                                    <td>{{ucwords($key->sector)}}</td>
                                    <td>{{!is_null($key->loans) ? $key->loans->count() : "0"}} </td>
                                    <td>
                                      @if (Auth::user()->account_type == "system")
                                        <a href="javascript:void(0)" onclick="fundbranch('{{route('loan.sector.updatecreate',['id' => $key->id])}}','{{$key->sector}}','{{$key->id}}','Edit')" class="btn vd_btn vd_bg-blue btn-sm">Edit</a>
                                        @endif
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
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header vd_bg-blue vd_white">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
          <h4 class="modal-title" id="myModalLabel"></h4>
        </div>
        <div class="modal-body"> 
          <form class="form-horizontal" action="" method="post" id="fundwlet">
            @csrf
            <div class="form-group">
              <label class="col-sm-2 control-label">Sector</label>
              <div class="col-sm-12 controls">
               <input type="text" name="sector"  class="form-control" required id="sect">
              </div>
              </div>
                <input type="hidden" name="stype" id="styp" value="">
                <input type="hidden" name="id" id="uid" value="">
        </div>
        <div class="modal-footer background-login">
          <button type="button" class="btn vd_btn vd_bg-grey" data-dismiss="modal">Close</button>
          <button type="submit" class="btn vd_btn vd_bg-green" id="btnssubmit">Save Record</button>
        </div>
        </form>
      </div>
      <!-- /.modal-content --> 
    </div>
    <!-- /.modal-dialog --> 
  </div>
  <!-- /.modal --> 
@endsection
@section('scripts')
<script>
    function fundbranch(ur,rt,id,ty){
        $("#myModal").modal('show');
        $("#fundwlet").attr('action',ur);
        $("#sect").val(rt);
        $("#styp").val(ty);
        $("#uid").val(id);
        $("#myModalLabel").text(ty);
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
            $("#btnssubmit").text('Save Record');
          $("#btnssubmit").attr('disabled',false);
          toastr.success(data.msg);
          $("#fundwlet")[0].reset();
          window.location.reload();
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
@endsection
