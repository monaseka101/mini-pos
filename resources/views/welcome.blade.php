{{-- Loop through sales --}}

@foreach ($saleItem as $item)
    {{$item->product->name}}
@endforeach
