@extends('layout.app')
@section('title')
Inward Transaction
@endsection
@section('pagetitle')
Inward Transaction
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <h4>Inward Transaction</h4>
                  </div>
                  <div class="panel-body">  
                    <div class="noprint" style="margin-bottom: 15px">
                      
                    </div> 
                          <div class="table-responsive">
                            <table id="success" class="table table-bordered table-striped table-condensed table-hover">
                                <thead>
                                <tr style="background-color: #D1F9FF">
                                     <th>S/N</th>
                                     <th><b>Account Name</b></th>
                                     <th><b>Account Number</b></th>
                                     <th><b>Amount</b></th>
                                     <th><b>Source</b></th>
                                     <th><b>Reference</b></th>
                                     <th><b>Transaction Date</b></th>
                                    </tr>
                                </thead>
                                <tbody>
                                  <?php $i=0; ?>

                                @foreach($paylods as $payld)
                                   <?php  $key = json_decode($payld->body,true); ?>
                                    <tr>
                                         <td>{{ $i+1 }}</td>  
                                        <td>{{ ucwords($key["accountName"])}}</td>
                                        <td> {{$key["accountNumber"]}}</td>
                                        <td>{{number_format($key["amount"],2)}} </td>
                                        <td>{{$key["sourceAccountName"]."-".$key["sourceAccountNumber"]}}  </td>
                                        <td> {{$key["sessionId"]}}</td>
                                     
                                       <td>{{date("d-m-Y h:ia",strtotime($payld->created_at))}}</td>
                                           
                                    </tr>
                                    <?php $i++; ?>
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
    $("#success").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  
  });
</script>
@endsection