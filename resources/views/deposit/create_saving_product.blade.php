@extends('layout.app')
@section('title')
    Create Savings Product
@endsection
@section('pagetitle')
Create Savings Product
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
                    <form class="form-horizontal"  action="{{route('savings.product.store')}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      
                      
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Name</label>
                        <div class="col-sm-7 controls">
                          <input class="form-control width-70" required="required" name="name" type="text" id="name" value="{{old('name')}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Product Number</label>
                        <div class="col-sm-7 controls">
                          <input class="form-control width-70" required="required" name="product_number" type="text" id="product_number" value="{{old('product_number')}}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Interest Rate Per Annum(%)</label>
                        <div class="col-sm-7 controls">
                            <input class="form-control width-70" placeholder="Interest Rate" required name="interest_rate" type="text" id="interest_rate" value="{{old('interest_rate')}}">                       
                           </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Allow Savings Account to be Overdrawn</label>
                        <div class="col-sm-7 controls">
                            <select name="allow_overdraw" required class="width-70 form-control" id="asov">
                                <option value="1">Yes</option>
                                <option value="0" selected>No</option>
                            </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Interest Posting Frequency on Savings Accounts</label>
                        <div class="col-sm-7 controls">
                            <select name="interest_posting" required class="width-70 form-control" id="asov">
                                <<option value="30">Every 1 Month</option>
                                <option value="60">Every 2 Months</option>
                                <option value="90">Every 3 Months</option>
                                <option value="120">Every 4 Months</option>
                                <option value="180">Every 6 Months</option>
                                <option value="265">Every 12 Months</option>
                            </select>    
                          </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label">When should Interest be added to Saving Account?</label>
                        <div class="col-sm-7 controls">
                            <select name="interest_adding" required class="width-70 form-control" id="asov">
                                <option selected disabled>Select...</option>
                                <option value="1">1st of the month</option>
                                <option value="2">2nd of the month</option>
                                <option value="3">3rd of the month</option>
                                <option value="4">4th of the month</option>
                                <option value="5">5th of the month</option>
                                <option value="6">6th of the month</option>
                                <option value="7">7th of the month</option>
                                <option value="8">8th of the month</option>
                                <option value="9">9th of the month</option>
                                <option value="10">10th of the month</option>
                                <option value="11">11th of the month</option>
                                <option value="12">12th of the month</option>
                                <option value="13">13th of the month</option>
                                <option value="14">14th of the month</option>
                                <option value="15">15th of the month</option>
                                <option value="16">16th of the month</option>
                                <option value="17">17th of the month</option>
                                <option value="18">18th of the month</option>
                                <option value="19">19th of the month</option>
                                <option value="20">20th of the month</option>
                                <option value="21">21th of the month</option>
                                <option value="22">22th of the month</option>
                                <option value="23">23th of the month</option>
                                <option value="24">24th of the month</option>
                                <option value="25">25th of the month</option>
                                <option value="26">26th of the month</option>
                                <option value="27">27th of the month</option>
                                <option value="28">28th of the month</option>
                                <option value="29">29th of the month</option>
                                <option value="30">30th of the month</option>
                                <option value="31">31st of the month</option>
                                <option value="0">End of the month</option>
                            </select>    
                          </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Minimum Balance</label>
                        <div class="col-sm-7 controls">
                            <input class="form-control width-70" placeholder="Minimum Balance" required name="minimum_balance" type="number"  id="interest_rate" value="{{old('minimum_balance')?? '0'}}">                        
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