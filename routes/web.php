<?php

declare(strict_types=1);

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DealerController;
use App\Http\Controllers\DealerPaymentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GoogleContactController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/', fn () => view('auth.login'));
Route::get('/home', fn () => view('welcome'));

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// Authenticated Users
Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// Dashboard
use App\Http\Controllers\DashboardController;

Route::middleware(['auth', 'permission:dashboard.view'])
    ->get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

// Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->middleware('permission:profile.view')->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->middleware('permission:profile.update')->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->middleware('permission:profile.update')->name('profile.destroy');
    Route::get('/admin/profile', [AdminController::class, 'profile'])->middleware('permission:profile.view')->name('admin.profile');
    Route::get('/admin/changepassword', [AdminController::class, 'changePassword'])->middleware('permission:profile.password.change')->name('admin.changepassword');
    Route::put('/admin/reset-password', [AdminController::class, 'newPassword'])->middleware('permission:profile.password.change')->name('reset-password');
    Route::post('/admin/storeprofile', [AdminController::class, 'updateProfile'])->middleware('permission:profile.update')->name('store.profile');
});

// Purchases
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/purchases', [PurchaseController::class, 'index'])->middleware('permission:purchases.view')->name('allpurchases');
    Route::get('/admin/purchase/add', [PurchaseController::class, 'newPurchase'])->middleware('permission:purchases.create')->name('purchase.create');
    Route::get('/admin/purchase/add-multiple', [PurchaseController::class, 'newMultiplePurchase'])->middleware('permission:purchases.create')->name('purchase.create.multiple');
    Route::post('/admin/purchase/add', [PurchaseController::class, 'savePurchase'])->middleware('permission:purchases.create')->name('purchase.store');
    Route::get('/admin/purchase/edit/{id}', [PurchaseController::class, 'editPurchase'])->middleware('permission:purchases.edit')->name('purchase.edit');
    Route::post('/admin/purchase/update/{id}', [PurchaseController::class, 'updatePurchase'])->middleware('permission:purchases.edit')->name('purchase.update');
    Route::delete('/admin/purchase/delete/{id}', [PurchaseController::class, 'deleteStock'])->middleware('permission:purchases.delete')->name('delete-stock');
    Route::get('/admin/purchase-detail/{id}', [PurchaseController::class, 'purchaseDetail'])->middleware('permission:purchases.view')->name('purchase-detail');
    Route::get('/admin/purchase/import', [PurchaseController::class, 'importStocks'])->middleware('permission:purchases.import')->name('purchase.importform');
    Route::post('/admin/purchase/importdata', [PurchaseController::class, 'importStocksData'])->middleware('permission:purchases.import')->name('purchase.import');
    Route::get('/admin/purchase/downloadstock', [PurchaseController::class, 'downloadStock'])->middleware('permission:purchases.download.stock')->name('purchase.downloadstock');
});

// Sales
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/sales', [SaleController::class, 'index'])->middleware('permission:sales.view')->name('allsales');
    Route::get('/admin/sales/edit/{id}', [SaleController::class, 'editSale'])->middleware('permission:sales.edit')->name('saleedit');
    Route::get('/admin/sales/{id}', [SaleController::class, 'saleDetail'])->middleware('permission:sales.view')->name('saledetail');
    Route::get('/admin/sale', [SaleController::class, 'newSale'])->middleware('permission:sales.create')->name('new-sale');
    Route::post('/admin/sale', [SaleController::class, 'saveSale'])->middleware('permission:sales.create')->name('save-sale');
    Route::delete('admin/sale/delete/{id}', [SaleController::class, 'deleteSale'])->middleware('permission:sales.delete')->name('delete-sale');
    Route::get('/admin/fetchstockdata/{id}', [SaleController::class, 'fetchModelData'])->middleware('permission:sales.fetch.stock')->name('fetchmodeldata');
    Route::get('/admin/fetchstockonimei/{imei}', [InvoiceController::class, 'fetchModelData'])->middleware('permission:sales.fetch.stock')->name('fetchmodeldata');
});

// Invoices
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/invoices', [InvoiceController::class, 'index'])->middleware('permission:invoices.view')->name('allinvoices');
    Route::get('/admin/invoice/add', [InvoiceController::class, 'newInvoice'])->middleware('permission:invoices.create')->name('newinvoice');
    Route::post('admin/invoice/create-invoice', [InvoiceController::class, 'createInvoice'])->middleware('permission:invoices.create')->name('create-invoice');
    Route::get('/admin/invoice/{id}', [InvoiceController::class, 'invoiceDetail'])->middleware('permission:invoices.detail')->name('invoice-detail');
    Route::get('/admin/invoice/print/{id}', [InvoiceController::class, 'printInvoice'])->middleware('permission:invoices.print')->name('print-invoice');
    Route::get('/admin/invoice/edit/{id}', [InvoiceController::class, 'editInvoice'])->middleware('permission:invoices.edit')->name('invoice-edit');
    Route::post('/admin/invoice/update/{id}', [InvoiceController::class, 'updateInvoice'])->middleware('permission:invoices.edit')->name('invoice-update');
    Route::post('/admin/invoice/delete/{id}', [InvoiceController::class, 'deleteInvoice'])->middleware('permission:invoices.delete')->name('invoice-delete');
});

// Reports
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/reports/sales', [ReportController::class, 'sale'])->middleware('permission:reports.sales.view')->name('sale-report');
    Route::get('/admin/reports/excel', [ReportController::class, 'downloadExcel'])->middleware('permission:reports.sales.export')->name('sale-export');
    Route::get('/admin/reports/buyexcel', [ReportController::class, 'downloadPurchaseExcel'])->middleware('permission:reports.purchases.export')->name('buy-export');
    Route::get('/admin/reports/purchasereport', [ReportController::class, 'downloadPurchaseReport'])->middleware('permission:reports.purchases.view')->name('purchase-report');
    Route::get('/admin/reports/salescharts', [ReportController::class, 'displayCharts'])->middleware('permission:reports.charts.view')->name('saleschart');
    Route::get('/admin/reports/customers', [ReportController::class, 'customers'])->middleware('permission:reports.customers.view')->name('customers');
    Route::get('/admin/reports/exportcustomers', [ReportController::class, 'exportcustomers'])->middleware('permission:reports.customers.export')->name('exportcustomers');
});

// Expenses
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/expenses', [ExpenseController::class, 'index'])->middleware('permission:expenses.view')->name('expenses');
    Route::get('/admin/expenses/add', [ExpenseController::class, 'addExpense'])->middleware('permission:expenses.create')->name('add-expense');
    Route::post('/admin/expenses/save-expense', [ExpenseController::class, 'saveExpense'])->middleware('permission:expenses.create')->name('save-expense');
    Route::get('/admin/expenses/edit/{id}', [ExpenseController::class, 'editExpense'])->middleware('permission:expenses.edit')->name('edit-expense');
    Route::post('/admin/expenses/update/{id}', [ExpenseController::class, 'updateExpense'])->middleware('permission:expenses.edit')->name('expenseedit');
    Route::delete('/admin/expenses/delete/{id}', [ExpenseController::class, 'deleteExpense'])->middleware('permission:expenses.delete')->name('delete-expense');
});

// Transactions
Route::middleware(['auth'])->prefix('admin/transactions')->name('transactions.')->group(function () {
    Route::get('/', [TransactionController::class, 'index'])->middleware('permission:transactions.view')->name('index');
    Route::post('/store', [TransactionController::class, 'store'])->middleware('permission:transactions.create')->name('store');
    Route::delete('/delete/{id}', [TransactionController::class, 'delete'])->middleware('permission:transactions.delete')->name('destroy');
    Route::get('/edit/{id}', [TransactionController::class, 'edit'])->middleware('permission:transactions.edit')->name('edit');
    Route::get('/resync', [TransactionController::class, 'resyncBalances'])->middleware('permission:transactions.resync')->name('resync');
});

// Google Contacts
Route::get('/google/redirect', [GoogleContactController::class, 'redirectToGoogle'])->middleware(['auth'])->name('google.redirect');
Route::get('/google-callback', [GoogleContactController::class, 'handleGoogleCallback'])->middleware(['auth'])->name('google.callback');
Route::post('/sync-contact', [GoogleContactController::class, 'syncContact'])->middleware(['auth', 'permission:google.sync'])->name('sync.contact');

// Roles & Users
Route::middleware(['auth'])->group(function () {
    Route::resource('roles', RoleController::class)->except(['show'])->middleware('permission:roles.view');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:roles.create')->name('roles.store');

    Route::resource('users', UserController::class)->except(['show'])->middleware('permission:users.view');
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:users.create')->name('users.store');
});

// Dealers
Route::middleware(['auth'])->group(function () {
    Route::get('/dealers', [DealerController::class, 'index'])->name('dealers.index');
    Route::post('/dealers', [DealerController::class, 'store'])->name('dealers.store');
    Route::get('/dealers/{id}/edit', [DealerController::class, 'edit'])->name('dealers.edit');
    Route::put('/dealers/{id}', [DealerController::class, 'update'])->name('dealers.update');
    Route::delete('/dealers/{id}', [DealerController::class, 'destroy'])->name('dealers.destroy');
});

// Bills
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/bills', [BillController::class, 'index'])->middleware('permission:bills.view')->name('allbills');
    Route::get('/admin/bill/add', [BillController::class, 'newBill'])->middleware('permission:bills.create')->name('newbill');
    Route::post('/admin/bill/create-bill', [BillController::class, 'createBill'])->middleware('permission:bills.create')->name('create-bill');
    Route::get('/admin/bill/{id}', [BillController::class, 'billDetail'])->middleware('permission:bills.detail')->name('bill-detail');
    Route::get('/admin/bill/print/{id}', [BillController::class, 'printBill'])->middleware('permission:bills.print')->name('print-bill');
    Route::get('/admin/bill/edit/{id}', [BillController::class, 'editBill'])->middleware('permission:bills.edit')->name('bill-edit');
    Route::post('/admin/bill/update/{id}', [BillController::class, 'updateBill'])->middleware('permission:bills.edit')->name('bill-update');
    Route::post('/admin/bill/delete/{id}', [BillController::class, 'deleteBill'])->middleware('permission:bills.delete')->name('bill-delete');
    Route::get('/admin/bill/fetch-model/{imei}', [BillController::class, 'fetchModelData'])->middleware('permission:bills.create')->name('fetch-bill-model');
    Route::get('/admin/bill/fetch-dealer/{id}', [BillController::class, 'fetchDealerData'])->middleware('permission:bills.create')->name('fetch-bill-dealer');
});

// Dealer Payments
Route::middleware(['auth'])->prefix('dealer-payments')->name('dealer-payments.')->group(function () {
    Route::get('/', [DealerPaymentController::class, 'index'])->middleware('permission:dealer-payments.view')->name('index');
    Route::get('/data', [DealerPaymentController::class, 'getDealersData'])->middleware('permission:dealer-payments.view')->name('data');
    Route::get('/{dealerId}/bills', [DealerPaymentController::class, 'getDealerBills'])->middleware('permission:dealer-payments.view')->name('bills');
    Route::post('/store', [DealerPaymentController::class, 'storePayment'])->middleware('permission:dealer-payments.create')->name('store');
});
