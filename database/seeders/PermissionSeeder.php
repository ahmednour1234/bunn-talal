<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Branches
            ['name' => 'branches.view', 'display_name' => 'عرض الفروع', 'group_name' => 'الفروع'],
            ['name' => 'branches.create', 'display_name' => 'إضافة فرع', 'group_name' => 'الفروع'],
            ['name' => 'branches.edit', 'display_name' => 'تعديل فرع', 'group_name' => 'الفروع'],
            ['name' => 'branches.delete', 'display_name' => 'حذف فرع', 'group_name' => 'الفروع'],

            // Admins
            ['name' => 'admins.view', 'display_name' => 'عرض المدراء', 'group_name' => 'المدراء'],
            ['name' => 'admins.create', 'display_name' => 'إضافة مدير', 'group_name' => 'المدراء'],
            ['name' => 'admins.edit', 'display_name' => 'تعديل مدير', 'group_name' => 'المدراء'],
            ['name' => 'admins.delete', 'display_name' => 'حذف مدير', 'group_name' => 'المدراء'],

            // Roles
            ['name' => 'roles.view', 'display_name' => 'عرض الأدوار', 'group_name' => 'الأدوار'],
            ['name' => 'roles.create', 'display_name' => 'إضافة دور', 'group_name' => 'الأدوار'],
            ['name' => 'roles.edit', 'display_name' => 'تعديل دور', 'group_name' => 'الأدوار'],
            ['name' => 'roles.delete', 'display_name' => 'حذف دور', 'group_name' => 'الأدوار'],

            // Permissions
            ['name' => 'permissions.view', 'display_name' => 'عرض الصلاحيات', 'group_name' => 'الصلاحيات'],
            ['name' => 'permissions.create', 'display_name' => 'إضافة صلاحية', 'group_name' => 'الصلاحيات'],

            // Vehicles
            ['name' => 'vehicles.view', 'display_name' => 'عرض المركبات', 'group_name' => 'المركبات'],
            ['name' => 'vehicles.create', 'display_name' => 'إضافة مركبة', 'group_name' => 'المركبات'],
            ['name' => 'vehicles.edit', 'display_name' => 'تعديل مركبة', 'group_name' => 'المركبات'],
            ['name' => 'vehicles.delete', 'display_name' => 'حذف مركبة', 'group_name' => 'المركبات'],

            // Categories
            ['name' => 'categories.view', 'display_name' => 'عرض التصنيفات', 'group_name' => 'التصنيفات'],
            ['name' => 'categories.create', 'display_name' => 'إضافة تصنيف', 'group_name' => 'التصنيفات'],
            ['name' => 'categories.edit', 'display_name' => 'تعديل تصنيف', 'group_name' => 'التصنيفات'],
            ['name' => 'categories.delete', 'display_name' => 'حذف تصنيف', 'group_name' => 'التصنيفات'],

            // Areas
            ['name' => 'areas.view', 'display_name' => 'عرض المناطق', 'group_name' => 'المناطق'],
            ['name' => 'areas.create', 'display_name' => 'إضافة منطقة', 'group_name' => 'المناطق'],
            ['name' => 'areas.edit', 'display_name' => 'تعديل منطقة', 'group_name' => 'المناطق'],
            ['name' => 'areas.delete', 'display_name' => 'حذف منطقة', 'group_name' => 'المناطق'],

            // Customers
            ['name' => 'customers.view', 'display_name' => 'عرض العملاء', 'group_name' => 'العملاء'],
            ['name' => 'customers.create', 'display_name' => 'إضافة عميل', 'group_name' => 'العملاء'],
            ['name' => 'customers.edit', 'display_name' => 'تعديل عميل', 'group_name' => 'العملاء'],
            ['name' => 'customers.delete', 'display_name' => 'حذف عميل', 'group_name' => 'العملاء'],

            // Suppliers
            ['name' => 'suppliers.view', 'display_name' => 'عرض الموردين', 'group_name' => 'الموردين'],
            ['name' => 'suppliers.create', 'display_name' => 'إضافة مورد', 'group_name' => 'الموردين'],
            ['name' => 'suppliers.edit', 'display_name' => 'تعديل مورد', 'group_name' => 'الموردين'],
            ['name' => 'suppliers.delete', 'display_name' => 'حذف مورد', 'group_name' => 'الموردين'],

            // Delegates
            ['name' => 'delegates.view', 'display_name' => 'عرض المناديب', 'group_name' => 'المناديب'],
            ['name' => 'delegates.create', 'display_name' => 'إضافة مندوب', 'group_name' => 'المناديب'],
            ['name' => 'delegates.edit', 'display_name' => 'تعديل مندوب', 'group_name' => 'المناديب'],
            ['name' => 'delegates.delete', 'display_name' => 'حذف مندوب', 'group_name' => 'المناديب'],

            // Measurement Units
            ['name' => 'units.view', 'display_name' => 'عرض وحدات القياس', 'group_name' => 'وحدات القياس'],
            ['name' => 'units.create', 'display_name' => 'إضافة وحدة قياس', 'group_name' => 'وحدات القياس'],
            ['name' => 'units.edit', 'display_name' => 'تعديل وحدة قياس', 'group_name' => 'وحدات القياس'],
            ['name' => 'units.delete', 'display_name' => 'حذف وحدة قياس', 'group_name' => 'وحدات القياس'],

            // Accounts
            ['name' => 'accounts.view', 'display_name' => 'عرض الحسابات', 'group_name' => 'الحسابات'],
            ['name' => 'accounts.create', 'display_name' => 'إضافة حساب', 'group_name' => 'الحسابات'],
            ['name' => 'accounts.edit', 'display_name' => 'تعديل حساب', 'group_name' => 'الحسابات'],
            ['name' => 'accounts.delete', 'display_name' => 'حذف حساب', 'group_name' => 'الحسابات'],

            // Treasuries
            ['name' => 'treasuries.view', 'display_name' => 'عرض الخزن', 'group_name' => 'الخزن'],
            ['name' => 'treasuries.create', 'display_name' => 'إضافة خزنة', 'group_name' => 'الخزن'],
            ['name' => 'treasuries.edit', 'display_name' => 'تعديل خزنة', 'group_name' => 'الخزن'],
            ['name' => 'treasuries.delete', 'display_name' => 'حذف خزنة', 'group_name' => 'الخزن'],

            // Treasury Transactions
            ['name' => 'treasury-transactions.view', 'display_name' => 'عرض حركات الخزن', 'group_name' => 'حركات الخزن'],
            ['name' => 'treasury-transactions.create', 'display_name' => 'إضافة حركة خزنة', 'group_name' => 'حركات الخزن'],

            // Financial Transactions
            ['name' => 'financial-transactions.view', 'display_name' => 'عرض المصروفات والإيرادات', 'group_name' => 'المصروفات والإيرادات'],
            ['name' => 'financial-transactions.create', 'display_name' => 'إضافة مصروف أو إيراد', 'group_name' => 'المصروفات والإيرادات'],
            ['name' => 'financial-transactions.edit', 'display_name' => 'تعديل مصروف أو إيراد', 'group_name' => 'المصروفات والإيرادات'],
            ['name' => 'financial-transactions.delete', 'display_name' => 'حذف مصروف أو إيراد', 'group_name' => 'المصروفات والإيرادات'],

            // Reports
            ['name' => 'reports.view', 'display_name' => 'عرض التقارير', 'group_name' => 'التقارير'],

            // Taxes
            ['name' => 'taxes.view', 'display_name' => 'عرض الضرائب', 'group_name' => 'الضرائب'],
            ['name' => 'taxes.create', 'display_name' => 'إضافة ضريبة', 'group_name' => 'الضرائب'],
            ['name' => 'taxes.edit', 'display_name' => 'تعديل ضريبة', 'group_name' => 'الضرائب'],
            ['name' => 'taxes.delete', 'display_name' => 'حذف ضريبة', 'group_name' => 'الضرائب'],

            // Products
            ['name' => 'products.view', 'display_name' => 'عرض المنتجات', 'group_name' => 'المنتجات'],
            ['name' => 'products.create', 'display_name' => 'إضافة منتج', 'group_name' => 'المنتجات'],
            ['name' => 'products.edit', 'display_name' => 'تعديل منتج', 'group_name' => 'المنتجات'],
            ['name' => 'products.delete', 'display_name' => 'حذف منتج', 'group_name' => 'المنتجات'],

            // Stock Transfers
            ['name' => 'stock-transfers.view', 'display_name' => 'عرض تحويلات المخزون', 'group_name' => 'تحويلات المخزون'],
            ['name' => 'stock-transfers.create', 'display_name' => 'إنشاء تحويل مخزون', 'group_name' => 'تحويلات المخزون'],
            ['name' => 'stock-transfers.approve', 'display_name' => 'اعتماد تحويل مخزون', 'group_name' => 'تحويلات المخزون'],
            ['name' => 'stock-transfers.receive', 'display_name' => 'استلام تحويل مخزون', 'group_name' => 'تحويلات المخزون'],

            // Inventory Dispatches
            ['name' => 'inventory-dispatches.view', 'display_name' => 'عرض أوامر الصرف', 'group_name' => 'أوامر الصرف'],
            ['name' => 'inventory-dispatches.create', 'display_name' => 'إنشاء أمر صرف', 'group_name' => 'أوامر الصرف'],
            ['name' => 'inventory-dispatches.edit', 'display_name' => 'تعديل أمر صرف', 'group_name' => 'أوامر الصرف'],
            ['name' => 'inventory-dispatches.delete', 'display_name' => 'حذف أمر صرف', 'group_name' => 'أوامر الصرف'],

            // Purchase Invoices
            ['name' => 'purchase-invoices.view', 'display_name' => 'عرض فواتير المشتريات', 'group_name' => 'فواتير المشتريات'],
            ['name' => 'purchase-invoices.create', 'display_name' => 'إنشاء فاتورة مشتريات', 'group_name' => 'فواتير المشتريات'],
            ['name' => 'purchase-invoices.edit', 'display_name' => 'تعديل فاتورة مشتريات', 'group_name' => 'فواتير المشتريات'],
            ['name' => 'purchase-invoices.delete', 'display_name' => 'حذف فاتورة مشتريات', 'group_name' => 'فواتير المشتريات'],

            // Purchase Returns
            ['name' => 'purchase-returns.view', 'display_name' => 'عرض مرتجعات المشتريات', 'group_name' => 'مرتجعات المشتريات'],
            ['name' => 'purchase-returns.create', 'display_name' => 'إنشاء مرتجع مشتريات', 'group_name' => 'مرتجعات المشتريات'],

            // Sale Quotations
            ['name' => 'sale-quotations.view', 'display_name' => 'عرض عروض الأسعار', 'group_name' => 'عروض الأسعار'],
            ['name' => 'sale-quotations.create', 'display_name' => 'إنشاء عرض سعر', 'group_name' => 'عروض الأسعار'],
            ['name' => 'sale-quotations.edit', 'display_name' => 'تعديل عرض سعر', 'group_name' => 'عروض الأسعار'],

            // Sale Orders
            ['name' => 'sale-orders.view', 'display_name' => 'عرض طلبات المبيعات', 'group_name' => 'طلبات المبيعات'],
            ['name' => 'sale-orders.create', 'display_name' => 'إنشاء طلب مبيعات', 'group_name' => 'طلبات المبيعات'],
            ['name' => 'sale-orders.edit', 'display_name' => 'تعديل/إلغاء طلب مبيعات', 'group_name' => 'طلبات المبيعات'],

            // Sale Returns
            ['name' => 'sale-returns.view', 'display_name' => 'عرض مرتجعات المبيعات', 'group_name' => 'مرتجعات المبيعات'],
            ['name' => 'sale-returns.create', 'display_name' => 'إنشاء/تأكيد مرتجع مبيعات', 'group_name' => 'مرتجعات المبيعات'],

            // Installments
            ['name' => 'installments.view', 'display_name' => 'عرض خطط التقسيط', 'group_name' => 'التقسيط'],
            ['name' => 'installments.create', 'display_name' => 'إنشاء خطة تقسيط', 'group_name' => 'التقسيط'],
            ['name' => 'installments.edit', 'display_name' => 'تعديل/إلغاء خطة تقسيط', 'group_name' => 'التقسيط'],

            // Product Depreciations
            ['name' => 'product-depreciations.view', 'display_name' => 'عرض إهلاك المنتجات', 'group_name' => 'إهلاك المنتجات'],
            ['name' => 'product-depreciations.create', 'display_name' => 'إنشاء طلب إهلاك', 'group_name' => 'إهلاك المنتجات'],
            ['name' => 'product-depreciations.approve', 'display_name' => 'موافقة/رفض طلب إهلاك', 'group_name' => 'إهلاك المنتجات'],

            // Trips
            ['name' => 'trips.view', 'display_name' => 'عرض الرحلات', 'group_name' => 'الرحلات'],
            ['name' => 'trips.create', 'display_name' => 'إنشاء رحلة', 'group_name' => 'الرحلات'],
            ['name' => 'trips.edit', 'display_name' => 'تعديل رحلة', 'group_name' => 'الرحلات'],
            ['name' => 'trips.settle', 'display_name' => 'تسوية رحلة', 'group_name' => 'الرحلات'],
            ['name' => 'trips.approve-settlement', 'display_name' => 'موافقة/رفض تسوية رحلة', 'group_name' => 'الرحلات'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}
