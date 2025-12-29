@extends('layout.app')
@section('title')
    Edit Customer
@endsection
@section('pagetitle')
Edit Customer Details
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('customer.view',['id' => $ced->id])}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                      @include('includes.errors')
                      @include('includes.success')
                    </div>
                    </div>
                    <div style="text-align: end;margin:20px 10px;width:100%">
                      <form action="{{route('customer.resetpasswpin')}}" method="post">
                        @csrf
                        <input type="hidden" name="userid" value="{{$ced->id}}">
                        <div class="d-flex">
                          <input type="submit" name="type" class="btn vd_btn vd_bg-yellow" onclick="this.setAtrribute='disabled'" value="Send Pin Reset">
                        <input type="submit" name="type" class="btn btn-danger" onclick="this.setAtrribute='disabled'"  value="Send Password Reset">
                        </div>
                      </form>
                     
                    </div> 
                    <form class="form-horizontal"  action="{{route('customer.update',['id' => $ced->id])}}" method="post" enctype="multipart/form-data" role="form" onsubmit="thisForm()">
                      @csrf
                      <div class="container">
                        <div class="row">
                          <div class="form-group col-sm-4 controls">
                            <label>Title</label>
                            <div>
                                <select name="title"  class="width-90 form-control" id="til" autocomplete="off">
                                    <option selected disabled>Select...</option>
                                    <option value="mr" {{$ced->title == "mr" ? "selected" : ""}}>Mr</option>
                                    <option value="mrs" {{$ced->title == "mrs" ? "selected" : ""}}>Mrs</option>
                                    <option value="miss" {{$ced->title == "miss" ? "selected" : ""}}>Miss</option>
                                </select>
                            </div>
                          </div>
                            <div class="form-group col-sm-4 controls">
                                <label>Last Name</label>
                                <div>
                                    <input class="width-90" type="text" name="last_name" autofocus id="flnm" required value="{{$ced->last_name}}" autocomplete="off" placeholder="Enter Last Name">
                                </div>
                              </div>
                            <div class="form-group col-sm-4 controls">
                                <label>First Name</label>
                                <div>
                                    <input class="width-90" type="text" name="first_name" autofocus id="flnm" required value="{{$ced->first_name}}" autocomplete="off" placeholder="Enter First Name">
                                </div>
                              </div>
                              
                              </div>
                              
                              
                              <div class="row">
                          <div class="form-group col-sm-4 controls">
                            <label>Username</label>
                            <div>
                                <input class="width-90" type="text" name="username"  id="urnm" value="{{$ced->username}}" autocomplete="off" placeholder="Enter Username">
                            </div>
                          </div>
                          <div class="form-group col-sm-4 controls">
                            <label>Enable SMS Alert</label>
                            <div>
                                <select name="enable_sms_alert"  class="width-90 form-control" id="til" autocomplete="off">
                                    <option selected disabled>Select...</option>
                                    <option value="1" {{$ced->enable_sms_alert == "1" ? "selected" : ""}}>Yes</option>
                                    <option value="0" {{$ced->enable_sms_alert == "0" ? "selected" : ""}}>No</option>
                                </select>
                            </div>
                          </div>
                          <div class="form-group col-sm-4 controls">
                            <label>Enable Email Alert</label>
                            <div>
                              <select name="enable_email_alert"  class="width-90 form-control" id="til" autocomplete="off">
                                <option selected disabled>Select...</option>
                                <option value="1" {{$ced->enable_email_alert == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$ced->enable_email_alert == "0" ? "selected" : ""}}>No</option>
                            </select>
                            </div>
                          </div>
                    </div>
                    
                    <div class="row">
                              <div class="form-group col-sm-4 controls">
                                <label>Email</label>
                                <div>
                                    <input class="width-90" type="email" name="email" id="email" value="{{$ced->email}}" autocomplete="off" placeholder="Enter Email" >
                                </div>
                              </div>
                              <div class="form-group col-sm-4 controls">
                                <label>Marital Status</label>
                                <div>
                                    <select name="marital_status"  class="width-90 form-control" id="til" autocomplete="off">
                                        <option selected disabled>Select...</option>
                                        <option value="single" {{$ced->marital_status == "single" ? "selected" : ""}}>Single</option>
                                        <option value="married" {{$ced->marital_status == "married" ? "selected" : ""}}>Married</option>
                                        <option value="other" {{$ced->marital_status == "other" ? "selected" : ""}}>Other</option>
                                    </select>
                                </div>
                              </div>
                              <div class="form-group col-sm-4 controls">
                                <label>Phone Number</label>
                                <div>
                                    <input class="width-90" type="tel" name="phone" id="phn" value="{{$ced->phone}}" autocomplete="off" placeholder="Enter phone Number" >
                                </div>
                              </div>
                              </div>
                              
                              <div class="row">
                              <div class="form-group col-sm-4 controls">
                                <label>DOB</label>
                                <div>
                                    <input class="width-90" type="date" name="dob" id="dob" value="{{$ced->dob}}" autocomplete="off" placeholder="Enter DOB">
                                </div>
                              </div>
                              <div class="form-group col-sm-4 controls">
                                <label>Gender</label>
                                <div>
                                    <select name="gender"  class="width-90 form-control" required id="gend" autocomplete="off">
                                        <option selected disabled>Select...</option>
                                        <option value="male" {{$ced->gender == "male" ? "selected" : ""}}>Male</option>
                                        <option value="female" {{$ced->gender == "female" ? "selected" : ""}}>Female</option>
                                    </select>
                                </div>
                              </div>
                              <div class="form-group col-sm-4 controls">
                                <label>Account Section<sup class="text-danger" style="font-size:14px;font-weight: bold">*</sup></label>
                                <div>
                                    <select name="account_section" class="width-90 form-control" required id="gend" autocomplete="off">
                                        <option selected disabled>Select...</option>
                                        <option value="asset matrix" {{$ced->section == "asset matrix" ? "selected" : ""}}>Asset Matrix</option>
                                        <option value="rich green masters" {{$ced->section == "rich green masters" ? "selected" : ""}}>Rich Green Masters</option>
                                        <option value="rich green capital" {{$ced->section == "rich green capital" ? "selected" : ""}}>Rich Green Capital</option>
                                    </select>
                                </div>
                              </div>
                              
                          </div>
    
                          <div class="row">
                              <div class="form-group col-sm-4 controls">
                                <label>Residential Address</label>
                                <div>
                                    <input class="width-90" type="text" name="address" id="addr" value="{{$ced->residential_address}}" autocomplete="off" placeholder="Enter Address" >
                                </div>
                              </div>
                            <div class="form-group col-sm-4 controls">
                              <label>Country</label>
                              <div>
                                  <input class="width-90" type="text" name="country"  id="stte" value="{{$ced->country}}" autocomplete="off" placeholder="Enter Country">
                              </div>
                            </div>
                              <div class="form-group col-sm-4 controls">
                                <label>State</label>
                                <div>
                                    <input class="width-90" type="text" name="state" id="stte" value="{{$ced->state}}" autocomplete="off" placeholder="Enter State">
                                </div>
                              </div>

                              <div class="form-group col-sm-4 controls">
                                <label>Local Govt Area</label>
                                <div>
                                    <input class="width-90" type="text" name="lga" id="lga" value="{{$ced->state_lga}}" autocomplete="off" placeholder="Enter Local Govt" >
                                </div>
                              </div>
                              
                              <div class="form-group col-sm-4 controls">
                                <label>Religion</label>
                                <div>
                                    <select name="religion"  class="width-90 form-control" id="gend" autocomplete="off">
                                        <option selected disabled>Select...</option>
                                        <option value="christainity" {{$ced->religion == "christainity" ? "selected" : ""}}>Christainity</option>
                                        <option value="islam" {{$ced->religion == "islam" ? "selected" : ""}}>Islam</option>
                                    </select>
                                </div>
                              </div>
                              <div class="form-group col-sm-4 controls">
                                <label>Bank Verification Number</label>
                                <div>
                                    <input class="width-90" type="number" name="bvn" id="bvn" value="{{$ced->bvn}}" autocomplete="off" placeholder="Enter BVN" >
                                    <img src="{{asset('img/loading.gif')}}" id="sttext" style="display: none" alt="loading">
                                  </div>
                                  <p id="bvnstat"></p>
                              </div>
                              
                          </div>

                          <div class="row">
                              <div class="form-group col-sm-4 controls">
                                <label>Change Phone Verification Status</label>
                                <select name="phone_status" class="width-90 form-control" id="acctstus" autocomplete="off">
                                  <option selected disabled>Select...</option>
                                  <option value="1" {{$ced->phone_verify == "1" ? "selected" : ""}}>Yes</option>
                                  <option value="0" {{$ced->phone_verify == "0" ? "selected" : ""}}>No</option>
                              </select>
                              </div>
                            <div class="form-group col-sm-4 controls">
                              <label>Change Account Status</label>
                              <select name="account_status"  class="width-90 form-control" required id="acctstus" autocomplete="off">
                                <option selected disabled>Select...</option>
                                @foreach ($statuses as $item)
                                   <option value="{{$item->id}}" {{$ced->status == $item->id ? "selected" : ""}}>{{$item->name}}</option>
                                @endforeach
                            </select>
                            </div>
                            <div class="form-group col-sm-4 controls">
                              <label>Lien Account </label>
                              <select name="lien_account"  class="width-90 form-control" required id="lien" autocomplete="off">
                                <option selected disabled>Select...</option>
                                   <option value="0" {{$ced->lien == "0" ? "selected" : ""}}>Default</option>
                                   <option value="1" {{$ced->lien == "1" ? "selected" : ""}}>PNC(Post No Credit)</option>
                                   <option value="2" {{$ced->lien == "2" ? "selected" : ""}}>PND(Post No Debit)</option>
                            </select>
                            </div>
                           
                            </div>
                            
                            <div class="row">
                                 <div class="form-group col-sm-4 controls">
                              <label>Transfer Limit </label>
                               <input name="transfer_limit"  class="width-90 form-control" value="{{$ced->transfer_limit}}">
                            </div>
                            <div class="form-group col-sm-4 controls">
                              <label>Occupation</label>
                              <div>
                                  <input class="width-90" type="text" name="occupation" id="ocp" value="{{$ced->occupation}}" autocomplete="off" placeholder="Enter Occupation" >
                              </div>
                            </div>
                            <div class="form-group col-sm-4 controls">
                              <label>Business Name</label>
                              <div>
                                  <input class="width-90" type="text" name="business_name" id="bn" value="{{$ced->business_name}}" autocomplete="off" placeholder="Enter Business Name">
                              </div>
                            </div>
                              
                          </div>

                          <div class="row">
                            <div class="form-group col-sm-3 controls">
                              <label>Working Status</label>
                              <div>
                                <select name="working_status"  class="width-90 form-control" autocomplete="off">
                                  <option selected disabled>Select...</option>
                                  <option value="teacher" {{$ced->working_status == "teacher" ? "selected" : ""}}>Teacher</option>
                                  <option value="employee" {{$ced->working_status == "employee" ? "selected" : ""}}>Employee</option>
                                  <option value="owner" {{$ced->working_status == "owner" ? "selected" : ""}}>Owner</option>
                                  <option value="student" {{$ced->working_status == "student" ? "selected" : ""}}>Student</option>
                                  <option value="overseas worker" {{$ced->working_status == "overseas worker" ? "selected" : ""}}>Overseas Worker</option>
                                  <option value="pensioner" {{$ced->working_status == "pensioner" ? "selected" : ""}}>Pensioner</option>
                              </select>
                              </div>
                            </div>
                            
                            <div class="form-group col-sm-3 controls">
                              <label>Means of Indentification</label>
                              <div>
                                  <select name="means_of_id"  class="width-90 form-control" required id="mense" autocomplete="off">
                                      <option selected disabled>Select...</option>
                                      <option value="national id" {{$ced->means_of_id == "national id" ? "selected" : ""}}>National ID</option>
                                      <option value="voters card" {{$ced->means_of_id == "voters card" ? "selected" : ""}}>Voters Card</option>
                                      <option value="international passport" {{$ced->means_of_id == "international passport" ? "selected" : ""}}>International Passport</option>
                                      <option value="drivers lincense" {{$ced->means_of_id == "drivers lincense" ? "selected" : ""}}>Drivers Lincense</option>
                                  </select>
                              </div>
                            </div>
                            
                             <div class="form-group col-sm-3 controls">
                              <label>Domicilary Account Type</label>
                              <div>
                                  <select name="domicilary"  class="width-90 form-control" autocomplete="off">
                                      <option value="" {{is_null($ced->exchangerate_id) ? "selected" : ""}}>Naira</option>
                                      @foreach ($exrate as $item)
                                          <option value="{{$item->id}}" {{$ced->exchangerate_id == $item->id ? "selected" : ""}}>{{ucwords($item->currency)}}</option>
                                      @endforeach
                                  </select>
                              </div>
                            </div>
                            
                            <div class="form-group col-sm-3 controls">
                              <label>Upload Identification</label>
                              <div>
                                  <input class="width-90" type="file" name="upload_id" id="emp" autocomplete="off" accept=".jpg,.jpeg,.png">
                              </div>
                            </div>
                            
                          </div>
                         
                          <div class="row">
                            <div class="col-sm-12">
                              <h3>Next of Kin details</h3>
                            </div>
                            <div class="form-group col-sm-3 controls">
                                <label>Next of Kin(Full Name)</label>
                                <div>
                                    <input class="width-90" type="text" name="kin"  id="kin" value="{{$ced->next_kin}}" autocomplete="off" placeholder="Enter Next of Kin">
                                </div>
                              </div>
                              <div class="form-group col-sm-3 controls">
                                <label>Next of Kin Address</label>
                                <div>
                                    <input class="width-90" type="text" name="kin_address"  id="mde" value="{{$ced->kin_address}}" autocomplete="off" placeholder="Enter Next of Kin Address" >
                                </div>
                              </div>
                              <div class="form-group col-sm-3 controls">
                                <label>Next of Kin Phone</label>
                                <div>
                                    <input class="width-90" type="tel" name="kin_phone" id="phn" value="{{$ced->kin_phone}}" autocomplete="off" placeholder="Enter Next of Kin Phone">
                                </div>
                              </div>
                              <div class="form-group col-sm-3 controls">
                                <label>Next of Kin(Relationship)</label>
                                <div>
                                    <input class="width-100" type="text" name="kin_relate" id="phn" value="{{$ced->kin_relate}}" autocomplete="off" placeholder="Enter Next of Kin Phone">
                                </div>
                              </div>
                              
                          </div>

                    
                          <div class="row">
                            <div class="form-group col-sm-4 controls">
                              <label>Account Type</label>
                              <div>
                                  <select name="account_type"  class="width-90 form-control" onchange="document.getElementById('pcde').value = this.options[this.selectedIndex].getAttribute('data-pcode')" required id="actype" autocomplete="off">
                                      <option selected disabled>Select...</option>
                                      @foreach ($savingsprods as $item)
                                          <option value="{{$item->id}}" {{$item->id == $ced->account_type ? "selected" : ""}} data-pcode="{{$item->product_number}}">{{$item->name}}</option>
                                      @endforeach
                                  </select>
                              </div>
                            </div>
                            <input class="width-90" type="hidden" name="pcode" id="pcde" value="" autocomplete="off">
                            <input class="width-90" type="hidden" name="account_number" maxlength="10" required id="aact" value="{{$ced->acctno}}" autocomplete="off" placeholder="Enter Account Number">
                            <div class="form-group col-sm-4 controls">
                                <label>Account Number</label>
                                <div>
                                    <input class="width-90" type="text" maxlength="10" readonly value="{{$ced->acctno}}">
                                </div>
                              </div>
                              <div class="form-group col-sm-4 controls">
                                <label>Reference Account</label>
                                <div>
                                    <input class="width-90" type="text" name="refacct" id="acceref" value="{{$ced->refacct}}" autocomplete="off" placeholder="Enter Reference Account" >
                                </div>
                              </div>
                              
                          </div>

                          <div class="row">
                            <div class="form-group col-sm-4 controls">
                              <label>Account Officer</label>
                              <div>
                                  <select name="account_officer"  class="width-90 form-control" id="actype" autocomplete="off">
                                      <option selected disabled>Select Account Officer...</option>
                                      @foreach ($officers as $officer)
                                        <option value="{{$officer->id}}" {{$officer->id == $ced->accountofficer_id ? "selected" : ""}}>{{$officer->full_name}} </option>
                                      @endforeach
                                  </select>
                              </div>
                            </div>
                            <div class="form-group col-sm-4 controls">
                                <label>Upload Photo</label>
                                <div>
                                    <input class="width-90" type="file" name="photo"  id="pht" value="{{old('photo')}}" autocomplete="off" accept=".jpg,.jpeg,.png">
                                </div>
                              </div>
                              <div class="form-group col-sm-4 controls">
                                <label>Signature</label>
                                <div>
                                    <input class="width-90" type="file" name="signature" id="mde" value="{{old('signature')}}" autocomplete="off" accept=".jpg,.jpeg,.png">
                                </div>
                              </div>
                              
                          </div>
                          <input type="hidden" name="branchid" value="{{session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id}}">
                            <div class="form-group form-actions">
                                <div class="col-sm-4"> </div>
                                <div class="col-sm-7">
                                  <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Save Record</button>
                                  
                                </div>
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
    var actypeopt = $("#actype option:selected").attr('data-pcode');
    $("#pcde").val(actypeopt);

    $("#aact").keyup(function(){
      let ac = $("#aact").val().length;
      if(ac > 10){
        alert('Account Number Exceeded');
      }
    });
    // $("#bvn").keyup(function(){
    //   let bvn = $("#bvn").val();
    //   let _token = "{{csrf_token()}}";
    //   if(bvn.length > 11){
    //     $("#bvnstat").html('<b>Bvn Number Exceeded(requires 11 digits)</b>').addClass('text-danger');
    //   }else{
    //     $("#bvnstat").html('');
    //     $.ajax({
    //       url: '{{route("checkbvn")}}',
    //       method: 'post',
    //       data: {'bvn':bvn,'_token':_token},
    //       beforeSend:function(){
    //         $("#sttext").show();
    //       },
    //       success:function(data){
    //         $("#sttext").hide();
    //         console.log(data);
    //       },
    //       error:function(xhr,status,errorThrown){
    //         $("#sttext").hide();
    //         alert('An error occured while validating Bvn '+errorThrown);
    //         return false;
    //       }
    //     });
    //   }
    // });
  });
</script>
@endsection