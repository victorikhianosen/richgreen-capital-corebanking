@extends('layout.app')
@section('title')
    Payroll Template
@endsection
@section('pagetitle')
Payroll Template
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                    <div style="text-align: right">
                      <button type="button" onclick="document.getElementById('addnw').style.display='block';" class="btn btn-primary btn-sm">Add New</button>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>
                    <div id="addnw" style="margin-bottom: 15px;display:none;">
                      <form action="{{route('payroll.store.template')}}" method="post" onsubmit="thisForm()">
                       @csrf
                        <input type="hidden" name="methodtype" value="store">
                        <table class="table table-bordered table-hover table-sm">
                          <thead>
                            <tr>
                              <th>Name</th>
                              <th>Position</th>
                              <th>Status</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>
                                <div class="form-group">
                                  <input type="text" name="name" required id="" class="form-control" value="{{old('name')}}">
                                </div>
                              </td>
                              <td>
                                <div class="form-group">
                                  <select name="position" required id="">
                                    <option selected disabled>Select...</option>
                                    <option value="1">Addition</option>
                                    <option value="2">Deduction</option>
                                  </select>
                                </div>
                              </td>
                              <td>
                                <div class="form-group">
                                  <select name="template_status" required id="">
                                    <option selected disabled>Select...</option>
                                    <option value="1">Default</option>
                                    <option value="2">Editable/deletable</option>
                                  </select>
                                </div>
                              </td>
                              <td>
                                <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Save Record</button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </form>
                    </div>
                    <div class="row">
                      <div class="col-md-6 col-lg-6 col-sm-12">
                        <div class="panel widget">
                          <div class="panel-heading vd_bg-blue">
                            <h3 class="panel-title">Addition</h3>
                          </div>
                          <div class="panel-body">
                           <div class="row">
                            @foreach ($adddata as $item)
                            <div class="col-md-8 col-lg-8 col-sm-8">
                             <div class="form-group">
                              <input type="text" readonly value="{{$item->name}}">
                             </div>
                            </div>
                            <div class="col-md-4 col-lg-4 col-sm-4">
                              @if ($item->status == '2')
                                  <a href="javascript:void(0)" onclick="document.getElementById('shwaddit{{$item->id}}').style.display='block'" class="text-primary"><i class="fa fa-edit" style="font-size: 20px"></i> </a>
                                  <a href="{{route('payroll.delete.template',['id' => $item->id])}}" class="text-danger" onclick="return confirm('are you sure you want to delete these record')">
                                    <i class="fa fa-trash-o" style="font-size: 20px"></i>
                                   </a>
                              @endif
                            </div>
                             <div class="row">
                              <div class="col-sm-12" id="shwaddit{{$item->id}}" style="margin-top:2px;display:none;">
                                <form action="{{route('payroll.store.template')}}" method="post" onsubmit="thisForm()">
                                  @csrf
                                  <input type="hidden" name="methodtype" value="edit">
                                  <input type="hidden" name="pid" value="{{$item->id}}">
                                  <div class="form-group">
                                    <input type="text" name="name" required id="" class="form-control" value="{{$item->name}}">
                                  </div>
                                  <div class="form-group">
                                    <select name="position" required id="">
                                      <option selected disabled>Select...</option>
                                      <option value="1" {{$item->position == '1' ? 'selected' : ''}}>Addition</option>
                                      <option value="2" {{$item->position == '2' ? 'selected' : ''}}>Deduction</option>
                                    </select>
                                  </div>
                                  <div class="form-group">
                                    <select name="template_status" required id="">
                                      <option selected disabled>Select...</option>
                                      <option value="1" {{$item->status == '1' ? 'selected' : ''}}>Default</option>
                                      <option value="2" {{$item->status == '2' ? 'selected' : ''}}>Editable/deletable</option>
                                    </select>
                                  </div>
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Update</button>
                                  <a href="javascript:void(0)" onclick="document.getElementById('shwaddit{{$item->id}}').style.display='none'" class="text-primary">Reset</a>
                                </form>
                              </div>
                             </div>
                            @endforeach
                           </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6 col-lg-6 col-sm-12">
                        <div class="panel widget">
                          <div class="panel-heading vd_bg-red">
                            <h3 class="panel-title">Deductions</h3>
                          </div>
                          <div class="panel-body">
                            <div class="row">
                              @foreach ($deddata as $item)
                            <div class="col-md-8 col-lg-8 col-sm-8">
                              <div class="form-group">
                                <input type="text" readonly value="{{$item->name}}">
                               </div>
                            </div>
                            <div class="col-md-4 col-lg-4 col-sm-4">
                              @if ($item->status == '2')
                              <a href="javascript:void(0)" onclick="document.getElementById('shwded{{$item->id}}').style.display='block'" class="text-primary"><i class="fa fa-edit" style="font-size: 20px"></i> </a>
                                  <a href="{{route('payroll.delete.template',['id' => $item->id])}}" class="text-danger" onclick="return confirm('are you sure you want to delete these record')">
                                    <i class="fa fa-trash-o" style="font-size: 20px"></i>
                                   </a>
                              @endif
                            </div>
                             <div class="row">
                              <div class="col-sm-12" id="shwded{{$item->id}}" style="margin-top:2px;display:none;">
                                <form action="{{route('payroll.store.template')}}" method="post" onsubmit="thisForm()">
                                  @csrf
                                  <input type="hidden" name="methodtype" value="edit">
                                  <input type="hidden" name="pid" value="{{$item->id}}">
                                  <div class="form-group">
                                    <input type="text" name="name" required id="" class="form-control" value="{{$item->name}}">
                                  </div>
                                  <div class="form-group">
                                    <select name="position" required id="">
                                      <option selected disabled>Select...</option>
                                      <option value="1" {{$item->position == '1' ? 'selected' : ''}}>Addition</option>
                                      <option value="2" {{$item->position == '2' ? 'selected' : ''}}>Deduction</option>
                                    </select>
                                  </div>
                                  <div class="form-group">
                                    <select name="template_status" required id="">
                                      <option selected disabled>Select...</option>
                                      <option value="1" {{$item->status == '1' ? 'selected' : ''}}>Default</option>
                                      <option value="2" {{$item->status == '2' ? 'selected' : ''}}>Editable/deletable</option>
                                    </select>
                                  </div>
                                  <button type="submit" class="btn btn-success btn-sm" id="btnsetsubmit">Update</button>
                                  <a href="javascript:void(0)" onclick="document.getElementById('shwded{{$item->id}}').style.display='none'" class="text-danger">Reset</a>
                                </form>
                              </div>
                             </div>
                            @endforeach
                            </div>
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
<script>
  $(document)
</script>