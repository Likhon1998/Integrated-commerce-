<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountEntry;
use App\Models\AccountTransaction;
use App\Models\Counter;
use App\Models\Order;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountService
{
    public const SYSTEM_ACCOUNTS = [
        ['code' => 'EQUITY', 'name' => "Owner's Capital", 'type' => 'equity'],
        ['code' => 'REVENUE', 'name' => 'Sales Revenue', 'type' => 'income'],
        ['code' => 'COGS', 'name' => 'Cost of Goods Sold', 'type' => 'expense'],
        ['code' => 'INVENTORY', 'name' => 'Inventory Asset', 'type' => 'asset'],
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
        $opening = (float) $account->opening_balance;

        return match ($account->type) {
            'asset', 'expense' => $opening + $debits - $credits,
            default => $opening + $credits - $debits,
        };
    }

    public function postOrderSale(Order $order): void
    {
        $order->loadMissing('items.product');

        if ($order->counter_id) {
            $this->postPosSale($order);
        } else {
            $this->postWebSale($order);
        }
    }

    public function postPosSale(Order $order): void
    {
        if ($this->transactionExists($order->shop_id, 'sale', Order::class, $order->id)) {
            return;
        }

        $this->ensureShopAccounts($order->shop_id);
        if ($order->counter_id) {
            $this->ensureCounterCashAccount($order->counter);
        }

        $paymentAccount = $this->resolvePaymentAccount($order);
        $revenue = $this->getAccount($order->shop_id, 'REVENUE');
        $cogs = $this->calculateCogs($order);

        $lines = [
            ['account' => $paymentAccount, 'debit' => $order->total_amount, 'credit' => 0, 'counter_id' => $order->counter_id],
            ['account' => $revenue, 'debit' => 0, 'credit' => $order->total_amount, 'counter_id' => $order->counter_id],
        ];

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
        $type = $order->counter_id ? 'refund' : 'web_refund';

        if ($this->transactionExists($order->shop_id, $type, Order::class, $order->id)) {
            return;
        }

        $order->loadMissing('items.product');
        $this->ensureShopAccounts($order->shop_id);

        $paymentAccount = $order->counter_id
            ? $this->resolvePaymentAccount($order)
            : ($this->transactionExists($order->shop_id, 'web_settlement', Order::class, $order->id)
                ? $this->getAccount($order->shop_id, 'WEB-CASH')
                : $this->getAccount($order->shop_id, 'WEB-COD'));

        $revenue = $this->getAccount($order->shop_id, 'REVENUE');
        $cogs = $this->calculateCogs($order);

        $lines = [
            ['account' => $revenue, 'debit' => $order->total_amount, 'credit' => 0, 'counter_id' => $order->counter_id],
            ['account' => $paymentAccount, 'debit' => 0, 'credit' => $order->total_amount, 'counter_id' => $order->counter_id],
        ];

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
            $closing = $this->accountBalance($account, $dayEnd);

            $rows[] = [
                'counter' => $counter,
                'account' => $account,
                'opening' => $opening,
                'sales_in' => $salesIn,
                'transfers_in' => $transfersIn,
                'transfers_out' => $transfersOut,
                'refunds_out' => $refundsOut,
                'closing' => $closing,
            ];
        }

        return $rows;
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
}
