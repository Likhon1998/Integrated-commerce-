<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusLog;

class OnlineOrderTrackingService
{
    public const FLOW_STATUSES = ['pending', 'processing', 'shipped', 'completed'];

    public function statusLabels(): array
    {
        return [
            'pending' => 'Order received',
            'processing' => 'Packing your order',
            'shipped' => 'Out for delivery',
            'completed' => 'Delivered',
            'cancelled' => 'Cancelled',
            'returned' => 'Returned',
            'refunded' => 'Refunded',
        ];
    }

    public function log(
        Order $order,
        string $status,
        ?string $note = null,
        ?string $courier = null,
        ?string $tracking = null,
        ?int $userId = null,
    ): OrderStatusLog {
        $label = $this->statusLabels()[$status] ?? ucfirst($status);

        return OrderStatusLog::create([
            'order_id' => $order->id,
            'status' => $status,
            'label' => $label,
            'note' => $note,
            'courier_name' => $courier,
            'tracking_number' => $tracking,
            'changed_by' => $userId,
        ]);
    }

    public function upsertLatestLog(
        Order $order,
        string $status,
        ?string $note = null,
        ?string $courier = null,
        ?string $tracking = null,
        ?int $userId = null,
    ): OrderStatusLog {
        $latest = $order->statusLogs()->latest('id')->first();

        if ($latest && $latest->status === $status) {
            $latest->update([
                'label' => $this->statusLabels()[$status] ?? ucfirst($status),
                'note' => $note ?? $latest->note,
                'courier_name' => $courier ?? $latest->courier_name,
                'tracking_number' => $tracking ?? $latest->tracking_number,
                'changed_by' => $userId ?? $latest->changed_by,
            ]);

            return $latest->fresh();
        }

        return $this->log($order, $status, $note, $courier, $tracking, $userId);
    }

    public function logInitialPlacement(Order $order): OrderStatusLog
    {
        return $this->log(
            $order,
            'pending',
            'We received your order. Our team will confirm and start packing soon.',
        );
    }

    /** Visual step tracker for customers. */
    public function customerTimeline(Order $order): array
    {
        $logs = $order->relationLoaded('statusLogs')
            ? $order->statusLogs->sortBy('created_at')->values()
            : $order->statusLogs()->orderBy('created_at')->get();

        $current = $order->status;
        $isTerminal = in_array($current, ['cancelled', 'returned', 'refunded'], true);

        if ($isTerminal) {
            $latest = $logs->last();

            return [
                [
                    'key' => $current,
                    'label' => $this->statusLabels()[$current] ?? ucfirst($current),
                    'done' => true,
                    'active' => true,
                    'at' => optional($latest)->created_at?->format('d M Y, h:i A'),
                    'note' => optional($latest)->note,
                ],
            ];
        }

        $statusRank = array_flip(self::FLOW_STATUSES);
        $currentRank = $statusRank[$current] ?? 0;
        $timeline = [];

        foreach (self::FLOW_STATUSES as $index => $step) {
            $stepLog = $this->latestLogForStatus($logs, $step);
            $timeline[] = [
                'key' => $step,
                'label' => $this->statusLabels()[$step],
                'done' => $index <= $currentRank,
                'active' => $step === $current,
                'at' => $stepLog?->created_at?->format('d M Y, h:i A')
                    ?? (($step === 'pending' && $index <= $currentRank) ? $order->created_at->format('d M Y, h:i A') : null),
                'note' => $stepLog?->note
                    ?? (($step === 'pending' && $index <= $currentRank) ? 'We received your order. Our team will confirm and start packing soon.' : null),
                'courier' => $step === 'shipped' ? ($stepLog?->courier_name ?: $order->shipping_courier) : null,
                'tracking' => $step === 'shipped' ? ($stepLog?->tracking_number ?: $order->shipping_tracking_no) : null,
            ];
        }

        return $timeline;
    }

    public function trackingPayload(Order $order): array
    {
        $order->loadMissing(['items.product', 'customer', 'statusLogs.changedBy']);

        $timeline = $this->customerTimeline($order);
        $activeStep = collect($timeline)->firstWhere('active', true) ?? $timeline[0] ?? null;

        return [
            'success' => true,
            'order_id' => $order->id,
            'invoice' => $order->invoice_no,
            'status' => $order->status,
            'status_label' => $this->statusLabels()[$order->status] ?? ucfirst($order->status),
            'message' => 'Order found!',
            'date' => asian_datetime($order->created_at, 'd M Y, h:i A'),
            'total' => number_format((float) $order->total_amount, 2),
            'delivery_address' => $order->customer?->address,
            'customer_name' => $order->customer?->name,
            'courier' => $order->shipping_courier,
            'tracking_number' => $order->shipping_tracking_no,
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->product?->name ?? 'Product',
                'qty' => (int) $item->quantity,
                'subtotal' => number_format((float) $item->subtotal, 2),
            ])->values()->all(),
            'timeline' => $timeline,
            'updates' => $order->statusLogs->sortByDesc('created_at')->values()->map(fn ($log) => [
                'status' => $log->status,
                'label' => $log->label,
                'note' => $log->note,
                'courier' => $log->courier_name,
                'tracking' => $log->tracking_number,
                'at' => $log->created_at->format('d M Y, h:i A'),
            ])->all(),
            'where_is_product' => $this->whereIsProductMessage($order, $activeStep),
        ];
    }

    protected function whereIsProductMessage(Order $order, ?array $activeStep): string
    {
        return match ($order->status) {
            'pending' => 'Your order is with our store team waiting to be confirmed.',
            'processing' => 'Your items are being picked and packed at our warehouse.',
            'shipped' => $order->shipping_courier
                ? "Your package is with {$order->shipping_courier}".($order->shipping_tracking_no ? " (tracking: {$order->shipping_tracking_no})" : '').' and on the way to you.'
                : 'Your package has left our store and is on the way to your address.',
            'completed' => 'Your order was delivered. Thank you for shopping with us!',
            'cancelled' => 'This order was cancelled and will not be delivered.',
            'returned' => 'This order was returned to our store.',
            'refunded' => 'This order was refunded.',
            default => 'We are processing your order.',
        };
    }

    protected function latestLogForStatus($logs, string $status): ?OrderStatusLog
    {
        return $logs->where('status', $status)->sortByDesc('created_at')->first();
    }
}
