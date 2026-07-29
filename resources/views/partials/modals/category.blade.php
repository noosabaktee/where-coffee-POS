<!-- CATEGORY MODAL -->
  <div id="categoryModal" class="fixed inset-0 z-[100] bg-black/40 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-100 flex flex-col overflow-hidden">
      <div class="p-6 border-b border-slate-100 flex justify-between items-center">
        <h3 id="categoryModalTitle" class="font-bold text-slate-900 text-lg">Tambah Kategori Baru</h3>
        <button onclick="closeCategoryModal()" class="p-2 text-slate-400 hover:bg-slate-50 rounded-xl"><i class="bx bx-x text-2xl"></i></button>
      </div>
      <form id="categoryForm" onsubmit="saveCategory(event)" class="p-6 space-y-4">
        <input type="hidden" id="catId">
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">ID CODE</label>
          <input type="text" id="catCode" required maxlength="30" autocomplete="off" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm uppercase" placeholder="Contoh: CAT-PST">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Nama Kategori</label>
          <input type="text" id="catName" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm">
        </div>
        <button type="submit" class="w-full py-3.5 bg-[#C00000] hover:bg-[#A00000] text-white text-sm font-bold rounded-xl flex items-center justify-center gap-2">
          <i class="bx bx-save text-lg"></i><span>Simpan Kategori</span>
        </button>
      </form>
    </div>
  </div>
