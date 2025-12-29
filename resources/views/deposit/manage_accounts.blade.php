@extends('layout.app')
@section('title')
    {{!empty($_GET['ac_type']) ? ucwords($_GET['ac_type']).'(s)' : ''}}   
@endsection
@section('pagetitle')
{{!empty($_GET['ac_type']) ? ucwords($_GET['ac_type']).'(s)' : ''}}   
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                  @include('includes.success')
                    </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Name</th>
                                    <th>Account No</th>
                                    <th>Account Officer</th>
                                    <th>Phone</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php 
                                $i=0;
                                $j=0;
                                ?>
                                @if (!empty($_GET['ac_type']) && $_GET['ac_type'] == "Current Account" || $_GET['ac_type'] == "Savings Account")
                                @foreach ($accounts as $item)
                                <tr>        

                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($item->last_name." ".$item->first_name)}}</td>
                                    <td>{{$item->acctno}}</td>
                                    <td>{{!is_null($item->accountofficer_id) ? $item->accountofficer->full_name : 'N/A'}}</td>
                                    <td>{{$item->phone}}</td>
                                    <td>
                                        @foreach ($item->savings as $bl)
                                        {{number_format($bl->account_balance,2)}}
                                        @endforeach
                                    </td>
                                    <td>
                                      @if ($item->status == '2')  
                                          <span class="badge vd_bg-black'}}">Closed</span>
                                        @elseif($item->status == '1')  
                                        <span class="badge vd_bg-green">Active</span> 
                                        @elseif($item->status == '8')  
                                        <span class="badge vd_bg-red'}}">Dormant </span>
                                        @elseif($item->status == '7')  
                                        <span class="badge vd_bg-red">Pending</span>
                                        @elseif($item->status == '4')  
                                        <span class="badge vd_bg-blue'}}">Restricted</span>
                                    @elseif($item->status == '5')
                                    <span class="badge vd_bg-red'}}">Fraud Blocked</span>
                                    @elseif($item->status == '6')
                                    <span class="badge vd_bg-yellow'}}">Wrong Password Blocked</span>
                                      @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"> Action <i class="fa fa-caret-down prepend-icon"></i> </button>
                                          <ul class="dropdown-menu" role="menu">
                                            @can('view savings')
                                            <li>
                                                <a href="{{route('customer.close',['id' => $item->id])}}?status=2" onclick="return confirm('Are you sure you want to close these account?')">Close Account</a>
                                              </li>
                                            @endcan
                                            @can('update savings')
                                                <li>
                                              <a href="{{route('customer.edit',['id' => $item->id])}}">Edit</a>
                                            </li>
                                            @endcan
                                            
                                            
                                            {{-- @can('delete savings')
                                            <li>
                                              <a href="{{route('savings.transaction.delete',['id' => $item->id])}}" onclick="return confirm('are you sure you want to delete these record')">Delete</a>
                                            </li>
                                            @endcan --}}
                                        </ul>
                                      </div>
                                        </td>
                                </tr>
                                <?php $i++?>
                                @endforeach

                                @elseif(!empty($_GET['ac_type']) && $_GET['ac_type'] == "domicilary account")
                                    @foreach ($domiaccounts as $item)
                                <tr>        

                                    <td>{{$j+1}}</td>
                                    <td>{{ucwords($item->last_name." ".$item->first_name)}}</td>
                                    <td>{{$item->acctno}}</td>
                                    <td>{{!is_null($item->accountofficer_id) ? $item->accountofficer->full_name : 'N/A'}}</td>
                                    <td>{{$item->phone}}</td>
                                    <td>
                                      {{!empty($item->exrate) ? $item->exrate->currency_symbol : ""}}
                                        @foreach ($item->savings as $bl)
                                        {{number_format($bl->account_balance,2)}}
                                        @endforeach
                                    </td>
                                    <td>
                                      @if ($item->status == '2')  
                                          <span class="badge vd_bg-black'}}">Closed</span>
                                        @elseif($item->status == '1')  
                                        <span class="badge vd_bg-green">Active</span> 
                                        @elseif($item->status == '8')  
                                        <span class="badge vd_bg-red'}}">Dormant </span>
                                        @elseif($item->status == '7')  
                                        <span class="badge vd_bg-red">Pending</span>
                                        @elseif($item->status == '4')  
                                        <span class="badge vd_bg-blue'}}">Restricted</span>
                                    @elseif($item->status == '5')
                                    <span class="badge vd_bg-red'}}">Fraud Blocked</span>
                                    @elseif($item->status == '6')
                                    <span class="badge vd_bg-yellow'}}">Wrong Password Blocked</span>
                                      @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"> Action <i class="fa fa-caret-down prepend-icon"></i> </button>
                                          <ul class="dropdown-menu" role="menu">
                                            @can('view savings')
                                            <li>
                                                <a href="{{route('customer.close',['id' => $item->id])}}?status=2" onclick="return confirm('Are you sure you want to close these account?')">Close Account</a>
                                              </li>
                                            @endcan
                                            @can('update savings')
                                                <li>
                                              <a href="{{route('customer.edit',['id' => $item->id])}}">Edit</a>
                                            </li>
                                            @endcan
                                            
                                            
                                            {{-- @can('delete savings')
                                            <li>
                                              <a href="{{route('savings.transaction.delete',['id' => $item->id])}}" onclick="return confirm('are you sure you want to delete these record')">Delete</a>
                                            </li>
                                            @endcan --}}
                                        </ul>
                                      </div>
                                        </td>
                                </tr>
                                <?php $j++?>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
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
  function edittran(id,typ){
    $("#mytranModal").modal('show');
    $("#trnid").val(id);
    let x = document.getElementById('type');
    for(i=0; i<x.length; i++){
      if(x.options[i].value == typ){
        x.options[i].selected = true;
      }
    }
  }
</script>
    <script type="text/javascript">
  $(document).ready(function(){
    $("#acoff").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
@endsection
