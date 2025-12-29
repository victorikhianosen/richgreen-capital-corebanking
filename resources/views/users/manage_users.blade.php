@extends('layout.app')
@section('title')
    Users
@endsection
@section('pagetitle')
Users
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                          @can('create users')
                           <a href="{{route('user.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Users</a>
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
                        <table class="table table-striped" id="acoff">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Gender</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;
                                $rol ="";
                                ?>
                                @foreach ($users as $item)
                                <tr id="d{{$item->id}}">
                                    <td>{{$i+1}}</td>
                                    <td style="width:45%">{{ucwords($item->last_name." ".$item->first_name)}}</td>
                                    <td>{{$item->phone}}</td>
                                    <td>{{$item->gender}}</td>
                                    <td>{{$item->email}}</td>
                                    <td>
                                      @foreach ($item->roles as $role)
                                       <span class="badge vd_bg-blue">{{$role->name}}</span>
                                       <?php $rol = $role->name;?>
                                      @endforeach
                                    </td>
                                    
                                    <td><span class="badge {{$item->status ? 'vd_bg-green' : 'vd_bg-black'}}">{{$item->status ? 'active' : 'Not Active'}}</span></td>
                                    
                                    <td style="width:60%">
                                       
                                        @if ($item->status)
                                            <a href="javascript:void(0)" onclick="activadectv('{{route('user.deactive',['id' => $item->id])}}')" class="btn vd_btn vd_bg-red btn-sm" title="Deactivate"><i class="fa fa-times"></i></a>
                                        @else
                                         <a href="javascript:void(0)" onclick="activadectv('{{route('user.active',['id' => $item->id])}}')" class="btn vd_btn vd_bg-green btn-sm" title="Activate"><i class="fa fa-check"></i></a>
                                        @endif
                                        
                                           @can('edit users')
                                            <a href="{{route('user.edit',['id' => $item->id])}}" class="btn vd_btn vd_bg-blue btn-sm" title="Edit"><i class="fa fa-edit"></i></a>
                                        @endcan

                                           @can('reset qrcode')
                                            <a href="javascript:void" onclick="resetQrCodde('{{route('user.resetqr',['id' => $item->id])}}')" class="btn vd_btn vd_bg-yellow btn-sm" title="Reset Qrcode"><i class="fa fa-refresh"></i></a>
                                        @endcan
                                    
                                        @can('delete users')
                                        <a href="javascript:void(0)" onclick="deleterecord('{{route('user.delete',['id' => $item->id])}}','{{$item->id}}')" class="btn vd_btn vd_bg-red btn-sm" title="delete"><i class="fa fa-trash-o"></i></a>
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
    <script type="text/javascript">
  $(document).ready(function(){
    $("#acoff").dataTable({'pageLength':25});
  });
</script>

<script>

function resetQrCodde(url){
      if(confirm('Are you sure you want to reset Qrcode')){
          $.ajax({
          url: url,
          method: 'get',
          beforeSend:function(){
            $(".loader").css('visibility','visible');
          $(".loadingtext").text('Resetting... Please Wait');
          },
          success:function(data){
            if(data.status == 'success'){
                $(".loader").css('visibility','hidden');
                toastr.success(data.msg);
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
  
    function activadectv(url){
      $.ajax({
          url: url,
          method: 'get',
          beforeSend:function(){
            $(".loader").css('visibility','visible');
          $(".loadingtext").text('Please Wait...');
          },
          success:function(data){
            if(data.status == 'success'){
              $(".loader").css('visibility','hidden');
                toastr.success(data.msg);
               window.location.reload();
            }else{
                $(".loader").css('visibility','hidden');
                toastr.error(data.msg);
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
  </script>
@endsection
