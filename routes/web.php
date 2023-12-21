<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AcctCashReceiptController;
use App\Http\Controllers\AcctBankReceiptController;
use App\Http\Controllers\AcctCheckReceiptController;
use App\Http\Controllers\AcctAccountController;
use App\Http\Controllers\AcctJournalVoucherController;
use App\Http\Controllers\AcctJournalVoucherPurchaseController;
use App\Http\Controllers\AcctJournalVoucherSalesController;
use App\Http\Controllers\AcctJournalVoucherCashBankController;
use App\Http\Controllers\AcctCashDisbursementController;
use App\Http\Controllers\AcctCheckDisbursementController;
use App\Http\Controllers\AcctBankDisbursementController;
use App\Http\Controllers\AcctCashReceiptReportController;
use App\Http\Controllers\AcctDebtCardController;
use App\Http\Controllers\AcctCashDisbursementReportController;
use App\Http\Controllers\AcctBankReceiptReportController;
use App\Http\Controllers\AcctBankDisbursementReportController;
use App\Http\Controllers\AcctGeneralLedgerReportController;
use App\Http\Controllers\AcctLedgerReportController;
use App\Http\Controllers\AcctProfitLossYearReportController;
use App\Http\Controllers\AcctProfitLossReportController;
use App\Http\Controllers\AcctBalanceSheetReportController;
use App\Http\Controllers\CoreAgencyController;
use App\Http\Controllers\CoreCustomerController;
use App\Http\Controllers\CoreExpeditionController;
use App\Http\Controllers\CoreGradeController;
use App\Http\Controllers\CorePackageController;
use App\Http\Controllers\CoreSupplierController;
use App\Http\Controllers\GradingController;
use App\Http\Controllers\InvGoodsReceivedNoteController;
use App\Http\Controllers\InvItemCategoryController;
use App\Http\Controllers\InvItemTypeController;
use App\Http\Controllers\InvItemController;
use App\Http\Controllers\InvItemUnitController;
use App\Http\Controllers\InvItemStockController;
use App\Http\Controllers\InvItemStockAdjustmentController;
use App\Http\Controllers\InvWarehouseController;
use App\Http\Controllers\InvWarehouseInApprovalController;
use App\Http\Controllers\InvWarehouseInRequisitionController;
use App\Http\Controllers\InvWarehouseInTypeController;
use App\Http\Controllers\InvWarehouseOutApprovalController;
use App\Http\Controllers\InvWarehouseOutRequisitionController;
use App\Http\Controllers\InvWarehouseOutTypeController;
use App\Http\Controllers\InvWarehouseTransferTypeController;
use App\Http\Controllers\InvWarehouseLocationController;
use App\Http\Controllers\InvWarehouseTransferController;
use App\Http\Controllers\InvWarehouseTransferReceivedNoteController;
use App\Http\Controllers\PreferenceCompanyController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseOrderApprovalController;
use App\Http\Controllers\PurchaseOrderReturnController;
use App\Http\Controllers\PurchaseOrderReturnReportController;
use App\Http\Controllers\PurchasePaymentController;
use App\Http\Controllers\SalesCollectionController;
use App\Http\Controllers\SalesDeliveryOrderController;
use App\Http\Controllers\SalesDeliveryNoteController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SalesOrderReturnController;
use App\Http\Controllers\SalesOrderApprovalController;
use App\Http\Controllers\SystemUserController;
use App\Http\Controllers\SystemUserGroupController;
use App\Http\Controllers\InvItemStockCardController;
use App\Http\Controllers\ReturnPDP_Controller;
use App\Http\Controllers\ReturnPDP_LostOnExpedition_Controller;
use App\Http\Controllers\BuyersAcknowledgmentController;
use App\Http\Controllers\SalesCollectionPieceController;
use App\Http\Controllers\KwitansiController;
use App\Http\Controllers\SalesCollectionDiscountController;
use App\Http\Controllers\SalesPromotionController;


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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
// Route::get('/', [HomeController::class, 'index']);
Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/cash-receipt', [AcctCashReceiptController::class, 'index'])->name('cash-receipt');
Route::get('/cash-receipt/add', [AcctCashReceiptController::class, 'addAcctCashReceipt'])->name('add-cash-receipt');
Route::post('/cash-receipt/process-add-cash-receipt', [AcctCashReceiptController::class, 'processAddAcctCashReceipt'])->name('process-add-cash-receipt');
Route::post('/cash-receipt/filter', [AcctCashReceiptController::class, 'filterAcctCashReceipt'])->name('filter-cash-receipt');
Route::post('/cash-receipt/select-project', [AcctCashReceiptController::class, 'selectProjectAcctCashReceipt'])->name('select-project-cash-receipt');
Route::post('/cash-receipt/add-array', [AcctCashReceiptController::class, 'addArrayAcctCashReceiptItem'])->name('add-cash-receipt-array');
Route::get('/cash-receipt/delete-array/{record_id}', [AcctCashReceiptController::class, 'deleteArrayAcctCashReceiptItem'])->name('delete-cash-receipt-array');
Route::get('/cash-receipt/detail/{cash_receipt_id}', [AcctCashReceiptController::class, 'detailAcctCashReceipt'])->name('detail-cash-receipt');
Route::get('/cash-receipt/void/{cash_receipt_id}', [AcctCashReceiptController::class, 'voidAcctCashReceipt'])->name('void-cash-receipt');
Route::post('/cash-receipt/process-void/', [AcctCashReceiptController::class, 'processVoidAcctCashReceipt'])->name('process-void-cash-receipt');
Route::post('/cash-receipt/elements-add/', [AcctCashReceiptController::class, 'elements_add'])->name('elements-add-cash-receipt');

Route::get('/bank-receipt', [AcctBankReceiptController::class, 'index'])->name('bank-receipt');
Route::get('/bank-receipt/add', [AcctBankReceiptController::class, 'addAcctBankReceipt'])->name('add-bank-receipt');
Route::post('/bank-receipt/process-add-bank-receipt', [AcctBankReceiptController::class, 'processAddAcctBankReceipt'])->name('process-add-bank-receipt');
Route::post('/bank-receipt/filter', [AcctBankReceiptController::class, 'filterAcctBankReceipt'])->name('filter-bank-receipt');
Route::post('/bank-receipt/select-project', [AcctBankReceiptController::class, 'selectProjectAcctBankReceipt'])->name('select-project-bank-receipt');
Route::post('/bank-receipt/add-array', [AcctBankReceiptController::class, 'addArrayAcctBankReceiptItem'])->name('add-bank-receipt-array');
Route::get('/bank-receipt/delete-array/{record_id}', [AcctBankReceiptController::class, 'deleteArrayAcctBankReceiptItem'])->name('delete-bank-receipt-array');
Route::get('/bank-receipt/detail/{bank_receipt_id}', [AcctBankReceiptController::class, 'detailAcctBankReceipt'])->name('detail-bank-receipt');
Route::get('/bank-receipt/void/{bank_receipt_id}', [AcctBankReceiptController::class, 'voidAcctBankReceipt'])->name('void-bank-receipt');
Route::post('/bank-receipt/process-void/', [AcctBankReceiptController::class, 'processVoidAcctBankReceipt'])->name('process-void-bank-receipt');
Route::post('/bank-receipt/elements-add/', [AcctBankReceiptController::class, 'elements_add'])->name('elements-add-bank-receipt');

Route::get('/check-receipt', [AcctCheckReceiptController::class, 'index'])->name('check-receipt');
Route::get('/check-receipt/add', [AcctCheckReceiptController::class, 'addAcctCheckReceipt'])->name('add-check-receipt');
Route::post('/check-receipt/process-add-check-receipt', [AcctCheckReceiptController::class, 'processAddAcctCheckReceipt'])->name('process-add-check-receipt');
Route::post('/check-receipt/filter', [AcctCheckReceiptController::class, 'filterAcctCheckReceipt'])->name('filter-check-receipt');
Route::post('/check-receipt/select-project', [AcctCheckReceiptController::class, 'selectProjectAcctCheckReceipt'])->name('select-project-check-receipt');
Route::post('/check-receipt/add-array', [AcctCheckReceiptController::class, 'addArrayAcctCheckReceiptItem'])->name('add-check-receipt-array');
Route::get('/check-receipt/delete-array/{record_id}', [AcctCheckReceiptController::class, 'deleteArrayAcctCheckReceiptItem'])->name('delete-check-receipt-array');
Route::get('/check-receipt/detail/{check_receipt_id}', [AcctCheckReceiptController::class, 'detailAcctCheckReceipt'])->name('detail-check-receipt');
Route::get('/check-receipt/void/{check_receipt_id}', [AcctCheckReceiptController::class, 'voidAcctCheckReceipt'])->name('void-check-receipt');
Route::post('/check-receipt/process-void/', [AcctCheckReceiptController::class, 'processVoidAcctCheckReceipt'])->name('process-void-check-receipt');
Route::post('/check-receipt/elements-add/', [AcctCheckReceiptController::class, 'elements_add'])->name('elements-add-check-receipt');


Route::get('/cash-disbursement', [AcctCashDisbursementController::class, 'index'])->name('cash-disbursement');
Route::get('/cash-disbursement/add', [AcctCashDisbursementController::class, 'addAcctCashDisbursement'])->name('add-cash-disbursement');
Route::post('/cash-disbursement/process-add-disbursement', [AcctCashDisbursementController::class, 'processAddAcctCashDisbursement'])->name('process-add-cash-disbursement');
Route::post('/cash-disbursement/filter', [AcctCashDisbursementController::class, 'filterAcctCashDisbursement'])->name('filter-cash-disbursement');
Route::post('/cash-disbursement/select-project', [AcctCashDisbursementController::class, 'selectProjectAcctCashDisbursement'])->name('select-project-cash-disbursement');
Route::post('/cash-disbursement/add-array', [AcctCashDisbursementController::class, 'addArrayAcctCashDisbursementItem'])->name('add-cash-disbursement-array');
Route::get('/cash-disbursement/delete-array/{record_id}', [AcctCashDisbursementController::class, 'deleteArrayAcctCashDisbursementItem'])->name('delete-cash-disbursement-array');
Route::get('/cash-disbursement/detail/{cash_disbursement_id}', [AcctCashDisbursementController::class, 'detailAcctCashDisbursement'])->name('detail-cash-disbursement');
Route::get('/cash-disbursement/void/{cash_disbursement_id}', [AcctCashDisbursementController::class, 'voidAcctCashDisbursement'])->name('void-cash-disbursement');
Route::post('/cash-disbursement/process-void/', [AcctCashDisbursementController::class, 'processVoidAcctCashDisbursement'])->name('process-void-cash-disbursement');
Route::post('/cash-disbursement/elements-add/', [AcctCashDisbursementController::class, 'elements_add'])->name('elements-add-cash-disbursement');

Route::get('/bank-disbursement', [AcctBankDisbursementController::class, 'index'])->name('bank-disbursement');
Route::get('/bank-disbursement/add', [AcctBankDisbursementController::class, 'addAcctBankDisbursement'])->name('add-bank-disbursement');
Route::post('/bank-disbursement/process-add-disbursement', [AcctBankDisbursementController::class, 'processAddAcctBankDisbursement'])->name('process-add-bank-disbursement');
Route::post('/bank-disbursement/filter', [AcctBankDisbursementController::class, 'filterAcctBankDisbursement'])->name('filter-bank-disbursement');
Route::post('/bank-disbursement/select-project', [AcctBankDisbursementController::class, 'selectProjectAcctBankDisbursement'])->name('select-project-bank-disbursement');
Route::post('/bank-disbursement/add-array', [AcctBankDisbursementController::class, 'addArrayAcctBankDisbursementItem'])->name('add-bank-disbursement-array');
Route::get('/bank-disbursement/delete-array/{record_id}', [AcctBankDisbursementController::class, 'deleteArrayAcctBankDisbursementItem'])->name('delete-bank-disbursement-array');
Route::get('/bank-disbursement/detail/{bank_disbursement_id}', [AcctBankDisbursementController::class, 'detailAcctBankDisbursement'])->name('detail-bank-disbursement');
Route::get('/bank-disbursement/void/{bank_disbursement_id}', [AcctBankDisbursementController::class, 'voidAcctBankDisbursement'])->name('void-bank-disbursement');
Route::post('/bank-disbursement/process-void/', [AcctBankDisbursementController::class, 'processVoidAcctBankDisbursement'])->name('process-void-bank-disbursement');
Route::post('/bank-disbursement/elements-add/', [AcctBankDisbursementController::class, 'elements_add'])->name('elements-add-bank-disbursement');

Route::get('/check-disbursement', [AcctCheckDisbursementController::class, 'index'])->name('check-disbursement');
Route::get('/check-disbursement/add', [AcctCheckDisbursementController::class, 'addAcctCheckDisbursement'])->name('add-check-disbursement');
Route::post('/check-disbursement/process-add-disbursement', [AcctCheckDisbursementController::class, 'processAddAcctCheckDisbursement'])->name('process-add-check-disbursement');
Route::post('/check-disbursement/filter', [AcctCheckDisbursementController::class, 'filterAcctCheckDisbursement'])->name('filter-check-disbursement');
Route::post('/check-disbursement/select-project', [AcctCheckDisbursementController::class, 'selectProjectAcctCheckDisbursement'])->name('select-project-check-disbursement');
Route::post('/check-disbursement/add-array', [AcctCheckDisbursementController::class, 'addArrayAcctCheckDisbursementItem'])->name('add-check-disbursement-array');
Route::get('/check-disbursement/delete-array/{record_id}', [AcctCheckDisbursementController::class, 'deleteArrayAcctCheckDisbursementItem'])->name('delete-check-disbursement-array');
Route::get('/check-disbursement/detail/{check_disbursement_id}', [AcctCheckDisbursementController::class, 'detailAcctCheckDisbursement'])->name('detail-check-disbursement');
Route::get('/check-disbursement/void/{check_disbursement_id}', [AcctCheckDisbursementController::class, 'voidAcctCheckDisbursement'])->name('void-check-disbursement');
Route::post('/check-disbursement/process-void/', [AcctCheckDisbursementController::class, 'processVoidAcctCheckDisbursement'])->name('process-void-check-disbursement');
Route::post('/check-disbursement/elements-add/', [AcctCheckDisbursementController::class, 'elements_add'])->name('elements-add-check-disbursement');

Route::get('/report-cash-receipt', [AcctCashReceiptReportController::class, 'index'])->name('report-cash-receipt');
Route::post('/report-cash-receipt/filter', [AcctCashReceiptReportController::class, 'filterAcctCashReceiptReport'])->name('report-filter-cash-receipt');

Route::get('/report-cash-disbursement', [AcctCashDisbursementReportController::class, 'index'])->name('report-cash-disbursement');
Route::post('/report-cash-disbursement/filter', [AcctCashDisbursementReportController::class, 'filterAcctCashDisbursementReport'])->name('report-filter-cash-disbursement');

Route::get('/report-bank-receipt', [AcctBankReceiptReportController::class, 'index'])->name('report-bank-receipt');
Route::post('/report-bank-receipt/filter', [AcctBankReceiptReportController::class, 'filterAcctBankReceiptReport'])->name('report-filter-bank-receipt');

Route::get('/report-bank-disbursement', [AcctBankDisbursementReportController::class, 'index'])->name('report-bank-disbursement');
Route::post('/report-bank-disbursement/filter', [AcctBankDisbursementReportController::class, 'filterAcctBankDisbursementReport'])->name('report-filter-bank-disbursement');

Route::get('/system-user', [SystemUserController::class, 'index'])->name('system-user');
Route::get('/system-user/add', [SystemUserController::class, 'addSystemUser'])->name('add-system-user');
Route::post('/system-user/process-add-system-user', [SystemUserController::class, 'processAddSystemUser'])->name('process-add-system-user');
Route::get('/system-user/edit/{user_id}', [SystemUserController::class, 'editSystemUser'])->name('edit-system-user');
Route::post('/system-user/process-edit-system-user', [SystemUserController::class, 'processEditSystemUser'])->name('process-edit-system-user');
Route::get('/system-user/delete-system-user/{user_id}', [SystemUserController::class, 'deleteSystemUser'])->name('delete-system-user');
Route::get('/system-user/change-password/{user_id}  ', [SystemUserController::class, 'changePassword'])->name('change-password');
Route::post('/system-user/process-change-password', [SystemUserController::class, 'processChangePassword'])->name('process-change-password');


Route::get('/system-user-group', [SystemUserGroupController::class, 'index'])->name('system-user-group');
Route::get('/system-user-group/add', [SystemUserGroupController::class, 'addSystemUserGroup'])->name('add-system-user-group');
Route::post('/system-user-group/process-add-system-user-group', [SystemUserGroupController::class, 'processAddSystemUserGroup'])->name('process-add-system-user-group');
Route::get('/system-user-group/edit/{user_id}', [SystemUserGroupController::class, 'editSystemUserGroup'])->name('edit-system-user-group');
Route::post('/system-user-group/process-edit-system-user-group', [SystemUserGroupController::class, 'processEditSystemUserGroup'])->name('process-edit-system-user-group');
Route::get('/system-user-group/delete-system-user-group/{user_id}', [SystemUserGroupController::class, 'deleteSystemUserGroup'])->name('delete-system-user-group');


Route::get('/account', [AcctAccountController::class, 'index'])->name('account');
Route::get('/account/add', [AcctAccountController::class, 'addAcctAccount'])->name('add-account');
Route::get('/account/delete-account/{account_id}', [AcctAccountController::class, 'deleteAcctAccount'])->name('delete-account');
Route::get('/account/edit/{account_id}', [AcctAccountController::class, 'editAcctAccount'])->name('edit-account');
Route::post('/account/process-add-account', [AcctAccountController::class, 'processAddAcctAccount'])->name('process-add-account');
Route::post('/account/process-edit-account/{account_id}', [AcctAccountController::class, 'processEditAcctAccount'])->name('process-edit-account');

Route::get('/journal', [AcctJournalVoucherController::class, 'index'])->name('journal');
Route::post('/journal/filter', [AcctJournalVoucherController::class, 'filterAcctJournalVoucher'])->name('filter-journal');
Route::post('/journal/select-project', [AcctJournalVoucherController::class, 'selectProjectAcctJournalVoucher'])->name('select-project-journal');
Route::get('/journal/add', [AcctJournalVoucherController::class, 'addJournalVoucher'])->name('add-journal');
Route::post('/journal/add-array', [AcctJournalVoucherController::class, 'addArrayJournalVoucher'])->name('add-journal-array');
Route::get('/journal/delete-array/{record_id}', [AcctJournalVoucherController::class, 'deleteArrayJournalVoucher'])->name('delete-journal-array');
Route::post('/journal/process-add', [AcctJournalVoucherController::class, 'processAddAcctJournalVoucher'])->name('process-add-journal');
Route::get('/journal/printing', [AcctJournalVoucherController::class, 'processPrintingAcctJournalVoucher'])->name('printing-journal');
Route::get('/journal/export', [AcctJournalVoucherController::class, 'processExportAcctJournalVoucher'])->name('export-journal');
Route::post('/journal/elements-add', [AcctJournalVoucherController::class, 'elements_add'])->name('elements-add-journal');
Route::get('/journal/reverse/{journal_voucher_id}', [AcctJournalVoucherController::class, 'reverseJournalVoucher'])->name('reverse-journal-voucher');


Route::get('/journal-Purchase', [AcctJournalVoucherPurchaseController::class, 'index'])->name('journal-purchase');
Route::post('/journal-Purchase/filter', [AcctJournalVoucherPurchaseController::class, 'filterAcctJournalVoucher'])->name('filter-journal-purchase');
Route::get('/journal-Purchase/printing', [AcctJournalVoucherPurchaseController::class, 'processPrintingAcctJournalVoucher'])->name('printing-journal-purchase');
Route::get('/journal-Purchase/export', [AcctJournalVoucherPurchaseController::class, 'processExportAcctJournalVoucher'])->name('export-journal-purchase');

Route::get('/journal-Sales', [AcctJournalVoucherSalesController::class, 'index'])->name('journal-Sales');
Route::post('/journal-Sales/filter', [AcctJournalVoucherSalesController::class, 'filterAcctJournalVoucher'])->name('filter-journal-sales');
Route::get('/journal-Sales/printing', [AcctJournalVoucherSalesController::class, 'processPrintingAcctJournalVoucher'])->name('printing-journal-sales');
Route::get('/journal-Sales/export', [AcctJournalVoucherSalesController::class, 'processExportAcctJournalVoucher'])->name('export-journal-sales');


Route::get('/journal-CashBank', [AcctJournalVoucherCashBankController::class, 'index'])->name('journal-CashBank');
Route::post('/journal-CashBank/filter', [AcctJournalVoucherCashBankController::class, 'filterAcctJournalVoucher'])->name('filter-journal-CashBank');
Route::get('/journal-CashBank/printing', [AcctJournalVoucherCashBankController::class, 'processPrintingAcctJournalVoucher'])->name('printing-journal-CashBank');
Route::get('/journal-CashBank/export', [AcctJournalVoucherCashBankController::class, 'processExportAcctJournalVoucher'])->name('export-journal-CashBank');


// Route::get('/ledger', [AcctGeneralLedgerReportController::class, 'index'])->name('ledger');
// Route::post('/ledger/filter', [AcctGeneralLedgerReportController::class, 'filterAcctGeneralLedgerReport'])->name('filter-ledger');
// Route::get('/ledger/printing', [AcctGeneralLedgerReportController::class, 'processPrintingAcctGeneralLedgerReport'])->name('printing-ledger');
// Route::get('/ledger/export', [AcctGeneralLedgerReportController::class, 'processExportAcctGeneralLedgerReport'])->name('export-ledger');

Route::get('/ledger-report',[AcctLedgerReportController::class, 'index'])->name('ledger-report');
Route::post('/ledger-report/filter',[AcctLedgerReportController::class, 'filterLedgerReport'])->name('filter-ledger-report');
Route::get('/ledger-report/reset-filter',[AcctLedgerReportController::class, 'resetFilterLedgerReport'])->name('reset-filter-ledger-report');
Route::get('/ledger-report/print',[AcctLedgerReportController::class, 'printLedgerReport'])->name('print-ledger-report');
Route::get('/ledger-report/export',[AcctLedgerReportController::class, 'exportLedgerReport'])->name('export-ledger-report');


Route::get('/profit-loss-report',[AcctProfitLossReportController::class, 'index'])->name('profit-loss-report');
Route::post('/profit-loss-report/filter',[AcctProfitLossReportController::class, 'filterProfitLossReport'])->name('filter-profit-loss-report');
Route::get('/profit-loss-report/reset-filter',[AcctProfitLossReportController::class, 'resetFilterProfitLossReport'])->name('reset-filter-profit-loss-report');
Route::get('/profit-loss-report/print',[AcctProfitLossReportController::class, 'printProfitLossReport'])->name('print-profit-loss-report');
Route::get('/profit-loss-report/export',[AcctProfitLossReportController::class, 'exportProfitLossReport'])->name('export-profit-loss-report');


Route::get('balance-sheet-report',[AcctBalanceSheetReportController::class, 'index'])->name('balance-sheet-report');
Route::post('balance-sheet-report/filter',[AcctBalanceSheetReportController::class, 'filterAcctBalanceSheetReport'])->name('filter-balance-sheet-report');
Route::get('balance-sheet-report/reset-filter',[AcctBalanceSheetReportController::class, 'resetFilterAcctBalanceSheetReport'])->name('reset-filter-balance-sheet-report');
Route::get('balance-sheet-report/print',[AcctBalanceSheetReportController::class, 'printAcctBalanceSheetReport'])->name('print-balance-sheet-report');
Route::get('balance-sheet-report/export',[AcctBalanceSheetReportController::class, 'exportAcctBalanceSheetReport'])->name('export-balance-sheet-report');


Route::get('/core-grade', [CoreGradeController::class, 'index'])->name('core-grade');
Route::get('/core-grade/add', [CoreGradeController::class, 'addCoreGrade'])->name('add-core-grade');
Route::post('/core-grade/process-add-core-grade', [CoreGradeController::class, 'processAddCoreGrade'])->name('process-add-core-grade');
Route::get('/core-grade/edit/{grade_id}', [CoreGradeController::class, 'editCoreGrade'])->name('edit-core-grade');
Route::post('/core-grade/process-edit-core-grade', [CoreGradeController::class, 'processEditCoreGrade'])->name('process-edit-core-grade');
Route::get('/core-grade/delete-core-grade/{grade_id}', [CoreGradeController::class, 'deleteCoreGrade'])->name('delete-core-grade');


Route::get('/core-package', [CorePackageController::class, 'index'])->name('core-package');
Route::get('/core-package/add', [CorePackageController::class, 'addCorePackage'])->name('add-core-package');
Route::post('/core-package/process-add-core-package', [CorePackageController::class, 'processAddCorePackage'])->name('process-add-core-package');
Route::get('/core-package/edit/{package_id}', [CorePackageController::class, 'editCorePackage'])->name('edit-core-package');
Route::post('/core-package/process-edit-core-package', [CorePackageController::class, 'processEditCorePackage'])->name('process-edit-core-package');
Route::get('/core-package/delete-core-package/{package_id}', [CorePackageController::class, 'deleteCorePackage'])->name('delete-core-package');


Route::get('/inv-item-category', [InvItemCategoryController::class, 'index'])->name('inv-item-category');
Route::get('/inv-item-category/add', [InvItemCategoryController::class, 'addInvItemCategory'])->name('add-inv-item-category');
Route::post('/inv-item-category/process-add-inv-item-category', [InvItemCategoryController::class, 'processAddInvItemCategory'])->name('process-add-inv-item-category');
Route::get('/inv-item-category/edit/{product_category_id}', [InvItemCategoryController::class, 'editInvItemCategory'])->name('edit-inv-item-category');
Route::post('/inv-item-category/process-edit-inv-item-category', [InvItemCategoryController::class, 'processEditInvItemCategory'])->name('process-edit-inv-item-category');
Route::get('/inv-item-category/delete-inv-item-category/{product_category_id}', [InvItemCategoryController::class, 'deleteInvItemCategory'])->name('delete-inv-item-category');


Route::get('/inv-item-type', [InvItemTypeController::class, 'index'])->name('inv-item-type');
Route::get('/inv-item-type/add', [InvItemTypeController::class, 'addInvItemType'])->name('add-inv-item-type');
Route::post('/inv-item-type/process-add-inv-item-type', [InvItemTypeController::class, 'processAddInvItemType'])->name('process-add-inv-item-type');
Route::get('/inv-item-type/edit/{product_type_id}', [InvItemTypeController::class, 'editInvItemType'])->name('edit-inv-item-type');
Route::post('/inv-item-type/process-edit-inv-item-type', [InvItemTypeController::class, 'processEditInvItemType'])->name('process-edit-inv-item-type');
Route::get('/inv-item-type/delete-inv-item-type/{product_type_id}', [InvItemTypeController::class, 'deleteInvItemType'])->name('delete-inv-item-type');
Route::post('/inv-item-type/add-category', [InvItemTypeController::class, 'addInvItemCategory'])->name('inv-item-type-add-category');


Route::get('/inv-item', [InvItemController::class, 'index'])->name('inv-item');
Route::get('/inv-item/add', [InvItemController::class, 'addInvItem'])->name('add-inv-item');
Route::post('/inv-item/process-add-inv-item', [InvItemController::class, 'processAddInvItem'])->name('process-add-inv-item');
Route::get('/inv-item/edit/{product_type_id}', [InvItemController::class, 'editInvItem'])->name('edit-inv-item');
Route::post('/inv-item/process-edit-inv-item', [InvItemController::class, 'processEditInvItem'])->name('process-edit-inv-item');
Route::get('/inv-item/delete-inv-item/{product_type_id}', [InvItemController::class, 'deleteInvItem'])->name('delete-inv-item');
Route::post('/inv-item/filter', [InvItemController::class, 'filterInvItem'])->name('filter-inv-item');
Route::post('/inv-item/type', [InvItemController::class, 'getCoreType'])->name('inv-item-item-type');
Route::post('/inv-item/add-category', [InvItemController::class, 'addInvItemCategory'])->name('inv-item-add-category');
Route::post('/inv-item/add-type', [InvItemController::class, 'addInvItemType'])->name('inv-item-add-type');
Route::post('/inv-item/add-grade', [InvItemController::class, 'addCoreGrade'])->name('inv-item-add-grade');
Route::post('/inv-item/add-unit', [InvItemController::class, 'addInvItemUnit'])->name('inv-item-add-unit');


Route::get('/inv-item-unit', [InvItemUnitController::class, 'index'])->name('inv-item-unit');
Route::get('/inv-item-unit/add', [InvItemUnitController::class, 'addInvItemUnit'])->name('add-inv-item-unit');
Route::post('/inv-item-unit/process-add-inv-item-unit', [InvItemUnitController::class, 'processAddInvItemUnit'])->name('process-add-inv-item-unit');
Route::get('/inv-item-unit/edit/{product_type_id}', [InvItemUnitController::class, 'editInvItemUnit'])->name('edit-inv-item-unit');
Route::post('/inv-item-unit/process-edit-inv-item-unit', [InvItemUnitController::class, 'processEditInvItemUnit'])->name('process-edit-inv-item-unit');
Route::get('/inv-item-unit/delete-inv-item-unit/{product_type_id}', [InvItemUnitController::class, 'deleteInvItemUnit'])->name('delete-inv-item-unit');
Route::post('/inv-item-unit/filter', [InvItemUnitController::class, 'filterInvItemUnit'])->name('filter-inv-item-unit');
Route::post('/inv-item-unit/type', [InvItemUnitController::class, 'getCoreType'])->name('inv-item-unit-type');


Route::get('/supplier', [CoreSupplierController::class, 'index'])->name('supplier');
Route::get('/supplier/add', [CoreSupplierController::class, 'addCoreSupplier'])->name('add-supplier');
Route::post('/supplier/process-add-supplier', [CoreSupplierController::class, 'processAddCoreSupplier'])->name('process-add-supplier');
Route::get('/supplier/edit/{product_type_id}', [CoreSupplierController::class, 'editCoreSupplier'])->name('edit-supplier');
Route::post('/supplier/process-edit-supplier', [CoreSupplierController::class, 'processEditCoreSupplier'])->name('process-edit-supplier');
Route::get('/supplier/delete-supplier/{product_type_id}', [CoreSupplierController::class, 'deleteCoreSupplier'])->name('delete-supplier');
Route::post('/supplier/filter', [CoreSupplierController::class, 'filterCoreSupplier'])->name('filter-supplier');
Route::post('/supplier/city', [CoreSupplierController::class, 'getCoreCity'])->name('supplier-city');


Route::get('/customer', [CoreCustomerController::class, 'index'])->name('customer');
Route::get('/customer/add', [CoreCustomerController::class, 'addCoreCustomer'])->name('add-customer');
Route::post('/customer/process-add-customer', [CoreCustomerController::class, 'processAddCoreCustomer'])->name('process-add-customer');
Route::get('/customer/edit/{product_type_id}', [CoreCustomerController::class, 'editCoreCustomer'])->name('edit-customer');
Route::post('/customer/process-edit-customer', [CoreCustomerController::class, 'processEditCoreCustomer'])->name('process-edit-customer');
Route::get('/customer/delete-customer/{product_type_id}', [CoreCustomerController::class, 'deleteCoreCustomer'])->name('delete-customer');
Route::post('/customer/filter', [CoreCustomerController::class, 'filterCoreCustomer'])->name('filter-customer');
Route::post('/customer/city', [CoreCustomerController::class, 'getCoreCity'])->name('customer-city');


Route::get('/agency', [CoreAgencyController::class, 'index'])->name('agency');
Route::get('/agency/add', [CoreAgencyController::class, 'addCoreAgency'])->name('add-agency');
Route::post('/agency/process-add-agency', [CoreAgencyController::class, 'processAddCoreAgency'])->name('process-add-agency');
Route::get('/agency/edit/{product_type_id}', [CoreAgencyController::class, 'editCoreAgency'])->name('edit-agency');
Route::post('/agency/process-edit-agency', [CoreAgencyController::class, 'processEditCoreAgency'])->name('process-edit-agency');
Route::get('/agency/delete-agency/{product_type_id}', [CoreAgencyController::class, 'deleteCoreAgency'])->name('delete-agency');
Route::post('/agency/filter', [CoreAgencyController::class, 'filterCoreAgency'])->name('filter-agency');
Route::post('/agency/city', [CoreAgencyController::class, 'getCoreCity'])->name('agency-city');


Route::get('/warehouse-location', [InvWarehouseLocationController::class, 'index'])->name('warehouse-location');
Route::get('/warehouse-location/add', [InvWarehouseLocationController::class, 'addInvWarehouseLocation'])->name('add-warehouse-location');
Route::post('/warehouse-location/process-add-warehouse-location', [InvWarehouseLocationController::class, 'processAddInvWarehouseLocation'])->name('process-add-warehouse-location');
Route::get('/warehouse-location/edit/{product_type_id}', [InvWarehouseLocationController::class, 'editInvWarehouseLocation'])->name('edit-warehouse-location');
Route::post('/warehouse-location/process-edit-warehouse-location', [InvWarehouseLocationController::class, 'processEditInvWarehouseLocation'])->name('process-edit-warehouse-location');
Route::get('/warehouse-location/delete-warehouse-location/{product_type_id}', [InvWarehouseLocationController::class, 'deleteInvWarehouseLocation'])->name('delete-warehouse-location');
Route::post('/warehouse-location/filter', [InvWarehouseLocationController::class, 'filterInvWarehouseLocation'])->name('filter-warehouse-location');
Route::post('/warehouse-location/city', [InvWarehouseLocationController::class, 'getCoreCity'])->name('warehouse-location-city');


Route::get('/warehouse', [InvWarehouseController::class, 'index'])->name('warehouse');
Route::get('/warehouse/add', [InvWarehouseController::class, 'addInvWarehouse'])->name('add-warehouse');
Route::post('/warehouse/process-add-warehouse', [InvWarehouseController::class, 'processAddInvWarehouse'])->name('process-add-warehouse');
Route::get('/warehouse/edit/{product_type_id}', [InvWarehouseController::class, 'editInvWarehouse'])->name('edit-warehouse');
Route::post('/warehouse/process-edit-warehouse', [InvWarehouseController::class, 'processEditInvWarehouse'])->name('process-edit-warehouse');
Route::get('/warehouse/delete-warehouse/{product_type_id}', [InvWarehouseController::class, 'deleteInvWarehouse'])->name('delete-warehouse');
Route::post('/warehouse/filter', [InvWarehouseController::class, 'filterInvWarehouse'])->name('filter-warehouse');
Route::post('/warehouse/city', [InvWarehouseController::class, 'getInvCity'])->name('warehouse-city');
Route::post('/warehouse/add-location', [InvWarehouseController::class, 'addInvWarehouseLocation'])->name('warehouse-add-location');


Route::get('/warehouse-transfer', [InvWarehouseTransferController::class, 'index'])->name('warehouse-transfer');
Route::get('/warehouse-transfer/add', [InvWarehouseTransferController::class, 'addInvWarehouseTransfer'])->name('add-warehouse-transfer');
Route::post('/warehouse-transfer/process-add-warehouse-transfer', [InvWarehouseTransferController::class, 'processAddInvWarehouseTransfer'])->name('process-add-warehouse-transfer');
Route::get('/warehouse-transfer/detail/{product_type_id}', [InvWarehouseTransferController::class, 'detailInvWarehouseTransfer'])->name('edit-warehouse-transfer');
Route::post('/warehouse-transfer/process-edit-warehouse-transfer', [InvWarehouseTransferController::class, 'processEditInvWarehouseTransfer'])->name('process-edit-warehouse-transfer');
Route::get('/warehouse-transfer/void/{product_type_id}', [InvWarehouseTransferController::class, 'voidInvWarehouseTransfer'])->name('void-warehouse-transfer');
Route::post('/warehouse-transfer/process-void', [InvWarehouseTransferController::class, 'processVoidInvWarehouseTransfer'])->name('process-void-warehouse-transfer');
Route::post('/warehouse-transfer/filter', [InvWarehouseTransferController::class, 'filterInvWarehouseTransfer'])->name('filter-warehouse-transfer');
Route::get('/warehouse-transfer/filter-reset', [InvWarehouseTransferController::class, 'resetFilterInvWarehouseTransfer'])->name('filter-reset-warehouse-transfer');
Route::post('/warehouse-transfer/city', [InvWarehouseTransferController::class, 'getInvCity'])->name('warehouse-transfer-city');
Route::post('/warehouse-transfer/type', [InvWarehouseTransferController::class, 'getCoreType'])->name('warehouse-transfer-change-type');
Route::post('/warehouse-transfer/item', [InvWarehouseTransferController::class, 'getCoreItem'])->name('warehouse-transfer-item');
Route::post('/warehouse-transfer/item-batch-number', [InvWarehouseTransferController::class, 'getItemBatchNumber'])->name('warehouse-transfer-batch-number');
Route::post('/warehouse-transfer/item-batch-number-detail', [InvWarehouseTransferController::class, 'getItemBatchNumberDetail'])->name('warehouse-transfer-batch-number-detail');
Route::post('/warehouse-transfer/add-array', [InvWarehouseTransferController::class, 'processAddArrayWarehouseTransferItem'])->name('warehouse_transfer-add-array');
Route::get('/warehouse-transfer/delete-array/{record_id}', [InvWarehouseTransferController::class, 'deleteArrayWarehouseTransferItem'])->name('warehouse-transfer-delete-array');
Route::get('/warehouse-transfer/search-purchase-invoice', [InvWarehouseTransferController::class, 'search'])->name('warehouse-transfer-search-purchase-invoice');
Route::post('/warehouse-transfer/elements-add', [InvWarehouseTransferController::class, 'elements_add'])->name('elements-add-warehouse-transfer');
Route::post('/warehouse-transfer/add-transfer-type', [InvWarehouseTransferController::class, 'addWarehouseTransferType'])->name('add-transfer-type-warehouse-transfer');


Route::get('/purchase-order', [PurchaseOrderController::class, 'index'])->name('purchase-order');
Route::get('/purchase-order/detail/{purchase_order_id}', [PurchaseOrderController::class, 'detailPurchaseOrder'])->name('detail-purchase-order');
Route::get('/purchase-order/edit/{purchase_order_id}', [PurchaseOrderController::class, 'editPurchaseOrder'])->name('edit-purchase-order');
Route::get('/purchase-order/add', [PurchaseOrderController::class, 'addPurchaseOrder'])->name('add-purchase-order');
Route::post('/purchase-order/process-add-purchase-order', [PurchaseOrderController::class, 'processAddPurchaseOrder'])->name('process-add-purchase-order');
Route::post('/purchase-order/add-array', [PurchaseOrderController::class, 'processAddArrayPurchaseOrderItem'])->name('purchase-order-add-array');
Route::get('/purchase-order/delete-array/{record_id}', [PurchaseOrderController::class, 'deleteArrayPurchaseOrderItem'])->name('purchase-order-delete-array');
Route::post('/purchase-order/elements-add', [PurchaseOrderController::class, 'elements_add'])->name('elements-add-purchase-order');
Route::post('/purchase-order/filter', [PurchaseOrderController::class, 'filterPurchaseOrder'])->name('filter-purchase-order');
Route::get('/purchase-order/filter-reset', [PurchaseOrderController::class, 'resetFilterPurchaseOrder'])->name('filter-reset-purchase-order');
Route::get('/purchase-order/delete/{purchase_order_id}', [PurchaseOrderController::class, 'deletePurchaseOrder'])->name('delete-purchase-order');
Route::post('/purchase-order/type', [PurchaseOrderController::class, 'getInvItemType'])->name('purchase-order-type');
Route::post('/purchase-order/city', [PurchaseOrderController::class, 'getCoreCity'])->name('purchase-order-city');
Route::post('/purchase-order/add-supplier', [PurchaseOrderController::class, 'addCoreSupplier'])->name('purchase-order-add-supplier');
Route::post('/purchase-order/add-warehouse', [PurchaseOrderController::class, 'addInvWarehouse'])->name('purchase-order-add-warehouse');
Route::get('/purchase-order/cetak/{purchase_order_id}', [PurchaseOrderController::class, 'cetakPurchaseOrder'])->name('cetak-purchase-order');


Route::get('/purchase-order-approval', [PurchaseOrderApprovalController::class, 'index'])->name('purchase-order-approval');
Route::get('/purchase-order-approval/approve/{purchase_order_id}', [PurchaseOrderApprovalController::class, 'approvePurchaseorder'])->name('approve-purchase-order-approval');
Route::post('/purchase-order-approval/process-approve', [PurchaseOrderApprovalController::class, 'processApprovePurchaseOrder'])->name('process-approve-purchase-order');
Route::post('/purchase-order-approval/process-disapprove', [PurchaseOrderApprovalController::class, 'processDisapprovePurchaseOrder'])->name('process-disapprove-purchase-order');


Route::get('/purchase-order-return', [PurchaseOrderReturnController::class, 'index'])->name('purchase-order-return');
Route::get('/purchase-order-return/search-purchase-order', [PurchaseOrderReturnController::class, 'searchPurchaseOrder'])->name('search-purchase-order-return');
Route::get('/purchase-order-return/search-purchase-invoice', [PurchaseOrderReturnController::class, 'searchPurchaseInvoice'])->name('search-purchase-invoice');
Route::get('/purchase-order-return/add/{purchase_order_id}', [PurchaseOrderReturnController::class, 'addPurchaseOrderReturn'])->name('add-purchase-order-return');
Route::get('/purchase-order-return/detail/{purchase_order_return_id}', [PurchaseOrderReturnController::class, 'detailPurchaseOrderReturn'])->name('detail-purchase-order-return');
Route::post('/purchase-order-return/process-add-purchase-order-return', [PurchaseOrderReturnController::class, 'processAddPurchaseOrderReturn'])->name('process-add-purchase-order-return');
Route::post('/purchase-order-return/filter', [PurchaseOrderReturnController::class, 'filterPurchaseOrderReturn'])->name('filter-purchase-order-return');
Route::get('/purchase-order-return/filter-reset', [PurchaseOrderReturnController::class, 'resetFilterPurchaseOrderReturn'])->name('filter-reset-purchase-order-return');
Route::get('/purchase-order-return/delete-purchase-order-return/{purchase_order_return_id}', [PurchaseOrderReturnController::class, 'voidPurchaseOrderReturn'])->name('delete-purchase-order-return');
Route::get('/purchase-order-return/process-delete/{purchase_order_return_id}', [PurchaseOrderReturnController::class, 'processVoidPurchaseOrderReturn'])->name('process-delete-purchase-order-return');
Route::post('/purchase-order-return/add-new-purchase-order-item/{purchase_order_id}', [PurchaseOrderReturnController::class, 'addNewPurchaseOrderItem'])->name('add-new-purchase-order-item2');
Route::get('/purchase-order-return/delete-new_purchase_order_item/{purchase_order_id}', [PurchaseOrderReturnController::class, 'deleteNewPurchaseOrderItem'])->name('delete-new-purchase-order-item2');
Route::get('/purchase-order-return/cetak/{purchase_order_return_id}', [PurchaseOrderReturnController::class, 'cetakPurchaseOrderReturn'])->name('cetak-purchase-order-return');
Route::get('/purchase-order-return/nota/{purchase_order_return_id}', [PurchaseOrderReturnController::class, 'notaPurchaseOrderReturn'])->name('nota-purchase-order-return');
Route::get('/purchase-order-return-report', [PurchaseOrderReturnController::class, 'reportPurchaseOrderReturn'])->name('report-purchase-order-return');

Route::get('purchase-order-return-report',[PurchaseOrderReturnReportController::class, 'index'])->name('purchase-order-return-report');
Route::post('purchase-order-return-report/filter',[PurchaseOrderReturnReportController::class, 'filterPurchaseOrderReturnReport'])->name('filter-purchase-order-return-report');
Route::get('purchase-order-return-report/reset-filter',[PurchaseOrderReturnReportController::class, 'resetFilterPurchaseOrderReturnReport'])->name('reset-filter-purchase-order-return-report');
Route::get('purchase-order-return-report/print',[PurchaseOrderReturnReportController::class, 'printPurchaseOrderReturnReport'])->name('print-purchase-order-return-report');
Route::get('purchase-order-return-report/export',[PurchaseOrderReturnReportController::class, 'export'])->name('export-purchase-order-return-report');


Route::get('/sales-order', [SalesOrderController::class, 'index'])->name('sales-order');
Route::get('/sales-order/detail/{sales_order_id}', [SalesOrderController::class, 'detailSalesOrder'])->name('detail-sales-order');
Route::get('/sales-order/kwitansi/{sales_order_id}', [SalesOrderController::class, 'kwitansiSalesOrder'])->name('kwitansi-sales-order');
Route::get('/sales-order/edit/{sales_order_id}', [SalesOrderController::class, 'editSalesOrder'])->name('edit-sales-order');
Route::get('/sales-order/add', [SalesOrderController::class, 'addSalesOrder'])->name('add-sales-order');
Route::post('/sales-order/process-add-sales-order', [SalesOrderController::class, 'processAddSalesOrder'])->name('process-add-sales-order');
Route::post('/sales-order/add-array', [SalesOrderController::class, 'processAddArraySalesOrderItem'])->name('sales-order-add-array');
Route::get('/sales-order/delete-array/{record_id}', [SalesOrderController::class, 'deleteArraySalesOrderItem'])->name('sales-order-delete-array');
Route::post('/sales-order/elements-add', [SalesOrderController::class, 'elements_add'])->name('elements-add-sales-order');
Route::get('/sales-order/delete/{sales_order_id}', [SalesOrderController::class, 'deleteSalesOrder'])->name('delete-sales-order');
Route::post('/sales-order/filter', [SalesOrderController::class, 'filterSalesOrder'])->name('filter-sales-order');
Route::get('/sales-order/filter-reset', [SalesOrderController::class, 'resetFilterSalesOrder'])->name('filter-reset-sales-order');
Route::post('/sales-order/add-customer', [SalesOrderController::class, 'addCoreCustomer'])->name('add-customer-sales-order');
Route::post('/sales-order/available-stock', [SalesOrderController::class, 'getAvailableStock'])->name('available-stock-sales-order');
Route::post('/sales-order/select-data-stock', [SalesOrderController::class, 'getSelectDataStock'])->name('select-data-stock-sales-order');
Route::post('/sales-order/select-data-unit', [SalesOrderController::class, 'getSelectDataUnit'])->name('select-data-unit');
Route::post('/sales-order/type', [SalesOrderController::class, 'getInvItemType'])->name('sales-order-type');
Route::post('/sales-order/stock', [SalesOrderController::class, 'getInvItemTypeId'])->name('select-id-stock');

    
Route::get('/sales-order-approval', [SalesOrderApprovalController::class, 'index'])->name('sales-order-approval');
Route::get('/sales-order-approval/approve/{sales_order_id}', [SalesOrderApprovalController::class, 'approveSalesorder'])->name('approve-sales-order-approval');
Route::post('/sales-order-approval/process-approve', [SalesOrderApprovalController::class, 'processApproveSalesOrder'])->name('process-approve-sales-order');
Route::post('/sales-order-approval/process-disapprove', [SalesOrderApprovalController::class, 'processDisapproveSalesOrder'])->name('process-disapprove-sales-order');


Route::get('/sales-order-return', [SalesOrderReturnController::class, 'index'])->name('sales-order-return');
Route::post('/sales-order-return/filter', [SalesOrderReturnController::class, 'filterSalesOrderReturn'])->name('filter-sales-order-return');
Route::get('/sales-order-return/filter-reset', [SalesOrderReturnController::class, 'resetFilterSalesOrderReturn'])->name('filter-reset-sales-order-return');
// Route::get('/sales-order-return/search-sales-delivery-note', [SalesOrderReturnController::class, 'searchSalesDeliveryNote'])->name('search-sales-delivery-note');
Route::get('/sales-order-return/search-sales-invoice', [SalesOrderReturnController::class, 'searchSalesInvoice'])->name('search-sales-invoice');
Route::get('/sales-order-return/add/{sales_invoice_id}', [SalesOrderReturnController::class, 'addSalesOrderReturn'])->name('add-sales-order-return');
Route::post('/sales-order-return/process-add-sales-order-return', [SalesOrderReturnController::class, 'processAddSalesOrderReturn'])->name('process-add-sales-order-return');
Route::get('/sales-order-return/detail/{sales_order_return_id}', [SalesOrderReturnController::class, 'detailSalesOrderReturn'])->name('detail-sales-order-return');
Route::get('/sales-order-return/export/', [SalesOrderReturnController::class, 'export'])->name('export-sales-order-return');
Route::get('/sales-order-return/edit/{sales_order_return_id}', [SalesOrderReturnController::class, 'editSalesOrderReturn'])->name('edit-sales-order-return');
Route::post('/sales-order-return/process-edit-sales-order-return', [SalesOrderReturnController::class, 'processEditSalesOrderReturn'])->name('process-edit-sales-order-return');


Route::get('/goods-received-note', [InvGoodsReceivedNoteController::class, 'index'])->name('goods-received-note');
Route::get('/goods-received-note/search-purchase-order', [InvGoodsReceivedNoteController::class, 'searchPurchaseOrder'])->name('search-po-goods-received-note');
Route::get('/goods-received-note/add/{purchase_order_id}', [InvGoodsReceivedNoteController::class, 'addInvGoodsReceivedNote'])->name('add-goods-received-note');
Route::get('/goods-received-note/detail/{goods_received_note_id}', [InvGoodsReceivedNoteController::class, 'detailInvGoodsReceivedNote'])->name('detail-goods-received-note');
Route::post('/goods-received-note/process-add-goods-received-note', [InvGoodsReceivedNoteController::class, 'processAddInvGoodsReceivedNote'])->name('process-add-goods-received-note');
Route::get('/goods-received-note/delete-goods-received-note/{goods_received_note_id}', [InvGoodsReceivedNoteController::class, 'voidInvGoodsReceivedNote'])->name('delete-goods-received-note');
Route::get('/goods-received-note/process-delete/{goods_received_note_id}', [InvGoodsReceivedNoteController::class, 'processVoidInvGoodsReceivedNote'])->name('process-delete-goods-received-note');
Route::post('/goods-received-note/filter', [InvGoodsReceivedNoteController::class, 'filterInvGoodsReceivedNote'])->name('filter-goods-received-note');
Route::get('/goods-received-note/filter-reset', [InvGoodsReceivedNoteController::class, 'resetFilterInvGoodsReceivedNote'])->name('filter-reset-goods-received-note');
Route::post('/goods-received-note/add-new-purchase-order-item/{purchase_order_id}', [InvGoodsReceivedNoteController::class, 'addNewPurchaseOrderItem'])->name('add-new-purchase-order-item');
Route::get('/goods-received-note/delete-new_purchase_order_item/{purchase_order_id}', [InvGoodsReceivedNoteController::class, 'deleteNewPurchaseOrderItem'])->name('delete-new-purchase-order-item');


Route::get('/grading', [GradingController::class, 'index'])->name('grading');
Route::get('/grading/search-item-stock', [GradingController::class, 'search'])->name('grading-search-item-stock');
Route::get('/grading/add/{item_stock_id}', [GradingController::class, 'addGrading'])->name('add-grading');
Route::post('/grading/add-array', [GradingController::class, 'addArrayInvItemStock'])->name('add-grading-array');
Route::get('/grading/delete-array/{record_id}/{item_stock_id}', [GradingController::class, 'deleteArrayInvItemStock'])->name('delete-grading-array');
Route::post('/grading/add-package-array', [GradingController::class, 'addArrayInvItemStockPackage'])->name('add-grading-package-array');
Route::get('/grading/delete-package-array/{record_id}/{item_stock_id}', [GradingController::class, 'deleteArrayInvItemStockPackage'])->name('delete-grading-package-array');
Route::get('/grading/reset-array/{item_stock_id}', [GradingController::class, 'resetArrayInvItemStock'])->name('reset-grading-array');
Route::post('/grading/process-add-grading', [GradingController::class, 'processAddGrading'])->name('process-add-grading');
Route::get('/grading/edit/{item_stock_id}', [GradingController::class, 'editGrading'])->name('edit-grading');
Route::post('/grading/process-edit-grading', [GradingController::class, 'processEditGrading'])->name('process-edit-grading');
Route::post('/grading/add-package', [GradingController::class, 'addCorePackage'])->name('add-package-grading');
Route::post('/grading/add-item', [GradingController::class, 'addInvItem'])->name('add-item-grading');
Route::post('/grading/add-item-unit', [GradingController::class, 'addInvItemUnit'])->name('add-item-unit-grading');
Route::post('/grading/package-detail', [GradingController::class, 'getPackageDetail'])->name('package-detail-grading');
Route::post('/grading/elements-add/', [GradingController::class, 'elements_add'])->name('elements-add-grading');


Route::get('/item-stock', [InvItemStockController::class, 'index'])->name('item-stock');
Route::post('/item-stock/filter', [InvItemStockController::class, 'filterInvItemStock'])->name('filter-item-stock');
Route::post('/item-stock/type', [InvItemStockController::class, 'getInvItemType'])->name('item-stock-type');
Route::post('/item-stock/grade', [InvItemStockController::class, 'getGrade'])->name('item-stock-grade');
Route::get('/item-stock/export', [InvItemStockController::class, 'export'])->name('item-stock-export');


Route::get('/expedition', [CoreExpeditionController::class, 'index'])->name('expedition');
Route::get('/expedition/add', [CoreExpeditionController::class, 'addCoreExpedition'])->name('add-expedition');
Route::post('/expedition/process-add-expedition', [CoreExpeditionController::class, 'processAddCoreExpedition'])->name('process-add-expedition');
Route::get('/expedition/edit/{grade_id}', [CoreExpeditionController::class, 'editCoreExpedition'])->name('edit-expedition');
Route::post('/expedition/process-edit-expedition', [CoreExpeditionController::class, 'processEditCoreExpedition'])->name('process-edit-expedition');
Route::get('/expedition/delete-expedition/{grade_id}', [CoreExpeditionController::class, 'deleteCoreExpedition'])->name('delete-expedition');

Route::get('/return-pdp', [ReturnPDP_Controller::class, 'index'])->name('return-pdp');
Route::post('/return-pdp/filter', [ReturnPDP_Controller::class, 'filterReturnPDP'])->name('filter-return-pdp');
Route::get('/return-pdp/filter-reset', [ReturnPDP_Controller::class, 'resetFilterReturnPDP'])->name('filter-reset-return-pdp');
Route::get('/return-pdp/search-sales-delivery-note', [ReturnPDP_Controller::class, 'searchSalesDeliveryNote'])->name('search-sales-delivery-note2');
Route::get('/return-pdp/add/{sales_delivery_note_id}', [ReturnPDP_Controller::class, 'addReturnPDP'])->name('add-return-pdp');
Route::post('/return-pdp/process-add-return-pdp', [ReturnPDP_Controller::class, 'processAddReturnPDP'])->name('process-add-return-pdp');
Route::get('/return-pdp/detail/{return_pdp_id}', [ReturnPDP_Controller::class, 'detailReturnPDP'])->name('detail-return-pdp');

Route::get('/buyers-acknowledgment', [BuyersAcknowledgmentController::class, 'index'])->name('buyers-acknowledgment');
Route::post('/buyers-acknowledgment/filter', [BuyersAcknowledgmentController::class, 'filterBuyersAcknowledgment'])->name('filter-buyers-acknowledgment');
Route::get('/buyers-acknowledgment/filter-reset', [BuyersAcknowledgmentController::class, 'resetFilterBuyersAcknowledgment'])->name('filter-reset-buyers-acknowledgment');
Route::get('/buyers-acknowledgment/search-sales-delivery-note', [BuyersAcknowledgmentController::class, 'searchSalesDeliveryNote'])->name('search-sales-delivery-note4');
Route::get('/buyers-acknowledgment/add/{sales_delivery_note_id}', [BuyersAcknowledgmentController::class, 'addBuyersAcknowledgment'])->name('add-buyers-acknowledgment');
Route::post('/buyers-acknowledgment/process-add-buyers-acknowledgment', [BuyersAcknowledgmentController::class, 'processAddBuyersAcknowledgment'])->name('process-add-buyers-acknowledgment');
Route::get('/buyers-acknowledgment/detail/{buyers_acknowledgment_id}', [BuyersAcknowledgmentController::class, 'detailBuyersAcknowledgment'])->name('detail-buyers-acknowledgment');

Route::get('/return-pdp-lost-on-expedition', [ReturnPDP_LostOnExpedition_Controller::class, 'index'])->name('return-pdp-lost-on-expedition');
Route::post('/return-pdp-lost-on-expedition/filter', [ReturnPDP_LostOnExpedition_Controller::class, 'filterReturnPDP_LostOnExpedition'])->name('filter-return-pdp-lost-on-expedition');
Route::get('/return-pdp-lost-on-expedition/filter-reset', [ReturnPDP_LostOnExpedition_Controller::class, 'resetFilterReturnPDP_LostOnExpedition'])->name('filter-reset-return-pdp-lost-on-expedition');
Route::get('/return-pdp-lost-on-expedition/search-sales-delivery-note', [ReturnPDP_LostOnExpedition_Controller::class, 'searchSalesDeliveryNote'])->name('search-sales-delivery-note3');
Route::get('/return-pdp-lost-on-expedition/add/{sales_delivery_note_id}', [ReturnPDP_LostOnExpedition_Controller::class, 'addReturnPDP_LostOnExpedition'])->name('add-return-pdp-lost-on-expedition');
Route::post('/return-pdp-lost-on-expedition/process-add-return-pdp-lost-on-expedition', [ReturnPDP_LostOnExpedition_Controller::class, 'processAddReturnPDP_LostOnExpedition'])->name('process-add-return-pdp-lost-on-expedition');
Route::get('/return-pdp-lost-on-expedition/detail/{return_pdp_lost_on_expedition_id}', [ReturnPDP_LostOnExpedition_Controller::class, 'detailReturnPDP_LostOnExpedition'])->name('detail-return-pdp-lost-on-expedition');

Route::get('/sales-delivery-order', [SalesDeliveryOrderController::class, 'index'])->name('sales-delivery-order');
Route::get('/sales-delivery-order/search-sales-order', [SalesDeliveryOrderController::class, 'search'])->name('sales-delivery-order-search-sales-order');
Route::post('/sales-delivery-order/add-elements', [SalesDeliveryOrderController::class, 'elements_add'])->name('elements-add-sales-delivery-order');
Route::get('/sales-delivery-order/add/{sales_order_id}', [SalesDeliveryOrderController::class, 'addSalesDeliveryOrder'])->name('add-sales-delivery-order');
Route::post('/sales-delivery-order/process-add-sales-delivery-order', [SalesDeliveryOrderController::class, 'processAddSalesDeliveryOrder'])->name('process-add-sales-delivery-order');
Route::get('/sales-delivery-order/edit/{sales_delivery_order_id}', [SalesDeliveryOrderController::class, 'editSalesDeliveryOrder'])->name('edit-sales-delivery-order');
Route::get('/sales-delivery-order/detail/{sales_delivery_order_id}', [SalesDeliveryOrderController::class, 'detailSalesDeliveryOrder'])->name('detail-sales-delivery-order');
Route::get('/sales-delivery-order/detail/detail-item-stock/{sales_delivery_order_id}/{sales_delivery_order_item}', [SalesDeliveryOrderController::class, 'detailStockSalesDeliveryOrder'])->name('detail-stock-sales-delivery-order');
Route::get('/sales-delivery-order/void/{sales_delivery_order_id}', [SalesDeliveryOrderController::class, 'voidSalesDeliveryOrder'])->name('void-sales-delivery-order');
Route::post('/sales-delivery-order/process-edit', [SalesDeliveryOrderController::class, 'processEditSalesDeliveryOrder'])->name('process-edit-sales-delivery-order');
Route::post('/sales-delivery-order/process-void', [SalesDeliveryOrderController::class, 'processVoidSalesDeliveryOrder'])->name('process-void-sales-delivery-order');
Route::post('/sales-delivery-order/add-checkbox', [SalesDeliveryOrderController::class, 'addSalesOrderItemCheckbox'])->name('add-checkbox-sales-delivery-order');
Route::post('/sales-delivery-order/filter', [SalesDeliveryOrderController::class, 'filterSalesDeliveryOrder'])->name('filter-sales-delivery-order');
Route::post('/sales-delivery-order/select-data-stock', [SalesDeliveryOrderController::class, 'getSelectDataStock'])->name('select-data-stock');
// Route::post('/sales-delivery-order/add-array-item-stock', [SalesDeliveryOrderController::class, 'addArrayInvItemStock'])->name('add-array-item-stock');
Route::post('/sales-delivery-order/add-item-stock', [SalesDeliveryOrderController::class, 'addItemStockToSalesOrder'])->name('add-item-stock-to-sdo');
// Route::get('/sales-delivery-order/detail-array-item-stock/{sales_order_id}/{sales_order_item_id}', [SalesDeliveryOrderController::class, 'detailArraySDOtemStock'])->name('detail-array-item-stock');
Route::get('/sales-delivery-order/detail-item-stock/{sales_order_id}/{sales_order_item_id}', [SalesDeliveryOrderController::class, 'detailItemStockToSalesOrder'])->name('detail-item-stock-sdo');
Route::get('/sales-delivery-order/detail-item-stock/delete-item-stock/{sales_order_id}/{sales_order_item_id}/{sdo_item_stock_id}', [SalesDeliveryOrderController::class, 'deleteItemStockToSalesOrder'])->name('delete-item-stock-sdo');
Route::post('/sales-delivery-order/delete-item-stock-temporary-sdo', [SalesDeliveryOrderController::class, 'deleteItemStockSalesDeliveryOrderTemp'])->name('delete-item-stock-sdo-temp');
Route::get('/sales-delivery-order/edit/detail-item-stock/{sales_delivery_order_id}/{sales_delivery_order_item}', [SalesDeliveryOrderController::class, 'editDetailStockSalesDeliveryOrder'])->name('detail-stock-sales-delivery-order2');
Route::post('/sales-delivery-order/edit/detail-item-stock/change-item-stock/{sales_delivery_order_id}/{sales_delivery_order_item}', [SalesDeliveryOrderController::class, 'changeItemStockSalesDeliveryOrder'])->name('change-item-stock-sales-delivery-order');
Route::get('/sales-delivery-order/edit/detail-item-stock/delete-item-stock/{sales_delivery_order_id}/{sales_delivery_order_item}/{sdo_item_stock_id}', [SalesDeliveryOrderController::class, 'deleteItemStockSalesDeliveryOrder'])->name('delete-item-stock-sales-delivery-order');
Route::get('/sales-delivery-order/void/detail-item-stock/{sales_delivery_order_id}/{sales_delivery_order_item}', [SalesDeliveryOrderController::class, 'voidDetailStockSalesDeliveryOrder'])->name('void-detail-stock-sales-delivery-order');
Route::post('/sales-delivery-order/select-data-unit', [SalesDeliveryOrderController::class, 'getSelectDataUnit'])->name('select-data-unit2');


Route::get('/sales-delivery-note', [SalesDeliveryNoteController::class, 'index'])->name('sales-delivery-note');
Route::get('/sales-delivery-note/search-sales-delivery-order', [SalesDeliveryNoteController::class, 'search'])->name('sales-delivery-note-search-sales-delivery-order');
Route::get('/sales-delivery-note/add/{sales_delivery_order_id}', [SalesDeliveryNoteController::class, 'addSalesDeliveryNote'])->name('add-sales-delivery-note');
Route::post('/sales-delivery-note/add-elements', [SalesDeliveryNoteController::class, 'elements_add'])->name('elements-add-sales-delivery-order-note');
Route::post('/sales-delivery-note/process-add-sales-delivery-note', [SalesDeliveryNoteController::class, 'processAddSalesDeliveryNote'])->name('process-add-sales-delivery-note');
Route::get('/sales-delivery-note/edit/{sales_delivery_note_id}', [SalesDeliveryNoteController::class, 'editSalesDeliveryNote'])->name('edit-sales-delivery-note');
Route::get('/sales-delivery-note/detail/{sales_delivery_note_id}', [SalesDeliveryNoteController::class, 'detailSalesDeliveryNote'])->name('detail-sales-delivery-note');
Route::get('/sales-delivery-note/void/{sales_delivery_note_id}', [SalesDeliveryNoteController::class, 'voidSalesDeliveryNote'])->name('void-sales-delivery-note');
Route::post('/sales-delivery-note/process-edit-sales-delivery-note', [SalesDeliveryNoteController::class, 'processEditSalesDeliveryNote'])->name('process-edit-sales-delivery-note');
Route::post('/sales-delivery-note/process-void', [SalesDeliveryNoteController::class, 'processVoidSalesDeliveryNote'])->name('process-void-sales-delivery-note');
Route::post('/sales-delivery-note/add-checkbox', [SalesDeliveryNoteController::class, 'addSalesNoteItemCheckbox'])->name('add-checkbox-sales-delivery-note');
Route::post('/sales-delivery-note/filter', [SalesDeliveryNoteController::class, 'filterSalesDeliveryNote'])->name('filter-sales-delivery-note');
Route::get('/sales-delivery-note/printing/{sales_delivery_note_id}', [SalesDeliveryNoteController::class, 'processPrintingSalesDeliveryNote'])->name('printing-sales-delivery-note');
Route::post('/sales-delivery-note/add-expedition', [SalesDeliveryNoteController::class, 'addCoreExpedition'])->name('add-expedition-sales-delivery-note');
Route::get('/sales-delivery-note/add/detail-item-stock/{sales_delivery_order_id}/{sales_delivery_order_item}', [SalesDeliveryNoteController::class, 'detailStockSalesDeliveryOrderToSDN'])->name('detail-stock-sales-delivery-order-to-sdn');
Route::get('/sales-delivery-note/export', [SalesDeliveryNoteController::class, 'export'])->name('sales-delivery-note-export');


Route::get('/purchase-invoice', [PurchaseInvoiceController::class, 'index'])->name('purchase-invoice');
Route::post('/purchase-invoice/filter', [PurchaseInvoiceController::class, 'filterPurchaseInvoice'])->name('filter-purchase-invoice');
Route::get('/purchase-invoice/filter-reset', [PurchaseInvoiceController::class, 'resetFilterPurchaseInvoice'])->name('filter-reset-purchase-invoice');
Route::get('/purchase-invoice/search-purchase-order', [PurchaseInvoiceController::class, 'search'])->name('purchase-invoice-search-purchase-order');
Route::get('/purchase-invoice/search-goods-received-note', [PurchaseInvoiceController::class, 'searchGoodsReceivedNote'])->name('purchase-invoice-search-goods-received-note');
// Route::get('/purchase-invoice/add/{purchase_order_id}', [PurchaseInvoiceController::class, 'addPurchaseInvoicePurchaseOrder'])->name('add-purchase-invoice');
Route::get('/purchase-invoice/add/{goods_received_note_id}', [PurchaseInvoiceController::class, 'addPurchaseInvoice'])->name('add-purchase-invoice');
Route::get('/purchase-invoice/edit/{purchase_invoice_id}', [PurchaseInvoiceController::class, 'editPurchaseInvoice'])->name('edit-purchase-invoice');
Route::get('/purchase-invoice/detail/{purchase_invoice_id}', [PurchaseInvoiceController::class, 'detailPurchaseInvoice'])->name('detail-purchase-invoice');
Route::get('/purchase-invoice/void/{purchase_invoice_id}', [PurchaseInvoiceController::class, 'voidPurchaseInvoice'])->name('void-purchase-invoice');
Route::post('/purchase-invoice/process-add-purchase-invoice', [PurchaseInvoiceController::class, 'processAddPurchaseInvoice'])->name('process-add-purchase-invoice');
Route::post('/purchase-invoice/process-edit-purchase-invoice', [PurchaseInvoiceController::class, 'processEditPurchaseInvoice'])->name('process-edit-purchase-invoice');
Route::post('/purchase-invoice/process-void-purchase-invoice', [PurchaseInvoiceController::class, 'processVoidPurchaseInvoice'])->name('process-void-purchase-invoice');


Route::get('/sales-invoice', [SalesInvoiceController::class, 'index'])->name('sales-invoice');
Route::post('/sales-invoice/filter', [SalesInvoiceController::class, 'filterSalesInvoice'])->name('filter-sales-invoice');
Route::get('/sales-invoice/filter-reset', [SalesInvoiceController::class, 'resetFilterSalesInvoice'])->name('filter-reset-sales-invoice');
Route::get('/sales-invoice/search-buyers-acknowledgment', [SalesInvoiceController::class, 'search'])->name('sales-invoice-search-buyers-acknowledgment');
Route::get('/sales-invoice/add/{sales_delivery_note_id}', [SalesInvoiceController::class, 'addSalesInvoice'])->name('add-sales-invoice');
// Route::get('/sales-invoice/add', [SalesInvoiceController::class, 'addSalesInvoice'])->name('add-sales-invoice');
Route::get('/sales-invoice/edit/{sales_invoice_id}', [SalesInvoiceController::class, 'editSalesInvoice'])->name('edit-sales-invoice');
Route::get('/sales-invoice/detail/{sales_invoice_id}', [SalesInvoiceController::class, 'detailSalesInvoice'])->name('detail-sales-invoice');
Route::get('/sales-invoice/void/{sales_invoice_id}', [SalesInvoiceController::class, 'voidSalesInvoice'])->name('void-sales-invoice');
Route::post('/sales-invoice/process-add-sales-invoice', [SalesInvoiceController::class, 'processAddSalesInvoice'])->name('process-add-sales-invoice');
Route::post('/sales-invoice/process-edit-sales-invoice', [SalesInvoiceController::class, 'processEditSalesInvoice'])->name('process-edit-sales-invoice');
Route::post('/sales-invoice/process-void-sales-invoice', [SalesInvoiceController::class, 'processVoidSalesInvoice'])->name('process-void-sales-invoice');
Route::post('/sales-invoice/change-sales-delivery-note', [SalesInvoiceController::class, 'changeSalesDeliveryNote'])->name('sales-invoice-change-delivery-note');
Route::get('/sales-invoice/printing/{sales_invoice_id}', [SalesInvoiceController::class, 'processPrintingSalesInvoice'])->name('printing-sales-invoice');
Route::get('/sales-invoice/closed/{sales_invoice_id}', [SalesInvoiceController::class, 'closedSalesInvoice'])->name('closed-sales-invoice');
Route::post('/sales-invoice/process-closed', [SalesInvoiceController::class, 'processClosedSalesInvoice'])->name('process-closed-sales-invoice');
Route::get('/sales-invoice/export', [SalesInvoiceController::class, 'export'])->name('sales-invoice-export');

Route::get('/sales-invoice-report', [SalesInvoiceController::class, 'ReportSalesInvoice'])->name('sales-invoice-report');
Route::post('/sales-invoice-report/filter', [SalesInvoiceController::class, 'filterSalesInvoiceReport'])->name('filter-sales-invoice-report');
Route::get('/sales-invoice-report/filter-reset', [SalesInvoiceController::class, 'resetFilterSalesInvoiceReport'])->name('filter-reset-sales-invoice-report');

Route::get('/warehouse-transfer-type', [InvWarehouseTransferTypeController::class, 'index'])->name('warehouse-transfer-type');
Route::get('/warehouse-transfer-type/add', [InvWarehouseTransferTypeController::class, 'addInvWarehouseTransferType'])->name('add-warehouse-transfer-type');
Route::post('/warehouse-transfer-type/process-add-warehouse-transfer-type', [InvWarehouseTransferTypeController::class, 'processAddInvWarehouseTransferType'])->name('process-add-warehouse-transfer-type');
Route::get('/warehouse-transfer-type/edit/{product_type_id}', [InvWarehouseTransferTypeController::class, 'editInvWarehouseTransferType'])->name('edit-warehouse-transfer-type');
Route::post('/warehouse-transfer-type/process-edit-warehouse-transfer-type', [InvWarehouseTransferTypeController::class, 'processEditInvWarehouseTransferType'])->name('process-edit-warehouse-transfer-type');
Route::get('/warehouse-transfer-type/delete-warehouse-transfer-type/{product_type_id}', [InvWarehouseTransferTypeController::class, 'deleteInvWarehouseTransferType'])->name('delete-warehouse-transfer-type');


Route::get('/preference-company', [PreferenceCompanyController::class, 'index'])->name('preference-company');
Route::get('/preference-company/edit/{company_id}', [PreferenceCompanyController::class, 'editPreferenceCompany'])->name('edit-preference-company');
Route::post('/preference-company/process-edit-preference-company', [PreferenceCompanyController::class, 'processEditPreferenceCompany'])->name('process-edit-preference-company');


Route::get('/ppn', [PreferenceCompanyController::class, 'index_ppn'])->name('ppn-preference-company');
Route::get('/ppn/edit/{company_id}', [PreferenceCompanyController::class, 'editppn'])->name('edit-ppn-preference-company');
Route::post('/ppn/process-edit-preference-company', [PreferenceCompanyController::class, 'processEditPpnPreferenceCompany'])->name('process-edit-ppn-preference-company');


Route::get('/warehouse-transfer-received-note', [InvWarehouseTransferReceivedNoteController::class, 'index'])->name('warehouse-transfer-received-note');
Route::get('/warehouse-transfer-received-note/search-warehouse-transfer', [InvWarehouseTransferReceivedNoteController::class, 'searchWarehouseTransfer'])->name('search-wt-warehouse-transfer-received-note');
Route::get('/warehouse-transfer-received-note/add/{purchase_order_item_id}', [InvWarehouseTransferReceivedNoteController::class, 'addInvWarehouseTransferReceivedNote'])->name('add-warehouse-transfer-received-note');
Route::get('/warehouse-transfer-received-note/detail/{purchase_order_item_id}', [InvWarehouseTransferReceivedNoteController::class, 'detailInvWarehouseTransferReceivedNote'])->name('detail-warehouse-transfer-received-note');
Route::post('/warehouse-transfer-received-note/process-add-warehouse-transfer-received-note', [InvWarehouseTransferReceivedNoteController::class, 'processAddInvWarehouseTransferReceivedNote'])->name('process-add-warehouse-transfer-received-note');
Route::get('/warehouse-transfer-received-note/delete-warehouse-transfer-received-note/{id}', [InvWarehouseTransferReceivedNoteController::class, 'voidInvWarehouseTransferReceivedNote'])->name('delete-warehouse-transfer-received-note');
Route::get('/warehouse-transfer-received-note/process-delete/{id}', [InvWarehouseTransferReceivedNoteController::class, 'processVoidInvWarehouseTransferReceivedNote'])->name('process-delete-warehouse-transfer-received-note');
Route::post('/warehouse-transfer-received-note/filter', [InvWarehouseTransferReceivedNoteController::class, 'filterInvWarehouseTransferReceivedNote'])->name('filter-warehouse-transfer-received-note');
Route::get('/warehouse-transfer-received-note/filter-reset', [InvWarehouseTransferReceivedNoteController::class, 'resetFilterInvWarehouseTransferReceivedNote'])->name('filter-reset-transfer-received-note');

Route::get('/warehouse-out-requisition', [InvWarehouseOutRequisitionController::class, 'index'])->name('warehouse-out-requisition');
Route::get('/warehouse-out-requisition/add', [InvWarehouseOutRequisitionController::class, 'addInvWarehouseOutRequisition'])->name('add-warehouse-out-requisition');
Route::post('/warehouse-out-requisition/add-array', [InvWarehouseOutRequisitionController::class, 'processAddArrayWarehouseOutRequisitionItem'])->name('warehouse-out-requisition-add-array');
Route::get('/warehouse-out-requisition/detail/{warehouse_out_id}', [InvWarehouseOutRequisitionController::class, 'detailInvWarehouseOutRequisition'])->name('detail-warehouse-out-requisition');
Route::post('/warehouse-out-requisition/process-add-warehouse-out-requisition', [InvWarehouseOutRequisitionController::class, 'processAddInvWarehouseOutRequisition'])->name('process-add-warehouse-out-requisition');
Route::get('/warehouse-out-requisition/delete-warehouse-out-requisition/{id}', [InvWarehouseOutRequisitionController::class, 'voidInvWarehouseOutRequisition'])->name('delete-warehouse-out-requisition');
Route::get('/warehouse-out-requisition/process-delete/{id}', [InvWarehouseOutRequisitionController::class, 'processVoidInvWarehouseOutRequisition'])->name('process-delete-warehouse-out-requisition');
Route::post('/warehouse-out-requisition/filter', [InvWarehouseOutRequisitionController::class, 'filterInvWarehouseOutRequisition'])->name('filter-warehouse-out-requisition');
Route::get('/warehouse-out-requisition/filter-reset', [InvWarehouseOutRequisitionController::class, 'resetFilterInvWarehouseOutRequisition'])->name('filter-reset-warehouse-out-requisition');
Route::post('/warehouse-out-requisition/elements-add/', [InvWarehouseOutRequisitionController::class, 'elements_add'])->name('elements-add-warehouse-out-requisition');
Route::post('/warehouse-out-requisition/item-stock-detail', [InvWarehouseOutRequisitionController::class, 'getItemStockDetail'])->name('warehouse-out-item-stock-detail');
Route::get('/warehouse-out-requisition/reset-array', [InvWarehouseOutRequisitionController::class, 'resetArrayInvWarehouseOutRequisition'])->name('reset-array-warehouse-out-requisition');
Route::get('/warehouse-out-requisition/delete-array/{record_id}', [InvWarehouseOutRequisitionController::class, 'deleteArrayInvWarehouseOutRequisitionItem'])->name('warehouse-out-requisition-delete-array');


Route::get('/warehouse-out-approval', [InvWarehouseOutApprovalController::class, 'index'])->name('warehouse-out-approval');
Route::get('/warehouse-out-approval/approve/{id}', [InvWarehouseOutApprovalController::class, 'approveInvWarehouseOutApproval'])->name('approve-warehouse-out-approval');
Route::get('/warehouse-out-approval/process-approve/{id}', [InvWarehouseOutApprovalController::class, 'processApproveInvWarehouseOutApproval'])->name('process-approve-warehouse-out-approval');
Route::post('/warehouse-out-approval/process-disapprove', [InvWarehouseOutApprovalController::class, 'processDisapproveInvWarehouseOutApproval'])->name('process-disapprove-warehouse-out-approval');


Route::get('/warehouse-out-type', [InvWarehouseOutTypeController::class, 'index'])->name('warehouse-out-type');
Route::get('/warehouse-out-type/add', [InvWarehouseOutTypeController::class, 'addInvWarehouseOutType'])->name('add-warehouse-out-type');
Route::post('/warehouse-out-type/process-add-warehouse-out-type', [InvWarehouseOutTypeController::class, 'processAddInvWarehouseOutType'])->name('process-add-warehouse-out-type');
Route::get('/warehouse-out-type/edit/{product_type_id}', [InvWarehouseOutTypeController::class, 'editInvWarehouseOutType'])->name('edit-warehouse-out-type');
Route::post('/warehouse-out-type/process-edit-warehouse-out-type', [InvWarehouseOutTypeController::class, 'processEditInvWarehouseOutType'])->name('process-edit-warehouse-out-type');
Route::get('/warehouse-out-type/delete-warehouse-out-type/{product_type_id}', [InvWarehouseOutTypeController::class, 'deleteInvWarehouseOutType'])->name('delete-warehouse-out-type');


Route::get('/purchase-payment', [PurchasePaymentController::class, 'index'])->name('purchase-payment');
Route::post('/purchase-payment/filter', [PurchasePaymentController::class, 'filterPurchasePayment'])->name('filter-purchase-payment');
Route::get('/purchase-payment/search', [PurchasePaymentController::class, 'searchCoreSupplier'])->name('search-core-supplier-purchase-payment');
Route::get('/purchase-payment/add/{supplier_id}', [PurchasePaymentController::class, 'addPurchasePayment'])->name('add-purchase-payment');
Route::get('/purchase-payment/detail/{supplier_id}', [PurchasePaymentController::class, 'detailPurchasePayment'])->name('detail-purchase-payment');
Route::get('/purchase-payment/delete/{supplier_id}', [PurchasePaymentController::class, 'deletePurchasePayment'])->name('delete-purchase-payment');
Route::post('/purchase-payment/process-delete', [PurchasePaymentController::class, 'processVoidPurchasePayment'])->name('process-delete-purchase-payment');
Route::post('/purchase-payment/process-add/', [PurchasePaymentController::class, 'processAddPurchasePayment'])->name('process-add-purchase-payment');
Route::post('/purchase-payment/elements-add/', [PurchasePaymentController::class, 'elements_add'])->name('elements-add-purchase-payment');
Route::post('/purchase-payment/add-bank/', [PurchasePaymentController::class, 'addCoreBank'])->name('add-bank-purchase-payment');
Route::post('/purchase-payment/add-transfer-array/', [PurchasePaymentController::class, 'processAddTransferArray'])->name('add-transfer-array-purchase-payment');
Route::get('/purchase-payment/delete-transfer-array/{record_id}/{supplier_id}', [PurchasePaymentController::class, 'deleteTransferArray'])->name('delete-transfer-array-purchase-payment');


Route::get('/sales-collection', [SalesCollectionController::class, 'index'])->name('sales-collection');
Route::post('/sales-collection/filter', [SalesCollectionController::class, 'filterSalesCollection'])->name('filter-sales-collection');
Route::get('/sales-collection/search', [SalesCollectionController::class, 'searchCoreCustomer'])->name('search-core-supplier-sales-collection');
Route::get('/sales-collection/add/{supplier_id}', [SalesCollectionController::class, 'addSalesCollection'])->name('add-sales-collection');
Route::get('/sales-collection/detail/{supplier_id}', [SalesCollectionController::class, 'detailSalesCollection'])->name('detail-sales-collection');
Route::get('/sales-collection/delete/{supplier_id}', [SalesCollectionController::class, 'deleteSalesCollection'])->name('delete-sales-collection');
Route::post('/sales-collection/process-delete', [SalesCollectionController::class, 'processVoidSalesCollection'])->name('process-delete-sales-collection');
Route::post('/sales-collection/process-add/', [SalesCollectionController::class, 'processAddSalesCollection'])->name('process-add-sales-collection');
Route::post('/sales-collection/elements-add/', [SalesCollectionController::class, 'elements_add'])->name('elements-add-sales-collection');
Route::post('/sales-collection/add-bank/', [SalesCollectionController::class, 'addCoreBank'])->name('add-bank-sales-collection');
Route::post('/sales-collection/add-transfer-array/', [SalesCollectionController::class, 'processAddTransferArray'])->name('add-transfer-array-sales-collection');
Route::get('/sales-collection/delete-transfer-array/{record_id}/{customer_id}', [SalesCollectionController::class, 'deleteTransferArray'])->name('delete-transfer-array-sales-collection');
Route::get('/sales-collection/printing/{collection_id}', [SalesCollectionController::class, 'processPrintingSalesCollection'])->name('printing-sales-collection');

Route::get('/sales-collection-piece', [SalesCollectionPieceController::class, 'index'])->name('sales-collection-piece');
Route::post('/sales-collection-piece/add-piece', [SalesCollectionPieceController::class, 'processAddSalesCollectionPiece'])->name('add-sales-collection-piece');
Route::post('/sales-collection-piece/delete-piece', [SalesCollectionPieceController::class, 'processDeleteSalesCollectionPiece'])->name('delete-sales-collection-piece');
Route::post('/sales-collection-piece/filter', [SalesCollectionPieceController::class, 'filterSalesCollectionPiece'])->name('filter-sales-collection-piece');
Route::get('/sales-collection-piece/filter-reset', [SalesCollectionPieceController::class, 'resetFilterSalesCollectionPiece'])->name('filter-reset-sales-collection-piece');
Route::get('/sales-collection-piece/search-sales-collection-piece', [SalesCollectionPieceController::class, 'search'])->name('search-sales-collection-piece');
Route::get('/sales-collection-piece/claim-sales-collection-piece/{sales_collection_piece_id}', [SalesCollectionPieceController::class, 'ClaimSalesCollectionPiece'])->name('claim-sales-collection-piece');
Route::post('/sales-collection-piece/process-claim/', [SalesCollectionPieceController::class, 'processClaimSalesCollectionPiece'])->name('process-claim');
Route::get('/sales-collection-piece/cancel-claim-sales-collection-piece/{sales_invoice_id}', [SalesCollectionPieceController::class, 'CancelClaimSalesCollectionPiece'])->name('cancel-claim-sales-collection-piece');
Route::post('/sales-collection-piece/process-cancel-claim/', [SalesCollectionPieceController::class, 'processCancelClaimSalesCollectionPiece'])->name('process-batal-claim');
Route::get('/sales-collection-piece/detail-claim-sales-collection-piece/{sales_collection_piece_id}', [SalesCollectionPieceController::class, 'detailClaimSalesCollectionPiece'])->name('detail-claim-sales-collection-piece');
Route::get('/sales-collection-piece/detail-sales-collection-piece/{sales_invoice_id}', [SalesCollectionPieceController::class, 'detailClaimSalesCollection'])->name('detail-claim-sales-collection');


Route::get('/sales-promotion', [SalesPromotionController::class, 'index'])->name('sales-promotion');
Route::post('/sales-promotion/filter', [SalesPromotionController::class, 'filterSalesPromotion'])->name('filter-sales-promotion');
Route::get('/sales-promotion/filter-reset', [SalesPromotionController::class, 'resetFilterSalesCollectionPiece'])->name('filter-reset-sales-promotion');
Route::get('/sales-promotion/export', [SalesPromotionController::class, 'export'])->name('sales-promotion-export');



Route::get('/debt-card', [AcctDebtCardController::class, 'index'])->name('debt-card');
Route::get('/debt-card/print/{sales_order_id}', [AcctDebtCardController::class, 'processPrinting'])->name('kwitansi-debt-card');
Route::post('/debt-card/filter', [AcctDebtCardController::class, 'filter'])->name('filter-debt-card');


Route::get('/item-stock-adjustment',[InvItemStockAdjustmentController::class,'index'])->name('stock-adjustment');
Route::post('/item-stock-adjustment/filter-list', [InvItemStockAdjustmentController::class, 'filterList'])->name('filter-list-stock-adjustment');
Route::get('/item-stock-adjustment/filter-reset', [InvItemStockAdjustmentController::class, 'listReset'])->name('filter-reset-stock-adjustment');
Route::get('/item-stock-adjustment/add', [InvItemStockAdjustmentController::class,'add'])->name('add-stock-adjustment');
Route::post('/item-stock-adjustment/get-item-type', [InvItemStockAdjustmentController::class, 'getInvItemType'])->name('get-item-type-stock-adjustment');
Route::post('/item-stock-adjustment/get-list-item-stock', [InvItemStockAdjustmentController::class,'getListItemStock'])->name('get-list-item-stock-adjustment');
Route::post('/item-stock-adjustment/add-elements',[InvItemStockAdjustmentController::class, 'elements_add'])->name('add-elements-stock-adjustment');
Route::post('/item-stock-adjustment/process-add', [InvItemStockAdjustmentController::class, 'processAdd'])->name('process-add-stock-adjustment');


Route::get('/warehouse-in-requisition', [InvWarehouseInRequisitionController::class, 'index'])->name('warehouse-in-requisition');
Route::get('/warehouse-in-requisition/add', [InvWarehouseInRequisitionController::class, 'addInvWarehouseInRequisition'])->name('add-warehouse-in-requisition');
Route::post('/warehouse-in-requisition/add-array', [InvWarehouseInRequisitionController::class, 'processAddArrayWarehouseInRequisitionItem'])->name('warehouse-in-requisition-add-array');
Route::get('/warehouse-in-requisition/detail/{warehouse_out_id}', [InvWarehouseInRequisitionController::class, 'detailInvWarehouseInRequisition'])->name('detail-warehouse-in-requisition');
Route::post('/warehouse-in-requisition/process-add-warehouse-in-requisition', [InvWarehouseInRequisitionController::class, 'processAddInvWarehouseInRequisition'])->name('process-add-warehouse-in-requisition');
Route::get('/warehouse-in-requisition/delete-warehouse-in-requisition/{id}', [InvWarehouseInRequisitionController::class, 'voidInvWarehouseInRequisition'])->name('delete-warehouse-in-requisition');
Route::get('/warehouse-in-requisition/process-delete/{id}', [InvWarehouseInRequisitionController::class, 'processVoidInvWarehouseInRequisition'])->name('process-delete-warehouse-in-requisition');
Route::post('/warehouse-in-requisition/filter', [InvWarehouseInRequisitionController::class, 'filterInvWarehouseInRequisition'])->name('filter-warehouse-in-requisition');
Route::get('/warehouse-in-requisition/filter-reset', [InvWarehouseInRequisitionController::class, 'resetFilterInvWarehouseInRequisition'])->name('filter-reset-warehouse-in-requisition');
Route::post('/warehouse-in-requisition/elements-add/', [InvWarehouseInRequisitionController::class, 'elements_add'])->name('elements-add-warehouse-in-requisition');
Route::post('/warehouse-in-requisition/item-stock-detail', [InvWarehouseInRequisitionController::class, 'getItemStockDetail'])->name('warehouse-in-item-stock-detail');
Route::get('/warehouse-in-requisition/reset-array', [InvWarehouseInRequisitionController::class, 'resetArrayInvWarehouseInRequisition'])->name('reset-array-warehouse-in-requisition');
Route::get('/warehouse-in-requisition/delete-array/{record_id}', [InvWarehouseInRequisitionController::class, 'deleteArrayInvWarehouseInRequisitionItem'])->name('warehouse-in-requisition-delete-array');


Route::get('/warehouse-in-approval', [InvWarehouseInApprovalController::class, 'index'])->name('warehouse-in-approval');
Route::get('/warehouse-in-approval/approve/{id}', [InvWarehouseInApprovalController::class, 'approveInvWarehouseInApproval'])->name('approve-warehouse-in-approval');
Route::get('/warehouse-in-approval/process-approve/{id}', [InvWarehouseInApprovalController::class, 'processApproveInvWarehouseInApproval'])->name('process-approve-warehouse-in-approval');
Route::post('/warehouse-in-approval/process-disapprove', [InvWarehouseInApprovalController::class, 'processDisapproveInvWarehouseInApproval'])->name('process-disapprove-warehouse-in-approval');


Route::get('/warehouse-in-type', [InvWarehouseInTypeController::class, 'index'])->name('warehouse-in-type');
Route::get('/warehouse-in-type/add', [InvWarehouseInTypeController::class, 'addInvWarehouseInType'])->name('add-warehouse-in-type');
Route::post('/warehouse-in-type/process-add-warehouse-in-type', [InvWarehouseInTypeController::class, 'processAddInvWarehouseInType'])->name('process-add-warehouse-in-type');
Route::get('/warehouse-in-type/edit/{product_type_id}', [InvWarehouseInTypeController::class, 'editInvWarehouseInType'])->name('edit-warehouse-in-type');
Route::post('/warehouse-in-type/process-edit-warehouse-in-type', [InvWarehouseInTypeController::class, 'processEditInvWarehouseInType'])->name('process-edit-warehouse-in-type');
Route::get('/warehouse-in-type/delete-warehouse-in-type/{product_type_id}', [InvWarehouseInTypeController::class, 'deleteInvWarehouseInType'])->name('delete-warehouse-in-type');


Route::get('/item-stock-card', [InvItemStockCardController::class, 'index'])->name('item-stock-card');
Route::post('/item-stock-card/filter', [InvItemStockCardController::class, 'filterInvItemStockCard'])->name('filter-item-stock-card');
Route::get('/item-stock-card/filter-reset', [InvItemStockCardController::class, 'resetFilterInvItemStockCard'])->name('filter-reset-item-stock-card');
Route::get('/item-stock-card/detail/{item_stock_id}', [InvItemStockCardController::class, 'detailInvItemStockCard'])->name('detail-item-stock-card');
Route::get('/item-stock-card/print-pdf/{item_stock_id}',[InvItemStockCardController::class, 'printStockCardReport'])->name('print-pdf-stock-card');

Route::get('/print-kwitansi',[KwitansiController::class, 'index'])->name('print-kwitansi');
Route::get('/print-kwitansi/add', [KwitansiController::class, 'searchCustomer'])->name('search-customer');
Route::get('/print-kwitansi/add/{customer_id}', [KwitansiController::class, 'addKwitansi'])->name('add-print-kwitansi');
Route::post('/print-kwitansi/save', [KwitansiController::class, 'processAddKwitansi'])->name('process-add-kwitansi');
Route::post('/print-kwitansi/filter', [KwitansiController::class, 'filterKwitansi'])->name('filter-print-kwitansi');
Route::get('/print-kwitansi/cetak-multiple/{sales_kwitansi_id}', [KwitansiController::class, 'printKwitansi'])->name('print-multiple');
Route::get('/print-kwitansi/cetak-single/{sales_kwitansi_id}', [KwitansiController::class, 'printKwitansiSingle'])->name('print-single');


Route::get('/sales-discount-collection', [SalesCollectionDiscountController::class, 'index'])->name('sales-collection-piece');
Route::post('/sales-discount-collection/add-piece', [SalesCollectionDiscountController::class, 'processAddSalesCollectionPiece'])->name('add-/sales-discount-collection');
Route::post('/sales-discount-collection/delete-piece', [SalesCollectionDiscountController::class, 'processDeleteSalesCollectionPiece'])->name('delete-/sales-discount-collection');
Route::post('/sales-discount-collection/filter', [SalesCollectionDiscountController::class, 'filterSalesCollectionPiece'])->name('filter-/sales-discount-collection');
Route::get('/sales-discount-collection/filter-reset', [SalesCollectionDiscountController::class, 'resetFilterSalesCollectionPiece'])->name('filter-reset-/sales-discount-collection');
Route::get('/sales-discount-collection/search', [SalesCollectionDiscountController::class, 'searchCoreCustomer'])->name('search-core-supplier-sales-collection');
Route::get('/sales-discount-collection/claim-/sales-discount-collection/{sales_collection_piece_id}', [SalesCollectionDiscountController::class, 'ClaimSalesCollectionPiece'])->name('claim-/sales-discount-collection');
Route::post('/sales-discount-collection/process-claim/', [SalesCollectionDiscountController::class, 'processClaimSalesCollectionPiece'])->name('process-claim');
Route::get('/sales-discount-collection/cancel-claim-/sales-discount-collection/{sales_invoice_id}', [SalesCollectionDiscountController::class, 'CancelClaimSalesCollectionPiece'])->name('cancel-claim-/sales-discount-collection');
Route::post('/sales-discount-collection/process-cancel-claim/', [SalesCollectionDiscountController::class, 'processCancelClaimSalesCollectionPiece'])->name('process-batal-claim');
Route::get('/sales-discount-collection/detail-claim-/sales-discount-collection/{sales_collection_piece_id}', [SalesCollectionDiscountController::class, 'detailClaimSalesCollectionPiece'])->name('detail-claim-/sales-discount-collection');
Route::get('/sales-discount-collection/detail-/sales-discount-collection/{sales_invoice_id}', [SalesCollectionDiscountController::class, 'detailClaimSalesCollection'])->name('detail-claim-sales-collection');



?>