@extends('layout.app')
@section('title')
Payment Structure
@endsection
@section('pagetitle')
Edit Payment Structure
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                      <a href="{{route('payment.structure')}}" class="btn btn-danger btn-sm"> < Back To Payroll List</a>
                   </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal" id="changepass" action="{{route('payment.structure.update',['id' => $ed->id])}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Employee Name</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" readonly name="employee_name" autofocus required placeholder="Enter Employee Name" value="{{!empty($ed->payroll) ? $ed->payroll->employee_name : ""}}">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-3 control-label">Basic Salary</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="number" name="basic_salary" id="basic" onkeyup="calculup()" required value="{{$ed->basic}}">
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Other Allowances</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70 form-control" type="number" id="otherallw" name="other_allowances" onkeyup="calculup()" required  value="{{$ed->other_allowance}}">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-sm-3 control-label">Gross Pay</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70 form-control gropay1" type="text" readonly  value="0">
                          <input class="gropay2" type="hidden" name="gross_pay" value="{{$ed->gross_pay}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Paye(%)</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70 form-control" type="number" step="any" name="paye" id="pe"  onkeyup="calculup()"  value="{{$ed->paye_percent}}">
                          % of basic =&nbsp;<span id="ptxt" class="ptxt font-weight-bold">{{number_format($ed->paye)}}</span>
                          <input type="hidden" class="form-control paey" name="paye_percent" value="0">
                        </div> 
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Other Deductions</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70 form-control" type="number" id="otherd" name="other_deductions"  onkeyup="calculup()"  value="{{$ed->other_deduction}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Total Deductions</label>
                        <div class="col-sm-7 controls">
                            <input type="text" readonly id="deduc1" class="width-70 form-control deduc1" value="0">
                          <input class="deduc2" type="hidden" id="deduc2" name="deduction" value="{{$ed->deduction}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Net Pay</label>
                        <div class="col-sm-7 controls">
                            <input type="text" readonly id="ntpay1" class="width-70 form-control ntpay1" value="0">
                          <input class="ntpay2" type="hidden" id="ntpay2" name="netpay" value="{{$ed->net_pay}}">
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
<<script type="text/javascript">
    function calculup(){
      let basi = $("#basic").val(); 
      let otherallowance = $("#otherallw").val(); 
      let paye = $("#pe").val(); 
      let otherdeduction = $("#otherd").val(); 
      
      let pae_percent = (parseFloat(paye)/100) * parseInt(basi);
      let pey = $(".paey").val(pae_percent); 
      let pentxt = $("#ptxt").text(Number(pae_percent).toLocaleString('en')); 
  
      let totgross = parseInt(basi) + parseInt(otherallowance);
      $(".gropay1").val(Number(totgross).toLocaleString('en')); 
      $(".gropay2").val(totgross); 
  
      let totdeduc = parseInt(pae_percent) + parseInt(otherdeduction);
      $("#deduc1").val(Number(totdeduc).toLocaleString('en'));
      $("#deduc2").val(totdeduc);
  
       let totnetp = parseInt(totgross) - parseInt(totdeduc);
      $("#ntpay1").val(Number(totnetp).toLocaleString('en')); 
      $("#ntpay2").val(totnetp); 
      
      
    }
  </script>
@endsection