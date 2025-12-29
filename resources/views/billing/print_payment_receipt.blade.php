<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Subcription Payslip</title>
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
        <h3 class="text-center"><img src="{{asset('img/myban.png')}}" class="img-responsive" width="120" alt="logo"></h3>
        
    
        <div style="width: 100%;margin:0px auto;font-size:10px;padding-top: 40px;text-transform: capitalize">
            <table border="1" style="width:100%">
               <tbody>
                <tr><td>To: <span style="text-transform: uppercase;font-weight:bold;margin-left:15px">{{$getsetvalue->getsettingskey('company_name')}}</span></td></tr>
                <tr><td>Description: <span style="text-transform: uppercase;font-weight:bold;margin-left:15px">{{$recipt->note}}</span></td></tr>
                <tr><td>Valid Till: <span style="text-transform: uppercase;font-weight:bold;margin-left:15px">{{date("d-M-Y",strtotime($recipt->expiration_date))}}</span></td></tr>
                <tr><td>Payment Reference: <span style="text-transform: uppercase;font-weight:bold;margin-left:15px">{{$recipt->paymentref}}</span></td></tr>
                <tr><td>DATE: <span style="text-transform: uppercase;font-weight:bold;margin-left:15px">{{date("d-M-Y",strtotime($recipt->payment_date))}}</span></td></tr>
               </tbody>
            </table>

            <h3>Dear Sir/Ma,</h3>
            <p>This is to acknowledge your payment for the above referenced.</p><br>

            <table border="1" style="width: 100%">
                
                <tbody>
                    <?php $vt = $recipt->amount_paid /100 * $recipt->vat;?>
                    <tr style="background-color: blanchedalmond;color:black">
                        <td colspan="3" align="center">Receipt Description</td>
                    </tr>
                            <tr>
                                <td colspan="3">{{$recipt->note}}</td>
                            </tr>
                            <tr>
                                <td>VAT</td>
                                <td align="right">{{$recipt->vat."%"}}</td>
                                <td align="right">{{number_format($vt,2)}}</td>
                            </tr>
                            <tr>
                                <td>Amount</td>
                                <td colspan="2" align="right">{{number_format($recipt->amount_paid,2)}}</td>
                            </tr>
                            <tr>
                                <td>Total Paid</td>
                                <td colspan="2" align="right">{{number_format($recipt->total_paid,2)}}</td>
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