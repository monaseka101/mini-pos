A product import can have multiple product items as collections with repeater field input.

After the import is completed I have to increase the stock in product model.

Whenever I perform Edit items in particular import some fields will be changed like qty of product sometimes some product
need to remove and new product then I want increase the stock in the product if that product has been remove and reduce
the stock if the qty of that product increase.

Here is my relationship between sale and sale_items
I want to write query to fetch the total sale in a year group it by each month

Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->nullOnDelete();
            $table->date('sale_date')->nullable();
            $table->text('note')->nullable();
            // $table->boolean('active')->default(true);
            $table->timestamps();
        });


public function up(): void
{
    Schema::create('sale_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
        $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
        $table->integer('qty');
        $table->decimal('unit_price', 10, 2);
        $table->unsignedTinyInteger('discount')->nullable();
        $table->timestamps();
    });
}
