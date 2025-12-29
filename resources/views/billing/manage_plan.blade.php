@extends('layout.app')
@section('title')
    Manage Subcription Plan
@endsection
@section('pagetitle')
Manage Subcription Plan
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                           <a href="#" onclick="createplan()" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Plan</a>
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
                                    <th>Subcription Plan</th>
                                    <th>Duration</th>
                                    <th>VAT</th>
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
                                    <td>{{$item->duration."Days"}}</td>
                                    <td>{{$item->vat."%"}}</td>
                                    <td>{{number_format($item->price,2)}}</td>
                                    <td>
                                        <a href="#" onclick="editplan('{{$item->id}}','{{$item->package_name}}','{{$item->duration}}','{{$item->price}}','{{$item->vat}}')" class="btn vd_btn vd_bg-blue btn-sm">Edit</a>
                                        <a href="{{route('deleteplan',['id' => $item->id])}}" class="btn vd_btn vd_bg-red btn-sm" onclick="return confirm('are you sure you want to delete these record')">Delete</a>
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
          <form class="form-horizontal" action="{{route('store.subcriptinplan')}}" method="post" id="moveuser">
            @csrf
            <div class="form-group">
              <label class="col-sm-3 control-label">Package Name</label>
              <div class="col-sm-8 controls">
                <input class="width-70" type="text" name="package_name" id="pakname" value="{{old('package_name')}}" autocomplete="off" placeholder="Package Name">
              </div>
              </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">Duration</label>
              <div class="col-sm-8 controls">
                <select name="duration" class="width-70" id="dura">
                    <option selected disabled>Select...</option>
                    <option value="30">Monthly</option>
                    <option value="90">Quaterly</option>
                    <option value="180">Bi-annual</option>
                    <option value="365">Annual</option>
                </select>
              </div>
              </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">Vat</label>
              <div class="col-sm-8 controls">
                <input class="width-70" type="number" step="0.01" name="vat" id="vt" value="{{old('vat')}}" autocomplete="off" placeholder="Vat">
              </div>
              </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">Price</label>
              <div class="col-sm-8 controls">
                <input class="width-70" type="number" name="price" id="price" value="{{old('price')}}" autocomplete="off" placeholder="Price">
              </div>
              </div>
            <input type="hidden" name="savetyp" id="stype" value="">
            <input type="hidden" name="planid" id="pid" value="">
          </form>
        
        </div>
        <div class="modal-footer background-login">
          <button type="button" class="btn vd_btn vd_bg-grey" data-dismiss="modal">Close</button>
          <button type="button" class="btn vd_btn vd_bg-green" id="cmbtnssubmit" onclick="document.getElementById('cmbtnssubmit').disabled=true; document.getElementById('moveuser').submit()">Save Record</button>
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
    function createplan(){
        $("#myModal").modal('show');
        $("#myModalLabel").text('Create Plan');
        $("#stype").val('create');
      }

    function editplan(id,nm,dr,pr,vt){
        $("#myModal").modal('show');
        $("#myModalLabel").text('Update Plan');
        $("#stype").val('update');
        $("#pakname").val(nm);
        $("#price").val(pr);
        $("#vt").val(vt);
        $("#pid").val(id);
         var y = document.getElementById("dura");
         for(i=0; i<y.length;i++){
            if(y.options[i].value == dr){
                y.options[i].selected = true;
            }
         }
      }
  </script>
    <script type="text/javascript">
  $(document).ready(function(){
    $("#acoff").dataTable({'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
@endsection
