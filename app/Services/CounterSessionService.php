<?php

namespace App\Services;

use App\Models\Counter;
use App\Models\CounterSession;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class CounterSessionService
{
    public function __construct(protected AccountService $accounts) {}

    public function openSession(Counter $counter, float $openingCash, ?string $notes = null, ?int $userId = null): CounterSession
    {
        if ($this->currentOpen($counter)) {
            throw new InvalidArgumentException("{$counter->name} already has an open cash session. Close it first.");
        }

        $userId ??= Auth::id();
        $openingCash = round(max(0, $openingCash), 2);

        $session = CounterSession::create([
            'shop_id' => $counter->shop_id,
            'counter_id' => $counter->id,
            'opened_by' => $userId,
            'opened_at' => now(),
            'opening_cash' => $openingCash,
            'status' => 'open',
            'notes' => $notes,
        ]);

        return $session;
    }

    public function closeSession(CounterSession $session, float $closingCash, ?string $notes = null, ?int $userId = null): CounterSession
    {
        if (! $session->isOpen()) {
            throw new InvalidArgumentException('This cash session is already closed.');
        }

        $userId ??= Auth::id();
        $closingCash = round(max(0, $closingCash), 2);
        $stats = $this->sessionStats($session, now());

        $expected = round(
            (float) $session->opening_cash + $stats['cash_sales'] - $stats['cash_refunds'],
            2
        );
        $variance = round($closingCash - $expected, 2);

        $session->update([
            'closed_by' => $userId,
            'closed_at' => now(),
            'closing_cash' => $closingCash,
            'expected_cash' => $expected,
            'variance' => $variance,
            'order_count' => $stats['order_count'],
            'total_sales' => $stats['total_sales'],
            'cash_sales' => $stats['cash_sales'],
            'card_sales' => $stats['card_sales'],
            'mobile_sales' => $stats['mobile_sales'],
            'cash_refunds' => $stats['cash_refunds'],
            'status' => 'closed',
            'notes' => trim(($session->notes ? $session->notes . "\n" : '') . ($notes ?? '')),
        ]);

        return $session->fresh(['counter', 'opener', 'closer']);
    }

    public function currentOpen(Counter $counter): ?CounterSession
    {
        return CounterSession::where('counter_id', $counter->id)
            ->open()
            ->latest('opened_at')
            ->first();
    }

    public function liveStats(CounterSession $session): array
    {
        return $this->sessionStats($session, now());
    }

    public function expectedCash(CounterSession $session, ?array $stats = null): float
    {
        $stats ??= $this->liveStats($session);

        return round((float) $session->opening_cash + $stats['cash_sales'] - $stats['cash_refunds'], 2);
    }

    protected function sessionStats(CounterSession $session, Carbon $until): array
    {
        $from = $session->opened_at;
        $orders = Order::where('shop_id', $session->shop_id)
            ->where('counter_id', $session->counter_id)
            ->whereBetween('created_at', [$from, $until])
            ->get();

        $completed = $orders->where('status', 'completed');
        $refunded = $orders->whereIn('status', ['refunded', 'returned']);

        $cashSales = 0.0;
        $cardSales = 0.0;
        $mobileSales = 0.0;
        $otherSales = 0.0;

        foreach ($completed as $order) {
            $amount = (float) $order->total_amount;
            $method = strtolower((string) $order->payment_method);

            if ($this->isPureCash($method)) {
                $cashSales += $amount;
            } elseif (str_contains($method, 'card')) {
                $cardSales += $amount;
            } elseif (str_contains($method, 'bkash') || str_contains($method, 'nagad') || str_contains($method, 'mobile')) {
                $mobileSales += $amount;
            } elseif (str_contains($method, 'cash')) {
                // Split tender (Cash + Card etc.) — count full amount toward cash for drawer safety,
                // store remainder isn't tracked on Order; treat mixed as cash-affecting.
                $cashSales += $amount;
            } else {
                $otherSales += $amount;
            }
        }

        $cashRefunds = $refunded
            ->filter(fn ($o) => $this->isPureCash(strtolower((string) $o->payment_method)) || str_contains(strtolower((string) $o->payment_method), 'cash'))
            ->sum(fn ($o) => (float) $o->total_amount);

        return [
            'order_count' => $completed->count(),
            'total_sales' => round((float) $completed->sum('total_amount'), 2),
            'cash_sales' => round($cashSales, 2),
            'card_sales' => round($cardSales, 2),
            'mobile_sales' => round($mobileSales, 2),
            'other_sales' => round($otherSales, 2),
            'cash_refunds' => round((float) $cashRefunds, 2),
        ];
    }

    protected function isPureCash(string $method): bool
    {
        $method = trim($method);

        return $method === 'cash' || $method === '';
    }
}
