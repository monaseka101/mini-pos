<?php

namespace App\Filament\Pages;

use App\Models\ProductImport;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\ProductImportItem;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class DetailPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Import History';
    protected static string $view = 'filament.pages.detail-page'; // your Blade view
    public int|string|null $productId = null;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public string $productName = '';

    public function mount(): void
    {
        $this->productId = request()->query('product_id');

        if ($this->productId) {
            $product = \App\Models\Product::find($this->productId);
            $this->productName = $product ? $product->name : 'Unknown Product';
        }
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return ProductImportItem::with('productImport.supplier', 'productImport.user')
            ->where('product_id', $this->productId);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('productImport.id')->label('Import ID')->sortable(),
            Tables\Columns\TextColumn::make('productImport.supplier.name')->label('Supplier'),
            Tables\Columns\TextColumn::make('qty')->label('Quantity')->sortable(),
            Tables\Columns\TextColumn::make('unit_price')->label('Unit Price')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('productImport.import_date')->label('Date')->date('d/m/Y')->sortable(),
            Tables\Columns\TextColumn::make('productImport.user.name')->label('Imported By'),
            Tables\Columns\TextColumn::make('sub_total')
                ->label('Sub Total')
                ->color('danger')
                ->money(currency: 'usd')
                ->getStateUsing(fn($record) => $record->qty * $record->unit_price)
                ->sortable(
                    query: fn(Builder $query, string $direction) =>
                    $query->orderByRaw('qty * unit_price ' . $direction)
                )
                ->weight(FontWeight::Bold),
        ];
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'productImport.import_date';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }

    protected function getTableFilters(): array
    {
        return [
            Filter::make('import_date')
                ->form([
                    DatePicker::make('start')->label('Start Date'),
                    DatePicker::make('end')->label('End Date'),
                ])
                ->query(function ($query, array $data) {
                    $query->whereHas('productImport', function ($q) use ($data) {
                        if (!empty($data['start']) && !empty($data['end'])) {
                            $q->whereBetween('import_date', [$data['start'], $data['end']]);
                        } elseif (!empty($data['start'])) {
                            $q->where('import_date', '>=', $data['start']);
                        } elseif (!empty($data['end'])) {
                            $q->where('import_date', '<=', $data['end']);
                        }
                    });
                })
                ->default(fn() => [
                    'start' => request()->get('startDate') ? \Carbon\Carbon::parse(request()->get('startDate')) : null,
                    'end'   => request()->get('endDate') ? \Carbon\Carbon::parse(request()->get('endDate')) : null,
                ]),
            Tables\Filters\SelectFilter::make('supplier')
                ->label('Supplier')
                ->relationship('productImport.supplier', 'name') // <- nested relation
                ->preload()
                ->searchable()
                ->multiple(),
        ];
    }
    /* protected function getTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
        ];
    } */
}
