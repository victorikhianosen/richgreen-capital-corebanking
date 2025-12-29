<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class CustomersBalanceExport implements FromView
{

    use Exportable;
    public $branch;
    public $datavalue; 
    public $filter;
    public $fx_filter;
    
    public function __construct($branch,$datavalue,$filter,$fx_filter)
    {
        $this->branch = $branch;
        $this->filter = $filter;
        $this->fx_filter = $fx_filter;
        $this->datavalue = $datavalue;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
      $filter = $this->fx_filter == "Null" ? null : $this->fx_filter;
     
             $cust = Customer::select('id','first_name','last_name','acctno','accountofficer_id','phone','exchangerate_id')
                            ->when(request()->filter == true, function ($query) {
                                $query->where(function ($q) {
                                    $q->where('first_name', 'like', '%' . request()->search . '%')
                                    ->orWhere('last_name', 'like', '%' . request()->search . '%')
                                    ->orWhere('acctno', 'like', '%' . request()->search . '%');
                                });
                            })->when(!is_null($filter), function ($query) use ($filter) {
                                    $query->where('exchangerate_id', $filter);
                            })
                            ->get();
        
        return view('deposit.exportbalance',[
            'customersbal' => $cust
        ]);
    }
}
