<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountEntry;
use App\Models\AccountTransaction;
use App\Models\Counter;
use App\Models\CounterSession;
use App\Models\Order;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountService
{
    public const SYSTEM_ACCOUNTS = [
        ['code' => 'EQUITY', 'name' => "Owner's Capital", 'type' => 'equity'],
        ['code' => 'REVENUE', 'name' => 'Sales Revenue', 'type' => 'income'],
        ['code' => 'COGS', 'name' => 'Cost of Goods Sold', 'type' => 'expense'],
        ['code' => 'INVENTORY', 'name' => 'Inventory Asset', 'type' => 'asset'],
        ['code' => 'AP', 'name' => 'Accounts Payable', 'type' => 'liability'],
        ['code' => 'PETTY', 'name' => 'Petty Cash', 'type' => 'asset'],
        ['code' => 'WEB-COD', 'name' => 'Online COD Receivable', 'type' => 'asset'],
        ['code' => 'WEB-CASH', 'name' => 'Online Settlement Cash', 'type' => 'asset'],
        ['code' => 'CARD', 'name' => 'Card Payments', 'type' => 'asset'],
        ['code' => 'BKASH', 'name' => 'Mobile Wallet (bKash)', 'type' => 'asset'],
        ['code' => 'EXPENSE', 'name' => 'General Expenses', 'type' => 'expense'],
    ];

    public function dateRange(Request $request): array
    {
        if ($request->boolean('all_time')) {
            $shopId = auth()->user()->shop_id;
            $first = AccountTransaction::where('shop_id', $shopId)->min('transaction_date');

            return [
                $first ? Carbon::parse($first)->startOfDay() : now()->startOfMonth(),
                now()->endOfDay(),
            ];
        }

        $start = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->subDays(29)->startOfDay();

        $end = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        return [$start, $end];
    }

    public function ensureShopAccounts(int $shopId): void
    {
        foreach (self::SYSTEM_ACCOUNTS as $def) {
            Account::firstOrCreate(
                ['shop_id' => $shopId, 'code' => $def['code']],
                [
                    'name' => $def['name'],
                    'type' => $def['type'],
                    'is_system' => true,
                    'is_active' => true,
                ]
            );
        }

        Counter::where('shop_id', $shopId)->each(fn (Counter $c) => $this->ensureCounterCashAccount($c));
    }

    public function ensureCounterCashAccount(Counter $counter): Account
    {
        return Account::firstOrCreate(
            ['shop_id' => $counter->shop_id, 'code' => 'CASH-' . $counter->id],
            [
                'counter_id' => $counter->id,
                'name' => 'Cash at ' . $counter->name,
                'type' => 'asset',
                'is_system' => true,
                'is_active' => true,
            ]
        );
    }

    public function getAccount(int $shopId, string $code): Account
    {
        return Account::where('shop_id', $shopId)->where('code', $code)->firstOrFail();
    }

    public function accountBalance(Account $account, ?Carbon $asOf = null): float
    {
        $query = AccountEntry::where('account_id', $account->id);

        if ($asOf) {
            $query->whereHas('transaction', fn ($q) => $q->where('transaction_date', '<=', $asOf->toDateString()));
        }

        $debits = (float) (clone $query)->where('entry_type', 'debit')->sum('amount');
        $credits = (float) (clone $query)->where('entry_type', 'credit')->sum('amount');

        return $this->resolveBalance($account, $debits, $credits);
    }

    /**
     * @param  iterable<Account>  $accounts
     * @return array<int, float>
     */
    public function accountBalances(iterable $accounts, ?Carbon $asOf = null): array
    {
        $accounts = collect($accounts);

        if ($accounts->isEmpty()) {
            return [];
        }

        $query = AccountEntry::query()
            ->whereIn('account_id', $accounts->pluck('id'))
            ->selectRaw('account_id, entry_type, SUM(amount) as total')
            ->groupBy('account_id', 'entry_type');

        if ($asOf) {
            $query->whereHas('transaction', fn ($q) => $q->where('transaction_date', '<=', $asOf->toDateString()));
        }

        $totals = $query->get()->groupBy('account_id');
        $balances = [];

        foreach ($accounts as $account) {
            $accountTotals = $totals->get($account->id, collect());
            $debits = (float) $accountTotals->where('entry_type', 'debit')->sum('total');
            $credits = (float) $accountTotals->where('entry_type', 'credit')->sum('total');
            $balances[$account->id] = $this->resolveBalance($account, $debits, $credits);
        }

        return $balances;
    }

    protected function resolveBalance(Account $account, float $debits, float $credits): float
    {
        $opening = (float) $account->opening_balance;

        return match ($account->type) {
            'asset', 'expense' => $opening + $debits - $credits,
            'liability', 'equity', 'income' => $opening + $credits - $debits,
            default => $opening + $credits - $debits,
        };
    }

    public function postOrderSale(Order $order): void
    {
        $order->loadMissing('items.product');

        // Web storefront orders only — POS invoices (even without counter_id) must not post as COD receivable
        if ($order->isOnlineOrder()) {
            $this->postWebSale($order);
        } else {
            $this->postPosSale($order);
        }
    }

    public function postPosSale(Order $order): void
    {
        if ($this->transactionExists($order->shop_id, 'sale', Order::class, $order->id)) {
            return;
        }

        $this->ensureShopAccounts($order->shop_id);
        $order->loadMissing('counter');
        if ($order->counter_id) {
            $this->ensureCounterCashAccount($order->counter);
        }

        $revenue = $this->getAccount($order->shop_id, 'REVENUE');
        $cogs = $this->calculateCogs($order);
        $netAmount = $order->netPayable();

        $cash = max(0, (float) ($order->cash_paid ?? 0));
        $card = max(0, (float) ($order->card_paid ?? 0));
        $mobile = max(0, (float) ($order->mobile_paid ?? 0));
        $hasBreakdown = $order->cash_paid !== null || $order->card_paid !== null || $order->mobile_paid !== null;

        $lines = [];

        if ($hasBreakdown) {
            $allocated = round($cash + $card + $mobile, 2);
            // If tender breakdown is short/long vs net, keep cash as residual for till accuracy
            if ($allocated <= 0 && $netAmount > 0) {
                $cash = $netAmount;
            } elseif (abs($allocated - $netAmount) > 0.05 && $cash > 0) {
                $cash = max(0, round($netAmount - $card - $mobile, 2));
            } elseif (abs($allocated - $netAmount) > 0.05) {
                $cash = max(0, round($netAmount - $card - $mobile, 2));
            }

            if ($cash > 0) {
                $cashAccount = $order->counter_id
                    ? $this->ensureCounterCashAccount($order->counter)
                    : $this->getAccount($order->shop_id, 'WEB-CASH');
                $lines[] = ['account' => $cashAccount, 'debit' => $cash, 'credit' => 0, 'counter_id' => $order->counter_id];
            }
            if ($card > 0) {
                $lines[] = ['account' => $this->getAccount($order->shop_id, 'CARD'), 'debit' => $card, 'credit' => 0, 'counter_id' => $order->counter_id];
            }
            if ($mobile > 0) {
                $lines[] = ['account' => $this->getAccount($order->shop_id, 'BKASH'), 'debit' => $mobile, 'credit' => 0, 'counter_id' => $order->counter_id];
            }

            $debited = round($cash + $card + $mobile, 2);
            if ($debited + 0.009 < $netAmount) {
                $gap = round($netAmount - $debited, 2);
                $cashAccount = $order->counter_id
                    ? $this->ensureCounterCashAccount($order->counter)
                    : $this->getAccount($order->shop_id, 'WEB-CASH');
                $lines[] = ['account' => $cashAccount, 'debit' => $gap, 'credit' => 0, 'counter_id' => $order->counter_id];
            }
        } else {
            $paymentAccount = $this->resolvePaymentAccount($order);
            $lines[] = ['account' => $paymentAccount, 'debit' => $netAmount, 'credit' => 0, 'counter_id' => $order->counter_id];
        }

        $lines[] = ['account' => $revenue, 'debit' => 0, 'credit' => $netAmount, 'counter_id' => $order->counter_id];

        if ($cogs > 0) {
            $lines[] = ['account' => $this->getAccount($order->shop_id, 'COGS'), 'debit' => $cogs, 'credit' => 0, 'counter_id' => $order->counter_id];
            $lines[] = ['account' => $this->getAccount($order->shop_id, 'INVENTORY'), 'debit' => 0, 'credit' => $cogs, 'counter_id' => $order->counter_id];
        }

        $this->createTransaction(
            shopId: $order->shop_id,
            type: 'sale',
            referenceType: Order::class,
            referenceId: $order->id,
            description: 'POS Sale - ' . $order->invoice_no,
            date: $order->created_at,
            lines: $lines,
            userId: $order->user_id,
        );
    }

    public function postWebSale(Order $order): void
    {
        if ($this->transactionExists($order->shop_id, 'web_sale', Order::class, $order->id)) {
            return;
        }

        $this->ensureShopAccounts($order->shop_id);

        $receivable = $this->getAccount($order->shop_id, 'WEB-COD');
        $revenue = $this->getAccount($order->shop_id, 'REVENUE');
        $cogs = $this->calculateCogs($order);

        $lines = [
            ['account' => $receivable, 'debit' => $order->total_amount, 'credit' => 0, 'counter_id' => null],
            ['account' => $revenue, 'debit' => 0, 'credit' => $order->total_amount, 'counter_id' => null],
        ];

        if ($cogs > 0) {
            $lines[] = ['account' => $this->getAccount($order->shop_id, 'COGS'), 'debit' => $cogs, 'credit' => 0, 'counter_id' => null];
            $lines[] = ['account' => $this->getAccount($order->shop_id, 'INVENTORY'), 'debit' => 0, 'credit' => $cogs, 'counter_id' => null];
        }

        $this->createTransaction(
            shopId: $order->shop_id,
            type: 'web_sale',
            referenceType: Order::class,
            referenceId: $order->id,
            description: 'Web Order - ' . $order->invoice_no,
            date: $order->created_at,
            lines: $lines,
            userId: $order->user_id,
        );
    }

    public function postWebSettlement(Order $order): void
    {
        if ($this->transactionExists($order->shop_id, 'web_settlement', Order::class, $order->id)) {
            return;
        }

        $this->ensureShopAccounts($order->shop_id);

        $cash = $this->getAccount($order->shop_id, 'WEB-CASH');
        $receivable = $this->getAccount($order->shop_id, 'WEB-COD');

        $this->createTransaction(
            shopId: $order->shop_id,
            type: 'web_settlement',
            referenceType: Order::class,
            referenceId: $order->id,
            description: 'COD Collected - ' . $order->invoice_no,
            date: now(),
            lines: [
                ['account' => $cash, 'debit' => $order->total_amount, 'credit' => 0, 'counter_id' => null],
                ['account' => $receivable, 'debit' => 0, 'credit' => $order->total_amount, 'counter_id' => null],
            ],
            userId: Auth::id(),
        );
    }

    public function postOrderRefund(Order $order): void
    {
        $type = $order->isOnlineOrder() ? 'web_refund' : 'refund';

        if ($this->transactionExists($order->shop_id, $type, Order::class, $order->id)) {
            return;
        }

        $order->loadMissing(['items.product', 'counter']);
        $this->ensureShopAccounts($order->shop_id);

        $revenue = $this->getAccount($order->shop_id, 'REVENUE');
        $cogs = $this->calculateCogs($order);
        $netAmount = $order->netPayable();

        $lines = [
            ['account' => $revenue, 'debit' => $netAmount, 'credit' => 0, 'counter_id' => $order->counter_id],
        ];

        if ($order->isOnlineOrder()) {
            $paymentAccount = $this->transactionExists($order->shop_id, 'web_settlement', Order::class, $order->id)
                ? $this->getAccount($order->shop_id, 'WEB-CASH')
                : $this->getAccount($order->shop_id, 'WEB-COD');
            $lines[] = ['account' => $paymentAccount, 'debit' => 0, 'credit' => $netAmount, 'counter_id' => null];
        } else {
            $cash = max(0, (float) ($order->cash_paid ?? 0));
            $card = max(0, (float) ($order->card_paid ?? 0));
            $mobile = max(0, (float) ($order->mobile_paid ?? 0));
            $hasBreakdown = $order->cash_paid !== null || $order->card_paid !== null || $order->mobile_paid !== null;

            if ($hasBreakdown && ($cash + $card + $mobile) > 0) {
                if ($cash > 0) {
                    $cashAccount = $order->counter_id
                        ? $this->ensureCounterCashAccount($order->counter)
                        : $this->getAccount($order->shop_id, 'WEB-CASH');
                    $lines[] = ['account' => $cashAccount, 'debit' => 0, 'credit' => $cash, 'counter_id' => $order->counter_id];
                }
                if ($card > 0) {
                    $lines[] = ['account' => $this->getAccount($order->shop_id, 'CARD'), 'debit' => 0, 'credit' => $card, 'counter_id' => $order->counter_id];
                }
                if ($mobile > 0) {
                    $lines[] = ['account' => $this->getAccount($order->shop_id, 'BKASH'), 'debit' => 0, 'credit' => $mobile, 'counter_id' => $order->counter_id];
                }
                $credited = round($cash + $card + $mobile, 2);
                if ($credited + 0.009 < $netAmount) {
                    $gap = round($netAmount - $credited, 2);
                    $cashAccount = $order->counter_id
                        ? $this->ensureCounterCashAccount($order->counter)
                        : $this->getAccount($order->shop_id, 'WEB-CASH');
                    $lines[] = ['account' => $cashAccount, 'debit' => 0, 'credit' => $gap, 'counter_id' => $order->counter_id];
                }
            } else {
                $paymentAccount = $this->resolvePaymentAccount($order);
                $lines[] = ['account' => $paymentAccount, 'debit' => 0, 'credit' => $netAmount, 'counter_id' => $order->counter_id];
            }
        }

        if ($cogs > 0) {
            $lines[] = ['account' => $this->getAccount($order->shop_id, 'INVENTORY'), 'debit' => $cogs, 'credit' => 0, 'counter_id' => $order->counter_id];
            $lines[] = ['account' => $this->getAccount($order->shop_id, 'COGS'), 'debit' => 0, 'credit' => $cogs, 'counter_id' => $order->counter_id];
        }

        $this->createTransaction(
            shopId: $order->shop_id,
            type: $type,
            referenceType: Order::class,
            referenceId: $order->id,
            description: 'Refund - ' . $order->invoice_no,
            date: now(),
            lines: $lines,
            userId: Auth::id(),
        );
    }

    public function postTransfer(int $shopId, Account $from, Account $to, float $amount, string $description, ?int $counterId = null): void
    {
        $this->createTransaction(
            shopId: $shopId,
            type: 'transfer',
            referenceType: null,
            referenceId: null,
            description: $description,
            date: now(),
            lines: [
                ['account' => $to, 'debit' => $amount, 'credit' => 0, 'counter_id' => $counterId ?? $to->counter_id],
                ['account' => $from, 'debit' => 0, 'credit' => $amount, 'counter_id' => $counterId ?? $from->counter_id],
            ],
            userId: Auth::id(),
        );
    }

    /**
     * Move cash from one counter till to another with a clear justification trail.
     * Debits destination counter cash / credits source counter cash.
     */
    public function postCounterCashTransfer(
        Counter $fromCounter,
        Counter $toCounter,
        float $amount,
        string $reason,
        ?int $userId = null,
    ): AccountTransaction {
        if ($fromCounter->id === $toCounter->id) {
            throw new \InvalidArgumentException('Choose two different counters.');
        }

        if ($fromCounter->shop_id !== $toCounter->shop_id) {
            throw new \InvalidArgumentException('Counters must belong to the same shop.');
        }

        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Transfer amount must be greater than zero.');
        }

        $fromAccount = $this->ensureCounterCashAccount($fromCounter);
        $toAccount = $this->ensureCounterCashAccount($toCounter);
        $available = $this->accountBalance($fromAccount);

        if ($amount > $available + 0.0001) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Insufficient cash on %s. Available ৳%s, tried ৳%s.',
                    $fromCounter->name,
                    number_format($available, 2),
                    number_format($amount, 2)
                )
            );
        }

        $reason = trim($reason);
        $description = sprintf(
            'Counter transfer: %s → %s — %s',
            $fromCounter->name,
            $toCounter->name,
            $reason
        );

        return $this->createTransaction(
            shopId: $fromCounter->shop_id,
            type: 'transfer',
            referenceType: null,
            referenceId: null,
            description: $description,
            date: now(),
            lines: [
                ['account' => $toAccount, 'debit' => $amount, 'credit' => 0, 'counter_id' => $toCounter->id],
                ['account' => $fromAccount, 'debit' => 0, 'credit' => $amount, 'counter_id' => $fromCounter->id],
            ],
            userId: $userId ?? Auth::id(),
        );
    }

    /**
     * Bank counted drawer cash back to Petty and clear shortage/overage so
     * the next day's opening float does not stack on leftover ledger cash.
     */
    public function settleCounterSessionClose(
        Counter $counter,
        CounterSession $session,
        float $closingCash,
        float $expected,
        ?int $userId = null,
    ): void {
        $this->ensureShopAccounts($counter->shop_id);
        $cashAccount = $this->ensureCounterCashAccount($counter);
        $petty = $this->getAccount($counter->shop_id, 'PETTY');
        $expense = $this->getAccount($counter->shop_id, 'EXPENSE');
        $equity = $this->getAccount($counter->shop_id, 'EQUITY');
        $userId ??= Auth::id();

        $closingCash = round(max(0, $closingCash), 2);
        $expected = round($expected, 2);
        $variance = round($closingCash - $expected, 2);

        if ($variance < -0.009) {
            $short = abs($variance);
            $this->createTransaction(
                shopId: $counter->shop_id,
                type: 'counter_shortage',
                referenceType: CounterSession::class,
                referenceId: $session->id,
                description: "Cash shortage — {$counter->name} session #{$session->id}",
                date: now(),
                lines: [
                    ['account' => $expense, 'debit' => $short, 'credit' => 0, 'counter_id' => $counter->id],
                    ['account' => $cashAccount, 'debit' => 0, 'credit' => $short, 'counter_id' => $counter->id],
                ],
                userId: $userId,
            );
        } elseif ($variance > 0.009) {
            $over = $variance;
            $this->createTransaction(
                shopId: $counter->shop_id,
                type: 'counter_overage',
                referenceType: CounterSession::class,
                referenceId: $session->id,
                description: "Cash overage — {$counter->name} session #{$session->id}",
                date: now(),
                lines: [
                    ['account' => $cashAccount, 'debit' => $over, 'credit' => 0, 'counter_id' => $counter->id],
                    ['account' => $equity, 'debit' => 0, 'credit' => $over, 'counter_id' => $counter->id],
                ],
                userId: $userId,
            );
        }

        if ($closingCash > 0.009) {
            $this->createTransaction(
                shopId: $counter->shop_id,
                type: 'counter_close',
                referenceType: CounterSession::class,
                referenceId: $session->id,
                description: "Closing deposit — {$counter->name} session #{$session->id}",
                date: now(),
                lines: [
                    ['account' => $petty, 'debit' => $closingCash, 'credit' => 0, 'counter_id' => null],
                    ['account' => $cashAccount, 'debit' => 0, 'credit' => $closingCash, 'counter_id' => $counter->id],
                ],
                userId: $userId,
            );
        }

        // Sweep tiny leftovers so till ledger starts clean next open
        $leftover = round($this->accountBalance($cashAccount), 2);
        if (abs($leftover) > 0.009 && abs($leftover) < 1.00) {
            if ($leftover > 0) {
                $this->createTransaction(
                    shopId: $counter->shop_id,
                    type: 'counter_close',
                    referenceType: CounterSession::class,
                    referenceId: $session->id,
                    description: "Closing remainder sweep — {$counter->name} session #{$session->id}",
                    date: now(),
                    lines: [
                        ['account' => $petty, 'debit' => $leftover, 'credit' => 0, 'counter_id' => null],
                        ['account' => $cashAccount, 'debit' => 0, 'credit' => $leftover, 'counter_id' => $counter->id],
                    ],
                    userId: $userId,
                );
            }
        }
    }

    /**
     * Individual transfer lines that hit a counter cash account during a window.
     *
     * @return list<array{direction:string,amount:float,counterpart:?string,reason:string,by:?string,at:string,txn_no:string}>
     */
    public function counterCashTransferLog(Counter $counter, Carbon $from, Carbon $until): array
    {
        $account = $this->ensureCounterCashAccount($counter);

        $entries = AccountEntry::query()
            ->where('account_id', $account->id)
            ->whereHas('transaction', function ($q) use ($from, $until) {
                $q->where('type', 'transfer')
                    ->where('created_at', '>=', $from)
                    ->where('created_at', '<=', $until);
            })
            ->with(['transaction.user', 'transaction.entries.account.counter'])
            ->get();

        $rows = [];

        foreach ($entries as $entry) {
            $txn = $entry->transaction;
            if (! $txn) {
                continue;
            }

            $counterpartEntry = $txn->entries->first(
                fn ($e) => (int) $e->account_id !== (int) $account->id
            );
            $counterpart = $counterpartEntry?->account?->counter?->name
                ?? $counterpartEntry?->account?->name
                ?? 'Other account';

            $direction = $entry->entry_type === 'debit' ? 'in' : 'out';
            $reason = (string) $txn->description;
            // Strip the auto prefix for cleaner UI when present
            if (str_contains($reason, ' — ')) {
                $reason = trim(Str::afterLast($reason, ' — '));
            }

            $rows[] = [
                'direction' => $direction,
                'amount' => round((float) $entry->amount, 2),
                'counterpart' => $counterpart,
                'reason' => $reason !== '' ? $reason : (string) $txn->description,
                'by' => $txn->user?->name,
                'at' => asian_datetime($txn->created_at, 'd M Y, h:i A'),
                'at_ts' => optional($txn->created_at)->timestamp ?? 0,
                'txn_no' => (string) $txn->transaction_no,
            ];
        }

        return collect($rows)
            ->sortByDesc('at_ts')
            ->values()
            ->map(function (array $row) {
                unset($row['at_ts']);

                return $row;
            })
            ->all();
    }

    public function postPettyCash(int $shopId, float $amount, string $description): void
    {
        $petty = $this->getAccount($shopId, 'PETTY');
        $expense = $this->getAccount($shopId, 'EXPENSE');

        $this->createTransaction(
            shopId: $shopId,
            type: 'petty_cash',
            referenceType: null,
            referenceId: null,
            description: $description,
            date: now(),
            lines: [
                ['account' => $expense, 'debit' => $amount, 'credit' => 0, 'counter_id' => null],
                ['account' => $petty, 'debit' => 0, 'credit' => $amount, 'counter_id' => null],
            ],
            userId: Auth::id(),
        );
    }

    public function postOpeningInventory(StockMovement $movement): void
    {
        if ($this->transactionExists($movement->shop_id, 'opening_inventory', StockMovement::class, $movement->id)) {
            return;
        }

        $movement->loadMissing('product');
        $product = $movement->product;

        if (! $product) {
            return;
        }

        $this->ensureShopAccounts($movement->shop_id);

        $value = (float) $product->cost_price * $movement->quantity;

        if ($value <= 0) {
            return;
        }

        $inventory = $this->getAccount($movement->shop_id, 'INVENTORY');
        $equity = $this->getAccount($movement->shop_id, 'EQUITY');

        $lines = $movement->type === 'in'
            ? [
                ['account' => $inventory, 'debit' => $value, 'credit' => 0, 'counter_id' => null],
                ['account' => $equity, 'debit' => 0, 'credit' => $value, 'counter_id' => null],
            ]
            : [
                ['account' => $inventory, 'debit' => 0, 'credit' => $value, 'counter_id' => null],
                ['account' => $equity, 'debit' => $value, 'credit' => 0, 'counter_id' => null],
            ];

        $this->createTransaction(
            shopId: $movement->shop_id,
            type: 'opening_inventory',
            referenceType: StockMovement::class,
            referenceId: $movement->id,
            description: 'Opening inventory - ' . $product->name,
            date: $movement->created_at,
            lines: $lines,
            userId: $movement->user_id,
        );
    }

    /**
     * Purchase receive: Dr Inventory / Cr Accounts Payable
     */
    public function postPurchaseReceive(StockMovement $movement, float $unitCost, ?string $poNumber = null): void
    {
        if ($this->transactionExists($movement->shop_id, 'purchase_receive', StockMovement::class, $movement->id)) {
            return;
        }

        $movement->loadMissing('product');
        $product = $movement->product;

        if (! $product) {
            return;
        }

        $this->ensureShopAccounts($movement->shop_id);

        $value = round($unitCost * $movement->quantity, 2);
        if ($value <= 0) {
            return;
        }

        $this->createTransaction(
            shopId: $movement->shop_id,
            type: 'purchase_receive',
            referenceType: StockMovement::class,
            referenceId: $movement->id,
            description: 'Purchase receive' . ($poNumber ? ' - ' . $poNumber : '') . ' - ' . $product->name,
            date: $movement->created_at,
            lines: [
                ['account' => $this->getAccount($movement->shop_id, 'INVENTORY'), 'debit' => $value, 'credit' => 0, 'counter_id' => null],
                ['account' => $this->getAccount($movement->shop_id, 'AP'), 'debit' => 0, 'credit' => $value, 'counter_id' => null],
            ],
            userId: $movement->user_id,
        );
    }

    /**
     * Purchase return: Dr Accounts Payable / Cr Inventory
     */
    public function postPurchaseReturn(StockMovement $movement, float $unitCost, ?string $returnNumber = null): void
    {
        if ($this->transactionExists($movement->shop_id, 'purchase_return', StockMovement::class, $movement->id)) {
            return;
        }

        $movement->loadMissing('product');
        $product = $movement->product;

        if (! $product) {
            return;
        }

        $this->ensureShopAccounts($movement->shop_id);

        $value = round($unitCost * $movement->quantity, 2);
        if ($value <= 0) {
            return;
        }

        $this->createTransaction(
            shopId: $movement->shop_id,
            type: 'purchase_return',
            referenceType: StockMovement::class,
            referenceId: $movement->id,
            description: 'Purchase return' . ($returnNumber ? ' - ' . $returnNumber : '') . ' - ' . $product->name,
            date: $movement->created_at,
            lines: [
                ['account' => $this->getAccount($movement->shop_id, 'AP'), 'debit' => $value, 'credit' => 0, 'counter_id' => null],
                ['account' => $this->getAccount($movement->shop_id, 'INVENTORY'), 'debit' => 0, 'credit' => $value, 'counter_id' => null],
            ],
            userId: $movement->user_id,
        );
    }

    /**
     * Pay supplier: Dr Accounts Payable / Cr Cash (or other payment account)
     */
    public function postSupplierPayment(int $shopId, float $amount, Account $paymentAccount, string $description, ?int $userId = null): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be greater than zero.');
        }

        $this->ensureShopAccounts($shopId);

        $this->createTransaction(
            shopId: $shopId,
            type: 'supplier_payment',
            referenceType: null,
            referenceId: null,
            description: $description,
            date: now(),
            lines: [
                ['account' => $this->getAccount($shopId, 'AP'), 'debit' => $amount, 'credit' => 0, 'counter_id' => null],
                ['account' => $paymentAccount, 'debit' => 0, 'credit' => $amount, 'counter_id' => $paymentAccount->counter_id],
            ],
            userId: $userId ?? Auth::id(),
        );
    }

    /**
     * Stock damage / adjustment out: Dr Expense / Cr Inventory.
     * Adjustment in (found stock): Dr Inventory / Cr Equity.
     */
    public function postInventoryAdjustment(StockMovement $movement): void
    {
        $typeKey = $movement->reason === 'damage' ? 'stock_damage' : 'stock_adjustment';

        if ($this->transactionExists($movement->shop_id, $typeKey, StockMovement::class, $movement->id)) {
            return;
        }

        $movement->loadMissing('product');
        $product = $movement->product;

        if (! $product) {
            return;
        }

        $this->ensureShopAccounts($movement->shop_id);

        $value = round((float) $product->cost_price * $movement->quantity, 2);
        if ($value <= 0) {
            return;
        }

        $inventory = $this->getAccount($movement->shop_id, 'INVENTORY');

        if ($movement->type === 'in') {
            $lines = [
                ['account' => $inventory, 'debit' => $value, 'credit' => 0, 'counter_id' => null],
                ['account' => $this->getAccount($movement->shop_id, 'EQUITY'), 'debit' => 0, 'credit' => $value, 'counter_id' => null],
            ];
            $description = 'Stock adjustment in - ' . $product->name;
        } else {
            $lines = [
                ['account' => $this->getAccount($movement->shop_id, 'EXPENSE'), 'debit' => $value, 'credit' => 0, 'counter_id' => null],
                ['account' => $inventory, 'debit' => 0, 'credit' => $value, 'counter_id' => null],
            ];
            $description = ($movement->reason === 'damage' ? 'Damaged stock write-off - ' : 'Stock adjustment out - ') . $product->name;
        }

        $this->createTransaction(
            shopId: $movement->shop_id,
            type: $typeKey,
            referenceType: StockMovement::class,
            referenceId: $movement->id,
            description: $description,
            date: $movement->created_at,
            lines: $lines,
            userId: $movement->user_id,
        );
    }

    /**
     * Opening float into a counter cash account (from Petty Cash).
     */
    public function postCounterFloat(int $shopId, Account $counterCash, float $amount, string $description, ?int $userId = null): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->ensureShopAccounts($shopId);
        $petty = $this->getAccount($shopId, 'PETTY');

        $this->createTransaction(
            shopId: $shopId,
            type: 'counter_open',
            referenceType: null,
            referenceId: null,
            description: $description,
            date: now(),
            lines: [
                ['account' => $counterCash, 'debit' => $amount, 'credit' => 0, 'counter_id' => $counterCash->counter_id],
                ['account' => $petty, 'debit' => 0, 'credit' => $amount, 'counter_id' => null],
            ],
            userId: $userId ?? Auth::id(),
        );
    }

    public function cashAccounts(int $shopId)
    {
        return Account::where('shop_id', $shopId)
            ->where('type', 'asset')
            ->where(function ($q) {
                $q->where('code', 'like', 'CASH-%')
                    ->orWhereIn('code', ['PETTY', 'WEB-CASH', 'BKASH', 'CARD']);
            })
            ->where('is_active', true)
            ->with('counter')
            ->orderBy('name')
            ->get();
    }

    public function dailySummary(int $shopId, Carbon $date, ?int $counterId = null): array
    {
        $counters = $counterId
            ? Counter::where('shop_id', $shopId)->where('id', $counterId)->get()
            : Counter::where('shop_id', $shopId)->where('is_active', true)->get();

        $rows = [];

        foreach ($counters as $counter) {
            $account = $this->ensureCounterCashAccount($counter);
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            $opening = $this->accountBalance($account, $dayStart->copy()->subSecond());
            $salesIn = $this->sumEntries($account->id, 'debit', $dayStart, $dayEnd, 'sale');
            $transfersIn = $this->sumEntries($account->id, 'debit', $dayStart, $dayEnd, 'transfer');
            $transfersOut = $this->sumEntries($account->id, 'credit', $dayStart, $dayEnd, 'transfer');
            $refundsOut = $this->sumEntries($account->id, 'credit', $dayStart, $dayEnd, 'refund');
            $purchasesOut = $this->sumEntries($account->id, 'credit', $dayStart, $dayEnd, 'supplier_payment');
            $closing = $this->accountBalance($account, $dayEnd);

            $rows[] = [
                'counter' => $counter,
                'account' => $account,
                'opening' => $opening,
                'sales_in' => $salesIn,
                'transfers_in' => $transfersIn,
                'transfers_out' => $transfersOut,
                'refunds_out' => $refundsOut,
                'purchases_out' => $purchasesOut,
                'closing' => $closing,
            ];
        }

        return $rows;
    }

    /**
     * Cash movements on a counter till between two timestamps (session window).
     * Excludes opening float (counter_open) — that is already in session opening_cash.
     */
    public function counterCashSessionMovements(Counter $counter, Carbon $from, Carbon $until): array
    {
        $account = $this->ensureCounterCashAccount($counter);

        return [
            'transfers_in' => $this->sumEntriesCreatedBetween($account->id, 'debit', $from, $until, 'transfer'),
            'transfers_out' => $this->sumEntriesCreatedBetween($account->id, 'credit', $from, $until, 'transfer'),
            'cash_purchases' => $this->sumEntriesCreatedBetween($account->id, 'credit', $from, $until, 'supplier_payment'),
        ];
    }

    protected function resolvePaymentAccount(Order $order): Account
    {
        $method = strtolower($order->payment_method ?? 'cash');

        if (in_array($method, ['card', 'credit_card', 'debit_card'])) {
            return $this->getAccount($order->shop_id, 'CARD');
        }

        if (in_array($method, ['bkash', 'nagad', 'rocket', 'mobile'])) {
            return $this->getAccount($order->shop_id, 'BKASH');
        }

        if ($order->counter_id) {
            return $this->ensureCounterCashAccount($order->counter);
        }

        return $this->getAccount($order->shop_id, 'WEB-CASH');
    }

    protected function calculateCogs(Order $order): float
    {
        return $order->items->sum(function ($item) {
            $cost = $item->product?->cost_price ?? 0;

            return (float) $cost * $item->quantity;
        });
    }

    protected function transactionExists(int $shopId, string $type, ?string $referenceType, ?int $referenceId): bool
    {
        return AccountTransaction::where('shop_id', $shopId)
            ->where('type', $type)
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->exists();
    }

    protected function createTransaction(
        int $shopId,
        string $type,
        ?string $referenceType,
        ?int $referenceId,
        string $description,
        $date,
        array $lines,
        ?int $userId = null,
    ): AccountTransaction {
        $txnNo = 'TXN-' . $shopId . '-' . now()->format('YmdHis') . '-' . rand(100, 999);

        return DB::transaction(function () use ($shopId, $type, $referenceType, $referenceId, $description, $date, $lines, $userId, $txnNo) {
            $txn = AccountTransaction::create([
                'shop_id' => $shopId,
                'user_id' => $userId,
                'transaction_no' => $txnNo,
                'type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'transaction_date' => Carbon::parse($date)->toDateString(),
            ]);

            foreach ($lines as $line) {
                $debit = (float) ($line['debit'] ?? 0);
                $credit = (float) ($line['credit'] ?? 0);

                if ($debit > 0) {
                    AccountEntry::create([
                        'account_transaction_id' => $txn->id,
                        'account_id' => $line['account']->id,
                        'counter_id' => $line['counter_id'] ?? $line['account']->counter_id,
                        'entry_type' => 'debit',
                        'amount' => $debit,
                    ]);
                }

                if ($credit > 0) {
                    AccountEntry::create([
                        'account_transaction_id' => $txn->id,
                        'account_id' => $line['account']->id,
                        'counter_id' => $line['counter_id'] ?? $line['account']->counter_id,
                        'entry_type' => 'credit',
                        'amount' => $credit,
                    ]);
                }
            }

            return $txn;
        });
    }

    protected function sumEntries(int $accountId, string $entryType, Carbon $start, Carbon $end, ?string $txnType = null): float
    {
        $query = AccountEntry::where('account_id', $accountId)
            ->where('entry_type', $entryType)
            ->whereHas('transaction', function ($q) use ($start, $end, $txnType) {
                $q->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()]);
                if ($txnType) {
                    $q->where('type', $txnType);
                }
            });

        return (float) $query->sum('amount');
    }

    protected function sumEntriesCreatedBetween(int $accountId, string $entryType, Carbon $from, Carbon $until, string $txnType): float
    {
        return (float) AccountEntry::where('account_id', $accountId)
            ->where('entry_type', $entryType)
            ->whereHas('transaction', function ($q) use ($from, $until, $txnType) {
                $q->where('type', $txnType)
                    ->where('created_at', '>=', $from)
                    ->where('created_at', '<=', $until);
            })
            ->sum('amount');
    }
}
