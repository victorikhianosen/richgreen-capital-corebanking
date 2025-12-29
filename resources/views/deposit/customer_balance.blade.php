@extends('layout.app')
@section('title')
    Customer Balance
@endsection
@section('pagetitle')
Customer Balance
@endsection
@section('content')
  <div class="container">
    <?php 
         $getsetvalue = new \App\Models\Setting();
          $stat = !empty($_GET['status']) ? "&status=".$_GET['status'] : "";
     ?>
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                      <div class="row">
                         <div class="form-group col-sm-5 controls">
                                <select class="width-90 form-control" onchange="window.location.href=this.value" autocomplete="off">
                                    <option {{empty($_GET['fx_filter']) ? "selected disabled" : ""}}>Filter FX Exchange</option>
                                  <option value="{{route('savings.cutomers.balance')}}?fx_filter=Null{{$stat}}" {{!empty($_GET['fx_filter']) && $_GET['fx_filter'] == "Null"  ? "selected" : "" }}>Naira</option>
                                    @foreach ($exrate as $item)
                                        <option value="{{route('savings.cutomers.balance')}}?fx_filter={{$item->id}}{{$stat}}" {{!empty($_GET['fx_filter']) && $_GET['fx_filter'] == $item->id  ? "selected" : "" }}>{{$item->currency}}</option>
                                    @endforeach
                                </select>
                            </div>

                        <div class="col-sm-7">
                          <?php
                          $filter = !empty($_GET['filter']) ? "?filter=".$_GET['filter'] : "";
                          $search = !empty($_GET['search']) ? "&search=".$_GET['search'] : "";
                          //$filter."".$search
                          ?>
                          <a href="{{ route('customer.balance.export') }}{{!empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : ''}}" class="btn btn-primary btn-sm"><span class="menu-icon"> <i class="fa fa-file-excel-o"></i> </span> Export Excel</a>
                          <a href="{{route('customer.create')}}" class="btn btn-default btn-sm"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Customer</a>
                       </div>

                       </div>
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
                      <form action="{{route('savings.cutomers.balance')}}" method="get" onsubmit="thisForm()">
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
                                  <input type="text" name="search" required id="" class="form-control" value="{{!empty($_GET['search']) ? $_GET['search'] : ''}}">
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
                                    <th>Phone No.</th>
                                    <th>Currency</th>
                                    <th>Balance</th>
                                    @if (Auth::user()->account_type == "system")
                                    <th>Recon bal</th>
                                    @endif
                                    <th>Action</th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach($customersbal as $key)
                                <?php 
                                $getsave = DB::table('savings')->select('account_balance','savings_product_id')
                                                            ->where('customer_id',$key->id)->first();

                                $getproname = DB::table('savings_products')->select('name')
                                                            ->where('id',$getsave->savings_product_id)->first();
                                                            
                                                            $creditTrnx = \App\Models\SavingsTransaction::where('customer_id',$key->id)
                                                                ->where('status','approved')
                                                              ->whereIn('type',["deposit","credit","dividend","interest","fixed_deposit","fd_interest","rev_withdrawal"])
                                                              ->sum('amount');
                                $debitTrnx = \App\Models\SavingsTransaction::where('customer_id',$key->id)
                                                                ->where('status','approved')
                                                              ->whereIn('type',["withdrawal","debit","rev_deposit"])
                                                              ->sum('amount');

                                $exchg = \App\Models\Exchangerate::where('id',$key->exchangerate_id)->first();
                               ?>
                                  <tr>
                                  <td>{{ $i+1 }}</td> 
                                      <td>{{ $key->first_name }}</td>
                                      <td> {{ $key->last_name }}</td> 
                                      <td>{{$key->acctno}}</td> 
                                      <td>{{!empty($getproname) ? $getproname->name : "N/A"}}</td>                       
                                       <td> {{!is_null($key->accountofficer) ? $key->accountofficer->full_name : "N/A"}}</td>
                                      <td> {{$key->phone}}</td>
                                                            
                                      <td>
                                         {{empty($exchg) ? 'Naira' : ucwords($exchg->currency)}}
                                      </td>
                                      <td>
                                          {{number_format($getsave->account_balance,2)}}
                                      </td>
                                      @if (Auth::user()->account_type == "system")
                                      <?php 
                                       $recnlin = $creditTrnx - $debitTrnx;
                                      ?>
                                      <td>
                                          {{number_format($recnlin,2)}}
                                      </td>
                                      @endif
                                      <td>
                                          @can('view savings')
                                          <a href="{{route('saving.transaction.details',['id' => $key->id])}}" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Details</a>
                                          @endcan
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