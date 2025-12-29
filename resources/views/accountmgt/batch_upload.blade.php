@extends('layout.app')
@section('title')

@if (!empty($_GET['type']) && $_GET['type'] == 'ac')
    Upload Account Category
    @endif

@if(!empty($_GET['type']) && $_GET['type'] == 'gl')
  Upload General Ledger      
  @endif

@if(!empty($_GET['type']) && $_GET['type'] == 'glc')
Upload General Ledger  To Customer   
@endif

@if(!empty($_GET['type']) && $_GET['type'] == 'cgl')
Upload Customer To General Ledger  
@endif

@if(!empty($_GET['type']) && $_GET['type'] == 'gltogl')
 Upload GL To GL  
 @endif

@endsection
@section('pagetitle')

@if (!empty($_GET['type']) && $_GET['type'] == 'ac')
    Upload Account Category
    @endif
    
    @if(!empty($_GET['type']) && $_GET['type'] == 'gl')
  Upload General Ledger
  @endif
  
  @if(!empty($_GET['type']) && $_GET['type'] == 'glc')
Upload General Ledger  To Customer
@endif

@if(!empty($_GET['type']) && $_GET['type'] == 'cgl')
Upload Customer To General Ledger 
@endif

@if(!empty($_GET['type']) && $_GET['type'] == 'gltogl')
 Upload GL To GL  
 @endif

@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                      @if (!empty($_GET['type']) && $_GET['type'] == 'ac')

                      <a href="{{asset('csv/account_category_format.csv')}}" class="btn btn-info btn-sm" download="account_category_format"><span class="menu-icon"> <i class="fa fa-download"></i> </span> Download Account Category Format</a>
                      @endif

                      @if(!empty($_GET['type']) && $_GET['type'] == 'gl')

                      <a href="{{asset('csv/general_ledger_format.csv')}}" class="btn btn-info btn-sm" download="general_ledger_format"><span class="menu-icon"> <i class="fa fa-download"></i> </span> Download General Ledger Format</a>
                      @endif

                      @if(!empty($_GET['type']) && $_GET['type'] == 'glc')
                      <a href="{{route('viewuploadstatus')}}?type=glc" class="btn btn-danger btn-sm"><span class="menu-icon"> </span> View GL to Customer Batch Upload</a>

                      <a href="{{asset('csv/GL_to_customer_batch_upload_format.csv')}}" class="btn btn-info btn-sm" download="GL_to_customer_batch_upload_format"><span class="menu-icon"> <i class="fa fa-download"></i> </span> Download GL to Customer Batch Upload Format</a>
                      @endif

                     @if(!empty($_GET['type']) && $_GET['type'] == 'cgl')
                     <a href="{{route('viewuploadstatus')}}?type=cgl" class="btn btn-danger btn-sm"><span class="menu-icon"> </span> View Customer to GL Batch Upload</a>

                        <a href="{{asset('csv/customer_to_GL_batch_upload_format.csv')}}" class="btn btn-info btn-sm" download="customer_to_GL_batch_upload_format"><span class="menu-icon"> <i class="fa fa-download"></i> </span> Download Customer to GL Batch Upload Format</a>
                        @endif

                        @if(!empty($_GET['type']) && $_GET['type'] == 'gltogl')
                        <a href="{{route('viewuploadstatus')}}?type=gltogl" class="btn btn-danger btn-sm"><span class="menu-icon"> </span> View Uploaded GL to GL Batch Upload</a>

                        <a href="{{asset('csv/GL_to_GL_batch_upload_format.csv')}}" class="btn btn-info btn-sm" download="GL_to_GL_batch_upload_format"><span class="menu-icon"> <i class="fa fa-download"></i> </span> Download GL to GL Batch Upload Format</a>
                      @endif
                      
                     </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-offset-4 col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('batch_upload.store')}}" method="post" role="form" id="trxtransfer" enctype="multipart/form-data" onsubmit="thisForm()">
                      @csrf
                        <div class="row">
                            <div class="col-md-offset-4 col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                      <div class="form-group" style="padding: 0 6px;">
                        <label>Select Batch 
                          @if (!empty($_GET['type']) && $_GET['type'] == 'ac')
                             Upload Account Category
                            @elseif(!empty($_GET['type']) && $_GET['type'] == 'gl')
                            General Ledger      
                            @elseif(!empty($_GET['type']) && $_GET['type'] == 'glc')
                            General Ledger  To Customer      
                            @elseif(!empty($_GET['type']) && $_GET['type'] == 'cgl')
                            Customer To General Ledger  
                            @else
                             GL To GL  
                            @endif
                          File</label>
                        <input class="form-control width-70" required="required" name="file_upload" type="file" id="upld" autocomplete="off" accept=".csv">
                    </div>
                    <input type="hidden" name="uploads" value="{{!empty($_GET['type']) ? $_GET['type'] : ''}}">
                    <div class="form-group form-actions">
                      <div class="col-md-6 col-md-offset-2">
                        <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Upload File</button>
                      </div>
                    </div>
                            </div>
                           
                        </div>
                    </form>
                  </div>
                </div>
                <!-- Panel Widget --> 
              </div>
              <!-- col-md-12 --> 
            </div>
            <!-- row -->
  </div>
@endsection
@section('scripts')
<script>
  $(document).ready(function(){
    
  });
</script>
@endsection