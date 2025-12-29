<!DOCTYPE html>
<head>
    <meta charset="utf-8" />
    <title>BanQPro - Login</title>
    <meta name="keywords" content="banking software, banking, bank, software, accounting software" />
    <meta name="description" content="banqpro is a banking software for commercial banks,mfb and mfi">
    <meta name="author" content="ggtconnect">
    <!-- Set the viewport width to device width for mobile -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <!-- Fav and touch icons -->
    <link rel="shortcut icon" href="{{asset('img/favicon.png')}}">
    
    <!-- CSS -->
@include('includes.styles')
<style>
  #loginpg{
    display: flex;
    flex-direction: column;
    justify-content: center;
    margin: 0px auto;
    height: 100vh;
    width: 100%;
  }
  #row{
    display: flex;
    flex-direction: row;
    overflow: hidden
  }

  .password{
     position: relative;
  }
  
  .ehohdpass{
     position: absolute;
    bottom: 75px;
    right: 25px;
  }

</style>
</head>    
<?php 
      $getsetvalue = new \App\Models\Setting();
  ?>
<body>
    <div id="intercont">
    <p id="intemsg" class="intcolor"></p>
  </div>  
  <div id="row">
    <div class="col-md-7 col-lg-7 col-sm-12">
      
      <div id="loginpg">
        <div class="col-md-6 col-lg-6 col-md-offset-3">
          <div class="heading clearfix">
            <h1 class="vd_black" style="font-size: 39px;font-weight:700">Welcome Back</h1>
            <p class="my-4 vd_black" style="font-size:16px;font-weight:400">
              Please enter your Username and Password
              </p>
          </div>
         
              {{-- <div class="login-icon entypo-icon"> <i class="icon-key"></i> </div> --}}
              @include('includes.errors')
              @include('includes.success')
              <form  id="login-form" action="{{route('users.login')}}" role="form" method="POST" onsubmit="thisForm()">
                    @csrf      
                <div class="form-group mgbt-xs-20">
                  <label for="username">Username</label>
                  <input type="text" placeholder="Username" style="width: 100%" id="username" name="username" class="required" autofocus required value="{{old('username')}}" >
                  </div>
                  <div class="form-group mgbt-xs-20" id="show_hide_password">
                    <label for="password">Password</label>
                    <input type="password" placeholder="Password" id="password" name="password" class="width-100 required password" required>
                    <a href="#" class="ehohdpass"><i class="fa fa-eye-slash text-dark" aria-hidden="true"></i></a>
                </div>
  
                <div id="vd_login-error" class="alert alert-danger hidden"><i class="fa fa-exclamation-circle fa-fw"></i> Please fill the necessary field </div>
                <div class="form-group">
                    <button class="btn vd_bg-green vd_white width-100" type="submit" id="login-submit">Login</button>
                </div>
              </form>
        </div>

           <div  style="margin:10px auto">
        <a href="https://ggtconnect.com" style="color:#000">Copyright &copy;{{date("Y")." RichGreen Limited"}}. All Rights Reserved </a>
    </div> 
      </div>
      
    </div>
    <div class="col-md-5 col-lg-5 col-sm-12">
      <div>
        <h3 style="float:left; margin-top:40px;margin-left:10px;margin-right:20px">
          <img src="{{asset('img/myban.png')}}" class="img-responsive" style="border-radius:8px" width="180" alt="logo">
        </h3>
    </div>
    
      {{-- <div style="background-color:rgba(44, 126, 44, 0.751); height:100vh;width:100vw"></div> --}}
      <div style="background-image: url({{asset($getsetvalue->getsettingskey('login_background'))}});background-size:cover;background-position:left right;background-repeat:no-repeat;min-height:100vh;width:100%"></div>

    </div>
  </div>

<!-- .vd_body END  -->
<a id="back-top" href="#" data-action="backtop" class="vd_back-top visible"> <i class="fa  fa-angle-up"> </i> </a>
<!--
<a class="back-top" href="#" id="back-top"> <i class="icon-chevron-up icon-white"> </i> </a> -->

<!-- Javascript =============================================== --> 
<!-- Placed at the end of the document so the pages load faster --> 
 @include('includes.scripts')
<!-- Specific Page Scripts Put Here -->
<script type="text/javascript">
//disabled submit button 
function thisForm(){
    document.querySelector("#login-submit").setAttribute('disabled',true);
    document.querySelector("#login-submit").textContent='Logging in...';
}

$(document).ready(function(){
 $("#show_hide_password a").on('click', function(event) {
        event.preventDefault();
        if($('#show_hide_password input').attr("type") == "text"){
            $('#show_hide_password input').attr('type', 'password');
            $('#show_hide_password i').addClass( "fa-eye-slash" );
            $('#show_hide_password i').removeClass( "fa-eye" );
        }else if($('#show_hide_password input').attr("type") == "password"){
            $('#show_hide_password input').attr('type', 'text');
            $('#show_hide_password i').removeClass( "fa-eye-slash" );
            $('#show_hide_password i').addClass( "fa-eye" );
        }
    });
});
</script>
</body>
</html>