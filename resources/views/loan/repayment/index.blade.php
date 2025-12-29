@extends('layout.app')
@section('title')
    Loan repayment
@endsection
@section('pagetitle')
Loan repayment
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                          {{-- <a href="{{route('repay.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Repayments</a>--}}
                        </div>
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                  @include('includes.success')
                    </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                              <th>Sn</th>
                            <th>Collection Date</th>
                            <th>Customer Name</th>
                            <th>Collected By</th>
                            <th>Amount</th>
                            <th>Receipt</th>
                            </thead>    
                            <tbody>
                                <?php $i=0;
                                $getsetvalue = new \App\Models\Setting();
                                ?>
                                @foreach ($allpayments as $item)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{date("d M, Y",strtotime($item->collection_date))}}</td>
                                    <td>{{ucwords($item->customer->last_name)." ".ucwords($item->customer->first_name)}}</td>
                                    <td>{{!is_null($item->user_id) ? ucwords($item->user->last_name)." ".ucwords($item->user->first_name) : "N/A"}}</td>
                                    <td>{{$getsetvalue->getsettingskey('currency_symbol')."".number_format($item->amount)}}</td>
                                    {{-- <td>
                                      @can('update repayments')
                                           <a href="{{route('repay.edit',['id' => $item->id])}}" class="text-info">Edit</a>
                                      @endcan
                                      @can('delete repayments')
                                          |  <a href="{{route('repay.delete',['id' => $item->id])}}" class="text-danger" onclick="return confirm('Are you sure you want to delete these record')">Delete</a>
                                      @endcan
                                    </td> --}}
                                    <td>
                                        <a href="{{route('repay.print',['id' => $item->id])}}?loanid={{$item->loan_id}}" class="btn vd_btn vd_bg-twitter btn-sm" target="_blank">Print</a>
                                        <a href="{{route('repay.pdf',['id' => $item->id])}}?loanid={{$item->loan_id}}" class="btn vd_btn vd_bg-red btn-sm" target="_blank">PDF</a>
                                    </td>
                                </tr>
                                <?php $i++?>
                                @endforeach
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
    $("#acoff").dataTable({'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
@endsection
