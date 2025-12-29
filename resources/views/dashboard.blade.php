@extends('layout.app')
@section('title')
    Dashboard
@endsection
@section('pagetitle')
Dashboard
@endsection
@section('content')
<?php
    $getsetvalue = new \App\Models\Setting();
   ?>
   @inject('getloan', 'App\Http\Controllers\PageController')
<div class="vd_content-section clearfix">
    <div class="row">
        @can('total loans released')
        <div class="col-lg-3 col-md-6 col-sm-6 mgbt-sm-15">
            <div class="vd_status-widget vd_bg-grey widget">                    
                <a class="panel-body" href="#">
                        <span style="font-size: 18px">
                            {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(\App\Models\Loan::where('status','disbursed')->sum('principal'),2)}}
                        </span>   
                    <div style="font-size: 14px">
                        Total Loan Principal outstanding
                    </div>                                                               
                </a>        
           </div>                
        </div>
        @endcan
      @can('total collections')
      <div class="col-lg-3 col-md-6 col-sm-6 mgbt-sm-15">
        <div class="vd_status-widget vd_bg-grey  widget">          
            <a class="panel-body" href="#">
                    <span style="font-size: 18px">
                       {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(\App\Models\Loan::where('status','pending')->sum('principal'),2)}}
                    </span>   
                <div style="font-size: 14px">
                  Loan Pending Not Approved
                </div>  
            </a>                                                                
        </div>             
     </div>
      @endcan
      @can('total collections')
      <div class="col-lg-3 col-md-6 col-sm-6 mgbt-sm-15">
        <div class="vd_status-widget vd_bg-grey  widget">          
            <a class="panel-body" href="#">
                    <span style="font-size: 18px">
                       {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(\App\Models\Loan::where('status','approved')->sum('principal'),2)}}
                    </span>   
                <div style="font-size: 14px">
                  Loan Approved Not Disbursed
                </div>  
            </a>                                                                
        </div>             
     </div>
      @endcan
      @can('total collections')
      <div class="col-lg-3 col-md-6 col-sm-6 mgbt-sm-15">
        <div class="vd_status-widget vd_bg-grey  widget">          
            <a class="panel-body" href="#">
                    <span style="font-size: 18px">
                       {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(\App\Models\Loan::where('status','closed')->sum('principal'),2)}}
                    </span>   
                <div style="font-size: 14px">
                  Loan Closed
                </div>  
            </a>                                                                
        </div>             
     </div>
      @endcan
    </div><!--row-->

    <div class="row">
        @can('registered customers')
            <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">
                <div class="vd_status-widget vd_bg-grey widget">                          
                <a class="panel-body"  href="#">                                  
                    <span style="font-size: 18px">
                        {{number_format(\App\Models\Customer::count())}}
                    </span>   
                <div style="font-size: 14px">
                    Registered Customers
                </div>
                </a>                                                                  
            </div>               
            </div>
        @endcan
       
          <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">
            <div class="vd_status-widget vd_bg-grey widget">
                <a class="panel-body"  href="#"> 
                    <span style="font-size: 18px">
                        {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(\App\Models\Loan::where('status','disbursed')->sum('principal'),2) }} 
                    </span>  
                <div style="font-size: 14px">
                    Total Loan To Be Released
                </div>  
                </a>                                                                
            </div> 
        </div>
          @can('total collections')
            <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">
                <div class="vd_status-widget vd_bg-grey widget">
                    <a class="panel-body"  href="#"> 
                        <span style="font-size: 18px">
                            {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loans_total_paid(),2) }}
                        </span>  
                    <div style="font-size: 14px">
                    Total Collection
                    </div>  
                    </a>                                                                
                </div> 
            </div>
          @endcan
          @can('disburse loans')
          <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">
            <div class="vd_status-widget vd_bg-grey widget">
                <a class="panel-body"  href="#"> 
                    <span style="font-size: 18px">
                        {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->loans_total_due(),2) }} 
                    </span>  
                <div style="font-size: 14px">
                   Total Outstanding
                </div>  
                </a>                                                                
            </div> 
        </div>
        @endcan
    </div><!--row-->

    <div class="row">
        @can('disburse loans')
        <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">
          <div class="vd_status-widget vd_bg-grey widget">
              <a class="panel-body"  href="#"> 
                  <span style="font-size: 18px">
                      {{\App\Models\Loan::where('status','disbursed')->count()}} 
                  </span>  
              <div style="font-size: 14px">
                 Open Loan
              </div>  
              </a>                                                                
          </div> 
      </div>
      @endcan
      @can('loans closed')
      <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">
        <div class="vd_status-widget vd_bg-grey widget">
            <a class="panel-body"  href="#"> 
                <span style="font-size: 18px">
                    {{\App\Models\Loan::where('status','closed')->count()}} 
                </span>  
            <div style="font-size: 14px">
               Closed Loan
            </div>  
            </a>                                                                
        </div> 
    </div>
    @endcan
    @can('total loans pending')
    <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">
      <div class="vd_status-widget vd_bg-grey widget">
          <a class="panel-body"  href="#"> 
              <span style="font-size: 18px">
                {{\App\Models\Loan::where('status','pending')->count()}} 
              </span>  
          <div style="font-size: 14px">
            Pending Loan
          </div>  
          </a>                                                                
      </div> 
  </div>
  @endcan
  @can('loans approved')
  <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">
    <div class="vd_status-widget vd_bg-grey widget">
        <a class="panel-body"  href="#"> 
            <span style="font-size: 18px">
                {{ \App\Models\Loan::where('status','approved')->count() }}
            </span>  
        <div style="font-size: 14px">
            Approved Loan
        </div>  
        </a>                                                                
    </div> 
</div>
@endcan
    </div><!--row-->

    {{-- <div class="row">
        @can('loans rescheduled')
        <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">
          <div class="vd_status-widget vd_bg-facebook widget">
              <a class="panel-body"  href="#"> 
                  <span style="font-size: 18px">
                    {{ \App\Models\Loan::where('status','rescheduled')->count() }}
                  </span>  
              <div style="font-size: 14px">
                  Rescheduled Loan
              </div>  
              </a>                                                                
          </div> 
      </div>
      @endcan  @can('loans written off')
      <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">
        <div class="vd_status-widget vd_bg-googleplus widget">
            <a class="panel-body"  href="#"> 
                <span style="font-size: 18px">
                    {{ \App\Models\Loan::where('status','written_off')->count() }}
                </span>  
            <div style="font-size: 14px">
                Written Off Loan
            </div>  
            </a>                                                                
        </div> 
    </div>
    @endcan
    
    @can('loans approved')
    <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">
      <div class="vd_status-widget vd_bg-red widget">
          <a class="panel-body"  href="#"> 
              <span style="font-size: 18px">
                {{ \App\Models\Loan::where('status','declined')->count() }}
              </span>  
          <div style="font-size: 14px">
             Declined Loan
          </div>  
          </a>                                                                
      </div> 
  </div>
  @endcan

  @can('loans withdrawn')
  <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">
    <div class="vd_status-widget vd_bg-black widget">
        <a class="panel-body"  href="#"> 
            <span style="font-size: 18px">
                {{ \App\Models\Loan::where('status','withdrawn')->count() }}
            </span>  
        <div style="font-size: 14px">
            Withdrawn Loan
        </div>  
        </a>                                                                
    </div> 
</div>
@endcan
    </div><!--row--> --}}

    <div class="row">
        @can('view fixed deposit')
        <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">
          <div class="vd_status-widget vd_bg-grey widget">
              <a class="panel-body"  href="#"> 
                  <span style="font-size: 18px">
                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(\App\Models\GeneralLedger::select('account_balance')->where('gl_code','20944548')->first()->account_balance,2) }}
                 </span>  
              <div style="font-size: 14px">
                Fixed Deposit
              </div>  
              </a>                                                                
          </div> 
      </div>
      @endcan 
      
        @can('view fixed deposit')
    <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">
      <div class="vd_status-widget vd_bg-grey widget">
          <a class="panel-body"  href="#"> 
              <span style="font-size: 18px">
                {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(\App\Models\GeneralLedger::select('account_balance')->where('gl_code','50249457')->first()->account_balance,2) }} </span>  
          <div style="font-size: 14px">
             Fixed Deposit Interest
          </div>  
          </a>                                                                
      </div> 
  </div>
  @endcan
  
      {{-- @can('general ledger')
      <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">
        <div class="vd_status-widget vd_bg-googleplus widget">
            <a class="panel-body"  href="#"> 
                <span style="font-size: 18px">
                    {{$getsetvalue->getsettingskey('currency_symbol')."".number_format(\App\Models\GeneralLedger::select('account_balance')->where('gl_code','20993097')->first()->account_balance,2) }}
                </span>  
            <div style="font-size: 14px">
                Saving Account
            </div>  
            </a>                                                                
        </div> 
    </div>
    @endcan --}}
    

  {{-- @can('general ledger')
  <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">
    <div class="vd_status-widget vd_bg-green widget">
        <a class="panel-body"  href="#"> 
            <span style="font-size: 18px">
                {{ $getsetvalue->getsettingskey('currency_symbol')."".number_format(\App\Models\GeneralLedger::select('account_balance')->where('gl_code','20639526')->first()->account_balance,2) }}
            </span>  
        <div style="font-size: 14px">
            Current Account
        </div>  
        </a>                                                                
    </div> 
</div>
@endcan --}}
    </div><!--row-->


<div class="row">
<!--  <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">-->
<!--    <div class="vd_status-widget vd_bg-yellow widget">-->
<!--        <a class="panel-body"  href="#"> -->
<!--            <span style="font-size: 18px">-->
<!--                {{ $getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->total_FD_interest_expense(),2) }}-->
<!--            </span>  -->
<!--        <div style="font-size: 14px">-->
<!--            Ikedc Product-->
<!--        </div>  -->
<!--        </a>                                                                -->
<!--    </div> -->
<!--</div>-->

<!--  <div class="col-lg-3 col-md-6 col-sm-6 mgbt-xs-15">-->
<!--    <div class="vd_status-widget vd_bg-green widget">-->
<!--        <a class="panel-body"  href="#"> -->
<!--            <span style="font-size: 18px">-->
<!--                {{ $getsetvalue->getsettingskey('currency_symbol')."".number_format($getloan->total_inv_int_expense(),2) }}-->
<!--            </span>  -->
<!--        <div style="font-size: 14px">-->
<!--            Gotv Product-->
<!--        </div>  -->
<!--        </a>                                                                -->
<!--    </div> -->
<!--</div>-->
    </div><!--row-->

    <div class="row">
             <div class="col-md-8 mgbt-md-20 mgbt-lg-0">
            <div class="panel vd_interactive-widget light-widget widget">
        <div class="panel-body-list">

        <div class="pd-20">
            <h5 class="mgbt-xs-20 mgtp-20"><span class="menu-icon append-icon"><i class="icon-graph"></i></span> <strong>Transactions</strong></h5>
            <div id="trnx-bar-chart" style="height:255px; "></div>
        </div>
     
    </div>
       </div>
    <!-- Panel Widget -->              
    </div>
           <div class="col-md-4 mgbt-md-20 mgbt-lg-0">
            <div class="panel vd_interactive-widget light-widget widget">
        <div class="panel-body-list">

        <div class="pd-20">
            <h5 class="mgbt-xs-20 mgtp-20"><span class="menu-icon append-icon"><i class="icon-pie"></i></span> <strong>Customers / System Accounts</strong></h5>
            <div id="customer-pie-chart" style="height:255px; "></div>
        </div>
     
    </div>
       </div>
    <!-- Panel Widget -->              
    </div>
    
    
    
    
        <div class="col-md-6 mgbt-md-20 mgbt-lg-0">
            <div class="panel vd_interactive-widget light-widget widget">
        <div class="panel-body-list">

        <div class="pd-20">
            <h5 class="mgbt-xs-20 mgtp-20"><span class="menu-icon append-icon"><i class="icon-graph"></i></span> <strong>Loans Released Monthly</strong></h5>
            <div id="revenue-bar-chart" style="height:255px; "></div>
        </div>
     
    </div>
       </div>
    <!-- Panel Widget -->              
    </div>
      <!-- col-md-8 -->
      <div class="col-md-6">
        <div class="panel vd_transaction-widget light-widget widget">
          <div class="panel-body">

       <!-- vd_panel-menu --> 
        <h5 class="mgbt-xs-20 mgtp-20"><span class="menu-icon append-icon"> <i class="icon-graph"></i> </span> <strong>Loan Collections Monthly</strong></h5>
        
        <div id="line-chart" class="pie-chart" style="height:388px;"></div>

        </div>
   </div>
      </div>
      <!-- .col-md-4 --> 
    </div>

    <div class="row">
      <div class="col-md-7">
        <div class="row">
          <div class="col-md-12">
            <div class="tabs widget">
<ul class="nav nav-tabs widget">
  <li class="active">
  <a data-toggle="tab" href="#home-tab">
      <span class="menu-icon"><i class="fa fa-money"></i></span>
      Recent Transactions
      <span class="menu-active"><i class="fa fa-caret-up"></i></span>
  </a></li>
  <li>
  <a data-toggle="tab" href="#posts-tab">
      <span class="menu-icon"><i class="fa fa-money"></i></span>
      Deposits
      <span class="menu-active"><i class="fa fa-caret-up"></i></span>
  </a></li>
  <li>
  <a data-toggle="tab" href="#list-tab">
      <span class="menu-icon"><i class="fa fa-money"></i></span>
      Withdrawals
      <span class="menu-active"><i class="fa fa-caret-up"></i></span>
  </a></li>
  <li>
  <a data-toggle="tab" href="#utlity-tab">
      <span class="menu-icon"><i class="fa fa-money"></i></span>
      Utility Bills
      <span class="menu-active"><i class="fa fa-caret-up"></i></span>
  </a></li>
</ul>
<div class="tab-content">
  <div id="home-tab" class="tab-pane active">                                         
   <div class="content-list content-image menu-action-right">
   <div  data-rel="scroll" data-scrollheight="400">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <th>Acct Name</th>
                    <th>Acct No</th>
                <th>Trx Type</th>
                <th>Amount</th>
                <th>Trx Date</th>
                </thead>
                <tbody>
                    @foreach (\App\Models\SavingsTransaction::orderBy('created_at','DESC')->take(10)->get() as $item)
                        <tr>
                            <td>{{!empty($item->customer) ? ucwords($item->customer->last_name." ".$item->customer->first_name) : "N/A"}}</td>
                            <td>{{!empty($item->customer) ? $item->customer->acctno : "N/A"}}</td>
                            <td>{{$item->type}}</td>
                            <td>{{number_format($item->amount,2)}} </td>
                            <td>{{date("d-m-Y H:ia",strtotime($item->created_at))}} </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
       </div>
       <div class="closing text-center">
            <a href="{{route('savings.transaction')}}">See All Transactions<i class="fa fa-angle-double-right"></i></a>
       </div>                                                                                                            
   </div>                              

  </div>
  <div id="posts-tab" class="tab-pane sidebar-widget">
       <div class="content-list">	
           <div data-rel="scroll" data-scrollheight="400">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <th>Acct Name</th>
                   <th>Acct No</th>
                <th>Trx Type</th>
                <th>Amount</th>
                <th>Trx Date</th>
                </thead>
                <tbody>
                    @foreach (\App\Models\SavingsTransaction::whereIn('type',['deposit','credit'])->where('trnx_type','trnsfer')->orderBy('created_at','DESC')->take(10)->get() as $item)
                        <tr>
                            <td>{{!empty($item->customer) ? ucwords($item->customer->last_name." ".$item->customer->first_name) : "N/A"}}</td>
                            <td>{{!empty($item->customer) ? $item->customer->acctno : "N/A"}}</td>
                            <td>{{$item->type}}</td>
                            <td>{{number_format($item->amount,2)}} </td>
                            <td>{{date("d-m-Y H:ia",strtotime($item->created_at))}} </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
           </div>
           {{-- <div class="closing text-center" style="">
                <a href="#">See All Activities <i class="fa fa-angle-double-right"></i></a>
           </div>                                                                        --}}
       </div>                              
  </div>
  
  <div id="list-tab" class="tab-pane">
       <div class="content-grid column-xs-2 column-sm-6 height-xs-3">	
       <div data-rel="scroll" data-scrollheight="400">
        <table class="table table-striped table-bordered table-sm">
            <thead>
                <th>Acct Name</th>
                <th>Acct No</th>
                <th>Trx Type</th>
                <th>Amount</th>
                <th>Trx Date</th>
            </thead>
            <tbody>
                @foreach (\App\Models\SavingsTransaction::whereIn('type',['withdrawal','debit'])->where('trnx_type','trnsfer')->orderBy('created_at','DESC')->take(10)->get() as $item)
                    <tr>
                        <td>{{!empty($item->customer) ? ucwords($item->customer->last_name." ".$item->customer->first_name) : "N/A"}}</td>
                        <td>{{!empty($item->customer) ? $item->customer->acctno : "N/A"}}</td>
                        <td>{{$item->type}}</td>
                        <td>{{number_format($item->amount,2)}} </td>
                        <td>{{date("d-m-Y H:ia",strtotime($item->created_at))}} </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
           </div>
           {{-- <div class="closing text-center">
                <a href="#">See All New Users <i class="fa fa-angle-double-right"></i></a>
           </div>  --}}
       </div>      
  </div>
  
    <div id="utlity-tab" class="tab-pane">
       <div class="content-grid column-xs-2 column-sm-6 height-xs-3">	
       <div data-rel="scroll" data-scrollheight="400">
        <table class="table table-striped table-bordered table-sm">
            <thead>
                <th>Acct Name</th>
                <th>Acct No</th>
                <th>Trx Type</th>
                <th>Amount</th>
                <th>Trx Date</th>
            </thead>
            <tbody>
                @foreach (\App\Models\SavingsTransaction::where('type','debit')->where('trnx_type','utility')->orderBy('created_at','DESC')->take(10)->get() as $item)
                    <tr>
                        <td>{{!empty($item->customer) ? ucwords($item->customer->last_name." ".$item->customer->first_name) : "N/A" }}</td>
                        <td>{{!empty($item->customer) ? $item->customer->acctno : "N/A"}}</td>
                        <td>{{$item->type}}</td>
                        <td>{{number_format($item->amount,2)}} </td>
                        <td>{{date("d-m-Y H:ia",strtotime($item->created_at))}} </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
           </div>
           {{-- <div class="closing text-center">
                <a href="#">See All New Users <i class="fa fa-angle-double-right"></i></a>
           </div>  --}}
       </div>      
  </div>
</div>
</div> <!-- tabs-widget -->                  </div>
          <!-- col-md-12 --> 
        </div>
        <!-- row --> 
        
      </div>
      <!-- col-md-6 -->
      <div class="col-md-5">
        <h4>Active Loans</h4>
        <div class="panel widget">
          <div class="panel-body-list  table-responsive">
            <table class="table table-striped table-bordered table-sm">
              <thead class="vd_bg-green vd_white">
                <tr>
                    <th>Name</th>
                    <th>Pricipal</th>
                    <th>Loan Product</th>
                </tr>
              </thead>
              <tbody>
                @foreach (\App\Models\Loan::where('status','disbursed')->orderBy('created_at','DESC')->take(5)->get() as $item)
                <tr>
                    <td><a href="{{route('loan.show',['id' => $item->id])}}" title="click to view details">{{!empty($item->customer) ? ucwords($item->customer->last_name." ".$item->customer->first_name) : "N/A"}}</a></td>
                    <td class="text-center">{{number_format($item->principal)}}</td>
                    <td class="text-center">{{ $item->loan_product->name }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        <h4>Pending Loans</h4>
        <div class="panel widget">
          <div class="panel-body-list  table-responsive">
            <table class="table table-striped table-bordered table-sm">
              <thead class="vd_bg-yellow vd_white">
                <tr>
                    <th>Name</th>
                    <th>Pricipal</th>
                    <th>Loan Product</th>
                </tr>
              </thead>
              <tbody>
                @foreach (\App\Models\Loan::where('status','pending')->orderBy('created_at','DESC')->take(5)->get() as $item)
                <tr>
                    <td><a href="{{route('loan.show',['id' => $item->id])}}" title="click to view details">{{!empty($item->customer) ? ucwords($item->customer->last_name." ".$item->customer->first_name) : "N/A"}}</a></td>
                    <td class="text-center">{{number_format($item->principal)}}</td>
                    <td class="text-center">{{ $item->loan_product->name }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- col-md-6 --> 
      
    </div>
    <!-- .row --> 
  </div>
  <!-- .vd_content-section --> 
@endsection
@section('scripts')
<script>
    Morris.Bar({
	  element: 'trnx-bar-chart',
	  data: [
      {!!$transactions!!}
    ],
	  xkey: 'month',
	  ykeys: ['total','success','failed'],
	  labels: ['total','success','failed'],
	  barColors: ["#8c00ff","#00a65a","#ff0000"]
	});
	
    Morris.Bar({
	  element: 'revenue-bar-chart',
	  data: [
      {!!$loans_released_monthly!!}
    ],
	  xkey: 'month',
	  ykeys: ['principal'],
	  labels: ['principal'],
	  barColors: ["#00a65a","#ff0000"]
	});

    Morris.Bar({
	  element: 'line-chart',
	  data: [
      {!!$loan_collections_monthly!!}
    ],
	  xkey: 'month',
	  ykeys: ['amount'],
	  labels: ['amount'],
	  barColors: ["#F85D2C"]
	});
	
	Morris.Donut({
  element: 'customer-pie-chart',
  resize: true,
  colors: ["#00a65a","#F89C2C","#f56954","#ff0000","#8c00ff","#d4c250","#823676"],
  data: [
    {label: "Active Accounts", value: {!!$active_accounts!!}},
    {label: "Pending Accounts", value: {!!$pending_accounts!!}},
    {label: "Closed Accounts", value: {!!$closed_accounts!!}},
    {label: "Domant Accounts", value: {!!$domant_accounts!!}},
    {label: "System Users", value: {!!$sysusers!!}},
    {label: "Saving Account", value: {!!$savingacct!!}},
    {label: "Current Account", value: {!!$currentacct!!}}
  ]
});
  
</script>
@endsection