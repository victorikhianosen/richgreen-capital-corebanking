@extends('layout.app')
@section('title')
    Edit Asset Type
@endsection
@section('pagetitle')
Edit Asset Type
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('assetstyp.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('assetstyp.update',['id' => $astyped->id])}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Asset Type Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="name" id="brnd" value="{{$astyped->name}}" autocomplete="off" placeholder="Enter Asset Type Name" data-rel="tooltip-right" data-original-title="Enter branch name">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Type</label>
                        <div class="col-sm-7 controls">
                            <select name="type" class="width-70" id="asty" onchange="document.getElementById('inial').value=this.options[this.selectedIndex].getAttribute('data-value');">
                                <option selected disabled>Select...</option>
                                <option value="current" {{$astyped->type == "current" ? "selected" : ""}} data-value="CA">Current</option>
                                <option value="fixed" {{$astyped->type == "fixed" ? "selected" : ""}} data-value="FA">Fixed</option>
                                <option value="intangible" {{$astyped->type == "intangible" ? "selected" : ""}} data-value="IA">Intangible</option>
                                <option value="other" {{$astyped->type == "other" ? "selected" : ""}} data-value="OA">Other</option>
                            </select>
                        </div>
                      </div>
                      
                      <input type="hidden" id="inial" name="initial" value="">
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
    var opt = $("#asty option:selected").attr('data-value');
     $("#inial").val(opt);
  });
</script>
@endsection