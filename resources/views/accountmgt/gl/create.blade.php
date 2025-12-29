@extends('layout.app')
@section('title')
    Create General Ledger
@endsection
@section('pagetitle')
Create General Ledger
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('gl.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('gl.store')}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      
                      
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Account Type</label>
                        <div class="col-sm-7 controls">
                           <select name="account_type" class="width-70 form-control" autocomplete="off" required id="actype" onchange="document.getElementById('cod').value = this.options[this.selectedIndex].getAttribute('data-code');">
                            <option selected disabled>Select Type</option>
                            @foreach ($actyps as $typ)
                                <option value="{{$typ->name}}" data-code="{{$typ->code}}">{{ucwords($typ->name)}}</option>
                            @endforeach
                           </select>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Account Category</label>
                        <div class="col-sm-7 controls">
                           <select name="account_category" class="width-70 form-control" autocomplete="off" required id="acatetye" onchange="">
                            <option selected disabled>Select Category</option>
                              @foreach ($accates as $actyp)
                                <option value="{{$actyp->id}}">{{ucwords($actyp->name)." [".$actyp->type."]"}}</option>
                            @endforeach
                           </select>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70 form-control" type="text" autofocus name="name" id="fname" autocomplete="off" placeholder="Enter Ledger Name" value="{{old('name')}}">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-3 control-label">Currency</label>
                          <div class="col-sm-7 controls">
                            <select class="width-70 form-control" name="currency_type" required autocomplete="off">
                                  <option selected disabled>Select...</option>
                                <option value="">Naira</option>
                                  @foreach ($exrate as $item)
                                      <option value="{{$item->id}}">{{$item->currency}}</option>
                                  @endforeach
                              </select>
                         </div>
                      </div>

                     
                      
                      <input class="width-70 form-control" type="hidden" autofocus name="code" id="cod" autocomplete="off" value="">

                                            
                        <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Save Record</button>
                              
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
     $("#acatetye").select2();
     
  });
</script>
@endsection