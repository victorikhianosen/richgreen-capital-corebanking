@extends('layout.app')
@section('title')
    Customer Details
@endsection
@section('pagetitle')
Customer Details
@endsection
@section('content')
  <div class="container">
    <?php
    $getsetvalue = new \App\Models\Setting();
   ?>
    @inject('getloan', 'App\Http\Controllers\ReportsController')

    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                  </div>
                  <div class="panel-body">
                    
                      <div class="noprint" style="margin-bottom: 15px">
                        <form action="{{route('report.customerdetail')}}" method="get" onsubmit="thisForm()">
                          <input type="hidden" name="filter" value="true">
                          <table class="table table-bordered table-hover table-sm">
                            <thead>
                              <tr>
                                <th>Search Customer</th>
                                <th>Officers</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>
                                  <div class="form-group">
                                    <select name="cusmername" class="form-control cutsm" data-placeholder="Select Customer" required>
                                      <option selected disabled>Select</option>
                                      @foreach ($customers as $item)
                                          <option value="{{$item->id}}" {{!empty($_GET['cusmername']) && $_GET['cusmername'] == $item->id ? "selected" : "" }}>{{ucwords($item->first_name." ".$item->last_name)}}</option>
                                      @endforeach
                                     
                                    </select>
                                  </div>
                                </td>
                                <td>
                                  <div class="form-group">
                                    <select name="officer" class="form-control offier" data-placeholder="Select Account Officer" required>
                                        <option value="all" selected> All</option>
                                       @foreach ($officers as $item)
                                          <option value="{{$item->id}}" {{!empty($_GET['officer']) && $_GET['officer'] == $item->id ? "selected" : "" }}>{{ucwords($item->full_name)}}</option>
                                       @endforeach
                                    </select>
                                  </div>
                                </td>
                                <td>
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Search Records</button>
                                  <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.customerdetail')}}'">Reset</button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </form>
                      </div>
                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                      <div class="box-body table-responsive no-padding">
                        <table id="custmer" class="table table-bordered table-striped table-condensed table-hover table-sm">
                            <thead>
                                <tr style="background-color: #D1F9FF">
                                     <th>Sn</th>
                                         <th>Name</th>
                                         <th>Gender</th>
                                        <th>Account Officer</th>
                                         <th>Phone No</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i=0;?>
                                    @foreach($data as $key)
                                        <tr>
                                          <td>{{$i+1}}</td>
                                            <td>{{ucwords($key->first_name." ".$key->last_name)}}</td>
                                            <td>{{$key->gender}}</td>
                                             <td> {{!empty($key->accountofficer) ? ucwords($key->accountofficer->full_name) : "N/A"}}</td>
                                             
                                             <td> {{$key->phone}} </a></td>
                                             <td>
                                              @if ($key->status == '2')  
                                                  <span class="badge vd_bg-black">Closed</span>
                                              @endif
                                              @if ($key->status == '0')  
                                              <span class="badge vd_bg-red">Pending</span>
                                          @endif
                                          @if ($key->status == '1')  
                                            <span class="badge vd_bg-green">Active</span>
                                        @endif
                                            </td>
                                           
                                        </tr>
                                        <?php $i++;?>
                                    @endforeach
                                    </tbody>
                        </table>
            
                    </div>
                      @else
                      <div class="alert alert-info">Please select a Customer and click on Search Record button</div>
                  @endif
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
    $(".offier").select2();
    $(".cutsm").select2();

    $("#custmer").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
<script>
  //  function printsection() {
  //   document.getElementById("noprint").style.display='none';
  // var divContents = document.getElementById("printdiv").innerHTML;
  // var a = window.open('', '', 'height=500, width=500');
  // a.document.write('<html>');
  // a.document.write('<body >');
  // a.document.write(divContents);
  // a.document.write('</body></html>');
  // a.document.close();
  // a.print();
  // }
</script>
@endsection