@extends('layout.app')
@section('title')
    Edit Roles
@endsection
@section('pagetitle')
Edit Roles
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                      <a href="{{route('roles')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                   </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal" id="changepass" action="{{route('roles.update',['id' => $ed->id])}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Role</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="role" value="{{$ed->name}}" required placeholder="Enter Role" data-toggle="tooltip" data-placement="top" data-original-title="First Name">
                        </div>
                      </div>
                      
                      <h4>Permissions</h4>
                        <div>
                          <div class="vd_checkbox checkbox-success">
                            <input type="checkbox"  id="checkb" class="chk"  autocomplete="off">
                            <label for="checkb" class="lbl">Check All</label>
                          </div>
                        </div><hr>
                        
                        <div class="form-group">
                        <div class="row">
                          @foreach ($permissions as $item)
                          <div class="col-md-4 col-lg-4 col-sm-12 col-xs-12">
                            <div class="vd_checkbox checkbox-success">
                              <input type="checkbox" value="{{$item->name}}" name="permissions[]" id="checkbox-{{$item->id}}" class="chk"  autocomplete="off"
                              @if (in_array($item->id, $rolepermissions))
                                       checked
                                    @endif
                              >
                              <label for="checkbox-{{$item->id}}" class="lbl">{{ucwords($item->name)}}</label>
                            </div>
                          </div>
                          @endforeach
                        </div>

                      </div>
                      
                        <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Update Record</button>
                              
                            </div>
                          </div>
                    </form>
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
  $(document).ready(function(){
$("#pmr").select2();

$("#checkb").click(function(){
    if ($(this).is(":checked")) {
    $(".chk").prop('checked',true)
    } else {
    $(".chk").prop('checked',false)
    }
});

  });
</script>
@endsection