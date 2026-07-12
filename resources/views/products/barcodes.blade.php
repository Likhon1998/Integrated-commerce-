<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barcodes</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f3f4f6; }
        .toolbar { max-width: 900px; margin: 0 auto 20px; display: flex; gap: 10px; }
        .toolbar button, .toolbar a { padding: 10px 16px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; text-decoration: none; }
        .btn-print { background: #4f46e5; color: white; }
        .btn-back { background: white; color: #374151; border: 1px solid #d1d5db; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; max-width: 900px; margin: 0 auto; }
        .label { background: white; border: 1px dashed #d1d5db; border-radius: 8px; padding: 16px; text-align: center; page-break-inside: avoid; }
        .label h3 { margin: 0 0 8px; font-size: 14px; }
        .label p { margin: 4px 0 0; font-size: 12px; color: #6b7280; }
        @media print {
            body { background: white; padding: 0; }
            .toolbar { display: none; }
            .grid { max-width: none; gap: 8px; }
            .label { border: 1px solid #000; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn-print" onclick="window.print()">Print Barcodes</button>
        <a class="btn-back" href="{{ route('products.index') }}">Back to Products</a>
    </div>

    @if($products->isEmpty())
        <p style="text-align:center;color:#6b7280;">No products found. Add products first.</p>
    @else
        <div class="grid">
            @foreach($products as $product)
                <div class="label">
                    <h3>{{ $product->name }}</h3>
                    <svg class="barcode" jsbarcode-format="CODE128" jsbarcode-value="{{ $product->barcode }}" jsbarcode-height="50" jsbarcode-displayValue="true" jsbarcode-fontSize="12"></svg>
                    <p>৳{{ number_format($product->selling_price, 2) }}</p>
                </div>
            @endforeach
        </div>
    @endif

    <script>JsBarcode('.barcode').init();</script>
</body>
</html>
