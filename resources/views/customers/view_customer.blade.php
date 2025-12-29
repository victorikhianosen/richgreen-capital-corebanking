@extends('layout.app')
@section('title')
    Customers Account
@endsection
@section('pagetitle')
Search Customers Account
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                            @can('create customer')
                            <a href="javascript:void(0)" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#myModaluploadcsv"><span class="menu-icon"> <i class="fa fa-upload"></i> </span> Add Customer Via CSV</a>
                           <a href="{{route('customer.create')}}" class="btn btn-default btn-sm"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Create Customer</a>
                        @endcan
                        </div>
                      </div>
                  <div class="panel-body">

                    <div class="noprint" style="margin-bottom: 15px">
                        <form action="{{route('customer.search')}}" method="get" onsubmit="thisForm()">
                          <input type="hidden" name="filter" value="true">
                          <table class="table table-bordered table-hover table-sm">
                            <thead>
                              <tr>
                                <th>Customer Name / Account Number</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                              
                                <td>
                                  <div class="form-group">
                                    <input type="text" name="csdetails" required id="" class="form-control" style="w" value="{{!empty($_GET['csdetails']) ? $_GET['csdetails'] : ''}}">
                                  </div>
                                </td>
                                                          
                                 <td>
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Search</button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </form>
                      </div>

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                  @include('includes.success')
                    </div>
                    </div>
                      <form action="{{route('customer.acticl')}}" method="post" onsubmit="return performAction(this)">
                        @csrf
                        <div class="text-center" style="margin: 7px 0px">
                        <input type="submit" class="btn btn-danger" name="cmdupdatestatus" value="Close Account(s)">
                        </div>
                        <div class="table-responsive">
                          <table class="table table-striped table-bordered table-condensed table-hover table-sm" id="acoff">
                              <thead>
                                  <tr>
                                    <th></th>
                                      <th>Sn</th>
                                      <th>Name</th>
                                      <th>Username</th>
                                      <th>Account Section</th>
                                      <th>Account No</th>
                                      <th>Account Type</th>
                                      <th>Phone</th>
                                      <th>Gender</th>
                                      <th>Email</th>
                                      <th>Reg Date</th>
                                      <th>Status</th>
                                      <th></th>
                                  </tr>
                              </thead>    
                              <tbody>
                                @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                                <?php $i=0;?>
                                @foreach ($customers as $item)
                                <tr>
                                  <td><input type="checkbox" name="customerid[]" style="cursor: pointer"  value="{{$item->id}}" class="checkcust" id=""></td>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($item->last_name." ".$item->first_name)}}</td>
                                    <td>{{$item->username}}</td>
                                    <td><span class="{{$item->section =='asset matrix' ? 'text-warning' : 'text-success'}}">{{ucwords($item->section)}}</span></td>
                                    <td>{{$item->acctno}}</td>
                                    <td>
                                      <?php 
                                        $actyp = \App\Models\SavingsProduct::select('name')->where('id',$item->account_type)->first();
                                      ?>
                                      {{$actyp->name}}</td>
                                    <td>{{$item->phone}}</td>
                                    <td>{{$item->gender}}</td>
                                    <td>{{$item->email}}</td>
                                    <td>{{date("d-m-Y",strtotime($item->reg_date))}}</td>
                                    <td>
                                      @if ($item->status == '1')  
                                          <span class="badge vd_bg-green">Active</span>
                                      @endif
                                    </td>
                                  <td>
                                    <div class="btn-group">
                                      <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown"> Action <i class="fa fa-caret-down prepend-icon"></i> </button>
                                      <ul class="dropdown-menu" role="menu">
                                          
                                          @can('view customer')
                                          <li>
                                              <a href="{{route('customer.close',['id' => $item->id])}}?status=2" onclick="return confirm('Are you sure you want to close these account?')">Close Account</a>
                                            </li>
                                            <li>
                                              <a href="{{route('customers.emails.create',['id' => $item->id])}}?sendmail=true">Send Mail</a>
                                            </li>
                                          <li>
                                            <a href="{{route('customer.view',['id' => $item->id])}}">Profile</a>
                                          </li>
                                          @endcan
                                         
                                           {{-- @can('delete customer')
                                          <li>
                                        <a href="{{route('customer.delete',['id' => $item->id])}}" onclick="return confirm('are you sure you want to delete these record')">Delete</a>
                                          </li>
                                          @endcan --}}
                                        </ul>
                                      </div>
                                      </td>
                                </tr>
                                <?php $i++?>
                                @endforeach
                                @endif
                              </tbody>
                          </table>
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
    <script type="text/javascript">
  $(document).ready(function(){
    $("#acoff").dataTable({
      'pageLength':50,
      'dom': 'Bfrtip',
        buttons: [ 'copy', 'csv', 'print','pdf']
    });

    $("#checkall").click(function(){
      if($(this).is(":checked")){
        $(".checkcust").prop('checked',true);
      }else{
        $(".checkcust").prop('checked',false);
      }
    });
  });
</script>

<script type="text/javascript">
  function whichButton(){
    var buttonValue = "";
    let checkbox = document.querySelectorAll(".checkcust");
    for(i = 0; i < checkbox.length; i++){//scan all form element
        if(checkbox[i].checked){
          buttonValue = checkbox[i].value;
        }
      
    }
    return buttonValue;
  }
  function performAction(thisform){
     with(thisform){
      if(whichButton()){
        if(confirm('Continue with selected action?')){return true;}
        else{return false;}
      }else{
        window.alert("Please make a selection to proceed");
        return false;
      }
     }
  }
</script>
@endsection
