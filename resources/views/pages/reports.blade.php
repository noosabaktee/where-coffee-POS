@extends('layouts.app')

@section('content')
<div id="view-laporan" class="space-y-6">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <h2 class="text-2xl font-bold text-slate-950 tracking-tight">Laporan Keuangan & Riwayat</h2>
            </div>
            <button onclick="exportToExcel()" class="w-full md:w-fit py-3 px-4 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-semibold rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
              <i class="bx bx-spreadsheet text-lg"></i><span>Ekspor Excel</span>
            </button>
          </div>
          <div class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row gap-4 items-center justify-between">
              <span class="font-bold text-slate-900 text-base">Riwayat Transaksi</span>
              <div class="relative w-full md:w-64">
                <i class="bx bx-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                <input type="text" id="reportSearch" oninput="renderReport()" class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 focus:border-[#C00000] focus:bg-white outline-none rounded-xl text-xs transition-all" placeholder="Cari ID transaksi...">
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full border-collapse text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                  <tr>
                    <th class="p-4 pl-6">ID Transaksi</th><th class="p-4">Tanggal & Waktu</th><th class="p-4">Rincian Barang</th>
                    <th class="p-4 text-right">Subtotal</th><th class="p-4 text-center">Diskon</th><th class="p-4 text-right">Total Akhir</th>
                    <th class="p-4 text-right">Profit Bersih</th><th class="p-4 pr-6 text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="reportTableBody"></tbody>
              </table>
            </div>
            <div id="reportPagination" class="border-t border-slate-100 px-4 py-3"></div>
          </div>
        </div>
@endsection

@push('modals')
  @include('partials.modals.invoice')
@endpush
