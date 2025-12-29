@extends('layout.app')
@section('title')
    Send SMS
@endsection
@section('pagetitle')
Send SMS
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
                    <div class="row">
                    <div class="col-sm-12">
                      @include('includes.errors')
                       @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('email.sendsms')}}" id="sendmsg" method="post" role="form">
                      @csrf 
                       
                      @if (!empty($_GET['sendsms']) && $_GET['sendsms'] == true)
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Send To</label>
                        <div class="col-sm-9 controls">
                          <input type="text" readonly class="width-70" value="{{ucwords($cusms->last_name." ".$cusms->first_name)}} [{{$cusms->phone}}]">
                              <input type="hidden" class="width-70" name="phone" value="{{$cusms->phone}}">
                              <input type="hidden" class="width-70" name="sendsms" value="{{$_GET['sendsms']}}">
                        </div>
                      </div>
                      @else 
                      
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Send To</label>
                        <div class="col-sm-9 controls">
                            <select class="width-70" name="phones[]" multiple required data-placeholder="Select Phone Numbers..."  id="pmr" autocomplete="off">
                              <option selected disabled>Select Phone Numbers...</option> 
                              @foreach ($cusms as $mail)
                                   <option value="{{$mail->phone}}">{{ucwords($mail->last_name." ".$mail->first_name)}} [{{$mail->phone}}]</option>
                                @endforeach
                            </select>
                          <span>
                              <div class="vd_checkbox checkbox-success">
                                <input type="checkbox" value="1" name="selectall" id="checkbox-1" class="chk"  autocomplete="off">
                                <label for="checkbox-1" class="lbl">Select All Phone Numbers </label>
                              </div>
                          </span>
                        </div>
                      </div>
                     
                      @endif
                      
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Message</label>
                        <div class="col-sm-9 controls">
                          <textarea id="message" name="message" class="width-100 form-control"  rows="10" placeholder="Write your message here"></textarea>
                        </div>
                      </div>
                      <input type="hidden" name="userid" value="{{Auth::user()->id}}">
                      <input type="hidden" name="branchid" value="{{session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id}}">
                      {{-- <div class="form-group">
                        <label class="col-sm-2 control-label">Attach File</label>
                        <div class="col-sm-7 controls">
                            <i class="fa fa-paperclip fa-2x" style="cursor: pointer" id="openattachfile" title="attach file"></i>&nbsp;<span class="filename"></span>
                          <input class="width-70" type="file" name="file[]" id="file"  placeholder="Enter Subject" multiple style="display: none">
                        </div>
                      </div>  --}}
                        <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Send Message</button>
                              
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
    $('#message').wysihtml5();

  $(".chk").change(function(){
    if($(".chk").is(':checked')){
            $("#pmr").attr('disabled',true);
            $(".lbl").text('Unselect All Phone Numbers');
        }else{
            $("#pmr").attr('disabled',false);
            $(".lbl").text('Select All Phone Numbers');
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

  $("#sendmsg").submit(function(e){
      e.preventDefault();
      $.ajax({
        url: $("#sendmsg").attr('action'),
        method: 'post',
        contentType : false,
        processData : false,
        data: new FormData(document.getElementById('sendmsg')),
        beforeSend:function(){
          $("#btnssubmit").text('Please wait...');
          $("#btnssubmit").attr('disabled',true);
        },
        success:function(data){
          if(data.status == 'success'){
            $("#btnssubmit").text('Send Message');
          $("#btnssubmit").attr('disabled',false);
          
          toastr.success(data.msg);
            window.location.href=data.uredirect;
          }else{
              toastr.error(data.msg);
             $("#btnssubmit").text('Send Message');
           $("#btnssubmit").attr('disabled',false);
             return false;
           }
        },
        error:function(xhr,status,errorThrown){
          let err = '';
          $.each(xhr.responseJSON.errors, function (key, value) {
                err += value;
            });
            toastr.error(err);
          $("#btnssubmit").text('Send Message');
          $("#btnssubmit").attr('disabled',false);
          return false;
        }
      });
    });

  });
</script>
@endsection