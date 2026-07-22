@extends('layouts.app')

@section('content')
<div id="view-outlets" class="space-y-6">
          <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-red-700 via-red-600 to-orange-500 p-6 text-white shadow-xl shadow-red-900/15">
            <div class="absolute -right-8 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute right-20 bottom-[-60px] h-36 w-36 rounded-full bg-amber-300/20"></div>
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-wider mb-3"><i class="bx bx-shield-quarter"></i> Administrator Area</div>
                <h2 class="text-2xl font-extrabold tracking-tight">Pengelolaan Toko Cabang</h2>
                <p class="text-sm text-red-50 mt-1">Tambah, ubah, aktifkan, nonaktifkan, atau hapus cabang yang belum memiliki transaksi.</p>
              </div>
              <button onclick="openOutletModal()" class="w-full md:w-fit py-3 px-5 bg-white text-red-700 hover:bg-red-50 text-sm font-extrabold rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                <i class="bx bx-plus-circle text-lg"></i><span>Tambah Cabang</span>
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="color-card-red accent-bar border rounded-3xl p-5 shadow-sm" style="--accent:#dc2626">
              <div class="flex items-center justify-between"><div><span class="text-[10px] font-bold text-slate-400 uppercase">Total Cabang</span><h3 id="outletTotalCount" class="text-2xl font-extrabold text-slate-950 mt-1">0</h3></div><div class="w-11 h-11 rounded-2xl bg-red-100 text-red-700 flex items-center justify-center text-xl"><i class="bx bx-store"></i></div></div>
            </div>
            <div class="color-card-emerald accent-bar border rounded-3xl p-5 shadow-sm" style="--accent:#059669">
              <div class="flex items-center justify-between"><div><span class="text-[10px] font-bold text-slate-400 uppercase">Cabang Aktif</span><h3 id="outletActiveCount" class="text-2xl font-extrabold text-emerald-700 mt-1">0</h3></div><div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl"><i class="bx bx-check-shield"></i></div></div>
            </div>
            <div class="color-card-indigo accent-bar border rounded-3xl p-5 shadow-sm" style="--accent:#4f46e5">
              <div class="flex items-center justify-between"><div><span class="text-[10px] font-bold text-slate-400 uppercase">Total Staff</span><h3 id="outletStaffCount" class="text-2xl font-extrabold text-indigo-700 mt-1">0</h3></div><div class="w-11 h-11 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl"><i class="bx bx-group"></i></div></div>
            </div>
          </div>

          <div class="bg-white/90 backdrop-blur border border-rose-100 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-rose-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
              <div><h3 class="font-extrabold text-slate-950">Daftar Toko & Cabang</h3><p class="text-xs text-slate-400">Cabang aktif dapat dipilih dari sidebar untuk melihat data operasionalnya.</p></div>
              <div class="relative w-full md:w-72"><i class="bx bx-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i><input id="outletSearch" oninput="renderOutlets()" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-red-400" placeholder="Cari nama, kode, atau alamat..."></div>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full border-collapse text-left text-sm">
                <thead class="bg-gradient-to-r from-red-50 to-orange-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-rose-100">
                  <tr><th class="p-4 pl-6">Cabang</th><th class="p-4">Kontak & Alamat</th><th class="p-4 text-center">Staff</th><th class="p-4 text-center">Produk</th><th class="p-4 text-center">Transaksi</th><th class="p-4 text-center">Status</th><th class="p-4 pr-6 text-center">Aksi</th></tr>
                </thead>
                <tbody id="outletTableBody" class="divide-y divide-slate-100"></tbody>
              </table>
            </div>
            <div id="outletPagination" class="border-t border-slate-100 px-4 py-3"></div>
          </div>
        </div>
@endsection

@push('modals')
  @include('partials.modals.outlet')
@endpush
