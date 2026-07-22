<div id="invoiceModal" class="fixed inset-0 z-[10001] hidden overflow-y-auto bg-black/55 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="invoiceModalTitle" onclick="handleInvoiceBackdrop(event)">
  <div class="min-h-full flex items-start md:items-center justify-center py-2 md:py-6">
    <div data-invoice-panel class="relative w-full max-w-md max-h-[calc(100dvh-2rem)] overflow-y-auto rounded-3xl border border-slate-100 bg-white p-5 md:p-6 shadow-2xl" onclick="event.stopPropagation()">
      <div class="sticky top-0 z-10 -mx-1 mb-3 flex items-center justify-between rounded-2xl bg-white/95 px-1 py-1 backdrop-blur">
        <div>
          <h3 id="invoiceModalTitle" class="font-extrabold text-slate-950">Pembayaran Berhasil</h3>
          <p class="text-[10px] text-slate-400">Struk transaksi siap dicetak.</p>
        </div>
        <button type="button" onclick="closeInvoiceModal()" class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-red-50 hover:text-red-600" aria-label="Tutup invoice"><i class="bx bx-x text-2xl"></i></button>
      </div>

      <div id="printableReceipt" class="space-y-4 rounded-2xl border border-dashed border-slate-300 bg-[#fcfbfa] p-3 text-xs font-mono text-slate-800">
        <div class="text-center space-y-1">
          <div id="recLogoContainer" class="flex justify-center mb-1"></div>
          <h4 class="font-bold text-sm tracking-wide" id="recStoreName">WHERE COFFEE</h4>
          <p id="recOutlet">Cabang: Utama</p>
          <p id="recStoreAddress">Alamat</p>
          <p id="recStorePhone">Telp:</p>
          <div class="border-t border-dashed border-slate-400 my-2"></div>
        </div>
        <div class="space-y-1 text-[11px]">
          <div class="flex justify-between gap-4"><span>No:</span><span id="recTxId" class="text-right">TXT-0000</span></div>
          <div class="flex justify-between"><span>Metode:</span><span id="recPayMode">Tunai</span></div>
          <div class="flex justify-between"><span>Waktu:</span><span id="recTime">10/07/2026 12:00</span></div>
        </div>
        <div class="border-t border-dashed border-slate-400 my-2"></div>
        <div class="space-y-2 text-[11px]" id="recItemsList"></div>
        <div class="border-t border-dashed border-slate-400 my-2"></div>
        <div class="space-y-1 text-[11px]">
          <div class="flex justify-between"><span>Subtotal:</span><span id="recSubtotal">Rp 0</span></div>
          <div class="flex justify-between"><span>Diskon:</span><span id="recDiscount">0%</span></div>
          <div id="recExtraCalculations" class="space-y-1"></div>
          <div class="flex justify-between font-bold text-sm text-[#C00000]"><span>TOTAL:</span><span id="recTotal">Rp 0</span></div>
          <div class="border-t border-dashed border-slate-400 my-1"></div>
          <div class="flex justify-between"><span>Bayar:</span><span id="recPay">Rp 0</span></div>
          <div class="flex justify-between font-bold"><span>Kembali:</span><span id="recChange">Rp 0</span></div>
        </div>
        <div class="text-center pt-3 text-[10px] space-y-1 text-slate-500"><p class="font-medium">Terima Kasih Atas Kunjungan Anda</p></div>
      </div>

      <div class="sticky bottom-0 -mx-1 mt-4 grid grid-cols-1 gap-2 rounded-2xl bg-white/95 px-1 py-2 backdrop-blur sm:grid-cols-2">
        <button type="button" onclick="window.print()" class="py-3 bg-[#C00000] hover:bg-[#A00000] text-white font-bold rounded-xl text-sm flex items-center justify-center gap-2"><i class="bx bx-printer text-lg"></i><span>Cetak</span></button>
        <button type="button" onclick="closeInvoiceModal()" class="py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm">Tutup</button>
      </div>
    </div>
  </div>
</div>
