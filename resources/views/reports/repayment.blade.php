@extends('layout.app')
@section('title')
    Repayment Report
@endsection
@section('pagetitle')
Repayment Report
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
                        <div class="col-md-10 col-lg-10 col-sm-12">
                          @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                          <h3>Repayment Report for period: {{date("d M, Y",strtotime($_GET['datefrom']))." To ".date("d M, Y",strtotime($_GET['dateto']))}}</h3>
                          @endif
                        </div>
                        </div>
                        <div  style="margin-bottom: 15px">
                            <form action="{{route('report.loanrepayrept')}}" method="get" onsubmit="thisForm()">
                              <input type="hidden" name="filter" value="true">
                              <table class="table table-bordered table-hover table-sm">
                                <thead>
                                  <tr>
                                    <th>From Date</th>
                                    <th>To Date</th>
                                    <th></th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr>
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
                                      <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Search Report</button>
                                      <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.loanrepayrept')}}'">Reset</button>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </form>
                          </div>
                          @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                          <div class="table-responsive">
                            <table id="datalist" class="table table-bordered table-striped table-condensed table-hover">
                                <thead>
                                <tr style="background-color: #D1F9FF">
                                     <th>S/N</th>
                                        <th>Account Name</th>
                                        <th>Repayment Date</th>
                                        <th>Description</th>
                                       <th>Principal</th>
                                        <th>Interest</th>
                                          <th>Fee</th>
                                       <th>Penalty</th>
                                          <th>Total Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                  <?php $i=0; ?>
                                @foreach($data as $key)
                                    <tr>
                                         <td>{{ $i+1 }}</td>  
                                        <td>
                                          @if(!empty($key->customer))
                                          {{$key->customer->first_name}} {{$key->customer->last_name}}
                                       @endif
                                        </td>
                
                                        <td> {{date("d M, Y",strtotime($key->due_date))}}</td>
                                            <td> {{$key->description}}</td>
                                             <td> {{number_format($key->principal,2)}}</td>
                                              <td>{{number_format($key->interest,2)}} </td>
                                              <td>  {{number_format($key->fees,2)}}  </td>
                                        <td>  {{number_format($key->penalty,2)}} </td>
                                     
                                       <td>   {{number_format(($key->principal+$key->interest+$key->fees+$key->penalty),2)}}  </td>
                                           
                                    </tr>
                                    <?php $i++; ?>
                                @endforeach
                               
                                </tbody>
                            </table>
                        </div>
                          @else
                          <div class="alert alert-info">Please Select a date range and click on Search report</div>
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
    $("#datalist").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
@endsection