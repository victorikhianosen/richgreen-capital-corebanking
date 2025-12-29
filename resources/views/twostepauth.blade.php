<!DOCTYPE html>
<head>
    <meta charset="utf-8" />
    <title>{{env('APP_NAME')}} - Two Factor Authentication</title>
    <meta name="keywords" content="banking software, banking, bank, software, accounting software" />
    <meta name="description" content="banqpro is a banking software for commercial banks,mfb and mfi">
    <meta name="author" content="ggtconnect">
    <!-- Set the viewport width to device width for mobile -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <link rel="shortcut icon" href="{{asset('img/favicon.png')}}">
    
    <!-- CSS -->
@include('includes.styles')
<style>
  #loginpg{
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100vh;
    width: 100%;
  }
  #row{
    display: flex;
    flex-direction: row;
  }

  .pinfield{
      display: flex;
      justify-content: center;
  }
  .pinfield input{
      font-size: 15px;
      padding: 10px;
      width: 35px;
      margin: 2px;
      border-radius: 5px;
  }
</style>
</head>    
<?php 
      $getsetvalue = new \App\Models\Setting();
  ?>
<body>
  <div id="row">
    <div class="col-md-7 col-lg-7 col-sm-12">
      
      <div id="loginpg">
        <div class="col-md-6 col-lg-6 col-md-offset-1">
        <div class="heading clearfix">
          <h1 class="vd_black" style="font-size: 39px;font-weight:700">Two Factor Verification</h1>
          <p class="my-4 vd_black" style="font-size:16px;font-weight:400">
                {{is_null(Auth::user()->is_2fa_enable) ? 'Scan QRcode or Enter Secret code and 2Fa code to get started' : 'Please Enter Two Factor Authentication Code'}}
            </p>
        </div>
       
            {{-- <div class="login-icon entypo-icon"> <i class="icon-key"></i> </div> --}}
            @include('includes.errors')
            @include('includes.success')
        
                {{-- <div class="my-3">
                     <img src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl={{ urlencode($qrcodurl) }}" alt="QR Code">
                </div> --}}

                <form  id="login-form" action="{{route('verify.store')}}" role="form" method="POST" onsubmit="thisForm()">
                  @csrf   
                  
                  @if (is_null(Auth::user()->is_2fa_enable))
                    <div class="form-group mgbt-xs-20 my-2">
                      <label for="secretky">Your secret key</label>
                      <input type="text" readonly autocomplete="off" class="width-100 required text-center" value="{{$sercretkey}}">
                  </div>
                    <div class="my-3 row justify-content-center" style="display: flex; justify-content: center;">
                        {!! QrCode::size(200)->generate($qrcodurl) !!}
                    </div>
                @endif

                  <div class="form-group mgbt-xs-20">
                    <label for="password">2FA Authentication Code</label>
                    {{-- <input type="text" placeholder="Authentication Code" autocomplete="off" id="codetwo" max="6" maxlength="6" name="two_step_code" class="width-100 required" required> --}}
                    <div class="pinfield mt-4" id="inputs">
                      <input type="text" size="1" maxlength="1" autofocus name="two_step_code[]" pattern="[0-9]*"  class="input-bordered" required>
                     <input type="text" size="1" maxlength="1" name="two_step_code[]" pattern="[0-9]*"  class="input-bordered" required>
                     <input type="text" size="1" maxlength="1" name="two_step_code[]" pattern="[0-9]*"  class="input-bordered" required>
                     <input type="text" size="1" maxlength="1" name="two_step_code[]" pattern="[0-9]*"  class="input-bordered" required>
                     <input type="text" size="1" maxlength="1" name="two_step_code[]" pattern="[0-9]*"  class="input-bordered" required>
                     <input type="text" size="1" maxlength="1" name="two_step_code[]" pattern="[0-9]*"  class="input-bordered" required>
                     </div>
                  </div>

              {{-- <div id="vd_login-error" class="alert alert-danger hidden"><i class="fa fa-exclamation-circle fa-fw"></i> Please fill the necessary field </div> --}}
              <div class="form-group">
                  <button class="btn vd_bg-blue vd_white width-100" type="submit" id="login-submit">Verify Authentication</button>
              </div>
            </form>
            <div class="form-group">
                <a href="{{ route('users.logout') }}" class="btn vd_bg-red vd_white width-100" id="login-submit">Logout</a>
                {{-- <form id="logout-form" action="{{ route('users.logout') }}" method="POST" style="display: none;">
                  @csrf   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                </form> --}}
              </div>
            {{-- <p>If you did not recieve code please click <a href="{{route('resnd')}}" class="font-weight-bold">Here</a> to resend </p> --}}
       
           
        </div>

        <div  style="margin:10px auto;text-align:center">
            <a href="https://" style="color:#000">Copyright &copy;{{date("Y")." RichGreen Limited"}}. All Rights Reserved </a>
        </div> 
      </div>
    </div>
    <div class="col-md-5 col-lg-5 col-sm-12">
      <div>
        <h3 style="float:left; margin-top:40px;margin-left:10px;margin-right:20px">
          <img src="{{asset('img/mybanq.png')}}" class="img-responsive" style="border-radius:8px" width="180" alt="logo">
        </h3>
    </div>
    <div style="background-image: url({{asset('img/businessman.jpg')}});background-size:cover;background-position:left right;background-repeat:no-repeat;min-height:100vh;width:100%"></div>
      {{-- <div style="background-color:rgba(44, 126, 44, 0.751); min-height:100vh;width:100vw"></div> --}}
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

<script>
  const inputs = document.getElementById('inputs');

inputs.addEventListener('input', function (e) {
    const target = e.target;
    const digit = target.value;

    if (isNaN(digit) || digit.length > 1) {
        target.value = ""; // Clear if input is invalid
        return;
    }

    if (digit !== "") {
        const next = target.nextElementSibling;
        if (next && next.tagName === 'INPUT') {
            next.focus();
        }
    }
});

inputs.addEventListener('keydown', function (e) {
    const target = e.target;
    const key = e.key;

    if (key === "Backspace" || key === "Delete") {
        target.value = ""; // Clear the current input
        const prev = target.previousElementSibling;
        if (prev && prev.tagName === 'INPUT') {
            prev.focus();
        }
        e.preventDefault(); // Prevent default deletion
    }
});

  
</script>

<script type="text/javascript">
//disabled submit button 
function thisForm(){
    document.querySelector("#login-submit").setAttribute('disabled',true);
    document.querySelector("#login-submit").textContent='Autheticating...';
}
</script>
</body>
</html>