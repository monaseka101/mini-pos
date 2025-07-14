<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SnapshotInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:snapshot-inventory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save daily total inventory into snapshots';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();
        $totalStock = \App\Models\Product::sum('stock');

        \App\Models\InventorySnapshot::updateOrCreate(
            ['date' => $today],
            ['total_stock' => $totalStock]
        );

        $this->info("Inventory snapshot saved for {$today}: {$totalStock}");
    }
}
