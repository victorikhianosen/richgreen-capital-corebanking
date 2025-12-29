<div class="vd_navbar vd_nav-width vd_navbar-tabs-menu vd_navbar-left noprint">
	<div class="navbar-tabs-menu clearfix">
			<span class="expand-menu" data-action="expand-navbar-tabs-menu">
            	{{-- <span class="menu-icon menu-icon-left">
            		<i class="fa fa-ellipsis-h"></i>
                    <span class="badge vd_bg-red">
                        
                    </span>                    
                </span>
            	<span class="menu-icon menu-icon-right">
            		<i class="fa fa-ellipsis-h"></i>
                    <span class="badge vd_bg-red">
                        
                    </span>                    
                </span>                 --}}
            </span>
            <div class="menu-container">
            	<div class="vd_mega-menu-wrapper">
                	<div class="vd_mega-menu"  data-intro="<strong>Tabs Menu</strong><br/>Can be placed for dropdown menu, tabs, or user profile. Responsive for medium and small size navigation." data-step=3>
        				<div style="margin-left: 10px; padding:5px 0">
                            <h4 id="grrt"></h4>
                          <?php 
                            $branch = session()->has('branchid') ? session()->get('branchid')['bname'] : (empty(Auth::user()->branch->branch_name) ? '' : "(Branch: ".Auth::user()->branch->branch_name.")");
                            ?>
                            <p>{{ucwords(Auth::user()->last_name." ".Auth::user()->first_name)}}
                               {{!empty($branch) ? ucwords($branch) : ""}}
                          </p>
                        </div>
                    </div>                
                </div>
            </div>                                                   
    </div>
	<div class="navbar-menu clearfix noprint">
        <div class="vd_panel-menu hidden-xs">
            {{-- <span data-original-title="Expand All" data-toggle="tooltip" data-placement="bottom" data-action="expand-all" class="menu" data-intro="<strong>Expand Button</strong><br/>To expand all menu on left navigation menu." data-step=4 >
                <i class="fa fa-sort-amount-asc"></i>
            </span>                    --}}
        </div>
    	<h5 class="menu-title hide-nav-medium hide-nav-small">
    	    Loggedin as role: {{ucwords(Auth::user()->roles()->first()->name)}}
    	</h5>
        <div class="vd_menu">
            
            <input type="text" id="mySearch" class="form-control width-90" style="margin: 8px 3px" onkeyup="myFunction()" placeholder="Search...">

        	 <ul id="menu">
        	     
      @if (Auth::user()->roles()->first()->name == "super admin")
        <li>
            <a href="{{route('branchpage')}}">
                <span class="menu-icon"><i class="fa fa-reply"></i></span> 
                <span class="menu-text">SWitch Branches</span> 
               </a> 
        </li>
        @endif
    <li>
    	<a href="{{route('dashboard')}}">
        	<span class="menu-icon"><i class="fa fa-dashboard"></i></span> 
            <span class="menu-text">Dashboard</span> 
       	</a> 
    </li>  
 	
    <li>
    	<a href="javascript:void(0);" data-action="click-trigger">
        	<span class="menu-icon"><i class="fa fa-user"> </i></span> 
            <span class="menu-text">Account Officer</span>  
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>
       	</a>
     	<div class="child-menu"  data-action="click-target">
            <ul>  
                @can('manage account officer')
                  <li>
                    <a href="{{route('acofficer.index')}}">
                        <span class="menu-text">Manage Account Officers</span>  
                    </a>
                </li>  
                @endcan
                @can('create account officer')
                <li>
                    <a href="{{route('acofficer.create')}}">
                        <span class="menu-text">Add Account Officer</span>  
                    </a>
                </li> 
                @endcan
                                                                                                               
            </ul>   
      	</div>
    </li> 
    <li>
    	<a href="javascript:void(0);" data-action="click-trigger">
        	<span class="menu-icon entypo-icon"><i class="fa fa-briefcase"> </i></span> 
            <span class="menu-text">Assets</span>  
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>
       	</a>
     	<div class="child-menu"  data-action="click-target">
            <ul>  
              @can('assets')
              <li>
                <a href="{{route('assets.index')}}">
                    <span class="menu-text">Manage Asset</span>  
                </a>
            </li>   
              @endcan
              @can('create assets')
              <li>
                <a href="{{route('assets.create')}}">
                    <span class="menu-text">Add Assets</span>                                      
                </a>
            </li>  
              @endcan
              
              @can('view assets')
              <li>
                <a href="{{route('assetstyp.index')}}">
                    <span class="menu-text">Manage Asset Types</span>  
                </a>
            </li>    
              @endcan
              @can('create assets')                         
                <li>
                    <a href="{{route('assetstyp.create')}}">
                        <span class="menu-text">Add Asset Types</span>                                      
                    </a>
                </li> 
             @endcan                                                                                                                                                                                                         
            </ul>   
      	</div>
    </li>  
    
  @can('audit trail')
  <li>
    <a href="{{route('audit')}}">
        <span class="menu-icon"><i class="fa fa-book"></i></span> 
        <span class="menu-text">Audit Trail</span>  
        {{-- <span class="menu-badge"><span class="badge vd_bg-red">78</span></span> --}}
       </a> 
</li>
  @endcan       
         @if (Auth::user()->account_type == "system")
    <li>
    	<a href="javascript:void(0);"   data-action="click-trigger">
            <span class="menu-icon"><i class="fa fa-home"> </i></span>
            <span class="menu-text">Branches</span>
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>            
        </a>
        <div class="child-menu"  data-action="click-target">
            <ul>  
                @can('branches')
                <li>
                    <a href="{{route('branch.index')}}">
                        <span class="menu-text">Manage Branches</span>  
                    </a>
                </li> 
                @endcan
                @can('create branches')
                <li>
                    <a href="{{route('branch.create')}}">
                        <span class="menu-text">Add Branch</span>                                      
                    </a>
                </li> 
                @endcan                                                                                                                                                                                                      
            </ul>   
      	</div>
    </li>
@endif

  @can('wallet transaction')
    <li>
     <a href="{{route('wallet')}}">
        <span class="menu-icon"><i class="fa fa-bank"></i></span> 
         <span class="menu-text">Manage Wallet Topup</span>  
     </a>                
   </li>   
    @endcan 
    <!--<li>-->
    <!--	<a href="javascript:void(0);" data-action="click-trigger">-->
    <!--    	<span class="menu-icon"><i class="icon-list"> </i></span> -->
    <!--        <span class="menu-text">Collateral</span>  -->
    <!--        <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>-->
    <!--   	</a>-->
    <!-- 	<div class="child-menu"  data-action="click-target">-->
    <!--        <ul>  -->
    <!--            @can('collateral')-->
    <!--              <li>-->
    <!--                <a href="{{route('collatype.index')}}">-->
    <!--                    <span class="menu-text">Manage Collateral Type</span>  -->
    <!--                </a>-->
    <!--            </li>  -->
    <!--            @endcan-->
    <!--            @can('collateral')-->
    <!--            <li>-->
    <!--                <a href="{{route('colla.index')}}">-->
    <!--                    <span class="menu-text">Manage Collateral</span>  -->
    <!--                </a>-->
    <!--            </li> -->
    <!--            @endcan-->
                                                                                                               
    <!--        </ul>   -->
    <!--  	</div>-->
    <!--</li>-->
    
      
    @can('communication')
         <li>
    	<a href="javascript:void(0);" data-action="click-trigger">
        	<span class="menu-icon entypo-icon"><i class="fa fa-mobile"></i></span> 
            <span class="menu-text">Communication</span>  
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>
       	</a>
     	<div class="child-menu"  data-action="click-target">
            <ul>
                @can('communication')
                    <li>
                    <a href="{{route('emails.index')}}">
                        <span class="menu-text">Manage Email</span>  
                    </a>
                </li>      
                @endcan
                @can('create communication')
                <li>
                    <a href="{{route('emails.create')}}">
                        <span class="menu-text">Create Email</span>  
                    </a>
                </li>
                @endcan
                  @can('create communication')
                <li>
                    <a href="{{route('sms.create')}}">
                        <span class="menu-text">Create Sms</span>  
                    </a>
                </li>
                @endcan                                                                                                                                                                
            </ul>   
      	</div>
    </li> 
    @endcan
    
     @can('manage bank')
    <li>
     <a href="{{route('bank.all')}}">
        <span class="menu-icon"><i class="fa fa-bank"></i></span> 
         <span class="menu-text">Manage Banks</span>  
     </a>                
   </li>   
    @endcan 

    
    <li>
    	<a href="javascript:void(0);" data-action="click-trigger">
        	<span class="menu-icon"><i class="fa fa-briefcase"></i></span> 
            <span class="menu-text">Accounting Management</span>  
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>
       	</a>
     	<div class="child-menu"  data-action="click-target">
            <ul>
               
                   @can('general ledger')
                   <li>
                    <a href="{{route('actype')}}">
                        <span class="menu-text">Manage Account Type</span>  
                    </a>
                </li> 
                   @endcan 
                   @can('manage account category')
                   <li>
                    <a href="{{route('ac.category.index')}}">
                        <span class="menu-text">Manage Account Categories</span>  
                    </a>
                </li>  
                @endcan 
                @can('upload account category')
                   <li>
                    <a href="{{route('ac.category.batchupload')}}?type=ac">
                        <span class="menu-text">Batch upload Account Categories</span>  
                    </a>
                </li> 
                @endcan 
                @can('capital')
                <li>
                    <a href="{{route('capital.index')}}">
                        <span class="menu-text">Manage Capital</span>  
                    </a>
                </li>  
                @endcan 
                @can('general ledger')
                <li>
                 <a href="{{route('gl.index')}}">
                     <span class="menu-text">Manage General Ledger</span>  
                 </a>
             </li> 
             @endcan 
             @can('general ledger') 
             <li>
                <a href="{{route('funds.gl')}}">
                    <span class="menu-text">Fund General Ledger</span>  
                </a>
            </li>
            @endcan 
            
             @can('general ledger') 
             <li>
                <a href="{{route('manage.gltrx')}}">
                    <span class="menu-text">Manage General Ledger Transactions</span>  
                </a>
            </li>
            @endcan 
            
            @can('upload general ledger')
                <li>
                 <a href="{{route('ac.category.batchupload')}}?type=gl">
                     <span class="menu-text">Batch Upload General Ledger</span>  
                 </a>
             </li>
             @endcan 
             @can('general ledger posting')
             <li>
                <a href="{{route('gl.customerposting')}}?options=gltogl">
                    <span class="menu-text">GL to GL Posting</span>  
                </a>
            </li>  
            @endcan 
            @can('general ledger posting')
                <li>
                 <a href="{{route('gl.customerposting')}}?options=glc">
                     <span class="menu-text">GL to Customer Posting</span>  
                 </a>
             </li> 
             
               @endcan  
             @can('general ledger posting')
                <li>
                 <a href="{{route('gl.customerposting')}}?options=cgl">
                     <span class="menu-text">Customer to GL Posting</span>  
                 </a>
             </li> 
             
                <li>
                 <a href="{{route('gl.reversal')}}">
                     <span class="menu-text">General Ledger Reversal</span>  
                 </a>
             </li>
               @endcan  
             @can('general ledger posting')
                <li>
                 <a href="{{route('ac.category.batchupload')}}?type=gltogl">
                     <span class="menu-text">GL to GL Batch Upload</span>  
                 </a>
             </li> 
               @endcan  
             @can('general ledger posting')
                <li>
                 <a href="{{route('ac.category.batchupload')}}?type=glc">
                     <span class="menu-text">GL to Customer Batch Upload</span>  
                 </a>
             </li> 
               @endcan 
             @can('general ledger posting') 
                <li>
                 <a href="{{route('ac.category.batchupload')}}?type=cgl">
                     <span class="menu-text">Customer to GL Batch Upload</span>  
                 </a>
             </li> 
               @endcan  
             @can('general ledger posting')
                <li>
                 <a href="{{route('vault-till-posting')}}?options=vtp">
                     <span class="menu-text">Vault to Till Posting</span>  
                 </a>
             </li>
               @endcan 
             @can('general ledger posting')
                <li>
                 <a href="{{route('vault-till-posting')}}?options=tvp">
                     <span class="menu-text">Till to Vault Posting</span>  
                 </a>
             </li>
               @endcan 
             @can('general ledger posting')
                <li>
                 <a href="{{route('vault-till-posting')}}?options=tcp">
                     <span class="menu-text">Till to Customer(Deposit)</span>  
                 </a>
             </li> 
               @endcan  
             @can('general ledger posting')
                <li>
                 <a href="{{route('vault-till-posting')}}?options=ctp">
                     <span class="menu-text">Customer to Till(withdrawal)</span>  
                 </a>
             </li> 
               @endcan      
            </ul>   
      	</div>
    </li>
    
     <li>
    	<a href="javascript:void(0);" data-action="click-trigger">
        	<span class="menu-icon"><i class="fa fa-users"></i></span> 
            <span class="menu-text">Customer Management</span>  
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>
       	</a>
     	<div class="child-menu"  data-action="click-target">
            <ul>
                @can('customer')
                <li>
                    <a href="{{route('customer.index')}}">
                        <span class="menu-text">Manage Customers &nbsp;<span class="badge vd_bg-green" id="activec"></span></span>  
                    </a>
                </li>

                <li>
                    <a href="{{route('customer.search')}}">
                        <span class="menu-text">Search Customers </span>  
                    </a>
                </li>
                @endcan
                @can('create customer')              
                <li>
                    <a href="{{route('customer.create')}}">
                        <span class="menu-text">Add New Customer</span>  
                    </a>
                </li> 
                @endcan
                {{-- <li>
                    <a href="#">
                        <span class="menu-text">Add Customer Group</span>  
                    </a>
                </li> --}}
                @can('view customer') 
                <li>
                    <a href="{{route('customer.restr')}}">
                        <span class="menu-text">Manage Restricted Customers &nbsp;<span class="badge vd_bg-red" id="restr"></span></span>  
                    </a>
                </li>
                <li>
                    <a href="{{route('customer.dom')}}">
                        <span class="menu-text">Manage Dom Accounts &nbsp;<span class="badge vd_bg-red" id="domac"></span></span>  
                    </a>
                </li>
                <li>
                    <a href="{{route('customer.closed')}}">
                        <span class="menu-text">Manage Closed Customers &nbsp;<span class="badge vd_bg-red" id="closedc"></span></span>  
                    </a>
                </li>
                <li>
                    <a href="{{route('customer.pending')}}">
                        <span class="menu-text">Manage Pending Customers &nbsp;<span class="badge vd_bg-yellow" id="pendc"></span></span>  
                    </a>
                </li>                
             @endcan                                                                                                                                        
            </ul>   
      	</div>
    </li>
    
   
     <li>
    	<a href="javascript:void(0);" data-action="click-trigger">
        	<span class="menu-icon"><i class="fa fa-money"></i></span> 
            <span class="menu-text">Savings Management</span>  
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>
       	</a>
     	<div class="child-menu"  data-action="click-target">
            <ul>
                 @can('manage savings products')
                <li>
                    <a href="{{route('savings.product')}}">
                        <span class="menu-text">Manage Savings Product</span>  
                    </a>
                </li>
                @endcan
                @can('manage savings fees')
                <li>
                    <a href="{{route('savings.fee')}}">
                        <span class="menu-text">Manage Savings Fees</span>  
                    </a>
                </li>
                @endcan
                
                @can('view savings transaction')
                <li>
                    <a href="{{route('savings.transaction')}}">
                        <span class="menu-text">Manage Savings Transactions</span>  
                    </a>
                </li>
                @endcan
                @can('view savings')
                <li>
                    <a href="{{route('savings.accounts')}}?ac_type=Savings Account">
                        <span class="menu-text">Manage Savings Accounts</span>  
                    </a>
                </li>
                @endcan
                
                @can('view current')
                <li>
                    <a href="{{route('savings.accounts')}}?ac_type=Current Account">
                        <span class="menu-text">Manage Current Accounts</span>  
                    </a>
                </li>
                @endcan
                @can('view current')
                <li>
                    <a href="{{route('savings.accounts')}}?ac_type=domicilary account">
                        <span class="menu-text">Manage Domicilary Accounts</span>  
                    </a>
                </li>
                @endcan

                @can('view savings transaction')
                <li>
                    <a href="{{route('savings.cutomers.balance')}}">
                        <span class="menu-text">Manage Customers Balance/Statement </span>  
                    </a>
                </li>
                @endcan
            </ul>   
      	</div>
    </li>
    
    <li>
    	<a href="javascript:void(0);" data-action="click-trigger">
        	<span class="menu-icon"><i class="fa fa-bank"></i></span> 
            <span class="menu-text">Posting Management</span>  
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>
       	</a>
     	<div class="child-menu"  data-action="click-target">
            <ul>
                
                 @can('fund approval')
                <li>
                    <a href="{{route('approvdata')}}">
                        <span class="menu-text">Account Transaction Approval</span>  
                    </a>
                </li>
                <li>
                    <a href="{{route('glapprovdata')}}">
                        <span class="menu-text">GL Transaction Approval</span>  
                    </a>
                </li>
                @endcan 
                
                @can('savings transactions post deposit')
                <li>
                    <a href="{{route('savings.transfer-funds')}}">
                        <span class="menu-text">Account Transfer</span>  
                    </a>
                </li>
                @endcan                                                                                                                                    
                           
                 @can('savings transaction withdrawal')
                <li>
                    <a href="{{route('savings.bank.transactions')}}">
                        <span class="menu-text">Bank Transfer</span>  
                    </a>
                </li>
                @endcan 
                
                @can('savings transactions post deposit')
                <li>
                    <a href="{{route('savings.create.transactions')}}?trx_type=deposit&initial=d">
                        <span class="menu-text">Deposit Posting</span>  
                    </a>
                </li>
                @endcan                                                                                                                                    
                                                                                                                                                   
               
                @can('savings transactions charges')
                <li>
                    <a href="{{route('charges.posting.create')}}?trx_type=charge posting&initial=cp">
                        <span class="menu-text">Charge Posting</span>  
                    </a>
                </li>
                @endcan                                                                                                                                    
                @can('savings transactions reversal')
                <li>
                    <a href="{{route('savings.create.transactions')}}?trx_type=reversal">
                        <span class="menu-text">Reversal Posting</span>  
                    </a>
                </li>
                @endcan                                                                                                                                    
                @can('savings transaction withdrawal')
                <li>
                    <a href="{{route('savings.create.transactions')}}?trx_type=withdrawal&initial=w">
                        <span class="menu-text">Withdrawal Posting</span>  
                    </a>
                </li>
                @endcan                                                                                                                                    
                {{-- @can('savings transaction charge')
                <li>
                    <a href="">
                        <span class="menu-text">Upload Charge</span>  
                    </a>
                </li>
                @endcan--}}
                @can('uploads')
                <li>
                    <a href="{{route('uploadtrxpg')}}">
                        <span class="menu-text">Uploads Transactions</span>  
                    </a>
                </li>
                @endcan                                                                                                                                    
                @can('manage charges')
                <li>
                    <a href="{{route('charges.index')}}">
                        <span class="menu-text">Manage Charges</span>  
                    </a>
                </li>
                @endcan  
                 @can('overdraft')
                <li>
                    <a href="{{route('overdraft')}}">
                        <span class="menu-text">Overdraft Posting</span>  
                    </a>
                </li>
                @endcan
            </ul>   
      	</div>
    </li>

    
    <li>
    	<a href="javascript:void(0);" data-action="click-trigger">
        	<span class="menu-icon"><i class="fa fa-money"></i></span> 
            <span class="menu-text">Investment Management</span>  
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>
       	</a>
     	<div class="child-menu"  data-action="click-target">
            <ul>

            @can('dashboard fixed deposit')
                <li>
                    <a href="{{route('manage.fd')}}">
                        <span class="menu-text">Manage Fixed Deposit</span>  
                    </a>
                </li>
                
                <li>
                    <a href="{{route('due.fd')}}">
                        <span class="menu-text">Manage Due Fixed Deposit</span>  
                    </a>
                </li>
                
                <li>
                    <a href="{{route('fd.search')}}">
                        <span class="menu-text">Search Fixed Deposit</span>  
                    </a>
                </li>
             @endcan 
                
            @can('create fixed deposit')
                <li>
                    <a href="{{route('create.fd')}}">
                        <span class="menu-text">Add Fixed Deposit</span>  
                    </a>
                </li>
                @endcan 

            @can('fixed deposit product') 
            <li>
                <a href="{{route('manage.fdproduct')}}">
                    <span class="menu-text">Fixed Deposit Products</span>  
                </a>
            </li>
            @endcan

                @can('dashboard fixed deposit')           
                    <li>
                        <a href="{{route('manage.fd')}}?status=approved">
                            <span class="menu-text">Active Fixed Deposit</span>  
                        </a>                
                    </li> 
                    @endcan
                    @can('dashboard fixed deposit')           
                    <li>
                        <a href="{{route('manage.fd')}}?status=pending">
                            <span class="menu-text">Pending Fixed Deposit</span>  
                        </a>                
                    </li> 
                    @endcan
                    
                    @can('dashboard fixed deposit')              
                    <li>
                        <a href="{{route('manage.fd')}}?status=closed">
                            <span class="menu-text">Closed Fixed Deposit</span>  
                        </a>                
                    </li> 
                    @endcan          
                    
                       @can('fixed deposit liquidation')  
                    <li>
                        <a href="{{route('liqfd')}}">
                            <span class="menu-text">Fixed Deposit Liquidation</span>  
                        </a>                
                    </li>   
                    @endcan
            </ul>   
      	</div>
    </li>
    
     <li>
    	<a href="javascript:void(0);" data-action="click-trigger">
        	<span class="menu-icon"><i class="fa fa-money"></i></span> 
            <span class="menu-text">Fx Management</span>  
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>
       	</a>
     	<div class="child-menu"  data-action="click-target">
            <ul>
                @can('manage fx') 
                <li>
                    <a href="{{route('managefx.sales')}}">
                        <span class="menu-text">Manage Fx Sales</span>  
                    </a>
                </li>
                @endcan
                @can('fx sales') 
                <li>
                    <a href="{{route('fx_sales.create')}}">
                        <span class="menu-text">Create Fx Sales</span>  
                    </a>
                </li>
                 @endcan

                 @can('manage fx') 
                    <li>
                        <a href="{{route('managefx.purchase')}}">
                            <span class="menu-text"> Manage Fx Purchase</span>  
                        </a>                
                    </li> 
                    @endcan 
                    @can('fx purchase') 
                    <li>
                        <a href="{{route('fx_purchase.create')}}">
                            <span class="menu-text"> Create Fx Purchase</span>  
                        </a>                
                    </li> 
                @endcan              
                 @can('fx reversal') 
                    <li>
                        <a href="{{route('fx_reversal')}}?fxrevtype=purchase">
                            <span class="menu-text">Fx Purchase Reversal</span>  
                        </a>                
                    </li> 
                    <li>
                        <a href="{{route('fx_reversal')}}?fxrevtype=sales">
                            <span class="menu-text">Fx Sales Reversal</span>  
                        </a>                
                    </li> 
                @endcan         
                @can('manage rate') 
                <li>
                    <a href="{{route('fxrate.all')}}">
                        <span class="menu-text">Exchange Rates</span>  
                    </a>
                </li>
               @endcan     
            </ul>   
      	</div>
    </li>

    <li>
    	<a href="javascript:void(0);" data-action="click-trigger">
        	<span class="menu-icon"><i class="fa fa-money"></i></span> 
            <span class="menu-text">Loan Management</span>  
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>
       	</a>
     	<div class="child-menu"  data-action="click-target">
            <ul>
                @can('loans')
                <li>
                    <a href="{{route('loan.index')}}">
                        <span class="menu-text">Manage Loans &nbsp;<span class="badge vd_bg-blue" id="alloans"></span></span>  
                    </a>
                </li>

                <li>
                    <a href="{{route('loan.search')}}">
                        <span class="menu-text">Search Loan </span>  
                    </a>
                </li>
                @endcan
                @can('loan products')
                <li>
                    <a href="{{route('loan.product.index')}}">
                        <span class="menu-text">Manage Loan Products</span>  
                    </a>
                </li>
                @endcan
                @can('loan fees')
                <li>
                    <a href="{{route('loan.fee.index')}}">
                        <span class="menu-text">Manage Loan Fees</span>  
                    </a>
                </li>
                @endcan
                @can('repayments')
                <li>
                    <a href="{{route('repay.index')}}">
                        <span class="menu-text">Manage Loan Repayments</span>  
                    </a>
                </li>
                @endcan
                @can('view loans')
                {{-- <li>
                    <a href="#">
                        <span class="menu-text">view Loan Applications</span>  
                    </a>
                </li>  --}}

                <li>
                    <a href="{{route('loan.statement')}}">
                        <span class="menu-text">Generate Loans Statement</span>  
                    </a>
                </li> 

                <li>
                    <a href="{{route('loan.outsnt')}}">
                        <span class="menu-text">Outstanding Loans</span>  
                    </a>
                </li> 
                @endcan                                                                                                                                   
                {{-- @can('loan fees')
                <li>
                    <a href="{{route('customer.index')}}">
                        <span class="menu-text">Manage Loan Fees</span>  
                    </a>
                </li> 
                @endcan --}}
                @can('use loan calculator')
                <li>
                    <a href="{{route('lcalcu')}}">
                        <span class="menu-text">Loan Calculator</span>  
                    </a>
                </li> 
                @endcan                                                                                                                                   
                @can('disburse loans')
                <li>
                    <a href="{{route('loan.index')}}?status=approved">
                        <span class="menu-text">Awaiting Disbursement &nbsp;<span class="badge vd_bg-yellow" id="awdis"></span></span>  
                    </a>
                </li> 
                @endcan                                                                                                                                   
                @can('loans declined')
                <li>
                    <a href="{{route('loan.index')}}?status=declined">
                        <span class="menu-text">Loans Declined &nbsp;<span class="badge vd_bg-red" id="decln"></span></span>  
                    </a>
                </li> 
                @endcan                                                                                                                                   
                @can('loans withdrawn')
                <li>
                    <a href="{{route('loan.index')}}?status=withdrawn">
                        <span class="menu-text">Loans Withdrawn &nbsp;<span class="badge vd_bg-linkedin" id="wdrw"></span></span>  
                    </a>
                </li> 
                @endcan                                                                                                                                   
                @can('loans written off')
                <li>
                    <a href="{{route('loan.index')}}?status=written_off">
                        <span class="menu-text">Loans Written Off &nbsp;<span class="badge vd_bg-grey" id="wrtoff"></span></span>  
                    </a>
                </li> 
                @endcan                                                                                                                                   
                @can('loans closed')
                <li>
                    <a href="{{route('loan.index')}}?status=closed">
                        <span class="menu-text">Loans Closed &nbsp;<span class="badge vd_bg-green" id="closed"></span></span>  
                    </a>
                </li> 
                @endcan                                                                                                                                   
                @can('total disbursed loans')
                <li>
                    <a href="{{route('loan.index')}}?status=disbursed">
                        <span class="menu-text">Loans Disbursed &nbsp;<span class="badge vd_bg-green" id="ldisb"></span></span>  
                    </a>
                </li> 
                @endcan                                                                                                                                   
                @can('loans rescheduled')
                <li>
                    <a href="{{route('loan.index')}}?status=pending_reschedule">
                        <span class="menu-text">Pending Reschedule &nbsp;<span class="badge vd_bg-red" id="pdrsc"></span></span>  
                    </a>
                </li> 
                @endcan                                                                                                                                   
                @can('total loans pending')
                <li>
                    <a href="{{route('loan.index')}}?status=pending">
                        <span class="menu-text">Pending Approval &nbsp;<span class="badge vd_bg-googleplus" id="pedappl"></span></span>  
                    </a>
                </li> 
                @endcan                                                                                                                                   
                                                                                                                                              
            </ul>   
      	</div>
    </li>

     <li>
    	<a href="javascript:void(0);" data-action="click-trigger">
        	<span class="menu-icon"><i class="fa fa-bank"></i></span> 
            <span class="menu-text">Payroll Management</span>  
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>
       	</a>
     	<div class="child-menu"  data-action="click-target">
            <ul>
                 @can('payroll')
                <li>
                    <a href="{{route('payroll.index')}}">
                        <span class="menu-text">Manage Payroll</span>  
                    </a>
                </li>
                @endcan
                @can('create payroll')
                <li>
                    <a href="{{route('payroll.create')}}">
                        <span class="menu-text">Add Payroll</span>  
                    </a>
                </li>
                @endcan
                @can('create payroll')
                <li>
                    <a href="{{route('payslip.generate')}}">
                        <span class="menu-text">Generate Payslips</span>  
                    </a>
                </li>
                @endcan
                @can('create payroll')
                <li>
                    <a href="{{route('payment.structure')}}">
                        <span class="menu-text">Manage Payment Structure</span>  
                    </a>
                </li>
                @endcan
                @can('create payroll')
                <li>
                    <a href="{{route('payroll.create.template')}}">
                        <span class="menu-text">Manage Payroll Template</span>  
                    </a>
                </li>
                @endcan
                                                                                                                                                       
            </ul>   
      	</div>
    </li>
    
   
        <li>
    	<a href="javascript:void(0);" data-action="click-trigger">
        	<span class="menu-icon"><i class="fa fa-money"></i></span> 
            <span class="menu-text">Expenses Management</span>  
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>
       	</a>
     	<div class="child-menu"  data-action="click-target">
            <ul>
                @can('expenses')
                <li>
                    <a href="{{route('expenses.index')}}">
                        <span class="menu-text">Manage Expenses</span>  
                    </a>
                </li>
               @endcan
                @can('create expenses')
                <li>
                    <a href="{{route('expenses.create')}}">
                        <span class="menu-text">Add Expense</span>  
                    </a>
                </li> 
                @endcan
                @can('expenses')
                <li>
                    <a href="{{route('expensestyp.index')}}">
                        <span class="menu-text"> Manage Expense Types</span>  
                    </a>                
                </li>
                @endcan                                                                                 
                @can('create expenses')
                <li>
                    <a href="{{route('expensestyp.create')}}">
                        <span class="menu-text">Add Expense Types</span>  
                    </a>
                </li> 
                @endcan                
            </ul>   
      	</div>
    </li>

    <li>
    	<a href="javascript:void(0);" data-action="click-trigger">
        	<span class="menu-icon"><i class="fa fa-signal"></i></span> 
            <span class="menu-text">Other Income</span>  
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>
       	</a>
     	<div class="child-menu"  data-action="click-target">
            <ul>
                @can('other income')
                <li>
                    <a href="{{route('income.index')}}">
                        <span class="menu-text">Manage Other Income</span>  
                    </a>
                </li>
               @endcan
                @can('create other income')
                <li>
                    <a href="{{route('income.create')}}">
                        <span class="menu-text">Add Other Income</span>  
                    </a>
                </li> 
                @endcan
                @can('other income')
                <li>
                    <a href="{{route('incometyp.index')}}">
                        <span class="menu-text"> Manage Other Income Types</span>  
                    </a>                
                </li>
                @endcan                                                                                 
                @can('create other income')
                <li>
                    <a href="{{route('incometyp.create')}}">
                        <span class="menu-text">Add Other Income Types</span>  
                    </a>
                </li> 
                @endcan                
            </ul>   
      	</div>
    </li>

<li>
    	<a href="javascript:void(0);" data-action="click-trigger">
        	<span class="menu-icon"><i class="fa fa-bar-chart-o"></i></span> 
            <span class="menu-text">Report Management</span>  
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>
       	</a>
     	<div class="child-menu"  data-action="click-target">
     	    @can('reports')
            <ul>
                
                {{-- <li>
                    <a href="{{route('report.balancesheet')}}?bsheettyp=1">
                        <span class="menu-text">Balance Sheet</span>  
                    </a>
                </li> --}}
                <li>
                    <a href="{{route('report.balancesheet')}}?bsheettyp=2">
                        <span class="menu-text">Balance Sheet</span>  
                    </a>
                </li>
               
                <li>
                    <a href="{{route('report.trialbalance')}}">
                        <span class="menu-text">Trial Balance</span>  
                    </a>
                </li>

                <li>
                    <a href="{{route('report.callover')}}?callovertype=1">
                        <span class="menu-text">Call Over</span>  
                    </a>
                </li>
                
                 <li>
                    <a href="{{route('report.callover')}}?callovertype=2">
                        <span class="menu-text">Call Over(2.0) </span>  
                    </a>
                </li>
               
                <li>
                    <a href="{{route('report.cashflow')}}">
                        <span class="menu-text">Cash Flow</span>  
                    </a>
                </li>
               
                <li>
                    <a href="{{route('report.refsearch')}}">
                        <span class="menu-text">Reference Search</span>  
                    </a>
                </li>
               
                <li>
                    <a href="{{route('report.collproject')}}">
                        <span class="menu-text">Collection Projection</span>  
                    </a>
                </li>
                
                <li>
                    <a href="{{route('report.collreport')}}">
                        <span class="menu-text">Collection Report</span>  
                    </a>
                </li>
                
                
               <li>
                    <a href="{{route('report.cbnreport')}}">
                        <span class="menu-text">CBN Report</span>  
                    </a>
                </li>
                <li>
                    <a href="{{route('report.customerstatement')}}">
                        <span class="menu-text">Generate Customer Statement</span>  
                    </a>
                </li>
               
               <li>
                    <a href="{{route('report.inward')}}">
                        <span class="menu-text">Inward Transactions</span>  
                    </a>
                </li>
                
                 <li>
                    <a href="{{route('tsq')}}">
                        <span class="menu-text">Transaction Status Query</span>  
                    </a>
                </li>
                
                <li>
                    <a href="{{route('reportfxmgt')}}">
                        <span class="menu-text">FX Mgmt Report</span>  
                    </a>
                </li>
                
                
                <li>
                    <a href="{{route('report.postingapp')}}">
                        <span class="menu-text">Posting Approval Report</span>  
                    </a>
                </li>
               
                {{-- <li>
                    <a href="{{route('report.profitloss')}}?prfltype=1">
                        <span class="menu-text">Profit/Loss Report</span>  
                    </a>
                </li> --}}
               
               <li>
                    <a href="{{route('report.profitloss')}}?prfltype=2">
                        <span class="menu-text">Profit/Loss Report</span>  
                    </a>
                </li>
                
                <li>
                    <a href="{{route('report.loanbal')}}">
                        <span class="menu-text">Loan Balance</span>  
                    </a>
                </li>
              
                <li>
                    <a href="{{route('report.loanclasfi')}}">
                        <span class="menu-text">Loan Classification</span>  
                    </a>
                </li>
               
                <li>
                    <a href="{{route('report.loanlist')}}">
                        <span class="menu-text">Loan List</span>  
                    </a>
                </li>
                
                 <li>
                    <a href="{{route('savingbalance_report')}}">
                        <span class="menu-text">Saving Balance Report</span>  
                    </a>
                </li>
               
                <li>
                    <a href="{{route('reportfixdp')}}">
                        <span class="menu-text">Fixed Deposit Report</span>  
                    </a>
                </li>
                
                
                <li>
                    <a href="{{route('report.loanrepayrept')}}">
                        <span class="menu-text">Expected Repayment Report</span>  
                    </a>
                </li>
               
                <li>
                    <a href="{{route('report.customerbal')}}">
                        <span class="menu-text">Customer Balance</span>  
                    </a>
                </li>
               
                <li>
                    <a href="{{route('report.customerdetail')}}">
                        <span class="menu-text">Customer View</span>  
                    </a>
                </li>
                
                <li>
                    <a href="{{route('report.loantrx')}}">
                        <span class="menu-text">Loan Transaction/Repayment</span>  
                    </a>
                </li>
                
               <li>
                    <a href="{{route('report.chartaccounts')}}">
                        <span class="menu-text">Chart of Account Report</span>  
                    </a>
                </li>
                <li>
                    <a href="{{route('report.accountsmgmt')}}">
                        <span class="menu-text">Account Mgt report</span>  
                    </a>
                </li>
                    <li>
                    <a href="{{route('report.trnsfdata')}}">
                        <span class="menu-text">Fund Transfer Report</span>  
                    </a>
                </li>
                <li>
                    <a href="{{route('report.utilitydata')}}">
                        <span class="menu-text">Vendor Trnx Report</span>  
                    </a>
                </li>                                                                                                                                     
            </ul>  
            @endcan 
      	</div>
    </li>
    
        <li>
    	<a href="javascript:void(0);" data-action="click-trigger">
        	<span class="menu-icon"><i class="fa fa-users"></i></span> 
            <span class="menu-text">Users</span>  
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>
       	</a>
     	<div class="child-menu"  data-action="click-target">
            <ul>
                @can('manage users')
                    <li>
                    <a href="{{route('user.all')}}">
                        <span class="menu-text">Manage Users</span>  
                    </a>
                </li> 
                @endcan
               @can('create users')
               <li>
                <a href="{{route('user.create')}}">
                    <span class="menu-text">Add Users</span>  
                </a>                
            </li>   
               @endcan            
                         
              @can('manage roles')
              <li>
                <a href="{{route('roles')}}">
                    <span class="menu-text">Manage Roles</span>  
                </a>                
            </li> 
              @endcan                                                                                                
                @can('manage permissions')
                <li>
                    <a href="{{route('permissions.all')}}">
                        <span class="menu-text">Manage Permissions</span>  
                    </a>                
                </li> 
                @endcan                                                                                                
                @can('manage ipwhitelist')
                <li>
                    <a href="{{route('manage.ipaddress')}}">
                        <span class="menu-text">Manage Ip Whitelist</span>  
                    </a>                
                </li> 
                @endcan                                                                                                
                                                                                                               
            </ul>   
      	</div>
    </li> 

    @can('subcription payment')
    <li>
    	<a href="javascript:void(0);" data-action="click-trigger">
        	<span class="menu-icon"><i class="fa fa-money"></i></span> 
            <span class="menu-text">Billing</span>  
            <span class="menu-badge"><span class="badge vd_bg-black-30"><i class="fa fa-angle-down"></i></span></span>
       	</a>
     	<div class="child-menu"  data-action="click-target">
            <ul>
                
                <li>
                    <a href="{{route('makesubcriptionpayment')}}">
                        <span class="menu-text">Make Subcription Payment</span>  
                    </a>
                </li>
              
                <li>
                    <a href="{{route('viewsubcription')}}">
                        <span class="menu-text">Manage Subcription Payment</span>  
                    </a>
                </li>
                    @if(Auth::user()->account_type == 'system')
                    <li>
                        <a href="{{route('subcriptinplan')}}">
                            <span class="menu-text"> Manage Subcription Plan</span>  
                        </a>                
                    </li>
                    @endif                
            </ul>   
      	</div>
    </li>
    @endcan

   

    @can('settings')
    <li>
      <a href="{{route('setting')}}">
          <span class="menu-icon"><i class="fa fa-cogs"></i></span> 
          <span class="menu-text">Settings</span>  
         </a> 
  </li>
    @endcan 
    <li>
        <a class="dropdown-item" href="{{ route('users.logout') }}" >
                <span class="menu-icon"><i class=" fa fa-sign-out"></i></span>  
                <span class="menu-text">{{ __('Logout') }}</span>
         </a>
    </li>                  
</ul>
<!-- Head menu search form ends -->         </div>             
    </div>
    <div class="navbar-spacing clearfix">
    </div>
    <!--<div class="vd_menu vd_navbar-bottom-widget noprint">-->
    <!--    <ul>-->
    <!--        <li>-->
    <!--            <a class="dropdown-item" href="{{ route('users.logout') }}" >-->
    <!--                <span class="menu-icon"><i class=" fa fa-sign-out"></i></span>  -->
    <!--                <span class="menu-text">{{ __('Logout') }}</span>-->
    <!--         </a>-->
    <!--        </li>-->
    <!--    </ul>-->
    <!--</div>     -->
</div>