@extends('layout.app')
@section('title')
    Cbn Report
@endsection
@section('pagetitle')
Cbn Report
@endsection
@section('content')
<?php
$getsetvalue = new \App\Models\Setting();
?>
 <style>
  .form-step{
      display: none;
  }
  .form-active{
      display: block;
  }
</style>
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading"></div>
                  <div class="panel-body">
                    <form class="form-horizontal" id="submitsetins" action="{{route('report.generatetcbnreport')}}" method="post" enctype="multipart/form-data" role="form" onsubmit="thisForm()">
                      @csrf
                    <div class="form-step form-active" id="">
                      
                        <h5 style="font-weight:900">Reporting Date Information</h5>
                          <div class="row">
                            <div class="col-md-8 col-lg-8 col-sm-12">
                              <div class="form-group" style="margin-bottom: 10px">
                                <label class="col-sm-4 control-label">Reporting Date:</label>
                                <div class="col-sm-8 controls">
                                  <input class="width-70 form-control" type="date" name="reporting_date" placeholder="Reporting Date" required value="">
                                </div>
                              </div>
                             
                            <div class="form-group" style="margin-bottom: 10px">
                                <label class="col-sm-4 control-label">Last EOY Date:</label>
                                <div class="col-sm-8 controls">
                                  <?php 
                                    $lyer = \Carbon\Carbon::now()->subYear()->format('Y');
                                    ?>
                                  <input class="width-70 form-control" type="text" name="last_eoy_date" readonly placeholder="Last EOY Date"  value="{{\Carbon\Carbon::parse($lyer.'-12')->endOfMonth()->format('d-m-Y')}}">
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="row" style="float:right;margin-top:10px;">
                            <button type="button" class="btn btn-info btn-sm next-step">Next <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                           </div>
                   </div><!-- .reporting date info-->

                   <div class="form-step" id="">
                    <h5 style="font-weight:900">General Information</h5>
                    <div class="row">
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Bank Name:</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="bank_name" required value="{{ucwords($getsetvalue->getsettingskey('company_name'))}}">
                        </div>
                      </div>
                     
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Bank Code:</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="bank_code" required value="{{ucwords($getsetvalue->getsettingskey('company_code'))}}">
                        </div>
                      </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Bank Email:</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="bank_email" required  value="">
                        </div>
                      </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">MD's Name:</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="md_name" placeholder="" required  value="">
                        </div>
                      </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">MD's Phone No:</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="tel" name="md_phone" placeholder="" required  value="">
                        </div>
                      </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Compliance Officer's Name:</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="co_name" placeholder="" required  value="">
                        </div>
                      </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Compliance Officer's Phone No:</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="tel" name="co_phone" placeholder="" required  value="">
                        </div>
                      </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">State:</label>
                        <div class="col-sm-7 controls">
                          <select id="stateoforigin" name="state" onchange="getcities(this.value)" style="width: 70% !important;" class="state" autocomplete="off" required>
                            <option selected disabled>State of Origin</option>
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
                    <div class="form-group">
                        <label class="col-sm-2 control-label">State Code:</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="state_code" placeholder="" required  value="">
                        </div>
                      </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">LGA:</label>
                        <div class="col-sm-7 controls">
                          <select name="lga" class="width-70 lga"  style="width: 70% !important;" required id="lga" autocomplete="off">
                            <option selected disabled>Select...</option>
                        </select>
                        </div>
                      </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">LGA Code:</label>
                        <div class="col-sm-7 controls">
                          <input class="width-70" type="text" name="lga_code" placeholder="" required  value="">
                        </div>
                      </div>
                      
                      </div>

                      <div class="row" style="float:right;margin-top:10px;">
                        <button type="button" class="btn btn-danger btn-sm prev-step"><i class="fa fa-arrow-left" aria-hidden="true"></i> Prev</button>
                        <button type="button" class="btn btn-info btn-sm next-step">Next <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                       </div>
                   </div><!-- .reporting date info-->

                   <div class="form-step" id="">
                    <h5 style="font-weight:900">Staff Information</h5>

                        <!-- Panel Widget -->
                <div class="panel widget">
                  {{-- <div class="panel-heading vd_bg-grey">
                    <h3 class="panel-title"> <span class="menu-icon"> <i class="fa fa-magic"></i> </span> UI Accordion </h3>
                  </div> --}}
                  <div class="panel-body">
                    <div class="panel-group" id="accordion">
                      <div class="panel panel-default">
                        <div class="panel-heading vd_bg-green vd_bd-green">
                          <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"><small style="color: #fff;">Current Month</small></a> </h4>
                        </div>
                        <div id="collapseOne" class="panel-collapse collapse in">
                          <div class="panel-body">
                            
                            <div class="col-md-6 col-lg-6 col-sm-12">
                              <div class="form-group">
                                <label class="col-sm-5 control-label">No of Male Senior Staff:</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="number" name="male_senior"  required value="">
                                </div>
                              </div>
                             
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-12">
                              <div class="form-group">
                                <label class="col-sm-5 control-label">No of Female Senior Staff:</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="number" name="female_senior" required value="">
                                </div>
                              </div>
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-12">
                              <div class="form-group">
                                <label class="col-sm-5 control-label">No of Male Junior Staff:</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="number" name="male_junior"  required value="">
                                </div>
                              </div>
                             
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-12">
                              <div class="form-group">
                                <label class="col-sm-5 control-label">No of Female Junior Staff:</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="number" name="female_junior" required value="">
                                </div>
                              </div>
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-12">
                              <div class="form-group">
                                <label class="col-sm-5 control-label">No of Male Staff Resigned/Dimissed:</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="number" name="male_resign"  required value="">
                                </div>
                              </div>
                             
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-12">
                              <div class="form-group">
                                <label class="col-sm-5 control-label">No of Female Staff Resigned/Dimissed:</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="number" name="female_resign" required value="">
                                </div>
                              </div>
                            </div>
    
                            <div class="col-md-6 col-lg-6 col-sm-12">
                              <div class="form-group">
                                <label class="col-sm-5 control-label">No of Male Staff Recruited:</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="number" name="male_recruit"  required value="">
                                </div>
                              </div>
                             
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-12">
                              <div class="form-group">
                                <label class="col-sm-5 control-label">No of Female Recruited:</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="number" name="female_recruit" required value="">
                                </div>
                              </div>
                            </div>

                          </div>
                        </div>
                      </div>
                      <div class="panel panel-default">
                        <div class="panel-heading vd_bg-yellow">
                          <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo"><small style="color: #fff;">Cummulative Till Date</small></a> </h4>
                        </div>
                        <div id="collapseTwo" class="panel-collapse collapse">
                          <div class="panel-body">

                            <div class="col-md-6 col-lg-6 col-sm-12">
                              <div class="form-group">
                                <label class="col-sm-5 control-label">No of Male Senior Staff:</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="number" name="cum_male_senior"  required value="">
                                </div>
                              </div>
                             
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-12">
                              <div class="form-group">
                                <label class="col-sm-5 control-label">No of Female Senior Staff:</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="number" name="cum_female_senior" required value="">
                                </div>
                              </div>
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-12">
                              <div class="form-group">
                                <label class="col-sm-5 control-label">No of Male Junior Staff:</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="number" name="cum_male_junior"  required value="">
                                </div>
                              </div>
                             
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-12">
                              <div class="form-group">
                                <label class="col-sm-5 control-label">No of Female Junior Staff:</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="number" name="cum_female_junior" required value="">
                                </div>
                              </div>
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-12">
                              <div class="form-group">
                                <label class="col-sm-5 control-label">No of Male Staff Resigned/Dimissed:</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="number" name="cum_male_resign"  required value="">
                                </div>
                              </div>
                             
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-12">
                              <div class="form-group">
                                <label class="col-sm-5 control-label">No of Female Staff Resigned/Dimissed:</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="number" name="cum_female_resign" required value="">
                                </div>
                              </div>
                            </div>
    
                            <div class="col-md-6 col-lg-6 col-sm-12">
                              <div class="form-group">
                                <label class="col-sm-5 control-label">No of Male Staff Recruited:</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="number" name="cum_male_recruit"  required value="">
                                </div>
                              </div>
                             
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-12">
                              <div class="form-group">
                                <label class="col-sm-5 control-label">No of Female Recruited:</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="number" name="cum_female_recruit" required value="">
                                </div>
                              </div>
                            </div>

                          </div>
                        </div>
                      </div>
                      <div class="panel panel-default">
                        <div class="panel-heading vd_bg-blue">
                          <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree"><small style="color: #fff;">Branch Information</small></a> </h4>
                        </div>
                        <div id="collapseThree" class="panel-collapse collapse">
                          <div class="panel-body"> 

                            <div class="row">
                              <div class="col-md-6 col-lg-6 col-sm-12">
                                <div class="form-group">
                                  <label class="col-sm-5 control-label">No of Listing Branches:</label>
                                  <div class="col-sm-7 controls">
                                    <input class="width-70" type="number" name="list_branch"  required value="">
                                  </div>
                                </div>
                               
                              </div>
                              <div class="col-md-6 col-lg-6 col-sm-12">
                                <div class="form-group">
                                  <label class="col-sm-5 control-label">No of Cash Center:</label>
                                  <div class="col-sm-7 controls">
                                    <input class="width-70" type="number" name="cash_center" required value="">
                                  </div>
                                </div>
                              </div>

                              <div class="col-md-6 col-lg-6 col-sm-12">
                                <div class="form-group">
                                  <label class="col-sm-5 control-label">No of New Branches:</label>
                                  <div class="col-sm-7 controls">
                                    <input class="width-70" type="number" name="new_branch"  required value="">
                                  </div>
                                </div>
                               
                              </div>
                              <div class="col-md-6 col-lg-6 col-sm-12">
                                <div class="form-group">
                                  <label class="col-sm-5 control-label">No of Meeting Points:</label>
                                  <div class="col-sm-7 controls">
                                    <input class="width-70" type="number" name="meet_point" required value="">
                                  </div>
                                </div>
                              </div>

                              <div class="col-md-6 col-lg-6 col-sm-12">
                                <div class="form-group">
                                  <label class="col-sm-5 control-label">No of Closed Branches:</label>
                                  <div class="col-sm-7 controls">
                                    <input class="width-70" type="number" name="closed_branch"  required value="">
                                  </div>
                                </div>
                               
                              </div>
                              <div class="col-md-6 col-lg-6 col-sm-12"></div>

                            </div>

                          </div>
                        </div>
                      </div>
                      <div class="panel panel-default">
                        <div class="panel-heading vd_bg-red">
                          <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordion" href="#collapsefour"><small style="color: #fff;">Others</small></a> </h4>
                        </div>
                        <div id="collapsefour" class="panel-collapse collapse">
                          <div class="panel-body"> 
                             
                            <div class="form-group">
                              <label class="col-sm-5 control-label">No Of Loan Officers:</label>
                              <div class="col-sm-7 controls">
                                <input class="width-70" type="number" name="loan_officer"  required value="">
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="col-sm-5 control-label">Recommended Provision As At Last Examination:</label>
                              <div class="col-sm-7 controls">
                                <input class="width-70" type="number" name="recommended_provision"  required value="">
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="col-sm-5 control-label">Date of last CBN/NDIC examination:</label>
                              <div class="col-sm-7 controls">
                                <input class="width-70" type="date" name="cbn_ndic"  required value="">
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="col-sm-5 control-label">Financial year end:</label>
                              <div class="col-sm-7 controls">
                                <input class="width-70" type="date" name="financial_year_end"  required value="">
                              </div>
                            </div>

                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                      <div class="row" style="float:right;margin-top:10px;">
                        <button type="button" class="btn btn-danger btn-sm prev-step"  id="pshd"><i class="fa fa-arrow-left" aria-hidden="true"></i> Prev</button>
                        <button type="Submit" class="btn btn-success btn-sm submt" id="btnssubmit"><i class="fa fa-save" aria-hidden="true"></i> Generate Report</button>
                       </div>
                   </div><!-- .reporting date info-->

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
   function printsection() {
    //document.getElementById("noprint").style.display='none';
  var divContents = document.getElementById("printdiv").innerHTML;
  var a = window.open('', '', 'height=500, width=500');
  a.document.write('<html>');
  a.document.write('<body>  @if (!empty($_GET["filter"]) && $_GET["filter"] == true)<h3>Profit / Loss for period: {{date("d M, Y",strtotime($_GET["datefrom"]))." To ".date("d M, Y",strtotime($_GET["dateto"]))}}</h3>@endif');
  a.document.write(divContents);
  a.document.write('</body></html>');
  a.document.close();
  a.print();
  }
  
  function exporttoexcel(){
      $("#profloss").table2excel({
    exclude: ".excludeThisClass",
    name: "Profit_And_Loss_Report",
    filename: "Profit_And_Loss_Report.xls", // do include extension
    preserveColors: false // set to true if you want background colors and font colors preserved
});
  }
</script>

<script>
  $(document).ready(function(){
    $(".state").select2();
    $(".lga").select2();
  });
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
@endsection