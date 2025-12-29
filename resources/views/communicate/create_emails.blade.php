@extends('layout.app')
@section('title')
    Send Mail
@endsection
@section('pagetitle')
Send Mail
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
                    <form class="form-horizontal"  action="{{route('email.sendmail')}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf 
                       
                      @if (!empty($_GET['sendmail']) && $_GET['sendmail'] == true)
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Send To</label>
                        <div class="col-sm-9 controls">
                          <input type="text" readonly class="width-70" value="{{$remail}}">
                              <input type="hidden" class="width-70" name="mail" value="{{$remail}}">
                        </div>
                      </div>
                      @else 
                      
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Send To</label>
                        <div class="col-sm-9 controls">
                            <select class="width-70" name="mail" required data-placeholder="Select Email..."  id="pmr" autocomplete="off">
                            <option selected disabled>Select Email...</option>
                                @foreach ($cusem as $mail)
                                   <option value="{{$mail->email}}">{{$mail->email}}</option>
                                @endforeach
                            </select>
                          <span>
                              <div class="vd_checkbox checkbox-success">
                                <input type="checkbox" value="1" name="selectall" id="checkbox-1" class="chk"  autocomplete="off">
                                <label for="checkbox-1" class="lbl">Select All Emails </label>
                              </div>
                          </span>
                        </div>
                      </div>
                      <div class="form-group" id="allemls" style="display:none">
                        <div class="col-sm-9 controls">
                          @foreach ($cusem as $email)
                           <input type="hidden" class="width-70" name="mail_to[]" value="{{$email->email}}">
                          @endforeach
                        </div>
                      </div>
                      @endif
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Subject</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="subject" required placeholder="Enter Subject" value="{{old('subject')}}">
                        </div>
                      </div>
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
            $("#allemls").show();
            $("#pmr").attr('disabled',true);
            $(".lbl").text('Unselect All Emails');
        }else{
            $("#allemls").hide();
            $("#pmr").attr('disabled',false);
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