@extends('layout.app')
@section('title')
    Manage Charges  
@endsection
@section('pagetitle')
Manage Charges  
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                      <div style="text-align: end">
                      @can('manage savings fees')
                      <a href="{{route('charges.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Charges</a>
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
                        <table class="table table-striped table-sm table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Charge Name</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th></th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($charges as $item)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{$item->chargename}}</td>
                                    <td>{{number_format($item->amount,2)}}</td>
                                    <td>{{$item->description}}</td>
                                    <td>
                                          @can('edit charges')
                                              <a href="{{route('charges.edit',['id' => $item->id])}}" title="Edit" class="btn menu-icon vd_bd-blue vd_blue btn-sm"><i class="fa fa-pencil"></i></a>
                                            @endcan
                                            
                                            @can('delete charges')
                                              <a href="{{route('charges.delete',['id' => $item->id])}}" title="Delete" class="btn menu-icon vd_bd-red vd_red btn-sm" onclick="return confirm('are you sure you want to delete these record')"><i class="fa fa-times"></i></a>
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

@endsection
@section('scripts')
<script>
  function edittran(id,typ){
    $("#mytranModal").modal('show');
    $("#trnid").val(id);
    let x = document.getElementById('type');
    for(i=0; i<x.length; i++){
      if(x.options[i].value == typ){
        x.options[i].selected = true;
      }
    }
  }
</script>
    <script type="text/javascript">
  $(document).ready(function(){
    $("#acoff").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
@endsection
