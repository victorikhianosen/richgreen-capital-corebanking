 <table class="table table-striped table-bordered table-condensed table-hover table-sm" id="acoff">
                            <thead>
                                <tr>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Account No</th>
                                    <th>Account Type</th>
                                    <th>Account Officer</th>
                                    <th>Phone No.</th>
                                    <th>Currency</th>
                                    <th>Balance</th>
                                    @if (Auth::user()->account_type == "system")
                                    <th>Recon bal</th>
                                    @endif
                                </tr>
                            </thead>    
                            <tbody>
                               
                                @foreach($customersbal as $key)
                                <?php 
                                $getsave = DB::table('savings')->select('account_balance','savings_product_id')
                                                            ->where('customer_id',$key->id)->first();

                                $getproname = DB::table('savings_products')->select('name')
                                                            ->where('id',$getsave->savings_product_id)->first();
                                                            
                                                            $creditTrnx = \App\Models\SavingsTransaction::where('customer_id',$key->id)
                                                                ->where('status','approved')
                                                              ->whereIn('type',["deposit","credit","dividend","interest","fixed_deposit","fd_interest","rev_withdrawal"])
                                                              ->sum('amount');
                                $debitTrnx = \App\Models\SavingsTransaction::where('customer_id',$key->id)
                                                                ->where('status','approved')
                                                              ->whereIn('type',["withdrawal","debit","rev_deposit"])
                                                              ->sum('amount');

                                  $exchg = \App\Models\Exchangerate::where('id',$key->exchangerate_id)->first();
                               ?>
                                  <tr>
                                      <td>{{ $key->first_name }}</td>
                                      <td> {{ $key->last_name }}</td> 
                                      <td>{{$key->acctno}}</td> 
                                      <td>{{!empty($getproname) ? $getproname->name : "N/A"}}</td>                       
                                       <td> {{!is_null($key->accountofficer) ? $key->accountofficer->full_name : "N/A"}}</td>
                                      <td> {{$key->phone}}</td>
                                        <td>
                                         {{empty($exchg) ? 'Naira' : ucwords($exchg->currency)}}
                                      </td>
                                      <td>
                                          {{number_format($getsave->account_balance,2)}}
                                      </td>
                                      @if (Auth::user()->account_type == "system")
                                      <?php 
                                       $recnlin = $creditTrnx - $debitTrnx;
                                      ?>
                                      <td>
                                          {{number_format($recnlin,2)}}
                                      </td>
                                      @endif
                                
                                  </tr>
                                  
                                  @endforeach
                            </tbody>
                        </table>