<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class DashboardService
{
    /** @return array<string, mixed> */
    public function metrics(Outlet $outlet, ?string $from = null, ?string $to = null): array
    {
        $now = CarbonImmutable::now($outlet->timezone);
        $periodStart = $from
            ? CarbonImmutable::createFromFormat('Y-m-d', $from, $outlet->timezone)->startOfDay()
            : $now->startOfMonth();
        $periodEnd = $to
            ? CarbonImmutable::createFromFormat('Y-m-d', $to, $outlet->timezone)->endOfDay()
            : $now->endOfDay();

        if ($periodEnd->isAfter($now->endOfDay())) {
            $periodEnd = $now->endOfDay();
        }

        $dayCount = (int) $periodStart->startOfDay()->diffInDays($periodEnd->startOfDay()) + 1;
        $previousEnd = $periodStart->subSecond();
        $previousStart = $periodStart->subDays($dayCount);

        [$periodTransactions, $periodExpenses, $periodItems] = $this->periodData($outlet, $periodStart, $periodEnd);
        [$previousTransactions, $previousExpenses, $previousItems] = $this->periodData($outlet, $previousStart, $previousEnd);

        $periodSummary = $this->summarize($periodTransactions, $periodExpenses, $periodItems, $dayCount);
        $previousSummary = $this->summarize($previousTransactions, $previousExpenses, $previousItems, $dayCount);
        $comparison = $this->comparison($periodSummary, $previousSummary);

        $todayStart = $now->startOfDay();
        $todaySummary = $this->aggregatePeriod($outlet, $todayStart, $now->endOfDay());

        $monthStart = $now->startOfMonth();
        $monthSummary = $this->aggregatePeriod($outlet, $monthStart, $now->endOfDay());
        $previousMonthStart = $monthStart->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = $monthStart->subSecond();
        $previousMonthSummary = $this->aggregatePeriod($outlet, $previousMonthStart, $previousMonthEnd);
        $monthSummary['revenue_growth'] = $this->growth($monthSummary['revenue'], $previousMonthSummary['revenue']);
        $monthSummary['transaction_growth'] = $this->growth($monthSummary['transaction_count'], $previousMonthSummary['transaction_count']);
        $monthSummary['projection'] = $now->day > 0
            ? round(($monthSummary['revenue'] / $now->day) * $now->daysInMonth, 2)
            : 0;
        $monthSummary['elapsed_days'] = $now->day;
        $monthSummary['days_in_month'] = $now->daysInMonth;

        $lowStock = Product::query()
            ->with('category')
            ->forOutlet($outlet)
            ->active()
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->limit(8)
            ->get();

        $topProducts = $this->topProducts($periodItems);
        $categoryContribution = $this->categoryContribution($periodItems);
        $paymentMix = $this->paymentMix($periodTransactions);
        $peakHours = $this->peakHours($periodTransactions);

        return [
            'period' => [
                'from' => $periodStart->toDateString(),
                'to' => $periodEnd->toDateString(),
                'label' => $this->periodLabel($periodStart, $periodEnd),
                'days' => $dayCount,
                'previous_from' => $previousStart->toDateString(),
                'previous_to' => $previousEnd->toDateString(),
            ],
            'today' => $todaySummary,
            'month' => $monthSummary,
            'summary' => $periodSummary,
            'comparison' => $comparison,
            'trend' => $this->trend($periodStart, $periodEnd, $periodTransactions, $periodExpenses),
            // Backward-compatible key used by older frontend builds.
            'weekly_sales' => $this->trend($now->subDays(6)->startOfDay(), $now->endOfDay(),
                $this->transactions($outlet, $now->subDays(6)->startOfDay(), $now->endOfDay()),
                $this->expenses($outlet, $now->subDays(6)->startOfDay(), $now->endOfDay())
            )->map(fn (array $row): array => ['label' => $row['label'], 'value' => $row['revenue']]),
            'category_contribution' => $categoryContribution,
            'top_products' => $topProducts,
            'payment_mix' => $paymentMix,
            'peak_hours' => $peakHours,
            'low_stock' => $lowStock,
            'insights' => $this->insights($lowStock->count(), $periodSummary, $comparison, $topProducts, $peakHours),
        ];
    }

    /** @return array{0:Collection<int,Transaction>,1:Collection<int,Expense>,2:Collection<int,TransactionItem>} */
    private function periodData(Outlet $outlet, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $transactions = $this->transactions($outlet, $start, $end);
        $expenses = $this->expenses($outlet, $start, $end);
        $items = $transactions->isEmpty()
            ? collect()
            : TransactionItem::query()
                ->whereIn('transaction_id', $transactions->pluck('id'))
                ->get(['transaction_id', 'product_id', 'product_name', 'category_name', 'quantity', 'line_subtotal', 'line_profit']);

        return [$transactions, $expenses, $items];
    }

    /** @return Collection<int,Transaction> */
    private function transactions(Outlet $outlet, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        return Transaction::query()
            ->forOutlet($outlet)
            ->where('status', 'paid')
            ->whereBetween('transacted_at', [$start, $end])
            ->get([
                'id', 'customer_id', 'payment_method', 'transacted_at', 'subtotal',
                'discount_amount', 'service_charge_amount', 'tax_amount', 'grand_total', 'gross_profit',
            ]);
    }

    /** @return Collection<int,Expense> */
    private function expenses(Outlet $outlet, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        return Expense::query()
            ->forOutlet($outlet)
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->get(['expense_date', 'amount', 'category']);
    }

    /** @return array<string, float|int> */
    private function summarize(Collection $transactions, Collection $expenses, Collection $items, int $days): array
    {
        $revenue = (float) $transactions->sum('grand_total');
        $grossProfit = (float) $transactions->sum('gross_profit');
        $expenseTotal = (float) $expenses->sum('amount');
        $transactionCount = $transactions->count();
        $uniqueCustomers = $transactions->whereNotNull('customer_id')->pluck('customer_id')->unique()->count();
        $memberTransactions = $transactions->whereNotNull('customer_id')->count();
        $repeatCustomers = $transactions->whereNotNull('customer_id')->groupBy('customer_id')->filter(fn (Collection $rows): bool => $rows->count() > 1)->count();
        $itemsSold = (int) $items->sum('quantity');
        $netProfit = $grossProfit - $expenseTotal;

        return [
            'revenue' => $revenue,
            'gross_profit' => $grossProfit,
            'expenses' => $expenseTotal,
            'net_profit' => $netProfit,
            'transaction_count' => $transactionCount,
            'average_basket' => $transactionCount > 0 ? round($revenue / $transactionCount, 2) : 0,
            'items_sold' => $itemsSold,
            'average_items' => $transactionCount > 0 ? round($itemsSold / $transactionCount, 1) : 0,
            'unique_customers' => $uniqueCustomers,
            'member_transactions' => $memberTransactions,
            'member_rate' => $transactionCount > 0 ? round(($memberTransactions / $transactionCount) * 100, 1) : 0,
            'repeat_customer_rate' => $uniqueCustomers > 0 ? round(($repeatCustomers / $uniqueCustomers) * 100, 1) : 0,
            'gross_margin' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 1) : 0,
            'net_margin' => $revenue > 0 ? round(($netProfit / $revenue) * 100, 1) : 0,
            'expense_ratio' => $revenue > 0 ? round(($expenseTotal / $revenue) * 100, 1) : 0,
            'tax_collected' => (float) $transactions->sum('tax_amount'),
            'service_charge' => (float) $transactions->sum('service_charge_amount'),
            'discount_total' => (float) $transactions->sum('discount_amount'),
            'revenue_per_day' => $days > 0 ? round($revenue / $days, 2) : 0,
        ];
    }

    /** @return array<string, float|int> */
    private function aggregatePeriod(Outlet $outlet, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $transactionQuery = Transaction::query()
            ->forOutlet($outlet)
            ->where('status', 'paid')
            ->whereBetween('transacted_at', [$start, $end]);

        $row = (clone $transactionQuery)
            ->selectRaw('COALESCE(SUM(grand_total), 0) revenue, COALESCE(SUM(gross_profit), 0) gross_profit, COALESCE(SUM(tax_amount), 0) tax_collected, COUNT(*) transaction_count')
            ->first();
        $expenses = (float) Expense::query()
            ->forOutlet($outlet)
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');
        $revenue = (float) ($row->revenue ?? 0);
        $grossProfit = (float) ($row->gross_profit ?? 0);
        $transactionCount = (int) ($row->transaction_count ?? 0);

        return [
            'revenue' => $revenue,
            'gross_profit' => $grossProfit,
            'expenses' => $expenses,
            'net_profit' => $grossProfit - $expenses,
            'transaction_count' => $transactionCount,
            'average_basket' => $transactionCount > 0 ? round($revenue / $transactionCount, 2) : 0,
            'gross_margin' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 1) : 0,
            'net_margin' => $revenue > 0 ? round((($grossProfit - $expenses) / $revenue) * 100, 1) : 0,
            'tax_collected' => (float) ($row->tax_collected ?? 0),
        ];
    }

    /** @return array<string, float> */
    private function comparison(array $current, array $previous): array
    {
        return [
            'revenue' => $this->growth((float) $current['revenue'], (float) $previous['revenue']),
            'gross_profit' => $this->growth((float) $current['gross_profit'], (float) $previous['gross_profit']),
            'net_profit' => $this->growth((float) $current['net_profit'], (float) $previous['net_profit']),
            'transaction_count' => $this->growth((float) $current['transaction_count'], (float) $previous['transaction_count']),
            'average_basket' => $this->growth((float) $current['average_basket'], (float) $previous['average_basket']),
            'items_sold' => $this->growth((float) $current['items_sold'], (float) $previous['items_sold']),
        ];
    }

    private function growth(float $current, float $previous): float
    {
        if ($previous == 0.0) {
            return $current == 0.0 ? 0.0 : 100.0;
        }

        return round((($current - $previous) / abs($previous)) * 100, 1);
    }

    /** @return Collection<int,array<string,mixed>> */
    private function trend(CarbonImmutable $start, CarbonImmutable $end, Collection $transactions, Collection $expenses): Collection
    {
        $days = (int) $start->startOfDay()->diffInDays($end->startOfDay()) + 1;
        $granularity = $days <= 45 ? 'day' : ($days <= 180 ? 'week' : 'month');
        $buckets = collect();

        if ($granularity === 'day') {
            for ($cursor = $start->startOfDay(); $cursor->lte($end); $cursor = $cursor->addDay()) {
                $key = $cursor->toDateString();
                $buckets->put($key, $this->emptyTrendBucket($cursor, $cursor, $cursor->locale('id')->isoFormat('D MMM')));
            }
        } elseif ($granularity === 'week') {
            for ($cursor = $start->startOfDay(); $cursor->lte($end); $cursor = $cursor->addDays(7)) {
                $bucketEnd = $cursor->addDays(6)->min($end);
                $label = $cursor->locale('id')->isoFormat('D MMM').'–'.$bucketEnd->locale('id')->isoFormat('D MMM');
                $buckets->put($cursor->toDateString(), $this->emptyTrendBucket($cursor, $bucketEnd, $label));
            }
        } else {
            for ($cursor = $start->startOfMonth(); $cursor->lte($end); $cursor = $cursor->addMonth()) {
                $bucketStart = $cursor->max($start);
                $bucketEnd = $cursor->endOfMonth()->min($end);
                $buckets->put($cursor->toDateString(), $this->emptyTrendBucket($bucketStart, $bucketEnd, $cursor->locale('id')->isoFormat('MMM YYYY')));
            }
        }

        $resolveKey = function (CarbonImmutable $date) use ($start, $granularity): string {
            if ($granularity === 'day') {
                return $date->toDateString();
            }
            if ($granularity === 'week') {
                $index = intdiv((int) $start->startOfDay()->diffInDays($date->startOfDay()), 7);
                return $start->startOfDay()->addDays($index * 7)->toDateString();
            }

            return $date->startOfMonth()->toDateString();
        };

        foreach ($transactions as $transaction) {
            $date = CarbonImmutable::instance($transaction->transacted_at)->setTimezone($start->timezone);
            $key = $resolveKey($date);
            if (! $buckets->has($key)) {
                continue;
            }
            $bucket = $buckets->get($key);
            $bucket['revenue'] += (float) $transaction->grand_total;
            $bucket['gross_profit'] += (float) $transaction->gross_profit;
            $bucket['transactions']++;
            $buckets->put($key, $bucket);
        }

        foreach ($expenses as $expense) {
            $date = CarbonImmutable::instance($expense->expense_date)->setTimezone($start->timezone);
            $key = $resolveKey($date);
            if (! $buckets->has($key)) {
                continue;
            }
            $bucket = $buckets->get($key);
            $bucket['expenses'] += (float) $expense->amount;
            $buckets->put($key, $bucket);
        }

        return $buckets->values()->map(function (array $bucket): array {
            $bucket['net_profit'] = $bucket['gross_profit'] - $bucket['expenses'];
            return $bucket;
        });
    }

    /** @return array<string,mixed> */
    private function emptyTrendBucket(CarbonImmutable $start, CarbonImmutable $end, string $label): array
    {
        return [
            'label' => $label,
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'revenue' => 0.0,
            'gross_profit' => 0.0,
            'expenses' => 0.0,
            'net_profit' => 0.0,
            'transactions' => 0,
        ];
    }

    /** @return Collection<int,array<string,mixed>> */
    private function topProducts(Collection $items): Collection
    {
        return $items->groupBy(fn (TransactionItem $item): string => $item->product_name)
            ->map(function (Collection $rows, string $name): array {
                return [
                    'name' => $name,
                    'category' => (string) ($rows->first()->category_name ?? 'Lainnya'),
                    'quantity' => (int) $rows->sum('quantity'),
                    'revenue' => (float) $rows->sum('line_subtotal'),
                    'profit' => (float) $rows->sum('line_profit'),
                ];
            })
            ->sortByDesc('revenue')
            ->take(7)
            ->values();
    }

    /** @return Collection<int,array{label:string,value:float,quantity:int}> */
    private function categoryContribution(Collection $items): Collection
    {
        return $items->groupBy(fn (TransactionItem $item): string => $item->category_name ?: 'Lainnya')
            ->map(fn (Collection $rows, string $label): array => [
                'label' => $label,
                'value' => (float) $rows->sum('line_subtotal'),
                'quantity' => (int) $rows->sum('quantity'),
            ])
            ->sortByDesc('value')
            ->values();
    }

    /** @return Collection<int,array{label:string,value:float,count:int,percentage:float}> */
    private function paymentMix(Collection $transactions): Collection
    {
        $total = max(1, $transactions->count());

        return $transactions->groupBy('payment_method')
            ->map(fn (Collection $rows, string $method): array => [
                'label' => $method,
                'value' => (float) $rows->sum('grand_total'),
                'count' => $rows->count(),
                'percentage' => round(($rows->count() / $total) * 100, 1),
            ])
            ->sortByDesc('value')
            ->values();
    }

    /** @return Collection<int,array{label:string,count:int,revenue:float}> */
    private function peakHours(Collection $transactions): Collection
    {
        return collect(range(7, 22))->map(function (int $hour) use ($transactions): array {
            $rows = $transactions->filter(fn (Transaction $transaction): bool => (int) $transaction->transacted_at->format('G') === $hour);
            return [
                'label' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT).':00',
                'count' => $rows->count(),
                'revenue' => (float) $rows->sum('grand_total'),
            ];
        });
    }

    /** @return list<array{type:string,title:string,message:string}> */
    private function insights(int $lowStockCount, array $summary, array $comparison, Collection $topProducts, Collection $peakHours): array
    {
        $insights = [];
        $insights[] = $lowStockCount > 0
            ? ['type' => 'warning', 'title' => 'Stok perlu perhatian', 'message' => "Ada {$lowStockCount} produk yang telah menyentuh batas minimum."]
            : ['type' => 'success', 'title' => 'Stok terkendali', 'message' => 'Semua menu aktif masih berada di atas batas minimum.'];

        $growth = (float) ($comparison['revenue'] ?? 0);
        $insights[] = $growth >= 0
            ? ['type' => 'success', 'title' => 'Pendapatan bertumbuh', 'message' => 'Pendapatan periode ini naik '.number_format($growth, 1, ',', '.').'% dibanding periode sebelumnya.']
            : ['type' => 'danger', 'title' => 'Pendapatan menurun', 'message' => 'Pendapatan periode ini turun '.number_format(abs($growth), 1, ',', '.').'%. Pertimbangkan promo pada jam sepi.'];

        if ($topProducts->isNotEmpty()) {
            $best = $topProducts->first();
            $insights[] = ['type' => 'info', 'title' => 'Menu terlaris', 'message' => "{$best['name']} menghasilkan omzet tertinggi sebesar Rp ".number_format($best['revenue'], 0, ',', '.').'.'];
        }

        $peak = $peakHours->sortByDesc('count')->first();
        if ($peak && $peak['count'] > 0) {
            $insights[] = ['type' => 'info', 'title' => 'Jam tersibuk', 'message' => "Aktivitas transaksi tertinggi terjadi sekitar pukul {$peak['label']}. Pastikan kesiapan kasir dan stok."];
        }

        if ((float) $summary['expense_ratio'] > 45) {
            $insights[] = ['type' => 'warning', 'title' => 'Rasio biaya tinggi', 'message' => 'Pengeluaran melebihi 45% dari pendapatan periode. Tinjau biaya operasional terbesar.'];
        }

        return array_slice($insights, 0, 4);
    }

    private function periodLabel(CarbonImmutable $start, CarbonImmutable $end): string
    {
        if ($start->isSameDay($end)) {
            return $start->locale('id')->isoFormat('D MMMM YYYY');
        }

        return $start->locale('id')->isoFormat('D MMM YYYY').' – '.$end->locale('id')->isoFormat('D MMM YYYY');
    }
}
