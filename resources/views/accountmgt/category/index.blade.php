@extends('layout.app')
@section('title')
    Account Category
@endsection
@section('pagetitle')
Account Category
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                             @can('upload account category')
                          <a href="{{route('ac.category.batchupload')}}?type=ac" class="btn btn-danger btn-sm"><span class="menu-icon"> <i class="fa fa-upload"></i> </span>Upload Account Category</a>
                          @endcan
                           @can('create account category')
                           <a href="{{route('ac.category.create')}}" class="btn btn-default btn-sm"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Account Category</a>
                      @endcan
                        </div>
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                  @include('includes.success')
                    </div>
                    </div>
                    <form action="{{route('removecate')}}" id="removeCategory" method="post" onsubmit="return performAction(this)">
                      @csrf
                      
                      <div class="text-center" style="margin: 7px 0px">
                        <input type="submit" class="btn btn-danger" name="cmddeleterecod" value="Delete">

                       </div>

                      <div class="table-responsive">
                        <table class="table table-striped table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr>
                                  <th><input type="checkbox" name="" id="checkall" style="cursor: pointer"></th>
                                    <th>Sn</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Descriptions</th>
                                    <th></th>
                                 </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($accates as $item)
                                <tr id="d{{$item->id}}">
                                  <td><input type="checkbox" name="accateid[]" style="cursor: pointer" value="{{$item->id}}" class="checkcust ch{{$i+1}}" id=""></td>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($item->name)}}</td>
                                    <td>{{ucwords($item->type)}}</td>
                                    <td>{{$item->description}}</td>
                                    <td>
                                           @can('create account category')
                                        <a href="{{route('ac.category.edit',['id' => $item->id])}}" class="btn menu-icon vd_bd-blue vd_blue btn-sm"><i class="fa fa-pencil"></i> </a>
                                       @endcan
                                          @can('delete account category')
                                        <a href="javascript:void(0)"  onclick="deleterecord('{{route('ac.category.delete',['id' => $item->id])}}','{{$item->id}}')" class="btn menu-icon vd_bd-red vd_red btn-sm" ><i class="fa fa-times"></i> </a>
                                          @endcan
                                    </td>
                                </tr>
                                <?php $i++?>
                                @endforeach
                            </tbody>
                        </table>
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
    <script type="text/javascript">
  $(document).ready(function(){
    $("#acoff").dataTable({'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });

  $("#checkall").click(function(){
    if($(this).is(":checked")){
        $(".checkcust").prop('checked',true);  
    }else{
      $(".checkcust").prop('checked',false);
    }
  });

    $("#removeCategory").submit(function(e){
        e.preventDefault();
      let url = $("#removeCategory").attr('action');

        $.ajax({
        url: url,
        method: 'post',
        data: $("#removeCategory").serialize(),
        beforeSend:function(){
          $(".loader").css('visibility','visible');
          $(".loadingtext").text('Deleting...');
        },
        success:function(data){
          if(data.status == 'success'){
            $(".loader").css('visibility','hidden');
          toastr.success(data.msg);
            window.location.reload();
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
       
    });

  });
</script>

<script type="text/javascript">
  function whichButton(){
    var buttonValue = "";
    let checkbox = document.querySelectorAll(".checkcust");
    for(i = 0; i < checkbox.length; i++){//scan all form element
        if(checkbox[i].checked){
          buttonValue = checkbox[i].value;
        }
      
    }
    return buttonValue;
  }
  function performAction(thisform){
     with(thisform){
      if(whichButton()){
        if(confirm('Continue with selected action?')){return true;}
        else{return false;}
      }else{
        window.alert("Please make a selection to proceed");
        return false;
      }
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
</script>
@endsection
