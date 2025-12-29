<?php
namespace App\Http\Traites;

use App\Models\Asset;
use App\Models\AssetValuation;
use App\Models\Capital;
use App\Models\Expenses;
use App\Models\Loan;
use App\Models\OtherIncome;
use App\Models\LoanRepayment;
use App\Models\LoanSchedule;
use App\Models\Payroll;
use App\Models\PayrollMeta;
use App\Models\SavingsTransaction;
use Illuminate\Support\Facades\Auth;

trait LoanTraite{

     //determine paid principal
     public function loan_paid_item($id, $item = 'principal', $date = '')

     {
 
         $loan = Loan::findorfail($id);
 
         $principal = 0;
 
         $interest = 0;
 
         $penalty = 0;
 
         $fees = 0;
 
         if (empty($date)) {
 
             $schedules = $loan->schedules;
 
         } else {
 
             $schedules = LoanSchedule::where('loan_id', $id)->where('due_date', '<=', $date)->get();
 
         }
 
      //  if(count($loan) > 0 && count($loan->loan_product) > 0){
 
           if(count((array)$loan) > 0 && count((array)$loan->loan_product) > 0){
 
             $repayment_order = (array)$loan->loan_product->repayment_order;
 
         }else{
 
             $repayment_order = [];
 
         }
 
         foreach ($schedules as $schedule) {
 
             $payments = LoanRepayment::where('loan_id', $id)->where('status','1')->where('due_date', $schedule->due_date)->sum('amount');
 
             if ($payments > 0) {
 
                 foreach ($repayment_order as $order) {
 
                     if ($payments > 0) {
 
                         if ($order == 'interest') {
 
                             if ($payments > $schedule->interest) {
 
                                 $interest = $interest + $schedule->interest;
 
                                 $payments = $payments - $schedule->interest;
 
                             } else {
 
                                 $interest = $interest + $payments;
 
                                 $payments = 0;
 
                             }
 
                         }
 
                         if ($order == 'penalty') {
 
                             if ($payments > $schedule->penalty) {
 
                                 $penalty = $penalty + $schedule->penalty;
 
                                 $payments = $payments - $schedule->penalty;
 
                             } else {
 
                                 $penalty = $penalty + $payments;
 
                                 $payments = 0;
 
                             }
 
                         }
 
                         if ($order == 'fees') {
 
                             if ($payments > $schedule->fees) {
 
                                 $fees = $fees + $schedule->fees;
 
                                 $payments = $payments - $schedule->fees;
 
                             } else {
 
                                 $fees = $fees + $payments;
 
                                 $payments = 0;
 
                             }
 
                         }
 
                         if ($order == 'principal') {
 
                             if ($payments > $schedule->principal) {
 
                                 $principal = $principal + $schedule->principal;
 
                                 $payments = $payments - $schedule->principal;
 
                             } else {
 
                                 $principal = $principal + $payments;
 
                                 $payments = 0;
 
                             }
 
                         }
 
                     }
 
                 }
 
             }
 
             //apply remainder to principal
 
             $principal = $principal + $payments;
 
         }
 
         if ($item == 'principal') {
 
             return $principal;
 
         }
 
         if ($item == 'fees') {
 
             return $fees;
 
         }
 
         if ($item == 'penalty') {
 
             return $penalty;
 
         }
 
         if ($item == 'interest') {
 
             return $interest;
 
         }
 
         return $principal;
 
     }

    

     //FOR INTEREST PAID
 public function loan_interest_paid_item($id, $item = 'interest', $date = '')
 {
       $loan = Loan::findorfail($id);
    //    $repayment_order = array();
       $principal = 0;

       $interest = 0;

       $penalty = 0;

       $fees = 0;

       if (empty($date)) {

           $schedules = $loan->schedules;

       } else {

           $schedules = LoanSchedule::where('loan_id', $id)->where('due_date', '<=', $date)->get();

       }

     //  if(count($loan) > 0 && count($loan->loan_product) > 0){

       if(count((array)$loan) > 0 && count((array)$loan->loan_product) > 0){

           $repayment_order = (array)$loan->loan_product->repayment_order;

       }else{

           $repayment_order = [];

       }

       foreach ($schedules as $schedule) {

           $payments = LoanRepayment::where('loan_id', $id)->where('status','1')->where('due_date', $schedule->due_date)->sum('amount');

           if ($payments > 0) {

               foreach ($repayment_order as $order) {

                   if ($payments > 0) {

                       if ($order == 'interest') {

                           if ($payments > $schedule->interest) {

                               $interest = $interest + $schedule->interest;

                               $payments = $payments - $schedule->interest;

                           } else {

                               $interest = $interest + $payments;

                               $payments = 0;

                           }

                       }

                       if ($order == 'penalty') {

                           if ($payments > $schedule->penalty) {

                               $penalty = $penalty + $schedule->penalty;

                               $payments = $payments - $schedule->penalty;

                           } else {

                               $penalty = $penalty + $payments;

                               $payments = 0;

                           }

                       }

                       if ($order == 'fees') {

                           if ($payments > $schedule->fees) {

                               $fees = $fees + $schedule->fees;

                               $payments = $payments - $schedule->fees;

                           } else {

                               $fees = $fees + $payments;

                               $payments = 0;

                           }

                       }

                       if ($order == 'principal') {

                           if ($payments > $schedule->principal) {

                               $principal = $principal + $schedule->principal;

                               $payments = $payments - $schedule->principal;

                           } else {

                               $principal = $principal + $payments;

                               $payments = 0;

                           }

                       }

                   }

               }

           }

           //apply remainder to principal

           $principal = $principal + $payments;

       }

       if ($item == 'principal') {

           return $principal;

       }

       if ($item == 'fees') {

           return $fees;

       }

       if ($item == 'penalty') {

           return $penalty;

       }

       if ($item == 'interest') {

           return $interest;

       }

       return $interest;

   }
   
   public static function loan_paid_items($id, $start_date = '', $end_date = '')
    {
        $allocation = [];
        $loan = Loan::findorfail($id);
        $principal = 0;
        $fees = 0;
        $penalty = 0;
        $interest_waived = 0;
        $interest = 0;

        if (!empty($loan->loan_product)) {
            if (empty($start_date)) {
                $payments = LoanRepayment::where('loan_id', $id)->where('status','1')->sum('amount');
                // $interest_waived = LoanTransaction::where('loan_id', $id)->where('transaction_type',
                //     'waiver')->where('reversed', 0)->sum('credit');
            } else {

                $payments = LoanRepayment::where('loan_id', $id)->where('status','1')->whereBetween('due_date',[$start_date, $end_date])->sum('amount');

                // $interest_waived = LoanRepayment::where('loan_id', $id)->where('transaction_type',
                //     'waiver')->where('reversed', 0)->whereBetween('date',
                //     [$start_date, $end_date])->sum('amount');

            }

            foreach ($loan->schedules as $schedule) {
                //$schedules have not yet been covered
                if ($payments > 0) {
                    //try to allocate the remaining payment to the respective elements
                    $repayment_order = $loan->loan_product->repayment_order;
                    
                        if ($repayment_order == 'interest') {
                            if ($payments > $schedule->interest) {
                                $interest = $interest + $schedule->interest;
                                $payments = $payments - $schedule->interest;
                            } else {
                                $interest = $interest + $payments;
                                $payments = 0;
                            }
                        }
                        if ($repayment_order == 'penalty') {
                            if ($payments > $schedule->penalty) {
                                $penalty = $penalty + $schedule->penalty;
                                $payments = $payments - $schedule->penalty;
                            } else {
                                $penalty = $penalty + $payments;
                                $payments = 0;
                            }
                        }
                        if ($repayment_order == 'fees') {
                            if ($payments > $schedule->fees) {
                                $fees = $fees + $schedule->fees;
                                $payments = $payments - $schedule->fees;
                            } else {

                                $fees = $fees + $payments;
                                $payments = 0;
                            }

                        }
                        if ($repayment_order == 'principal') {
                            if ($payments > $schedule->principal) {
                                $principal = $principal + $schedule->principal;
                                $payments = $payments - $schedule->principal;
                            } else {
                                $principal = $principal + $payments;
                                $payments = 0;
                            }
                        }
                    
                } else {
                    break;
                }
            }
        }

        $allocation["principal"] = $principal;
        $allocation["interest"] = $interest;
        // $allocation["interest_waived"] = $interest_waived;
        $allocation["fees"] = $fees;
        $allocation["penalty"] = $penalty;
        return $allocation;

    }

   public function loan_total_due_amount($id, $date = '')

    {

        if (empty($date)) {

            return  $this->loan_total_penalty($id) + $this->loan_total_fees($id) + $this->loan_total_interest($id) + $this->loan_total_principal($id);

        } else {

            return $this->loan_total_penalty($id, $date) + $this->loan_total_fees($id,$date) + $this->loan_total_interest($id, $date) + $this->loan_total_principal($id,$date);

        }
    }

    public function loan_total_principal($id, $date = '')

    {

        if (empty($date)) {

            return LoanSchedule::where('loan_id', $id)->sum('principal');

        } else {

            return LoanSchedule::where('loan_id', $id)->where('due_date', '<=', $date)->sum('principal');

        }
    }

    public function loan_total_interest($id, $date = '')
    {

        if (empty($date)) {

            return LoanSchedule::where('loan_id', $id)->sum('interest');

        } else {

            return LoanSchedule::where('loan_id', $id)->where('due_date', '<=', $date)->sum('interest');

        }

    }

    public function loan_total_penalty($id, $date = '')

    {

        if (empty($date)) {

            return LoanSchedule::where('loan_id', $id)->sum('penalty');

        } else {

            return LoanSchedule::where('loan_id', $id)->where('due_date', '<=', $date)->sum('penalty');

        }
    }

    public function loan_total_fees($id, $date = '')

    {

        if (empty($date)) {

            return LoanSchedule::where('loan_id', $id)->sum('fees');

        } else {

            return LoanSchedule::where('loan_id', $id)->where('due_date', '<=', $date)->sum('fees');

        }
    }

    public function loan_total_balance($id, $date = '')

    {

        $loan = Loan::findorfail($id);

        if (empty($date)) {

            if ($loan->override == 1) {
                return $loan->balance - $this->loan_total_paid($id);
            } else {

                return $this->loan_total_due_amount($id) - $this->loan_total_paid($id);
            }

        } else {
         return $this->loan_total_due_amount($id, $date) - $this->loan_total_paid($id, $date);
        }
    }

    public function loan_total_paid($id, $date = '')

    {

        if (empty($date)) {

            return LoanRepayment::where('loan_id', $id)->where('status','1')->sum('amount');

        } else {

            return LoanRepayment::where('loan_id', $id)->where('status','1')->where('due_date', '<=', $date)->sum('amount');
        }
    }

    public static function loan_due_items($id, $start_date = '', $end_date = '')
    {
        $allocation = [];
        $principal = 0;
        $fees = 0;
        $penalty = 0;
        $interest = 0;
        if (empty($start_date)) {
            $schedules = LoanSchedule::where('loan_id', $id)->get();
        } else {
            $schedules = LoanSchedule::where('loan_id', $id)->whereBetween('due_date',
                [$start_date, $end_date])->get();
        }
        foreach ($schedules as $schedule) {
            $interest = $interest + $schedule->interest;
            $penalty = $penalty + $schedule->penalty;
            $fees = $fees + $schedule->fees;
            $principal = $principal + $schedule->principal;
        }
        $allocation["principal"] = $principal;
        $allocation["interest"] = $interest;
        $allocation["fees"] = $fees;
        $allocation["penalty"] = $penalty;
        return $allocation;
    }

    public static function loan_period($id)
    {

        $loan = Loan::findorfail($id);

        $period = 0;

        if ($loan->repayment_cycle == 'annually') {

            if ($loan->loan_duration_type == 'year') {

                $period = ceil($loan->loan_duration);

            }

            if ($loan->loan_duration_type == 'month') {

                $period = ceil($loan->loan_duration * 12);

            }

            if ($loan->loan_duration_type == 'week') {

                $period = ceil($loan->loan_duration * 52);

            }

            if ($loan->loan_duration_type == 'day') {

                $period = ceil($loan->loan_duration * 365);

            }

        }

        if ($loan->repayment_cycle == 'semi_annually') {

            if ($loan->loan_duration_type == 'year') {

                $period = ceil($loan->loan_duration * 2);

            }

            if ($loan->loan_duration_type == 'month') {

                $period = ceil($loan->loan_duration * 6);

            }

            if ($loan->loan_duration_type == 'week') {

                $period = ceil($loan->loan_duration * 26);

            }

            if ($loan->loan_duration_type == 'day') {

                $period = ceil($loan->loan_duration * 182.5);

            }

        }

        if ($loan->repayment_cycle == 'quarterly') {

            if ($loan->loan_duration_type == 'year') {

                $period = ceil($loan->loan_duration);

            }

            if ($loan->loan_duration_type == 'month') {

                $period = ceil($loan->loan_duration * 12);

            }

            if ($loan->loan_duration_type == 'week') {

                $period = ceil($loan->loan_duration * 52);

            }

            if ($loan->loan_duration_type == 'day') {

                $period = ceil($loan->loan_duration * 365);

            }

        }

        if ($loan->repayment_cycle == 'bi_monthly') {

            if ($loan->loan_duration_type == 'year') {

                $period = ceil($loan->loan_duration*6);

            }

            if ($loan->loan_duration_type == 'month') {

                $period = ceil($loan->loan_duration * 2);

            }

            if ($loan->loan_duration_type == 'week') {

                $period = ceil($loan->loan_duration * 8);

            }

            if ($loan->loan_duration_type == 'day') {

                $period = ceil($loan->loan_duration * 60);

            }

        }


        if ($loan->repayment_cycle == 'monthly') {

            if ($loan->loan_duration_type == 'year') {

                $period = ceil($loan->loan_duration * 12);

            }

            if ($loan->loan_duration_type == 'month') {

                $period = ceil($loan->loan_duration);

            }

            if ($loan->loan_duration_type == 'week') {

                $period = ceil($loan->loan_duration * 4.3);

            }

            if ($loan->loan_duration_type == 'day') {

                $period = ceil($loan->loan_duration * 30.4);

            }

        }

        if ($loan->repayment_cycle == 'weekly') {

            if ($loan->loan_duration_type == 'year') {

                $period = ceil($loan->loan_duration * 52);

            }

            if ($loan->loan_duration_type == 'month') {

                $period = ceil($loan->loan_duration * 4);

            }

            if ($loan->loan_duration_type == 'week') {

                $period = ceil($loan->loan_duration * 1);

            }

            if ($loan->loan_duration_type == 'day') {

                $period = ceil($loan->loan_duration * 7);

            }

        }

        if ($loan->repayment_cycle == 'daily') {

            if ($loan->loan_duration_type == 'year') {

                $period = ceil($loan->loan_duration * 365);

            }

            if ($loan->loan_duration_type == 'month') {

                $period = ceil($loan->loan_duration * 30.42);

            }

            if ($loan->loan_duration_type == 'week') {

                $period = ceil($loan->loan_duration * 7.02);

            }

            if ($loan->loan_duration_type == 'day') {

                $period = ceil($loan->loan_duration);

            }

        }

        return $period;
    }

    public static function determine_interest_rate($id)
    {
        $loan = Loan::findorfail($id);
        $interest = '';
        if ($loan->override_interest == 1) {
            $interest = $loan->override_interest_amount;
        } else {

            if ($loan->repayment_cycle == 'annually') {

                //return the interest per year

                if ($loan->interest_period == 'year') {

                    $interest = $loan->interest_rate;

                }

                if ($loan->interest_period == 'month') {

                    $interest = $loan->interest_rate * 12;

                }

                if ($loan->interest_period == 'week') {

                    $interest = $loan->interest_rate * 52;

                }

                if ($loan->interest_period == 'day') {

                    $interest = $loan->interest_rate * 365;
                }

            }

            if ($loan->repayment_cycle == 'semi_annually') {

                //return the interest per semi annually

                if ($loan->interest_period == 'year') {

                    $interest = $loan->interest_rate / 2;

                }

                if ($loan->interest_period == 'month') {

                    $interest = $loan->interest_rate * 6;

                }

                if ($loan->interest_period == 'week') {

                    $interest = $loan->interest_rate * 26;

                }

                if ($loan->interest_period == 'day') {

                    $interest = $loan->interest_rate * 182.5;

                }

            }

            if ($loan->repayment_cycle == 'quarterly') {

                //return the interest per quaterly
                if ($loan->interest_period == 'year') {
                    $interest = $loan->interest_rate / 4;

                }

                if ($loan->interest_period == 'month') {

                    $interest = $loan->interest_rate * 3;

                }

                if ($loan->interest_period == 'week') {

                    $interest = $loan->interest_rate * 13;

                }

                if ($loan->interest_period == 'day') {

                    $interest = $loan->interest_rate * 91.25;

                }

            }

            if ($loan->repayment_cycle == 'bi_monthly') {

                //return the interest per bi-monthly

                if ($loan->interest_period == 'year') {

                    $interest = $loan->interest_rate / 6;

                }

                if ($loan->interest_period == 'month') {

                    $interest = $loan->interest_rate * 2;

                }

                if ($loan->interest_period == 'week') {

                    $interest = $loan->interest_rate * 8.67;

                }

                if ($loan->interest_period == 'day') {

                    $interest = $loan->interest_rate * 58.67;

                }

            }

            if ($loan->repayment_cycle == 'monthly') {

                //return the interest per monthly

                if ($loan->interest_period == 'year') {

                    $interest = $loan->interest_rate / 12;
                }

                if ($loan->interest_period == 'month') {
                    $interest = $loan->interest_rate * 1;

                }

                if ($loan->interest_period == 'week') {

                    $interest = $loan->interest_rate * 4.33;

                }

                if ($loan->interest_period == 'day') {

                    $interest = $loan->interest_rate * 30.4;

                }

            }

            if ($loan->repayment_cycle == 'weekly') {

                //return the interest per weekly

                if ($loan->interest_period == 'year') {

                    $interest = $loan->interest_rate / 52;

                }

                if ($loan->interest_period == 'month') {

                    $interest = $loan->interest_rate / 4;

                }

                if ($loan->interest_period == 'week') {

                    $interest = $loan->interest_rate;

                }

                if ($loan->interest_period == 'day') {

                    $interest = $loan->interest_rate * 7;

                }

            }

            if ($loan->repayment_cycle == 'daily') {

                //return the interest per day

                if ($loan->interest_period == 'year') {

                    $interest = $loan->interest_rate / 365;

                }

                if ($loan->interest_period == 'month') {

                    $interest = $loan->interest_rate / 30.4;

                }

                if ($loan->interest_period == 'week') {

                    $interest = $loan->interest_rate / 7.02;

                }

                if ($loan->interest_period == 'day') {

                    $interest = $loan->interest_rate * 1;

                }

            }

        }

        return $interest / 100;
    }

    
    public function customer_loans_total_paid($id)

    {
        $paid = 0;
        $getloans = Loan::where('status', 'disbursed')->where('customer_id', $id)->get();
        foreach ($getloans as $key) {
            $paid = $paid + LoanRepayment::where('loan_id', $key->id)->where('status','1')->sum('amount');
        }
        return $paid;
    }

    public function customer_loans_total_due($id)

    {

        $due = 0;
 $getloans = Loan::where('status', 'disbursed')->where('customer_id', $id)->get();

        foreach ($getloans as $key) {
            $due = $due + $this->loan_total_due_amount($key->id);
        }

        return $due;
    }

    public function amortized_monthly_payment($id, $balance)

    {

        $loan = Loan::findorfail($id);

        $period = $this->loan_period($id);

        $interest_rate = $this->determine_interest_rate($id);

        //calculate here

        $amount = ($interest_rate * $balance * pow((1 + $interest_rate), $period)) / (pow((1 + $interest_rate),$period) - 1);

        return $amount;
    }

    public function determine_due_date($id, $date)
    {
 $schedule = LoanSchedule::where('due_date', '>=', $date)->where('loan_id', $id)->orderBy('due_date','asc')->first();

        if (!empty($schedule)) {

            return $schedule->due_date;

        } else {

            $ckschedule = LoanSchedule::where('loan_id',$id)->orderBy('due_date','desc')->first();

            if(!empty($ckschedule)){
                if ($date > $ckschedule->due_date) {

                return $ckschedule->due_date;

            } else {

                $schedule2 = LoanSchedule::where('due_date', '>=', $date)
                                            ->where('loan_id',$id)
                                            ->orderBy('due_date','asc')->first();

                return $schedule2->due_date;

             }
            }
        }

    }
    
     
    public function loans_total_due($start_date = '', $end_date = '')

    {
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (empty($start_date)) {

            $due = 0;

            foreach (Loan::where('branch_id', $branch)->where('status', 'disbursed')->get() as $key) {

                $due = $due + $this->loan_total_due_amount($key->id);

            }

            return $due;

        } else {

            $due = 0;

            foreach (Loan::where('branch_id', $branch)->where('status', 'disbursed')->whereBetween('release_date',

                [$start_date, $end_date])->get() as $key) {

                $due = $due + $this->loan_total_due_amount($key->id);

            }

            return $due;

        }

    }

    //TOTAL WITHHOLDING TAX

    public function total_wht($start_date = '', $end_date = '')
    {
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (empty($start_date)) {
        return SavingsTransaction::where('branch_id',$branch)->where('type', 'wht')->sum('amount');

        } else {

     return SavingsTransaction::where('branch_id',$branch)->where('type', 'wht')->whereBetween('created_at',[$start_date, $end_date])->sum('amount');
        }
    }
   
    public function total_savings_deposits($start_date = '', $end_date = '')

    {
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (empty($start_date)) {

            return SavingsTransaction::where('branch_id',$branch)->where('type', 'deposit')->sum('amount');

        } else {

        return SavingsTransaction::where('branch_id',$branch)->where('type', 'deposit')->whereBetween('created_at',[$start_date, $end_date])->sum('amount');
        }
    }
    
    public function total_capital($start_date = '', $end_date = '')

    {
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (empty($start_date)) {

            return Capital::where('branch_id', $branch)->sum('amount');

        } else {

            return Capital::where('branch_id',$branch)->whereBetween('created_at', [$start_date, $end_date])->sum('amount');
        }

    }

    public function total_expenses($start_date = '', $end_date = '')

    {
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (empty($start_date)) {

            return Expenses::where('branch_id', $branch)->sum('amount');

        } else {

            return Expenses::where('branch_id', $branch)->whereBetween('created_at', [$start_date, $end_date])->sum('amount');

        }

    }
    
     

    public  function total_payroll($start_date = '', $end_date = '')

    {
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (empty($start_date)) {

            $payroll = 0;

            foreach (Payroll::where('branch_id', $branch)->get() as $key) {

                $payroll = $payroll + $this->single_payroll_total_pay($key->id);

            }

            return $payroll;

        } else {

            $payroll = 0;

            foreach (Payroll::where('branch_id',$branch)->whereBetween('created_at', [$start_date, $end_date])->get() as $key) {

                $payroll = $payroll + $this->single_payroll_total_pay($key->id);

            }

            return $payroll;
        }
    }

    
    public function single_payroll_total_pay($id)

    {

        return PayrollMeta::where('payroll_id', $id)->where('position', 'bottom_left')->sum('value');

    }


    public function single_payroll_total_deductions($id)

    {

        return PayrollMeta::where('payroll_id', $id)->where('position', 'bottom_right')->sum('value');

    }
    
    public function loans_total_principal($start_date = '', $end_date = '')
    {
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (empty($start_date)) {

            $principal = 0;

            foreach (Loan::where('branch_id',$branch)->where('status', 'disbursed')->get() as $key) {

                $principal = $principal + $key->principal;

            }

            return $principal;

        } else {

            $principal = 0;

            foreach (Loan::where('branch_id',$branch)->where('status', 'disbursed')->whereBetween('release_date',[$start_date, $end_date])->get() as $key) {

                $principal = $principal + $key->principal;

            }

            return $principal;
        }

    }
    
    public function total_other_income($start_date = '', $end_date = '')

    {
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (empty($start_date)) {

            return OtherIncome::where('branch_id', $branch)->sum('amount');

        } else {

            return OtherIncome::where('branch_id', $branch)->whereBetween('created_at', [$start_date, $end_date])->sum('amount');
        }

    }
    
    public function total_savings_withdrawals($start_date = '', $end_date = '')
    {
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (empty($start_date)) {

            return SavingsTransaction::where('branch_id', $branch)->where('type', 'withdrawal')->sum('amount');

        } else {

            return SavingsTransaction::where('branch_id', $branch)->where('type', 'withdrawal')->whereBetween('created_at',[$start_date, $end_date])->sum('amount');
        }
    }
    
    public function loans_total_paid_item($item, $start_date = '', $end_date = '')

    {
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (empty($start_date)) {

            $amount = 0;

            foreach (Loan::where('branch_id',$branch)->where('status', 'disbursed')->get() as $key) {

                $amount = $amount + $this->loan_paid_item($key->id, $item, $key->due_date);

            }

            return $amount;

        } else {

            $amount = 0;

            foreach (Loan::where('branch_id',$branch)->where('status', 'disbursed')->whereBetween('release_date',

                [$start_date, $end_date])->get() as $key) {

                $amount = $amount + $this->loan_paid_item($key->id, $item, $key->due_date);

            }

            return $amount;
        }
    }
    
    
     public function asset_type_valuation($id, $start_date = '')

    {
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (empty($start_date)) {

            $value = 0;

            foreach (Asset::where('branch_id', $branch)->where('asset_type_id', $id)->get() as $key) {

                if (!empty(AssetValuation::where('asset_id', $key->id)->orderBy('date', 'desc')->first())) {

                    $value = AssetValuation::where('asset_id', $key->id)->orderBy('date', 'desc')->first()->amount;

                }

            }

            return $value;

        } else {

            $value = 0;

            foreach (Asset::where('branch_id', $branch)->where('asset_type_id', $id)->get() as $key) {

                if (!empty(AssetValuation::where('asset_id', $key->id)->where('date', '<=',

                    $start_date)->orderBy('date',

                    'desc')->first())

                ) {

                    $value = AssetValuation::where('asset_id', $key->id)->where('date', '<=',

                        $start_date)->orderBy('date',

                        'desc')->first()->amount;

                }

            }

            return $value;
        }

    }

    //REVERSAL DEPOSIT
    public function rev_total_savings_deposits($start_date = '', $end_date = '')
    {
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (empty($start_date)) {

            return SavingsTransaction::where('branch_id',$branch)->where('type', 'rev_deposit')->sum('amount');

        } else {

        return SavingsTransaction::where('branch_id',$branch)->where('type', 'rev_deposit')->whereBetween('created_at',

                [$start_date, $end_date])->sum('amount');

        }
    }

    //FIXED DEPOSIT

    public function total_fixed_deposit($start_date = '', $end_date = '')
    {
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (empty($start_date)) {
        return SavingsTransaction::where('branch_id',$branch)->where('type', 'fixed_deposit')->sum('amount');

        } else {

     return SavingsTransaction::where('branch_id',$branch)->where('type', 'fixed_deposit')->whereBetween('created_at',

                [$start_date, $end_date])->sum('amount');
        }
    }

    public  function total_investment($start_date = '', $end_date = '')
    {
       $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (empty($start_date)) {
        return SavingsTransaction::where('branch_id',$branch)->where('type', 'investment')->sum('amount');

        } else {

     return SavingsTransaction::where('branch_id',$branch)->where('type', 'investment')->whereBetween('created_at',[$start_date, $end_date])->sum('amount');
        }
    }

    //FIXED DEPOSIT REVERSAL

     public function rev_total_fixed_deposit($start_date = '', $end_date = '')
    {
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (empty($start_date)) {
        return SavingsTransaction::where('branch_id',$branch)->where('type', 'rev_fixed_deposit')->sum('amount');

        } else {

     return SavingsTransaction::where('branch_id',$branch)->where('type', 'rev_fixed_deposit')->whereBetween('created_at',[$start_date, $end_date])->sum('amount');
        }
    }

    //REVERSAL WITHDRAWAL

    public static function rev_total_savings_withdrawals($start_date = '', $end_date = '')
    {
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (empty($start_date)) {

            return SavingsTransaction::where('branch_id', $branch)->where('type', 'rev_withdrawal')->sum('amount');

        } else {

            return SavingsTransaction::where('branch_id',$branch)->where('type', 'rev_withdrawal')->whereBetween('created_at',

                [$start_date, $end_date])->sum('amount');
        }
    }

    public  function loan_total_due_period($id, $date)

    {

        return (LoanSchedule::where('loan_id', $id)->where('due_date',

                $date)->sum('penalty') + LoanSchedule::where('loan_id', $id)->where('due_date',

                $date)->sum('fees') + LoanSchedule::where('loan_id', $id)->where('due_date',

                $date)->sum('principal') + LoanSchedule::where('loan_id', $id)->where('due_date',

                $date)->sum('interest'));
    }
    
    
    public function loan_total_paid_period($id, $date)

    {
        return LoanRepayment::where('loan_id', $id)->where('due_date', $date)->where('status','1')->sum('amount');

    }
    
    
     public function loans_total_due_item($item, $start_date = '', $end_date = '')

    {
$branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
        if (empty($start_date)) {

            $amount = 0;

            foreach (Loan::where('branch_id',$branch)->where('status', 'disbursed')->get() as $key) {

                if ($item == 'principal') {

                    $amount = $amount + $this->loan_total_principal($key->id);

                }

                if ($item == 'interest') {

                    $amount = $amount + $this->loan_total_interest($key->id);

                }

                if ($item == 'fees') {

                    $amount = $amount + $this->loan_total_fees($key->id);

                }

                if ($item == 'penalty') {

                    $amount = $amount + $this->loan_total_penalty($key->id);

                }
            }

            return $amount;

        } else {

            $amount = 0;

            foreach (Loan::where('branch_id', $branch)->where('status', 'disbursed')->whereBetween('release_date',

                [$start_date, $end_date])->get() as $key) {

                if ($item == 'principal') {

                    $amount = $amount + $this->loan_total_principal($key->id);

                }

                if ($item == 'interest') {

                    $amount = $amount + $this->loan_total_interest($key->id);

                }

                if ($item == 'fees') {

                    $amount = $amount + $this->loan_total_fees($key->id);

                }

                if ($item == 'penalty') {

                    $amount = $amount + $this->loan_total_penalty($key->id);

                }

            }

            return $amount;

        }



    }
    
    public function loans_total_paid($start_date = '', $end_date = '')

    {
$branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
        if (empty($start_date)) {

            $paid = 0;

            foreach (Loan::where('branch_id', $branch)->where('status', 'disbursed')->get() as $key) {

                $paid = $paid + LoanRepayment::where('loan_id', $key->id)->where('status','1')->sum('amount');

            }

            return $paid;

        } else {

            $paid = 0;

            foreach (Loan::where('branch_id', $branch)->where('status', 'disbursed')->whereBetween('release_date',

                [$start_date, $end_date])->get() as $key) {

                $paid = $paid + LoanRepayment::where('loan_id', $key->id)->where('status','1')->sum('amount');

            }

            return $paid;

        }

    }
    
    
    public function total_savings_interest($start_date = '', $end_date = '')

    {
$branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
        if (empty($start_date)) {

            return SavingsTransaction::where('branch_id',$branch)->where('type', 'interest')->sum('amount');

        } else {

            return SavingsTransaction::where('branch_id',$branch)->where('type', 'interest')->whereBetween('created_at',

                [$start_date, $end_date])->sum('amount');

        }

    }

    //FD INTEREST EXPENSE

     public function total_FD_interest_expense($start_date = '', $end_date = '')

    {
$branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
        if (empty($start_date)) {

            return SavingsTransaction::where('branch_id',$branch)->where('type', 'fd_interest')->sum('amount');

        } else {

            return SavingsTransaction::where('branch_id',$branch)->where('type', 'fd_interest')->whereBetween('created_at',

                [$start_date, $end_date])->sum('amount');

        }

    }

    //INV INTEREST EXPENSE

 public function total_inv_int_expense($start_date = '', $end_date = '')

 {
$branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
     if (empty($start_date)) {

         return SavingsTransaction::where('branch_id',$branch)->where('type', 'inv_int')->sum('amount');

     } else {

         return SavingsTransaction::where('branch_id', $branch)->where('type', 'inv_int')->whereBetween('created_at',

             [$start_date, $end_date])->sum('amount');

     }

 }

 public function total_bank_fees($start_date = '', $end_date = '')

    {
$branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
        if (empty($start_date)) {

            return SavingsTransaction::where('branch_id',$branch)->where('type', 'bank_fees')->sum('amount');

        } else {

            return SavingsTransaction::where('branch_id',$branch)->where('type', 'bank_fees')->whereBetween('created_at',

                [$start_date, $end_date])->sum('amount');

        }

    }  

    public function total_form_fees($start_date = '', $end_date = '')

    {
$branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
        if (empty($start_date)) {

            return SavingsTransaction::where('branch_id',$branch)->where('type', 'form_fees')->sum('amount');

        } else {

            return SavingsTransaction::where('branch_id',$branch)->where('type', 'form_fees')->whereBetween('created_at',

                [$start_date, $end_date])->sum('amount');

        }

    }  

    public function total_process_fees($start_date = '', $end_date = '')

    {
$branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
        if (empty($start_date)) {

            return SavingsTransaction::where('branch_id',$branch)->where('type', 'process_fees')->sum('amount');

        } else {

            return SavingsTransaction::where('branch_id',$branch)->where('type', 'process_fees')->whereBetween('created_at',

                [$start_date, $end_date])->sum('amount');

        }

    }  

 public function total_esusu($start_date = '', $end_date = '')

    {
$branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
        if (empty($start_date)) {

            return SavingsTransaction::where('branch_id',$branch)->where('type', 'esusu')->sum('amount');

        } else {

            return SavingsTransaction::where('branch_id',$branch)->where('type', 'esusu')->whereBetween('created_at',[$start_date, $end_date])->sum('amount');

        }

    }  

    public function total_monthly_charge($start_date = '', $end_date = '')
    {
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
     if (empty($start_date)) {
            return SavingsTransaction::where('branch_id', $branch)->where('type', 'monthly_charge')->sum('amount');
        } else {
            return SavingsTransaction::where('branch_id', $branch)->where('type', 'monthly_charge')->whereBetween('created_at',[$start_date, $end_date])->sum('amount');
        }
    }  
    
    public function total_transfer_charge($start_date = '', $end_date = '')
    {
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
        if (empty($start_date)) {
            return SavingsTransaction::where('branch_id', $branch)->where('type', 'transfer_charge')->sum('amount');
        } else {
            return SavingsTransaction::where('branch_id', $branch)->where('type', 'transfer_charge')->whereBetween('created_at',
                [$start_date, $end_date])->sum('amount');
        }
    } 

    public function loans_total_default($start_date = '', $end_date = '')
    {
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
       if (empty($start_date)) {
           $principal = 0;
            foreach (Loan::where('branch_id',$branch)->where('status', 'written_off')->get() as $key) {
                $principal = $principal + ($key->principal - $this->loan_total_paid($key->id));
            }
            return $principal;
        } else {
            $principal = 0;
        foreach (Loan::where('branch_id',$branch)->where('status', 'written_off')->whereBetween('release_date',
                [$start_date, $end_date])->get() as $key) {
                $principal = $principal + ($key->principal - $this->loan_total_paid($key->id));
            }
            return $principal;
        }
    }

    
    public function outsndLoanbal(){
        $outptcbal = 0;
        $schedprincipal = 0;

        $totprx =  Loan::where('status', 'disbursed')->sum('principal');

        foreach(Loan::where('status', 'disbursed')->get() as $loan){

            $outptcbal += $this->loan_paid_item($loan->id);
            // foreach (LoanSchedule::where('loan_id', $loan->id)->get() as $schedule) {
 
            //     $payments = LoanRepayment::where('loan_id',$loan->id)->sum('amount');

            //     $schedprincipal += $schedule->principal;
            // }

        }

        $total = $totprx - $outptcbal;

        return $total;
    }
}//end trait