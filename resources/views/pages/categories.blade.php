@extends('layouts.app')

@section('content')
<div id="view-kategori" class="space-y-4">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <h2 class="text-2xl font-bold text-slate-950 tracking-tight">Master Kategori</h2>
              <p class="mt-1 text-sm text-slate-500">Kelola kategori produk dan biaya operasional pada tab yang terpisah.</p>
            </div>
            <button onclick="openCategoryModal()" class="w-full md:w-fit py-3 px-5 bg-[#C00000] hover:bg-[#A00000] text-white text-sm font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
              <i class="bx bx-plus-circle text-lg"></i><span id="categoryAddLabel">Tambah Kategori Produk</span>
            </button>
          </div>
          <div class="bg-white border border-slate-100 rounded-3xl p-5 shadow-sm">
            <div class="mb-5 grid grid-cols-2 gap-2 rounded-2xl bg-slate-100 p-1.5" role="tablist" aria-label="Jenis kategori">
              <button type="button" id="categoryProductTab" role="tab" aria-selected="true" onclick="setCategoryType('product')" class="rounded-xl bg-white px-4 py-3 text-sm font-extrabold text-[#C00000] shadow-sm transition-all">
                <i class="bx bx-package mr-1.5 text-lg align-middle"></i>Kategori Produk
              </button>
              <button type="button" id="categoryExpenseTab" role="tab" aria-selected="false" onclick="setCategoryType('expense')" class="rounded-xl px-4 py-3 text-sm font-bold text-slate-500 transition-all hover:text-slate-800">
                <i class="bx bx-receipt mr-1.5 text-lg align-middle"></i>Kategori Biaya Operasional
              </button>
            </div>
            <div class="relative">
              <i class="bx bx-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
              <input type="text" id="categorySearch" oninput="renderCategories()" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#C00000] focus:bg-white outline-none rounded-xl text-sm transition-all" placeholder="Cari ID CODE atau nama kategori...">
            </div>
          </div>
          <div class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
              <table class="w-full border-collapse text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                  <tr><th class="p-4 pl-6">ID CODE</th><th class="p-4">Nama Kategori</th><th class="p-4 pr-6 text-center">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="categoryTableBody"></tbody>
              </table>
            </div>
            <div id="categoryPagination" class="border-t border-slate-100 px-4 py-3"></div>
          </div>
        </div>
@endsection

@push('modals')
  @include('partials.modals.category')
@endpush
