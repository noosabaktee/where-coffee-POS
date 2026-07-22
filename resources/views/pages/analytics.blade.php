@extends('layouts.app')

@section('content')
<div id="view-analytic" class="space-y-6">
  <section class="rounded-3xl border border-indigo-100 bg-gradient-to-br from-white via-indigo-50/60 to-red-50/70 p-5 shadow-sm md:p-6">
    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
      <div>
        <div class="flex items-center gap-3"><div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-700 to-violet-500 text-2xl text-white shadow-lg shadow-indigo-900/20"><i class="bx bx-bar-chart-square"></i></div><div><h2 class="text-2xl font-extrabold tracking-tight text-slate-950">Analisis Kinerja Bisnis</h2><p class="mt-1 text-sm text-slate-500">Bedah profitabilitas, perilaku pelanggan, kontribusi menu, pembayaran, dan jam operasional.</p></div></div>
      </div>
      <div class="rounded-full border border-indigo-100 bg-white/80 px-3.5 py-2 text-xs font-bold text-indigo-700"><i class="bx bx-calendar mr-1"></i><span id="activePeriodLabel">-</span></div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-3 rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
      <div><label for="periodFrom" class="mb-1.5 block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Dari tanggal</label><input id="periodFrom" type="date" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-50"></div>
      <div><label for="periodTo" class="mb-1.5 block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Sampai tanggal</label><input id="periodTo" type="date" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-50"></div>
      <div class="flex flex-wrap items-end gap-2 sm:col-span-2 xl:col-span-1">
        <button type="button" onclick="setAnalysisPreset('7d')" class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-xs font-bold text-slate-600 hover:border-indigo-200 hover:text-indigo-700">7H</button>
        <button type="button" onclick="setAnalysisPreset('30d')" class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-xs font-bold text-slate-600 hover:border-indigo-200 hover:text-indigo-700">30H</button>
        <button type="button" onclick="setAnalysisPreset('month')" class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-xs font-bold text-slate-600 hover:border-indigo-200 hover:text-indigo-700">Bulan Ini</button>
        <button type="button" onclick="applyDashboardPeriod()" class="rounded-xl bg-gradient-to-r from-indigo-700 to-violet-600 px-5 py-3 text-xs font-extrabold text-white shadow-lg shadow-indigo-900/15"><i class="bx bx-filter-alt mr-1"></i>Analisis</button>
      </div>
    </div>
  </section>

  <section class="grid grid-cols-2 gap-3 lg:grid-cols-4 xl:grid-cols-8">
    <div class="rounded-2xl border border-red-100 bg-red-50/70 p-4"><span class="text-[9px] font-extrabold uppercase text-red-500">Omzet</span><h3 id="anRevenue" class="mt-1 truncate text-lg font-extrabold text-slate-900">Rp 0</h3></div>
    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4"><span class="text-[9px] font-extrabold uppercase text-emerald-600">Laba Bersih</span><h3 id="anNetProfit" class="mt-1 truncate text-lg font-extrabold text-slate-900">Rp 0</h3></div>
    <div class="rounded-2xl border border-amber-100 bg-amber-50/70 p-4"><span class="text-[9px] font-extrabold uppercase text-amber-600">Biaya</span><h3 id="anTotalExpenses" class="mt-1 truncate text-lg font-extrabold text-slate-900">Rp 0</h3></div>
    <div class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-4"><span class="text-[9px] font-extrabold uppercase text-indigo-600">Avg. Basket</span><h3 id="anAvgBasket" class="mt-1 truncate text-lg font-extrabold text-slate-900">Rp 0</h3></div>
    <div class="rounded-2xl border border-slate-100 bg-white p-4"><span class="text-[9px] font-extrabold uppercase text-slate-400">Gross Margin</span><h3 id="anGrossMargin" class="mt-1 text-lg font-extrabold text-red-700">0%</h3></div>
    <div class="rounded-2xl border border-slate-100 bg-white p-4"><span class="text-[9px] font-extrabold uppercase text-slate-400">Net Margin</span><h3 id="anProfitRatio" class="mt-1 text-lg font-extrabold text-emerald-700">0%</h3></div>
    <div class="rounded-2xl border border-slate-100 bg-white p-4"><span class="text-[9px] font-extrabold uppercase text-slate-400">Transaksi Member</span><h3 id="anMemberRate" class="mt-1 text-lg font-extrabold text-indigo-700">0%</h3></div>
    <div class="rounded-2xl border border-slate-100 bg-white p-4"><span class="text-[9px] font-extrabold uppercase text-slate-400">Repeat Customer</span><h3 id="anRepeatRate" class="mt-1 text-lg font-extrabold text-violet-700">0%</h3></div>
  </section>

  <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm md:p-6 xl:col-span-2"><div class="mb-5"><h3 class="font-extrabold text-slate-950">Cashflow & Profitability Trend</h3><p class="text-xs text-slate-400">Pendapatan, laba kotor, biaya, dan laba bersih pada periode aktif</p></div><div class="relative h-80"><canvas id="cashflowChart"></canvas></div></div>
    <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm md:p-6"><div class="mb-4"><h3 class="font-extrabold text-slate-950">Kontribusi Kategori</h3><p class="text-xs text-slate-400">Persentase omzet per kelompok menu</p></div><div class="relative h-72"><canvas id="categoryChart"></canvas></div></div>
  </section>

  <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm md:p-6"><div class="mb-4"><h3 class="font-extrabold text-slate-950">Ranking Menu Berdasarkan Omzet</h3><p class="text-xs text-slate-400">Gunakan data ini untuk bundling, promo, dan prioritas stok</p></div><div class="relative h-72"><canvas id="topProductsChart"></canvas></div></div>
    <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm md:p-6"><div class="mb-4"><h3 class="font-extrabold text-slate-950">Pola Transaksi per Jam</h3><p class="text-xs text-slate-400">Identifikasi jam sibuk untuk penjadwalan staff</p></div><div class="relative h-72"><canvas id="peakHoursChart"></canvas></div></div>
  </section>

  <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm md:p-6"><h3 class="font-extrabold text-slate-950">Metode Pembayaran</h3><div class="relative mt-4 h-60"><canvas id="analyticsPaymentChart"></canvas></div></div>
    <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm md:p-6 lg:col-span-2">
      <div class="mb-4 flex items-center justify-between"><div><h3 class="font-extrabold text-slate-950">Operational Ratios</h3><p class="text-xs text-slate-400">Indikator efisiensi untuk keputusan manajerial</p></div><i class="bx bx-pulse text-2xl text-indigo-500"></i></div>
      <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <div class="rounded-2xl bg-slate-50 p-4"><span class="text-[9px] font-bold uppercase text-slate-400">Expense Ratio</span><div id="anExpenseRatio" class="mt-1 text-xl font-extrabold text-slate-900">0%</div></div>
        <div class="rounded-2xl bg-slate-50 p-4"><span class="text-[9px] font-bold uppercase text-slate-400">Item / Transaksi</span><div id="anAverageItems" class="mt-1 text-xl font-extrabold text-slate-900">0</div></div>
        <div class="rounded-2xl bg-slate-50 p-4"><span class="text-[9px] font-bold uppercase text-slate-400">Pajak Terkumpul</span><div id="anTaxCollected" class="mt-1 truncate text-xl font-extrabold text-slate-900">Rp 0</div></div>
        <div class="rounded-2xl bg-slate-50 p-4"><span class="text-[9px] font-bold uppercase text-slate-400">Diskon Diberikan</span><div id="anDiscountTotal" class="mt-1 truncate text-xl font-extrabold text-slate-900">Rp 0</div></div>
      </div>
      <div id="analyticsInsightList" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2"></div>
    </div>
  </section>
</div>
@endsection
