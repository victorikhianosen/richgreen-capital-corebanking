@extends('layout.app')
@section('title')
    Collateral Types
@endsection
@section('pagetitle')
Collateral Types
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('collatype.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span>Add Type</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <div class="table-responsive">
                      <table id="acoff" class="table table-bordered table-condensed table-hover">
                  <thead>
                  <tr>
                      <th>Name</th>
                      <th></th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($data as $key)
                      <tr>
                          <td>{{ ucwords($key->name) }}</td>
                          <td>
                            <a href="{{route('collatype.edit',['id' => $key->id])}}" class="btn btn-info btn-sm"><i
                              class="fa fa-edit"></i> Edit </a>

                              <a href="{{route('collatype.delete',['id' => $key->id])}}"
                                class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete these record');"><i
                                         class="fa fa-trash"></i> Delete </a>
                          </td>
                      </tr>
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
    $("#acoff").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
@endsection