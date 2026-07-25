@extends('layouts.app')

@section('content')
<div id="view-pos" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
  <div class="lg:col-span-7 lg:sticky lg:top-0 bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
    <div class="flex flex-col md:flex-row gap-3">
      <div class="relative flex-1">
        <i class="bx bx-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
        <input type="text" id="posSearch" oninput="renderPOS()" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#C00000] focus:bg-white outline-none rounded-xl text-sm transition-all" placeholder="Cari nama kopi...">
      </div>
      <select id="posFilterCategory" onchange="renderPOS()" class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 focus:border-[#C00000] outline-none">
        <option value="">Semua Kategori</option>
      </select>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 overflow-y-auto max-h-[60vh] md:max-h-[600px] pr-1" id="posProductsGrid"></div>
  </div>

  <div class="lg:col-span-5 lg:sticky lg:top-0 bg-white border border-slate-100 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
    <div>
      <div class="flex items-center justify-between pb-4 border-b border-slate-100">
        <h3 class="font-bold text-slate-950 text-base">Keranjang Transaksi</h3>
        <button type="button" onclick="clearCart()" class="text-xs text-red-500 font-semibold hover:underline">Hapus Semua</button>
      </div>
      <div class="divide-y divide-slate-100 overflow-y-auto max-h-[30vh] md:max-h-[250px] pr-1" id="cartList"></div>
    </div>

    <div class="bg-slate-50/50 p-4 border border-slate-200/60 rounded-2xl mb-2 mt-4">
      <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5 flex justify-between items-center">
        <span>Pelanggan / Member</span>
        <span id="memberBonusInfo" class="text-[#C00000] font-bold truncate max-w-[120px]"></span>
      </label>
      <div class="flex gap-2 items-start">
        <div class="relative flex-1 min-w-0" id="memberSearchWrapper">
          <i class="bx bx-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base pointer-events-none"></i>
          <input type="search" id="posCustomerSearch" autocomplete="off" oninput="searchPOSCustomers()" onfocus="openPOSCustomerResults()" onkeydown="handlePOSCustomerSearchKeydown(event)" class="w-full pl-10 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:border-[#C00000] focus:ring-4 focus:ring-red-100 outline-none transition-all" placeholder="Ketik nama / nomor member..." aria-label="Cari pelanggan atau member" aria-controls="posCustomerResults" aria-expanded="false">
          <button type="button" id="clearSelectedCustomerBtn" onclick="clearSelectedPOSCustomer()" class="hidden absolute right-2 top-1/2 -translate-y-1/2 w-7 h-7 items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600" title="Hapus member"><i class="bx bx-x text-lg"></i></button>
          <div id="posCustomerResults" class="member-results hidden absolute z-50 left-0 right-0 top-[calc(100%+8px)] max-h-72 overflow-y-auto rounded-2xl border border-rose-100 bg-white p-2"></div>
        </div>
        <button type="button" onclick="toggleUseCustomerPoints()" id="usePointsBtn" class="px-3 py-2.5 bg-slate-200 text-slate-600 font-bold text-xs rounded-xl hover:bg-red-50 hover:text-[#C00000] transition-all hidden whitespace-nowrap">Pakai Poin</button>
      </div>
      <div id="selectedCustomerSummary" class="hidden mt-2 rounded-xl border border-emerald-100 bg-emerald-50/80 px-3 py-2 text-[10px] text-emerald-800"></div>
    </div>

    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 space-y-4">
      <div class="space-y-2">
        <div class="flex justify-between text-sm text-slate-500"><span>Subtotal</span><span class="font-semibold text-slate-800" id="cartSubtotal">Rp 0</span></div>
        <div class="flex justify-between items-center text-sm text-slate-500">
          <span>Diskon (%)</span>
          <input type="text" inputmode="numeric" data-number-format data-min="0" data-max="100" id="cartDiscount" oninput="calculateCartTotal()" class="w-20 px-2 py-1 bg-white border border-slate-200 rounded-lg text-center text-sm font-semibold outline-none" value="0">
        </div>
        <div class="flex justify-between text-sm text-slate-500"><span id="cartServiceLabel">Service Charge (0%)</span><span class="font-semibold text-slate-700" id="cartServiceAmount">Rp 0</span></div>
        <div class="flex justify-between text-sm text-slate-500"><span id="cartTaxLabel">Pajak (0%)</span><span class="font-semibold text-slate-700" id="cartTaxAmount">Rp 0</span></div>
        <div id="cartPointsRow" class="hidden flex justify-between text-sm text-emerald-700"><span>Potongan Poin</span><span class="font-bold" id="cartPointsDiscount">-Rp 0</span></div>
        <div class="flex justify-between text-base font-bold text-slate-950 border-t border-slate-200/60 pt-3"><span>Total Tagihan</span><span id="cartTotal" class="text-red-600 text-lg">Rp 0</span></div>
      </div>

      <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Metode Pembayaran</label>
        <div class="grid grid-cols-2 gap-2">
          <button type="button" id="payModeCash" onclick="setPaymentMode('Tunai')" class="py-2.5 px-3 bg-[#C00000] text-white font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 transition-all"><i class="bx bx-money"></i> Tunai</button>
          <button type="button" id="payModeQRIS" onclick="setPaymentMode('QRIS')" class="py-2.5 px-3 bg-white border border-slate-200 text-slate-600 font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 transition-all"><i class="bx bx-qr"></i> QRIS</button>
        </div>
      </div>

      <div id="cashPayArea" class="grid grid-cols-2 gap-2">
        <input type="text" inputmode="numeric" data-number-format data-min="0" id="cashInput" placeholder="Bayar tunai..." class="col-span-2 w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 outline-none focus:border-[#C00000]" oninput="calculateChange()">
        <div class="col-span-2 flex justify-between items-center text-xs text-slate-500 bg-red-50/50 p-2.5 rounded-lg border border-red-100"><span class="font-medium text-slate-600">Kembalian</span><span class="font-bold text-red-700 text-sm truncate" id="cashChange">Rp 0</span></div>
      </div>

      <div id="qrisPayArea" class="hidden flex flex-col items-center bg-white p-3 rounded-2xl border border-slate-200 text-center space-y-2">
        <div class="text-[10px] font-bold text-slate-400">SCAN QRIS STATIS</div>
        <div id="qrisContainer" class="w-44 h-44 bg-white rounded-xl flex items-center justify-center overflow-hidden border border-slate-100">
          <button type="button" onclick="openQrisModal()" class="h-full w-full rounded-xl bg-white p-2 transition-all hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-red-100" aria-label="Perbesar QRIS untuk scan">
            <img src="{{ asset('images/qris/where-coffee-qris.png') }}" class="h-full w-full object-contain" alt="QRIS statis Where Coffee">
          </button>
        </div>
      </div>

      <button type="button" onclick="confirmCheckout()" class="w-full py-3.5 bg-[#C00000] hover:bg-[#A00000] active:scale-[0.98] transition-all text-white text-sm font-bold rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-red-900/10"><i class="bx bx-check-shield text-lg"></i><span>Selesaikan Pembayaran</span></button>
    </div>
  </div>
</div>
@endsection

@push('modals')
  @include('partials.modals.invoice')
  <div id="qrisPreviewModal" class="fixed inset-0 z-[10002] hidden overflow-y-auto bg-black/70 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="qrisPreviewTitle" onclick="handleQrisModalBackdrop(event)">
    <div class="min-h-full flex items-center justify-center">
      <div data-qris-preview-panel class="relative w-full max-w-lg rounded-3xl border border-white/20 bg-white p-4 shadow-2xl" onclick="event.stopPropagation()">
        <div class="mb-3 flex items-center justify-between gap-3">
          <div>
            <h3 id="qrisPreviewTitle" class="text-sm font-extrabold text-slate-950">Scan QRIS</h3>
            <p class="text-[10px] font-medium text-slate-400">Perbesar layar ini untuk memudahkan pembayaran.</p>
          </div>
          <button type="button" onclick="closeQrisModal()" class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-red-50 hover:text-red-600" aria-label="Tutup QRIS"><i class="bx bx-x text-2xl"></i></button>
        </div>
        <div class="aspect-square w-full overflow-hidden rounded-2xl border border-slate-100 bg-white p-4">
          <img id="qrisPreviewImage" class="h-full w-full object-contain" alt="QRIS statis Where Coffee">
        </div>
      </div>
    </div>
  </div>
@endpush
