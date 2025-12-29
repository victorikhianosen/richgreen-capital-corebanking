@extends('layout.app')
@section('title')
    Manage Mails
@endsection
@section('pagetitle')
Manage Mails
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                            @can('create communication')
                           <a href="{{route('emails.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span>Send Mail</a>
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
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Recipient</th>
                                    <!--<th>Sent By</th>-->
                                    <!--<th>Branch</th>-->
                                    <th>Initiated Date</th>
                                    <th></th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($emails as $item)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{$item->subject}}</td>
                                    <td>{!!Str::limit($item->message,'15','...')!!}</td>
                                    <td>{{$item->recipient}}</td>
                                    
                                    <td>{{date('d-M-Y h:ia',strtotime($item->created_at))}}</td>
                                    <td>
                                      <a href="{{route('emails.create',['id' => $item->id])}}?sendmail=true" class="btn menu-icon vd_bd-yellow vd_yellow btn-sm"><i class="fa fa-envelope"></i></a>
                                        @can('view communication')
                                            <a href="{{route('emails.view',['id' => $item->id])}}" class="btn menu-icon vd_bd-blue vd_blue btn-sm"><i class="fa fa-eye"></i></a>
                                        @endcan
                                        @can('delete communication')
                                        <a href="{{route('emails.delete',['id' => $item->id])}}" class="btn menu-icon vd_bd-red vd_red btn-sm" onclick="return confirm('are you sure you want to delete these record')"><i class="fa fa-times"></i></a>
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
    $("#acoff").dataTable({'pageLength':25,
        'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
    });
  });
</script>
@endsection
