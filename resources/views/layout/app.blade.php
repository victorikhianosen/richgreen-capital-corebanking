<!DOCTYPE html>
<head>
    <meta charset="utf-8" />
    <title class="noprint">BanQPro - @yield('title')</title>
    <meta name="keywords" content="banking software, banking, bank, software, accounting software" />
    <meta name="description" content="banqpro is a banking software for commercial banks,mfb and mfi">
    <meta name="author" content="ggtconnect">
    
    <!-- Set the viewport width to device width for mobile -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    
    <link rel="shortcut icon" href="{{asset('img/favicon.png')}}">
    
    
    <!-- CSS -->
    @include('includes.styles')
  <style>
      @media print{
    .noprint{
        display: none;
    }
    #fullwidth{
        width: 100vh !important;
    }
}
.table-responsive{
display: block !important;
overflow-x: auto !important;
width: 100% !important;
}

.loader{
    position: fixed;
    top: 0;
    left: 0;
    background-color: rgba(142, 142, 142, 0.696);
    height: 100%;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1;
    visibility: hidden;
}
.card{
    background-color: #fff;
    width: 12%;
    height: 8%;
    border-radius: 5px;
    padding: 5px;
    display: flex;
    justify-content: center;
    align-items: center;
}
  </style>
</head>    

<body id="dashboard" class="full-layout  nav-right-hide nav-right-start-hide  nav-top-fixed  responsive    clearfix" data-active="dashboard "  data-smooth-scrolling="1">     
  <div class="vd_body">
  
  
      <div class="loader">
  <div class="card">
    <img src="{{asset('img/loading.gif')}}"  alt="loading">
    &nbsp;
    <span class="loadingtext"></span>
  </div>
</div>
<!-- Header Start -->
  @include('includes.navbar')
  <!-- Header Ends --> 
<div class="content">
  <div class="container">
    <!--sidebar -->  
    @include('includes.sidebar') 
    <!--sidebar -->   

    <!-- Middle Content Start -->
    
    <div class="vd_content-wrapper">
      <div class="vd_container">
        <div class="vd_content clearfix">
          <div class="vd_head-section clearfix">
            <div class="vd_panel-header noprint">
              <ul class="breadcrumb">
                <li><a href="{{route('dashboard')}}">Home</a> </li>
                <li class="active">@yield('pagetitle')</li>
              </ul>
              <div class="vd_panel-menu hidden-sm hidden-xs" data-intro="<strong>Expand Control</strong><br/>To expand content page horizontally, vertically, or Both. If you just need one button just simply remove the other button code." data-step=5  data-position="left">
    <div data-action="remove-navbar" data-original-title="Remove Navigation Bar Toggle" data-toggle="tooltip" data-placement="bottom" class="remove-navbar-button menu"> <i class="fa fa-arrows-h"></i> </div>
      <div data-action="remove-header" data-original-title="Remove Top Menu Toggle" data-toggle="tooltip" data-placement="bottom" class="remove-header-button menu"> <i class="fa fa-arrows-v"></i> </div>
      <div data-action="fullscreen" data-original-title="Remove Navigation Bar and Top Menu Toggle" data-toggle="tooltip" data-placement="bottom" class="fullscreen-button menu"> <i class="glyphicon glyphicon-fullscreen"></i> </div>
      
</div>
@if (session()->has('subw'))
     <marquee behavior="scroll" direction="left" style="color:orangered">{{session()->get('subw')['msg']}}</marquee>
@endif


            </div>
          </div>
          <!-- vd_head-section -->
          
          <div class="vd_title-section clearfix">
            <div class="vd_panel-header">
              <h1>@yield('pagetitle')</h1>
              {{-- <small class="subtitle"></small> --}}
     
<!-- vd_panel-menu --> 
            </div>
            <!-- vd_panel-header --> 
          </div>
          <!-- vd_title-section -->
          
          @yield('content')
          
        </div>
        <!-- .vd_content --> 
      </div>
      <!-- .vd_container --> 
    </div>
    <!-- .vd_content-wrapper --> 
    
    <!-- Middle Content End --> 
    
  </div>
</div>

<!-- customer upload Modal -->
<div class="modal fade" id="myModaluploadcsv" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
	  <div class="modal-content">
		<div class="modal-header vd_bg-blue vd_white">
		  <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
		  <h4 class="modal-title" id="myModalLabel">Upload Customer</h4>
		</div>
		<form class="form-horizontal" action="{{route('customer.uploadcsv')}}" method="post" enctype="multipart/form-data" onsubmit="thisForm()">
		  @csrf
		<div class="modal-body"> 
			<div class="row">
			 <div class="bg-primary col-sm-8" style="padding: 8px 3px; border-radius:5px;margin:10px 10px">Please click here to <a href="{{asset('csv/customers.csv')}}" style="color:#fff; font-weight:bold" download="customers">Download Format</a></div>
			  <div class="form-group col-md-11 col-sm-12 col-lg-11" style="margin: 0px 10px">
				<label>Upload Customers</label>
				<input type="file" class="form-control" name="customer_file" id="csv" accept=".csv">
				</div>
			</div>
		</div>
		<div class="modal-footer background-login">
		  <button type="button" class="btn vd_btn vd_bg-red" data-dismiss="modal">Close</button>
		  <button type="submit" class="btn vd_btn vd_bg-green" id="btnssubmit">Upload File</button>
		</div>
	  </form>
	  </div>
	  <!-- /.modal-content --> 
	</div>
	<!-- /.modal-dialog --> 
  </div>
  <!-- /.modal -->

<!-- Footer Start -->
  <footer class="footer-1"  id="footer">      
    <div class="vd_bottom ">
        <div class="container">
            <div class="row">
              <div class=" col-xs-12">
                <div class="copyright">
                  	Copyright &copy;{{date("Y")}} mybanqPro Inc. All Rights Reserved 
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
@yield('scripts')

<script>
//disabled submit button 
function thisForm(){
    document.querySelector("#btnssubmit").setAttribute('disabled',true);
    document.querySelector("#btnssubmit").textContent='Please wait...';
    document.querySelector("#btnsetsubmit").setAttribute('disabled',true);
    document.querySelector("#btnsetsubmit").textContent='Please wait...';
}
</script>

<script>
    const time = new Date().getHours();
let greeting;
if (time < 12) {
  greeting = "Good Morning";
} else if (time < 17) {
  greeting = "Good Afternoon";
} else {
  greeting = "Good Evening";
}
document.getElementById("grrt").innerHTML = greeting;
</script>

<script>
$(document).ready(function(){
    setInterval(() => {
       $.get('{{route("getpendingcust")}}',(data) => {
        $("#pendc").text(data);
       }); 
    },3000);
    setInterval(() => {
       $.get('{{route("getclosecust")}}',(data) => {
        $("#closedc").text(data);
       }); 
    },3000);
    setInterval(() => {
       $.get('{{route("getactivecust")}}',(data) => {
        $("#activec").text(data);
       }); 
    },3000);
    setInterval(() => {
       $.get('{{route("getrestcust")}}',(data) => {
        $("#restr").text(data);
       }); 
    },3000);
    setInterval(() => {
       $.get('{{route("getdomcust")}}',(data) => {
        $("#domac").text(data);
       }); 
    },3000);
});
</script> 

</body>
<!-- Mirrored from www.venmond.com/demo/vendroid/index-ecommerce.php by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 05 Apr 2017 13:56:34 GMT -->
</html>