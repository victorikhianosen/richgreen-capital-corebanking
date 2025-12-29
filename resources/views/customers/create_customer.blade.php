@extends('layout.app')
@section('title')
    Create Customer
@endsection
@section('pagetitle')
Create Customer
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('customer.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('customer.store')}}" method="post" enctype="multipart/form-data" role="form" onsubmit="thisForm()">
                      @csrf
                      <div class="container">
                        <div class="row">
                          <div class="form-group col-sm-4 controls">
                            <label>Title</label>
                            <div>
                                <select name="title"  class="width-90 form-control" id="til" autocomplete="off">
                                    <option selected disabled>Select...</option>
                                    <option value="mr">Mr</option>
                                    <option value="mrs">Mrs</option>
                                    <option value="miss">Miss</option>
                                </select>
                            </div>
                          </div>
                            <div class="form-group col-sm-4 controls">
                                <label>Last Name<sup class="text-danger" style="font-size:14px;font-weight: bold">*</sup></label>
                                <div>
                                    <input class="width-90" type="text" name="last_name" autofocus id="flnm" required value="{{old('last_name')}}" autocomplete="off" placeholder="Enter Last Name">
                                </div>
                              </div>
                            <div class="form-group col-sm-4 controls">
                                <label>First Name<sup class="text-danger" style="font-size:14px;font-weight: bold">*</sup></label>
                                <div>
                                    <input class="width-90" type="text" name="first_name" autofocus id="flnm" required value="{{old('first_name')}}" autocomplete="off" placeholder="Enter First Name">
                                </div>
                              </div>
                              <!--<div class="form-group col-sm-3 controls">-->
                              <!--  <label>Username<sup class="text-danger" style="font-size:14px;font-weight: bold">*</sup></label>-->
                              <!--  <div>-->
                              <!--      <input class="width-90" type="text" name="username" autofocus id="urnm" required value="{{old('username')}}" autocomplete="off" placeholder="Enter Username">-->
                              <!--  </div>-->
                              <!--</div>-->
                              <div class="form-group col-sm-4 controls">
                                <label>Email</label>
                                <div>
                                    <input class="width-90" type="email" name="email" id="email" value="{{old('email')}}" autocomplete="off" placeholder="Enter Email" >
                                </div>
                              </div>
                              <div class="form-group col-sm-4 controls">
                                <label>Marital Status</label>
                                <div>
                                    <select name="marital_status"  class="width-90 form-control" id="til" autocomplete="off">
                                        <option selected disabled>Select...</option>
                                        <option value="single">Single</option>
                                        <option value="married">Married</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                              </div>
                              <div class="form-group col-sm-4 controls">
                                <label>Phone Number</label>
                                <div>
                                    <input class="width-90" type="tel" name="phone" id="phn"  value="{{old('phone')}}" autocomplete="off" placeholder="Enter phone Number" >
                                </div>
                              </div>
                              <div class="form-group col-sm-4 controls">
                                <label>DOB</label>
                                <div>
                                    <input class="width-90" type="date" name="dob" id="dob"  value="{{old('dob')}}" autocomplete="off" placeholder="Enter DOB">
                                </div>
                              </div>
                              <div class="form-group col-sm-4 controls">
                                <label>Gender<sup class="text-danger" style="font-size:14px;font-weight: bold">*</sup></label>
                                <div>
                                    <select name="gender" class="width-90 form-control" required id="gend" autocomplete="off">
                                        <option selected disabled>Select...</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                              </div>
                              <div class="form-group col-sm-4 controls">
                                <label>Account Section<sup class="text-danger" style="font-size:14px;font-weight: bold">*</sup></label>
                                <div>
                                    <select name="account_section" class="width-90 form-control" required id="gend" autocomplete="off">
                                        <option selected disabled>Select...</option>
                                        <option value="asset matrix">Asset Matrix</option>
                                        <option value="rich green masters">Rich Green Masters</option>
                                        <option value="rich green capital">Rich Green Capital</option>
                                    </select>
                                </div>
                              </div>
                              
                          </div>
    
                          <div class="row">
                              <div class="form-group col-sm-4 controls">
                                <label>Residential Address</label>
                                <div>
                                    <input class="width-90" type="text" name="address" id="addr" value="{{old('address')}}" autocomplete="off" placeholder="Enter Address" >
                                </div>
                              </div>
                            <div class="form-group col-sm-4 controls">
                              <label>Country</label>
                              <div>
                                  <input class="width-90" type="text" name="country" id="stte" value="{{old('country')?? 'Nigeria'}}" autocomplete="off" placeholder="Enter Country">
                              </div>
                            </div>
                              <div class="form-group col-sm-4 controls">
                                <label>State</label>
                                <div>
                                <select id="stateoforigin" name="state" onchange="getcities(this.value)" class="width-90 form-control" autocomplete="off">
                                    <option selected disabled label="State of Origin"></option>
                                    <option value="Abia">Abia</option>
                                    <option value="Abuja">Abuja</option>
                                    <option value="Adamawa">Adamawa</option>
                                    <option value="Anambra">Anambra</option>
                                    <option value="Akwa Ibom">Akwa Ibom </option>
                                    <option value="Bauchi">Bauchi</option>
                                    <option value="Bayelsa">Bayelsa </option>
                                    <option value="Benue">Benue</option>
                                    <option value="Borno">Borno</option>
                                    <option value="Cross River">Cross River</option>
                                    <option value="Delta">Delta</option>
                                    <option value="Ebonyi">Ebonyi</option>
                                    <option value="Edo">Edo</option>
                                    <option value="Ekiti">Ekiti</option>
                                    <option value="Enugu">Enugu</option>
                                    <option value="Gombe">Gombe</option>
                                    <option value="Lagos">Lagos</option>
                                    <option value="Imo">Imo</option>
                                    <option value="Jigawa">Jigawa</option>
                                    <option value="Kaduna">Kaduna</option>
                                    <option value="Kano">Kano</option>
                                    <option value="Katsina">Katsina</option>
                                    <option value="Kebbi">Kebbi</option>
                                    <option value="Kogi">Kogi</option>
                                    <option value="Nasarawa">Nasarawa</option>
                                    <option value="Niger">Niger</option>
                                    <option value="Ogun">Ogun</option>
                                    <option value="Ondo">Ondo</option>
                                    <option value="Osun">Osun</option>
                                    <option value="Oyo">Oyo</option>
                                    <option value="Plateau">Plateau</option>
                                    <option value="Rivers">Rivers</option>
                                    <option value="Sokoto">Sokoto</option>
                                    <option value="Taraba">Taraba</option>
                                    <option value="Yobe">Yobe</option>
                                    <option value="Zamfara">Zamfara</option>
                                
                                </select>
                                </div>
                              </div>

                              
                              
                          </div>

                          <div class="row">
                              <div class="form-group col-sm-4 controls">
                                <label>Local Govt Area</label>
                                <div>
                               <select name="lga" class="width-90 form-control" id="lga" autocomplete="off">
                                        <option selected disabled>Select...</option>
                                        
                                    </select>
                                </div>
                              </div>
                              <div class="form-group col-sm-4 controls">
                                <label>Religion</label>
                                <div>
                                    <select name="religion"  class="width-90 form-control" id="gend" autocomplete="off">
                                        <option selected disabled>Select...</option>
                                        <option value="christainity">Christainity</option>
                                        <option value="islam">Islam</option>
                                    </select>
                                </div>
                              </div>
                              <div class="form-group col-sm-4 controls">
                                <label>Bank Verification Number</label>
                                <div>
                                    <input class="width-90" type="number" name="bvn" id="bvn" value="{{old('bvn')}}" autocomplete="off" placeholder="Enter BVN" >
                                    <img src="{{asset('img/loading.gif')}}" id="sttext" style="display: none" alt="loading">
                                  </div>
                                  <p id="bvnstat"></p>
                              </div>
                           </div>
                            
                          <div class="row">
                            <div class="form-group col-sm-4 controls">
                              <label>Occupation</label>
                              <div>
                                  <input class="width-90" type="text" name="occupation" id="ocp" value="{{old('occupation')}}" autocomplete="off" placeholder="Enter Occupation" >
                              </div>
                            </div>
                            <div class="form-group col-sm-4 controls">
                              <label>Business Name</label>
                              <div>
                                  <input class="width-90" type="text" name="business_name" id="bn" value="{{old('business_name')}}" autocomplete="off" placeholder="Enter Business Name">
                              </div>
                            </div>
                            <div class="form-group col-sm-4 controls">
                              <label>Working Status</label>
                              <div>
                                <select name="working_status"  class="width-90 form-control" autocomplete="off">
                                  <option selected disabled>Select...</option>
                                  <option value="teacher">Teacher</option>
                                  <option value="employee">Employee</option>
                                  <option value="owner">Owner</option>
                                  <option value="student">Student</option>
                                  <option value="overseas worker">Overseas Worker</option>
                                  <option value="pensioner">Pensioner</option>
                              </select>
                              </div>
                            </div>
                              
                          </div>

                          <div class="row">
                            <div class="form-group col-sm-4 controls">
                              <label>Means of Indentification</label>
                              <div>
                                  <select name="means_of_id"  class="width-90 form-control" id="mense" autocomplete="off">
                                      <option selected disabled>Select...</option>
                                      <option value="national id">National ID</option>
                                      <option value="voters card">Voters Card</option>
                                      <option value="international passport">International Passport</option>
                                      <option value="drivers lincense">Drivers Lincense</option>
                                  </select>
                              </div>
                            </div>
                            <div class="form-group col-sm-4 controls">
                              <label>Upload Identification</label>
                              <div>
                                  <input class="width-90" type="file" name="upload_id" id="emp" autocomplete="off" accept=".jpg,.jpeg,.png">
                              </div>
                            </div>
                            
                             <div class="form-group col-sm-4 controls">
                                <label>Domicilary Account Type</label>
                                <div>
                                    <select name="domicilary"  class="width-90 form-control" autocomplete="off">
                                        <option value="">Naira</option>
                                        @foreach ($exrate as $item)
                                            <option value="{{$item->id}}">{{ucwords($item->currency)}}</option>
                                        @endforeach
                                    </select>
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
                                    <input class="width-90" type="text" name="kin"  id="kin" value="{{old('kin')}}" autocomplete="off" placeholder="Enter Next of Kin">
                                </div>
                              </div>
                              <div class="form-group col-sm-3 controls">
                                <label>Next of Kin Address</label>
                                <div>
                                    <input class="width-90" type="text" name="kin_address" id="mde" value="{{old('kin_address')}}" autocomplete="off" placeholder="Enter Next of Kin Address" >
                                </div>
                              </div>
                              <div class="form-group col-sm-3 controls">
                                <label>Next of Kin Phone</label>
                                <div>
                                    <input class="width-90" type="tel" name="kin_phone" id="phn" value="{{old('kin_phone')}}" autocomplete="off" placeholder="Enter Next of Kin Phone">
                                </div>
                              </div>
                              <div class="form-group col-sm-3 controls">
                                <label>Next of Kin(Relationship)</label>
                                <div>
                                    <input class="width-100" type="text" name="kin_relate" id="phn"  value="{{old('kin_relate')}}" autocomplete="off" placeholder="Enter Next of Kin Phone">
                                </div>
                              </div>
                              
                          </div>

                    
                          <div class="row">
                            <div class="form-group col-sm-4 controls">
                              <label>Account Type<sup class="text-danger" style="font-size:14px;font-weight: bold">*</sup></label>
                              <div>
                                  <select name="account_type"  class="width-90 form-control" required id="actype" autocomplete="off">
                                      <option selected disabled>Select...</option>
                                      @foreach ($savingsprods as $item)
                                          <option value="{{$item->id}}" data-pcode="{{$item->product_number}}">{{$item->name}}</option>
                                      @endforeach
                                  </select>
                              </div>
                            </div>
                            <div class="form-group col-sm-4 controls">
                                <label>Account Number<sup class="text-danger" style="font-size:14px;font-weight: bold">*</sup></label>
                                <div>
                                    <input class="width-90" type="number" readonly name="account_number" maxlength="10" required id="aact" value="{{old('account_number')}}" autocomplete="off" placeholder="Enter Account Number">
                                </div>
                                <a href="javascript:void(0)" class="text-primary" style="cursor: pointer" onclick="genacnum(document.getElementById('actype').options[document.getElementById('actype').selectedIndex].getAttribute('data-pcode'))">Generate Account Number</a>
                              </div>
                              <div class="form-group col-sm-4 controls">
                                <label>Reference Account</label>
                                <div>
                                    <input class="width-90" type="text" name="refacct" id="acceref" value="{{old('refacct')}}" autocomplete="off" placeholder="Enter Reference Account" >
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
                                        <option value="{{$officer->id}}">{{$officer->full_name}} </option>
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
                                    <input class="width-90" type="file" name="signature"  id="mde" value="{{old('signature')}}" autocomplete="off" accept=".jpg,.jpeg,.png">
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
  function genacnum(n){
    //alert("gdgkhfjh");
    if(n === null){
      alert('please select an account type');
    }else{
      let rnum = "";
    rnum = Math.floor(1000000 + Math.random() * 9000000);
    $("#aact").val(n+""+rnum);
    }
  }
</script>

<script>
  function getcities(city){
            let options = "<option selected disabled>Select Lga</option>";
            for(var s=0; s < locations.length; s++){
              if(locations[s].name == city){
                // console.log('city', locations.length[s].name);
                let cities = locations[s].lgas;
                for(let i=0; i<cities.length;i++){
                    options += "<option value="+cities[i]+">"+cities[i]+"</option>";
                }
              }
            }
            document.getElementById("lga").innerHTML=options;
        }
</script>

<script>
  $(document).ready(function(){
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