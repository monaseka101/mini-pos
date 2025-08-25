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

### Dashboard widget charts ideas
Great! For a **POS (Point of Sale) Dashboard**, here's a curated list of **high-impact charts** you can use — grouped by type and priority — along with suggestions to avoid overwhelming the dashboard.

---

### 🔝 **Essential Charts (Top Priority — always visible)**

| Chart                                      | Type                           | Description                                                  |
| ------------------------------------------ | ------------------------------ | ------------------------------------------------------------ |
| **Total Sales (Last 30 Days)**             | Line / Bar                     | Shows daily revenue trend — highlights growth or drops.      |
| **Top Selling Products**                   | Horizontal Bar                 | Reveals best-performing items. Great for inventory planning. |
| **Sales by Category**                      | Pie / Doughnut                 | Visual breakdown of product types (e.g., food, drinks).      |
| **Monthly Sales Summary**                  | Bar                            | Compares sales across months (usually this year).            |
| **Revenue Today / This Week / This Month** | Stat Cards / Small Line Charts | Quick KPIs with totals.                                      |

---

### 📊 **Secondary Charts (Optional or Tabbed)**

| Chart                          | Type          | Description                                    |
| ------------------------------ | ------------- | ---------------------------------------------- |
| **Sales by Payment Type**      | Pie           | Breakdown of cash vs card vs digital payments. |
| **Sales by Location / Branch** | Bar / Map     | If your POS has multiple locations.            |
| **Customer Visit Trend**       | Line          | If you track customer visits or orders.        |
| **Hourly Sales Distribution**  | Heatmap / Bar | Shows busiest times of day.                    |
| **Discounts Given**            | Bar           | Track how discounts affect revenue.            |

---

### 🧠 **Smart Layout Tips**

* Use `StatOverviewWidget` at the top for:

  * Total Revenue Today
  * Total Orders Today
  * Average Sale Value
* Use `ChartWidget` below for trends.
* Group secondary charts in **tabs** or a collapsible section.
* Filter data using:

  * Year selector
  * Month selector
  * Product/category dropdown

---

### 🧱 Recommended Layout (Wireframe)

```
[ Stat Cards ]
--------------------------
| Line Chart (30d Trend) |
--------------------------
| Bar (Top Products)     | Pie (Sales by Category) |
--------------------------
[ Tabs: {Location} {Time} {Payment Method} ]
```

---

If you'd like, I can help write the code structure for these widgets using **Filament** and **Laravel Trend**, tailored to your schema. Just say the word!
