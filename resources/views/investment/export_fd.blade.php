@inject('getloan', 'App\Http\Controllers\InvestmentController')

<table class="table table-striped table-sm table-bordered table-condensed table-hover" id="acoff">
    <thead>
        <tr>
          
            <th>Code</th>
            <th>Name</th>
            <th>Account No</th>
            <th>Phone</th>
             <th>Principal</th>
             <th>Interest </th>
             <th>Interest Rate</th>
             <th>Interest Method</th>
             <th>Withholding Tax</th>
            <th>Released</th>
            <th>Maturity</th>
            <th>Officer</th>
            <th>Fd Product</th>
            <th>Date Created</th>
        </tr>
    </thead>    
    <tbody>
      
        @foreach ($fixds as $item)
        <tr>
            <td>{{$item->fixed_deposit_code}}</td>
            <td>{{ucwords($item->customer->last_name." ".$item->customer->first_name)}}</td>
            <td>{{$item->customer->acctno}}</td>
            <td>{{$item->customer->phone}}</td>
            <td>{{number_format($item->principal)}}</td>
            <td>{{number_format($getloan->investment_total_interest($item->id))}}</td>
            <td>{{$item->interest_rate."% /".$item->interest_period}}</td>
            <td>{{ucfirst($item->interest_method)}}</td>
            <td>{{$item->enable_withholding_tax == '1' ? $item->withholding_tax : '0'}}%</td>
            <td>{{date("d-m-Y",strtotime($item->release_date))}}</td>
            <td>{{date("d-m-Y",strtotime($item->maturity_date))}}</td>
            <td>{{!is_null($item->accountofficer) ? $item->accountofficer->full_name : "N/A"}}</td>
            <td>{{ $item->fixed_deposit_product->name }}</td>
            <td>{{ date('d-m-Y h:ia',strtotime($item->created_at)) }}</td>
          
        </tr>
    
        @endforeach
    </tbody>
</table>