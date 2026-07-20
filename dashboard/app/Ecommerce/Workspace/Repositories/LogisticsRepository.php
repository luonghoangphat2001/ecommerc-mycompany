<?php

namespace App\Ecommerce\Workspace\Repositories;

use App\Models\DepartmentPurchaseOrder;
use App\Models\ShopProductInventoryStock;
use App\Models\Product;

class LogisticsRepository implements LogisticsRepositoryInterface
{
    public function getMetrics(string $period = 'all'): array
    {
        $totalStock = ShopProductInventoryStock::sum('stock_quantity');
        $lowStock = Product::whereColumn('qty', '<=', 'security_stock')->count();
        $pendingPOs = DepartmentPurchaseOrder::whereIn('status', ['shipping', 'partial'])->count();
        $totalPOs = DepartmentPurchaseOrder::count();

        $poRate = $totalPOs > 0 ? round(($pendingPOs / $totalPOs) * 100, 2) . '%' : 'N/A';

        return [
            'total_stock' => $totalStock,
            'low_stock_alerts' => $lowStock,
            'pending_pos' => $pendingPOs,
            'po_rate' => $poRate,
        ];
    }

    public function getLowStockAlerts(string $period = 'all')
    {
        return Product::whereColumn('qty', '<=', 'security_stock')
            ->select(['id', 'name', 'sku', 'qty as stock_quantity', 'security_stock as low_stock_threshold'])
            ->get();
    }

    public function getInventoryStocks(string $period = 'all')
    {
        return ShopProductInventoryStock::with(['product:id,name,sku', 'inventory:id,name'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function getPurchaseOrders(string $period = 'all')
    {
        return DepartmentPurchaseOrder::orderBy('updated_at', 'desc')->get();
    }

    public function createPO(array $data)
    {
        return DepartmentPurchaseOrder::create($data);
    }

    public function updatePO($id, array $data)
    {
        $po = DepartmentPurchaseOrder::findOrFail($id);
        $po->update($data);
        return $po;
    }

    public function deletePO($id)
    {
        $po = DepartmentPurchaseOrder::findOrFail($id);
        return $po->delete();
    }
}
