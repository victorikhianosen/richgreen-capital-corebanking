<header class="header-1 noprint" id="header">
    <div class="vd_top-menu-wrapper">
      <div class="container ">
        <div class="vd_top-nav vd_nav-width  ">
        <div class="vd_panel-header">
          <?php
                $getsetvalue = new \App\Models\Setting();
            ?>
            <div class="logo">
              <h3><img src="{{asset($getsetvalue->getsettingskey('company_logo'))}}" class="img-responsive" width="120" alt="logo"></h3>
          </div>
          <!-- logo -->
          <div class="vd_panel-menu  hidden-sm hidden-xs" data-intro="<strong>Minimize Left Navigation</strong><br/>Toggle navigation size to medium or small size. You can set both button or one button only. See full option at documentation." data-step=1>
                                      <span class="nav-medium-button menu" data-toggle="tooltip" data-placement="bottom" data-original-title="Medium Nav Toggle" data-action="nav-left-medium">
                      <i class="fa fa-bars"></i>
                  </span>

                  <span class="nav-small-button menu" data-toggle="tooltip" data-placement="bottom" data-original-title="Small Nav Toggle" data-action="nav-left-small">
                      <i class="fa fa-ellipsis-v"></i>
                  </span>

          </div>
          <div class="vd_panel-menu left-pos visible-sm visible-xs">

                      <span class="menu" data-action="toggle-navbar-left">
                          <i class="fa fa-ellipsis-v"></i>
                      </span>


          </div>
          <div class="vd_panel-menu visible-sm visible-xs">
                  <span class="menu visible-xs" data-action="submenu">
                      <i class="fa fa-bars"></i>
                  </span>

                      <span class="menu visible-sm visible-xs" data-action="toggle-navbar-right">
                          <i class="fa fa-comments"></i>
                      </span>

          </div>
          <!-- vd_panel-menu -->
        </div>
        <!-- vd_panel-header -->

        </div>
        <div class="vd_container">
            <div class="row">
              {{-- <div class="col-sm-2 col-xs-12"></div> --}}

              <div class="col-sm-12 col-xs-12">
                    <div class="vd_mega-menu-wrapper">
                      <div class="vd_mega-menu pull-right">
                          <ul class="mega-ul">

                      @can('wallet transaction')
                      <li class="one-icon mega-li">
                        <h5 style="color:#fff; margin-left:15px; padding:5px 20px;font-weight:bold">Wallet Balance {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getsetvalue->getsettingskey('company_balance'),2)}}</h5>
                      </li>
                      @endcan

   @if (Auth::user()->roles()->first()->name == "super admin" || Auth::user()->roles()->first()->name == "managing director")
              <li class="one-icon mega-li">
                <h5 style="color:#fff; margin-left:15px; padding:5px 20px;font-weight:bold">Bank Funds {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getsetvalue->getsettingskey('bank_fund'),2)}}</h5>
              </li>
              <li class="one-icon mega-li">
                <h5 style="color:#fff; margin-left:15px; padding:5px 20px;font-weight:bold">Capital {{$getsetvalue->getsettingskey('currency_symbol')."".number_format($getsetvalue->getsettingskey('company_capital'),2)}}</h5>
              </li>
              @endif

  <li id="top-menu-2" class="one-icon mega-li">
    <a href="#" class="mega-link" data-action="click-trigger">
      <span class="mega-icon"><i class="fa fa-envelope"></i></span>
      <span class="badge vd_bg-red">10</span>
    </a>
    <div class="vd_mega-menu-content width-xs-3 width-sm-4 width-md-5 width-lg-4 right-xs left-sm" data-action="click-target">
      <div class="child-menu">
         <div class="title">
                Messages
             <div class="vd_panel-menu">
                   <span data-original-title="Message Setting" data-toggle="tooltip" data-placement="bottom" class="menu">
                      <i class="fa fa-cog"></i>
                  </span>
              </div>
         </div>
         <div class="content-list content-image">
                <div  data-rel="scroll">
             <ul class="list-wrapper pd-lr-10">
                  <li>
                          <div class="menu-icon"><img alt="example image" src="img/avatar/avatar.jpg"></div>
                          <div class="menu-text"> Do you play or follow any sports?
                              <div class="menu-info">
                                  <span class="menu-date">12 Minutes Ago </span>
                                  <span class="menu-action">
                                      <span class="menu-action-icon" data-original-title="Mark as Unread" data-toggle="tooltip" data-placement="bottom">
                                          <i class="fa fa-eye"></i>
                                      </span>
                                  </span>
                              </div>
                          </div>
                  </li>


             </ul>
             </div>
             <div class="closing text-center" style="">
                     <a href="#">See All Notifications <i class="fa fa-angle-double-right"></i></a>
             </div>
         </div>
      </div> <!-- child-menu -->
    </div>   <!-- vd_mega-menu-content -->
  </li>
  <li id="top-menu-3"  class="one-icon mega-li">
    <a href="#" class="mega-link" data-action="click-trigger">
      <span class="mega-icon"><i class="fa fa-globe"></i></span>
      <span class="badge vd_bg-red">51</span>
    </a>
    <div class="vd_mega-menu-content  width-xs-3 width-sm-4  center-xs-3 left-sm" data-action="click-target">
      <div class="child-menu">
         <div class="title">
                 Notifications
             <div class="vd_panel-menu">
                   <span data-original-title="Notification Setting" data-toggle="tooltip" data-placement="bottom" class="menu">
                      <i class="fa fa-cog"></i>
                  </span>
<!--                     <span class="text-menu" data-original-title="Settings" data-toggle="tooltip" data-placement="bottom">
                      Settings
                  </span> -->
              </div>
         </div>
         <div class="content-list">
                <div  data-rel="scroll">
             <ul  class="list-wrapper pd-lr-10">
                  <li> <a href="#">
                          <div class="menu-icon vd_yellow"><i class="fa fa-suitcase"></i></div>
                          <div class="menu-text"> Someone has give you a surprise
                              <div class="menu-info"><span class="menu-date">12 Minutes Ago</span></div>
                          </div>
                  </a> </li>

             </ul>
             </div>
             <div class="closing text-center" style="">
                     <a href="#">See All Notifications <i class="fa fa-angle-double-right"></i></a>
             </div>
         </div>
      </div> <!-- child-menu -->
    </div>   <!-- vd_mega-menu-content -->
  </li>

  <li id="top-menu-profile" class="profile mega-li">
      <a href="#" class="mega-link"  data-action="click-trigger">
            {{-- <span  class="mega-image">
                <img src="img/avatar/avatar.jpg" alt="example image" />
            </span> --}}
          <span class="mega-name">
              {{ucwords(Auth::user()->last_name." ".Auth::user()->first_name)}} <i class="fa fa-caret-down fa-fw"></i>
          </span>
      </a>
    <div class="vd_mega-menu-content  width-xs-2  left-xs left-sm" data-action="click-target">
      <div class="child-menu">
          <div class="content-list content-menu">
              <ul class="list-wrapper pd-lr-10">
                  <li> <a href="{{route('profile')}}"> <div class="menu-icon"><i class=" fa fa-user"></i></div> <div class="menu-text">Edit Profile</div> </a> </li>
                  <li> <a href="{{route('changepass')}}"> <div class="menu-icon"><i class=" fa fa-key"></i></div> <div class="menu-text">Change Password</div> </a> </li>
                  <li>
                    <a class="dropdown-item" href="{{ route('users.logout') }}" >
                                                    <div class="menu-icon"><i class=" fa fa-sign-out"></i></div>  <div class="menu-text">{{ __('Logout') }}</div>
                                            </a>
                  {{-- <li class="line"></li>                 --}}
                  {{-- <li> <a href="#"> <div class="menu-icon"><i class=" fa fa-question-circle"></i></div> <div class="menu-text">Help</div> </a> </li>
                  <li> <a href="#"> <div class="menu-icon"><i class=" glyphicon glyphicon-bullhorn"></i></div> <div class="menu-text">Report a Problem</div> </a> </li>               --}}
              </ul>
          </div>
      </div>
    </div>

  </li>


  </ul>
<!-- Head menu search form ends -->
                      </div>
                  </div>
              </div>

          </div>
        </div>
      </div>
      <!-- container -->
    </div>
    <!-- vd_primary-menu-wrapper -->
</header>
<div id="intercont">
  <p id="intemsg" class="intcolor"></p>
</div>  
