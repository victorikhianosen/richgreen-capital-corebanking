@extends('layout.app')
@section('title')
    Edit Other Income 
@endsection
@section('pagetitle')
Edit Other Income 
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('income.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('income.update',['id' => $incms->id])}}" method="post" role="form"  enctype="multipart/form-data" onsubmit="thisForm()">
                      @csrf
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Other Income Type</label>
                        <div class="col-sm-7 controls">
                            <select name="income_types" class="width-70 form-control" id="asty">
                                <option selected disabled>Select...</option>
                                @foreach ($incmtypes as $incmtyp)
                                   <option value="{{$incmtyp->id}}" {{$incmtyp->id == $incms->other_income_type_id ? 'selected' : ''}}>{{$incmtyp->name}}</option>  
                                @endforeach
                            </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Income Amount</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" required name="amount" id="br" value="{{$incms->amount}}" autocomplete="off" placeholder="Enter Income Amount">
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Date</label>
                        <div class="col-sm-7 controls">
                            <input type="date" name="date" value="{{$incms->income_date}}" id="dtprc" class="width-70">
                        </div>
                      </div>
                      
                      
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Description</label>
                        <div class="col-sm-7 controls">
                            <textarea name="note" id="nte" class="width-70" cols="30" rows="3">{{$incms->notes}}</textarea>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Files (doc, docx,pdf,image)</label>
                        <div class="col-sm-7 controls">
                            <input type="file" name="file" id="flel" class="width-70" accept=".jpg,.jpeg,.png,.docx,.doc,.pdf">
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
    var recuropt = $("#recur option:selected").val();
    if (recuropt === '1') {
      $("#showrecur").show();
    } else {
      $("#showrecur").hide();
    };
  });
</script>
@endsection