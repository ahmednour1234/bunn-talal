<?php

namespace App\Providers;

use App\Repositories\Contracts\AdminRepositoryInterface;
use App\Repositories\Contracts\AreaRepositoryInterface;
use App\Repositories\Contracts\BranchRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\DelegateRepositoryInterface;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UnitRepositoryInterface;
use App\Repositories\Contracts\VehicleRepositoryInterface;
use App\Repositories\Contracts\AccountRepositoryInterface;
use App\Repositories\Contracts\TreasuryRepositoryInterface;
use App\Repositories\Contracts\TreasuryTransactionRepositoryInterface;
use App\Repositories\Contracts\FinancialTransactionRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\StockTransferRepositoryInterface;
use App\Repositories\Contracts\InventoryDispatchRepositoryInterface;
use App\Repositories\Contracts\TaxRepositoryInterface;
use App\Repositories\Contracts\PurchaseInvoiceRepositoryInterface;
use App\Repositories\Contracts\PurchaseReturnRepositoryInterface;
use App\Repositories\Contracts\ProductDepreciationRepositoryInterface;
use App\Repositories\Contracts\SaleOrderRepositoryInterface;
use App\Repositories\Contracts\SaleQuotationRepositoryInterface;
use App\Repositories\Contracts\SaleReturnRepositoryInterface;
use App\Repositories\Contracts\InstallmentPlanRepositoryInterface;
use App\Repositories\Eloquent\AdminRepository;
use App\Repositories\Eloquent\AreaRepository;
use App\Repositories\Eloquent\BranchRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\CustomerRepository;
use App\Repositories\Eloquent\DelegateRepository;
use App\Repositories\Eloquent\SupplierRepository;
use App\Repositories\Eloquent\PermissionRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\UnitRepository;
use App\Repositories\Eloquent\VehicleRepository;
use App\Repositories\Eloquent\AccountRepository;
use App\Repositories\Eloquent\TreasuryRepository;
use App\Repositories\Eloquent\TreasuryTransactionRepository;
use App\Repositories\Eloquent\FinancialTransactionRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\StockTransferRepository;
use App\Repositories\Eloquent\InventoryDispatchRepository;
use App\Repositories\Eloquent\TaxRepository;
use App\Repositories\Eloquent\PurchaseInvoiceRepository;
use App\Repositories\Eloquent\PurchaseReturnRepository;
use App\Repositories\Eloquent\ProductDepreciationRepository;
use App\Repositories\Eloquent\SaleOrderRepository;
use App\Repositories\Eloquent\SaleQuotationRepository;
use App\Repositories\Eloquent\SaleReturnRepository;
use App\Repositories\Eloquent\InstallmentPlanRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminRepositoryInterface::class, AdminRepository::class);
        $this->app->bind(AreaRepositoryInterface::class, AreaRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(BranchRepositoryInterface::class, BranchRepository::class);
        $this->app->bind(VehicleRepositoryInterface::class, VehicleRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(DelegateRepositoryInterface::class, DelegateRepository::class);
        $this->app->bind(SupplierRepositoryInterface::class, SupplierRepository::class);
        $this->app->bind(UnitRepositoryInterface::class, UnitRepository::class);
        $this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);
        $this->app->bind(TreasuryRepositoryInterface::class, TreasuryRepository::class);
        $this->app->bind(TreasuryTransactionRepositoryInterface::class, TreasuryTransactionRepository::class);
        $this->app->bind(FinancialTransactionRepositoryInterface::class, FinancialTransactionRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(StockTransferRepositoryInterface::class, StockTransferRepository::class);
        $this->app->bind(InventoryDispatchRepositoryInterface::class, InventoryDispatchRepository::class);
        $this->app->bind(TaxRepositoryInterface::class, TaxRepository::class);
        $this->app->bind(PurchaseInvoiceRepositoryInterface::class, PurchaseInvoiceRepository::class);
        $this->app->bind(PurchaseReturnRepositoryInterface::class, PurchaseReturnRepository::class);
        $this->app->bind(ProductDepreciationRepositoryInterface::class, ProductDepreciationRepository::class);
        $this->app->bind(SaleOrderRepositoryInterface::class, SaleOrderRepository::class);
        $this->app->bind(SaleQuotationRepositoryInterface::class, SaleQuotationRepository::class);
        $this->app->bind(SaleReturnRepositoryInterface::class, SaleReturnRepository::class);
        $this->app->bind(InstallmentPlanRepositoryInterface::class, InstallmentPlanRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
