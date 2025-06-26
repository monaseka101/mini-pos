<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Models\Product;
use App\Models\Sale;
use Filament\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CreateSale extends CreateRecord
{
    use HasWizard;

    protected static string $resource = SaleResource::class;

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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }

    protected function afterCreate(): void
    {
        $items =  $this->record->items;
        $total = 0;

        foreach ($items as $item) {

            $unitPrice = $item->unit_price;
            $qty = $item->qty;
            $discountAmount = 0;
            if ($item->discount_id) {
                $discountModel = \App\Models\Discount::find($item->discount_id);
                if ($discountModel && $discountModel->value > 0) {
                    $discount = $discountModel->value;
                    $isPercent = $discountModel->ispercent;

                    $discountAmount = $isPercent
                        ? ($unitPrice * $discount / 100)
                        : $discount;
                }
            }

            $finalPrice = ($unitPrice - $discountAmount) * $qty;
            $total += $finalPrice;

            $product = Product::find($item->product_id);
            if ($product && $product->stock >= $item->qty) {
                $product->decrement('stock', $item->qty);
            }
        }

        $this->record->total_pay = $this->record->totalPay(); // ← this line saves the calculated value
        $this->record->save();
    }


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
                        SaleResource::getItemsRepeater()
                    ]),
                ]),
        ];
    }



    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
