<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FxController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\AssetsController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ChargesController;
use App\Http\Controllers\CronjobController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\LoanfeesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\RepaymentController;
use App\Http\Controllers\AccountmgtController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CollateralController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\DepositmgmtController;
use App\Http\Controllers\LoanCommentController;
use App\Http\Controllers\LoanProductController;
use App\Http\Controllers\OtherincomeController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\SubcriptionController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\VerifyTwoAuthController;
use App\Http\Controllers\AccountofficerController;
use App\Http\Controllers\IpwhitelistingController;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
global $getsetvalue;

$getsetvalue = \App\Models\Setting::first();

if($getsetvalue->getsettingskey('enable_2FA') == 1){
    $middleware = ['twofactor'];
}else{
    $middleware =[];
}

Route::get('/', function () {
//    return Hash::make('12345678');
    return view('welcome');
})->name('welcome')->middleware('guest');;

Route::post('/user-login',[LoginController::class,'login_account'])->name('users.login');
Route::get('/user-logout', [LoginController::class,'logout'])->name('users.logout');

//two factor auth
Route::get('verify',[VerifyTwoAuthController::class,'index'])->name('verify.index');
Route::resource('verify',VerifyTwoAuthController::class)->only(['index', 'store']);
Route::get('verify/resend',[VerifyTwoAuthController::class,'resendcode'])->name('resnd');

Route::group(['prefix' => 'banqpro','middleware' => $middleware],function(){
  Route::middleware(['log.route','checksubcription'])->group(function () {
    global $getsetvalue;

    Route::get('/dashboard',[PageController::class,'dashboard'])->name('dashboard')->middleware('twofactor');
    Route::get('/goto/branch',[PageController::class,'branchpage'])->name('branchpage')->middleware('twofactor');
    //get user details
    Route::get('/get-user-details',[PageController::class,'getuser_details'])->name('getuserdetails')->middleware('twofactor');

    //audit trails
    Route::get('/manage-audit-trail',[PageController::class,'audit_trail'])->name('audit');

    //wallet funding
    Route::get('/wallet-fund',[SettingsController::class,'wallet'])->name('wallet');
    Route::post('/wallet-fund/credit',[SettingsController::class,'fund_wallet'])->name('walletfund');

    //loan calculator
    Route::get('/loan-calculator',[PageController::class,'loan_calculator'])->name('lcalcu');
    Route::post('/loan-calculator/show',[PageController::class,'loan_calculator_show'])->name('calculate-show');
    Route::post('/loan-calculator/print',[PageController::class,'loan_calculator_print'])->name('calculate-print');

    //profile
    Route::get('/profile',[PageController::class,'profile'])->name('profile');
    Route::post('/profile/update',[SettingsController::class,'update_profile'])->name('update.profile');

    //change password
    Route::get('/change-password',[SettingsController::class,'change_password'])->name('changepass');
    Route::post('/change-password/update',[SettingsController::class,'update_password'])->name('update.changepass');

    //account officers
    Route::get('/all-account-officers',[AccountofficerController::class,'index'])->name('acofficer.index');
    Route::get('/account-officers/create',[AccountofficerController::class,'create'])->name('acofficer.create');
    Route::post('/account-officers/store',[AccountofficerController::class,'store'])->name('acofficer.store');
    Route::get('/account-officers/{id}/edit',[AccountofficerController::class,'edit'])->name('acofficer.edit');
    Route::post('/account-officers/update/{id}',[AccountofficerController::class,'update'])->name('acofficer.update');
    Route::get('/account-officers/delete/{id}',[AccountofficerController::class,'delete'])->name('acofficer.delete');

    //branches
    Route::get('/all-branch',[BranchController::class,'index'])->name('branch.index');
    Route::get('/branch/create',[BranchController::class,'create'])->name('branch.create');
    Route::post('/branch/store',[BranchController::class,'store'])->name('branch.store');
    Route::get('/branch/{id}/edit',[BranchController::class,'edit'])->name('branch.edit');
    Route::post('/branch/update/{id}',[BranchController::class,'update'])->name('branch.update');
    Route::get('/branch/delete/{id}',[BranchController::class,'delete'])->name('branch.delete');
    Route::get('/branch/assign-users/{id}',[BranchController::class,'branch_assign_user'])->name('branch.assignuser');
    Route::post('/branch/assign-users/save',[BranchController::class,'branch_assign_user_save'])->name('branch.assign');
    Route::get('/branch/list-users/{id}',[BranchController::class,'branch_showuser'])->name('branch.showuser');
    Route::post('/branch/move/user/branch',[BranchController::class,'move_user_branch'])->name('branch.moveubrnd');

    //users
    Route::get('/user/manage-users',[UserController::class,'manage_users'])->name('user.all');
    Route::get('/user/create-users',[UserController::class,'user_create'])->name('user.create');
    Route::post('/user/store-users',[UserController::class,'user_store'])->name('user.store');
    Route::get('/user/{id}/edit-users',[UserController::class,'user_edit'])->name('user.edit');
    Route::get('/user/reset-qrcode/{id}',[UserController::class,'user_resetqr'])->name('user.resetqr');
    Route::post('/user/update-users/{id}',[UserController::class,'user_update'])->name('user.update');
    Route::get('/user/deactivate-users/{id}',[UserController::class,'user_deactivate'])->name('user.deactive');
    Route::get('/user/activate-users/{id}',[UserController::class,'user_activate'])->name('user.active');
    Route::get('/user/delete-user/{id}',[UserController::class,'user_delete'])->name('user.delete');
    Route::get('/bank/all',[UserController::class,'allbanks'])->name('bank.all');
    Route::post('/bank/add-update-bank',[UserController::class,'add_update_banks'])->name('bank.edit.create');
    Route::get('/bank/delete-bank/{id}',[UserController::class,'delete_bank'])->name('bank.delete');
    Route::get('/user/user-reset-password/{id}',[UserController::class,'reset_aduserpass'])->name('user.resetadusrepass');

    //roles
    Route::get('/user/manage-roles',[RolesController::class,'manage_roles'])->name('roles');
    Route::get('/user/roles/create',[RolesController::class,'role_create'])->name('roles.create');
    Route::post('/user/roles/store',[RolesController::class,'role_store'])->name('roles.store');
    Route::get('/user/roles/{id}/edit',[RolesController::class,'role_edit'])->name('roles.edit');
    Route::post('/user/roles/update/{id}',[RolesController::class,'role_update'])->name('roles.update');
    Route::get('/user/roles/add-permissions/{id}',[RolesController::class,'role_add_permission'])->name('roles.addprm');
    Route::post('/user/roles/assign-permissions',[RolesController::class,'role_assign_permission'])->name('roles.assignpermission');

    //permission
     Route::get('/user/manage-permissions',[PermissionsController::class,'manage_permission'])->name('permissions.all');
     Route::get('/user/permissions/create',[PermissionsController::class,'permission_create'])->name('permissions.create');
     Route::post('/user/permissions/store',[PermissionsController::class,'permission_store'])->name('permissions.store');
     Route::get('/user/permissions/{id}/edit',[PermissionsController::class,'permission_edit'])->name('permissions.edit');
     Route::post('/user/permissions/update/{id}',[PermissionsController::class,'permission_update'])->name('permissions.update');
     Route::get('/user/permissions/delete/{id}',[PermissionsController::class,'permission_delete'])->name('permissions.delete');

     //assets
     Route::get('/assets/manage-assets',[AssetsController::class,'index'])->name('assets.index');
     Route::get('/assets/create',[AssetsController::class,'create'])->name('assets.create');
     Route::post('/assets/store',[AssetsController::class,'store'])->name('assets.store');
     Route::get('/assets/{id}/edit',[AssetsController::class,'edit'])->name('assets.edit');
     Route::post('/assets/update/{id}',[AssetsController::class,'update'])->name('assets.update');
     Route::get('/assets/delete/{id}',[AssetsController::class,'delete'])->name('assets.delete');
     Route::get('/assets/manage-asset-type',[AssetsController::class,'manage_asset_type'])->name('assetstyp.index');
     Route::get('/assets/create-asset-type',[AssetsController::class,'create_asset_type'])->name('assetstyp.create');
     Route::post('/assets/store-asset-type',[AssetsController::class,'store_asset_type'])->name('assetstyp.store');
     Route::get('/assets/edit-asset-type/{id}/edit',[AssetsController::class,'edit_asset_type'])->name('assetstyp.edit');
     Route::post('/assets/update-asset-type/update/{id}',[AssetsController::class,'update_asset_type'])->name('assetstyp.update');
     Route::get('/assets/delete-asset-type/delete/{id}',[AssetsController::class,'delete_asset_type'])->name('assetstyp.delete');

    //send mails
    Route::get('/communication/manage-mail',[EmailController::class,'manage_mail'])->name('emails.index');
    Route::get('/communication/create-mail/{id?}',[EmailController::class,'create_mail'])->name('emails.create');
    Route::get('/communication/create-sms/{id?}',[EmailController::class,'create_sms'])->name('sms.create');
    Route::get('/communication/show-mail/{id}',[EmailController::class,'view_mail'])->name('emails.view');
    Route::get('/communication/delete-mail/{id}',[EmailController::class,'delete_mail'])->name('emails.delete');
    Route::post('/communication/mail-send',[EmailController::class,'sendmail'])->name('email.sendmail');
    Route::post('/communication/sms-send',[EmailController::class,'sendSms'])->name('email.sendsms');


     //customer
     Route::get('/customers/manage-customers',[CustomersController::class,'manage_customers'])->name('customer.index');
     Route::get('/customers/manage-pending-customers',[CustomersController::class,'manage_pending_customers'])->name('customer.pending');
     Route::get('/customers/manage-closed-customers',[CustomersController::class,'manage_closed_customers'])->name('customer.closed');
     Route::get('/customers/manage-restricted-customers',[CustomersController::class,'manage_restricted_customers'])->name('customer.restr');
     Route::get('/customers/manage-dom-customers',[CustomersController::class,'manage_dom_accounts'])->name('customer.dom');
     Route::get('/customers/search-customers',[CustomersController::class,'view_customer'])->name('customer.search');
     Route::get('/customers/create-customers',[CustomersController::class,'customer_create'])->name('customer.create');
     Route::get('/customers/show-customers/{id}',[CustomersController::class,'customer_show'])->name('customer.view');
     Route::post('/customers/store-customers',[CustomersController::class,'customer_store'])->name('customer.store');
     Route::get('/customers/{id}/edit-customers',[CustomersController::class,'customer_edit'])->name('customer.edit');
     Route::post('/customers/update-customers/{id}',[CustomersController::class,'customer_update'])->name('customer.update');
     Route::get('/customers/delete-customers/{id}',[CustomersController::class,'customer_delete'])->name('customer.delete');
     Route::get('/customers/activate-customers-account/{id}',[CustomersController::class,'customer_activate'])->name('customer.active');
     Route::get('/customers/close-customers-account/{id}',[CustomersController::class,'customer_closed'])->name('customer.close');
     Route::get('/customers/create-mail/{id?}',[CustomersController::class,'create_mail'])->name('customers.emails.create');
     Route::get('/customers/create/sms/{id?}',[CustomersController::class,'create_sms'])->name('customers.sms.create');
     Route::get('/customers/print/statement/{id}',[LoanController::class,'print_customer_statement'])->name('customer.printstatement');
     Route::get('/customers/download-pdf/statement/{id}',[LoanController::class,'pdf_download_Statement'])->name('customer.pdfdownloadstatement');
     Route::post('/customers/upload/csv-data',[CustomersController::class,'store_upload_customer'])->name('customer.uploadcsv');
     Route::post('/customers/activate-close',[CustomersController::class,'activate_close_customer'])->name('customer.acticl');
     Route::post('/customers/reset/pin-password',[CustomersController::class,'customer_reset_pin_password'])->name('customer.resetpasswpin');
    Route::get('/customers/balance/export-data',[CustomersController::class,'export_customerbalance_data'])->name('customer.balance.export');

     //expenses
     Route::get('/expenses/manage-expenses',[ExpensesController::class,'index'])->name('expenses.index');
     Route::get('/expenses/create',[ExpensesController::class,'create'])->name('expenses.create');
     Route::post('/expenses/store',[ExpensesController::class,'store'])->name('expenses.store');
     Route::get('/expenses/{id}/edit',[ExpensesController::class,'edit'])->name('expenses.edit');
     Route::post('/expenses/update/{id}',[ExpensesController::class,'update'])->name('expenses.update');
     Route::get('/expenses/delete/{id}',[ExpensesController::class,'delete'])->name('expenses.delete');
     Route::get('/expenses/manage-expense-type',[ExpensesController::class,'manage_expense_type'])->name('expensestyp.index');
     Route::get('/expenses/create-expense-type',[ExpensesController::class,'create_expense_type'])->name('expensestyp.create');
     Route::post('/expenses/store-expense-type',[ExpensesController::class,'store_expense_type'])->name('expensestyp.store');
     Route::get('/expenses/edit-expense-type/{id}/edit',[ExpensesController::class,'edit_expense_type'])->name('expensestyp.edit');
     Route::post('/expenses/update-expense-type/update/{id}',[ExpensesController::class,'update_expense_type'])->name('expensestyp.update');
     Route::get('/expenses/delete-expense-type/delete/{id}',[ExpensesController::class,'delete_expense_type'])->name('expensestyp.delete');

     //other income
     Route::get('/other-income/manage-other-income',[OtherincomeController::class,'index'])->name('income.index');
     Route::get('/other-income/create',[OtherincomeController::class,'create'])->name('income.create');
     Route::post('/other-income/store',[OtherincomeController::class,'store'])->name('income.store');
     Route::get('/other-income/{id}/edit',[OtherincomeController::class,'edit'])->name('income.edit');
     Route::post('/other-income/update/{id}',[OtherincomeController::class,'update'])->name('income.update');
     Route::get('/other-income/delete/{id}',[OtherincomeController::class,'delete'])->name('income.delete');
     Route::get('/other-income/manage-income-type',[OtherincomeController::class,'manage_income_type'])->name('incometyp.index');
     Route::get('/other-income/create-income-type',[OtherincomeController::class,'create_income_type'])->name('incometyp.create');
     Route::post('/other-income/store-income-type',[OtherincomeController::class,'store_income_type'])->name('incometyp.store');
     Route::get('/other-income/edit-income-type/{id}/edit',[OtherincomeController::class,'edit_income_type'])->name('incometyp.edit');
     Route::post('/other-income/update-income-type/update/{id}',[OtherincomeController::class,'update_income_type'])->name('incometyp.update');
     Route::get('/other-income/delete-eincometype/delete/{id}',[OtherincomeController::class,'delete_income_type'])->name('incometyp.delete');

    //settings
    Route::get('/settings',[SettingsController::class,'settings'])->name('setting');
    Route::post('/settings/save',[SettingsController::class,'update_settings'])->name('setting.save');
    Route::post('/settings/check-bvn',[SettingsController::class,'check_bvn'])->name('checkbvn');

    //deposit mgmt/saving product
    Route::get('/manage/savings/products',[DepositmgmtController::class,'manage_saving_product'])->name('savings.product');
    Route::get('/create/savings/products',[DepositmgmtController::class,'saving_product_create'])->name('savings.product.create');
    Route::post('/savings/products/store',[DepositmgmtController::class,'saving_product_store'])->name('savings.product.store');
    Route::get('/savings/products/{id}/edit',[DepositmgmtController::class,'saving_product_edit'])->name('savings.product.edit');
    Route::post('/savings/products/update/{id}',[DepositmgmtController::class,'saving_product_update'])->name('savings.product.update');
    Route::get('/savings/products/delete/{id}',[DepositmgmtController::class,'saving_product_delete'])->name('savings.product.delete');

    //deposit mgmt/savings fee
    Route::get('/manage/savings/fees',[DepositmgmtController::class,'manage_saving_fee'])->name('savings.fee');
    Route::get('/create/savings/fees',[DepositmgmtController::class,'saving_fee_create'])->name('savings.fee.create');
    Route::post('/savings/fees/store',[DepositmgmtController::class,'saving_fee_store'])->name('savings.fee.store');
    Route::get('/savings/fees/{id}/edit',[DepositmgmtController::class,'saving_fee_edit'])->name('savings.fee.edit');
    Route::post('/savings/fees/update/{id}',[DepositmgmtController::class,'saving_fee_update'])->name('savings.fee.update');
    Route::get('/savings/fees/delete/{id}',[DepositmgmtController::class,'saving_fee_delete'])->name('savings.fee.delete');

    //deposit mgmt/savings transaction
    Route::get('/savings/manage-transaction',[DepositmgmtController::class,'manage_saving_tran'])->name('savings.transaction');
    Route::post('/savings/update-transaction',[DepositmgmtController::class,'saving_tran_update'])->name('savings.transaction.update');
    Route::get('/savings/delete-transaction/{id}',[DepositmgmtController::class,'saving_tran_delete'])->name('savings.transaction.delete');
    Route::get('/savings/customer-balance',[DepositmgmtController::class,'saving_customer_balance'])->name('savings.cutomers.balance');
    Route::get('/savings/details-transaction/{id}',[DepositmgmtController::class,'saving_tran_details'])->name('saving.transaction.details');
    Route::get('/savings/print-statement/{id}',[DepositmgmtController::class,'print_statement'])->name('saving.print_statement');
    Route::get('/savings/pdf-statement/{id}',[DepositmgmtController::class,'pdf_statement'])->name('saving.pdf_statement');

    //deposit mgmt / manage all accounts. transfers
    Route::get('/get-accounts',[DepositmgmtController::class,'manage_all_accounts'])->name('savings.accounts');
    Route::get('/get-accounts/details',[PageController::class,'get_account_details'])->name('savings.accounts.details');
    Route::get('/get-transactions/slipno',[PageController::class,'get_transaction_slip'])->name('savings.checkslipnumber');
    Route::get('/savings/accounts/fund-transfer',[DepositmgmtController::class,'accounts_transfer_funds'])->name('savings.transfer-funds');
    Route::post('/savings/accounts/transfer',[DepositmgmtController::class,'accounts_transfer'])->name('savings.accounttransfer');
    Route::get('/savings/accounts/create/transaction',[DepositmgmtController::class,'create_transactions'])->name('savings.create.transactions');
    Route::post('/savings/accounts/store/transaction',[DepositmgmtController::class,'store_transactions'])->name('savings.store.transactions');
    Route::get('/savings/accounts/uploads',[DepositmgmtController::class,'upload_transactions'])->name('uploadtrxpg');
    Route::post('/savings/accounts/upload/transaction',[DepositmgmtController::class,'store_upload_transactions'])->name('uploadtrx');
    Route::get('/savings/accounts/approve/transaction/data',[DepositmgmtController::class,'transactions_approve_data'])->name('approvdata');
    Route::get('/savings/accounts/approve/gltransaction/data',[DepositmgmtController::class,'transactions_approve_GL_data'])->name('glapprovdata');
    Route::get('/savings/accounts/approve-transaction/{ref}/{cusid}',[TransactionsController::class,'approve_transactions'])->name('approveTrnx');
    Route::get('/savings/accounts/approve-gltransaction/{ref}',[TransactionsController::class,'approve_GLtransactions'])->name('GLapproveTrnx');
    Route::get('/savings/accounts/create/charges-posting/transaction',[DepositmgmtController::class,'charges_posting'])->name('charges.posting.create');
    Route::get('/savings/accounts/transaction/viewuploadstatus',[DepositmgmtController::class,'uploadtrx_status'])->name('viewuploadstatus');
    Route::post('/savings/accounts/transaction/changeuploadstaus',[DepositmgmtController::class,'changeuploadstaus'])->name('changeuploadstaus');
    Route::get('/savings/transaction/overdraft',[DepositmgmtController::class,'overdraft'])->name('overdraft');
    Route::post('/savings/accounts/transaction/overdraft',[DepositmgmtController::class,'overdraft_transactions'])->name('overdrafttransactions');

    //bankTransfer
    Route::get('/savings/accounts/bank-transaction',[TransactionsController::class,'bank_transactions'])->name('savings.bank.transactions');

    //deposit mgmt / charges
    Route::get('/manage/charges/fees',[ChargesController::class,'manage_charges_fee'])->name('charges.index');
    Route::get('/create/charges/fees',[ChargesController::class,'charges_fee_create'])->name('charges.create');
    Route::post('/charges/fees/store',[ChargesController::class,'charges_fee_store'])->name('charges.store');
    Route::get('/charges/fees/{id}/edit',[ChargesController::class,'charges_fee_edit'])->name('charges.edit');
    Route::post('/charges/fees/update/{id}',[ChargesController::class,'charges_fee_update'])->name('charges.update');
    Route::get('/charges/fees/delete/{id}',[ChargesController::class,'charges_fee_delete'])->name('charges.delete');

    //loan mgmt / loan products
    Route::get('/manage/loan/products',[LoanProductController::class,'manage_loan_product'])->name('loan.product.index');
    Route::get('/create/loan/products',[LoanProductController::class,'loan_product_create'])->name('loan.product.create');
    Route::post('/loan/products/store',[LoanProductController::class,'loan_product_store'])->name('loan.product.store');
    Route::get('/loan/products/{id}/edit',[LoanProductController::class,'loan_product_edit'])->name('loan.product.edit');
    Route::post('/loan/products/update/{id}',[LoanProductController::class,'loan_product_update'])->name('loan.product.update');
    Route::get('/loan/products/delete/{id}',[LoanProductController::class,'loan_product_delete'])->name('loan.product.delete');
    Route::get('/loan/products/details',[LoanProductController::class,'loan_products_details'])->name('loan.products.details');

    //loan mgmt / loan fees
    Route::get('/manage/loan/fees',[LoanfeesController::class,'manage_loan_fee'])->name('loan.fee.index');
    Route::get('/create/loan/fees',[LoanfeesController::class,'loan_fee_create'])->name('loan.fee.create');
    Route::post('/loan/fees/store',[LoanfeesController::class,'loan_fee_store'])->name('loan.fee.store');
    Route::get('/loan/fees/{id}/edit',[LoanfeesController::class,'loan_fee_edit'])->name('loan.fee.edit');
    Route::post('/loan/fees/update/{id}',[LoanfeesController::class,'loan_fee_update'])->name('loan.fee.update');
    Route::get('/loan/fees/delete/{id}',[LoanfeesController::class,'loan_fee_delete'])->name('loan.fee.delete');

    //loan mgmt / all- loan
    Route::get('/manage/all-loan',[LoanController::class,'index'])->name('loan.index');
    Route::get('/manage/loan/create',[LoanController::class,'create'])->name('loan.create');
    Route::post('/manage/loan/store',[LoanController::class,'store'])->name('loan.store');
    Route::get('/manage/loan/details/{id}',[LoanController::class,'show'])->name('loan.show');
    Route::get('/manage/loan/search',[LoanController::class,'view_loan'])->name('loan.search');
    Route::get('/manage/loan/close/{id}',[LoanController::class,'loan_close'])->name('loan.close');
    Route::get('/manage/loan/{id}/edit',[LoanController::class,'edit'])->name('loan.edit');
    Route::post('/manage/loan/update/{id}',[LoanController::class,'update'])->name('loan.update');
    Route::get('/manage/loan/delete/{id}',[LoanController::class,'delete'])->name('loan.delete');
    Route::post('/manage/loan/approve/{id}',[LoanController::class,'approve'])->name('loan.approve');
    Route::get('/manage/loan/unapprove/{id}',[LoanController::class,'unapprove'])->name('loan.unapprove');
    Route::post('/manage/loan/disburse/{id}',[LoanController::class,'disburse'])->name('loan.disburse');
    Route::get('/manage/loan/undisburse/{id}',[LoanController::class,'undisburse'])->name('loan.undisburse');
    Route::post('/manage/loan/withdraw/{id}',[LoanController::class,'withdraw'])->name('loan.withdraw');
    Route::get('/manage/loan/unwithdraw/{id}',[LoanController::class,'unwithdraw'])->name('loan.unwithdraw');
    Route::post('/manage/loan/write_off/{id}',[LoanController::class,'write_off'])->name('loan.write_off');
    Route::post('/manage/loan/decline/{id}',[LoanController::class,'decline'])->name('loan.decline');
    Route::get('/manage/loan/unwrite_off/{id}',[LoanController::class,'unwrite_off'])->name('loan.unwrite_off');
    Route::post('/manage/loan/override/{id}',[LoanController::class,'loan_override'])->name('loan.override');
    Route::get('/manage/loan/{id}/reschedule',[LoanController::class,'reschedule'])->name('loan.reschedule');
    Route::post('/manage/loan/reschedule/store{id}',[LoanController::class,'reschedule_store'])->name('loan.reschedule.store');
    Route::get('/manage/loan/print-statement/{id}',[LoanController::class,'print_loan_statement'])->name('print.loan.statement');
    Route::get('/manage/loan/print-offer-letter/{id}',[LoanController::class,'print_offer_letter'])->name('print.offer');
    Route::get('/manage/loan/download-statement/pdf/{id}',[LoanController::class,'pdf_download_Statement'])->name('download.loan.statement');
    Route::get('/manage/loan/email-statement/{id}',[LoanController::class,'email_customer_statement'])->name('email.loan.statement');
    Route::get('/manage/loan-schedule/{id}/edit',[LoanController::class,'edit_schedule'])->name('schedule.edit');
    Route::post('/manage/loan/update-schedule/{id}',[LoanController::class,'update_schedule'])->name('schedule.update');
    Route::get('/manage/loan/print-schedule/{id}',[LoanController::class,'print_schedule'])->name('schedule.print');
    Route::get('/manage/loan/pdf-schedule/{id}',[LoanController::class,'pdf_schedule'])->name('schedule.downloadpdf');
    Route::get('/manage/loan/email-loan-schedule/{id}',[LoanController::class,'email_loan_schedule'])->name('schedule.loanemail');
    Route::get('/manage/loan-provision',[LoanController::class,'loan_provision'])->name('loan.provision');
    Route::post('/manage/loan-provision/update/{id}',[LoanController::class,'loan_provision_update'])->name('loan.provision.update');
    Route::get('/manage/loan-sectors',[LoanController::class,'loan_sector'])->name('loan.sector.index');
    Route::post('/manage/loan-sector/update-create',[LoanController::class,'loan_sector_update_create'])->name('loan.sector.updatecreate');
    Route::get('/loan/export-data',[LoanController::class,'exportloandata'])->name('ld.export');
    Route::get('/manage/loan-outstanding',[LoanController::class,'getoutstandingdata'])->name('loan.outsnt');
    Route::get('/view/loan-statement',[LoanController::class,'ViewLoanStatement'])->name('loan.statement');


    //collateral
    Route::get('/manage/all-collateral',[CollateralController::class,'index'])->name('colla.index');
    Route::get('/manage/collateral/create',[CollateralController::class,'create'])->name('colla.create');
    Route::post('/manage/collateral/store',[CollateralController::class,'store'])->name('colla.store');
    Route::get('/manage/collateral/show/{id}',[CollateralController::class,'show'])->name('colla.show');
    Route::get('/manage/collateral/{id}/edit',[CollateralController::class,'edit'])->name('colla.edit');
    Route::post('/manage/collateral/update/{id}',[CollateralController::class,'update'])->name('colla.update');
    Route::get('/manage/collateral/delete/{id}',[CollateralController::class,'delete'])->name('colla.delete');

    //collateral Type
    Route::get('/manage/all-collateral-type',[CollateralController::class,'collateral_type_index'])->name('collatype.index');
    Route::get('/manage/collateral-type/create',[CollateralController::class,'collateral_type_create'])->name('collatype.create');
    Route::post('/manage/collateral-type/store',[CollateralController::class,'collateral_type_store'])->name('collatype.store');
    Route::get('/manage/collateral-type/{id}/edit',[CollateralController::class,'collateral_type_edit'])->name('collatype.edit');
    Route::post('/manage/collateral-type/update/{id}',[CollateralController::class,'collateral_type_update'])->name('collatype.update');
    Route::get('/manage/collateral-type/delete/{id}',[CollateralController::class,'collateral_type_delete'])->name('collatype.delete');

    //loan comment
    Route::get('loan_comment/data', [LoanCommentController::class,'index'])->name('comment.index');
    Route::get('/loan_comment/create', [LoanCommentController::class,'create'])->name('comment.create');
    Route::post('/loan_comment/store', [LoanCommentController::class,'store'])->name('comment.store');
    Route::get('/loan_comment/{id}/edit', [LoanCommentController::class,'edit'])->name('comment.edit');
    // Route::get('/loan_comment/{loan_comment}/show', 'LoanCommentController@show');
    Route::post('/loan_comment/update/{id}', [LoanCommentController::class,'update'])->name('comment.update');
    Route::get('/loan_comment/delete/{id}', [LoanCommentController::class,'delete'])->name('comment.delete');

    //repayment
    Route::get('/repayment/loan/data', [RepaymentController::class,'index'])->name('repay.index');
    Route::get('/repayment/loan/create', [RepaymentController::class,'create'])->name('repay.create');
    Route::post('/repayment/loan/store', [RepaymentController::class,'store'])->name('repay.store');
    Route::get('/repayment/loan/{id}/edit', [RepaymentController::class,'edit'])->name('repay.edit');
    Route::post('/repayment/loan/update/{id}', [RepaymentController::class,'update'])->name('repay.update');
    Route::get('/repayment/loan/delete/{id}', [RepaymentController::class,'delete'])->name('repay.delete');
    Route::get('/repayment/loan/print/{id}', [RepaymentController::class,'print'])->name('repay.print');
    Route::get('/repayment/loan/pdf/{id}', [RepaymentController::class,'pdf'])->name('repay.pdf');
    Route::get('/repayment/loan/getuser-loan-details', [RepaymentController::class,'getuserloandetails'])->name('getuserloandetails');

        //reports
    Route::get('reports/balance-sheet',[ReportsController::class,'balancesheet'])->name('report.balancesheet');
    Route::get('reports/trial-balance',[ReportsController::class,'trialbalance'])->name('report.trialbalance');
    Route::get('reports/balance-sheet/print',[ReportsController::class,'print_balancesheet'])->name('report.printbalsht');
    Route::get('reports/callover',[ReportsController::class,'callover'])->name('report.callover');
    Route::get('reports/cashflow',[ReportsController::class,'cashflow'])->name('report.cashflow');
    Route::get('reports/reference-search',[ReportsController::class,'reference_search'])->name('report.refsearch');
    Route::get('reports/collection-project',[ReportsController::class,'collection_project'])->name('report.collproject');
    Route::get('reports/collection-report',[ReportsController::class,'collection_report'])->name('report.collreport');
    Route::get('reports/posting-approval',[ReportsController::class,'posting_approval'])->name('report.postingapp');
    Route::get('reports/customer-statement',[ReportsController::class,'customer_statement'])->name('report.customerstatement');
    Route::get('reports/customer-balance',[ReportsController::class,'customer_balance'])->name('report.customerbal');
    Route::get('reports/customer-view',[ReportsController::class,'customer_view'])->name('report.customerdetail');
    Route::get('reports/profit-loss',[ReportsController::class,'profit_loss'])->name('report.profitloss');
    Route::get('reports/loan-balance',[ReportsController::class,'loan_balance'])->name('report.loanbal');
    Route::get('reports/loan-classification',[ReportsController::class,'loan_classification'])->name('report.loanclasfi');
    Route::get('reports/loan-list',[ReportsController::class,'loan_list'])->name('report.loanlist');
    Route::get('reports/repayment-report',[ReportsController::class,'repayment_report'])->name('report.loanrepayrept');
    Route::get('reports/loan-transaction-report',[ReportsController::class,'loan_transaction'])->name('report.loantrx');
    Route::get('reports/chart-of-accounts-report',[ReportsController::class,'chart_of_accounts'])->name('report.chartaccounts');
    Route::get('reports/accounts-mgmt-report',[ReportsController::class,'accounts_mgmt_report'])->name('report.accountsmgmt');
    Route::get('reports/fundtransfer-report',[ReportsController::class,'fund_transfer_report'])->name('report.trnsfdata');
    Route::get('reports/vendors-data-report',[ReportsController::class,'vendors_data_report'])->name('report.utilitydata');
    Route::get('reports/cbn-returns-report',[ReportsController::class,'cbn_returns_report'])->name('report.cbnreport');
    Route::post('reports/generate-cbn-returns-report',[ReportsController::class,'generate_cbn_report'])->name('report.generatetcbnreport');
    Route::get('reports/inward-transactions',[ReportsController::class,'notificationpayload'])->name('report.inward');
    Route::get('reports/get-ledger-details',[ReportsController::class,'ledger_details'])->name('ledgerdetails');
    Route::get('reports/tsq_report',[ReportsController::class,'tsq_report'])->name('tsq');
    Route::get('reports/fxmgmt_report',[ReportsController::class,'fxmgmt_report'])->name('reportfxmgt');
    Route::get('reports/savingbalance_report',[ReportsController::class,'savingbalance_report'])->name('savingbalance_report');
    Route::get('reports/savingbalance_export',[ReportsController::class,'savingbalances_export'])->name('savingbalances.export');
    Route::get('reports/fdmgmt_report',[ReportsController::class,'fixedsepo_report'])->name('reportfixdp');
    Route::post('reports/get-query-transaction-status',[ReportsController::class,'queryTransactionStatus'])->name('gettsqrecord');
    Route::get('reports/get-transaction-details',[ReportsController::class,'tranx_details'])->name('tranxdetails');

        //payroll
    Route::get('/payroll/all-data', [PayrollController::class,'index'])->name('payroll.index');
    Route::get('/payroll/create', [PayrollController::class,'create'])->name('payroll.create');
    Route::post('/payroll/store', [PayrollController::class,'store'])->name('payroll.store');
    Route::get('/payroll/{id}/edit', [PayrollController::class,'edit'])->name('payroll.edit');
    Route::post('/payroll/update/{id}', [PayrollController::class,'update'])->name('payroll.update');
    Route::get('/payroll/delete/{id}', [PayrollController::class,'delete'])->name('payroll.delete');
    Route::get('/payroll/create/template', [PayrollController::class,'create_template'])->name('payroll.create.template');
    Route::post('/payroll/store/template', [PayrollController::class,'store_template'])->name('payroll.store.template');
    Route::get('/payroll/delete/template/{id}', [PayrollController::class,'delete_template'])->name('payroll.delete.template');

        //payment structure
    Route::get('/payment/structure', [PayrollController::class,'payment_structure'])->name('payment.structure');
    Route::post('/payment/structure/store', [PayrollController::class,'payment_structure_store'])->name('payment.structure.store');
    Route::get('/payment/structure/{id}/edit', [PayrollController::class,'payment_structure_edit'])->name('payment.structure.edit');
    Route::post('/payment/structure/update/{id}', [PayrollController::class,'payment_structure_update'])->name('payment.structure.update');
    Route::get('/payment/payslips', [PayrollController::class,'payslips'])->name('payslips');
    Route::any('/payment/payslips/send/mail', [PayrollController::class,'payslips_send_mail'])->name('payslip.send');
    Route::get('/payment/payslip/generate', [PayrollController::class,'payslip_generate'])->name('payslip.generate');
    Route::post('/payment/payslip/save', [PayrollController::class,'payslip_save'])->name('payslip.save');
    Route::get('/payment/payslip/show', [PayrollController::class,'payslip_view'])->name('payslip.view');
    Route::get('/payment/payslip/pdf-print', [PayrollController::class,'pdf_print_payslip'])->name('payslip.print.pdf');

    //account Management
    Route::get('/account-mgt/account-types',[AccountmgtController::class,'account_type'])->name('actype');
    Route::post('/account-mgt/account-update-code/{id}',[AccountmgtController::class,'update_accountcode'])->name('update.accountcode');
    Route::get('/account-mgt/manage-gl-transaction',[AccountmgtController::class,'manage_gl_trans'])->name('manage.gltrx');

    //account category
    Route::get('/account-mgt/account-category',[AccountmgtController::class,'account_category_index'])->name('ac.category.index');
    Route::get('/account-mgt/account-category/uploads',[AccountmgtController::class,'batch_upload'])->name('ac.category.batchupload');
    Route::post('/account-mgt/batch/uploads/store',[AccountmgtController::class,'batch_upload_store'])->name('batch_upload.store');
    Route::get('/account-mgt/account-category/create',[AccountmgtController::class,'account_category_create'])->name('ac.category.create');
    Route::post('/account-mgt/account-category/store',[AccountmgtController::class,'account_category_store'])->name('ac.category.store');
    Route::get('/account-mgt/account-category/{id}/edit',[AccountmgtController::class,'account_category_edit'])->name('ac.category.edit');
    Route::post('/account-mgt/account-category/update/{id}',[AccountmgtController::class,'account_category_update'])->name('ac.category.update');
    Route::get('/account-mgt/account-category/delete/{id}',[AccountmgtController::class,'account_category_delete'])->name('ac.category.delete');
    Route::post('/account-mgt/account-category/remove/categories',[AccountmgtController::class,'multiple_account_category_delete'])->name('removecate');

    //capital
    Route::get('/account-mgt/capital',[AccountmgtController::class,'capital_index'])->name('capital.index');
    Route::get('/account-mgt/capital/create',[AccountmgtController::class,'capital_create'])->name('capital.create');
    Route::post('/account-mgt/capital/store',[AccountmgtController::class,'capital_store'])->name('capital.store');
    Route::get('/account-mgt/capital/{id}/edit',[AccountmgtController::class,'capital_edit'])->name('capital.edit');
    Route::post('/account-mgt/capital/update/{id}',[AccountmgtController::class,'capital_update'])->name('capital.update');
    Route::get('/account-mgt/capital/delete/{id}',[AccountmgtController::class,'capital_delete'])->name('capital.delete');

    //general ledger
    Route::get('/account-mgt/general-ledger',[AccountmgtController::class,'gl_index'])->name('gl.index');
    Route::get('/account-mgt/general-ledger/create',[AccountmgtController::class,'gl_create'])->name('gl.create');
    Route::post('/account-mgt/general-ledger/store',[AccountmgtController::class,'gl_store'])->name('gl.store');
    Route::get('/account-mgt/general-ledger/{id}/edit',[AccountmgtController::class,'gl_edit'])->name('gl.edit');
    Route::post('/account-mgt/general-ledger/update/{id}',[AccountmgtController::class,'gl_update'])->name('gl.update');
    Route::get('/account-mgt/general-ledger/delete/{id}',[AccountmgtController::class,'gl_delete'])->name('gl.delete');
    Route::get('/account-mgt/general-ledger/status-change/{glid}/{status}',[AccountmgtController::class,'change_gl_status'])->name('gl.status');
    Route::post('/account-mgt/general-ledger/activate-close',[AccountmgtController::class,'activate_deactive_glaccount'])->name('gl.actideactve');

    //subcriptions
    Route::get('/subcription/manage-subcription-plan',[SubcriptionController::class,'manage_plan'])->name('subcriptinplan');
    Route::post('/subcription/store-subcription-plan',[SubcriptionController::class,'store_plan'])->name('store.subcriptinplan');
    Route::get('/subcription/manage-subcription-payments',[SubcriptionController::class,'view_subcription_payment'])->name('viewsubcription');
    Route::get('/subcription/make-subcription-payment',[SubcriptionController::class,'make_subcription_payment'])->name('makesubcriptionpayment');
    Route::get('/subcription/print-payment-receipt/{id}',[SubcriptionController::class,'print_payment_receipt'])->name('printreceipt');
    Route::get('/subcription/check-Glaccount',[SubcriptionController::class,'checkGlaccount'])->name('checkaccount');
    Route::get('/subcription/delete-plan/{id}',[SubcriptionController::class,'delete_plan'])->name('deleteplan');

    //ip whitelisting
    Route::get('/ipwhitelisting/manage-ip-address',[IpwhitelistingController::class,'manage_ipaddress'])->name('manage.ipaddress');
    Route::post('/ipwhitelisting/store-ip',[IpwhitelistingController::class,'store_ipaddress'])->name('store.ipaddress');
    Route::get('/ipwhitelisting/delete-ip/{id}',[IpwhitelistingController::class,'delete_ipaddress'])->name('delete.ipaddress');

    //general ledger transactions
    Route::get('/account-mgt/general-ledger/customer/posting',[AccountmgtController::class,'gl_customer_posting'])->name('gl.customerposting');
    Route::post('/account-mgt/general-ledger/make-transactions',[AccountmgtController::class,'gl_make_transaction'])->name('gl.make_transactions');
    Route::get('/account-mgt/general-ledger/get-gl-code',[PageController::class,'gl_getcode'])->name('gl.getcode');
    Route::get('/account-mgt/general-ledger/reversal',[AccountmgtController::class,'gl_reversal'])->name('gl.reversal');
    Route::get('/account-mgt/general-ledger/validate/transaction/reference',[AccountmgtController::class,'gl_check_transref'])->name('gl.checkref');
    Route::post('/account-mgt/general-ledger/reversal-posting',[AccountmgtController::class,'gl_reversal_posting'])->name('glreversal.posting');

    //vault-till transactions
    Route::get('/account-mgt/vault-till',[AccountmgtController::class,'vault_till_posting'])->name('vault-till-posting');
    Route::get('/account-mgt/fund-gl-account',[AccountmgtController::class,'fund_gl_accounts'])->name('funds.gl');
    Route::post('/account-mgt/credit-gl-account',[AccountmgtController::class,'credit_gl_accounts'])->name('gl.credit');
    Route::post('/account-mgt/make-vault-transaction',[AccountmgtController::class,'make_vault_transactions'])->name('make_vault_transactions');

    //investments
    Route::get('/investment/manage-fixed-deposit',[InvestmentController::class,'manage_fd'])->name('manage.fd');
    Route::get('/investment/create/fixed-deposit',[InvestmentController::class,'create_fd'])->name('create.fd');
    Route::get('/investment/search/fixed-deposit',[InvestmentController::class,'view_fd'])->name('fd.search');
    Route::get('/investment/show/fixed-deposit/{id}',[InvestmentController::class,'show_fd'])->name('show.fd');
    Route::post('/investment/fixed-deposit/store',[InvestmentController::class,'store_fd'])->name('store.fd');
    Route::get('/investment/fixed-deposit/{id}/edit',[InvestmentController::class,'edit_fd'])->name('edit.fd');
    Route::post('/investment/fixed-deposit/update/{id}',[InvestmentController::class,'update_fd'])->name('update.fd');
    Route::get('/investment/fixed-deposit/due',[InvestmentController::class,'fd_duepayment'])->name('due.fd');
    Route::get('/investment/fixed-deposit/delete/{id}',[InvestmentController::class,'delete_fd'])->name('delete.fd');
    Route::post('/investment/fixed-deposit/approve/{id}',[InvestmentController::class,'approve_fd'])->name('fd.approve');
    Route::post('/investment/fixed-deposit/decline/{id}',[InvestmentController::class,'decline_fd'])->name('fd.decline');
    Route::get('/investment/fixed-deposit/{id}/edit-schedule',[InvestmentController::class,'fdedit_schedule'])->name('schedulefd.edit');
    Route::post('/investment/fixed-deposit/update-schedule/{id}',[InvestmentController::class,'fdupdate_schedule'])->name('fdschedule.update');
    Route::get('/investment/fixed-deposit/print-offer-letter/{id}',[InvestmentController::class,'print_offer_letter'])->name('printfd.offer');
    Route::get('/investment/fixed-deposit/print-investment-schedule/{id}',[InvestmentController::class,'print_investment_schedule'])->name('printfd.schedule');
    Route::get('/investment/fixed-deposit/pdf-investment-schedule/{id}',[InvestmentController::class,'pdf_investment_schedule'])->name('schedulefd.downloadpdf');
    Route::get('/investment/fixed-deposit/email-investment-schedule/{id}',[InvestmentController::class,'email_investment_schedule'])->name('schedule.fdemail');
    Route::get('/investment/fixed-deposit/email-investment-offer-letter/{id}',[InvestmentController::class,'email_investment_offer_letter'])->name('fdemail.offer');
    Route::get('/investment/fixed-deposit/liquidation',[InvestmentController::class,'fdliquidation'])->name('liqfd');
    Route::post('/investment/fixed-deposit/liquidation-save',[InvestmentController::class,'fdliquidation_save'])->name('fd.liqutae');
    Route::get('/investment/fixed-deposit/manual-repayment',[InvestmentController::class,'manual_repayment'])->name('manaul_repayment');
    Route::get('/investment/fixed-deposit/export-data',[InvestmentController::class,'exportdata'])->name('fd.export');

    //investment/fixed deposit product
    Route::get('/investment/manage-fixed-deposit-product',[InvestmentController::class,'manage_fd_product'])->name('manage.fdproduct');
    Route::get('/investment/create/manage-fixed-deposit-product',[InvestmentController::class,'create_fd_product'])->name('create.fdproduct');
    Route::post('/investment/store/manage-fixed-deposit-product',[InvestmentController::class,'store_fd_product'])->name('store.fdproduct');
    Route::get('/investment/manage-fixed-deposit-product/{id}/edit',[InvestmentController::class,'edit_fd_product'])->name('edit.fdproduct');
    Route::post('/investment/update/fixed-deposit-product/{id}',[InvestmentController::class,'update_fd_product'])->name('update.fdproduct');
    Route::get('/investment/fixed-deposit-product/delete/{id}',[InvestmentController::class,'fd_product_delete'])->name('delete.fdproduct');
    Route::get('/investment/fixed-deposit-product/details',[InvestmentController::class,'fd_products_details'])->name('fd.products.details');

  //fx management
    Route::get('/fx/exchange-rates/all',[FxController::class,'allrates'])->name('fxrate.all');
    Route::post('/fx/add-update-exchange-rates',[FxController::class,'add_update_rates'])->name('rates.edit.create');

    Route::get('/fx/manage/sales',[FxController::class,'managefx_sales'])->name('managefx.sales');
    Route::get('/fx/manage/sales/details/{id}',[FxController::class,'get_fx_details'])->name('fx.sales.details');
    Route::get('/fx/sales/create',[FxController::class,'fx_sales_create'])->name('fx_sales.create');
    Route::post('/fx/sales/store',[FxController::class,'fx_sales_store'])->name('fx_sales.store');

    Route::get('/fx/manage/purchase',[FxController::class,'managefx_purchase'])->name('managefx.purchase');
    Route::get('/fx/manage/purchase/details/{id}',[FxController::class,'get_fx_details'])->name('fx.purchase.details');
    Route::get('/fx/purchase/create',[FxController::class,'fx_purchase_create'])->name('fx_purchase.create');
    Route::post('/fx/purchase/store',[FxController::class,'fx_purchase_store'])->name('fx_purchase.store');

    Route::get('/fx/reversal',[FxController::class,'fx_reversal'])->name('fx_reversal');
    Route::post('/fx/reversal/details',[FxController::class,'get_fx_reversal_details'])->name('fx_reversaldetails');
    Route::post('/fx/reversal/store',[FxController::class,'fx_reversal_store'])->name('fx_reversalstore');

    //transaction
    Route::get('/verify-bank-account', [TransactionsController::class, 'VerifyBankAccount'])->name('verifybnkacct');

  if($getsetvalue->getsettingskey('payoption') == "1"){

        Route::post('/bank-transfer', [TransactionsController::class, 'transferToBankAccount'])->name('bnkTransfer');

    }elseif($getsetvalue->getsettingskey('payoption') == "2"){

        //Route::post('/initiate-transaction', [MonnifyController::class, 'initiateTransaction'])->middleware(['auth:sanctum','abilities:customer']);
        Route::post('/bank-transfer', [TransactionsController::class, 'transferToBankAccountViaMonnify'])->name('bnkTransfer');

    }elseif($getsetvalue->getsettingskey('payoption') == "3"){
        return "3";
    }elseif($getsetvalue->getsettingskey('payoption') == "4"){
       Route::post('/bank-transfer', [TransactionsController::class, 'transferToBankAccountViawireless'])->name('bnkTransfer');
    }
 });

 Route::get('/customers/account/pending',[CustomersController::class,'getpendingcust'])->name('getpendingcust');
     Route::get('/customers/account/closing',[CustomersController::class,'getclosecust'])->name('getclosecust');
     Route::get('/customers/account/active',[CustomersController::class,'getactivecust'])->name('getactivecust');
     Route::get('/customers/account/restriction',[CustomersController::class,'getrestricust'])->name('getrestcust');
     Route::get('/customers/account/dom-account',[CustomersController::class,'getdomcust'])->name('getdomcust');

 //for subcription
 Route::get('/subcription/make-subcription-payment',[SubcriptionController::class,'make_subcription_payment'])->name('makesubcriptionpayment');
 Route::post('/subcription/save-subcription-payment',[SubcriptionController::class,'store_subcription_payment'])->name('storepayment');
});

// Route::get('/banqpro/cron/birthday-cron',[CronjobController::class,'birthday_cron'])->middleware('log.route');
// Route::get('/banqpro/cron/loan-reminder-cron',[CronjobController::class,'loan_reminder_cron'])->middleware('log.route');
// Route::get('/banqpro/cron/loan-cron',[CronjobController::class,'loan_cron'])->middleware('log.route');
// Route::get('/banqpro/cron/after-loan-maturity-cron',[CronjobController::class,'after_loan_maturity_cron'])->middleware('log.route');
// Route::get('/banqpro/cron/missed-loan-payment-cron',[CronjobController::class,'missed_loan_payment_cron'])->middleware('log.route');
// Route::get('/banqpro/cron/subcription-cron',[CronjobController::class,'subcription_cron'])->middleware('log.route');
// Route::get('/banqpro/cron/subcription-warning-cron',[CronjobController::class,'subcription_warning_cron'])->middleware('log.route');
// Route::get('/banqpro/cron/investment-cron',[CronjobController::class,'investment_cron'])->middleware('log.route');
// Route::get('/banqpro/cron/check-pending-transaction',[CronjobController::class,'checkPendingTransaction_cron'])->middleware('log.route');
