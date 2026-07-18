<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountEntry;
use App\Models\Counter;
use App\Models\CounterSession;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DashboardSummaryService
{
    public function __construct(
        protected AccountService $accounts,
        protected CounterSessionService $sessions,
    ) {}

    /**
     * Business summary for today.
     * $counterId = null means shop-wide (admin "All together").
     */
    public function todaySummary(int $shopId, ?int $counterId = null): array
    {
        $this->accounts->ensureShopAccounts($shopId);

        $today = Carbon::today();
        $dayStart = $today->copy()->startOfDay();
        $dayEnd = $today->copy()->endOfDay();

        $totalSales = $this->todaySales($shopId, $counterId, $today);
        $returns = $this->todayReturns($shopId, $counterId, $today);
        // Petty-cash expenses are shop-wide (not till-scoped)
        $expenses = $counterId === null
            ? $this->todayExpenses($shopId, $dayStart, $dayEnd)
            : 0.0;
        $netAmount = $totalSales - $returns - $expenses;

        $cash = $this->cashDrawerSummary($shopId, $counterId, $today);
        $pettyCash = $this->pettyCashBalance($shopId);

        return [
            'total_sales' => round($totalSales, 2),
            'returns' => round($returns, 2),
            'expenses' => round($expenses, 2),
            'net_amount' => round($netAmount, 2),
            'petty_cash' => round($pettyCash, 2),
            'opening_balance' => round($cash['opening'], 2),
            'cash_in' => round($cash['cash_in'], 2),
            'cash_out' => round($cash['cash_out'], 2),
            'closing_balance' => round($cash['closing'], 2),
            'session_status' => $cash['session_status'],
            'orders_count' => $cash['orders_count'],
            'has_session' => $cash['has_session'],
        ];
    }

    /** Empty summary used when staff has no counter assigned. */
    public function emptySummary(): array
    {
        return [
            'total_sales' => 0.0,
            'returns' => 0.0,
            'expenses' => 0.0,
            'net_amount' => 0.0,
            'petty_cash' => 0.0,
            'opening_balance' => 0.0,
            'cash_in' => 0.0,
            'cash_out' => 0.0,
            'closing_balance' => 0.0,
            'session_status' => 'none',
            'orders_count' => 0,
            'has_session' => false,
        ];
    }

    public function countersForShop(int $shopId): Collection
    {
        return Counter::where('shop_id', $shopId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /** Completed revenue orders (excludes exchanges / refunds / voids). */
    public function revenueOrdersQuery(int $shopId, ?int $counterId = null): Builder
    {
        return Order::where('shop_id', $shopId)
            ->where('status', 'completed')
            ->where(function ($q) {
                $q->where('is_exchange_receipt', false)
                    ->orWhereNull('is_exchange_receipt');
            })
            ->when($counterId !== null, fn ($q) => $q->where('counter_id', $counterId));
    }

    protected function todaySales(int $shopId, ?int $counterId, Carbon $today): float
    {
        return (float) $this->revenueOrdersQuery($shopId, $counterId)
            ->whereDate('created_at', $today)
            ->sum('total_amount');
    }

    protected function todayReturns(int $shopId, ?int $counterId, Carbon $today): float
    {
        return (float) Order::where('shop_id', $shopId)
            ->whereDate('updated_at', $today)
            ->whereIn('status', ['refunded', 'returned'])
            ->when($counterId !== null, fn ($q) => $q->where('counter_id', $counterId))
            ->sum('total_amount');
    }

    protected function todayExpenses(int $shopId, Carbon $dayStart, Carbon $dayEnd): float
    {
        $expense = Account::where('shop_id', $shopId)->where('code', 'EXPENSE')->first();
        if (! $expense) {
            return 0.0;
        }

        return (float) AccountEntry::query()
            ->where('account_id', $expense->id)
            ->where('entry_type', 'debit')
            ->whereHas('transaction', function ($q) use ($shopId, $dayStart, $dayEnd) {
                $q->where('shop_id', $shopId)
                    ->where('type', 'petty_cash')
                    ->whereBetween('transaction_date', [$dayStart->toDateString(), $dayEnd->toDateString()]);
            })
            ->sum('amount');
    }

    protected function pettyCashBalance(int $shopId): float
    {
        try {
            $petty = $this->accounts->getAccount($shopId, 'PETTY');

            return $this->accounts->accountBalance($petty);
        } catch (\Throwable) {
            return 0.0;
        }
    }

    protected function cashDrawerSummary(int $shopId, ?int $counterId, Carbon $today): array
    {
        $ledgerRows = $this->accounts->dailySummary($shopId, $today, $counterId);

        $openingLedger = 0.0;
        $salesIn = 0.0;
        $transfersIn = 0.0;
        $transfersOut = 0.0;
        $refundsOut = 0.0;
        $closingLedger = 0.0;

        foreach ($ledgerRows as $row) {
            $openingLedger += (float) $row['opening'];
            $salesIn += (float) $row['sales_in'];
            $transfersIn += (float) $row['transfers_in'];
            $transfersOut += (float) $row['transfers_out'];
            $refundsOut += (float) $row['refunds_out'];
            $closingLedger += (float) $row['closing'];
        }

        // Latest session per counter for today (avoid double-counting reopened tills)
        $sessions = CounterSession::where('shop_id', $shopId)
            ->whereDate('opened_at', $today)
            ->when($counterId !== null, fn ($q) => $q->where('counter_id', $counterId))
            ->orderByDesc('opened_at')
            ->get()
            ->unique('counter_id')
            ->values();

        $hasSession = $sessions->isNotEmpty();
        $anyOpen = $sessions->contains(fn ($s) => $s->status === 'open');
        $allClosed = $hasSession && $sessions->every(fn ($s) => $s->status === 'closed');

        $openingSession = (float) $sessions->sum('opening_cash');
        $closingSession = 0.0;
        $ordersCount = 0;

        foreach ($sessions as $session) {
            if ($session->status === 'closed' && $session->closing_cash !== null) {
                $closingSession += (float) $session->closing_cash;
                $ordersCount += (int) ($session->order_count ?: 0);
            } else {
                $live = $this->sessions->liveStats($session);
                $closingSession += $this->sessions->expectedCash($session, $live);
                $ordersCount += (int) ($live['order_count'] ?? 0);
            }
        }

        // Prefer declared till float when a session exists; otherwise use ledger cash-on-hand
        $opening = $hasSession ? $openingSession : $openingLedger;
        $closing = $hasSession ? $closingSession : $closingLedger;

        $sessionStatus = 'none';
        if ($anyOpen) {
            $sessionStatus = 'open';
        } elseif ($allClosed) {
            $sessionStatus = 'closed';
        }

        return [
            'opening' => $opening,
            'cash_in' => $salesIn + $transfersIn,
            'cash_out' => $transfersOut + $refundsOut,
            'closing' => $closing,
            'session_status' => $sessionStatus,
            'orders_count' => $ordersCount,
            'has_session' => $hasSession,
        ];
    }
}
