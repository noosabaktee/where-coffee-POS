@extends('layouts.app')

@section('content')
<div id="view-dashboard" class="space-y-6">
  <section class="relative overflow-hidden rounded-3xl border border-red-100 bg-gradient-to-br from-white via-red-50/70 to-amber-50 p-5 shadow-sm md:p-6">
    <div class="pointer-events-none absolute -right-12 -top-16 h-48 w-48 rounded-full bg-red-200/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-20 left-1/3 h-40 w-40 rounded-full bg-amber-200/30 blur-3xl"></div>
    <div class="relative flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
      <div>
        <div class="flex items-center gap-3">
          <div class="relative flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-red-700 to-red-500 text-white shadow-lg shadow-red-900/20">
            <i class="bx bxs-coffee text-2xl"></i>
            <span class="steam-line absolute left-5 top-0 h-4 w-1 rounded-full border-l-2 border-red-100"></span>
            <span class="steam-line absolute left-7 top-0 h-4 w-1 rounded-full border-l-2 border-amber-100"></span>
          </div>
          <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-950">Ringkasan Bisnis Where Coffee</h2>
            <p class="mt-1 text-sm text-slate-500">Pantau omzet, profit, biaya, tren pelanggan, dan performa menu dalam satu tempat.</p>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3.5 py-2 text-xs text-emerald-800">
        <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-70"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-600"></span></span>
        <span class="font-bold" id="syncStatus">Menyinkronkan outlet...</span>
      </div>
    </div>

    <div class="relative mt-6 rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur">
      <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-2 xl:max-w-2xl">
          <div>
            <label for="periodFrom" class="mb-1.5 block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Dari tanggal</label>
            <input id="periodFrom" type="date" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-red-300 focus:bg-white focus:ring-4 focus:ring-red-50">
          </div>
          <div>
            <label for="periodTo" class="mb-1.5 block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Sampai tanggal</label>
            <input id="periodTo" type="date" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-red-300 focus:bg-white focus:ring-4 focus:ring-red-50">
          </div>
        </div>
        <div class="flex flex-wrap gap-2">
          <button type="button" onclick="setAnalysisPreset('7d')" class="period-preset rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-bold text-slate-600 hover:border-red-200 hover:text-red-600">7 Hari</button>
          <button type="button" onclick="setAnalysisPreset('30d')" class="period-preset rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-bold text-slate-600 hover:border-red-200 hover:text-red-600">30 Hari</button>
          <button type="button" onclick="setAnalysisPreset('month')" class="period-preset rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-bold text-slate-600 hover:border-red-200 hover:text-red-600">Bulan Ini</button>
          <button type="button" onclick="setAnalysisPreset('90d')" class="period-preset rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-bold text-slate-600 hover:border-red-200 hover:text-red-600">90 Hari</button>
          <button type="button" onclick="applyDashboardPeriod()" class="rounded-xl bg-gradient-to-r from-red-700 to-red-600 px-5 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-red-900/15 transition hover:-translate-y-0.5 hover:shadow-xl">
            <i class="bx bx-filter-alt mr-1"></i>Terapkan Filter
          </button>
        </div>
      </div>
      <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
        <p class="text-xs text-slate-500">Periode aktif: <strong id="activePeriodLabel" class="text-slate-800">-</strong></p>
        <p class="text-[11px] text-slate-400">Dibandingkan otomatis dengan periode sebelumnya yang berdurasi sama.</p>
      </div>
    </div>
  </section>

  <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <article class="color-card-red accent-bar rounded-3xl border p-5 shadow-sm" style="--accent:#dc2626">
      <div class="flex items-start justify-between gap-3">
        <div><span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Pendapatan Periode</span><h3 id="periodRevenue" class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">Rp 0</h3></div>
        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-red-100 text-xl text-red-600"><i class="bx bx-line-chart"></i></div>
      </div>
      <p id="periodRevenueGrowth" class="mt-3 text-xs font-bold text-slate-500">0% dari periode sebelumnya</p>
    </article>
    <article class="color-card-emerald accent-bar rounded-3xl border p-5 shadow-sm" style="--accent:#059669">
      <div class="flex items-start justify-between gap-3">
        <div><span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Laba Bersih Periode</span><h3 id="periodNetProfit" class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">Rp 0</h3></div>
        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-xl text-emerald-600"><i class="bx bx-wallet-alt"></i></div>
      </div>
      <p id="periodNetProfitGrowth" class="mt-3 text-xs font-bold text-slate-500">0% dari periode sebelumnya</p>
    </article>
    <article class="color-card-amber accent-bar rounded-3xl border p-5 shadow-sm" style="--accent:#d97706">
      <div class="flex items-start justify-between gap-3">
        <div><span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Transaksi Periode</span><h3 id="periodTransactionCount" class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">0</h3></div>
        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-xl text-amber-600"><i class="bx bx-receipt"></i></div>
      </div>
      <p id="periodTransactionGrowth" class="mt-3 text-xs font-bold text-slate-500">0% dari periode sebelumnya</p>
    </article>
    <article class="color-card-indigo accent-bar rounded-3xl border p-5 shadow-sm" style="--accent:#4f46e5">
      <div class="flex items-start justify-between gap-3">
        <div><span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Rata-rata Belanja</span><h3 id="periodAverageBasket" class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">Rp 0</h3></div>
        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-100 text-xl text-indigo-600"><i class="bx bx-basket"></i></div>
      </div>
      <p id="periodAverageGrowth" class="mt-3 text-xs font-bold text-slate-500">0% dari periode sebelumnya</p>
    </article>
  </section>

  <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm"><span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Omzet Bulan Ini</span><div id="monthRevenue" class="mt-1 truncate text-lg font-extrabold text-red-700">Rp 0</div><div id="monthRevenueGrowth" class="mt-1 text-[10px] font-bold text-slate-400">vs bulan lalu</div></div>
    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm"><span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Laba Bersih Bulan Ini</span><div id="monthNetProfit" class="mt-1 truncate text-lg font-extrabold text-emerald-700">Rp 0</div><div id="monthNetMargin" class="mt-1 text-[10px] font-bold text-slate-400">Margin 0%</div></div>
    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm"><span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Proyeksi Akhir Bulan</span><div id="monthProjection" class="mt-1 truncate text-lg font-extrabold text-indigo-700">Rp 0</div><div id="monthProjectionInfo" class="mt-1 text-[10px] font-bold text-slate-400">Berdasarkan run rate</div></div>
    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm"><span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Barang Terjual Periode</span><div id="periodItemsSold" class="mt-1 text-lg font-extrabold text-amber-700">0 item</div><div id="periodMemberRate" class="mt-1 text-[10px] font-bold text-slate-400">0% transaksi member</div></div>
  </section>

  <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm md:p-6 xl:col-span-2">
      <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div><h3 class="font-extrabold text-slate-950">Tren Pendapatan, Profit & Biaya</h3><p class="text-xs text-slate-400">Performa berdasarkan periode yang dipilih</p></div>
        <span id="trendGranularityLabel" class="w-fit rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold text-slate-500">Tren harian</span>
      </div>
      <div class="relative h-72"><canvas id="salesChart"></canvas></div>
    </div>

    <div class="flex flex-col rounded-3xl border border-slate-100 bg-white p-5 shadow-sm md:p-6">
      <div class="mb-4 flex items-center justify-between"><div><h3 class="font-extrabold text-slate-950">Alarm Stok Menipis</h3><p class="text-xs text-slate-400">Produk yang perlu segera diisi</p></div><span id="lowStockBadge" class="rounded-lg bg-red-50 px-2.5 py-1 text-[10px] font-extrabold uppercase text-red-800">0 Produk</span></div>
      <div id="dashLowStockList" class="max-h-[230px] flex-1 space-y-3 overflow-y-auto pr-1"></div>
      <button onclick="changeView('inventori')" class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100"><span>Kelola Inventori</span><i class="bx bx-chevron-right text-lg"></i></button>
    </div>
  </section>

  <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm md:p-6">
      <div class="mb-5 flex items-center justify-between"><div><h3 class="font-extrabold text-slate-950">Menu Terlaris</h3><p class="text-xs text-slate-400">Diurutkan berdasarkan kontribusi omzet</p></div><i class="bx bx-trophy text-2xl text-amber-500"></i></div>
      <div id="dashboardTopProducts" class="space-y-3"></div>
    </div>
    <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm md:p-6">
      <div class="mb-4"><h3 class="font-extrabold text-slate-950">Komposisi Pembayaran</h3><p class="text-xs text-slate-400">Metode pembayaran yang digunakan pelanggan</p></div>
      <div class="relative h-60"><canvas id="paymentMixChart"></canvas></div>
    </div>
  </section>

  <section class="rounded-3xl border border-red-100 bg-gradient-to-r from-red-50 via-white to-amber-50 p-5 shadow-sm md:p-6">
    <div class="flex items-center gap-3">
      <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-red-700 to-red-500 text-xl text-white shadow-lg shadow-red-900/15"><i class="bx bx-bulb animate-pulse"></i></div>
      <div><h3 class="font-extrabold text-slate-900">Smart Business Insights</h3><p class="text-xs text-slate-500">Rekomendasi otomatis berdasarkan periode aktif, tren penjualan, stok, dan biaya.</p></div>
    </div>
    <div id="proactiveInsightsList" class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2"></div>
  </section>
</div>
@endsection
