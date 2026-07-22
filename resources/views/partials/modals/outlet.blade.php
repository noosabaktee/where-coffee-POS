<!-- OUTLET MANAGEMENT MODAL -->
  <div id="outletModal" class="fixed inset-0 z-[100] bg-slate-950/55 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-rose-100 flex flex-col overflow-hidden max-h-[92vh]">
      <div class="relative p-6 bg-gradient-to-r from-red-700 to-orange-500 text-white flex justify-between items-center overflow-hidden">
        <div class="absolute -right-6 -top-8 w-24 h-24 rounded-full bg-white/10"></div>
        <div class="relative"><span class="text-[9px] font-bold uppercase tracking-widest text-red-100">Administrator</span><h3 id="outletModalTitle" class="font-extrabold text-lg">Tambah Cabang Baru</h3></div>
        <button onclick="closeOutletModal()" class="relative w-10 h-10 flex items-center justify-center bg-white/15 hover:bg-white/25 rounded-xl"><i class="bx bx-x text-2xl"></i></button>
      </div>
      <form id="outletForm" onsubmit="saveOutlet(event)" class="p-6 overflow-y-auto space-y-4">
        <input type="hidden" id="outletId">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div><label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Kode Cabang</label><input id="outletCode" required maxlength="30" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-red-500 rounded-xl text-sm uppercase outline-none" placeholder="CONTOH: BANDUNG"></div>
          <div><label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Nama Cabang</label><input id="outletName" required maxlength="120" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-red-500 rounded-xl text-sm outline-none" placeholder="Where Coffee - Bandung"></div>
        </div>
        <div><label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Alamat Lengkap</label><textarea id="outletAddress" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-red-500 rounded-xl text-sm outline-none" placeholder="Alamat toko cabang..."></textarea></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div><label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Nomor Kontak</label><input id="outletPhone" maxlength="30" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-red-500 rounded-xl text-sm outline-none" placeholder="021-555-0000"></div>
          <div><label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Zona Waktu</label><select id="outletTimezone" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-red-500 rounded-xl text-sm outline-none"><option value="Asia/Jakarta">WIB - Asia/Jakarta</option><option value="Asia/Makassar">WITA - Asia/Makassar</option><option value="Asia/Jayapura">WIT - Asia/Jayapura</option></select></div>
        </div>
        <label class="flex items-center justify-between bg-emerald-50 border border-emerald-100 p-4 rounded-2xl cursor-pointer"><div><div class="text-xs font-extrabold text-slate-900">Cabang Aktif</div><div class="text-[10px] text-slate-500">Cabang aktif tersedia pada pemilih outlet di sidebar.</div></div><input type="checkbox" id="outletIsActive" checked class="w-5 h-5 accent-red-600"></label>
        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-red-700 to-red-600 hover:from-red-800 hover:to-red-700 text-white text-sm font-extrabold rounded-xl shadow-lg shadow-red-900/15 flex items-center justify-center gap-2"><i class="bx bx-save text-lg"></i><span>Simpan Cabang</span></button>
      </form>
    </div>
  </div>
