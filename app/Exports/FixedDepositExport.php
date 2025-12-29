<?php

namespace App\Exports;

use App\Models\Customer;
use App\Models\FixedDeposit;
use App\Models\Accountofficer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class FixedDepositExport implements FromView
{

    use Exportable;
    
   // public $branch;
    public $datavalue = null;
    public $filter;
    public $fxfilter;
    public $status;
    public $dateto;
    
    public function __construct($datavalue,$filter,$fxfilter,$status,$dateto)
    {
        // $this->branch = $branch;
        $this->filter = $filter;
        $this->datavalue = $datavalue;
        $this->fxfilter = $fxfilter;
        $this->status = $status;
        $this->dateto = $dateto;
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

                $data = FixedDeposit::with([
                'customer:id,first_name,last_name,acctno,phone,state', 
                'accountofficer:id,full_name',
                'fixed_deposit_product:id,name'
                 ])->select('id','fixed_deposit_code','principal','interest_method','interest_rate','interest_period','release_date','maturity_date','status','customer_id','accountofficer_id','fixed_deposit_product_id','enable_withholding_tax','withholding_tax','created_at')
                       ->when($this->filter == true, function ($query) {
                        $query->whereDate('created_at',$this->dateto);
                    })->whereIn('customer_id',$fx)
                    ->get();
            }else{

                $data = FixedDeposit::with([
                'customer:id,first_name,last_name,acctno,phone,state', 
                'accountofficer:id,full_name',
                'fixed_deposit_product:id,name'
                ])->select('id','fixed_deposit_code','principal','interest_method','interest_rate','interest_period','release_date','maturity_date','status','customer_id','accountofficer_id','fixed_deposit_product_id','enable_withholding_tax','withholding_tax','created_at')
                   ->when($this->filter == true && !empty($this->dateto), function ($query) {
                    $query->whereDate('created_at',$this->dateto);
                })->whereIn('customer_id',$fx)
                ->where('status', request()->status)
                ->get();
                
            }

        
       // Log::info($data);
         return view('investment.export_fd',[
                    'fixds' => $data
                ]);
       
    }
}
