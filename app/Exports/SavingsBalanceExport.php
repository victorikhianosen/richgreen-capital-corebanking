<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class SavingsBalanceExport implements FromView
{
    use Exportable;
    public $branch;
    public $datavalue; 
    public $filter;
    public $fxfilter;
    
    public function __construct($datavalue,$filter,$fxfilter)
    {
        $this->filter = $filter;
        $this->fxfilter = $fxfilter;
        $this->datavalue = $datavalue;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $cust = Customer::select('id','first_name','last_name','acctno','accountofficer_id','phone','exchangerate_id')
                                 ->where('exchangerate_id',$this->fxfilter)->get();
        
        return view('reports.savingbalanceexport',[
            'customersbal' => $cust,
            'dateto' =>  $this->datavalue
        ]);
    }
}
