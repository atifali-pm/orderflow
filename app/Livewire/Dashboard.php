<?php

namespace App\Livewire;

use App\Models\AutomationLog;
use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $counts = Order::query()
            ->selectRaw('status, count(*) as order_count')
            ->groupBy('status')
            ->pluck('order_count', 'status')
            ->map(fn ($n) => (int) $n)
            ->all();

        $totalsByStatus = array_merge(
            array_fill_keys(Order::STATUSES, 0),
            $counts,
        );

        $recentOrders = Order::with('customer')
            ->latest('placed_at')
            ->take(5)
            ->get();

        $recentLogs = AutomationLog::with('order')
            ->latest('received_at')
            ->take(6)
            ->get();

        return view('livewire.dashboard', [
            'totalsByStatus' => $totalsByStatus,
            'totalOrders' => array_sum($totalsByStatus),
            'totalRevenue' => (float) Order::query()
                ->whereIn('status', ['paid', 'shipped'])
                ->sum('total'),
            'recentOrders' => $recentOrders,
            'recentLogs' => $recentLogs,
        ]);
    }
}
