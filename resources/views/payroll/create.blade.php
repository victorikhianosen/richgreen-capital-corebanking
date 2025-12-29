@extends('layout.app')
@section('title')
    Create Payroll
@endsection
@section('pagetitle')
Create Payroll
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
                    <form class="form-horizontal" id="changepass" action="{{route('payroll.store')}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Employee Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="employee_name" autofocus required placeholder="Enter Employee Name" value="{{old('employee_name')}}">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-3 control-label">Employee Email</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="email" name="employee_email" required placeholder="Enter Employee Email" value="{{old('employee_email')}}">
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Employee Designation</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70 form-control" type="text" name="designation" required placeholder="Enter Employee Designation" value="{{old('designation')}}">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-3 control-label">Payment Method</label>
                        <div class="col-sm-7 controls">
                          <select name="payment_method" required id="selepay" class="width-70 form-control" data-placeholder="Select Bank">
                            <option selected disabled>Select...</option>
                            <option value="cheque">Cheque</option>
                            <option value="transfer">Transfer</option>
                            <option value="cash">Cash</option>
                          </select>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-3 control-label">Bank Name</label>
                        <div class="col-sm-7 controls">
                          <select name="bank_name" required id="selebank" class="width-70 form-control" data-placeholder="Select Bank">
                            @foreach ($banks as $bank)
                                 <option value="{{$bank->bank_name}}">{{ucwords($bank->bank_name)}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Bank Account Number</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70 form-control" type="number" name="account_number" placeholder="Enter Bank Account Number" required value="{{old('account_number')}}">
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
    $("#selebank").select2();
  });
</script>
@endsection