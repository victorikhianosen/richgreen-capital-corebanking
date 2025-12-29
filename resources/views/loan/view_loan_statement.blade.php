@extends('layout.app')
@section('title')
{{!empty($_GET['filter']) ? $loan->customer->last_name." ".$loan->customer->first_name."--".$_GET['londetails'] : "Loan Statement"}}  
@endsection
@section('pagetitle')
Loan Statement
@endsection

<?php
 $getsetvalue = new \App\Models\Setting();
 
?>
     @inject('getloan', 'App\Http\Controllers\LoanController')

@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                          @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                            <a href="{{route('print.loan.statement',['id' => $loan->customer->id])}}?loanid={{$loan->id}}&ty=lst" class="btn btn-primary btn-sm"  target="_blank">Print Statement</a>
                              <a href="{{route('download.loan.statement',['id' => $loan->customer->id])}}?loanid={{$loan->id}}&ty=lst" class="btn btn-default btn-sm" target="_blank">Download Statement</a>
                         @endif
                          </div>
                         
                      </div>
                  <div class="panel-body">
                    @include('includes.errors')
                     <div class="noprint" style="margin-bottom: 15px">
                        <form action="{{route('loan.statement')}}" method="get" onsubmit="thisForm()">
                          <input type="hidden" name="filter" value="true">
                          <table class="table table-bordered table-hover table-sm">
                            <thead>
                              <tr>
                                <th>Loan Account Number</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                              
                                <td>
                                  <div class="form-group">
                                    <input type="text" name="londetails" required id="" class="form-control" value="{{!empty($_GET['londetails']) ? $_GET['londetails'] : ''}}">
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

                    {{-- <div class="row">
                        <div class="col-md-12 col-lg-12 col-sm-12">
                            @include('includes.success')
                        </div>
                    </div> --}}

                  @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                      <div style="margint: 30px 0px;float: left">
                              Customer Name: <b>{{$loan->customer->last_name." ".$loan->customer->first_name}}</b><br />
                              Customer Account No: <b>{{$loan->customer->acctno}} </b><br />
                              Loan Account Number: <b>{{!empty($_GET['londetails']) ? $_GET['londetails'] : ''}} </b>
                    </div>
                  @endif
                         
                     <div class="table-responsive">
                        <table class="table table-striped table-sm table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr style="background-color: #F2F8FF">
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>    
                            <tbody>

                                @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                             
                                        <?php 
                                            $i=0;
                                            $totinster = $getloan->loan_total_interest($loan->id); 
                                                $totpricpla = $loan->principal + $totinster;
                                            $hasDebit = \App\Models\LoanRepayment::where('type','debit')->exists();
                                            $balance = $hasDebit ? 0 : $totpricpla;
                                        ?>
                                @if (!$hasDebit)
                                    <tr>
                                        <td>{{ date('d-m-Y H:ia', strtotime($loan->created_at)) }}</td>
                                        <td>Loan Disbursed</td>
                                        <td>{{ number_format($totpricpla, 2) }}</td>
                                        <td></td>
                                        <td>{{ number_format($balance, 2) }}</td>
                                    </tr>
                                @endif
                    @foreach($payments as $key)
                        <tr>
                            @if ($hasDebit)
                               <td>{{date('d-m-Y H:ia',strtotime($key->created_at))}}</td>
                               <td>
                                   {{$key->notes}}
                               </td>
                                 @if ($key->type == 'debit')
                                    <?php $balance += $key->amount;?>
                                    <td>{{number_format($key->amount,2)}}</td> 
                                    <td> </td>  
                                 @else
                                    <?php $balance -= $key->amount;?>
                                    <td> </td> 
                                    <td>{{number_format($key->amount,2)}}</td> 
                                 @endif
                              
                                @else

                                <td>{{date('d-m-Y H:ia',strtotime($key->created_at))}}</td>
                                    <td></td>

                                    @if ($key->type == 'credit')
                                    <?php $balance -= $key->amount;?>
                                        <td> </td> 
                                        <td>{{number_format($key->amount,2)}}</td>  
                                    @else
                                    <?php $balance += $totpricpla;?>
                                    <td>
                                        {{number_format($totpricpla,2)}}
                                    </td> 
                                     <td> </td>
                                    @endif
                                @endif
                            <td>{{number_format($balance,2)}}</td>
                        </tr>
                    @endforeach

                     @else
                         
                     @endif
                            </tbody>
                        </table>
                    </div>

                    

                    {{-- <div class="row justify-content-center">
                      {{$loans->appends(request()->query())->links()}}
                    </div> --}}
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
  function edittran(id,typ){
    $("#mytranModal").modal('show');
    $("#trnid").val(id);
    let x = document.getElementById('type');
    for(i=0; i<x.length; i++){
      if(x.options[i].value == typ){
        x.options[i].selected = true;
      }
    }
  }
</script>
    <script type="text/javascript">
  $(document).ready(function(){
    $("#acoff").dataTable({
    'pageLength':50,
    'dom': 'Bfrtip',
      buttons: ['csv']
    });
  });
</script>
@endsection
