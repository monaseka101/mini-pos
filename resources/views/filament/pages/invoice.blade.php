<x-filament::page>
    <h1>Invoice for Sale #{{ $this->record->id }}</h1>

    <table class="w-full">
        <thead>
            <tr>
                <th class="text-left">Product</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($this->record->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-right">{{ $item->qty }}</td>
                    <td class="text-right">${{ $item->unit_price }}</td>
                    <td class="text-right">${{ $item->qty * $item->unit_price }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <strong>Total: ${{ $this->record->total_price }}</strong>
</x-filament::page>
