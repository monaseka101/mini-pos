<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceiptController;
use App\Exports\CategoriesExport;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/receipt/print/{sale}', [ReceiptController::class, 'print'])->name('receipt.print');

//Route::get('/export/categories/csv', function () {
//    return Excel::download(new CategoriesExport, 'categories.csv', \Maatwebsite\Excel\Excel::CSV);
//})->name('export.categories.csv');
//
//Route::get('/export/categories/xlsx', function () {
//    return Excel::download(new CategoriesExport, 'categories.xlsx', \Maatwebsite\Excel\Excel::XLSX);
//})->name('export.categories.xlsx');
