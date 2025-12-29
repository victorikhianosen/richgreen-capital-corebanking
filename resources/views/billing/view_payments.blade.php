@extends('layout.app')
@section('title')
    Subcription Payments
@endsection
@section('pagetitle')
Subcription Payments
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        
                      </div>
                  <div class="panel-body">
                    @if (!empty($spye))
                    <div style="font-size: 14px;color:darkgreen">
                        <p>Subcription Type: {{$spye->subcription}}</p>
                        <p>Subcription Status: {{$spye->is_active == 1 ? 'Active' : 'Inactive'}}</p>
                       <p> Valid Till: {{date("d-M-Y",strtotime($spye->expiration_date))}}</p>
                      </div>
                    @endif
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Payment Date</th>
                                    <th>Amount Paid</th>
                                    <th>VAT</th>
                                    <th>Total Paid</th>
                                    <th>Subcription Expenses Account</th>
                                    <th>Credit Account</th>
                                    <th></th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($payments as $item)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{date("d-M-Y H:ia",strtotime($item->payment_date))}}</td>
                                    <td>{{number_format($item->amount_paid,2)}}</td>
                                    <td>{{$item->vat."%"}}</td>
                                    <td>{{number_format($item->total_paid,2)}}</td>
                                    <td>{{$item->expense_account}}</td>
                                    <td>{{$item->credit_account}}</td>
                                    <td>
                                        <a href="{{route('printreceipt',['id' => $item->id])}}" target="_blank" class="btn vd_btn vd_bg-twitter btn-sm">Print receipt</a>
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
