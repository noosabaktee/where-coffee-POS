<!-- USER MODAL -->
  <div id="userModal" class="fixed inset-0 z-[100] bg-black/40 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-100 flex flex-col overflow-hidden max-h-[90vh]">
      <div class="p-6 border-b border-slate-100 flex justify-between items-center">
        <h3 id="userModalTitle" class="font-bold text-slate-900 text-lg">Tambah Pengguna</h3>
        <button onclick="closeUserModal()" class="p-2 text-slate-400 hover:bg-slate-50 rounded-xl"><i class="bx bx-x text-2xl"></i></button>
      </div>
      <form id="userForm" onsubmit="saveUser(event)" class="p-6 overflow-y-auto space-y-4">
        <input type="hidden" id="userId">
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
          <input type="text" id="usrName" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Username Login</label>
          <input type="text" id="usrUsername" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Kata Sandi</label>
          <input type="password" id="usrPassword" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm">
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Hak Akses</label>
            <select id="usrRole" onchange="toggleUserOutletConstraint()" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm">
              <option value="Administrator">Administrator</option>
              <option value="Outlet">Manajer Outlet</option>
              <option value="Kasir">Kasir</option>
            </select>
          </div>
          <div id="usrOutletSelectWrapper">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Relasi Cabang</label>
            <select id="usrOutlet" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm">
              <option value="Utama">Utama</option>
              <option value="Cabang 2">Cabang 2</option>
              <option value="Cabang 3">Cabang 3</option>
            </select>
          </div>
        </div>
        <button type="submit" class="w-full py-3.5 bg-[#C00000] hover:bg-[#A00000] text-white text-sm font-bold rounded-xl mt-4 flex items-center justify-center gap-2">
          <i class="bx bx-save text-lg"></i><span>Simpan Data</span>
        </button>
      </form>
    </div>
  </div>
