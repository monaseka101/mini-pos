<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <head>

        <title>Receipt</title>
        <style>
            /* Styles for Print and Screen */
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
            }

            .receipt {
                width: 190mm;
                height: 297mm;
                /* Standard receipt width */
                margin: 10px auto;
                padding: 10px;
                border: 1px solid #ffffff;
            }

            h2 {
                font-size: 1.5em;
                text-align: left;
                margin-bottom: 2px;
            }

            p {
                font-size: 1em;
            }

            .store-info {
                margin-bottom: 1px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                border: 1px solid #eee;
                padding: 5px;
                text-align: left;
            }

            th {
                background-color: #ffffff;
            }

            .total {
                font-weight: bold;
                text-align: right;
            }

            .signature-container {
                display: flex;
                justify-content: space-between;
                margin-top: 50px;
                width: 100%;
                align: center;
                gap: 20px;
                /* optional: adds spacing between columns */
            }

            .signature-section {
                width: 45%;
                /* now they can sit side-by-side */
                text-align: center;
            }


            .signature_buy,
            .signature_sell {
                margin-top: 20px;
                margin-bottom: 50px;
            }

            .receipt-details-table {
                font-size: 0.8em;
                font-style: Arial;
                margin-top: 5px;
            }


            .receipt-details {
                font-size: 0.8em;
                font-style: italic;
                margin-top: 5px;
            }

            @media print {
                .receipt {
                    width: 80mm;
                    /* Ensure correct print width */
                    margin: 0;
                    padding: 5px;
                    border: none;
                }

                body {
                    font-size: 0.5em;
                }

                /* Reduce font size for printing */
            }
        </style>
    </head>
</head>

<body onload="window.print()">
    <div class="receipt">
        <div style="text-align: center;">
            <h1 text-align: center;>Invoice</h1>
        </div>
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <img src="{{ public_path('storage\default\bg.png') }}" alt="Logo" width="150" align="right">
            <h2 style="margin: 0;">TL Gold Computer</h2>
        </div>

        <table class="receipt-details", style="width: 70%; margin-top: 20px; border-collapse: collapse; border: none;">
            <tr>
                <td
                    style="text-align: left; width: 50%; border: none; padding: 0;margin-left: 30px;  vertical-align: top;">
                    <p>Sell to:{{ $sale->customer->name }}</p>
                    <p>Phone: {{ $sale->customer->phone ?? 'N/A' }}</p>
                    <p>Address: {{ $sale->customer->address ?? 'N/A' }} </p>
                </td>
                <td
                    style="text-align: left; width: 50%; border: none; padding: 0;margin-left: 90px;  vertical-align: top;">
                    <p>Invoice No: 00{{ $sale->id }}</p>
                    <p>Invoice Date: {{ \Carbon\Carbon::parse($sale->sale_date)->format('F d, Y') }}</p>

                </td>
            </tr>
        </table>
        <table border="1" cellspacing="0" cellpadding="6" width="100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th style="white-space: nowrap;">Unit Price</th>
                    <th>Discount</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody class="receipt-details-table">
                @php $subTotal = 0; @endphp
                @foreach ($sale->items ?? [] as $item)
                    @php
                        $amount = $item->qty * $item->unit_price;
                        $discount = $item->discount_amount ?? 0;
                        $subTotal += $amount;
                    @endphp
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{!! $item->product->description ?? '-' !!}</td>
                        <td>{{ $item->qty }}</td>
                        <td>${{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->qty > 0 ? $item->discount / $item->qty : 0) }}%</td>
                        <td>${{ number_format($item->subTotal() - $item->discount_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <br>
        <p class="total">Total Discount:.......................${{ number_format($sale->discount_amount ?? 0, 2) }}
        </p>
        <p class="total">Sub Total:.....................${{ number_format($subTotal - $sale->discount_amount, 2) }}
        </p>
        <!--<p class="total">Paid Amount:...................${{ number_format($sale->paid_amount ?? 0, 2) }}</p>
        <p class="total">Discount:.......................${{ number_format($sale->discount ?? 0, 2) }}</p>
        <p class="total">Amount Due:.............
            ${{ number_format($subTotal - ($sale->paid_amount ?? 0) - ($sale->discount ?? 0), 2) }}</p>-->
        <p class="total">________________</p>
        <div class = "receipt-details">
            <p> <strong>Term of Conditions: </strong><br>
                1.Customers must thoroughly check the products before taking them out of the store.
                Our store is not responsible for any damages, breakages, or missing parts once the products have left
                our premises.<br>
                2.All purchased products cannot be refunded or exchanged.<br>
                3.For laptops, desktops, and other products, the company is not responsible for damages caused by fire,
                water exposure, missing warranty seals, physical damage, or deformation of the original product.<br>
                4.Certain parts are not covered by warranty, such as power supplies, adapters, batteries, speakers, dead
                pixels, or vertical screen lines.<br>
                5.The warranty does not apply if the customer irons or tapes over the product in a way that clearly
                causes
                issues.
            </p>
        </div>

        <table style="width: 100%; margin-top: 50px; border-collapse: collapse; border: none;">
            <tr>
                <td style="text-align: left; width: 50%; border: none; padding: 0;margin-left: 30px;">
                    <p style="margin-bottom: 10px; margin-left: 30px;">Customer Signature</p>
                    <p style="margin-top: 50;">&nbsp;&nbsp;&nbsp;&nbsp;----------------------------------</p>
                </td>
                <td style="text-align: right; width: 50%; border: none; padding: 0;margin-right: 30px;">
                    <p style="margin-bottom: 10px; margin-right: 50px;">Seller Signature</p>
                    <p style="margin-top: 50;">----------------------------------&nbsp;&nbsp;&nbsp;&nbsp;</p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
