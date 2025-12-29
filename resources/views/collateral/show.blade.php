@extends('layout.app')
@section('title')
    Collateral Details
@endsection
@section('pagetitle')
Collateral Details
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: end">
                       <a href="{{!empty($_GET['return_url']) ? url($_GET['return_url']) : route('colla.index')}}" class="btn btn-danger"><span class="menu-icon"> <i class="fa fa-angle-left"></i> </span> Back</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                                <div class="box-header with-border">
                                    {{-- <h3 class="box-title">{{ $collateral->name }} {{ $collateral->last_name }}</h3> --}}
            
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <tr>
                                            <td>Loan Code</td>
                                            <td><a href="{{route('loan.show',['id' => $collateral->loan_id])}}" title="view loan details"> {{!empty($collateral->loan) ? $collateral->loan->loan_code :"N/A" }}</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Customer</td>
                                            <td>
                                                    <a href="{{route('customer.view',['id' => $collateral->customer_id])}}" title="view customers details"> {{$collateral->customer->first_name}} {{$collateral->customer->last_name}}</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Collateral Type</td>
                                            <td>
                                                    {{$collateral->collateraltype->name}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Value</td>
                                            <td>{{ number_format($collateral->value,2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Status</td>
                                            <td>
                                                {{str_replace("_"," ",$collateral->status)}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Serial Number</td>
                                            <td>{{ $collateral->serial_number }}</td>
                                        </tr>
                                        <tr>
                                            <td>Model Name</td>
                                            <td>{{ $collateral->model_name }}</td>
                                        </tr>
                                        <tr>
                                            <td>Model Number</td>
                                            <td>{{ $collateral->model_number }}</td>
                                        </tr>
                                        <tr>
                                            <td>Manufacture Date</td>
                                            <td>{{ date("d M, Y",strtotime($collateral->manufacture_date)) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Date</td>
                                            <td>{{ date("d M, Y",strtotime($collateral->date)) }}</td>
                                        </tr>
                                        <tr>
                                            <td>File</td>
                                            <td>
                                                <ul class="" style="font-size:12px; padding-left:10px">
                
                                                    @foreach((array)$collateral->files as $value)
                                                        <li><a href="{{asset($value)}}"
                                                               target="_blank" download="">View/Download</a></li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                        </tr>
                                       
                                        <tr>
                                            <td>Updated At</td>
                                            <td>{{ date('d M, Y',strtotime($collateral->updated_at)) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            
                        </div>
                        <div class="col-md-6">
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Note</h3>
                
                                    <div class="box-tools pull-right">
                
                                    </div>
                                </div>
                                <div class="box-body">
                                    @if(!empty($collateral->photo))
                                        <img src="{{asset($collateral->photo)}}" width="80" height="80" class="img-responsive"/><br><br>
                                    @endif
                                    {!!   $collateral->notes !!}
                                </div>
                            </div>
                        </div>
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
<script>
  $(document).ready(function(){
    
  });
</script>
@endsection