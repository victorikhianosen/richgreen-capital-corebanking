@extends('layout.app')
@section('title')
    Manage Expenses
@endsection
@section('pagetitle')
Manage Expenses
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                          @can('create expenses')
                           <a href="{{route('expenses.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Expenses</a>
                         @endcan
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
                                <tr>
                                    <th>Sn</th>
                                    <th>Expenses Type</th>
                                    <th>Amount</th>
                                    <th>Expense Account</th>
                                    <th>Credit Account</th>
                                    <th>Posted By</th>
                                    <th>Purchase Date</th>
                                    <th>Date Posted</th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($expenses as $expense)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($expense->expensetype->name)}}</td>
                                    <td>{{number_format($expense->amount,2)}}</td>
                                    <td>{{$expense->expense_account}}</td>
                                    <td>{{$expense->credit_account}}</td>
                                    <td>{{$expense->user->last_name." ".$expense->user->first_name}}</td>
                                   <td>
                                    {{date('d-m-Y',strtotime($expense->date))}}
                                   </td>
                                    <td>{{date('d-m-Y',strtotime($expense->created_at))}}</td>
                                    
                                    {{--<td style="width: 15%">
                                       @can('view expenses')
                                       <a href="{{route('expenses.view',['id' => $expense->id])}}" class="btn vd_btn vd_bg-blue btn-sm">Edit</a>
                                      @endcan --}}
                                      {{-- @can('edit expenses')
                                        <a href="{{route('expenses.edit',['id' => $expense->id])}}" class="btn vd_btn vd_bg-blue btn-sm">Edit</a>
                                      @endcan --}}
                                      {{-- @can('view expenses')
                                      <a href="{{route('expenses.delete',['id' => $expense->id])}}" class="btn vd_btn vd_bg-red btn-sm" onclick="return confirm('are you sure you want to delete these record')">Delete</a>  
                                      @endcan 
                                    </td>--}}
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

  
 <!-- Modal -->
 <div class="modal fade" id="myModalfile" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header vd_bg-blue vd_white">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h4 class="modal-title" id="myModalLabel"></h4>
      </div>
      <div class="modal-body"> 
        <iframe src="" id="fileview" width="900" height="850" frameborder="0"></iframe>
      </div>
      
    </div>
    <!-- /.modal-content --> 
  </div>
  <!-- /.modal-dialog --> 
</div>
<!-- /.modal --> 
@endsection
@section('scripts')
    <script type="text/javascript">
    function viewfile(fl,ti){
      $("#myModalfile").modal('show');
      $("#fileview").attr('src',fl);
      $("#myModalLabel").text(ti+" File");
    }

  $(document).ready(function(){
    $("#acoff").dataTable({
      'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
@endsection
