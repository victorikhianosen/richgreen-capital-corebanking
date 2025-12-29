@extends('layout.app')
@section('title')
    Transfer Report
@endsection
@section('pagetitle')
Transfer Report
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <h4>Transaction</h4>
                  </div>
                  <div class="panel-body">   
                    <div class="noprint" style="margin-bottom: 15px">
                      <form action="{{route('report.trnsfdata')}}" method="get" onsubmit="thisForm()">
                        <input type="hidden" name="filter" value="true">
                        <table class="table table-bordered table-hover table-sm">
                          <thead>
                            <tr>
                              <th>Search By Reference or Account Number</th>
                              <th>Date From</th>
                              <th>Date To</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>
                                <div class="form-group" id="byreference">
                                  <input type="text" name="reference" placeholder="Enter Reference or Account Number" class="form-control">
                                </div>
                              </td>
                              <td>
                                  <div class="form-group">
                                    <input type="date" name="datefrom" required id="" class="form-control" value="{{!empty($_GET['datefrom']) ? $_GET['datefrom'] : ''}}">
                                  </div>
                                </td>
                                <td>
                                  <div class="form-group">
                                    <input type="date" name="dateto" required id="" class="form-control" value="{{!empty($_GET['dateto']) ? $_GET['dateto'] : ''}}">
                                  </div>
                                </td>
                              <td>
                                <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Generate</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.trnsfdata')}}'">Reset</button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </form>
                    </div> 
                          <div class="table-responsive">
                            <table id="success" class="table table-bordered table-striped table-condensed table-hover">
                                <thead>
                                <tr style="background-color: #D1F9FF">
                                     <th>S/N</th>
                                     <th><b>Account Name</b></th>
                                     <th><b>Account Number</b></th>
                                     <th><b>Transaction</b></th>
                                     <th><b>Amount</b></th>
                                     <th><b>Reference</b></th>
                                     <th><b>Status</b></th>
                                     <th><b>Transaction Date</b></th>
                                    </tr>
                                </thead>
                                <tbody>
                                  <?php $i=0; ?>
                                  @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                                @foreach($data as $key)
                                    <tr>
                                         <td>{{ $i+1 }}</td>  
                                        <td>
                                          {{!empty($key->customer) ? ucwords($key->customer->last_name." ".$key->customer->first_name) : "N/A"}}
                                        </td>
                
                                        <td> {{!empty($key->customer) ? $key->customer->acctno : "N/A"}}</td>
                                             <td><b>{{$key->type}}</b></td>
                                              <td>{{number_format($key->amount,2)}} </td>
                                              <td>  {{$key->reference_no}}  </td>
                                        <td> 
                                          <a class="label {{$key->status == 'approved' ? 'label-success' : ($key->status == 'pending' ? 'label-warning' : 'label-danger' )}}">
                                            {{$key->status == 'approved' ? 'Successful' : ($key->status == 'pending' ? 'Pending' : $key->status )}}
                                        </a> 
                                        </td>
                                     
                                       <td>{{date("d-m- H:ia",strtotime($key->created_at))}}</td>
                                           
                                    </tr>
                                    <?php $i++; ?>
                                @endforeach
                                @else
                                @foreach($data as $key)
                                <tr>
                                     <td>{{ $i+1 }}</td>  
                                     <td>
                                      {{!empty($key->customer) ? ucwords($key->customer->last_name." ".$key->customer->first_name) : "N/A"}}
                                    </td>
            
                                    <td> {{!empty($key->customer) ? $key->customer->acctno : "N/A"}}</td>
                                         <td><b>{{$key->type}}</b></td>
                                          <td>{{number_format($key->amount,2)}} </td>
                                          <td>  {{$key->reference_no}}  </td>
                                    <td> 
                                      <a class="label {{$key->status == 'approved' ? 'label-success' : ($key->status == 'pending' ? 'label-warning' : 'label-danger' )}}">
                                        {{$key->status == 'approved' ? 'Successful' : ($key->status == 'pending' ? 'Pending' : $key->status )}}
                                    </a> 
                                    </td>
                                 
                                   <td>{{date("d-m-Y H:ia",strtotime($key->created_at))}}</td>
                                </tr>
                                <?php $i++; ?>
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
<script type="text/javascript">
  $(document).ready(function(){
    $("#success").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  
  });
</script>
@endsection