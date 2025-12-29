@extends('layout.app')
@section('title')
    Edit Schedule
@endsection
@section('pagetitle')
Edit Schedule
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('loan.show',['id' => $loan->id])}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                  
                  @inject('getloan', 'App\Http\Controllers\LoanController')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('schedule.update',['id' => $loan->id])}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      <div class="table-responsive">
                        <ol>
                            <li><b>Due Date</b> can not be less than the Loan Release Date of <b>{{$loan->release_date}}</b>
                            </li>
                            <li>Description is optional</li>
                            <li>If you have set automated loan penalties and you see <b>System Generated Penalty</b> rows
                                below, you can delete them by visiting <b>Edit Loan</b>, checking <b>Overriding System
                                    Generated Penalties</b> field and putting 0 in <b>Manual Penalty Amount</b></li>
                        </ol>
                            <div style="float:right;margin-bottom: 7px">
                          <button type="button" class="btn btn-danger btn-sm" id="deleterow" title="add delete rows"><i class="fa fa-times"></i> Delete Row(s)</button>
                          <button type="button" class="btn btn-primary btn-sm" id="addrow" title="add new row"><i class="fa fa-plus"></i> Add Row(s)</button>
                      </div>
                        <table class="table table-bordered table-condensed  table-hover">
                            <thead>
                            <tr class="bg-gray">
                                <th><input type="checkbox" name="" id="checkall" style="cursor: pointer"></th>
                                <th>#</th>
                                <th>Due Date</th>
                                <th>Principal Amount</th>
                                <th></th>
                                <th>Interest Amount</th>
                                <th></th>
                                <th>Fee Amount</th>
                                <th></th>
                                <th>Penalty Amount</th>
                                <th></th>
                                <th>Due Amount</th>
                                <th>Principal Balance</th>
                                <th>Description</th>
                            </tr>
                            </thead>
                            <tbody id="appendrow">
                            <?php
                            $count = 0;
                            $principal_balance = \App\Models\LoanSchedule::where('loan_id',
                                    $loan->id)->sum('principal');
            
                            foreach ($schedules as $schedule) {
                            $principal_balance = $principal_balance - $schedule->principal;
                            ?>
                            <tr id="del{{$schedule->id}}">
                              <td> <input type="checkbox" name="schid" style="cursor: pointer" onchange="chkbox('{{$count+1}}')" value="del{{$schedule->id}}" class="checkcust ch{{$count+1}}" id=""></td>
                                <td>
                                    {{$count+1}}<input type="hidden" name="scheduleid[]" class="form-control"
                                                       id="inputCollectionId" value="{{$schedule->id}}">
                                </td>
                                <td>
                                    <input type="date" name="due_date[]" {{$schedule->closed == '1' ? 'readonly' : ''}} class="form-control" id="due_date{{$count}}" value="{{$schedule->due_date}}">
                                </td>
                                <td>
                                    <input type="number" name="principal[]" step="any" {{$schedule->closed == '1' ? 'readonly' : ''}} class="form-control principal" id="principal{{$count}}" onkeyup="updatesum('{{$count}}')" value="{{round($schedule->principal,2)}}">
                                </td>
                                <td>+</td>
                                <td>
                                    <input type="number" step="any" name="interest[]" {{$schedule->closed == '1' ? 'readonly' : ''}} class="form-control interest" id="interest{{$count}}" onkeyup="updatesum('{{$count}}')" value="{{round($schedule->interest,2)}}">
                                </td>
                                <td>+</td>
                                <td>
                                    <input type="number" step="any" name="fees[]" {{$schedule->closed == '1' ? 'readonly' : ''}} class="form-control fees" id="fees{{$count}}" onkeyup="updatesum('{{$count}}')" value="{{round($schedule->fees,2)}}">
                                </td>
                                <td>+</td>
                                <td>
                                    <input type="number" step="any" name="penalty[]" {{$schedule->closed == '1' ? 'readonly' : ''}} class="form-control penalty" id="penalty{{$count}}" onkeyup="updatesum('{{$count}}')"  value="{{round($schedule->penalty,2)}}">
                                </td>
                                <td>=</td>
                                <td>
                                    <input type="text" name="due[]" class="form-control" {{$schedule->closed == '1' ? 'readonly' : ''}} id="due{{$count}}"  readonly value="{{round(($schedule->principal+$schedule->interest+$schedule->fees+$schedule->penalty),2)}}">
                                </td>
                                <td>
                                    <input type="text" name="principal_balance[]" {{$schedule->closed == '1' ? 'readonly' : ''}} class="form-control" id="principal_balance{{$count}}"  readonly value="{{round($schedule->principal_balance,2)}}">
                                </td>
                                <td>
                                    <input type="text" name="description[]" {{$schedule->closed == '1' ? 'readonly' : ''}} class="form-control" id="description{{$count}}" value="{{$schedule->description}}">
                                </td>
                            </tr>
                            <?php
                            $count++;
                            }
                            ?>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <input type="text" class="form-control" value="Total" readonly="">
                                </td>
                                <td>
                                    <input type="text" name="principalTotal" class="form-control"
                                           id="principalTotal"
                                           value="{{round($getloan->loan_total_principal($loan->id),2)}}"
                                           readonly="">
                                </td>
                                <td>+</td>
                                <td>
                                    <input type="text" name="interestTotal" class="form-control"
                                           id="interestTotal"
                                           value="{{round($getloan->loan_total_interest($loan->id),2)}}"
                                           readonly="">
                                </td>
                                <td>+</td>
                                <td>
                                    <input type="text" name="feesTotal" class="form-control"
                                           id="feesTotal"
                                           value="{{round($getloan->loan_total_fees($loan->id),2)}}"
                                           readonly="">
                                </td>
                                <td>+</td>
                                <td>
                                    <input type="text" name="penaltyTotal" class="form-control"
                                           id="penaltyTotal"
                                           value="{{round($getloan->loan_total_penalty($loan->id),2)}}"
                                           readonly="">
                                </td>
                                <td>=</td>
                                <td>
                                    <input type="text" name="inputTotalDueAmountTotal" class="form-control"
                                           id="inputTotalDueAmountTotal"
                                           value="{{round($getloan->loan_total_due_amount($loan->id),2)}}"
                                           readonly="">
                                </td>
                                <td></td>
                                <td></td>
                                
                            </tr>
                            </tbody>
                        </table>
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
    function updatesum(r) {
        var principalTotal = 0;
        var interestTotal = 0;
        var feesTotal = 0;
        var penaltyTotal = 0;
        var inputTotalDueAmountTotal = 0;
       
        var principal = document.getElementById("principal"+r).value;
            var interest = document.getElementById("interest"+r).value;
            var fees = document.getElementById("fees"+r).value;
            var penalty = document.getElementById("penalty"+r).value;

            let getallprinpal = document.querySelectorAll(".principal");
            let getallfees = document.querySelectorAll(".fees");
            let getpenty = document.querySelectorAll(".penalty");
            let getallinterest = document.querySelectorAll(".interest");

            if (principal == "")
                principal = 0;
            if (interest == "")
                interest = 0;
            if (fees == "")
                fees = 0;
            if (penalty == "")
                penalty = 0;

            var totaldue = parseFloat(principal) + parseFloat(interest) + parseFloat(fees) + parseFloat(penalty);
            document.getElementById("due"+r).value = Math.floor(totaldue * 100) / 100;

              for ( var i = 0; i<getallprinpal.length; i++){
                 var prele = getallprinpal[i];
                 principalTotal += parseInt(prele.value);
                }

                for ( var i = 0; i<getallfees.length; i++){
                 var fsele = getallfees[i];
                 feesTotal += parseInt(fsele.value);
                }

                for ( var i = 0; i<getpenty.length; i++){
                 var ptyele = getpenty[i];
                 penaltyTotal += parseInt(ptyele.value);
                }

                for ( var i = 0; i<getallinterest.length; i++){
                 var inele = getallinterest[i];
                 interestTotal += parseInt(inele.value);
                }
           
            
 
             inputTotalDueAmountTotal = parseFloat(principalTotal) + parseFloat(feesTotal) + parseFloat(penaltyTotal) + parseFloat(interestTotal);


        document.getElementById("principalTotal").value = principalTotal;
        document.getElementById("interestTotal").value = interestTotal;
        document.getElementById("feesTotal").value = feesTotal;
        document.getElementById("penaltyTotal").value = penaltyTotal;
        document.getElementById("inputTotalDueAmountTotal").value = inputTotalDueAmountTotal;

        var total_principal_amount = 0;
        var pending_balance = 0;
        var principalTotal = document.getElementById("principalTotal").value;
        
        var principal = document.getElementById("principal"+r).value;
            total_principal_amount = (parseFloat(total_principal_amount) + parseFloat(principal));
            pending_balance = parseFloat(principalTotal) - parseFloat(total_principal_amount);
            document.getElementById("principal_balance"+r).value = Math.ceil(pending_balance * 100) / 100;

    }
</script>
<script>
 $delchekboxarry = [];
 
    $(document).ready(function(){
        var principalTotal = 0;
        var interestTotal = 0;
        var feesTotal = 0;
        var penaltyTotal = 0;
        var inputTotalDueAmountTotal = 0;
        
        $("#addrow").click(function(){
            let exp = new Date().getTime();
            let trow = '<tr id="ttrow'+exp+'"></td><td><td><span id="removerow'+exp+'" class="btn btn-danger btn-sm" style="cursor:pointer;font-weight:bold;">&times</span></td>\
                <td><input type="date" name="due_date[]" style="width:100%" class="form-control" id="due_date{{$count}}" value=""></td>\
                <td><input type="number" name="principal[]" step="any" style="width:100%" class="form-control principal" id="principal{{$count}}" onkeyup="updatesum({{$count}})"></td>\
                <td>+</td>\
               <td><input type="number" name="interest[]" step="any" style="width:100%" class="form-control interest" id="interest{{$count}}" onkeyup="updatesum({{$count}})"></td>\
               <td>+</td>\
               <td><input type="number" name="fees[]" step="any" style="width:100%" class="form-control fees" id="fees{{$count}}" onkeyup="updatesum({{$count}})"></td>\
               <td>+</td>\
               <td><input type="number" name="penalty[]" step="any" style="width:100%" class="form-control penalty" id="penalty{{$count}}" onkeyup="updatesum({{$count}})"></td>\
               <td>=</td>\
                <td><input type="text" name="due[]" class="form-control" id="due{{$count}}"  readonly></td>\
                <td><input type="text" name="principal_balance[]" style="width:100%" class="form-control" id="principal_balance{{$count}}"  readonly></td>\
                <td><input type="text" name="description[]" style="width:100%" class="form-control" id="description{{$count}}" ></td>\
               </tr>';

               $("#appendrow").prepend(trow);
               
               $("#removerow"+exp).click(function(){
                $("#ttrow"+exp).remove();
                let getallprinpal = document.querySelectorAll(".principal");
            let getallfees = document.querySelectorAll(".fees");
            let getpenty = document.querySelectorAll(".penalty");
            let getallinterest = document.querySelectorAll(".interest");

                for ( var i = 0; i<getallprinpal.length; i++){
                 var prele = getallprinpal[i];
                 principalTotal += parseInt(prele.value);
                }

                for ( var i = 0; i<getallfees.length; i++){
                 var fsele = getallfees[i];
                 feesTotal += parseInt(fsele.value);
                }

                for ( var i = 0; i<getpenty.length; i++){
                 var ptyele = getpenty[i];
                 penaltyTotal += parseInt(ptyele.value);
                }

                for ( var i = 0; i<getallinterest.length; i++){
                 var inele = getallinterest[i];
                 interestTotal += parseInt(inele.value);
                }

                document.getElementById("principalTotal").value = principalTotal;
                document.getElementById("interestTotal").value = interestTotal;
                document.getElementById("feesTotal").value = feesTotal;
                document.getElementById("penaltyTotal").value = penaltyTotal;
                document.getElementById("inputTotalDueAmountTotal").value = inputTotalDueAmountTotal;
               });
        });
        

    $("#checkall").click(function(){

      if($(this).is(":checked")){
        $(".checkcust").prop('checked',true);  
       
          $.each($("input[name='schid']:checked"), function(){
            if(!$delchekboxarry.includes($(this).val())){
                  $delchekboxarry.push($(this).val())
                }
              });
          
        
        console.log($delchekboxarry);
      }else{
        $(".checkcust").prop('checked',false);
        $.each($("input[name='schid']:not(:checked)"), function(){
                  $delchekboxarry.pop($(this).val())
              });
              console.log($delchekboxarry);
      }
    });

    $("#deleterow").click(function(){
        for(i=0; i<$delchekboxarry.length; i++){
          $("#"+$delchekboxarry[i]).remove();
        }
    }); 
       
    });
</script>
<script>
  function chkbox(id){
    if ($(".ch"+id).is(':checked')) {
            $delchekboxarry.push($(".ch"+id).val());
          }else{
            $delchekboxarry.pop($(".ch"+id).val());
            //listvalues.filter(item => item != this.value)
          }
          console.log($delchekboxarry);
  }
</script>
@endsection