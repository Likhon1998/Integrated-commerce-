<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\CounterSession;
use App\Services\CounterSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CounterSessionController extends Controller
{
    public function __construct(protected CounterSessionService $sessions) {}

    public function index()
    {
        $shopId = Auth::user()->shop_id;

        $counters = Counter::where('shop_id', $shopId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $openSessions = CounterSession::where('shop_id', $shopId)
            ->open()
            ->with(['counter', 'opener'])
            ->get()
            ->keyBy('counter_id');

        $recent = CounterSession::where('shop_id', $shopId)
            ->with(['counter', 'opener', 'closer'])
            ->latest('opened_at')
            ->paginate(15);

        $live = [];
        foreach ($openSessions as $counterId => $session) {
            $stats = $this->sessions->liveStats($session);
            $live[$counterId] = [
                'stats' => $stats,
                'expected' => $this->sessions->expectedCash($session, $stats),
            ];
        }

        return view('counters.sessions', compact('counters', 'openSessions', 'recent', 'live'));
    }

    public function openTodayForm()
    {
        $user = Auth::user();

        if ($user->isAdminUser()) {
            return redirect()->route('counters.sessions.index');
        }

        if (! $user->counter_id) {
            return redirect()->route('dashboard')
                ->with('error', 'No counter assigned. Ask your admin to assign you a counter.');
        }

        if ($user->hasTodayOpenSession()) {
            return redirect()->route('pos.index');
        }

        $counter = Counter::where('shop_id', $user->shop_id)->findOrFail($user->counter_id);
        $staleSession = $this->sessions->currentOpen($counter);

        // Open from a previous day — must close before today's opening cash
        if ($staleSession && ! $staleSession->opened_at->isToday()) {
            $stats = $this->sessions->liveStats($staleSession);
            $expected = $this->sessions->expectedCash($staleSession, $stats);
            $staleSession->load(['counter', 'opener']);

            return view('counters.open-today', compact('counter', 'staleSession', 'stats', 'expected'));
        }

        if ($staleSession && $staleSession->opened_at->isToday()) {
            return redirect()->route('pos.index');
        }

        return view('counters.open-today', [
            'counter' => $counter,
            'staleSession' => null,
            'stats' => null,
            'expected' => null,
        ]);
    }

    public function openTodayStore(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdminUser() || ! $user->counter_id) {
            abort(403);
        }

        if ($user->hasTodayOpenSession()) {
            return redirect()->route('pos.index');
        }

        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $counter = Counter::where('shop_id', $user->shop_id)->findOrFail($user->counter_id);

        if ($this->sessions->currentOpen($counter)) {
            return back()->with('error', 'Close the previous cash session before entering today\'s opening balance.');
        }

        try {
            $this->sessions->openSession($counter, (float) $request->opening_cash, $request->notes);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('pos.index')
            ->with('success', "{$counter->name} opened with ৳" . number_format((float) $request->opening_cash, 2) . ' starting cash.');
    }

    public function open(Request $request)
    {
        $request->validate([
            'counter_id' => 'required|exists:counters,id',
            'opening_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $counter = Counter::where('shop_id', Auth::user()->shop_id)->findOrFail($request->counter_id);

        try {
            $this->sessions->openSession($counter, (float) $request->opening_cash, $request->notes);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$counter->name} opened with starting cash ৳" . number_format((float) $request->opening_cash, 2));
    }

    public function closeForm(CounterSession $session)
    {
        $this->authorizeSession($session);

        if (! $session->isOpen()) {
            return redirect()->route('counters.sessions.index')->with('error', 'Session already closed.');
        }

        $stats = $this->sessions->liveStats($session);
        $expected = $this->sessions->expectedCash($session, $stats);
        $session->load(['counter', 'opener']);

        return view('counters.close-session', compact('session', 'stats', 'expected'));
    }

    public function close(Request $request, CounterSession $session)
    {
        $this->authorizeSession($session);

        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $closed = $this->sessions->closeSession($session, (float) $request->closing_cash, $request->notes);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $user = Auth::user();
        if ($user->requiresDailyOpeningBalance() && ! $user->hasTodayOpenSession()) {
            return redirect()
                ->route('counters.sessions.open-today')
                ->with('success', 'Previous session closed. Now enter today\'s opening cash.');
        }

        return redirect()
            ->route('counters.sessions.show', $closed)
            ->with('success', 'Counter closed. Variance: ৳' . number_format((float) $closed->variance, 2));
    }

    public function show(CounterSession $session)
    {
        $this->authorizeSession($session);
        $session->load(['counter', 'opener', 'closer']);

        return view('counters.session-show', compact('session'));
    }

    protected function authorizeSession(CounterSession $session): void
    {
        if ($session->shop_id !== Auth::user()->shop_id) {
            abort(403);
        }
    }
}
