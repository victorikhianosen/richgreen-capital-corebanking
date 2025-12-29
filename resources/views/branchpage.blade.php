<!DOCTYPE html>
<head>
    <meta charset="utf-8" />
    <title class="noprint">BanqPro - Institutions</title>
    <meta name="keywords" content="HTML5 Template, CSS3, All Purpose Admin Template, " />
    <meta name="description" content="Responsive Admin Template for e-commerce dashboard">
    <meta name="author" content="Venmond">
    
    <!-- Set the viewport width to device width for mobile -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    
    
    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="img/ico/apple-touch-icon-144-precomposed.html">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{{asset('img/ico/apple-touch-icon-114-precomposed.png')}}">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{{asset('img/ico/apple-touch-icon-72-precomposed.png')}}">
    <link rel="apple-touch-icon-precomposed" href="{{asset('img/ico/apple-touch-icon-57-precomposed.png')}}">
    <link rel="shortcut icon" href="{{asset('img/favicon.png')}}" sizes="72x72">
    
    
    <!-- CSS -->
    @include('includes.styles')
   
</head>    
<?php
    $getsetvalue = new \App\Models\Setting();
   ?>
<body id="dashboard" class="full-layout  nav-right-hide nav-right-start-hide  nav-top-fixed  responsive    clearfix" data-active="dashboard "  data-smooth-scrolling="1">   
  <div class="container">
    <div class="row" id="advanced-input">
      <div class="col-md-3"></div>
              <div class="col-md-7" style="margin:80px auto;">
                <div class="panel widget">
                  <div class="panel-heading vd_bg-grey">
                    <h3 class="panel-title"> <span class="menu-icon"> <i class="fa fa-home"></i> </span>branches</h3>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    
                      <div class="form-group">
                        <label class="col-sm-3 control-label">Select A Branch</label>
                        <div class="col-sm-7 controls">
                            <select class="width-90 form-control" onchange="window.location.href=this.value">
                                <option selected disabled>Select A Branch</option>
                                @foreach ($getbranches as $item)
                                     <option value="{{route('dashboard')}}?branchid={{$item->id}}">{{$item->branch_name}}</option>
                                @endforeach
                               
                            </select>
                        </div>
                      </div>
                       <div class="col-md-12 col-lg-12" style="text-align: center; margin-top:10px">
                        <a class="btn btn-success" href="{{ route('users.logout') }}">
                            <span class="menu-icon"><i class=" fa fa-sign-out"></i></span>  
                            <span class="menu-text">{{ __('Logout') }}</span>
                     </a>
                      </div>
                  </div>
                </div>
                <!-- Panel Widget --> 
              </div>
              <!-- col-md-12 --> 
            </div>
            <!-- row -->
  </div>
<!-- Footer Start -->
<footer class="footer-1"  id="footer">      
  <div class="vd_bottom ">
      <div class="container">
          <div class="row">
            <div class="col-xs-12">
              <div class="copyright text-center">
                  Copyright &copy;{{date("Y")." ".$getsetvalue->getsettingskey('company_name')}} All Rights Reserved 
              </div>
            </div>
          </div><!-- row -->
      </div><!-- container -->
  </div>
</footer>
<!-- Footer END -->


</div>

<!-- .vd_body END  -->
<a id="back-top" href="#" data-action="backtop" class="vd_back-top visible"> <i class="fa  fa-angle-up"> </i> </a>

<!--
<a class="back-top" href="#" id="back-top"> <i class="icon-chevron-up icon-white"> </i> </a> -->

<!-- Javascript =============================================== --> 
<!-- Placed at the end of the document so the pages load faster --> 
@include('includes.scripts')
</body>

<!-- Mirrored from www.venmond.com/demo/vendroid/index-ecommerce.php by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 05 Apr 2017 13:56:34 GMT -->
</html>