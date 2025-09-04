{{-- resources/views/components/product-grid.blade.php --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-4">
    @foreach($products as $product)
        <div class="border rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow bg-white">
            {{-- Product Image (if you have images) --}}
            @if($product->image)
                <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-32 object-cover rounded mb-2">
            @else
                <div class="w-full h-32 bg-gray-200 rounded mb-2 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            @endif

            {{-- Product Info --}}
            <h3 class="font-semibold text-sm mb-1 truncate" title="{{ $product->name }}">{{ $product->name }}</h3>
            <p class="text-lg font-bold text-green-600 mb-1">${{ number_format($product->price, 2) }}</p>
            <p class="text-xs text-gray-500 mb-3">Stock: {{ $product->stock }}</p>

            {{-- Add to Cart Button --}}
            <button
                type="button"
                onclick="addToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->price }}, {{ $product->stock }})"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-2 rounded transition-colors flex items-center justify-center gap-1"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add to Cart
            </button>
        </div>
    @endforeach
</div>

<script>
function addToCart(productId, productName, price, stock) {
    // Get the current form state
    const itemsInput = document.querySelector('[name="items"]');
    let currentItems = [];

    // Parse existing items if any
    if (itemsInput && itemsInput.value) {
        try {
            currentItems = JSON.parse(itemsInput.value);
        } catch (e) {
            currentItems = [];
        }
    }

    // Check if product already exists
    const existingIndex = currentItems.findIndex(item => item.product_id == productId);

    if (existingIndex !== -1) {
        // Increase quantity
        currentItems[existingIndex].qty = (currentItems[existingIndex].qty || 1) + 1;
    } else {
        // Add new item
        currentItems.push({
            product_id: productId,
            qty: 1,
            unit_price: price,
            discount: 0,
            available_stock: stock
        });
    }

    // Update the form field
    if (itemsInput) {
        itemsInput.value = JSON.stringify(currentItems);
        itemsInput.dispatchEvent(new Event('input'));
    }

    // Show success message
    showNotification(`${productName} added to cart!`);
}

function showNotification(message) {
    // Create a simple notification
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
