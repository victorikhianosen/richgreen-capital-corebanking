@extends('layout.app')
@section('title')
    Settings
@endsection
@section('pagetitle')
Settings
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <form class="form-horizontal" id="submitsetins" action="{{route('setting.save')}}" method="post" enctype="multipart/form-data" role="form" onsubmit="thisForm()">
                    @csrf
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                        <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit">Save Record</button>
                     </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    
                      <ul class="nav nav-pills">
                        <li class="active"><a href="#general" data-toggle="tab">General</a></li>
                        <li><a href="#sms" data-toggle="tab">SMS</a></li>
                        <li><a href="#emtep" data-toggle="tab">Email Template</a></li>
                        <li><a href="#smstep" data-toggle="tab">SMS Template</a></li>
                        <li><a href="#sys" data-toggle="tab">System</a></li>
                        @if (Auth::user()->roles()->first()->name == "super admin")
                             <li><a href="#pymt" data-toggle="tab">Payment</a></li>
                        @endif
                       
                        <li><a href="#compy" data-toggle="tab">Company</a></li>
                      </ul>
                      <br/>
                      <?php 
                        $getsetvalue = new \App\Models\Setting();
                      ?>
                      <div class="tab-content  mgbt-xs-20">
                        {{-- general --}}
                        <div class="tab-pane active" id="general">
                              @if (Auth::user()->roles()->first()->name == "super admin" || Auth::user()->roles()->first()->name == "admin" || Auth::user()->roles()->first()->name == "managing director")

                            <div class="form-group">
                                <label class="col-sm-2 control-label">Company Name</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="text" name="company_name" placeholder="Company Name" required value="{{$getsetvalue->getsettingskey('company_name') ?? old('company_name')}}">
                                </div>
                              </div>
                              
                               <div class="form-group">
                                <label class="col-sm-2 control-label">Bank Code</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="text" name="company_code" placeholder="Bank Code" required value="{{$getsetvalue->getsettingskey('company_code') ?? old('company_code')}}">
                                </div>
                              </div>
                             @endif
                             
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Company Email</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="text" name="company_email" placeholder="Company Email"  value="{{$getsetvalue->getsettingskey('company_email') ?? old('company_email')}}">
                                </div>
                              </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Company Phone</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="text" name="company_phone" placeholder="Company Phone"  value="{{$getsetvalue->getsettingskey('company_phone') ?? old('company_phone')}}">
                                </div>
                              </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Company Website</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="text" name="company_website" placeholder="Company Website" value="{{$getsetvalue->getsettingskey('company_website') ?? old('company_website')}}">
                                </div>
                              </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Company Address</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="text" name="company_address" placeholder="Company Address" value="{{$getsetvalue->getsettingskey('company_address') ?? old('company_address')}}">
                                </div>
                              </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Country</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="text" name="company_country" placeholder="Company Country" value="{{$getsetvalue->getsettingskey('company_country') ?? old('company_country')}}">
                                </div>
                              </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Portal Address</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="text" name="portal_address" placeholder="Portal Address" value="{{$getsetvalue->getsettingskey('portal_address') ?? old('portal_address')}}">
                                </div>
                              </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Currency</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="text" name="company_currency" placeholder="Currency"  value="{{$getsetvalue->getsettingskey('company_currency') ?? old('company_currency')}}">
                                  
                                </div>
                              </div>

                              <div class="form-group">
                                <label class="col-sm-2 control-label">Currency Symbol</label>
                                <div class="col-sm-7 controls">
                                  <select name="currency_symbol" required class="width-70 form-control">
                                    <option selected disabled>Select Currency Symbol</option>
                                    <option value="$" {{$getsetvalue->getsettingskey('currency_symbol') == "$" ? "selected" : ""}} >$</option>
                                    <option value="£" {{$getsetvalue->getsettingskey('currency_symbol') == "£" ? "selected" : ""}} >£</option>
                                    <option value="₦" {{$getsetvalue->getsettingskey('currency_symbol') == "₦" ? "selected" : ""}}>₦</option>
                                    <option value="€" {{$getsetvalue->getsettingskey('currency_symbol') == "€" ? "selected" : ""}} >€</option>
                                     <option value="¢" {{$getsetvalue->getsettingskey('currency_symbol') == "¢" ? "selected" : ""}} >¢</option>
                                     <option value="GH¢" {{$getsetvalue->getsettingskey('currency_symbol') == "GH¢" ? "selected" : ""}} >GH¢</option>
                                </select>
                                </div>
                              </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Currency Position</label>
                                <div class="col-sm-7 controls">
                                  <select name="currency_position" required class="width-70 form-control">
                                    <option value="left" {{$getsetvalue->getsettingskey('currency_position') == "left" ? "selected" : ""}}>Left</option>
                                    <option value="right" {{$getsetvalue->getsettingskey('currency_position') == "right" ? "selected" : ""}}>Right</option>
                                  </select>
                                </div>
                              </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Company Logo</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="file" name="company_logo" placeholder="Company logo" accept=".jpeg,.jpg,.png">
                                </div>
                                <div class="col-sm-2"><img src="{{asset($getsetvalue->getsettingskey('company_logo'))}}" alt="company logo"></div>
                              </div>
                              <div class="form-group">
                                <label class="col-sm-2 control-label">Login Background</label>
                                <div class="col-sm-7 controls">
                                  <input class="width-70" type="file" name="background_image" placeholder="Login Background" accept=".jpeg,.jpg,.png">
                                </div>
                                <div class="col-sm-2"><img src="{{asset($getsetvalue->getsettingskey('login_background'))}}" alt="login background"></div>
                              </div>
                              
                        </div>
                        {{-- general --}}
                        <div class="tab-pane" id="sms"><!--sms -->
                          <div class="form-group">
                            <label class="col-sm-2 control-label">SMS Enabled</label>
                            <div class="col-sm-7 controls">
                              <select name="sms_enabled" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('sms_enabled') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('sms_enabled') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-2 control-label">SMS Sender Name</label>
                            <div class="col-sm-7 controls">
                              <input class="width-70" type="text" name="sms_sender" placeholder="Senders Name" value="{{$getsetvalue->getsettingskey('sms_sender') ?? old('sms_sender')}}">
                            </div>
                          </div>

                         <div class="form-group">
                            <label class="col-sm-2 control-label">Active SMS Gateway</label>
                            <div class="col-sm-7 controls">
                              <select name="active_sms" id="activesms" required class="width-70 form-control" autocomplete="off" onchange="if(this.value == 'vtpass'){document.getElementById('vtpass').style.display='block';}else{document.getElementById('vtpass').style.display='none'}">
                               <option disabled selected>Select SMS Gateway</option>
                                <option value="vtpass" {{$getsetvalue->getsettingskey('active_sms') == "vtpass" ? "selected" : ""}}>vtpass</option>
                                <option value="termii" {{$getsetvalue->getsettingskey('active_sms') == "termii" ? "selected" : ""}}>Termii</option>
                                
                              </select>
                            </div>
                          </div>
                          <div>
                            <div class="form-group">
                              <label class="col-sm-2 control-label">SMS Public Key</label>
                              <div class="col-sm-7 controls">
                                <input class="width-70" type="text" name="sms_public_key" placeholder="public key"  value="{{$getsetvalue->getsettingskey('sms_public_key') ?? old('sms_public_key')}}">
                              </div>
                            </div>
                         
                            <div class="form-group" id="vtpass" style="display: {{$getsetvalue->getsettingskey('active_sms') == "vtpass" ? "block" : "none"}}">
                              <label class="col-sm-2 control-label">SMS Secret Key</label>
                              <div class="col-sm-7 controls">
                                <input class="width-70" type="text" name="sms_secret_key" placeholder="Secret Key"  value="{{$getsetvalue->getsettingskey('sms_secret_key') ?? old('sms_secret_key')}}">
                              </div>
                            </div>

                            <div class="form-group">
                              <label class="col-sm-2 control-label">SMS Base Url</label>
                              <div class="col-sm-7 controls">
                                <input class="width-70" type="text" name="sms_baseurl" placeholder="SMS Base Url"  value="{{$getsetvalue->getsettingskey('sms_baseurl') ?? old('sms_baseurl')}}">
                              </div>
                            </div>
                          </div>
                         
                        </div>
                        <!--sms -->
                        <!--email template -->
                        <div class="tab-pane" id="emtep">
                          <h6 class="vd_bg-grey vd_white" style="padding: 6px 2px;margin:5px 0;">Payments</h6>

                          <div class="form-group">
                            <label class="col-sm-2 control-label">Payment Recieved Subject</label>
                            <div class="col-sm-7 controls">
                              <input class="width-70" type="text" name="payment_received_email_subject" placeholder="First Name" value="{{$getsetvalue->getsettingskey('payment_received_email_subject') ?? old('payment_received_email_subject')}}">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Payment Recieved Template</label>
                            <div class="col-sm-9 controls">
                              <textarea id="emmessage" name="payment_received_email_template" class="width-100 form-control"  rows="10" placeholder="Write your message here">{!!$getsetvalue->getsettingskey('payment_received_email_template') ?? old('payment_received_email_template')!!}</textarea>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-2 control-label">Payment Receipt Subject</label>
                            <div class="col-sm-7 controls">
                              <input class="width-70" type="text" name="payment_email_subject" placeholder="First Name" value="{{$getsetvalue->getsettingskey('payment_email_subject') ?? old('payment_email_subject')}}">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Payment Receipt Template</label>
                            <div class="col-sm-9 controls">
                              <textarea id="rptmessage" name="payment_email_template" class="width-100 form-control"  rows="10" placeholder="Write your message here">{!!$getsetvalue->getsettingskey('payment_email_template') ?? old('payment_email_template')!!}</textarea>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-2 control-label">Repayment Reminder Subject</label>
                            <div class="col-sm-7 controls">
                              <input class="width-70" type="text" name="loan_payment_reminder_subject" placeholder="" value="{{$getsetvalue->getsettingskey('loan_payment_reminder_subject') ?? old('loan_payment_reminder_subject')}}">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Repayment Reminder Template</label>
                            <div class="col-sm-9 controls">
                              <textarea id="rpymessage" name="loan_payment_reminder_email_template" class="width-100 form-control"  rows="10" placeholder="Write your message here">{!!$getsetvalue->getsettingskey('loan_payment_reminder_email_template') ?? old('loan_payment_reminder_email_template')!!}</textarea>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-2 control-label">Missed Repayment Subject</label>
                            <div class="col-sm-7 controls">
                              <input class="width-70" type="text" name="missed_payment_email_subject" placeholder="" value="{{$getsetvalue->getsettingskey('missed_payment_email_subject') ?? old('missed_payment_email_subject')}}">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Missed Repayment Template</label>
                            <div class="col-sm-9 controls">
                              <textarea id="mimessage" name="missed_payment_email_template" class="width-100 form-control"  rows="10" placeholder="Write your message here">{!!$getsetvalue->getsettingskey('missed_payment_email_template') ?? old('missed_payment_email_template')!!}</textarea>
                            </div>
                          </div>

                          <h6 class="vd_bg-grey vd_white" style="padding: 6px 2px; margin:5px 0;">Loans</h6>

                          <div class="form-group">
                            <label class="col-sm-2 control-label">Borrower Statement Subject</label>
                            <div class="col-sm-7 controls">
                              <input class="width-70" type="text" name="borrower_statement_email_subject" placeholder="" value="{{$getsetvalue->getsettingskey('borrower_statement_email_subject') ?? old('borrower_statement_email_subject')}}">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Borrower Statement Template</label>
                            <div class="col-sm-9 controls">
                              <textarea id="bstmessage" name="borrower_statement_email_template" class="width-100 form-control"  rows="10" placeholder="Write your message here">{!!$getsetvalue->getsettingskey('borrower_statement_email_template') ?? old('borrower_statement_email_template')!!}</textarea>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-2 control-label">Loan Statement Subject</label>
                            <div class="col-sm-7 controls">
                              <input class="width-70" type="text" name="loan_statement_email_subject" value="{{$getsetvalue->getsettingskey('loan_statement_email_subject') ?? old('loan_statement_email_subject')}}">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Loan Statement Template</label>
                            <div class="col-sm-9 controls">
                              <textarea id="lstmessage" name="loan_statement_email_template" class="width-100 form-control"  rows="10" placeholder="Write your message here">{!!$getsetvalue->getsettingskey('loan_statement_email_template') ?? old('loan_statement_email_template')!!}</textarea>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-2 control-label">Loan Schedule Subject</label>
                            <div class="col-sm-7 controls">
                              <input class="width-70" type="text" name="loan_schedule_email_subject" placeholder="Loan Schedule Subject" value="{{$getsetvalue->getsettingskey('loan_schedule_email_subject') ?? old('loan_schedule_email_subject')}}">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Loan Schedule Template</label>
                            <div class="col-sm-9 controls">
                              <textarea id="lschmessage" name="loan_schedule_email_template" class="width-100 form-control"  rows="10" placeholder="Write your message here">{!!$getsetvalue->getsettingskey('loan_schedule_email_template') ?? old('loan_schedule_email_template')!!}</textarea>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-2 control-label">Loan Overdue Subject</label>
                            <div class="col-sm-7 controls">
                              <input class="width-70" type="text" name="loan_overdue_email_subject" placeholder="Loan Overdue Subject" value="{{$getsetvalue->getsettingskey('loan_overdue_email_subject') ?? old('loan_overdue_email_subject')}}">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Loan Overdue Template</label>
                            <div class="col-sm-9 controls">
                              <textarea id="lovmessage" name="loan_overdue_email_template" class="width-100 form-control"  rows="10" placeholder="Write your message here">{!!$getsetvalue->getsettingskey('loan_overdue_email_template') ?? old('loan_overdue_email_template')!!}</textarea>
                            </div>
                          </div>
                        </div>

                        <div class="tab-pane" id="smstep"><!-- Sms template -->
                          <h6 class="vd_bg-grey vd_white" style="padding: 6px 2px;margin:5px 0;">Payments</h6>

                          <div class="form-group">
                            <label class="col-sm-2 control-label">Payment Recieved SMS Template</label>
                            <div class="col-sm-9 controls">
                              <textarea  name="payment_received_sms_template" class="width-100 form-control"  rows="6" placeholder="Write your message here">{!!$getsetvalue->getsettingskey('payment_received_sms_template') ?? old('payment_received_sms_template')!!}</textarea>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-2 control-label">Repayment Reminder SMS Template</label>
                            <div class="col-sm-9 controls">
                              <textarea  name="loan_payment_reminder_sms_template" class="width-100 form-control"  rows="6" placeholder="Write your message here">{!!$getsetvalue->getsettingskey('loan_payment_reminder_sms_template') ?? old('loan_payment_reminder_sms_template')!!}</textarea>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-2 control-label">Missed Repayment Template</label>
                            <div class="col-sm-9 controls">
                              <textarea  name="missed_payment_sms_template" class="width-100 form-control"  rows="6" placeholder="Write your message here">{!!$getsetvalue->getsettingskey('missed_payment_sms_template') ?? old('missed_payment_sms_template')!!}</textarea>
                            </div>
                          </div>

                          <h6 class="vd_bg-grey vd_white" style="padding: 6px 2px; margin:5px 0;">Loans</h6>
                          
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Loan Overdue Template</label>
                            <div class="col-sm-9 controls">
                              <textarea  name="loan_overdue_sms_template" class="width-100 form-control"  rows="6" placeholder="Write your message here">{!!$getsetvalue->getsettingskey('loan_overdue_sms_template') ?? old('loan_overdue_sms_template')!!}</textarea>
                            </div>
                          </div>
                          
                        <h6 class="vd_bg-grey vd_white" style="padding: 6px 2px; margin:5px 0;">Birthday Message</h6>
                          
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Birthday Message</label>
                            <div class="col-sm-9 controls">
                              <textarea  name="birthday_msg" class="width-100 form-control"  rows="6" placeholder="Write your message here">{!!$getsetvalue->getsettingskey('birthday_msg') ?? old('birthday_msg')!!}</textarea>
                            </div>
                          </div>

                        </div>
                        
                        <div class="tab-pane" id="sys"><!--system-->
                         @if (Auth::user()->roles()->first()->name == "super admin" || Auth::user()->roles()->first()->name == "admin" || Auth::user()->roles()->first()->name == "managing director")
                          <div class="form-group">
                            <label class="col-sm-3 control-label"> Enable 2FA</label>
                            <div class="col-sm-7 controls">
                              <select name="enable2fa" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('enable_2FA') == "1" ? "selected" : ""}}>Enable</option>
                                <option value="0" {{$getsetvalue->getsettingskey('enable_2FA') == "0" ? "selected" : ""}}>Disable</option>
                              </select>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-3 control-label">Outward/Transfer Options</label>
                            <div class="col-sm-7 controls">
                              <select name="payoptn" required class="width-70 form-control">
                                <option selected disabled>Select...</option>
                                <option value="1" {{$getsetvalue->getsettingskey('payoption') == "1" ? "selected" : ""}}>Asset Matrix MFB</option>
                                <option value="2" {{$getsetvalue->getsettingskey('payoption') == "2" ? "selected" : ""}}>Monnify</option>
                                <option value="3" {{$getsetvalue->getsettingskey('payoption') == "3" ? "selected" : ""}}>Nibbs Pay</option>
                              <option value="4" {{$getsetvalue->getsettingskey('payoption') == "4" ? "selected" : ""}}>Wireless Verify</option>
                              </select>
                            </div>
                          </div>
                          
                          <div class="form-group">
                            <label class="col-sm-3 control-label">Enable Virtual Account</label>
                            <div class="col-sm-7 controls">
                              <select name="enable_virtual_account" required class="width-70 form-control" style="width:70%">
                                <option selected disabled>Select</option>
                                <option value="1" {{$getsetvalue->getsettingskey('enable_virtual_ac') == '1' ? "selected" : ""}} >Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('enable_virtual_ac') == '0' ? "selected" : ""}} >No</option>
              
                            </select>
                            </div>
                          </div>
                          
                          <div class="form-group">
                            <label class="col-sm-3 control-label">BVN Verification Route</label>
                            <div class="col-sm-7 controls">
                              <select name="bvnroute" required class="width-70 form-control">
                                <option selected disabled>Select...</option>
                                <option value="1" {{$getsetvalue->getsettingskey('bvnroute') == "1" ? "selected" : ""}}>Asset Matrix MFB</option>
                                <option value="2" {{$getsetvalue->getsettingskey('bvnroute') == "2" ? "selected" : ""}}>Wireless Verify</option>
                              </select>
                            </div>
                          </div>
                          
                          @endif
                          
                           @if (Auth::user()->account_type == "system")
                          <div class="form-group">
                            <label class="col-sm-3 control-label">Inward Trnx Options</label>
                            <div class="col-sm-7 controls">
                              <select name="inwardpayoptn" required class="width-70 form-control">
                                <option selected disabled>Select...</option>
                                <option value="1" {{$getsetvalue->getsettingskey('inwardoption') == "1" ? "selected" : ""}}>Providus Bank</option>
                                <option value="2" {{$getsetvalue->getsettingskey('inwardoption') == "2" ? "selected" : ""}}>Asset Matrix MFB</option>
                                 <option value="0" {{$getsetvalue->getsettingskey('inwardoption') == "0" ? "selected" : ""}}>None</option>
                              </select>
                            </div>
                          </div>
                          
                          @endif
                          <div class="form-group">
                            <label class="col-sm-3 control-label"> Enable Cron Job</label>
                            <div class="col-sm-7 controls">
                              <select name="enable_cron" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('enable_cron') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('enable_cron') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-3 control-label">Auto Apply Penalty</label>
                            <div class="col-sm-7 controls">
                              <select name="auto_apply_penalty" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('auto_apply_penalty') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('auto_apply_penalty') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-3 control-label">Auto Payment Receipt Email</label>
                            <div class="col-sm-7 controls">
                              <select name="auto_payment_receipt_email" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('auto_payment_receipt_email') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('auto_payment_receipt_email') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-3 control-label">Auto Payment Receipt SMS</label>
                            <div class="col-sm-7 controls">
                              <select name="auto_payment_receipt_sms" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('auto_payment_receipt_sms') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('auto_payment_receipt_sms') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>
                          
                          <div class="form-group">
                            <label class="col-sm-3 control-label">Auto Repayment Reminder SMS</label>
                            <div class="col-sm-7 controls">
                              <select name="auto_repayment_sms_reminder" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('auto_repayment_sms_reminder') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('auto_repayment_sms_reminder') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-3 control-label">Auto Repayment Reminder Email</label>
                            <div class="col-sm-7 controls">
                              <select name="auto_repayment_email_reminder" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('auto_repayment_email_reminder') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('auto_repayment_email_reminder') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-3 control-label">Auto Repayment Days Before</label>
                            <div class="col-sm-7 controls">
                              <input type="number" name="auto_repayment_days" required class="width-70 form-control" value="{{$getsetvalue->getsettingskey('auto_repayment_days') ?? old('auto_repayment_days')}}">
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-3 control-label">Auto Overdue Repayment Reminder SMS</label>
                            <div class="col-sm-7 controls">
                              <select name="auto_overdue_repayment_sms_reminder" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('auto_overdue_repayment_sms_reminder') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('auto_overdue_repayment_sms_reminder') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-3 control-label">Auto Overdue Repayment Reminder Email</label>
                            <div class="col-sm-7 controls">
                              <select name="auto_overdue_repayment_email_reminder" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('auto_overdue_repayment_email_reminder') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('auto_overdue_repayment_email_reminder') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-3 control-label">Auto Overdue Repayment Days After</label>
                            <div class="col-sm-7 controls">
                              <input type="number" name="auto_overdue_repayment_days" required class="width-70 form-control" value="{{$getsetvalue->getsettingskey('auto_overdue_repayment_days') ?? old('auto_overdue_repayment_days')}}">
                            </div>
                          </div>
            
                          <div class="form-group">
                            <label class="col-sm-3 control-label">Auto Overdue Loan Reminder SMS</label>
                            <div class="col-sm-7 controls">
                              <select name="auto_overdue_loan_sms_reminder" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('auto_overdue_loan_sms_reminder') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('auto_overdue_loan_sms_reminder') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-3 control-label">Auto Overdue Loan Reminder Email</label>
                            <div class="col-sm-7 controls">
                              <select name="auto_overdue_loan_email_reminder" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('auto_overdue_loan_email_reminder') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('auto_overdue_loan_email_reminder') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-3 control-label">Auto Overdue Loan Days After</label>
                            <div class="col-sm-7 controls">
                              <input type="number" name="auto_overdue_loan_days" required class="width-70 form-control" value="{{$getsetvalue->getsettingskey('auto_overdue_loan_days') ?? old('auto_overdue_loan_days')}}">
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-3 control-label">Allow Self Registration</label>
                            <div class="col-sm-7 controls">
                              <select name="allow_self_registration" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('allow_self_registration') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('allow_self_registration') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-3 control-label">Client auto activate account</label>
                            <div class="col-sm-7 controls">
                              <select name="client_auto_activate_account" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('client_auto_activate_account') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('client_auto_activate_account') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-3 control-label">Allow Client login</label>
                            <div class="col-sm-7 controls">
                              <select name="allow_client_login" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('allow_client_login') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('allow_client_login') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-3 control-label">Allow client to request guarantor</label>
                            <div class="col-sm-7 controls">
                              <select name="client_request_guarantor" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('client_request_guarantor') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('client_request_guarantor') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-3 control-label"> Allow client apply</label>
                            <div class="col-sm-7 controls">
                              <select name="allow_client_apply" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('allow_client_apply') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('allow_client_apply') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-3 control-label">Auto post savings interest</label>
                            <div class="col-sm-7 controls">
                              <select name="auto_post_savings_interest" required class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('auto_post_savings_interest') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('auto_post_savings_interest') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-3 control-label">Welcome note</label>
                            <div class="col-sm-7 controls">
                              <textarea  name="welcome_note" class="width-70 form-control"  rows="3" placeholder="Write your message here">{!!$getsetvalue->getsettingskey('welcome_note') ?? old('welcome_note')!!}</textarea>
                            </div>
                          </div>
                        </div>

                        <div class="tab-pane" id="pymt"><!-- Payment -->
                          <div class="form-group">
                            <label class="col-sm-3 control-label"> Enable Online Payment</label>
                            <div class="col-sm-7 controls">
                              <select name="enable_online_payment" class="width-70 form-control">
                                <option value="1" {{$getsetvalue->getsettingskey('enable_online_payment') == "1" ? "selected" : ""}}>Yes</option>
                                <option value="0" {{$getsetvalue->getsettingskey('enable_online_payment') == "0" ? "selected" : ""}}>No</option>
                              </select>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-3 control-label">Payment Gateway</label>
                            <div class="col-sm-7 controls">
                              <input type="text" class="width-70 form-control" name="payment_gateway" value="{{$getsetvalue->getsettingskey('payment_gateway') ?? old('payment_gateway')}}">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-3 control-label">Public Key</label>
                            <div class="col-sm-7 controls">
                              <input type="text" class="width-70 form-control" name="gateway_pub_key"  value="{{$getsetvalue->getsettingskey('gateway_pub_key') ?? old('gateway_pub_key')}}">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-3 control-label">Secret Key</label>
                            <div class="col-sm-7 controls">
                              <input type="text" class="width-70 form-control" name="gateway_secret_key" value="{{$getsetvalue->getsettingskey('gateway_secret_key') ?? old('gateway_secret_key')}}">
                            </div>
                          </div>
                          
                        </div>

                        <div class="tab-pane" id="compy"><!-- Company -->

                          @if (Auth::user()->roles()->first()->name == "super admin" || Auth::user()->roles()->first()->name == "admin" || Auth::user()->roles()->first()->name == "managing director")
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Company Total Shares</label>
                            <div class="col-sm-7 controls">
                              <input class="width-70" type="text" readonly name="company_share" id="cshrs" placeholder="Company Share" required value="{{$getsetvalue->getsettingskey('company_share') ?? old('company_share')}}">
                              <span style="cursor: pointer;" class="btn btn-primary btn-sm" onclick="document.getElementById('cshares').style.display='block'">ADD Shares</span>

                              <div class="col-sm-8 controls" style="display:none; margin-top:7px" id="cshares">
                                <input class="width-70" type="number" placeholder="Company Share" onkeyup="calculte(this.value,'{{$getsetvalue->getsettingskey('company_share')}}','cshrs','add')" value="">
                                <span style="cursor: pointer;" class="btn btn-success btn-sm" onclick="document.getElementById('submitsetins').submit()">OK</span>
                              </div>
                            </div>
                           
                          </div>
                          
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Company Total Capital</label>
                            <div class="col-sm-7 controls">
                              <input class="width-70" type="text" name="company_capital" id="adcp" readonly placeholder="Company Capital" value="{{$getsetvalue->getsettingskey('company_capital') ?? old('company_capital')}}">
                              <span style="cursor: pointer;" class="btn btn-primary btn-sm" onclick="document.getElementById('cpital').style.display='block'">ADD Capital</span>
                             
                              <div class="col-sm-8 controls" style="display:none; margin-top:7px" id="cpital">
                                <input class="width-70" type="number"  placeholder="Add Company Capital" onkeyup="calculte(this.value,'{{$getsetvalue->getsettingskey('company_capital')}}','adcp','add')"  value="">
                                <span style="cursor: pointer;" class="btn btn-success btn-sm" onclick="document.getElementById('submitsetins').submit()">OK</span>
                              </div>
                            </div>
                            
                          </div>
                          
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Bank Fund</label>
                            <div class="col-sm-7 controls">
                              <input class="width-70" type="text" name="bank_fund" readonly id="adbk" value="{{$getsetvalue->getsettingskey('bank_fund') ?? old('bank_fund')}}">
                              <span style="cursor: pointer;" class="btn btn-primary btn-sm" onclick="document.getElementById('bkfund').style.display='block'">ADD Bank Fund</span>
                              
                              <div class="col-sm-8 controls" style="display: none; margin-top:7px" id="bkfund">
                                <input class="width-70" type="number"  placeholder="Bank Funds" onkeyup="calculte(this.value,'{{$getsetvalue->getsettingskey('bank_fund')}}','adbk','add')" value="">
                                <span style="cursor: pointer;" class="btn btn-success btn-sm" onclick="document.getElementById('submitsetins').submit()">OK</span>
                              </div>
                            </div>
                          </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Deposit Limit</label>
                            <div class="col-sm-8 controls">
                                <div style="display:flex">
                              <input class="width-70" type="text" name="deposit_limit" readonly id="dlimt" value="{{$getsetvalue->getsettingskey('deposit_limit') ?? old('deposit_limit')}}">
                                  &nbsp;<span style="cursor: pointer;" class="btn btn-primary btn-sm" onclick="document.getElementById('dlimit').style.display='block'">ADD Deposit Limit</span>
                              &nbsp;
                              <span style="cursor: pointer;" class="btn btn-danger btn-sm" onclick="document.getElementById('sdlimit').style.display='block'">SUB Deposit Limit</span>
                              </div>
                              
                              <div class="col-sm-8 controls" style="display: none; margin-top:7px" id="dlimit">
                                <input class="width-70" type="number"  placeholder="Deposit Limit" onkeyup="calculte(this.value,'{{$getsetvalue->getsettingskey('deposit_limit')}}','dlimt','add')" value="">
                                <span style="cursor: pointer;" class="btn btn-success btn-sm" onclick="document.getElementById('submitsetins').submit()">OK</span>
                              </div>
                              <div class="col-sm-8 controls" style="display: none; margin-top:7px" id="sdlimit">
                                <input class="width-70" type="number"  placeholder="Deposit Limit subtract" onkeyup="calculte(this.value,'{{$getsetvalue->getsettingskey('deposit_limit')}}','dlimt','sub')" value="">
                                <span style="cursor: pointer;" class="btn btn-success btn-sm" onclick="document.getElementById('submitsetins').submit()">OK</span>
                              </div>
                            </div>
                          </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Withdrawal Limit</label>
                            <div class="col-sm-8 controls">
                              <div style="display:flex">
                                  <input class="width-70" type="text" name="withdrawal_limit" readonly id="wlimt" value="{{$getsetvalue->getsettingskey('withdrawal_limit') ?? old('withdrawal_limit')}}">
                              &nbsp;<span style="cursor: pointer;" class="btn btn-primary btn-sm" id="wadd" onclick="document.getElementById('wlimit').style.display='block'">ADD Withdrawal Limit</span>
                              &nbsp;<span style="cursor: pointer;" class="btn btn-danger btn-sm" id="wsub" onclick="document.getElementById('swlimit').style.display='block'">SUB Withdrawal Limit</span>
                              
                              </div>
                              <div class="col-sm-8 controls" style="display: none; margin-top:7px" id="wlimit">
                                <input class="width-70" type="number"  placeholder="Withdrawal Limit" onkeyup="calculte(this.value,'{{$getsetvalue->getsettingskey('withdrawal_limit')}}','wlimt','add')" value="">
                                <span style="cursor: pointer;" class="btn btn-success btn-sm" onclick="document.getElementById('submitsetins').submit()">OK</span>
                              </div>
                              <div class="col-sm-8 controls" style="display: none; margin-top:7px" id="swlimit">
                                <input class="width-70" type="number"  placeholder="Withdrawal Limit substract" onkeyup="calculte(this.value,'{{$getsetvalue->getsettingskey('withdrawal_limit')}}','wlimt','sub')" value="">
                                <span style="cursor: pointer;" class="btn btn-success btn-sm" onclick="document.getElementById('submitsetins').submit()">OK</span>
                              </div>
                            </div>
                          </div>
                          
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Till Account</label>
                            <div class="col-sm-7 controls">
                              <select name="glcode" required class="glsect width-70 form-control" style="width:70%">
                                <option selected disabled>Select Till Account</option>
                                @foreach ($data as $item)
                                <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('till_account') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                @endforeach
                            </select>
                            </div>
                          </div>
                          
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Vault Account</label>
                            <div class="col-sm-7 controls">
                              <select name="vglcode" required class="glsect width-70 form-control" style="width:70%">
                                <option selected disabled>Select Vault Account</option>
                                @foreach ($data as $item)
                                <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('vault_account') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                @endforeach
                            </select>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Inward Trnx GL</label>
                            <div class="col-sm-7 controls">
                              <select name="assetmtx" required class="glsect width-70 form-control" style="width:70%">
                                <option selected disabled>Select Inward GL</option>
                                @foreach ($data as $item)
                                <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('assetmtx') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                @endforeach
                            </select>
                            </div>
                          </div>
                          
                             <div class="form-group">
                            <label class="col-sm-2 control-label">Manual Liquidation Charge</label>
                            <div class="col-sm-7 controls">
                              <select name="liquidation_interest" required class="glsect width-70 form-control" style="width:70%">
                                <option selected disabled>Select Liquidation Charge</option>
                                @foreach ($data as $item)
                                <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('fdliquid_interest') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                @endforeach
                            </select>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="col-sm-2 control-label">Company Account</label>
                            <div class="col-sm-7 controls">
                              <select name="cmglcode" required class="glsect width-70 form-control" style="width:70%">
                                <option selected disabled>Select GL Account</option>
                                @foreach ($data as $item)
                                <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('company_account') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                @endforeach
                            </select>
                            </div>
                          </div>
                        
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Moniepoint GL Account</label>
                            <div class="col-sm-7 controls">
                              <select name="moniepglcode" required class="glsect width-70 form-control" style="width:70%">
                                <option selected disabled>Select GL Account</option>
                                @foreach ($data as $item)
                                <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('moniepointgl') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                @endforeach
                            </select>
                            </div>
                          </div>
                          
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Vtpass GL Account</label>
                            <div class="col-sm-7 controls">
                              <select name="vtglcode" required class="glsect width-70 form-control" style="width:70%">
                                <option selected disabled>Select GL Account</option>
                                @foreach ($data as $item)
                                <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('vtpass_account') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                @endforeach
                            </select>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Vtpass IncomeGL Account</label>
                            <div class="col-sm-7 controls">
                              <select name="vtincmglcode" required class="glsect width-70 form-control" style="width:70%">
                                <option selected disabled>Select GL Account</option>
                                @foreach ($data as $item)
                                <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('vtpass_income') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                @endforeach
                            </select>
                            </div>
                          </div>
                          
                            <div class="form-group">
                            <label class="col-sm-2 control-label">Giftbills GL Account</label>
                            <div class="col-sm-7 controls">
                              <select name="gblglcode" required class="glsect width-100 form-control" style="width:70%">
                                <option selected disabled>Select GL Account</option>
                                @foreach ($data as $item)
                                <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('giftbill_account') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                @endforeach
                            </select>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Giftbills IncomeGL Account</label>
                            <div class="col-sm-7 controls">
                              <select name="giftincmglcode" required class="glsect width-70 form-control" style="width:70%">
                                <option selected disabled>Select GL Account</option>
                                @foreach ($data as $item)
                                <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('giftbill_income') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                @endforeach
                            </select>
                            </div>
                          </div>
                          
                           <div class="form-group">
                            <label class="col-sm-2 control-label">POS(charge) GL Account</label>
                            <div class="col-sm-7 controls">
                              <select name="poschrglcode" required class="glsect width-70 form-control" style="width:70%">
                                <option selected disabled>Select GL Account</option>
                                @foreach ($data as $item)
                                <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('pos_charges') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                @endforeach
                            </select>
                            </div>
                          </div>
                          
                           <div class="form-group">
                            <label class="col-sm-2 control-label">Charges GL Account</label>
                            <div class="col-sm-7 controls">
                              <select name="chrglcode" required class="glsect width-70 form-control" style="width:70%">
                                <option selected disabled>Select GL Account</option>
                                @foreach ($data as $item)
                                <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('glcharges') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                @endforeach
                            </select>
                            </div>
                          </div>
                          
                           <div class="form-group">
                            <label class="col-sm-2 control-label">Other Charges GL Account</label>
                            <div class="col-sm-7 controls">
                              <select name="chrgother" required class="glsect width-70 form-control" style="width:70%">
                                <option selected disabled>Select GL Account</option>
                                @foreach ($data as $item)
                                <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('othrchargesgl') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                @endforeach
                            </select>
                            </div>
                          </div>

                          
                          
                          <div class="form-group">
                            <label class="col-sm-2 control-label">Max Online Tranfer</label>
                            <div class="col-sm-7 controls">
                              <input type="number" pattern="0-9"  name="online_transfer" required class="width-70 form-control" value="{{$getsetvalue->getsettingskey('online_transfer')}}">
                            </div>
                          </div>
                                
                                <fieldset>
                            <legend>Suspense GL Accounts</legend>
                            <div class="row">
                              <div class="col-md-4 col-lg-4 col-sm-6">
                                <div class="form-group">
                                  <label>Income Suspense Account</label>
                                    <select name="inmsusp" required class="glsect width-70 form-control" style="width:70%">
                                      <option selected disabled>Select GL Account</option>
                                        @foreach ($data as $item)
                                        <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('income_suspense') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                        @endforeach
                                  </select>
                                </div>
                              </div>
                              <div class="col-md-4 col-lg-4 col-sm-6">
                                <div class="form-group">
                                  <label>Asset Suspense Account</label>
                                    <select name="asstsusp" required class="glsect width-70 form-control" style="width:70%">
                                      <option selected disabled>Select GL Account</option>
                                        @foreach ($data as $item)
                                        <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('asset_suspense') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                        @endforeach
                                  </select>
                                </div>
                              </div>
                              <div class="col-md-4 col-lg-4 col-sm-6">
                                <div class="form-group">
                                  <label>Liability Suspense Account</label>
                                    <select name="libsusp" required class="glsect width-70 form-control" style="width:70%">
                                      <option selected disabled>Select GL Account</option>
                                        @foreach ($data as $item)
                                        <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('liability_suspense') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                        @endforeach
                                  </select>
                                </div>
                              </div>
                              <div class="col-md-4 col-lg-4 col-sm-6">
                                <div class="form-group">
                                  <label>Expense Suspense Account</label>
                                    <select name="expsusp" required class="glsect width-70 form-control" style="width:70%">
                                      <option selected disabled>Select GL Account</option>
                                        @foreach ($data as $item)
                                        <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('exps_suspense') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                        @endforeach
                                  </select>
                                </div>
                              </div>
                              <div class="col-md-4 col-lg-4 col-sm-6">
                                <div class="form-group">
                                  <label>Capital Suspense Account</label>
                                    <select name="capsusp" required class="glsect width-70 form-control" style="width:70%">
                                      <option selected disabled>Select GL Account</option>
                                        @foreach ($data as $item)
                                        <option value="{{$item->gl_code}}" {{$getsetvalue->getsettingskey('capital_suspense') == $item->gl_code ? "selected" : ""}} >{{ucwords($item->gl_name)}}</option>
                                        @endforeach
                                  </select>
                                </div>
                              </div>

                              </div>
                          </fieldset>
                          
                       <fieldset>
                            <legend>Charges</legend>
                            
                           <div class="row">
                            <div class="col-md-4 col-lg-4 col-sm-6">
                              <div class="form-group">
                                <label >Transfer Charge</label>
                                  <select name="chrgtrn" required class="glsect width-70 form-control" style="width:70%">
                                    <option selected disabled>Select Charges</option>
                                    @foreach ($chargedata as $item)
                                    <option value="{{$item->id}}" {{$getsetvalue->getsettingskey('transfer_charge') == $item->id ? "selected" : ""}} >{{ucwords($item->chargename)}}</option>
                                    @endforeach
                                </select>
                              </div>
                            </div>

                            
                            <div class="col-md-4 col-lg-4 col-sm-6">
                              <div class="form-group">
                                <label>Esusu Charge</label><br>
                                  <select name="chrgesusu" required class="glsect width-100 form-control" style="width:70%">
                                    <option selected disabled>Select Charges</option>
                                    @foreach ($chargedata as $item)
                                    <option value="{{$item->id}}" {{$getsetvalue->getsettingskey('esusucharges') == $item->id ? "selected" : ""}} >{{ucwords($item->chargename)}}</option>
                                    @endforeach
                                </select>
                              </div>
                            </div>

                            <div class="col-md-4 col-lg-4 col-sm-6">
                              <div class="form-group">
                                <label>Monthly Fee Charge</label>
                                  <select name="chrgmonthly" required class="glsect width-70 form-control" style="width:70%">
                                    <option selected disabled>Select Charges</option>
                                    @foreach ($chargedata as $item)
                                    <option value="{{$item->id}}" {{$getsetvalue->getsettingskey('monthlycharges') == $item->id ? "selected" : ""}} >{{ucwords($item->chargename)}}</option>
                                    @endforeach
                                </select>
                              </div>
                            </div>

                            <div class="col-md-4 col-lg-4 col-sm-6">
                              <div class="form-group">
                                <label>Form Fees Charge</label>
                                  <select name="chrgformfee" required class="glsect width-70 form-control" style="width:70%">
                                    <option selected disabled>Select Charges</option>
                                    @foreach ($chargedata as $item)
                                    <option value="{{$item->id}}" {{$getsetvalue->getsettingskey('frmfeecharges') == $item->id ? "selected" : ""}} >{{ucwords($item->chargename)}}</option>
                                    @endforeach
                                </select>
                                </div>
                              </div>
                            

                            <div class="col-md-4 col-lg-4 col-sm-6">
                              <div class="form-group">
                                <label>Process Fee Charge</label>
                               
                                  <select name="chrgprcessfee" required class="glsect width-70 form-control" style="width:70%">
                                    <option selected disabled>Select Charges</option>
                                    @foreach ($chargedata as $item)
                                    <option value="{{$item->id}}" {{$getsetvalue->getsettingskey('processcharges') == $item->id ? "selected" : ""}} >{{ucwords($item->chargename)}}</option>
                                    @endforeach
                                </select>
                              </div>
                            </div>

                          <div class="col-md-4 col-lg-4 col-sm-6">
                              <div class="form-group">
                                <label>Other Charges</label><br>
                               
                                  <select name="chrgsother" required class="glsect width-70 form-control" style="width:70%">
                                    <option selected disabled>Select Charges</option>
                                    @foreach ($chargedata as $item)
                                    <option value="{{$item->id}}" {{$getsetvalue->getsettingskey('othercharges') == $item->id ? "selected" : ""}} >{{ucwords($item->chargename)}}</option>
                                    @endforeach
                                </select>
                              </div>
                            </div>
                            
                          <div class="col-md-4 col-lg-4 col-sm-6">
                              <div class="form-group"><br>
                                <label>Monnify Charges</label><br>
                                  <input type="number" pattern="0-9"  name="monnify_charge" required class="width-70 form-control" value="{{$getsetvalue->getsettingskey('monnifycharge')}}">
                              </div>
                            </div>
                            
                          <div class="col-md-4 col-lg-4 col-sm-6">
                              <div class="form-group"><br>
                                <label>Bank Charges</label><br>
                                  <input type="number" pattern="0-9"  name="bank_charge" required class="width-70 form-control" value="{{$getsetvalue->getsettingskey('bankcharge')}}">
                              </div>
                            </div>
                            
                            <div class="col-md-4 col-lg-4 col-sm-6">
                              <div class="form-group"><br>
                                <label>Withholding Tax(%)</label><br>
                                  <input type="number" pattern="0-9" step="0.01"  name="withholding_tax" required class="width-70 form-control" value="{{$getsetvalue->getsettingskey('withholdingtax')}}">
                              </div>
                            </div>
                          <div class="col-md-4 col-lg-4 col-sm-6">
                              <div class="form-group"><br>
                                <label>Fixed Deposit Investment Charges(%)</label><br>
                                  <input type="number" pattern="0-9"  name="fd_charge" required class="width-70 form-control" value="{{$getsetvalue->getsettingskey('fdcharge')}}">
                              </div>
                            </div>
                            
                           </div>
                          </fieldset>
                            
                          @endif

                        </div><!--company-->

                      </div>

                      <div class="form-group form-actions">
                        <div class="col-sm-4"> </div>
                        <div class="col-sm-7">
                          <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnsetsubmit"><i class="icon-ok"></i>Save Record</button>
                          
                        </div>
                      </div>
                  </div>
                </div>
                <!-- Panel Widget -->
                
            </form> 
              </div>
              <!-- col-md-12 --> 
            </div>
            <!-- row -->
  </div>
@endsection 
@section('scripts')
<script type="text/javascript">
function actsms(vl){
  if (vl === "clickatell") {
    $("#clickatell").show();
    $("#twilio").hide();
    $("#infobip").hide();
  } else if (vl === "twilio") {
    $("#clickatell").hide();
    $("#twilio").show();
    $("#infobip").hide();
  }else if (vl === "infobip"){
    $("#clickatell").hide();
    $("#twilio").hide();
    $("#infobip").show();
  }
}

function calculte(val1,val2,id,opt){
  //alert(val1);
  let tot = 0;
   if(opt == "sub"){
        tot = parseInt(val2) - parseInt(val1);
   }else{
     tot = parseInt(val1) + parseInt(val2);
   }
  document.getElementById(id).value=tot;
}
</script>
<script>
  $(document).ready(function(){
          $(".glsect").select2();

    $('#emmessage').wysihtml5();
    $('#rptmessage').wysihtml5();
    $('#rmmessage').wysihtml5();
    $('#rpymessage').wysihtml5();
    $('#mimessage').wysihtml5();
    $('#bstmessage').wysihtml5();
    $('#lstmessage').wysihtml5();
    $('#lschmessage').wysihtml5();
    $('#lovmessage').wysihtml5();

    var smsopt = $("#activesms option:selected").val();
    if (smsopt === 'clickatell') {
      $("#clickatell").show();
    } else if (vl === "twilio"){
      $("#twilio").show();
    }else if (vl === "infobip"){
      $("#infobip").show();
    }
  });
</script>
@endsection