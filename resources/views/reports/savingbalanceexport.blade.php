 <table class="table table-striped table-bordered table-condensed table-hover table-sm" id="acoff">
                            <thead>
                                <tr style="background-color: #D1F9FF">
                                    <th>Sn</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Account No</th>
                                    <th>Account Type</th>
                                    <th>Account Officer</th>
                                    <th>Phone No</th>
                                    <th>Balance</th>
                                  
                                </tr>
                            </thead>    
                            <tbody>
                                <?php  $i=0;
                                       $firsttrnx = \App\Models\SavingsTransaction::orderBy('created_at','asc')->first();
                                    $datefrom = $firsttrnx->created_at;
                                ?>
                                @foreach($customersbal as $key)
                                <?php 
                                $getsave = DB::table('savings')->select('account_balance','savings_product_id')
                                                            ->where('customer_id',$key->id)->first();

                                $getproname = DB::table('savings_products')->select('name')
                                                            ->where('id',$getsave->savings_product_id)->first();
                                                            
                                                $creditTrnx = \App\Models\SavingsTransaction::where('customer_id',$key->id)
                                                                ->where('status','approved')
                                                              ->whereIn('type',["deposit","credit","dividend","interest","fixed_deposit","fd_interest","rev_withdrawal"])
                                                              ->whereBetween('created_at',[$datefrom, $dateto])
                                                              ->sum('amount');

                                $debitTrnx = \App\Models\SavingsTransaction::where('customer_id',$key->id)
                                                                ->where('status','approved')
                                                              ->whereIn('type',["withdrawal","debit","rev_deposit"])
                                                              ->whereBetween('created_at',[$datefrom, $dateto])
                                                              ->sum('amount');
                               ?>
                                  <tr>
                                  <td>{{ $i+1 }}</td> 
                                      <td>{{ $key->first_name }}</td>
                                      <td> {{ $key->last_name }}</td> 
                                      <td>{{$key->acctno}}</td> 
                                      <td>{{!empty($getproname) ? $getproname->name : "N/A"}}</td>                       
                                       <td> {{!is_null($key->accountofficer) ? $key->accountofficer->full_name : "N/A"}}</td>
                                      <td> {{$key->phone}}</td>
                              
                                      <?php 
                                       $recnlin = $creditTrnx - $debitTrnx;
                                      ?>
                                      <td>
                                          {{number_format($recnlin,2)}}
                                      </td>
                                    
                                  </tr>
                                  <?php $i++; ?>
                                  @endforeach
                            </tbody>
                        </table>