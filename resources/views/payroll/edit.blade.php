@extends('layout.app')
@section('title')
    Edit Payroll
@endsection
@section('pagetitle')
Edit Payroll
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                      <a href="{{route('payroll.index')}}" class="btn btn-danger btn-sm"> < Back To Payroll List</a>
                   </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal" id="changepass" action="{{route('payroll.update',['id' => $ed->id])}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Employee Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="employee_name" required placeholder="Enter Employee Name" value="{{$ed->employee_name}}">
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Employee Email</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="email" name="employee_email" required placeholder="Enter Employee Email" value="{{$ed->email}}">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-3 control-label">Employee Designation</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70 form-control" type="text" name="designation" required placeholder="Enter Employee Designation" value="{{$ed->designation}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Payment Method</label>
                        <div class="col-sm-7 controls">
                          <select name="payment_method" required id="selepay" class="width-70 form-control" data-placeholder="Select Bank">
                            <option selected disabled>Select...</option>
                            <option value="cheque" {{$ed->payment_method == 'cheque' ? 'selected' : ''}}>Cheque</option>
                            <option value="transfer" {{$ed->payment_method == 'transfer' ? 'selected' : ''}}>Transfer</option>
                            <option value="cash" {{$ed->payment_method == 'cash' ? 'selected' : ''}}>Cash</option>
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Bank Name</label>
                        <div class="col-sm-7 controls">
                          <select name="bank_name" required id="selebank" class="width-70 form-control" data-placeholder="Select Bank">
                            @foreach ($banks as $bank)
                                 <option value="{{$bank->bank_name}}" {{$bank->bank_name == $ed->bank_name ? "selected" : ""}}>{{ucwords($bank->bank_name)}}</option>
                            @endforeach
                           
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Bank Account Number</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70 form-control" type="number" name="account_number" placeholder="Enter Bank Account Number" required value="{{$ed->account_number}}">
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
    $("#selebank").select2();
    $("#selepay").select2();
  });
</script>
@endsection