<!-- CUSTOMER MODAL -->
  <div id="customerModal" class="fixed inset-0 z-[100] bg-black/40 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-100 flex flex-col overflow-hidden max-h-[90vh]">
      <div class="p-6 border-b border-slate-100 flex justify-between items-center">
        <h3 id="customerModalTitle" class="font-bold text-slate-900 text-lg">Tambah Member</h3>
        <button onclick="closeCustomerModal()" class="p-2 text-slate-400 hover:bg-slate-50 rounded-xl"><i class="bx bx-x text-2xl"></i></button>
      </div>
      <form id="customerForm" onsubmit="saveCustomer(event)" class="p-6 overflow-y-auto space-y-4">
        <input type="hidden" id="custId">
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
          <input type="text" id="custName" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">No. Telepon</label>
          <input type="text" id="custPhone" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm">
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tier</label>
            <select id="custTier" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm">
              <option value="Bronze">Bronze</option>
              <option value="Silver">Silver</option>
              <option value="Gold">Gold</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Poin Awal</label>
            <input type="text" inputmode="numeric" data-number-format data-min="0" id="custPoints" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm" value="0">
          </div>
        </div>
        <button type="submit" class="w-full py-3.5 bg-[#C00000] hover:bg-[#A00000] text-white text-sm font-bold rounded-xl flex items-center justify-center gap-2">
          <i class="bx bx-save text-lg"></i><span>Simpan Member</span>
        </button>
      </form>
    </div>
  </div>
