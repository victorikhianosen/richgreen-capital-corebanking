@extends('layout.app')
@section('title')
    Upload Transaction File
@endsection
@section('pagetitle')
Upload Transaction File
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                         <a href="{{route('viewuploadstatus')}}" class="btn btn-danger btn-sm"><span class="menu-icon"> </span> View Uploaded Transactions</a>
                        <a href="{{asset('csv/transaction_format.csv')}}" class="btn btn-info btn-sm" download="transaction_format"><span class="menu-icon"> <i class="fa fa-download"></i> </span> Download Format</a>
                     </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-offset-4 col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('uploadtrx')}}" method="post" role="form" id="trxtransfer" enctype="multipart/form-data" onsubmit="thisForm()">
                      @csrf
                        <div class="row">
                            <div class="col-md-offset-4 col-md-6 col-lg-6 col-sm-12" style="border: 1px solid #f4f4f4;">
                      <div class="form-group" style="padding: 0 6px;">
                        <label>Upload Transactions File</label>
                        <input class="form-control width-70" required="required" name="file_upload" type="file" id="upld" autocomplete="off" accept=".csv">
                    </div>
                    
                    <div class="form-group form-actions">
                      <div class="col-md-6 col-md-offset-2">
                        <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Upload Transactions File</button>
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