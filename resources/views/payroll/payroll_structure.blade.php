@extends('layout.app')
@section('title')
    Payroll Structure
@endsection
@section('pagetitle')
Payroll Structure
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

                    <div class="row">
                      <div class="form-group col-md-8 col-lg-8 col-sm-12">
                        <label class="col-sm-3 control-label">Employee Name</label>
                        <div class="col-sm-7 controls">
                          <select  required id="getpayid" class="width-70 form-control seleename" data-placeholder="Select Employee">
                            <option selected disabled>Select Employee...</option>
                            @foreach ($payrolls as $item)
                            <option value="{{$item->id}}" data-name="{{$item->employee_name}}">{{ucwords($item->employee_name)}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                    </div><hr>
                    <form class="form-horizontal" id="changepass" action="{{route('payment.structure.store')}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                            {{-- <div style="float: right;margin-right:2px; margin-bottom:5px;">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Save Record</button> 
                            </div> --}}
                          <div class="table-resposive mt-5">
                            <table style="width:100% !important" class="table table-default font-weight-bold text-gray table-striped table-bordered table-condensed table-sm">
                            
                             <thead>
                              <th class="text-uppercase">Name</th>
                              @foreach ($paddit as $item)
                                  <th class="text-uppercase">{{$item->name}}</th>
                              @endforeach
                               <th class="text-uppercase">Gross Pay</th>
                               @foreach ($pdeduc as $item)
                                 <th class="text-uppercase">{{$item->name}}</th>
                               @endforeach
                               <th class="text-uppercase">Total Deduction</th>
                               <th class="text-uppercase">Net Pay</th>
                               <th></th>
                             </thead>

                             <tbody id="appendpaystru"></tbody>

                           </table>
                         </div>
                         <div style="float: right;margin-right:2px; margin-bottom:10px;">
                          <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Save Record</button> 
                        </div><br><br>
                    </form>


                    <div style="padding:5px; margin:5px 3px; background-color:rgb(226, 226, 221);border-left:2px solid #9fa115">
                      <h6 class="text-uppercase">Payment Structure Records: {{count($pstrus)}}</h6> 
                  </div>
                  <div class="table-resposive mt-5">
                    <table class="table table-default font-weight-bold text-gray table-striped table-bordered table-condensed table-sm" id="salstrutable">
                    
                     <thead>
                       <tr>
                        <th  class="text-uppercase">Sn</th>
                      <th class="text-uppercase">Name</th>
                      <th class="text-uppercase">Basic</th>
                       <th class="text-uppercase">Gross Pay</th>
                       <th class="text-uppercase">Total Deduction</th>
                       <th class="text-uppercase">Net Pay</th>
                       <th></th>
                       </tr>
                     </thead>
                     <tbody>
                      <?php $i=0;?>
                         @foreach ($pstrus as $item)
                         <tr>  
                          <td>{{$i+1}}</td>
                          <td>{{!empty($item->payroll) ? $item->payroll->employee_name : "N/A"}}</td>
                          <td>{{number_format($item->basic,2)}}</td>
                          <td>{{number_format($item->gross_pay,2)}}</td>
                          <td>{{number_format($item->deduction,2)}}</td>
                          <td>{{number_format($item->net_pay,2)}}</td>
                          <td>
                            <a href="{{route('payment.structure.edit',['id' => $item->id])}}" class="text-danger" >Edit</a>
                          </td>
                        </tr>
                        <?php $i++;?>
                         @endforeach
                      </tbody>
                   </table>
                  </div>
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
    $(".seleename").select2();
    $("#salstrutable").dataTable();
    let useridarray = [];
    $("#getpayid").change(function(){
      let prval = $("#getpayid").val();
      let prname = $("#getpayid option:selected").attr('data-name');
     
      const isInArray = useridarray.includes(prval);
      //console.log(isInArray);
      if(isInArray == false){
        useridarray.push(prval);
       let exp = new Date().getTime();
       let prow = '<tr id="trow'+exp+'"><td><span id="tname'+prval+'">'+prname+'</span>\
                  <input type="hidden" name="staffid[]" id="pyid'+prval+'" value="'+prval+'">\
                  <input type="hidden" name="staff[]" id="pname'+prval+'" value="'+prname+'">\
                </td>\
                 @foreach ($paddit as $item)\
                <td>\
                  <input type="number" class="form-control" name="{{strtolower(str_replace(' ','_',$item->name))}}[]" id="{{strtolower(str_replace(' ','_',$item->name))}}'+prval+'" required onkeyup="calcul('+prval+')"  value="0">\
                </td>\
                @endforeach\
                <td>\
                <span id="grosspay'+prval+'">0</span>\
                <input type="hidden" class="form-control" name="gross_pay[]" id="gross'+prval+'" autocomplete="off"  value="0">\
                <input type="hidden" class="form-control paey'+prval+'" name="paye_percent[]" "autocomplete="off" value="0">\
                </td>\
              @foreach ($pdeduc as $item)\
              <td>\
                <input type="number" class="form-control" step="any" name="{{strtolower(str_replace(' ','_',$item->name))}}[]" id="{{strtolower(str_replace(' ','_',$item->name))}}'+prval+'" onkeyup="calcul('+prval+')"  autocomplete="off" value="0">\
              </td>\
              @endforeach\
              <td>\
                <span id="deduc'+prval+'">0</span>\
                <input type="hidden" name="deduction[]" id="deduction'+prval+'" autocomplete="off"  value="0">\
              </td>\
              <td>\
                <span id="netp'+prval+'">0</span>\
                <input type="hidden" name="netpay[]" id="netpay'+prval+'" autocomplete="off"  value="0">\
              </td>\
              <td><span class="pdeltrow'+exp+' text-danger" style="cursor:pointer;font-weight:bold" title="remove row"><i class="fa fa-trash-o"></i></span></td>\
            </tr>';
            $("#appendpaystru").append(prow);

            $(".pdeltrow"+exp).click(function(){
              $("#trow"+exp).remove();
            });
      }else{
        alert('row added already');
       return false;
      }
      
    });
  });
</script>
<script>
    function editstruct(id,sname,basic,othr,grpay,pypct,pye,odeduc,deduc,ntpay){
    $("#grdstrmodal").modal('show');
        $("#stname").val(sname); 
        $(".basi").val(basic); 
        $(".transport").val(trans); 
        $(".medi").val(medi); 
        $(".util").val(othr);
        $(".pe").val(ppct); 
        $(".ptxt").text(pns); 
        $(".pensi").val(pns);
        $(".py").val(pypct);
        $(".pytx").text(pye); 
        $(".paye").val(pye);

        $(".nhf").val(nhf);

        $(".deduc1").val(deduc);
        $(".deduc2").val(deduc);

        $(".gropay1").val(grpay);
        $(".gropay2").val(grpay);

        $(".ntpay1").val(ntpay); 
        $(".ntpay2").val(ntpay); 

        $("#struid").val(id); 
  }
</script>

<script type="text/javascript">
  function calcul(id){
   let pens_percent = "";
   let paye_percent = ""; 
   let totalgross = "";
    let totaldeduc = "";
    let totalnet = "";

  let basic = document.getElementById("basic_salary"+id).value; 
  let alwance = document.getElementById("other_allowances"+id).value; 
  

  let grosspay = document.getElementById("grosspay"+id); 
  let gross = document.getElementById("gross"+id); 

  let paye = document.getElementById("paye"+id).value; 
  let othdec = document.getElementById("other_deductions"+id).value; 

  let deduc = document.getElementById("deduc"+id); 
  let deductin = document.getElementById("deduction"+id); 

  let netp = document.getElementById("netp"+id); 
  let netpay = document.getElementById("netpay"+id); 

  
  paye_percent = parseFloat(paye)/100 * parseInt(basic);
  
  $(".paey"+id).val(paye_percent);
  // py.value = paye_percent;

  totalgross = parseInt(basic) + parseInt(alwance);
  grosspay.innerHTML = '<b>'+Number(totalgross).toLocaleString('en')+'</b>';
  gross.value = totalgross;

   totaldeduc = parseFloat(othdec) + parseFloat(paye_percent);
 deduc.innerHTML = '<b>'+Number(totaldeduc).toLocaleString('en')+'</b>';
 deductin.value = totaldeduc;

  totalnet = parseInt(totalgross) - parseInt(totaldeduc);
  netp.innerHTML = '<b>'+Number(totalnet).toLocaleString('en')+'</b>';
  netpay.value = totalnet;

 }
</script>
@endsection