<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Sale;

class ReceiptController extends Controller
{
    public function print(Sale $sale)
    {
        $pdf = Pdf::loadView('receipt', compact('sale'));
        return $pdf->stream('receipt.pdf');
    }
    public function show(Sale $sale)
    {
        return view('receipt', compact('sale'));
    }
}
