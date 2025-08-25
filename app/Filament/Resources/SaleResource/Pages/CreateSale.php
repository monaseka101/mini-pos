<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Discount;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Forms\Form;

class CreateSale extends CreateRecord
{
    use HasWizard;

    protected static string $resource = SaleResource::class;

    /**
     * Define the overall form structure using a wizard with steps.
     */
    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Wizard::make($this->getSteps())
                    ->startOnStep($this->getStartStep())
                    ->cancelAction($this->getCancelFormAction())
                    ->submitAction($this->getSubmitFormAction())
                    ->skippable($this->hasSkippableSteps())
                    ->contained(false),
            ])
            ->columns(null);
    }

    /**
     * Automatically assign the current user as the creator of the sale.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }

    /**
     * After creating the sale:
     * - Calculate total with discounts
     * - Decrement product stock accordingly
     */
    protected function afterCreate(): void
    {
        $items = $this->record->items;
        $total = 0;

        foreach ($items as $item) {
            $unitPrice = $item->unit_price;
            $qty = $item->qty;
            $discountAmount = 0;

            // Check and calculate discount
            if ($item->discount_id) {
                $discountModel = Discount::find($item->discount_id);
                if ($discountModel && $discountModel->value > 0) {
                    $discount = $discountModel->value;
                    $discountAmount = $discountModel->ispercent
                        ? ($unitPrice * $discount / 100)
                        : $discount;
                }
            }

            $finalPrice = ($unitPrice - $discountAmount) * $qty;
            $total += $finalPrice;

            // Decrease product stock
            $product = Product::find($item->product_id);
            if ($product && $product->stock >= $qty) {
                $product->decrement('stock', $qty);
            }
        }

        // Save the computed total
        $this->record->total_pay = $this->record->totalPay();
        $this->record->save();
    }

    /**
     * Define wizard steps (Sale Details & Sale Items)
     */
    protected function getSteps(): array
    {
        return [
            Step::make('Sale Details')
                ->schema([
                    Section::make()->schema(SaleResource::getDetailsFormSchema())->columns(),
                ]),
            Step::make('Sale Items')
                ->schema([
                    Section::make()->schema([
                        SaleResource::getItemsRepeater(),
                    ]),
                ]),
        ];
    }

    /**
     * Redirect to the index page after successful creation
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
