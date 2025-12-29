@extends('layout.app')
@section('title')
    Create Collateral
@endsection
@section('pagetitle')
Create Collateral
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{!empty($_GET['return_url']) ? url($_GET['return_url']) : route('colla.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    
                    <form action="{{route('colla.update',['id' => $ed->id])}}" method="post" enctype="multipart/form-data" onsubmit="thisForm()">
                        @csrf
                        @if(!empty($_GET['return_url']))
                      <input type="hidden" value="{{$_GET['return_url']}}" name="return_url">
                  @endif
                  @if(!empty($_GET['customerid']))
                      <input type="hidden" value="{{$_GET['customerid']}}" name="customerid">
                  @endif
                  @if(!empty($_GET['loanid']))
                      <input type="hidden" value="{{$_GET['loanid']}}" name="loanid">
                  @endif
                  <p class="bg-danger">Required Field</p>
      
                  <div class="form-group">
                    <label for="">Collateral type</label>
                    <select name="collateral_type_id" class="form-control" id="" required>
                      <option disabled selected>Select...</option>
                      @foreach ($types as $item)
                         <option value="{{$item->id}}" {{$ed->collateral_type_id == $item->id ? "selected" : ""}}>{{$item->name}}</option> 
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group">
                     <label for="">Product Name</label>
                     <input type="text" name="name" class="form-control" placeholder="Enter Value" required value="{{$ed->name}}">
                  </div>
                  <div class="form-group">
                    <label for="">Value</label>
                      <input type="number" name="value" class="form-control" placeholder="Enter Value" required value="{{$ed->value}}">
                  </div>
                  <div class="form-group">
                    <label for="">Register Date</label>
                    <input type="date" name="date" class="form-control" required value="{{$ed->date}}">
                  </div>
                  <div class="form-group">
                    <label for="">Current Status</label>
                    <select name="status" class="form-control" id="" required>
                      <option disabled selected>Select...</option>
                      <option value="deposited_into_branch" {{$ed->status == "deposited_into_branch" ? "selected" : ""}}>Deposited into branch</option>
                      <option value="collateral_with_customer" {{$ed->status == "collateral_with_customer" ? "selected" : ""}}>Collateral with customer</option>
                      <option value="returned_to_customer" {{$ed->status == "returned_to_customer" ? "selected" : ""}}>Returned to customer</option>
                      <option value="repossession_initiated" {{$ed->status == "repossession_initiated" ? "selected" : ""}}>Repossession initiated</option>
                      <option value="repossessed" {{$ed->status == "repossessed" ? "selected" : ""}}>Repossessed</option>
                      <option value="sold" {{$ed->status == "sold" ? "selected" : ""}}>Sold</option>
                      <option value="lost" {{$ed->status == "lost" ? "selected" : ""}}>Lost</option>
                    </select>
                  </div>
                  <p class="bg-primary">optional Field</p>
      
                  <div class="form-group">
                    <label for="">Serial Number</label>
                    <input type="text" name="serial_number" class="form-control" value="{{$ed->serial_number}}">
                  </div>
                  <div class="form-group">
                    <label for="">Model Name</label>
                    <input type="text" name="model_name" class="form-control" value="{{$ed->model_name}}">
                  </div>
                  <div class="form-group">
                    <label for="">Model Number</label>
                    <input type="text" name="model_number" class="form-control" value="{{$ed->model_number}}">
                  </div>
                  <div class="form-group">
                    <label for="">Manufacture Date</label>
                    <input type="date" name="manufacture_date" class="form-control" value="{{$ed->manufacture_date}}">
                  </div>
                  <div class="form-group">
                    <label for="">Notes</label>
                    <textarea name="notes" id="" class="form-control" cols="10" rows="4">{{$ed->notes}}</textarea>
                  </div>
                  <div class="form-group">
                    <label>
                      Collateral Photo
                    </label>
                    <input type="file" name="photo" class="form-control" accept=".jpeg,.jpg,.png">
                  </div>
                  <div class="form-group">
                    <label>
                      Collateral Files(docx,doc,pdf,image)
                    </label>
                    <input type="file" name="files[]" class="form-control" multiple accept=".pdf,.doc,.docx,.jpeg,.jpg,.png">
                  </div>

                  <div class="form-group form-actions">
                    <div class="col-sm-4"> </div>
                    <div class="col-sm-7">
                      <button class="btn vd_btn vd_bg-green vd_white" type="submit" id="btnssubmit">Update Record</button>
                      
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