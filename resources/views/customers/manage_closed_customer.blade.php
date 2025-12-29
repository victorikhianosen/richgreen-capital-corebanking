@extends('layout.app')
@section('title')
    Closed Customers
@endsection
@section('pagetitle')
Closed Customers Accounts
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
                       <a href="{{route('customer.create')}}" class="btn btn-default btn-sm"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Customer</a>
                    @endcan
                    </div>
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                  @include('includes.success')
                    </div>
                    </div>
                    <form action="{{route('customer.acticl')}}" method="post" onsubmit="return performAction(this)">
                      @csrf
                      <div class="text-center" style="margin: 7px 0px">
                      <input type="submit" class="btn btn-success" name="cmdupdatestatus" value="Activate Account(s)">
                      </div>
                      <div class="table-responsive">
                        <table class="table table-striped table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr>
                                  <th></th>
                                  <th>Sn</th>
                                  <th>Name</th>
                                  <th>Account No</th>
                                  <th>Account Section</th>
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
                                <?php $i=0;?>
                                @foreach ($clcustomers as $item)
                                <tr>
                                  <td><input type="checkbox" name="customerid[]" style="cursor: pointer"  value="{{$item->id}}" class="checkcust" id=""></td>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($item->last_name." ".$item->first_name)}}</td>
                                    <td>{{$item->acctno}}</td>
                                    <td><span class="{{$item->section =='asset matrix' ? 'text-warning' : 'text-success'}}">{{ucwords($item->section)}}</span></td>
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
                                        @if ($item->status == '2')  
                                            <span class="badge vd_bg-black'}}">Closed</span>
                                        @endif
                                      </td>
                                    <td>
                                      <div class="btn-group">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"> Action <i class="fa fa-caret-down prepend-icon"></i> </button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li>
                                                <a href="{{route('customer.active',['id' => $item->id])}}?status=1">Activate Account</a>
                                              </li>
                                          @can('view customer')
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
                            </tbody>
                        </table>
                    </div>
                    </form>
                  </div>
                  {{-- <div class="row justify-content-center">
                    {{$clcustomers->links()}}
                  </div> --}}
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
