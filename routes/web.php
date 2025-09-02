<?php

use App\Models\Product;
use App\Models\ProductImport;
use App\Models\ProductImportItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Get;
use Flowframe\Trend\Trend;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
<<<<<<< HEAD
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
=======
use App\Http\Controllers\ReceiptController;
use App\Exports\CategoriesExport;
use Maatwebsite\Excel\Facades\Excel;
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73

use Flowframe\Trend\TrendValue;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use function Livewire\of;

// Route::get('/', function () {
//     return view('welcome');
// });

Artisan::command('test', function () {
    // What is Laravel Trend ?
    // What does it use for?

    // $year = 2025; // Change this dynamically if needed

    // $data = Trend::query(
    //     SaleItem::query()
    //         ->selectRaw('sale_items.*, sale_items.qty * sale_items.unit_price as total')
    //         ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
    //         ->whereYear('sales.created_at', now())
    // )
    // ->dateColumn('sate_date')
    // ->between(
    //     start: now()->setYear($year)->startOfYear(),
    //     end: now()->setYear($year)->endOfYear(),
    // )
    // ->perMonth()
    // ->sum('total');

    $year = 2025;

    $data = DB::table(DB::raw('(SELECT 1 as month UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8
        UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) as months'))
        ->leftJoin('sales', function ($join) use ($year) {
            $join->whereRaw('MONTH(sales.sale_date) = months.month')
                ->whereYear('sales.sale_date', '=', $year);
        })
        ->leftJoin('sale_items', 'sale_items.sale_id', '=', 'sales.id')
        ->select(
            'months.month',
            DB::raw('COALESCE(SUM(sale_items.qty * sale_items.unit_price), 0) as total_sales')
        )
        ->groupBy('months.month')
        ->orderBy('months.month')
        ->get();

    dd($data);
});

Artisan::command('exp', function () {

    // Aggregate function you perform the operations on it individually and as group (Group By)

    $result = Sale::query()
        ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
        ->select('sales.sale_date', DB::raw('COUNT(sale_items.qty) as total_sale'))
        ->groupBy('sales.sale_date')
        ->get();

    dd($result);
});

Route::get('/data', function () {
    // The most sale products

    $result = Sale::query()
        ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
        ->join('customers', 'sales.customer_id', '=', 'customers.id')
        ->select('customers.name', DB::raw('COUNT(DISTINCT sales.id) as total_purchases'))
        ->where('sale_items.qty', '>', 2)
        ->groupBy('sales.customer_id', 'customers.name')
        ->orderBy('total_purchases', 'desc')
        ->get();
});



Artisan::command('play', function () {
    $sql =  SaleItem::query()
        ->select(
            'P.name',
            \DB::raw('SUM(sale_items.qty) as total_qty'),
            \DB::raw('SUM((sale_items.unit_price * sale_items.qty) * (1 - COALESCE(sale_items.discount, 0)/100)) as total_amount')
        )
        ->join('products as P', 'sale_items.product_id', '=', 'P.id')
        ->groupBy('p.id');
    foreach(collect($sql->get()) as $sale) {
        dump($sale);
    }

});


Route::get('/dumb', function () {
    $result = Trend::query(
        Sale::query()->join('sale_items', 'sale.id', '=', 'sale_items.sale_id')
    );
    $selectedYear = now()->year;
    $data = Trend::query(
        Sale::query()
            ->join('sale_items as SI', 'sales.id', '=', 'SI.sale_id')
    )
        ->dateColumn('sales.sale_date')
        ->between(
            start: Carbon::createFromDate($selectedYear, 1, 1)->startOfYear(),
            end: Carbon::createFromDate($selectedYear, 12, 31)->endOfYear(),
        )
        ->perMonth()
        ->sum('SI.qty');
    // dd($data);

    $query =  Sale::query()->join(
        'sale_items as SI',
        'sales.id',
        '=',
        'SI.sale_id'
    )->select('SI.qty', 'SI.unit_price');
    dd($query->get());
});
Route::get('/receipt/print/{sale}', [ReceiptController::class, 'print'])->name('receipt.print');

//Route::get('/export/categories/csv', function () {
//    return Excel::download(new CategoriesExport, 'categories.csv', \Maatwebsite\Excel\Excel::CSV);
//})->name('export.categories.csv');
//
//Route::get('/export/categories/xlsx', function () {
//    return Excel::download(new CategoriesExport, 'categories.xlsx', \Maatwebsite\Excel\Excel::XLSX);
//})->name('export.categories.xlsx');
