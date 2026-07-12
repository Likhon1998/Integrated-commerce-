<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $order->invoice_no }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #000; margin: 0; padding: 0; background-color: #fff; }
        .ticket { width: 80mm; max-width: 80mm; margin: 0 auto; padding: 10px; }
        .text-center { text-align: center; } .text-right { text-align: right; } .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 0; vertical-align: top; }
        .section-title { text-align: center; font-weight: bold; font-size: 13px; margin: 8px 0 4px 0; border-bottom: 1px solid #000; display: inline-block; padding: 0 4px; }
        .warning-box { border: 2px solid #000; padding: 10px; text-align: center; margin-top: 15px; margin-bottom: 15px; border-radius: 4px; }
        .address-box { margin: 4px 0; padding: 4px; border: 1px dashed #ccc; border-radius: 2px; }
    </style>
</head>
<body onload="window.print()">
    <div class="ticket">
        
        <div class="text-center">
            <h2 style="margin: 0; font-size: 18px;">{{ Auth::user()->shop->name ?? 'Nexa POS' }}</h2>
            <p style="margin: 2px 0;">Invoice: {{ $order->invoice_no }}</p>
            <p style="margin: 2px 0;">Date: {{ $order->created_at->format('d M Y, h:i A') }}</p>
            <p style="margin: 2px 0;">Sales Channel: {{ $order->counter_id ? ($order->user->name ?? 'Cashier') : 'Online Web Store' }}</p>
            
            @if($order->customer)
                <div class="divider"></div>
                <div class="text-left">
                    <p style="margin: 2px 0;"><span class="bold">Customer:</span> {{ $order->customer->name }}</p>
                    <p style="margin: 2px 0;"><span class="bold">Phone:</span> {{ $order->customer->phone }}</p>
                    @if($order->customer->address)
                        <div class="address-box">
                            <span class="bold">Delivery Address:</span><br>
                            <span style="word-wrap: break-word;">{{ $order->customer->address }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>
        
        <div class="divider"></div>

        @if(in_array($order->status, ['refunded', 'cancelled', 'returned']))
            <div class="text-center bold" style="font-size: 16px; text-transform: uppercase; border: 2px dashed #000; padding: 5px; margin-bottom: 10px;">
                *** VOID / {{ strtoupper($order->status) }} ***
            </div>
        @endif

        @if($order->is_exchange_receipt)
            <div class="text-center bold" style="font-size: 15px; text-transform: uppercase;">*** EXCHANGE RECEIPT ***</div>
            <div class="divider"></div>
            <div class="text-center"><span class="section-title">[ RETURNED ITEMS ]</span></div>
            <table>
                <tr>
                    <td class="text-left bold">{{ $returnProduct ? Str::limit($returnProduct->name, 18) : 'Unknown Item' }}</td>
                    <td class="text-center bold">x{{ $order->return_qty }}</td>
                    <td class="text-right bold">- ৳{{ number_format($order->exchange_credit, 2) }}</td>
                </tr>
            </table>
            <div class="divider"></div>
            <div class="text-center"><span class="section-title">[ NEW ITEMS ]</span></div>
        @endif
        
        <table>
            <thead>
                <tr>
                    <th class="text-left border-bottom" style="border-bottom: 1px solid #000;">Item</th>
                    <th class="text-center border-bottom" style="border-bottom: 1px solid #000;">Qty</th>
                    <th class="text-right border-bottom" style="border-bottom: 1px solid #000;">Price</th>
                    <th class="text-right border-bottom" style="border-bottom: 1px solid #000;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td class="text-left">{{ Str::limit($item->product->name, 14) }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="divider"></div>
        
        <table>
            @if($order->is_exchange_receipt)
                <tr>
                    <td>Items Total:</td>
                    <td class="text-right">৳{{ number_format($order->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding-bottom: 6px; border-bottom: 1px dashed #000;">Return Credit:</td>
                    <td class="text-right" style="padding-bottom: 6px; border-bottom: 1px dashed #000;">- ৳{{ number_format($order->exchange_credit, 2) }}</td>
                </tr>
                <tr>
                    <td class="bold" style="padding-top: 6px;">Net Payable:</td>
                    <td class="text-right bold" style="padding-top: 6px;">৳{{ number_format(max(0, $order->total_amount - $order->exchange_credit), 2) }}</td>
                </tr>
            @else
                @if(($order->delivery_charge ?? 0) > 0)
                    <tr>
                        <td class="bold">Subtotal (Items):</td>
                        <td class="text-right bold">৳{{ number_format($order->total_amount - $order->delivery_charge, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="bold">Delivery Charge:</td>
                        <td class="text-right bold">+ ৳{{ number_format($order->delivery_charge, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="bold border-top" style="border-top: 1px dashed #000; padding-top:4px;">GRAND TOTAL:</td>
                        <td class="text-right bold border-top" style="border-top: 1px dashed #000; padding-top:4px; font-size: 14px;">৳{{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                @else
                    <tr>
                        <td class="bold">GRAND TOTAL:</td>
                        <td class="text-right bold" style="font-size: 14px;">৳{{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                @endif
            @endif

            <tr>
                <td style="padding-top: 6px;">Paid ({{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}):</td>
                
                <td class="text-right" style="padding-top: 6px;">৳{{ number_format(in_array($order->status, ['refunded', 'cancelled', 'returned']) ? 0 : $order->paid_amount, 2) }}</td>
            </tr>
            @if(($order->change_amount ?? 0) > 0)
            <tr>
                <td class="bold">Change Due:</td>
                <td class="text-right bold">৳{{ number_format($order->change_amount, 2) }}</td>
            </tr>
            @endif
        </table>
        
        @if($order->is_exchange_receipt)
        <div class="warning-box">
            <div class="bold" style="font-size: 15px; margin-bottom: 5px;">*** FINAL SALE ***</div>
            <div style="font-size: 11px; line-height: 1.4;">
                This transaction includes exchanged items.<br>
                The items listed above are strictly<br>
                <span class="bold" style="font-size: 13px; text-decoration: underline;">NON-RETURNABLE</span>
            </div>
        </div>
        @endif
        
        <div class="divider"></div>
        
        <div class="text-center">
            <p style="margin-top: 10px; font-weight: bold;">Thank you for your business!</p>
        </div>
        
    </div>
</body>
</html>