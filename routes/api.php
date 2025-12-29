<?php

use App\Http\Controllers\Api\CustomersController;
use App\Http\Controllers\Api\GiftbillController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\MonnifyController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\VtpassController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WirelessController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
global $payoption;
$payoption = \App\Models\Setting::first();


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('/customers')->group(function () {
  Route::middleware(['log.route'])->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout_customer'])->middleware(['auth:sanctum','abilities:customer']);
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/existregister', [RegisterController::class, 'existingAccount']);
    Route::post('/confirm-otp', [CustomersController::class, 'confirm_otp']);//for new and existing registered customers
    Route::post('/verify-otp', [CustomersController::class, 'verify_otp']);// for forget password
    Route::post('/resend-otp', [CustomersController::class, 'resend_otp']);
    Route::post('/forgot-password', [CustomersController::class, 'forgetpassword']);
    Route::post('/reset-password', [CustomersController::class, 'reset_password']);

    Route::post('/change-password', [CustomersController::class, 'change_password'])->middleware(['auth:sanctum','abilities:customer']);
    Route::post('/change-pin', [CustomersController::class, 'reset_pin'])->middleware(['auth:sanctum','abilities:customer']);
    //resetPin
    Route::post('/upload-file', [CustomersController::class, 'uploadFile'])->middleware(['auth:sanctum','abilities:customer']);
    Route::get('/get-details', [CustomersController::class, 'get_customers_details'])->middleware(['auth:sanctum','abilities:customer']);
    Route::post('/update-profile', [CustomersController::class, 'update_profile'])->middleware(['auth:sanctum','abilities:customer']);
    
    //get beneficiary
    Route::get('/get-beneficiary', [CustomersController::class, 'getbeneficiary'])->middleware(['auth:sanctum','abilities:customer']);

 });
});

Route::post('/bvn/verification',[TransactionController::class,'bvn_verify'])->middleware(['log.route']);

Route::post('/transactions/webhook/notification-payload', [TransactionController::class, 'save_notification_payload']);
 
Route::prefix('/transactions')->group(function () {
  Route::middleware(['log.route'])->group(function () {
    global $payoption;
    
    Route::post('/verify-smartcard',[VtpassController::class,'verify_smartcard_number']);
    Route::get('/get-subcriptions',[VtpassController::class,'get_subcriptions']);
    Route::post('/pay-cabletv-subcription',[VtpassController::class,'pay_cable_tv'])->middleware(['auth:sanctum','abilities:customer']);

    Route::post('/buy-airtime',[VtpassController::class,'buy_airtime'])->middleware(['auth:sanctum','abilities:customer']);
    Route::get('/get-databundles/{networktype}',[VtpassController::class,'getdatabundles']);
    Route::post('/buy-data-bundle',[VtpassController::class,'buy_data_bundle'])->middleware(['auth:sanctum','abilities:customer']);

    Route::post('/verify-meter',[VtpassController::class,'verify_meter_number']);
    Route::post('/pay-electricty',[VtpassController::class,'pay_electricty'])->middleware(['auth:sanctum','abilities:customer']);

    Route::get('/betting-providers',[GiftbillController::class,'get_betting_companies']);
    Route::post('/verify-betting-account',[GiftbillController::class,'verify_betting_account']);
    Route::post('/top-betting-account',[GiftbillController::class,'topup_betting'])->middleware(['auth:sanctum','abilities:customer']);
    
    Route::get('/get-transaction-statement', [TransactionController::class, 'get_transactionStatement'])->middleware(['auth:sanctum','abilities:customer']);
    Route::get('/get-transaction-history', [TransactionController::class, 'get_transactionHistory'])->middleware(['auth:sanctum','abilities:customer']);
    
    //wallet to wallet transfer 
    Route::post('/verify-wallet-account', [TransactionController::class, 'verifyWalletAccount'])->middleware(['auth:sanctum','abilities:customer']);
    Route::post('/initiate-transaction', [TransactionController::class, 'initiateTransaction'])->middleware(['auth:sanctum','abilities:customer']);
    Route::post('/wallet-transfer', [TransactionController::class, 'transferToWalletAccount'])->middleware(['auth:sanctum','abilities:customer']);

    if($payoption->getsettingskey('payoption') == "1"){
        
        Route::get('/get-banks', [TransactionController::class, 'getAllBanks']);
        Route::post('/verify-bank-account', [TransactionController::class, 'verifyBankAccount'])->middleware(['auth:sanctum','abilities:customer']);
        Route::post('/initiate-transaction', [TransactionController::class, 'initiateTransaction'])->middleware(['auth:sanctum','abilities:customer']);
        Route::post('/bank-transfer', [TransactionController::class, 'transferToBankAccount'])->middleware(['auth:sanctum','abilities:customer']);
        
    }elseif($payoption->getsettingskey('payoption') == "2"){//monnify transfer

        Route::get('/get-banks', [MonnifyController::class, 'getBanks']);
        Route::post('/verify-bank-account', [MonnifyController::class, 'VeriyBankAccount'])->middleware(['auth:sanctum','abilities:customer']);
        Route::post('/initiate-transaction', [MonnifyController::class, 'initiateTransaction'])->middleware(['auth:sanctum','abilities:customer']);
        Route::post('/bank-transfer', [MonnifyController::class, 'transferToBankAccount'])->middleware(['auth:sanctum','abilities:customer']);
        
    }elseif($payoption->getsettingskey('payoption') == "3"){//nibbspay
           Route::post('/verify-bank-account', [NibbspayController::class, 'VeriyBankAccount'])->middleware(['auth:sanctum','abilities:customer']);
      Route::post('/initiate-transaction', [NibbspayController::class, 'initiateTransaction'])->middleware(['auth:sanctum','abilities:customer']);
      Route::post('/bank-transfer', [NibbspayController::class, 'transferToBankAccount'])->middleware(['auth:sanctum','abilities:customer']);
      
    }elseif($payoption->getsettingskey('payoption') == "4"){
      Route::post('/verify-bank-account', [WirelessController::class, 'VeriyBankAccount'])->middleware(['auth:sanctum','abilities:customer']);
        Route::post('/initiate-transaction', [WirelessController::class, 'initiateTransaction'])->middleware(['auth:sanctum','abilities:customer']);
        Route::post('/bank-transfer', [WirelessController::class, 'transferToBankAccount'])->middleware(['auth:sanctum','abilities:customer']);
    }
 });
});

