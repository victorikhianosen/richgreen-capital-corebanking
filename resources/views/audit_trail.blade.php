@extends('layout.app')
@section('title')
    Audit Trail
@endsection
@section('pagetitle')
Audit Trail
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                           {{-- <a href="{{route('branch.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Branch</a> --}}
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
                                    <th>Name</th>
                                    <th>Module</th>
                                    <th>Note</th>
                                    <th>Initiated Date</th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($audits as $item)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($item->user)}}</td>
                                    <td>{{ucwords($item->module)}}</td>
                                    <td>{{ucfirst($item->notes)}}</td>
                                    <td>{{date('d-M-Y h:ia',strtotime($item->created_at))}}</td>
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
    <script type="text/javascript">
  $(document).ready(function(){
     let aud = $("#acoff").dataTable({
      'pageLength':25,
      'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
    });
  });
</script>
@endsection
