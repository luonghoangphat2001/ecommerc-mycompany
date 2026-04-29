<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class AuditStockConsistencyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:audit-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit distributed warehouse stocks vs denormalized product overall stocks';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $products = Product::all();
        $mismatches = 0;

        foreach ($products as $product) {
            // Sum of stocks across all warehouses
            $sum = DB::table('shop_product_inventory_stocks')
                ->where('shop_product_id', $product->id)
                ->sum('stock_quantity');

            if ((int)$sum !== (int)$product->total_stock) {
                $this->warn("Mismatch found for Product SKU: {$product->sku}. Expected overall sum: {$sum}, Denormalized total: {$product->total_stock}");
                
                // Auto-fix
                DB::table('shop_products')
                    ->where('id', $product->id)
                    ->update(['total_stock' => $sum]);

                $mismatches++;
            }
        }

        if ($mismatches > 0) {
            $this->info("Audit completed. Automatically corrected {$mismatches} inconsistencies successfully.");
        } else {
            $this->info("Audit completed. All inventory balances completely synchronized.");
        }

        return Command::SUCCESS;
    }
}
