@extends('layout.app')
@section('title')
    Create Collateral Type
@endsection
@section('pagetitle')
Create Collateral Type
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                      @if (!empty($_GET['return_url']))
                         <a href="{{url($_GET['return_url'])}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                      @endif
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                     <form action="{{route('comment.store')}}" method="post" onsubmit="thisForm()">
                      @csrf
                      <div class="form-group">
                        <label class="col-sm-12 control-label">Loan Comment</label>
                        <div class="col-sm-12">
                            <textarea name="notes" id="" cols="10" rows="4">{{old('notes')}}</textarea>
                        </div>
                    </div>

                    @if(!empty($_GET['return_url']))
                      <input type="hidden" value="{{$_GET['return_url']}}" name="return_url">
                  @endif
                  
                  @if(!empty($_GET['loanid']))
                      <input type="hidden" value="{{$_GET['loanid']}}" name="loanid">
                  @endif

                    <div class="form-group form-actions">
                      <div class="col-sm-4"> </div>
                      <div class="col-sm-7" style="margin: 10px 0px">
                        <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit">Save Record</button>
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
    
  });
</script>
@endsection