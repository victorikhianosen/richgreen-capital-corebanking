<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title')</title>
 <!-- CSS -->
 @include('includes.styles')
    </head>
        <body id="pages" class="full-layout no-nav-left no-nav-right  nav-top-fixed background-login     responsive remove-navbar login-layout   clearfix" data-active="pages "  data-smooth-scrolling="1">     
            <div class="vd_body">
            <!-- Header Start -->
            
            <!-- Header Ends --> 
            <div class="content">
              <div class="container"> 
                
                <!-- Middle Content Start -->
                <?php
    $getsetvalue = new \App\Models\Setting();
   ?>
                <div class="vd_content-wrapper">
                  <div class="vd_container">
                    <div class="vd_content clearfix">
                      <div class="vd_content-section clearfix">
                        <div class="vd_register-page">
                          <div class="heading clearfix">
                            <center>
                              <h3><img src="{{asset($getsetvalue->getsettingskey('company_logo'))}}" class="img-responsive" width="120" alt="logo"></h3>
                            </center>
                          </div>
                          <div class="panel widget">
                            <div class="panel-body">
                              <div class="login-icon"> <i class="fa fa-cog"></i> </div>
                              <h1 class="font-semibold text-center" style="font-size:52px">@yield('code') ERROR</h1>
                              <form class="form-horizontal" action="#" role="form">
                                <div class="form-group">
                                  <div class="col-md-12">
                                    <h4 class="text-center mgbt-xs-20">@yield('message')</h4>
                                    <p class="text-center"> Please <a href="{{route('dashboard')}}">click here</a> to go back home</p>
                                    <div class="vd_input-wrapper" id="email-input-wrapper"> <span class="menu-icon"> <i class="fa fa-search"></i> </span>
                                      <input type="text" placeholder="Search Here" class="width-80">
                                    </div>
                                  </div>
                                </div>
                                <div id="vd_login-error" class="alert alert-danger hidden"><i class="fa fa-exclamation-circle fa-fw"></i> Please fill the necessary field </div>
                              </form>
                            </div>
                          </div>
                          {{-- <!-- Panel Widget -->
                          <div class="register-panel text-center font-semibold"> <a href="#">Home</a> <span class="mgl-10 mgr-10 vd_soft-grey">|</span> <a href="#">About</a> <span class="mgl-10 mgr-10 vd_soft-grey">|</span> <a href="#">FAQ</a> <span class="mgl-10 mgr-10 vd_soft-grey">|</span> <a href="#">Contact</a> </div> --}}
                        </div>
                        <!-- vd_login-page --> 
                        
                      </div>
                      <!-- .vd_content-section --> 
                      
                    </div>
                    <!-- .vd_content --> 
                  </div>
                  <!-- .vd_container --> 
                </div>
                <!-- .vd_content-wrapper --> 
                
                <!-- Middle Content End --> 
                
              </div>
              <!-- .container --> 
            </div>
            <!-- .content -->
            
            <!-- Footer Start -->
              <footer class="footer-2"  id="footer">      
                <div class="vd_bottom ">
                    <div class="container">
                        <div class="row">
                          <div class=" col-xs-12">
                            <div class="copyright text-center">
                                  Copyright &copy;{{date("Y")}} Mybank. All Rights Reserved 
                            </div>
                          </div>
                        </div><!-- row -->
                    </div><!-- container -->
                </div>
              </footer>
            <!-- Footer END -->
            
            </div>
            @include('includes.scripts')
    </body>
</html>
