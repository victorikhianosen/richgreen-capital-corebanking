@extends('layout.app')
@section('title')
    Customer Balance Report
@endsection
@section('pagetitle')
Customer Balance Report
@endsection
@section('content')
  <div class="container">
    <?php 
         $getsetvalue = new \App\Models\Setting();
          $getcbo = !empty($_GET['fx_filter']) && $_GET['fx_filter'] != "Null" ?  \App\Models\Exchangerate::select('currency_symbol')->where('id',$_GET['fx_filter'])->first() : "";

     ?>
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                      <?php
                          $firsttrnx = \App\Models\SavingsTransaction::orderBy('created_at','asc')->first();
                        $datefrom = $firsttrnx->created_at;
                        $dateto = !empty($_GET['dateto']) ? $_GET['dateto']  : date('Y-m-d');
                      $filter = !empty($_GET['filter']) ? "?filter=".$_GET['filter'] : "";
                      ?>
                      <a href="{{ route('savingbalances.export') }}{{!empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '?dateto='.$dateto.'&fx_filter=Null' }}" class="btn btn-primary btn-sm"><span class="menu-icon"> <i class="fa fa-file-excel-o"></i> </span> Export Excel</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-offset-4 col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>

                    <div class="noprint" style="margin-bottom: 15px">
                      <form action="{{route('savingbalance_report')}}" method="get" onsubmit="thisForm()">
                        <input type="hidden" name="filter" value="true">
                        <input type="hidden" name="datefrom" value="{{date('Y-m-d',strtotime($datefrom))}}">
                        <table class="table table-bordered table-hover table-sm">
                          <thead>
                            <tr>
                              <th>Date</th>
                              <th></th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                               <td>
                                <div class="form-group">
                                  <input type="date" name="dateto" required id="" class="form-control" value="{{!empty($_GET['dateto']) ? $_GET['dateto'] : date('Y-m-d')}}">
                                </div>
                              </td>
                                    <td>
                                        <div class="form-group controls">
                                          <select name="fx_filter" class="width-90 form-control" autocomplete="off">
                                              <option {{empty($_GET['fx_filter']) ? "selected disabled" : ""}}>FX Exchange</option>
                                            <option value="Null" {{!empty($_GET['fx_filter']) && $_GET['fx_filter'] == "Null"  ? "selected" : "" }}>Naira</option>
                                              @foreach ($exrate as $item)
                                                  <option value="{{$item->id}}" {{!empty($_GET['fx_filter']) && $_GET['fx_filter'] == $item->id  ? "selected" : "" }}>{{$item->currency}}</option>
                                              @endforeach
                                          </select>
                                      </div>  
                                    </td>                    
                               <td>
                                 <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Generate Report</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('savingbalance_report')}}'">Reset</button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </form>
                    </div>
                   
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-condensed table-hover table-sm" id="acoff">
                            <thead>
                                <tr style="background-color: #D1F9FF">
                                    <th>Sn</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Account No</th>
                                    <th>Account Type</th>
                                    <th>Account Officer</th>
                                    <th>Phone No</th>
                                    <th>Balance  ({{empty($getcbo) ? "N" : $getcbo->currency_symbol}})</th>
                                   
                                </tr>
                            </thead>    
                            <tbody>
                                <?php  $i=0;?>
                                @foreach($customersbal as $key)
                                <?php 
                                $getsave = DB::table('savings')->select('account_balance','savings_product_id')
                                                            ->where('customer_id',$key->id)->first();

                                $getproname = DB::table('savings_products')->select('name')
                                                            ->where('id',$getsave->savings_product_id)->first();
                                                            
                                                $creditTrnx = \App\Models\SavingsTransaction::where('customer_id',$key->id)
                                                                ->where('status','approved')
                                                              ->whereIn('type',["deposit","credit","dividend","interest","fixed_deposit","fd_interest","rev_withdrawal"])
                                                              ->whereBetween('created_at',[$datefrom, $dateto])
                                                              ->sum('amount');

                                $debitTrnx = \App\Models\SavingsTransaction::where('customer_id',$key->id)
                                                                ->where('status','approved')
                                                              ->whereIn('type',["withdrawal","debit","rev_deposit"])
                                                              ->whereBetween('created_at',[$datefrom, $dateto])
                                                              ->sum('amount');
                               ?>
                                  <tr>
                                  <td>{{ $i+1 }}</td> 
                                      <td>{{ $key->first_name }}</td>
                                      <td> {{ $key->last_name }}</td> 
                                      <td>{{$key->acctno}}</td> 
                                      <td>{{!empty($getproname) ? $getproname->name : "N/A"}}</td>                       
                                       <td> {{!is_null($key->accountofficer) ? $key->accountofficer->full_name : "N/A"}}</td>
                                      <td> {{$key->phone}}</td>
                              
                                      <?php 
                                       $recnlin = $creditTrnx - $debitTrnx;
                                      ?>
                                      <td>
                                          {{number_format($recnlin,2)}}
                                      </td>
                                    
                                  </tr>
                                  <?php $i++; ?>
                                  @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row" style="float: right;margin-top:10px 0px">
                      {{$customersbal->appends(request()->query())->links()}}
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
    $("#acoff").dataTable({
    'paging':false,
      'lengthChange':false,
      'searching':false,
      'ordering':false,
      'info':false,
    });
  });
</script>
@endsection