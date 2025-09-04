<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Models\Sale;
use Filament\Resources\Pages\Page;

class SaleInvoice extends Page
{
    protected static string $resource = SaleResource::class;
    protected static string $view = 'filament.pages.invoice';

    public ?Sale $record = null;

    public function mount(Sale $record): void
    {
        // eager load anything you need
        $this->record = $record->load(['items.product', 'customer']);
    }
}
