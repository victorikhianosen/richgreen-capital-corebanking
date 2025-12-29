@extends('layout.app')
@section('title')
    Edit Investment Schedule
@endsection
@section('pagetitle')
Edit Investment Schedule
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('show.fd',['id' => $fd->id])}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                  
                  @inject('getloan', 'App\Http\Controllers\InvestmentController')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('fdschedule.update',['id' => $fd->id])}}" method="post" role="form" id="updateschedule" onsubmit="thisForm()">
                      @csrf
                      <div class="table-responsive">
                        <ol>
                            <li><b>Due Date</b> can not be less than the Investment Release Date of <b>{{$fd->release_date}}</b>
                            </li>
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
                                    <th>Rollover Amount</th>
                                    <th></th>
                                    <th>Total Interest</th>
                                    <th></th>
                                    <th>Total Due</th>
                                    <th>Description</th>
                                </tr>
                                </thead>
                            <tbody id="appendrow">
                              
                            <?php
                            $count = 0;
                            foreach ($schedules as $schedule) {
                            ?>
                            <tr id="del{{$schedule->id}}">
                              <td> <input type="checkbox" name="schid" style="cursor: pointer" onchange="chkbox('{{$count+1}}')" value="del{{$schedule->id}}" class="checkcust ch{{$count+1}}" id=""></td>
                                <td>
                                    {{$count+1}}<input type="hidden" name="scheduleid[]" class="form-control"
                                                       id="inputCollectionId" value="{{$schedule->id}}">
                                </td>
                                <td>
                                    <input type="date" name="due_date[]" class="form-control" id="due_date{{$count}}" value="{{$schedule->due_date}}">
                                </td>
                                <td>
                                    <input type="number" name="principal[]" step="any" class="form-control principal" id="principal{{$count}}" readonly value="{{round($schedule->principal,2)}}">
                                </td>
                                <td>+</td>
                                <td>
                                    <input type="number" step="any" name="interest[]" class="form-control interest" id="interest{{$count}}" onkeyup="updatesum('{{$count}}')" value="{{round($schedule->interest,2)}}">
                                </td>
                                <td>+</td>
                                <td>
                                    <input type="number" step="any" name="rollover[]" class="form-control fees" id="fees{{$count}}" onkeyup="updatesum('{{$count}}')" value="{{round($schedule->rollover,2)}}">
                                </td>
                                <td>=</td>
                                <td>
                                    <input type="number" step="any" name="total_interest[]" class="form-control penalty" id="penalty{{$count}}" readonly value="{{round($schedule->total_interest,2)}}">
                                </td>
                                <td>=</td>
                                <td>
                                    <input type="text" name="total_due[]" class="form-control" id="due{{$count}}"  readonly value="{{round(($schedule->total_due),2)}}">
                                </td>
                                <td>
                                    <input type="text" name="description[]" class="form-control" id="description{{$count}}" value="{{$schedule->description}}">
                                </td>
                            </tr>
                            <?php
                            $count++;
                            }
                            ?>
                            
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
                    <div id="shearry"></div>
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
            var rollover = document.getElementById("fees"+r).value;
            var totinterest = document.getElementById("penalty"+r).value;

            var totalintser = parseFloat(interest) + parseFloat(rollover);
            document.getElementById("due"+r).value = totalintser;
            document.getElementById("penalty"+r).value = totalintser;
            totinterest = totalintser;
            
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
            let trow = '<tr id="ttrow'+exp+'"><td></td><td><span id="removerow'+exp+'" class="btn btn-danger btn-sm" style="cursor:pointer;font-weight:bold;">&times</span></td>\
                <td><input type="date" name="due_date[]" style="width:100%" class="form-control" id="due_date{{$count}}" value=""></td>\
                <td><input type="number" name="principal[]" step="any" style="width:100%" class="form-control principal" id="principal{{$count}}" onkeyup="updatesum({{$count}})"></td>\
                <td>+</td>\
               <td><input type="number" step="any" name="interest[]" style="width:100%" class="form-control interest" id="interest{{$count}}" onkeyup="updatesum({{$count}})"></td>\
               <td>+</td>\
               <td><input type="number" step="any" name="rollover[]" style="width:100%" class="form-control fees" id="fees{{$count}}" onkeyup="updatesum({{$count}})"></td>\
               <td>=</td>\
               <td><input type="number" step="any" name="total_interest[]" style="width:100%" readonly class="form-control penalty" id="penalty{{$count}}"></td>\
               <td>=</td>\
                <td><input type="text" name="total_due[]" class="form-control" id="due{{$count}}"  readonly></td>\
                <td><input type="text" name="description[]" style="width:100%" class="form-control" id="description{{$count}}" ></td>\
               </tr>';

               $("#appendrow").prepend(trow);
               
               $("#removerow"+exp).click(function(){
                $("#ttrow"+exp).remove();
               
               });
        });

    $("#updateschedule").submit(function(e){
      e.preventDefault();
      $.ajax({
        url: $("#updateschedule").attr('action'),
        method: 'post',
        data: $("#updateschedule").serialize(),
        beforeSend:function(){
          $("#btnssubmit").text('Please wait...');
          $("#btnssubmit").attr('disabled',true);
        },
        success:function(data){
          if(data.status == 'success'){
            $("#btnssubmit").text('Update Record');
          $("#btnssubmit").attr('disabled',false);
          toastr.success(data.msg);
          window.location.href=data.redirect;
          }else{
            toastr.error(data.msg);
             $("#btnssubmit").text('Update Record');
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
          $("#btnssubmit").text('Update Record');
          $("#btnssubmit").attr('disabled',false);
          return false;
        }
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