@extends('layout.app')
@section('title')
    Edit repayment
@endsection
@section('pagetitle')
Edit repayment
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                           <a href="{{route('repay.index')}}" class="btn btn-danger"><span class="menu-icon">  </span>Back</a>
                        </div>
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                  @include('includes.success')
                    </div>
                    </div>
                   
                    <form action="{{route('repay.update',['id' => $repayment->id])}}" method="post" onsubmit="thisForm()">
                        @csrf
                        <input type="hidden" name="loanid" value="{{$repayment->loan_id}}">
                        <div class="form-group">
                            <label for="">Repayment Amount</label>
                            <input type="number" name="amount" id="amount" class="form-control" required value="{{$repayment->amount}}">
                            </div>
                        

                        <div class="form-group">
                            <label for="">Collection  Date</label>
                            <input type="date" name="collection_date" id="collection_date" class="form-control" required value="{{$repayment->collection_date}}">
                        </div>
                        <p class="bg-danger">Optional Field</p>
                
                        <div class="form-group">
                            <label for="">Notes</label>
                            <textarea name="notes" id="notes" cols="10" rows="3">{{$repayment->notes}}</textarea>
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
    <script type="text/javascript">
  $(document).ready(function(){
    $("#acoff").dataTable({'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
@endsection
