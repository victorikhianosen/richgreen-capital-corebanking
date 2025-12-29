@extends('layout.app')
@section('title')
    Branches
@endsection
@section('pagetitle')
List of user at {{Str::lower($b->branch_name)}} branches
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                           <a href="{{route('branch.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                        </div>
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                  @include('includes.success')
                    </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="acoff">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Name</th>
                                    <th></th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($allbranchusers as $item)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($item->last_name." ".$item->first_name)}}</td>
                                    <td>
                                        <a href="javascript:void(0)" onclick="moveuserbranch('{{$b->id}}','{{$item->uid}}')" class="btn vd_btn vd_bg-blue btn-sm">Move to another branch</a>
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
        <h4 class="modal-title" id="myModalLabel">Move User</h4>
      </div>
      <div class="modal-body"> 
        <form class="form-horizontal" action="{{route('branch.moveubrnd')}}" method="post" id="moveuser">
          @csrf
          <div class="form-group">
            <label class="col-sm-4 control-label">Branches</label>
            <div class="col-sm-12 controls">
               <select name="branch_id" id="brn" autocomplete="off">
                <option selected disabled>Select...</option>
                @foreach ($branches as $item)
                    <option value="{{$item->id}}">{{$item->branch_name}}</option>
                @endforeach
               </select>
            </div>
          <input type="hidden" name="userid" id="userid" value="">
        </form>
      
      </div>
      <div class="modal-footer background-login">
        <button type="button" class="btn vd_btn vd_bg-grey" data-dismiss="modal">Close</button>
        <button type="button" class="btn vd_btn vd_bg-green" id="btnssubmit" onclick="document.getElementById('moveuser').submit()">Save changes</button>
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
  function moveuserbranch(b,u){
      $("#myModal").modal('show');
      $("#userid").val(u);
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
