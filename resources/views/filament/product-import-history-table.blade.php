{{-- resources/views/filament/components/product-import-history-table.blade.php --}}
@php
    use Carbon\Carbon;
@endphp

<div class="w-full overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Import ID</th>
                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Supplier</th>
                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Qty</th>
                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Unit Price</th>
                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Imported By</th>
            </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($items as $item)
                <tr>
                    <td class="px-4 py-2 text-sm">
                        <a href="{{ \App\Filament\Resources\ProductImportResource::getUrl('view', ['record' => $item->product_import_id]) }}"
                            class="font-medium hover:underline">
                            {{ $item->import->id ?? $item->product_import_id }}
                        </a>
                    </td>

                    <td class="px-4 py-2 text-sm">
                        {{ $item->import->supplier->name ?? '-' }}
                    </td>

                    <td class="px-4 py-2 text-sm">
                        {{ $item->qty }}
                    </td>

                    <td class="px-4 py-2 text-sm">
                        ${{ number_format($item->unit_price, 2) }}
                    </td>

                    <td class="px-4 py-2 text-sm">
                        @php $d = optional($item->import)->import_date; @endphp
                        {{ $d ? Carbon::parse($d)->format('d/m/Y') : '-' }}
                    </td>

                    <td class="px-4 py-2 text-sm">
                        {{ $item->import->user->name ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-500" colspan="6">No import history found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
