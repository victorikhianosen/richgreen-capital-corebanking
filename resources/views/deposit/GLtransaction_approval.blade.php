@extends('layout.app')
@section('title')
    GL Transaction Approval
@endsection
@section('pagetitle')
Manage GL Transaction Approval
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
                        <!--<h4>Transaction Approval</h4>-->
                      </div>
                  <div class="panel-body">

                    <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12">
                  @include('includes.success')
                  @include('includes.errors')
                    </div>
                    </div>
                    <div class="noprint" style="margin-bottom: 15px">
                        <form action="{{route('glapprovdata')}}" method="get" onsubmit="thisForm()">
                          <input type="hidden" name="filter" value="true">
                          <table class="table table-bordered table-hover table-sm">
                            <thead>
                              <tr>
                                <th>Date From</th>
                                <th>Date To</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
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
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Generate</button>
                                  <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href='{{route('approvdata')}}'">Reset</button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </form>
                      </div> 
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-condensed table-hover" style="width:100%" id="acoff">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                     <th><b>Account Name</b></th>
                                     <th><b>Account No</b></th>
                                     <th><b>Transaction</b></th>
                                     <th><b>Amount</b></th>
                                     <th><b>Reference</b></th>
                                     <th><b>RefLink</b></th>
                                     <th><b>Status</b></th>
                                     <th><b>Posted By</b></th>
                                     <th><b>Tranx Date</b></th>
                                    <th></th>
                                 </tr>
                            </thead>    
                            <tbody>
                                <?php $i=0;?>
                                @if (!empty($_GET['filter']) && $_GET['filter'] == true)
                                @foreach($transactions as $key)
                                <?php 
                                  $name =!empty($key->generalledger) ? ucwords($key->generalledger->gl_name) : "N/A";
                                 $acno = !empty($key->generalledger) ? $key->generalledger->gl_code : "N/A";

                                 $slip = !is_null($key->slip) ? $key->slip : "";

                                $dstingl = \App\Models\SavingsTransactionGL::where('slip',$key->reference_no)->first();
                               
                                $reference_no = $key->reference_no; 
                                 $rname = $dstingl ? ucwords($dstingl->generalledger->gl_name) : "N/A"; 
                                 $acctn =  $dstingl ? $dstingl->generalledger->gl_code : "N/A"; 
                                ?>
                                <tr>
                                      <td>{{ $i+1 }}</td>  
                                      <td> {{$name}}</td>
             
                                     <td> {{$acno}}</td>
                                     <td><span class="label label-default">{{$key->type}}</span></td>
                                   <td>{{number_format($key->amount,2)}} </td>
                                     <td>  {{$key->reference_no}}  </td>
                                      <td>  {{$slip}} </td>
                                     <td> 
                                       <a class="label {{$key->status == 'approved' ? 'label-success' : ($key->status == 'pending' ? 'label-warning' : 'label-danger' )}}">
                                         {{$key->status == 'approved' ? 'Successful' : ($key->status == 'pending' ? 'Pending' : $key->status )}}
                                     </a> 
                                     </td>
                                     
                                   <td>{{$key->initiated_by}}</td>
                                   
                                    <td>{{date("d-m-Y H:ia",strtotime($key->created_at))}}</td>
                                
                                     <td>
                                         @if ($key->initiated_by != Auth::user()->last_name." ".Auth::user()->first_name)
                                         <a href="#" title="View Transaction" onclick="openapproval('{{$rname}}','{{$acctn}}','{{$name}}','{{$acno}}','{{$key->type}}','{{number_format($key->amount,2)}}','{{$key->reference_no}}','{{date('d-m-Y H:ia',strtotime($key->created_at))}}','{{route('GLapproveTrnx',['ref' =>$key->reference_no])}}?btnType=approve','{{route('GLapproveTrnx',['ref' => $key->reference_no])}}?btnType=declined','{{$key->notes}}')" class="btn menu-icon vd_bd-blue vd_blue btn-sm"><i class="fa fa-eye"></i> </a>
                                    @endif
                                     </td>
                                </tr>
                                <?php $i++?>
                                @endforeach
                               @else
                               @foreach($transactions as $key)
                               <?php 
                                 $name =!empty($key->generalledger) ? ucwords($key->generalledger->gl_name) : "N/A";
                                 $acno = !empty($key->generalledger) ? $key->generalledger->gl_code : "N/A";

                                 $reference_no = $key->reference_no; 
                                $dstingl = \App\Models\SavingsTransactionGL::where('slip',$key->reference_no)->first();
                                
                                $slip = !is_null($key->slip) ? $key->slip : "";
                                 $rname = $dstingl ? ucwords($dstingl->generalledger->gl_name) : "N/A"; 
                                 $acctn =  $dstingl ? $dstingl->generalledger->gl_code : "N/A"; 
                               ?>
                               <tr>
                                     <td>{{ $i+1 }}</td>  
                                     <td> {{$name}}</td>
            
                                    <td> {{$acno}}</td>
                                    <td><span class="label label-default">{{$key->type}}</span></td>
                                  <td>{{number_format($key->amount,2)}} </td>
                                    <td>  {{$key->reference_no}}  </td>
                                     <td>  {{$slip}} </td>
                                    <td> 
                                      <a class="label {{$key->status == 'approved' ? 'label-success' : ($key->status == 'pending' ? 'label-warning' : 'label-danger' )}}">
                                        {{$key->status == 'approved' ? 'Successful' : ($key->status == 'pending' ? 'Pending' : $key->status )}}
                                    </a> 
                                    </td>
                                 
                                  <td>{{$key->initiated_by}}</td>
                                  
                                   <td>{{date("d-m-Y H:ia",strtotime($key->created_at))}}</td>
                               
                                    <td>
                                      @if ($key->initiated_by != Auth::user()->last_name." ".Auth::user()->first_name)
                                        <a href="#" title="View Transaction" onclick="openapproval('{{$rname}}','{{$acctn}}','{{$name}}','{{$acno}}','{{$key->type}}','{{number_format($key->amount,2)}}','{{$key->reference_no}}','{{date('d-m-Y H:ia',strtotime($key->created_at))}}','{{route('GLapproveTrnx',['ref' => $key->reference_no])}}?btnType=approve','{{route('GLapproveTrnx',['ref' => $key->reference_no])}}?btnType=declined','{{$key->notes}}')" class="btn menu-icon vd_bd-blue vd_blue btn-sm"><i class="fa fa-eye"></i> </a>
                                    @endif
                                      </td>
                               </tr>
                               <?php $i++?>
                               @endforeach
                               @endif
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

  <div class="modal fade" id="approvetrnx">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header vd_bg-blue vd_white">
                <button type="button" class="close vd_white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Approve Transaction</h4>
            </div>
           
           <div class="modal-body">
                <div class="row">
                  <div class="col-md-10 col-lg-10 col-sm-12">
                    <h3>Debit Account</h3>
                    <h4>Name: <span class="name"></span></h4>
                    <h4>Account Number: <span class="acno"></span></h4>
                    <h4>Trnx Type: <span class="trntype"></span></h4>
                    <h4>Amount: <span class="amt"></span></h4>
                    <h4>Reference: <span class="ref"></span></h4>
                    <h4>Tranx Date: <span class="txdt"></span></h4>
                    <h4>Narration: <span class="des"></span></h4>
                    <br>
                    
                    <div id="hiddestinaacct">
                        <h3>Credit Account</h3>
                       <h4>Reciptient: <span class="rect">N/A</span></h4>
                    <h4>Account No: <span class="acn">N/A</span></h4>
                  
                    </div>
                  </div>
                </div>
                
            </div>
        
            <div class="modal-footer  background-login">
              <button type="button" id="approvetnx" title="Approve Transaction" data-href="" class="btn menu-icon vd_bd-green vd_green btn-sm apprv"><i class="fa fa-check"></i> Approve</button>
              <button type="button" id="declinetnx" title="Decline Transaction" data-href="" class="btn menu-icon vd_bd-red vd_red btn-sm decli"><i class="fa fa-times"></i> Decline</button>

                {{-- <input type="submit"  class="btn btn-success btn-sm" name="btnType" id="btnssubmit" onsubmit="document.getElementById('btnssubmit').disabled = true" title="Approve Transaction" value="Approve">
                <input type="submit" class="btn btn-danger btn-sm" name="btnType" id="declbtn" onsubmit="document.getElementById('declbtn').disabled = true" title="Decline Transaction" value="Decline"> --}}
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
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
  
   //approve trnx
    $("#approvetnx").click(function(e){
      let url = $("#approvetnx").data('href');

       $("#approvetnx").attr('disabled',true);

      $("#approvetrnx").modal('hide');
        $.ajax({
        url: url,
        method: 'get',
        beforeSend:function(){
          $(".loader").css('visibility','visible');
          $(".loadingtext").text('Please Wait...');
        },
        success:function(data){
          if(data.status == 'success'){
            $(".loader").css('visibility','hidden');
          toastr.success(data.msg);
          window.location.reload();
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
    });
    
    //decline trnx
    $("#declinetnx").click(function(e){
      
      let url = $("#declinetnx").data('href');
      $("#declinetnx").attr('disabled',true);

      $("#approvetrnx").modal('hide');
      
        $.ajax({
        url: url,
        method: 'get',
        beforeSend:function(){
          $(".loader").css('visibility','visible');
          $(".loadingtext").text('Please Wait...');
        },
        success:function(data){
          if(data.status == 'success'){
            $(".loader").css('visibility','hidden');
          toastr.success(data.msg);
           window.location.reload();
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
    });
  
  });
</script>

<script>
  function openapproval(rept,acct,name,acno,typ,amount,ref,da,apurl,dcurl,decs){
      $("#approvetrnx").modal('show');
      $(".name").text(name);
      $(".acno").text(acno);
      $(".trntype").text(typ);
      $(".amt").text(amount);
      $(".ref").text(ref);
      $(".txdt").text(da);
       $(".rect").text(rept == "" ? "N/A" : rept);
      $(".acn").text(acct == "" ? "N/A" : acct);;
      $(".apprv").attr('data-href',apurl);
      $(".decli").attr('data-href',dcurl);
      $(".des").text(decs);
      
  }
</script>
@endsection