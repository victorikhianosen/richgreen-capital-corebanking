@extends('layout.app')
@section('title')
    Fx Mgmt Report
@endsection
@section('pagetitle')
Fx Mgmt Report
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                     
                      </div>
                  <div class="panel-body">

                   <div class="noprint" style="margin-bottom: 15px">
                      <form action="{{route('reportfxmgt')}}" method="get" onsubmit="thisForm()">
                        <input type="hidden" name="filter" value="true">
                        <table class="table table-bordered table-hover table-sm">
                          <thead>
                            <tr>
                              <th>Date From</th>
                              <th>Date To</th>
                              <th>FX Type</th>
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
                                <div class="form-group" id="byreference">
                                    <select name="fxtyp" class="form-control"  id="">
                                        <option selected disabled>--select--</option>
                                        <option value="purchase">Purchase</option>
                                        <option value="sales">Sales</option>
                                    </select>
                                </div>
                              </td>
                              <td>
                                <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Generate</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('reportfxmgt')}}'">Reset</button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </form>
                    </div> 

                 @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                @if ($_GET["fxtyp"] == "purchase")
                                    <tr>
                                        <th>Sn</th>
                                        <th>Customer</th>
                                        <th>Purchased Rate</th>
                                        <th>Naira Amount</th>
                                        <th>Foreign Amount</th>
                                        <th>Currency</th>
                                        <th>Reference</th>
                                        <th>Payment Mode</th>
                                        <th>Trnx Date</th>
                                    </tr>
                                @elseif($_GET["fxtyp"] == "sales")
                                      <tr>
                                    <th>Sn</th>
                                    <th>Customer</th>
                                    <th>Purchased Rate</th>
                                    <th>Sales Rate</th>
                                    <th>Naira Amount</th>
                                    <th>Foreign Amount</th>
                                    <th>Currency</th>
                                    <th>Reference</th>
                                    <th>Payment Mode</th>
                                    <th>Trnx Date</th>
                                </tr>
                                @endif
                           
                              
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($sales as $sale)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($sale->customer)}}</td>
                                    <td>{{number_format($sale->purchase_exchange_rate,2)}}</td>
                                    <td>{{number_format($sale->sales_exchange_rate,2)}}</td>
                                    <td>{{number_format($sale->naria_amount,2)}}</td>
                                    <td>{{number_format($sale->foreign_amount,2)}}</td>
                                    <td>{{$sale->exchangerate->currency}}</td>
                                    <td>{{$sale->fx_reference}}</td>
                                    <td>{{$sale->payment_mode}}</td>
                                    <td>{{date("d-m-Y",strtotime($sale->tranx_date))}}</td>
                                </tr>
                                <?php $i++?>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                         @else
                      <div class="alert alert-info">Please Select a date range,FX Type and click on generate report</div>
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
    $("#acoff").dataTable({'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>


@endsection
