@extends('layout.app')
@section('title')
    Edit Savings Fees
@endsection
@section('pagetitle')
Edit Savings Fees
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('savings.fee')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('savings.fee.update',['id' => $ed->id])}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      
                      
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Name</label>
                        <div class="col-sm-7 controls">
                          <input class="form-control width-70" required="required" name="name" type="text" id="name" value="{{$ed->name}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Amount</label>
                        <div class="col-sm-7 controls">
                          <input class="form-control width-70" required="required" name="amount"  type="number" id="product_number" value="{{$ed->amount}}">
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Fee Posting Frequency on Savings Accounts</label>
                        <div class="col-sm-7 controls">
                            <select name="fees_posting" class="width-70 form-control" id="asov">
                                <option value="1" {{$ed->fees_posting == '1' ? 'selected' : ''}}>Every 1 Month</option>
                                <option value="2" {{$ed->fees_posting == '2' ? 'selected' : ''}}>Every 2 Months</option>
                                <option value="3" {{$ed->fees_posting == '3' ? 'selected' : ''}}>Every 3 Months</option>
                                <option value="4" {{$ed->fees_posting == '4' ? 'selected' : ''}}>Every 3 Months</option>
                                <option value="5" {{$ed->fees_posting == '5' ? 'selected' : ''}}>Every 4 Months</option>
                                <option value="6" {{$ed->fees_posting == '6' ? 'selected' : ''}}>Every 6 Months</option>
                                <option value="7" {{$ed->fees_posting == '7' ? 'selected' : ''}}>Every 12 Months</option>
                                <option value="8" {{$ed->fees_posting == '8' ? 'selected' : ''}}>One-time for new savings accounts only on account opening date</option>
                            </select>    
                          </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">When should Fee be added to Saving Account?</label>
                        <div class="col-sm-7 controls">
                            <select name="fees_adding" class="width-70 form-control" id="asov">
                                <option selected disabled>Select...</option>
                                <option value="1" {{$ed->fees_adding == '1' ? 'selected' : ''}}>1st of the month</option>
                                <option value="2" {{$ed->fees_adding == '2' ? 'selected' : ''}}>2nd of the month</option>
                                <option value="3" {{$ed->fees_adding == '3' ? 'selected' : ''}}>3rd of the month</option>
                                <option value="4" {{$ed->fees_adding == '4' ? 'selected' : ''}}>4th of the month</option>
                                <option value="5" {{$ed->fees_adding == '5' ? 'selected' : ''}}>5th of the month</option>
                                <option value="6" {{$ed->fees_adding == '6' ? 'selected' : ''}}>6th of the month</option>
                                <option value="7" {{$ed->fees_adding == '7' ? 'selected' : ''}}>7th of the month</option>
                                <option value="8" {{$ed->fees_adding == '8' ? 'selected' : ''}}>8th of the month</option>
                                <option value="9" {{$ed->fees_adding == '9' ? 'selected' : ''}}>9th of the month</option>
                                <option value="10" {{$ed->fees_adding == '10' ? 'selected' : ''}}>10th of the month</option>
                                <option value="11" {{$ed->fees_adding == '11' ? 'selected' : ''}}>11th of the month</option>
                                <option value="12" {{$ed->fees_adding == '12' ? 'selected' : ''}}>12th of the month</option>
                                <option value="13" {{$ed->fees_adding == '13' ? 'selected' : ''}}>13th of the month</option>
                                <option value="14" {{$ed->fees_adding == '14' ? 'selected' : ''}}>14th of the month</option>
                                <option value="15" {{$ed->fees_adding == '15' ? 'selected' : ''}}>15th of the month</option>
                                <option value="16" {{$ed->fees_adding == '16' ? 'selected' : ''}}>16th of the month</option>
                                <option value="17" {{$ed->fees_adding == '17' ? 'selected' : ''}}>17th of the month</option>
                                <option value="18" {{$ed->fees_adding == '18' ? 'selected' : ''}}>18th of the month</option>
                                <option value="19" {{$ed->fees_adding == '19' ? 'selected' : ''}}>19th of the month</option>
                                <option value="20" {{$ed->fees_adding == '20' ? 'selected' : ''}}>20th of the month</option>
                                <option value="21" {{$ed->fees_adding == '21' ? 'selected' : ''}}>21th of the month</option>
                                <option value="22" {{$ed->fees_adding == '22' ? 'selected' : ''}}>22th of the month</option>
                                <option value="23" {{$ed->fees_adding == '23' ? 'selected' : ''}}>23th of the month</option>
                                <option value="24" {{$ed->fees_adding == '24' ? 'selected' : ''}}>24th of the month</option>
                                <option value="25" {{$ed->fees_adding == '25' ? 'selected' : ''}}>25th of the month</option>
                                <option value="26" {{$ed->fees_adding == '26' ? 'selected' : ''}}>26th of the month</option>
                                <option value="27" {{$ed->fees_adding == '27' ? 'selected' : ''}}>27th of the month</option>
                                <option value="28" {{$ed->fees_adding == '28' ? 'selected' : ''}}>28th of the month</option>
                                <option value="29" {{$ed->fees_adding == '29' ? 'selected' : ''}}>29th of the month</option>
                                <option value="30" {{$ed->fees_adding == '30' ? 'selected' : ''}}>30th of the month</option>
                                <option value="31" {{$ed->fees_adding == '31' ? 'selected' : ''}}>31st of the month</option>
                                <option value="0" {{$ed->fees_adding == '0' ? 'selected' : ''}}>End of the month</option>
                            </select>    
                          </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="col-sm-3 control-label">The Fee will apply to the following Savings Products</label>
                        <div class="col-sm-7 controls">
                            @foreach ($savingsprods as $item)
                                <label> 
                                    <input class="inputDisbursedById{{$item->id}}" type="checkbox" name="savings_products[]"
                                    @foreach ((array)$ed->savings_products as $value)
                                     @if($value == $item->id)
                                      checked
                                     @endif
                                    @endforeach
                                     value="{{$item->id}}">
                                    {{ucwords($item->name)}}
                                </label>
                               <br> 
                            @endforeach
                           
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
    $("#acuser").change(function(){
      let userid = $("#acuser").val();
      $.ajax({
        url:"{{route('getuserdetails')}}",
        method:"get",
        data:{'uid':userid},
        beforeSend:function(){
          $("#sttext").show();
        },
        success:function(data){
          $("#sttext").hide();
          $("#fname").val(data.name);
          $("#em").val(data.email);
          $("#gdn").val(data.gender);
          $("#phn").val(data.phone);
          $("#adr").val(data.addr);
        },
        error:function(xhr,status,errorThrown){
          alert('An Error Occured... '+errorThrown);
          $("#sttext").hide();
        }
      })
    });
  });
</script>
@endsection