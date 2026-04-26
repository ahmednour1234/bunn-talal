<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DelegateController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\MeasurementUnitController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TreasuryController;
use App\Http\Controllers\TreasuryTransactionController;
use App\Http\Controllers\FinancialTransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AccountingExportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImportExportController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\InventoryDispatchController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\ProductDepreciationController;
use App\Http\Controllers\BranchReportController;
use App\Http\Controllers\SaleOrderController;
use App\Http\Controllers\SaleQuotationController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\HrController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
});

// Authenticated Admin Routes
Route::middleware('auth:admin')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');

    // Branches
    Route::middleware('permission:branches.view')->group(function () {
        Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
    });
    Route::middleware('permission:branches.create')->group(function () {
        Route::get('/branches/create', [BranchController::class, 'create'])->name('branches.create');
    });
    Route::middleware('permission:branches.edit')->group(function () {
        Route::get('/branches/{id}/edit', [BranchController::class, 'edit'])->name('branches.edit');
    });

    // Admins
    Route::middleware('permission:admins.view')->group(function () {
        Route::get('/admins', [AdminController::class, 'index'])->name('admins.index');
    });
    Route::middleware('permission:admins.create')->group(function () {
        Route::get('/admins/create', [AdminController::class, 'create'])->name('admins.create');
    });
    Route::middleware('permission:admins.edit')->group(function () {
        Route::get('/admins/{id}/edit', [AdminController::class, 'edit'])->name('admins.edit');
    });

    // Roles
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    });
    Route::middleware('permission:roles.create')->group(function () {
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    });
    Route::middleware('permission:roles.edit')->group(function () {
        Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    });

    // Permissions
    Route::middleware('permission:permissions.view')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    });

    // Vehicles
    Route::middleware('permission:vehicles.view')->group(function () {
        Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    });
    Route::middleware('permission:vehicles.create')->group(function () {
        Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    });
    Route::middleware('permission:vehicles.edit')->group(function () {
        Route::get('/vehicles/{id}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
    });

    // Categories
    Route::middleware('permission:categories.view')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    });
    Route::middleware('permission:categories.create')->group(function () {
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    });
    Route::middleware('permission:categories.edit')->group(function () {
        Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    });

    // Areas
    Route::middleware('permission:areas.view')->group(function () {
        Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');
    });
    Route::middleware('permission:areas.create')->group(function () {
        Route::get('/areas/create', [AreaController::class, 'create'])->name('areas.create');
    });
    Route::middleware('permission:areas.edit')->group(function () {
        Route::get('/areas/{id}/edit', [AreaController::class, 'edit'])->name('areas.edit');
    });

    // Customers
    Route::middleware('permission:customers.view')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{id}', [CustomerController::class, 'show'])->name('customers.show');
    });
    Route::middleware('permission:customers.create')->group(function () {
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    });
    Route::middleware('permission:customers.edit')->group(function () {
        Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    });

    // Delegates
    Route::middleware('permission:delegates.view')->group(function () {
        Route::get('/delegates', [DelegateController::class, 'index'])->name('delegates.index');
        Route::get('/delegates/{id}', [DelegateController::class, 'show'])->name('delegates.show');
    });
    Route::middleware('permission:delegates.create')->group(function () {
        Route::get('/delegates/create', [DelegateController::class, 'create'])->name('delegates.create');
    });
    Route::middleware('permission:delegates.edit')->group(function () {
        Route::get('/delegates/{id}/edit', [DelegateController::class, 'edit'])->name('delegates.edit');
    });

    // Suppliers
    Route::middleware('permission:suppliers.view')->group(function () {
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    });
    Route::middleware('permission:suppliers.create')->group(function () {
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    });
    Route::middleware('permission:suppliers.edit')->group(function () {
        Route::get('/suppliers/{id}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    });

    // Measurement Units
    Route::middleware('permission:units.view')->group(function () {
        Route::get('/units', [MeasurementUnitController::class, 'index'])->name('units.index');
    });
    Route::middleware('permission:units.create')->group(function () {
        Route::get('/units/create', [MeasurementUnitController::class, 'create'])->name('units.create');
    });
    Route::middleware('permission:units.edit')->group(function () {
        Route::get('/units/{id}/edit', [MeasurementUnitController::class, 'edit'])->name('units.edit');
    });

    // Taxes
    Route::middleware('permission:taxes.view')->group(function () {
        Route::get('/taxes', [TaxController::class, 'index'])->name('taxes.index');
    });
    Route::middleware('permission:taxes.create')->group(function () {
        Route::get('/taxes/create', [TaxController::class, 'create'])->name('taxes.create');
    });
    Route::middleware('permission:taxes.edit')->group(function () {
        Route::get('/taxes/{id}/edit', [TaxController::class, 'edit'])->name('taxes.edit');
    });

    // Accounts
    Route::middleware('permission:accounts.view')->group(function () {
        Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    });
    Route::middleware('permission:accounts.create')->group(function () {
        Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    });
    Route::middleware('permission:accounts.edit')->group(function () {
        Route::get('/accounts/{id}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
    });

    // Treasuries
    Route::middleware('permission:treasuries.view')->group(function () {
        Route::get('/treasuries', [TreasuryController::class, 'index'])->name('treasuries.index');
    });
    Route::middleware('permission:treasuries.create')->group(function () {
        Route::get('/treasuries/create', [TreasuryController::class, 'create'])->name('treasuries.create');
    });
    Route::middleware('permission:treasuries.edit')->group(function () {
        Route::get('/treasuries/{id}/edit', [TreasuryController::class, 'edit'])->name('treasuries.edit');
    });

    // Treasury Transactions
    Route::middleware('permission:treasury-transactions.view')->group(function () {
        Route::get('/treasury-transactions', [TreasuryTransactionController::class, 'index'])->name('treasury-transactions.index');
    });
    Route::middleware('permission:treasury-transactions.create')->group(function () {
        Route::get('/treasury-transactions/create', [TreasuryTransactionController::class, 'create'])->name('treasury-transactions.create');
    });

    // Financial Transactions
    Route::middleware('permission:financial-transactions.view')->group(function () {
        Route::get('/financial-transactions', [FinancialTransactionController::class, 'index'])->name('financial-transactions.index');
    });
    Route::middleware('permission:financial-transactions.create')->group(function () {
        Route::get('/financial-transactions/create', [FinancialTransactionController::class, 'create'])->name('financial-transactions.create');
    });
    Route::middleware('permission:financial-transactions.edit')->group(function () {
        Route::get('/financial-transactions/{id}/edit', [FinancialTransactionController::class, 'edit'])->name('financial-transactions.edit');
    });

    // Reports
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/income-statement', [ReportController::class, 'incomeStatement'])->name('reports.income-statement');
        Route::get('/reports/account-statement', [ReportController::class, 'accountStatement'])->name('reports.account-statement');
        Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
    });

    // Trips
    Route::middleware('permission:trips.view')->group(function () {
        Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
        Route::get('/trips/create', [TripController::class, 'create'])->name('trips.create');
        Route::get('/trips/{id}/edit', [TripController::class, 'edit'])->name('trips.edit');
        Route::get('/trips/{id}/settle', [TripController::class, 'settle'])->name('trips.settle');
        Route::get('/trips/{id}/pdf', [TripController::class, 'pdf'])->name('trips.pdf');
        Route::get('/trips/{id}', [TripController::class, 'show'])->name('trips.show');
        Route::get('/booking-requests', [TripController::class, 'bookingRequests'])->name('trips.booking-requests');
        Route::get('/booking-requests/create', [TripController::class, 'createBookingRequest'])->name('trips.booking-requests.create');
    });

    // Products
    Route::middleware('permission:products.view')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/export/excel', [ProductImportExportController::class, 'export'])->name('products.export.excel');
    });
    Route::middleware('permission:products.create')->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::get('/products/import/template', [ProductImportExportController::class, 'template'])->name('products.import.template');
        Route::post('/products/import', [ProductImportExportController::class, 'import'])->name('products.import');
    });
    Route::middleware('permission:products.edit')->group(function () {
        Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    });

    // Stock Transfers
    Route::middleware('permission:stock-transfers.view')->group(function () {
        Route::get('/stock-transfers', [StockTransferController::class, 'index'])->name('stock-transfers.index');
    });
    Route::middleware('permission:stock-transfers.create')->group(function () {
        Route::get('/stock-transfers/create', [StockTransferController::class, 'create'])->name('stock-transfers.create');
    });

    // Inventory Dispatches
    Route::middleware('permission:inventory-dispatches.create')->group(function () {
        Route::get('/inventory-dispatches/create', [InventoryDispatchController::class, 'create'])->name('inventory-dispatches.create');
    });
    Route::middleware('permission:inventory-dispatches.view')->group(function () {
        Route::get('/inventory-dispatches', [InventoryDispatchController::class, 'index'])->name('inventory-dispatches.index');
        Route::get('/inventory-dispatches/{id}', [InventoryDispatchController::class, 'show'])->name('inventory-dispatches.show');
        Route::get('/inventory-dispatches/{id}/pdf', [InventoryDispatchController::class, 'showPdf'])->name('inventory-dispatches.pdf');
    });

    // ─── Accounting Exports (Excel & PDF) ───
    Route::middleware('permission:accounts.view')->group(function () {
        Route::get('/accounts/export/excel', [AccountingExportController::class, 'accountsExcel'])->name('accounts.export.excel');
        Route::get('/accounts/export/pdf', [AccountingExportController::class, 'accountsPdf'])->name('accounts.export.pdf');
    });
    Route::middleware('permission:treasuries.view')->group(function () {
        Route::get('/treasuries/export/excel', [AccountingExportController::class, 'treasuriesExcel'])->name('treasuries.export.excel');
        Route::get('/treasuries/export/pdf', [AccountingExportController::class, 'treasuriesPdf'])->name('treasuries.export.pdf');
    });
    Route::middleware('permission:treasury-transactions.view')->group(function () {
        Route::get('/treasury-transactions/export/excel', [AccountingExportController::class, 'treasuryTransactionsExcel'])->name('treasury-transactions.export.excel');
        Route::get('/treasury-transactions/export/pdf', [AccountingExportController::class, 'treasuryTransactionsPdf'])->name('treasury-transactions.export.pdf');
    });
    Route::middleware('permission:financial-transactions.view')->group(function () {
        Route::get('/financial-transactions/export/excel', [AccountingExportController::class, 'financialTransactionsExcel'])->name('financial-transactions.export.excel');
        Route::get('/financial-transactions/export/pdf', [AccountingExportController::class, 'financialTransactionsPdf'])->name('financial-transactions.export.pdf');
    });
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports/export/pdf', [AccountingExportController::class, 'reportsPdf'])->name('reports.export.pdf');
    });

    // Purchase Invoices
    Route::middleware('permission:purchase-invoices.view')->group(function () {
        Route::get('/purchase-invoices', [PurchaseInvoiceController::class, 'index'])->name('purchase-invoices.index');
        Route::get('/purchase-invoices/export/pdf', [PurchaseInvoiceController::class, 'exportPdf'])->name('purchase-invoices.export.pdf');
        Route::get('/purchase-invoices/create', [PurchaseInvoiceController::class, 'create'])->name('purchase-invoices.create')->middleware('permission:purchase-invoices.create');
        Route::get('/purchase-invoices/{id}', [PurchaseInvoiceController::class, 'show'])->name('purchase-invoices.show');
        Route::get('/purchase-invoices/{id}/edit', [PurchaseInvoiceController::class, 'edit'])->name('purchase-invoices.edit')->middleware('permission:purchase-invoices.edit');
    });

    // Purchase Returns
    Route::middleware('permission:purchase-returns.view')->group(function () {
        Route::get('/purchase-returns', [PurchaseReturnController::class, 'index'])->name('purchase-returns.index');
        Route::get('/purchase-returns/export/pdf', [PurchaseReturnController::class, 'exportPdf'])->name('purchase-returns.export.pdf');
        Route::get('/purchase-returns/create', [PurchaseReturnController::class, 'create'])->name('purchase-returns.create')->middleware('permission:purchase-returns.create');
        Route::get('/purchase-returns/{id}/pdf', [PurchaseReturnController::class, 'showPdf'])->name('purchase-returns.show.pdf');
    });

    // Product Depreciations
    Route::middleware('permission:product-depreciations.view')->group(function () {
        Route::get('/product-depreciations', [ProductDepreciationController::class, 'index'])->name('product-depreciations.index');
        Route::get('/product-depreciations/create', [ProductDepreciationController::class, 'create'])->name('product-depreciations.create')->middleware('permission:product-depreciations.create');
        Route::get('/product-depreciations/{id}', [ProductDepreciationController::class, 'show'])->name('product-depreciations.show');
        Route::get('/product-depreciations/{id}/pdf', [ProductDepreciationController::class, 'showPdf'])->name('product-depreciations.pdf');
    });

    // Sale Quotations
    Route::middleware('permission:sale-quotations.view')->group(function () {
        Route::get('/sale-quotations', [SaleQuotationController::class, 'index'])->name('sale-quotations.index');
        Route::get('/sale-quotations/create', [SaleQuotationController::class, 'create'])->name('sale-quotations.create')->middleware('permission:sale-quotations.create');
        Route::get('/sale-quotations/{id}', [SaleQuotationController::class, 'show'])->name('sale-quotations.show');
        Route::get('/sale-quotations/{id}/pdf', [SaleQuotationController::class, 'showPdf'])->name('sale-quotations.pdf');
    });

    // Sale Orders
    Route::middleware('permission:sale-orders.view')->group(function () {
        Route::get('/sale-orders', [SaleOrderController::class, 'index'])->name('sale-orders.index');
        Route::get('/sale-orders/create', [SaleOrderController::class, 'create'])->name('sale-orders.create')->middleware('permission:sale-orders.create');
        Route::get('/sale-orders/{id}', [SaleOrderController::class, 'show'])->name('sale-orders.show');
        Route::get('/sale-orders/{id}/pdf', [SaleOrderController::class, 'showPdf'])->name('sale-orders.pdf');
    });

    // Sale Returns
    Route::middleware('permission:sale-returns.view')->group(function () {
        Route::get('/sale-returns', [SaleReturnController::class, 'index'])->name('sale-returns.index');
        Route::get('/sale-returns/create', [SaleReturnController::class, 'create'])->name('sale-returns.create')->middleware('permission:sale-returns.create');
        Route::get('/sale-returns/{id}', [SaleReturnController::class, 'show'])->name('sale-returns.show');
        Route::get('/sale-returns/{id}/pdf', [SaleReturnController::class, 'showPdf'])->name('sale-returns.pdf');
    });

    // Installments
    Route::middleware('permission:installments.view')->group(function () {
        Route::get('/installments', [InstallmentController::class, 'index'])->name('installments.index');
        Route::get('/installments/create', [InstallmentController::class, 'create'])->name('installments.create')->middleware('permission:installments.create');
        Route::get('/installments/{id}', [InstallmentController::class, 'show'])->name('installments.show');
    });

    // Collections (تحصيلات)
    Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index')->middleware('permission:collections.view');
    Route::get('/collections/create', [CollectionController::class, 'create'])->name('collections.create')->middleware('permission:collections.create');
    Route::get('/collections/{id}', [CollectionController::class, 'show'])->name('collections.show')->middleware('permission:collections.view');

    // Branch Reports
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports/branch-inventory', [BranchReportController::class, 'inventory'])->name('reports.branch-inventory');
        Route::get('/reports/branch-inventory/export/pdf', [BranchReportController::class, 'inventoryPdf'])->name('reports.branch-inventory.export.pdf');
        Route::get('/reports/branch-movements', [BranchReportController::class, 'movements'])->name('reports.branch-movements');
    });

    // HR - Leaves
    Route::middleware('permission:hr.view')->group(function () {
        Route::get('/hr/leaves', [HrController::class, 'leavesIndex'])->name('hr.leaves.index');
    });
    Route::middleware('permission:hr.create')->group(function () {
        Route::get('/hr/leaves/create', [HrController::class, 'leavesCreate'])->name('hr.leaves.create');
    });
    Route::middleware('permission:hr.edit')->group(function () {
        Route::get('/hr/leaves/{id}/edit', [HrController::class, 'leavesEdit'])->name('hr.leaves.edit');
    });

    // HR - Attendance
    Route::middleware('permission:hr.view')->group(function () {
        Route::get('/hr/attendance', [HrController::class, 'attendanceIndex'])->name('hr.attendance.index');
    });
    Route::middleware('permission:hr.create')->group(function () {
        Route::get('/hr/attendance/create', [HrController::class, 'attendanceCreate'])->name('hr.attendance.create');
    });
    Route::middleware('permission:hr.edit')->group(function () {
        Route::get('/hr/attendance/{id}/edit', [HrController::class, 'attendanceEdit'])->name('hr.attendance.edit');
    });

    // HR - Salaries
    Route::middleware('permission:hr.view')->group(function () {
        Route::get('/hr/salaries', [HrController::class, 'salariesIndex'])->name('hr.salaries.index');
    });
    Route::middleware('permission:hr.create')->group(function () {
        Route::get('/hr/salaries/create', [HrController::class, 'salariesCreate'])->name('hr.salaries.create');
    });
    Route::middleware('permission:hr.edit')->group(function () {
        Route::get('/hr/salaries/{id}/edit', [HrController::class, 'salariesEdit'])->name('hr.salaries.edit');
    });
});
