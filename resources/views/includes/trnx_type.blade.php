@if($key->type=="deposit")
<span class="label label-success">Deposit</span>
@endif

@if($key->type=="withdrawal")
<span class="label label-danger">Withdrawal</span>
@endif
@if($key->type=="bank_fees")
<a class="label label-primary">Bank Fee</a>
@endif
@if($key->type=="esusu" || $key->type=="transfer_charge")
<a class="label label-primary">Transfer Charge</a>
@endif
@if($key->type=="repayment")
<a class="label label-warning">Repayment</a>
@endif
@if($key->type=="credit")
<a class="label label-success">Credit</a>
@endif
@if($key->type=="debit")
<a class="label label-danger">Debit</a>
@endif
@if($key->type=="default_fee")
<a class="label label-info">Default Charge</a>
@endif
@if($key->type=="monthly_charge")
<a class="label label-danger">Monthly Charge</a>
@endif
@if($key->type=="sms")
<a class="label label-defualt"> Sms</a>
@endif
@if($key->type=="dividend")
<span class="label label-danger">Dividend</span>
@endif
@if($key->type=="interest")
<a class="label label-warning">Interest</a>
@endif
@if($key->type=="fixed_deposit")
<a class="label label-warning">Fixed Deposit</a>
@endif 
@if($key->type=="fd_interest")
<a class="label label-info"> FD Interest</a>
@endif
@if($key->type=="investment")
<a class="label label-default"> Investment </a>
@endif
@if($key->type=="inv_int")
<a class="label label-warning">Investment Int</a>
@endif
@if($key->type=="rev_withdrawal")
 <span class="label label-danger"> Withdrawal Reversed</span>
@endif 
@if($key->type=="rev_fixed_deposit")
 <span class="label label-primary">Fixed Deposit Reversed</span>
@endif 
@if($key->type=="form_fees")
 <span class="label label-primary">Loan Form</span>
@endif
@if($key->type=="process_fees")
 <span class="label label-danger">Process Fee</span>
@endif
@if($key->type=="rev_interest")
<span class="label label-warning">Interest Reversed</span>
@endif 
@if($key->type=="rev_deposit")
<span class="label label-danger">Deposit Reversed</span>
@endif 
@if($key->type=="loan")
<span class="label label-primary">Loan Disbursed</span>
@endif 
@if($key->type=="wht")
<span class="label label-default">Withholding Tax</span>
@endif 
@if($key->type=="guarantee")
<span class="label label-success">Guarantee</span>
@endif
@if($key->type=="guarantee_restored")
   <span class="label label-info"> Guarantee Restored</span>
@endif
@if($key->type=="electricity")
   <span class="label label-info">Electricity</span>
@endif
@if($key->type=="data_subscription")
   <span class="label label-info">Data Subscription</span>
@endif
@if($key->type=="cabletv")
   <span class="label label-info">Cable TV Subcription</span>
@endif
@if($key->type=="airtime_topup")
   <span class="label label-info">Airtime Topup</span>
@endif