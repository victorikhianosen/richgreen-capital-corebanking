@inject('getloan', 'App\Http\Controllers\LoanController')

 <table class="table table-striped table-sm table-bordered table-condensed table-hover" id="acoff">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Acct No</th>
                                    <th>Phone</th>
                                     <th>District</th>
                                    <th>Principal</th>
                                    <th>Interest </th>
                                    <th>Released</th>
                                    <th>Maturity</th>
                                    <th>Officer</th>
                                    <th>Principal Paid </th>
                                    <th>Principal Unpaid</th>
                                    <th>Interest Paid </th>
                                     <th>Interest Unpaid</th>
                                    <th>Total Due</th>
                                    <th>Paid Amount </th>
                                    <th>Balance</th>
                                    <th>Branch</th>
                                    <th>Loan Product</th>
                                     <th>Loan Equity</th>
                                     <th>Description </th>
                                      <th>Loan Purpose</th>
                                    <th>Status</th>
                                </tr>
                            </thead>    
                            <tbody>

                                @inject('getloan', 'App\Http\Controllers\LoanController')

                                @foreach ($loans as $item)
                                <tr>
                                    <td>{{$item->loan_code}}</td>
                                    <td><a href="{{route('customer.view',['id' => $item->customer->id])}}">{{ucwords($item->customer->last_name." ".$item->customer->first_name)}}</a></td>
                                    <td><a href="{{route('saving.transaction.details',['id' => $item->customer->id])}}">{{$item->customer->acctno}}</a></td>
                                    <td>{{$item->customer->phone}}</td>
                                    <td>{{$item->customer->state}}</td>
                                    <td>{{number_format($item->principal)}}</td>
                                    <td>{{number_format($getloan->loan_total_interest($item->id))}}</td>
                                    <td>{{date("d-m-Y",strtotime($item->release_date))}}</td>
                                    <td>{{date("d-m-Y",strtotime($item->maturity_date))}}</td>
                                    <td>{{!is_null($item->accountofficer) ? $item->accountofficer->full_name : "N/A"}}</td>
                                    <td>{{number_format($getloan->loan_paid_item($item->id))}}</td>

                                 <td>
                                      @php
                                          $unpaid_pricp = $item->principal - $getloan->loan_paid_item($item->id); 
                                      @endphp
                                      {{ number_format($unpaid_pricp,2)}}
                                    </td>

                                    <td>{{number_format($getloan->loan_interest_paid_item($item->id))}}</td>

                                    
                                     <td>
                                      @php
                                          $unpaid_intr = $getloan->loan_total_interest($item->id) - $getloan->loan_interest_paid_item($item->id); 
                                      @endphp
                                      {{ number_format($unpaid_intr,2)}}
                                    </td>

                                    <td>
                                        <a href="{{route('loan.show',['id' => $item->id])}}">
                                            @if($item->override)
                                                <s>{{number_format($getloan->loan_total_due_amount($item->id))}}</s><br>
                                                {{number_format($item->balance,2)}}
                                            @else
                                                {{number_format($getloan->loan_total_due_amount($item->id))}}
                                            @endif
                                          </a>
                                    </td>
                                    <td>{{number_format($getloan->loan_total_paid($item->id))}}</td>
                                    <td>{{number_format($getloan->loan_total_balance($item->id))}}</td>
                                    <td>{{!is_null($item->branch) ? $item->branch->branch_name : "N/A"}}</td>
                                    <td><span class="text-info">{{ $item->loan_product->name }} </span></td>
                                    <td>{{number_format($item->equity)}}</td>
                                    <td>{{$item->description}}</td>
                                    <td>{{$item->purpose}}</td>
                                    <td>
                                        @if($item->maturity_date < date("Y-m-d") && $getloan->loan_total_balance($item->id) > 0)
                                        <span class="label label-danger">Past Maturity</span> 
                                    
                                        @elseif($item->status == 'pending')
                                             
                                               <span class="label label-warning">Pending Approval</span> 
                                            
                                              @elseif($item->status == 'approved')
                                              
                                                  <span class="label label-info">Awaiting Disbursement</span>
                                              
                                             @elseif($item->status == 'disbursed')
                                             
                                              <span class="label label-success">Active</span>
                                            
                                             @elseif($item->status == 'declined')
                                             
                                                 <span class="label label-danger">Declined</span>
                                             
                                             @elseif($item->status == 'withdrawn')
                                             
                                                 <span class="label label-danger">Withdrawn</span>
                                            
                                             @elseif($item->status == 'written_off')
                                             
                                                 <span class="label label-danger">Written Off</span>
                                            
                                             @elseif($item->status == 'closed')
                                             
                                                 <span class="badge vd_bg-black">Closed</span>
                                             
                                             @elseif($item->status == 'pending_reschedule')
                                             
                                                 <span class="label label-warning">Pending Reschedule </span>
                                            
                                             @elseif($item->status == 'rescheduled')
                                             
                                                 <span class="label label-info">Rescheduled</span>
                                                 
                                             @else
                                             {{ucwords($item->provision_type)}}
                                        @endif
                                    </td>
                                   
                                </tr>
                          
                                @endforeach
                            </tbody>
                        </table>