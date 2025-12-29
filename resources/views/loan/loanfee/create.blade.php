@extends('layout.app')
@section('title')
    Create Loan Product
@endsection
@section('pagetitle')
Create Loan Product
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('loan.fee.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('loan.fee.store')}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Loan Fees Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="name" autofocus required value="{{old('name')}}" autocomplete="off" placeholder="Enter Loan Fee Name">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">GL Account</label>
                        <div class="col-sm-7 controls">
                          <select name="glcode" required class="glsect width-70 form-control">
                            <option selected disabled>Select Account</option>
                            @foreach ($data as $item)
                            <option value="{{$item->gl_code}}" >{{ucwords($item->gl_name)." [".$item->gl_code."]"}}</option>
                            @endforeach
                        </select>
                        </div>
                   </div>

                      <hr>
                      
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Fee Calculation</label>
                        <div class="col-sm-7 controls">
                          <div class="vd_radio radio-success">
                            <input type="radio" value="percentage"  autocomplete="off" name="loan_fee_type" id="yes4">
                            <label for="yes4">I want Fee to be percentage % based</label>
                          </div>
                          <div class="vd_radio radio-success">
                            <input type="radio"  value="fixed" checked="checked" name="loan_fee_type" autocomplete="off" id="no4">
                            <label for="no4">I want Fee to be a fixed amount</label>
                          </div>

                        </div>
                      </div>
                      
                       <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
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
    $("#ro").select2();
    $("#sibo").select2();
  });
</script>
@endsection