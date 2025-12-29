<?php

namespace App\Exports;

use App\Models\Loan;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;

class LoanExport implements FromView
{

     use Exportable;

     // public $datavalue = null;
    public $filter;
    public $fxfilter;
     public $status;
    
    public function __construct($filter,$fxfilter,$status)
    {
        // $this->branch = $branch;
        $this->filter = $filter;
       // $this->datavalue = $datavalue;
        $this->fxfilter = $fxfilter;
         $this->status = $status;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view() :View
    {
              $fx=array();

        $fxcust = Customer::select('id')->where('exchangerate_id',$this->fxfilter)->get();
        foreach($fxcust as $fxc){
            $fx[] = $fxc->id;
        }

          if(empty($this->status)){
            $data = Loan::whereIn('customer_id',$fx)->get();
          }else{
            $data = Loan::whereIn('customer_id',$fx)
                        ->where('status', $this->status)->get();
          }

        return view('loan.export_loan',[
                    'loans' => $data
                ]);                            

    }
}
