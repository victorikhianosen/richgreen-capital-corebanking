@extends('layout.app')
@section('title')
    Account Types
@endsection
@section('pagetitle')
Account Types
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                           {{-- <a href="{{route('acofficer.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Account Officer</a> --}}
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
                        <table class="table table-striped table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Name</th>
                                   <th>Account Code</th>
                                   <th></th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($actyps as $item)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($item->name)}}</td>
                                    <td>{{ucwords($item->code)}} <br>
                                        <div id="shwfrm{{$item->id}}" style="display:none">
                                          <form action="{{route('update.accountcode',['id' => $item->id])}}" method="post" id="formupdate{{$item->id}}">
                                            @csrf
                                            <input type="hidden" class="width-70" name="ac_id" value="{{$item->id}}">
                                            <div class="col-sm-7 controls">
                                              <div class="input-group">
                                                <input type="text" name="ac_code" value="{{$item->code}}">
                                                <span class="input-group-addon" style="cursor: pointer" onclick="document.getElementById('formupdate{{$item->id}}').submit();">Update</span> </div>
                                            </div>
                                          </form>
    
                                        </div>
                                      </td>
                                    <td>
                                        @if (Auth::user()->roles()->first()->name == "super admin")
                                        <div>
                                             <a href="javascript:void(0)" title="click to edit Account code" class="btn vd_btn vd_bg-blue btn-sm" onclick="document.getElementById('shwfrm{{$item->id}}').style.display='block';"><i class="fa fa-pencil"></i> </a>
                                             <a href="javascript:void(0)" title="click to reset to row" class="btn vd_btn vd_bg-red btn-sm" onclick="document.getElementById('shwfrm{{$item->id}}').style.display='none';">reset row</a>
                                        </div>
                                        
                                      @endif
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
    $("#acoff").dataTable({'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
@endsection
