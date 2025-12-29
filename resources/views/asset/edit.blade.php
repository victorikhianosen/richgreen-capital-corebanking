@extends('layout.app')
@section('title')
    Edit Assets
@endsection
@section('pagetitle')
Edit Assets
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('assets.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('assets.update',['id' => $ad->id])}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Asset Type</label>
                        <div class="col-sm-7 controls">
                            <select name="asset_type" class="width-70" id="asty" onchange="document.getElementById('inial').value=this.options[this.selectedIndex].getAttribute('data-value');">
                                <option selected disabled>Select...</option>
                                @foreach ($asstypes as $asstyp)
                                   <option value="{{$asstyp->id}}" {{$asstyp->id == $ad->asset_type_id ? "selected" : ""}} data-value="{{$asstyp->initial}}">{{$asstyp->name}} [{{$asstyp->type}}]</option>  
                                @endforeach
                            </select>
                        </div>
                      </div>
                      <input type="hidden" id="inial" name="initial" value="">
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Date Purchased</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="date" name="date_purchased" id="purc" value="{{$ad->purchase_date}}" autocomplete="off" placeholder="Enter Pu" data-rel="tooltip-right" data-original-title="Enter branch name">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Price</label>
                        <div class="col-sm-7 controls">
                            <input type="number" name="price" id="prc" class="width-70" value="{{$ad->purchase_price}}" placeholder="Price">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Replacement Value</label>
                        <div class="col-sm-7 controls">
                            <input type="number" name="replacement_value" id="prc" value="{{$ad->replacement_value}}" class="width-70" placeholder="Price">
                        </div>
                      </div>
                      {{-- <div class="form-group">
                        <label class="col-sm-2 control-label">Serial Number</label>
                        <div class="col-sm-7 controls">
                            <input type="number" name="serial_number" id="prc" class="width-70" value="{{$ad->serial_number}}" placeholder="Serial Number">
                        </div>
                      </div> --}}
                      
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Purchased From</label>
                        <div class="col-sm-7 controls">
                            <input type="text" name="purchased_from" id="prc" class="width-70" value="{{$ad->bought_from}}" placeholder="Purchased From">
                        </div>
                      </div>

                       {{-- <div class="form-group">
                        <label class="col-sm-2 control-label">File</label>
                        <div class="col-sm-7 controls">
                            <input type="file" name="file" id="pfil" class="width-70" placeholder="Price">
                        </div>
                      </div> --}}

                      <div class="form-group">
                        <label class="col-sm-2 control-label">Note</label>
                        <div class="col-sm-7 controls">
                            <textarea name="note" id="nte" class="width-70" cols="30" rows="3">{{$ad->note}}</textarea>
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
    var opt = $("#asty option:selected").attr('data-value');
     $("#inial").val(opt);
  });
</script>
@endsection