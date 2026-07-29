<!-- EXPENSE MODAL -->
  <div id="expenseModal" class="fixed inset-0 z-[100] bg-black/40 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-100 flex flex-col overflow-hidden max-h-[90vh]">
      <div class="p-6 border-b border-slate-100 flex justify-between items-center">
        <h3 id="expenseModalTitle" class="font-bold text-slate-900 text-lg">Catat Pengeluaran</h3>
        <button onclick="closeExpenseModal()" class="p-2 text-slate-400 hover:bg-slate-50 rounded-xl"><i class="bx bx-x text-2xl"></i></button>
      </div>
      <form id="expenseForm" onsubmit="saveExpense(event)" class="p-6 overflow-y-auto space-y-4">
        <input type="hidden" id="expId">
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">ID CODE</label>
          <input type="text" id="expCode" required maxlength="40" autocomplete="off" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm uppercase" placeholder="Contoh: EXP-UTAMA-260729-AB12">
          <p class="mt-1.5 text-[11px] text-slate-400">Dibuat otomatis saat menambah biaya dan tetap bisa diubah.</p>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Kategori</label>
          <select id="expCategory" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm">
            <option value="Bahan Baku">Bahan Baku</option>
            <option value="Utilitas">Utilitas (Listrik/Air)</option>
            <option value="Gaji Karyawan">Gaji Karyawan</option>
            <option value="Promosi & Marketing">Promosi</option>
            <option value="Lain-lain">Lain-lain</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Deskripsi Biaya</label>
          <input type="text" id="expDesc" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Jumlah (Rp)</label>
          <input type="text" inputmode="numeric" data-number-format data-min="1" id="expAmount" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm">
        </div>
        <button type="submit" class="w-full py-3.5 bg-[#C00000] hover:bg-[#A00000] text-white text-sm font-bold rounded-xl flex items-center justify-center gap-2">
          <i class="bx bx-save text-lg"></i><span>Simpan Biaya</span>
        </button>
      </form>
    </div>
  </div>
