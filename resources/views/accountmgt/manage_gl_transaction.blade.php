@extends('layout.app')
@section('title')
    Manage General Ledger Transactions
@endsection
@section('pagetitle')
Manage General Ledger Transactions
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
                    <div class="row">
                      <div class="col-md-12 col-lg-12 col-sm-12">
                           @include('includes.success')
                           <form action="{{route('manage.gltrx')}}" method="get" onsubmit="thisForm()">
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
                                    <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Generate Record</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('manage.gltrx')}}'">Reset</button>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </form>
                      </div>
                      </div>
                      <div class="box-body table-responsive no-padding">
                        <table id="custmer" class="table table-bordered table-striped table-condensed table-hover table-sm">
                            <thead>
                                <tr style="background-color: #D1F9FF">
                                     <th>Sn</th>
                                         <th>GL Name</th>
                                         <th>GL Code</th>
                                         <th>Trx Type</th>
                                         <th>Reference</th>
                                         <th>Description</th>
                                        <th>Amount ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                         <th>Debit ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                        <th>Credit ({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                        <th>Transaction Date</th>
                                        {{-- <th>Balance({{$getsetvalue->getsettingskey('currency_symbol')}})</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i=0;
                                     $balance=0;
                                    ?>
                                    @foreach($data as $key)
                                        <tr>
                                          <td>{{$i+1}}</td>
                                            <td>{{!empty($key->generalledger) ? $key->generalledger->gl_name : "N/A"}}</td>
                                            <td>{{!empty($key->generalledger) ? $key->generalledger->gl_code : "N/A"}}</td>
                                            <td>{{$key->type}}</td>
                                            <td>{{$key->reference_no}}</td>
                                             <td>{{!is_null($key->notes) ? $key->notes : "N/A"}}</td>
                                             <td>{{number_format($key->amount,2)}}</td>

                                                @if ($key->generalledger->gl_type == "asset")

                                                @if($key->type == "credit")
                                                <?php $balance -= $key->amount;?>
                                                <td style="text-align:right">
                
                                                </td>
                                                <td style="text-align:right">
                                                    {{number_format($key->amount,2)}}
                                                </td>
                                              @else
                                            <?php $balance += $key->amount;?>
                                                <td style="text-align:right">
                                                    {{number_format($key->amount,2)}}
                                                </td>
                                                <td style="text-align:right">
                                                </td>
                                            @endif

                                            @elseif($key->generalledger->gl_type == "liability")

                                            @if($key->type == "credit")
                                                <?php $balance += $key->amount;?>
                                                <td style="text-align:right">
                
                                                </td>
                                                <td style="text-align:right">
                                                    {{number_format($key->amount,2)}}
                                                </td>
                                              @else
                                            <?php $balance -= $key->amount;?>
                                                <td style="text-align:right">
                                                    {{number_format($key->amount,2)}}
                                                </td>
                                                <td style="text-align:right">
                                                </td>
                                            @endif

                                            @elseif($key->generalledger->gl_type == "capital")

                                            @if($key->type == "credit")
                                            <?php $balance += $key->amount;?>
                                            <td style="text-align:right">
            
                                            </td>
                                            <td style="text-align:right">
                                                {{number_format($key->amount,2)}}
                                            </td>
                                          @else
                                        <?php $balance -= $key->amount;?>
                                            <td style="text-align:right">
                                                {{number_format($key->amount,2)}}
                                            </td>
                                            <td style="text-align:right">
                                            </td>
                                        @endif

                                        @elseif($key->generalledger->gl_type == "income")

                                        @if($key->type == "credit")
                                        <?php $balance += $key->amount;?>
                                        <td style="text-align:right">
        
                                        </td>
                                        <td style="text-align:right">
                                            {{number_format($key->amount,2)}}
                                        </td>
                                      @else
                                    <?php $balance -= $key->amount;?>
                                        <td style="text-align:right">
                                            {{number_format($key->amount,2)}}
                                        </td>
                                        <td style="text-align:right">
                                        </td>
                                        @endif
                                        @elseif($key->generalledger->gl_type == "expense")
                                        
                                        @if($key->type == "credit")
                                        <?php $balance -= $key->amount;?>
                                        <td style="text-align:right">
        
                                        </td>
                                        <td style="text-align:right">
                                            {{number_format($key->amount,2)}}
                                        </td>
                                      @else
                                    <?php $balance += $key->amount;?>
                                        <td style="text-align:right">
                                            {{number_format($key->amount,2)}}
                                        </td>
                                        <td style="text-align:right">
                                        </td>
                                    @endif
                                                @endif
                                            {{-- <td>{{ number_format($balance,2) }}</td> --}}
                                            <td>{{date("d M, Y H:ia",strtotime($key->created_at))}}</td>
                                        </tr>
                                        <?php $i++;?>
                                    @endforeach
                                    </tbody>
                        </table>
                        {{-- <div class="float-right mt-3">
                          {{$data->links()}}
                      </div> --}}
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