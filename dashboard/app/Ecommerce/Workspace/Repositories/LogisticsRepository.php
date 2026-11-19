<?php

namespace App\Ecommerce\Workspace\Repositories;

use App\Ecommerce\Workspace\Repositories\Concerns\HasWorkspaceCrudOperations;
use App\Models\DepartmentPurchaseOrder;
use App\Models\Product;
use App\Models\ShopProductInventoryStock;

class LogisticsRepository implements LogisticsRepositoryInterface
{
    use HasWorkspaceCrudOperations;

    public function getMetrics(string $period = 'all'): array
    {
        $totalStock = ShopProductInventoryStock::sum('stock_quantity');
        $lowStock = $this->lowStockProductsQuery()->count();
        $poMetrics = DepartmentPurchaseOrder::query()
            ->selectRaw('COUNT(*) as total_pos')
            ->selectRaw("SUM(CASE WHEN status IN ('shipping', 'partial') THEN 1 ELSE 0 END) as pending_pos")
            ->first();
        $pendingPOs = (int) ($poMetrics->pending_pos ?? 0);
        $totalPOs = (int) ($poMetrics->total_pos ?? 0);

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
        return $this->lowStockProductsQuery()
            ->select(['id', 'name', 'sku', 'qty as stock_quantity', 'security_stock as low_stock_threshold'])
            ->get();
    }

    private function lowStockProductsQuery()
    {
        return Product::query()->whereColumn('qty', '<=', 'security_stock');
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
        return $this->createWorkspaceModel(DepartmentPurchaseOrder::class, $data);
    }

    public function updatePO($id, array $data)
    {
        return $this->updateWorkspaceModel(DepartmentPurchaseOrder::class, $id, $data);
    }

    public function deletePO($id)
    {
        return $this->deleteWorkspaceModel(DepartmentPurchaseOrder::class, $id);
    }
}
