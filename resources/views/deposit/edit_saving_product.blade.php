@extends('layout.app')
@section('title')
    Edit Savings Product
@endsection
@section('pagetitle')
Edit Savings Product
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('savings.product')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('savings.product.update',['id' => $ed->id])}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      
                      
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Name</label>
                        <div class="col-sm-7 controls">
                          <input class="form-control width-70" required="required" name="name" type="text" id="name" value="{{$ed->name}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Product Number</label>
                        <div class="col-sm-7 controls">
                          <input class="form-control width-70" required="required" name="product_number" type="text" id="product_number" value="{{$ed->product_number}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Interest Rate Per Annum(%)</label>
                        <div class="col-sm-7 controls">
                            <input class="form-control width-70" placeholder="Interest Rate" required name="interest_rate" type="text" id="interest_rate" value="{{$ed->interest_rate}}">                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Allow Savings Account to be Overdrawn</label>
                        <div class="col-sm-7 controls">
                            <select name="allow_overdraw" class="width-70 form-control" id="asov">
                                <option value="1" {{$ed->allow_overdraw == '1' ? 'selected' : ''}}>Yes</option>
                                <option value="0" {{$ed->allow_overdraw == '0' ? 'selected' : ''}}>No</option>
                            </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Interest Posting Frequency on Savings Accounts</label>
                        <div class="col-sm-7 controls">
                            <select name="interest_posting" class="width-70 form-control" id="asov">
                                <option value="30" {{$ed->interest_posting == '30' ? 'selected' : ''}}>Every 1 Month</option>
                                <option value="60" {{$ed->interest_posting == '60' ? 'selected' : ''}}>Every 2 Months</option>
                                <option value="90" {{$ed->interest_posting == '90' ? 'selected' : ''}}>Every 3 Months</option>
                                <option value="120" {{$ed->interest_posting == '120' ? 'selected' : ''}}>Every 4 Months</option>
                                <option value="180" {{$ed->interest_posting == '180' ? 'selected' : ''}}>Every 6 Months</option>
                                <option value="365" {{$ed->interest_posting == '365' ? 'selected' : ''}}>Every 12 Months</option>
                            </select>    
                          </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">When should Interest be added to Saving Account?</label>
                        <div class="col-sm-7 controls">
                            <select name="interest_adding" class="width-70 form-control" id="asov">
                                <option selected disabled>Select...</option>
                                <option value="1" {{$ed->interest_adding == '1' ? 'selected' : ''}}>1st of the month</option>
                                <option value="2" {{$ed->interest_adding == '2' ? 'selected' : ''}}>2nd of the month</option>
                                <option value="3" {{$ed->interest_adding == '3' ? 'selected' : ''}}>3rd of the month</option>
                                <option value="4" {{$ed->interest_adding == '4' ? 'selected' : ''}}>4th of the month</option>
                                <option value="5" {{$ed->interest_adding == '5' ? 'selected' : ''}}>5th of the month</option>
                                <option value="6" {{$ed->interest_adding == '6' ? 'selected' : ''}}>6th of the month</option>
                                <option value="7" {{$ed->interest_adding == '7' ? 'selected' : ''}}>7th of the month</option>
                                <option value="8" {{$ed->interest_adding == '8' ? 'selected' : ''}}>8th of the month</option>
                                <option value="9" {{$ed->interest_adding == '9' ? 'selected' : ''}}>9th of the month</option>
                                <option value="10" {{$ed->interest_adding == '10' ? 'selected' : ''}}>10th of the month</option>
                                <option value="11" {{$ed->interest_adding == '11' ? 'selected' : ''}}>11th of the month</option>
                                <option value="12" {{$ed->interest_adding == '12' ? 'selected' : ''}}>12th of the month</option>
                                <option value="13" {{$ed->interest_adding == '13' ? 'selected' : ''}}>13th of the month</option>
                                <option value="14" {{$ed->interest_adding == '14' ? 'selected' : ''}}>14th of the month</option>
                                <option value="15" {{$ed->interest_adding == '15' ? 'selected' : ''}}>15th of the month</option>
                                <option value="16" {{$ed->interest_adding == '16' ? 'selected' : ''}}>16th of the month</option>
                                <option value="17" {{$ed->interest_adding == '17' ? 'selected' : ''}}>17th of the month</option>
                                <option value="18" {{$ed->interest_adding == '18' ? 'selected' : ''}}>18th of the month</option>
                                <option value="19" {{$ed->interest_adding == '19' ? 'selected' : ''}}>19th of the month</option>
                                <option value="20" {{$ed->interest_adding == '20' ? 'selected' : ''}}>20th of the month</option>
                                <option value="21" {{$ed->interest_adding == '21' ? 'selected' : ''}}>21th of the month</option>
                                <option value="22" {{$ed->interest_adding == '22' ? 'selected' : ''}}>22th of the month</option>
                                <option value="23" {{$ed->interest_adding == '23' ? 'selected' : ''}}>23th of the month</option>
                                <option value="24" {{$ed->interest_adding == '24' ? 'selected' : ''}}>24th of the month</option>
                                <option value="25" {{$ed->interest_adding == '25' ? 'selected' : ''}}>25th of the month</option>
                                <option value="26" {{$ed->interest_adding == '26' ? 'selected' : ''}}>26th of the month</option>
                                <option value="27" {{$ed->interest_adding == '27' ? 'selected' : ''}}>27th of the month</option>
                                <option value="28" {{$ed->interest_adding == '28' ? 'selected' : ''}}>28th of the month</option>
                                <option value="29" {{$ed->interest_adding == '29' ? 'selected' : ''}}>29th of the month</option>
                                <option value="30" {{$ed->interest_adding == '30' ? 'selected' : ''}}>30th of the month</option>
                                <option value="31" {{$ed->interest_adding == '31' ? 'selected' : ''}}>31st of the month</option>
                                <option value="0" {{$ed->interest_adding == '0' ? 'selected' : ''}}>End of the month</option>
                            </select>    
                          </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Minimum Balance</label>
                        <div class="col-sm-7 controls">
                            <input class="form-control width-70" placeholder="Minimum Balance" required name="minimum_balance" type="number"  id="interest_rate" value="{{$ed->minimum_balance}}">                        </div>
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