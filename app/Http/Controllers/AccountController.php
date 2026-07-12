<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountEntry;
use App\Models\Counter;
use App\Services\AccountService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function __construct(protected AccountService $accounts) {}

    protected function bootstrap(): int
    {
        $shopId = Auth::user()->shop_id;
        $this->accounts->ensureShopAccounts($shopId);

        return $shopId;
    }

    public function openingBalance(Request $request)
    {
        $shopId = $this->bootstrap();
        [$start, $end] = $this->accounts->dateRange($request);

        $accounts = Account::where('shop_id', $shopId)
            ->where('is_active', true)
            ->with('counter')
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->map(fn (Account $a) => [
                'account' => $a,
                'balance' => $this->accounts->accountBalance($a),
            ]);

        return view('accounts.opening-balance', compact('accounts', 'start', 'end'));
    }

    public function updateOpeningBalance(Request $request)
    {
        $shopId = $this->bootstrap();

        $request->validate([
            'balances' => 'required|array',
            'balances.*' => 'nullable|numeric|min:0',
        ]);

        foreach ($request->balances as $accountId => $amount) {
            $account = Account::where('shop_id', $shopId)->findOrFail($accountId);
            $account->update(['opening_balance' => $amount ?? 0]);
        }

        return back()->with('success', 'Opening balances updated successfully.');
    }

    public function chart(Request $request)
    {
        $shopId = $this->bootstrap();
        [$start, $end] = $this->accounts->dateRange($request);

        $accounts = Account::where('shop_id', $shopId)
            ->with('counter')
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->map(fn (Account $a) => [
                'account' => $a,
                'balance' => $this->accounts->accountBalance($a),
            ]);

        $grouped = $accounts->groupBy(fn ($row) => $row['account']->type);

        return view('accounts.chart', compact('grouped', 'start', 'end'));
    }

    public function storeAccount(Request $request)
    {
        $shopId = $this->bootstrap();

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,income,expense',
        ]);

        $code = 'CUSTOM-' . strtoupper(substr(md5($request->name . now()), 0, 8));

        Account::create([
            'shop_id' => $shopId,
            'code' => $code,
            'name' => $request->name,
            'type' => $request->type,
            'is_system' => false,
            'is_active' => true,
        ]);

        return back()->with('success', 'Account added to chart.');
    }

    public function ledger(Request $request)
    {
        $shopId = $this->bootstrap();
        [$start, $end] = $this->accounts->dateRange($request);

        $accountList = Account::where('shop_id', $shopId)->where('is_active', true)->orderBy('name')->get();
        $accountId = $request->get('account_id', $accountList->first()?->id);
        $account = $accountId ? Account::where('shop_id', $shopId)->find($accountId) : null;

        $entries = collect();
        $runningBalance = 0;

        if ($account) {
            $opening = (float) $account->opening_balance;
            $priorEntries = AccountEntry::where('account_id', $account->id)
                ->whereHas('transaction', fn ($q) => $q->where('transaction_date', '<', $start->toDateString()))
                ->get();

            foreach ($priorEntries as $e) {
                $opening += $this->entryDelta($account, $e);
            }

            $periodEntries = AccountEntry::where('account_id', $account->id)
                ->whereHas('transaction', fn ($q) => $q->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()]))
                ->with(['transaction', 'counter'])
                ->get()
                ->sortBy(fn ($e) => $e->transaction->transaction_date . $e->id);

            $runningBalance = $opening;
            $entries = $periodEntries->map(function ($e) use ($account, &$runningBalance) {
                $delta = $this->entryDelta($account, $e);
                $runningBalance += $delta;

                return [
                    'entry' => $e,
                    'debit' => $e->entry_type === 'debit' ? $e->amount : 0,
                    'credit' => $e->entry_type === 'credit' ? $e->amount : 0,
                    'balance' => $runningBalance,
                ];
            });
        }

        $counters = Counter::where('shop_id', $shopId)->get();

        return view('accounts.ledger', compact('account', 'accountList', 'entries', 'start', 'end', 'counters'));
    }

    public function cashBook(Request $request)
    {
        $shopId = $this->bootstrap();
        [$start, $end] = $this->accounts->dateRange($request);

        $cashAccounts = $this->accounts->cashAccounts($shopId);
        $accountId = $request->get('account_id');
        $counterId = $request->get('counter_id');

        if ($counterId && !$accountId) {
            $accountId = $cashAccounts->firstWhere('counter_id', (int) $counterId)?->id;
        }

        $account = $accountId
            ? $cashAccounts->firstWhere('id', (int) $accountId)
            : $cashAccounts->first();

        $entries = collect();

        if ($account) {
            $entries = AccountEntry::where('account_id', $account->id)
                ->whereHas('transaction', fn ($q) => $q->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()]))
                ->with(['transaction', 'counter'])
                ->join('account_transactions', 'account_entries.account_transaction_id', '=', 'account_transactions.id')
                ->orderBy('account_transactions.transaction_date')
                ->orderBy('account_entries.id')
                ->select('account_entries.*')
                ->get();
        }

        $counters = Counter::where('shop_id', $shopId)->get();
        $balance = $account ? $this->accounts->accountBalance($account) : 0;

        return view('accounts.cash-book', compact('cashAccounts', 'account', 'entries', 'start', 'end', 'counters', 'balance'));
    }

    public function dailySummary(Request $request)
    {
        $shopId = $this->bootstrap();

        $date = $request->filled('date') ? Carbon::parse($request->date) : now();
        $counterId = $request->get('counter_id');
        $rows = $this->accounts->dailySummary($shopId, $date, $counterId ? (int) $counterId : null);
        $counters = Counter::where('shop_id', $shopId)->get();

        return view('accounts.daily-summary', compact('rows', 'date', 'counters', 'counterId'));
    }

    public function pettyCashForm()
    {
        $shopId = $this->bootstrap();
        $pettyBalance = $this->accounts->accountBalance($this->accounts->getAccount($shopId, 'PETTY'));

        return view('accounts.petty-cash', compact('pettyBalance'));
    }

    public function pettyCashStore(Request $request)
    {
        $shopId = $this->bootstrap();

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
        ]);

        $petty = $this->accounts->getAccount($shopId, 'PETTY');
        $balance = $this->accounts->accountBalance($petty);

        if ($request->amount > $balance) {
            return back()->with('error', 'Insufficient petty cash balance.');
        }

        $this->accounts->postPettyCash($shopId, (float) $request->amount, $request->description);

        return back()->with('success', 'Petty cash expense recorded.');
    }

    public function transferForm()
    {
        $shopId = $this->bootstrap();

        $accounts = Account::where('shop_id', $shopId)
            ->where('is_active', true)
            ->where('type', 'asset')
            ->with('counter')
            ->orderBy('name')
            ->get();

        $counters = Counter::where('shop_id', $shopId)->get();

        return view('accounts.transfer', compact('accounts', 'counters'));
    }

    public function transferStore(Request $request)
    {
        $shopId = $this->bootstrap();

        $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'counter_id' => 'nullable|exists:counters,id',
        ]);

        $from = Account::where('shop_id', $shopId)->findOrFail($request->from_account_id);
        $to = Account::where('shop_id', $shopId)->findOrFail($request->to_account_id);

        $fromBalance = $this->accounts->accountBalance($from);
        if ($request->amount > $fromBalance) {
            return back()->with('error', 'Insufficient balance in source account.');
        }

        $this->accounts->postTransfer(
            $shopId,
            $from,
            $to,
            (float) $request->amount,
            $request->description,
            $request->counter_id ? (int) $request->counter_id : null,
        );

        return back()->with('success', 'Transfer completed successfully.');
    }

    protected function entryDelta(Account $account, AccountEntry $entry): float
    {
        $amount = (float) $entry->amount;

        if (in_array($account->type, ['asset', 'expense'])) {
            return $entry->entry_type === 'debit' ? $amount : -$amount;
        }

        return $entry->entry_type === 'credit' ? $amount : -$amount;
    }
}
