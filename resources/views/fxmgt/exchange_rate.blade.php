@extends('layout.app')
@section('title')
    Exchange Rates
@endsection
@section('pagetitle')
Exchange Rates
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                          @can('create fx')
                           <a href="#" onclick="addrates('Add Exchange Rate')" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Rates</a>
                           @endcan
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
                                    <th>Sn</th>
                                    <th>Currency</th>
                                    <th>Rates</th>
                                    <th>Symbol</th>
                                    <th></th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($rates as $item)
                                <tr id="d{{$item->id}}">
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($item->currency)}}</td>
                                    <td>{{number_format($item->currency_rate,2)}}</td>
                                    <td>{{$item->currency_symbol}}</td>
                                    <td>
                                        @can('edit fx')
                                        <a href="#" class="btn menu-icon vd_bd-blue vd_blue btn-sm" onclick="editrates('{{$item->id}}','{{$item->currency}}','{{$item->currency_rate}}','{{$item->currency_symbol}}','edit')"><i class="fa fa-pencil"></i> </a>
                                       @endcan
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
          <h4 class="modal-title" id="myModalLabel"></h4>
        </div>
        <div class="modal-body"> 
          <form class="form-horizontal" action="{{route('rates.edit.create')}}" method="post" id="bankd" enctype="multipart/form-data">
            @csrf
            <div class="form-group" style="margin:0 5px">
              <label>Currency</label>
                <input type="text" name="currency" id="bn" value="{{old('currency')}}">
             
              </div>
            <div class="form-group" style="margin:0 5px">
              <label>Currency Rate</label>
                <input type="number" step="any" pattern="0-9" name="currency_rate" id="bncd" value="{{old('currency_rate')}}">
              </div>
            <div class="form-group" style="margin:0 5px">
              <label>Currency Symbol</label>
              <select name="currency_symbol" id="cursybl" required class="width-70 form-control">
                <option selected disabled>Select Currency Symbol</option>
                <option value="$">$</option>
                <option value="£">£</option>
                <option value="€">€</option>
                <option value="CA$">CA$</option>
            </select>
              </div>
            
            <input type="hidden" name="id" id="bnkid" value="">
            <input type="hidden" name="type" id="typ" value="">
        
        </div>
        <div class="modal-footer background-login">
          <button type="button" class="btn vd_btn vd_bg-grey" data-dismiss="modal">Close</button>
          <button type="submit" class="btn vd_btn vd_bg-green" id="btnssubmit">Save</button>
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
    function addrates(ad){
        $("#myModal").modal('show');
        $("#myModalLabel").text(ad);
        $("#bn").val("");
      $("#bncd").val("");
      $("#bnkid").val("");
        $("#typ").val('create');
    }
</script>
<script>
    function editrates(id,bnm,bcd,sym,ed){
        $("#myModal").modal('show');
        $("#myModalLabel").text('Edit Rate');
      $("#bn").val(bnm);
      $("#bncd").val(bcd);
      $("#bnkid").val(id);
      $("#typ").val('update');

      let y = document.getElementById("cursybl");
      for(i=0;i<y.length;i++){
        if(y.options[i].value == sym){
          y.options[i].selected =true;
        }
      }
    }
</script>
    <script type="text/javascript">
  $(document).ready(function(){
     let aud = $("#acoff").dataTable({
      'pageLength':25,
      'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
    });

    $("#bankd").submit(function(e){
      e.preventDefault();
      $.ajax({
        url: $("#bankd").attr('action'),
        method: 'post',
        contentType : false,
        processData : false,
        data: new FormData(document.getElementById('bankd')),
        beforeSend:function(){
          $("#btnssubmit").text('Please wait...');
          $("#btnssubmit").attr('disabled',true);
        },
        success:function(data){
          if(data.status == 'success'){
            $("#btnssubmit").text('Save');
          $("#btnssubmit").attr('disabled',false);
          toastr.success(data.msg);
            window.location.reload();
          }else{
            toastr.error(data.msg);
             $("#btnssubmit").text('Save');
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
          $("#btnssubmit").text('Save');
          $("#btnssubmit").attr('disabled',false);
          return false;
        }
      });
    });
  });
</script>


<script type="text/javascript">

  function deleterecord(url,ids){
    if(confirm('Are you sure you want to delete these record')){
        $.ajax({
        url: url,
        method: 'get',
        beforeSend:function(){
          $(".loader").css('visibility','visible');
          $(".loadingtext").text('Deleting...');
        },
        success:function(data){
          if(data.status == 'success'){
            $(".loader").css('visibility','hidden');
          toastr.success(data.msg);
          $("#d"+ids).remove();
          }else{
            toastr.error(data.msg);
            $(".loader").css('visibility','hidden');
             return false;
           }
        },
        error:function(xhr,status,errorThrown){
             $(".loader").css('visibility','hidden');
            toastr.error('Error '+errorThrown);
          return false;
        }
      });
      }  
  }
</script>
@endsection
