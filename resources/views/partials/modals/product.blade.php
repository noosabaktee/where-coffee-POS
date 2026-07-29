<!-- PRODUCT MODAL -->
  <div id="productModal" class="fixed inset-0 z-[100] bg-black/40 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-100 flex flex-col overflow-hidden max-h-[90vh]">
      <div class="p-6 border-b border-slate-100 flex justify-between items-center">
        <h3 id="modalTitle" class="font-bold text-slate-900 text-lg">Tambah Menu</h3>
        <button onclick="closeProductModal()" class="p-2 text-slate-400 hover:bg-slate-50 rounded-xl"><i class="bx bx-x text-2xl"></i></button>
      </div>
      <form id="productForm" onsubmit="saveProduct(event)" class="p-6 overflow-y-auto space-y-4">
        <input type="hidden" id="pId">
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Foto Menu</label>
          <div class="flex flex-col gap-2">
            <div class="relative h-44 overflow-hidden rounded-2xl border border-dashed border-slate-200 bg-slate-50">
              <img id="pImgPreview" alt="Preview foto menu" class="hidden h-full w-full object-cover">
              <div id="pImgPreviewEmpty" class="flex h-full flex-col items-center justify-center gap-2 text-slate-400">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white shadow-sm"><i class="bx bx-image-alt text-2xl"></i></span>
                <span id="pImgPreviewEmptyText" class="text-xs font-semibold">Preview gambar akan tampil di sini</span>
              </div>
              <span class="absolute left-3 top-3 rounded-lg bg-slate-950/70 px-2.5 py-1 text-[10px] font-bold text-white backdrop-blur">Preview</span>
            </div>
            <input type="file" id="pImgFile" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:bg-red-50 file:text-[#C00000]" onchange="handleImageCompress(event)">
            <input type="hidden" id="pImgUrl">
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">ID SKU</label>
            <input type="text" id="pSku" required maxlength="40" autocomplete="off" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm" placeholder="">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Barcode</label>
            <input type="text" id="pBarcode" required maxlength="80" autocomplete="off" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm" placeholder="">
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Nama Menu</label>
          <input type="text" id="pName" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Kategori</label>
          <select id="pCategory" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm"></select>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Harga Modal (Rp)</label>
            <input type="text" inputmode="numeric" data-number-format data-min="0" id="pCapital" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm" placeholder="0">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Harga Jual (Rp)</label>
            <input type="text" inputmode="numeric" data-number-format data-min="0" id="pPrice" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm" placeholder="0">
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Stok Awal</label>
            <input type="text" inputmode="numeric" data-number-format data-min="0" id="pStock" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm" placeholder="0">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Batas Minimum Stok</label>
            <input type="text" inputmode="numeric" data-number-format data-min="1" id="pMinStock" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm" value="5">
          </div>
        </div>
        <button type="submit" class="w-full py-3.5 bg-[#C00000] hover:bg-[#A00000] text-white text-sm font-bold rounded-xl mt-4 flex items-center justify-center gap-2">
          <i class="bx bx-save text-lg"></i><span>Simpan Menu</span>
        </button>
      </form>
    </div>
  </div>
