@extends('layout.app')
@section('title')
    Create Expenses Type
@endsection
@section('pagetitle')
Create Expenses Type
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{route('expensestyp.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <form class="form-horizontal"  action="{{route('expensestyp.store')}}" method="post" role="form" onsubmit="thisForm()">
                      @csrf
                      
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Category</label>
                        <div class="col-sm-7 controls">
                            <select name="category" class="width-70" id="asty" onchange="document.getElementById('inial').value=this.options[this.selectedIndex].getAttribute('data-value');">
                                <option selected disabled>Select...</option>
                                <option value="admin expenses">Admin Expenses</option>
                                <option value="prepaid expenses">Prepaid Expenses</option>
                                <option value="operating expenses">Operating Expenses</option>
                            </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-2 control-label">Name</label>
                        <div class="col-sm-7 controls">
                            <input type="text" name="name" class="width-70" id="nme" value="{{old('name')}}">
                        </div>
                      </div>
                        <div class="form-group form-actions">
                            <div class="col-sm-4"> </div>
                            <div class="col-sm-7">
                              <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit"><i class="icon-ok"></i>Save Record</button>
                              
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