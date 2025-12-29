@extends('layout.app')
@section('title')
    Account Management Report
@endsection
@section('pagetitle')
Account Management Report
@endsection
@section('content')
  <div class="container">
    <?php
    $getsetvalue = new \App\Models\Setting();
   ?>
    @inject('getloan', 'App\Http\Controllers\ReportsController')

    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                  </div>
                  <div class="panel-body">
                     @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                    <div class="my-5">
                     <h3 class="text-center">Account mgmt Report For Period: {{date("d M, Y",strtotime($_GET['datefrom']))." To ".date("d M, Y",strtotime($_GET['dateto']))}}</h3>
                      @if ($_GET['fetchby'] == "byref")
                      <h4 class="text-center">Reference: {{$_GET['reference']}}</h4>
                      @elseif($_GET['fetchby'] == "bygl")
                      <h4 class="text-center">GL Code: {{$_GET['glcode']}} ({{ucwords($gl->gl_name ?? '')}})</h4>
                       @endif
                  
                       <h3 class="mb-3">Opening Balance: <b>{{number_format($opbal,2)}}</b></h3>
                     
                    </div>
                    @endif
                      <div class="noprint" style="margin-bottom: 15px">
                        <form action="{{route('report.accountsmgmt')}}" method="get" onsubmit="thisForm()">
                          <input type="hidden" name="filter" value="true">
                          <table class="table table-bordered table-hover table-sm">
                            <thead>
                              <tr>
                                <th>Search By Reference or GL Code</th>
                                <th>Date From</th>
                                <th>Date To</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>
                                    <div class="form-group">
                                        <select name="fetchby" class="form-control" required onchange="if(this.value=='byref'){document.getElementById('byreference').style.display='block';document.getElementById('byglcode').style.display='none'}
                                        else if(this.value=='bygl'){document.getElementById('byglcode').style.display='block';document.getElementById('byreference').style.display='none';}else{document.getElementById('byreference').style.display='none';document.getElementById('byglcode').style.display='none'}">
                                            <option selected disabled>Select Search Options</option>
                                            <option value="byref">Search By Reference</option>
                                            <option value="bygl">Search By GL Code</option>
                                        </select>
                                      </div>
                                  <div class="form-group" id="byreference" style="display: none">
                                    <input type="text" name="reference" placeholder="Enter Reference Number" class="form-control">
                                  </div>
                                  <div class="form-group" id="byglcode" style="display: none">
                                    <input type="number" name="glcode" placeholder="Enter GL Code" class="form-control">
                                  </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                      <input type="date" name="datefrom" required id="" class="form-control" value="{{!empty($_GET['datefrom']) ? $_GET['datefrom'] : ''}}">
                                    </div>
                                  </td>
                                  <td>
                                    <div class="form-group">
                                      <input type="date" name="dateto" required id="" class="form-control" value="{{!empty($_GET['dateto']) ? $_GET['dateto'] : ''}}">
                                    </div>
                                  </td>
                                <td>
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Generate Report</button>
                                  <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('report.accountsmgmt')}}'">Reset</button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </form>
                      </div>
                      @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                      
                      <div class="box-body table-responsive no-padding">
                          @if (count($data) > 0)
                        <table id="custmer" class="table table-bordered table-striped table-condensed table-hover table-sm">
                            <thead>
                                <tr style="background-color: #D1F9FF">
                                     <th>Sn</th>
                                         <th>Transaction Date</th>
                                         <th>Reference</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Posted By</th>
                                        <th>Approved By</th>
                                         <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Balance({{$getsetvalue->getsettingskey('currency_symbol')}})</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i=0;
                                    $balance = $opbal;
                                    ?>
                                    
                                    @foreach($data as $key)
                                    <?php
                                        $reference_no = !is_null($key->slip) ? $key->slip : $key->reference_no; 
                                    ?>
                                    <tr>
                                      <td>{{$i+1}}</td>
                                        <td>{{date("d M, Y   h:ia",strtotime($key->created_at))}}</td>
                                        <td>{{$key->reference_no}}</td>
                                         <td>{{!is_null($key->notes) ? $key->notes : "N/A"}}</td>
                                            <td>
                                                 <a class="label {{$key->status == 'approved' ? 'label-success' : ($key->status == 'pending' ? 'label-warning' : 'label-danger' )}}">
                                                 {{$key->status == 'approved' ? 'Successful' : ($key->status == 'pending' ? 'Pending' : $key->status )}}
                                             </a>
                                            </td>
                                            <td>{{ucwords($key->initiated_by)}}</td>
                                            @if ($key->generalledger->gl_type == "asset")

                                            @if($key->type == "credit")
                                                @if($key->status == 'approved')
                                                <?php $balance -= $key->amount;?>
                                                <td style="text-align:right">
                
                                                </td>
                                                <td style="text-align:right">
                                                    {{number_format($key->amount,2)}}
                                                </td>
                                                @else
                                                <?php $balance -= 0;?>
                                                <td style="text-align:right">
                
                                                </td>
                                                <td style="text-align:right">
                                                    {{number_format($key->amount,2)}}
                                                </td>
                                              @endif
                                          @else
                                          @if($key->status == 'pending' || $key->status == 'declined')
                                          <?php $balance += 0;?>
                                           <td style="text-align:right">
                                                 {{number_format($key->amount,2)}}
                                             </td>
                                             <td style="text-align:right">
                                                 
                                             </td>
                                           @else
                                        <?php $balance += $key->amount;?>
                                            <td style="text-align:right">
                                                {{number_format($key->amount,2)}}
                                            </td>
                                            <td style="text-align:right">
                                            </td>
                                        @endif
                                        @endif

                                        @elseif($key->generalledger->gl_type == "liability")

                                        @if($key->type == "credit")
                                          @if($key->status == 'approved')
                                            <?php $balance += $key->amount;?>
                                            <td style="text-align:right">
            
                                            </td>
                                            <td style="text-align:right">
                                                {{number_format($key->amount,2)}}
                                            </td>
                                            @else
                                            <?php $balance += 0;?>
                                            <td style="text-align:right">
            
                                            </td>
                                            <td style="text-align:right">
                                                {{number_format($key->amount,2)}}
                                            </td>
                                            @endif
                                          @else
                                          @if($key->status == 'pending' || $key->status == 'declined')
                                          <?php $balance -= 0;?>
                                           <td style="text-align:right">
                                                 {{number_format($key->amount,2)}}
                                             </td>
                                             <td style="text-align:right">
                                                 
                                             </td>
                                           @else
                                        <?php $balance -= $key->amount;?>
                                            <td style="text-align:right">
                                                {{number_format($key->amount,2)}}
                                            </td>
                                            <td style="text-align:right">
                                            </td>
                                        @endif
                                        @endif

                                        @elseif($key->generalledger->gl_type == "capital")

                                        @if($key->type == "credit")
                                         @if($key->status == 'approved')
                                        <?php $balance += $key->amount;?>
                                        <td style="text-align:right">
        
                                        </td>
                                        <td style="text-align:right">
                                            {{number_format($key->amount,2)}}
                                        </td>
                                        @else
                                         <?php $balance += 0;?>
                                        <td style="text-align:right">
        
                                        </td>
                                        <td style="text-align:right">
                                            {{number_format($key->amount,2)}}
                                        </td>
                                        @endif
                                      @else
                                      @if($key->status == 'pending' || $key->status == 'declined')
                                      <?php $balance -= 0;?>
                                       <td style="text-align:right">
                                             {{number_format($key->amount,2)}}
                                         </td>
                                         <td style="text-align:right">
                                             
                                         </td>
                                       @else
                                    <?php $balance -= $key->amount;?>
                                        <td style="text-align:right">
                                            {{number_format($key->amount,2)}}
                                        </td>
                                        <td style="text-align:right">
                                        </td>
                                    @endif
                                    @endif

                                    @elseif($key->generalledger->gl_type == "income")

                                    @if($key->type == "credit")
                                        @if($key->status == 'approved')
                                    <?php $balance += $key->amount;?>
                                    <td style="text-align:right">
    
                                    </td>
                                    <td style="text-align:right">
                                        {{number_format($key->amount,2)}}
                                    </td>
                                    @else
                                     <?php $balance += 0;?>
                                    <td style="text-align:right">
    
                                    </td>
                                    <td style="text-align:right">
                                        {{number_format($key->amount,2)}}
                                    </td>
                                    @endif
                                  @else
                                  @if($key->status == 'pending' || $key->status == 'declined')
                                  <?php $balance -= 0;?>
                                   <td style="text-align:right">
                                         {{number_format($key->amount,2)}}
                                     </td>
                                     <td style="text-align:right">
                                         
                                     </td>
                                   @else
                                <?php $balance -= $key->amount;?>
                                    <td style="text-align:right">
                                        {{number_format($key->amount,2)}}
                                    </td>
                                    <td style="text-align:right">
                                    </td>
                                    @endif
                                    @endif

                                    @elseif($key->generalledger->gl_type == "expense")
                                    
                                    @if($key->type == "credit")
                                        @if($key->status == 'approved')
                                    <?php $balance -= $key->amount;?>
                                    <td style="text-align:right">
    
                                    </td>
                                    <td style="text-align:right">
                                        {{number_format($key->amount,2)}}
                                    </td>
                                    @else
                                     <?php $balance -= 0;?>
                                    <td style="text-align:right">
    
                                    </td>
                                    <td style="text-align:right">
                                        {{number_format($key->amount,2)}}
                                    </td>
                                    @endif
                                  @else
                                  @if($key->status == 'pending' || $key->status == 'declined')
                                  <?php $balance += 0;?>
                                   <td style="text-align:right">
                                         {{number_format($key->amount,2)}}
                                     </td>
                                     <td style="text-align:right">
                                         
                                     </td>
                                   @else 
                                <?php $balance += $key->amount;?>
                                    <td style="text-align:right">
                                        {{number_format($key->amount,2)}}
                                    </td>
                                    <td style="text-align:right">
                                    </td>
                                @endif
                                @endif
                                            @endif
                                        <td>{{ number_format($balance,2) }}</td>
                                        <td>
                                          <a href="javascript:void(0)" onclick="viewledgerdetails('{{route('ledgerdetails')}}?ref={{$reference_no}}')" title="View Details" class="btn menu-icon vd_bd-blue vd_blue btn-sm"><i class="fa fa-eye"></i> </a>
                                          </td>
                                    </tr>
                                    <?php $i++;?>
                                @endforeach
                                   
                                   
                                </tbody>
                        </table>
                             @else
                                
                                    <p class="alert alert-info text-center">Invalid GL Code or reference number</p>
                                  
                            @endif
                    </div>
                      @else
                      <div class="alert alert-info">Please enter a GL Code or reference number then pick a date range then click on generate report button</div>
                  @endif
                  </div>
                </div>
                <!-- Panel Widget reference_no--> 
              </div>
              <!-- col-md-12 --> 
            </div>
            <!-- row -->
  </div>

  <!-- Modal -->
 <div class="modal fade" id="sdmyModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header vd_bg-blue vd_white">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h4 class="modal-title" id="myModalLabel">Ledger Details</h4>
      </div>
      <div class="modal-body"> 
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-condensed table-hover table-sm">
            <thead>
              <tr>
                <th>Account Name</th>
                <th>Account No</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Description</th>
              </tr>
            </thead>
              <tbody id="legdgerdetails">
              </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer background-login">
        <button type="button" class="btn vd_btn vd_bg-grey" data-dismiss="modal">Close</button>
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
  $(document).ready(function(){
    $("#custmer").dataTable({
    'pageLength':25,
    'dom': 'Bfrtip',
      buttons: [ 'copy', 'csv', 'print','pdf']
  });
  });
</script>
<script>
  //  function printsection() {
  //   document.getElementById("noprint").style.display='none';
  // var divContents = document.getElementById("printdiv").innerHTML;
  // var a = window.open('', '', 'height=500, width=500');
  // a.document.write('<html>');
  // a.document.write('<body >');
  // a.document.write(divContents);
  // a.document.write('</body></html>');
  // a.document.close();
  // a.print();
  // }
  
  function viewledgerdetails(durl){
    let tabdata = "";
    $.ajax({
      url: durl,
      method: "get",
      beforeSend:function(){
        $(".loader").css('visibility','visible');
          $(".loadingtext").text('Please wait...');
      },
      success:function(data){
        if(data.status == 'success'){
            $(".loader").css('visibility','hidden');
          toastr.success(data.msg);
          $("#sdmyModal").modal("show");
      
              $("#legdgerdetails").html(data.data);
             
          }else{
            toastr.error(data.msg);
            $(".loader").css('visibility','hidden');
           return false;
           }
        },
        error:function(xhr,status,errorThrown){
            $(".loader").css('visibility','hidden');
            toastr.error('Error '+errorThrown);
          return false;
        }
      });
  
  }

</script>
@endsection