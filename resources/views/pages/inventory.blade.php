@extends('layouts.app')

@section('content')
<div id="view-inventori" class="space-y-4">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <h2 class="text-2xl font-bold text-slate-950 tracking-tight">Manajemen Inventori</h2>
              <p class="text-sm text-slate-500">Pantau stok kedaimu secara real-time.</p>
            </div>
            <button onclick="openProductModal()" class="w-full md:w-fit py-3 px-5 bg-[#C00000] hover:bg-[#A00000] text-white text-sm font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
              <i class="bx bx-plus-circle text-lg"></i><span>Tambah Menu Baru</span>
            </button>
          </div>

          <div class="bg-white border border-slate-100 rounded-3xl p-5 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="relative md:col-span-2">
              <i class="bx bx-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
              <input type="text" id="inventorySearch" oninput="renderInventory()" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#C00000] focus:bg-white outline-none rounded-xl text-sm transition-all" placeholder="Cari ID SKU, barcode, nama menu...">
            </div>
            <select id="inventoryCategoryFilter" onchange="renderInventory()" class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 focus:border-[#C00000] outline-none w-full">
              <option value="">Semua Kategori</option>
            </select>
            <select id="inventoryStatusFilter" onchange="renderInventory()" class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 focus:border-[#C00000] outline-none w-full">
              <option value="active" selected>Produk Aktif</option>
              <option value="inactive">Produk Nonaktif</option>
              <option value="all">Semua Status</option>
            </select>
          </div>

          <div class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
              <table class="w-full border-collapse text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                  <tr>
                    <th class="p-4 pl-6">ID SKU / Barcode</th><th class="p-4">Nama Menu</th><th class="p-4">Kategori</th>
                    <th class="p-4 text-right">Modal</th><th class="p-4 text-right">Harga Jual</th>
                    <th class="p-4 text-center">Sisa</th><th class="p-4 text-center">Status Stok</th>
                    <th class="p-4 text-center">Status Produk</th><th class="p-4 pr-6 text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="inventoryTableBody"></tbody>
              </table>
            </div>
            <div id="inventoryPagination" class="border-t border-slate-100 px-4 py-3"></div>
            <div id="inventoryEmptyState" class="hidden p-12 text-center flex flex-col items-center">
              <i class="bx bx-box text-5xl text-slate-300 mb-3"></i>
              <h4 class="font-bold text-slate-800 text-base">Belum Ada Menu</h4>
            </div>
          </div>
        </div>
@endsection

@push('modals')
  @include('partials.modals.product')
@endpush
