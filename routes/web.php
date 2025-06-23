<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

Artisan::command('test', function () {

    $year = 2025; // Change this to the desired year

    $data = DB::table('sale_items')
        ->join('products', 'sale_items.product_id', '=', 'products.id')
        ->join('brands', 'products.brand_id', '=', 'brands.id')
        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->whereYear('sales.sale_date', $year)
        ->select('brands.name as brand', DB::raw('SUM(sale_items.qty) as total_qty'))
        ->groupBy('brands.id', 'brands.name')
        ->orderByDesc('total_qty')
        ->get();

    dd($data);
});
