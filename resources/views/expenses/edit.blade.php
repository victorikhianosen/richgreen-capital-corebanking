@extends('layout.app')
@section('title')
    Edit Expenses
@endsection
@section('pagetitle')
Edit Expenses
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('expenses.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('expenses.update',['id' => $exp->id])}}" method="post" role="form"  enctype="multipart/form-data" onsubmit="thisForm()">
                      @csrf
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Expense Type</label>
                        <div class="col-sm-7 controls">
                            <select name="expense_types" class="width-70 form-control" id="asty">
                                <option selected disabled>Select...</option>
                                @foreach ($exptypes as $exptyp)
                                   <option value="{{$exptyp->id}}" {{$exptyp->id == $exp->expense_type_id ? 'selected' : ''}}>{{$exptyp->name}} [{{$exptyp->expcat}}]</option>  
                                @endforeach
                            </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Expense Amount</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number"  required name="amount" id="brnd" value="{{$exp->amount}}" autocomplete="off" placeholder="Enter Expense Amount">
                          <input class="width-70" type="hidden" required name="current_amount" value="{{$exp->amount}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Expense Slip No</label>
                        <div class="col-sm-7 controls">
                            <input type="text" name="slip_no" required id="prc" value="{{$exp->expslip}}" class="width-70" placeholder="Slip Number">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Date</label>
                        <div class="col-sm-7 controls">
                            <input type="date" name="date" value="{{$exp->date}}" id="dtprc" class="width-70">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Is Expense Recurring?</label>
                        <div class="col-sm-7 controls">
                            <select name="recurring" class="width-70 form-control" id="recur" onchange="if(this.value == 1){document.getElementById('showrecur').style.display='block';}else{document.getElementById('showrecur').style.display='none'}">
                                   <option value="1" {{$exp->recurring == '1' ? 'selected' : ''}}>Yes</option>  
                                   <option value="0" {{ $exp->recurring == '0' ? 'selected' : ''}}>No</option>  
                              </select>
                        </div>
                      </div>
                      <div style="margin: 10px auto; display:none" id="showrecur">
                        <div class="row">
                          <div class="col-md-8 col-sm-12">
                            <div class="col-md-6 col-sm-12">
                              <label>Recurring Frequency</label>
                              <input type="number" name="recurring_frequency" value="{{$exp->recur_frequency}}">
                            </div>
                            <div class="col-md-6 col-sm-12">
                              <label>Recurring Type</label>
                              <select name="recurring_type" class="form-control" id="recue">
                                <option value="daily" {{$exp->recur_type == 'daily' ? 'selected' : ''}}>Daily</option>  
                                <option value="weekly" {{$exp->recur_type == 'weekly' ? 'selected' : ''}}>Weekly</option>  
                                <option value="monthly" {{$exp->recur_type == 'monthly' ? 'selected' : ''}}>Monthly</option>  
                                <option value="yearly" {{$exp->recur_type == 'yearly' ? 'selected' : ''}}>Yearly</option>  
                           </select>                          
                          </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-8 col-sm-12">
                            <div class="col-md-6 col-sm-12">
                              <label>Recurring Starts</label>
                              <input type="date" name="recurring_start" value="{{$exp->recur_start_date}}">
                            </div>
                            <div class="col-md-6 col-sm-12">
                              <label>Recurring Ends</label>
                              <input type="date" name="recurring_end" value="{{$exp->recur_end_date}}">
                            </div>
                          </div>
                        </div>
                      </div>
                      
                    
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Description</label>
                        <div class="col-sm-7 controls">
                            <textarea name="note" id="nte" class="width-70" cols="30" rows="3">{{$exp->note}}</textarea>
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