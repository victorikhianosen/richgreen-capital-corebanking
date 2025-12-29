@extends('layout.app')
@section('title')
    View Mail
@endsection
@section('pagetitle')
View Mail
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('emails.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <table class="table table-bordered table-striped">
                      <tr><td>Recipient</td><td>{{$ems->recipient}}</td></tr>
                        <tr><td>Subject</td><td>{{$ems->subject}}</td></tr>
                        <tr><td>Message</td><td>{!!$ems->message!!}</td></tr>
                    </table>
                      
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
    $('#message').wysihtml5();

  $(".chk").change(function(){
    if($(".chk").is(':checked')){
            $("#allemls").show();
            $(".lbl").text('Unselect All Emails');
        }else{
            $("#allemls").hide();
            $(".lbl").text('Select All Emails');
        }
  });

  $("#openattachfile").click(function(){
    $("#file").trigger('click');
  });

  $("#file").change(function(){
    for(i=0; i < this.files.length; i++){
        $(".filename").append('<span>'+this.files[i].name+' &nbsp;</span>');
    }
  });
  });
</script>
@endsection