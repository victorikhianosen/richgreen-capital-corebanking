@extends('layout.app')
@section('title')
    Create Expenses
@endsection
@section('pagetitle')
Create Expenses
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
                    <form class="form-horizontal"  action="{{route('expenses.store')}}" method="post" enctype="multipart/form-data" role="form" id="postexp">
                      @csrf
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Expense Type</label>
                        <div class="col-sm-7 controls">
                            <select name="expense_types" required autocomplete="off" class="width-70 form-control" id="asty">
                                <option selected disabled>Select...</option>
                                @foreach ($exptypes as $exptyp)
                                   <option value="{{$exptyp->id}}">{{$exptyp->name}} [{{$exptyp->expcat}}]</option>  
                                @endforeach
                            </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Expense GL</label>
                        <div class="col-sm-7 controls">
                            <select name="expensegl" autocomplete="off" class="width-70 form-control" required  id="exgl" >
                                    @foreach ($expns as $item)
                                    <option value="{{$item->gl_code}}">{{$item->gl_name}}</option>  
                                @endforeach
                              </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">GL To Credit</label>
                        <div class="col-sm-7 controls">
                            <select name="creditgl" autocomplete="off" class="width-70 form-control" required  id="crgl" >
                              @foreach ($crgls as $item)
                              <option value="{{$item->gl_code}}">{{$item->gl_name}} [{{$item->gl_type}}]</option> 
                          @endforeach
                              </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Expense Amount</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" required name="amount" id="brnd" value="{{old('amount')}}" autocomplete="off" placeholder="Enter Expense Amount">
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Date</label>
                        <div class="col-sm-7 controls">
                            <input type="date" name="date" autocomplete="off" value="{{old('date')}}" id="dtprc" class="width-70">
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Description</label>
                        <div class="col-sm-7 controls">
                            <textarea name="note" id="nte" class="width-70" cols="30" rows="3">{{old('note')}}</textarea>
                        </div>
                      </div>
                
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
    $("#asty").select2();
    $("#crgl").select2();
    $("#exgl").select2();


    $("#postexp").submit(function(e){
      e.preventDefault();
      $.ajax({
        url: $("#postexp").attr('action'),
        method: 'post',
        data: $("#postexp").serialize(),
        beforeSend:function(){
          $("#btnssubmit").text('Please wait...');
          $("#btnssubmit").attr('disabled',true);
        },
        success:function(data){
          if(data.status == 'success'){
            $("#btnssubmit").text('Save Record');
          $("#btnssubmit").attr('disabled',false);
          toastr.success(data.msg);
          window.location.href=data.uredirct;
          }else{
            toastr.error(data.msg);
             $("#btnssubmit").text('Save Record');
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
          $("#btnssubmit").text('Save Record');
          $("#btnssubmit").attr('disabled',false);
          return false;
        }
      });
    });
  });
</script>
@endsection