<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Satff Payslip</title>
    <style>

        .table {
            width: 100%;
            max-width: 100%;
            margin-bottom: 20px;
            display: table;
        }
    
        .text-left {
            text-align: left;
        }
    
        .text-right {
            text-align: right;
        }
    
        .text-center {
            text-align: center;
        }
    
        .text-justify {
            text-align: justify;
        }
    
        .pull-right {
            float: right !important;
        }
    </style>
</head>
<body>
    <?php
        $getsetvalue = new \App\Models\Setting();
       ?>
       
    <div>
        <h3 class="text-center"><img src="{{asset($getsetvalue->getsettingskey('company_logo'))}}" class="img-responsive" width="120" alt="logo"></h3>
        <h3 class="text-center">Payslip For {{$monthName.", ".$payslips->year}}</h3>
    
        <div style="width: 100%;margin:0px auto;font-size:10px;padding-top: 40px;text-transform: capitalize">
            <table border="1" style="width:100%">
               <tbody>
                <tr><td>NAME OF EMPLOYEE: <span style="text-transform: uppercase;font-weight:bold;margin-left:15px">{{$payslips->payroll->employee_name}}</span></td></tr>
                <tr><td>DESIGNATION: <span style="text-transform: uppercase;font-weight:bold;margin-left:15px">{{$payslips->payroll->designation}}</span></td></tr>
                <tr><td>MONTH & YEAR: <span style="text-transform: uppercase;font-weight:bold;margin-left:15px">{{$monthName.", ".$payslips->year}}</span></td></tr>
                <tr><td>DATE: <span style="text-transform: uppercase;font-weight:bold;margin-left:15px">{{date("d-M-Y")}}</span></td></tr>
               </tbody>
            </table>
            <table border="1" style="width: 100%">
                <thead>
                    <tr style="background-color: blanchedalmond;color:black">
                        <th>Payments</th>
                        <th>Deductions</th>
                    </tr>
                </thead>
                <tbody>
                            <tr>
                                <td>Basic <span style="float: right;font-weight:bold">{{number_format($payslips->paymentstructure->basic,2)}}</span></td>
                                <td>Paye <span style="float: right;font-weight:bold">{{number_format($payslips->paymentstructure->paye,2)}}</span></td>
                            </tr>
                            <tr>
                                <td>Other Allowances<span style="float: right;font-weight:bold">{{number_format($payslips->paymentstructure->other_allowance,2)}}</span></td>
                                <td>Other Deductions<span style="float: right;font-weight:bold">{{number_format($payslips->paymentstructure->other_deduction,2)}}</span></td>
                            </tr>
                            <tr>
                                <td>Total Payment<span style="float: right;font-weight:bold">{{number_format($payslips->paymentstructure->gross_pay,2)}}</span></td>
                                <td>Total Deductions<span style="float: right;font-weight:bold">{{number_format($payslips->paymentstructure->deduction,2)}}</span></td>
                            </tr>
                     
                    <tr style="background-color: blanchedalmond;color:black">
                        <td colspan="2">Net Pay <span style="float: right;font-weight:bold">{{number_format($payslips->paymentstructure->net_pay,2)}}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
    </div>
    
    <script>
        window.onload = function () {
            window.print();
        }
    </script>
</body>
</html>