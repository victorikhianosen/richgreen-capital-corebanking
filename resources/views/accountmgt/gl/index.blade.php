@extends('layout.app')
@section('title')
    General Ledger
@endsection
@section('pagetitle')
Manage General Ledger
@endsection
@section('content')
<?php
    $getsetvalue = new \App\Models\Setting();
   ?>
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                         @can('upload general ledger')
                           <a href="{{route('ac.category.batchupload')}}?type=gl" class="btn btn-danger btn-sm"><span class="menu-icon"> <i class="fa fa-upload"></i> </span>Upload General Ledger</a>
                           @endcan
                         @can('create general ledger')
                           <a href="{{route('gl.create')}}" class="btn btn-default btn-sm"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add General Ledger</a>
                          @endcan
                       </div>
                      </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                  @include('includes.success')
                    </div>
                    </div>     
                    <form action="{{route('gl.actideactve')}}" method="post" onsubmit="return performAction(this)">
                      @csrf
                      <div class="text-center" style="margin: 7px 0px">
                      <input type="submit" class="btn btn-success btn-sm" name="cmdupdatestatus" value="Activate Account(s)">
                      <input type="submit" class="btn btn-danger btn-sm" name="cmdupdatestatus" value="Deactivate Account(s)">
                      </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Sn</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Account Type</th>
                                    <th>Account Category</th>
                                    <th>Balance</th>
                                    @if (Auth::user()->account_type == "system")
                                    <th>Recon bal</th>
                                    @endif
                                    <th>Status</th>
                                    <th></th>
                                 </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;
                                $recnbal=0;
                            
                                // Fetch all transaction sums in one query and index them by general_ledger_id
                                $transactions = DB::table('savings_transaction_g_l_s')
                                    ->select('general_ledger_id', 'type', DB::raw('SUM(amount) as total'))
                                    ->where('status', 'approved')
                                    ->whereIn('general_ledger_id', $gls->pluck('id')) // Avoids multiple queries
                                    ->groupBy('general_ledger_id', 'type')
                                    ->get()
                                    ->groupBy('general_ledger_id');

                                ?>
                                @foreach ($gls as $item)
                                <tr>
                                     <td><input type="checkbox" name="glid[]" style="cursor: pointer"  value="{{$item->id}}" class="checkcust" id=""></td>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($item->gl_name)}}</td>
                                    <td>{{$item->gl_code}}</td>
                                    <td>{{ucwords($item->gl_type)}}</td>
                                    <td>{{!empty($item->accountcategories) ? ucwords($item->accountcategories->name) : "" }}</td>
                                    <td>{{number_format($item->account_balance,2)}}</td>

                                    @if (Auth::user()->account_type == "system")
                                    <?php 
                                    //          $lbcrtrnx = DB::table('savings_transaction_g_l_s')->where(['general_ledger_id' => $item->id,'type' => 'credit','status' => 'approved'])
                                    //                                                   ->sum('amount');
                                                                                     
                                    // $lbdbtrnx = DB::table('savings_transaction_g_l_s')->where(['general_ledger_id' => $item->id,'type' => 'debit','status' => 'approved'])
                                    //                                                 ->sum('amount');


                              //  $lbtrnx = $item->gl_type == "capital" || $item->gl_type == "income" || $item->gl_type == "liability" ? $lbcrtrnx - $lbdbtrnx : $lbdbtrnx - $lbcrtrnx;
                                $ledgerTransactions = $transactions[$item->id] ?? collect([]);
                                        $lbcrtrnx = $ledgerTransactions->where('type', 'credit')->sum('total');
                                        $lbdbtrnx = $ledgerTransactions->where('type', 'debit')->sum('total');
                        
                                        // Calculate balance
                                        $lbtrnx = in_array($item->gl_type, ['capital', 'income', 'liability']) ? $lbcrtrnx - $lbdbtrnx : $lbdbtrnx - $lbcrtrnx;
                                          $recnbal = $lbtrnx;
                                ?>
                                    <td>{{number_format($recnbal,2)}}</td>
                                    @endif

                                    <td>
                                      <span class="badge {{$item->status ? 'vd_bg-green' : 'vd_bg-red'}}">{{$item->status ? "Active" : "Not Active"}}</span></td>
                                    </td>
                                    
                                    <td>
                                      @can('upload general ledger')
                                      @if ($item->status)
                                      <a href="{{route('gl.status',['glid' => $item->id,'status' => '0'])}}" title="Disable" class="btn menu-icon vd_bd-red vd_red btn-sm"><i class="fa fa-eye-slash"></i> </a>
                                      @else
                                      <a href="{{route('gl.status',['glid' => $item->id,'status' => '1'])}}" title="Enable" class="btn menu-icon vd_bd-green vd_green btn-sm"><i class="fa fa-check"></i> </a>
                                      @endif
                                      @endcan
                                      @can('edit general ledger')
                                        <a href="{{route('gl.edit',['id' => $item->id])}}" title="Edit" class="btn menu-icon vd_bd-blue vd_blue btn-sm"><i class="fa fa-pencil"></i> </a>
                                       @endcan
                                       @can('delete general ledger')
                                        <a href="{{route('gl.delete',['id' => $item->id])}}" title="Delete" class="btn menu-icon vd_bd-red vd_red btn-sm" onclick="return confirm('Are you sure you want to delete the record')"><i class="fa fa-times"></i> </a>
                                       @endcan
                                    </td>
                                </tr>
                                <?php $i++?>
                                @endforeach
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
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
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
