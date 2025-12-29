@extends('layout.app')
@section('title')
    Manage User Roles
@endsection
@section('pagetitle')
Manage User Roles
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                           <a href="{{route('roles.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Roles</a>
                        </div>
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                  @include('includes.success')
                    </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="acoff">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Role Name</th>
                                    <th>Premissions</th>
                                    <th></th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($roles as $role)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td style="width:15%">{{ucwords($role->name)}}</td>
                                    <td style="width:70%">
                                      <div style="overflow-y: scroll; height:100px;">
                                           @foreach ($role->permissions as $key => $item)
                                      <span class="badge vd_bg-black'}}">{{ucwords($item->name)}}</span>
                                      @endforeach
                                      </div>
                                    </td>
                                    <td>
                                        {{-- <a href="{{route('roles.addprm',['id' => $role->id])}}" class="btn vd_btn vd_bg-twitter btn-sm">Add Premissions</a> --}}
                                        <a href="{{route('roles.edit',['id' => $role->id])}}" class="btn vd_btn vd_bg-blue btn-sm">Edit</a>
                                        {{-- <a href="{{route('branch.delete',['id' => $item->id])}}" class="btn vd_btn vd_bg-red btn-sm" onclick="return confirm('are you sure you want to delete these branch')">Delete</a> --}}
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
    <script type="text/javascript">
  $(document).ready(function(){
    $("#acoff").dataTable({'pageLength':25});
  });
</script>
@endsection
