@extends('layout.app')
@section('title')
    Manage Other Income 
@endsection
@section('pagetitle')
Manage Other Income 
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                    <div class="panel-heading">
                        <div style="text-align: end">
                          @can('create expenses')
                           <a href="{{route('income.create')}}" class="btn btn-default"><span class="menu-icon"> <i class="fa fa-plus"></i> </span> Add Other Income</a>
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
                                    <th>Income Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Posted By</th>
                                    <th>Branch</th>
                                    <th>File</th>
                                    <th>Created On</th>
                                    <th></th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @foreach ($incomes as $income)
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{ucwords($income->otherincomtype->name)}}</td>
                                    <td>{{number_format($income->amount)}}</td>
                                    <td>{{Str::limit($income->notes,'80','...')}}</td>
                                    <td>{{$income->user->last_name." ".$income->user->first_name}}</td>
                                    <td>{{$income->branch->branch_name}}</td>
                                    <td>
                                      @if (!is_null($income->files))
                                      <a href="javascript:void(0)" onclick="viewfile('{{asset($income->files)}}','{{ucwords($income->otherincomtype->name)}}')">View File</a>
                                      @endif
                                     </td>
                                    <td>{{date('d/m/Y',strtotime($income->income_date))}}</td>
                                    
                                    <td style="width: 15%">
                                      {{-- @can('view expenses')
                                       <a href="{{route('expenses.view',['id' => $expense->id])}}" class="btn vd_btn vd_bg-blue btn-sm">Edit</a>
                                      @endcan --}}
                                      @can('edit other income')
                                        <a href="{{route('income.edit',['id' => $income->id])}}" class="btn vd_btn vd_bg-blue btn-sm">Edit</a>
                                      @endcan
                                      @can('delete other income')
                                      <a href="{{route('income.delete',['id' => $income->id])}}" class="btn vd_btn vd_bg-red btn-sm" onclick="return confirm('are you sure you want to delete these record')">Delete</a>  
                                      @endcan
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
