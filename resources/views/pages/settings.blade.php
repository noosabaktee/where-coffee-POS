@extends('layouts.app')

@section('content')
<div id="view-setting" class="space-y-6">
          <div>
            <h2 class="text-2xl font-bold text-slate-950 tracking-tight">Pengaturan Aplikasi & Toko ⚙️</h2>
          </div>

          <form id="settingsForm" onsubmit="saveSettings(event)" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Card 1: Identitas -->
            <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
              <h3 class="font-bold text-slate-950 text-base border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="bx bx-store text-[#C00000] text-xl"></i> Profil Outlet
              </h3>
              <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Nama Toko</label>
                <input type="text" id="setStoreName" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#C00000] rounded-xl text-sm">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Logo Toko</label>
                <div class="flex flex-col gap-2">
                  <input type="file" id="setStoreLogoFile" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:bg-red-50 file:text-[#C00000]" onchange="handleLogoCompress(event)">
                  <input type="text" id="setStoreLogoUrl" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#C00000] rounded-xl text-sm" placeholder="Atau link URL...">
                </div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Alamat Lengkap</label>
                <textarea id="setStoreAddress" rows="2" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#C00000] rounded-xl text-sm"></textarea>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Nomor Kontak</label>
                <input type="text" id="setStorePhone" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#C00000] rounded-xl text-sm">
              </div>
            </div>

            <!-- Card 2: Konfigurasi -->
            <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
              <h3 class="font-bold text-slate-950 text-base border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="bx bx-calculator text-[#C00000] text-xl"></i> Pajak & Integrasi
              </h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">PPN / Pajak (%)</label>
                  <input type="text" inputmode="numeric" data-number-format data-min="0" data-max="100" id="setTaxRate" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm" value="10">
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Service Charge (%)</label>
                  <input type="text" inputmode="numeric" data-number-format data-min="0" data-max="100" id="setServiceCharge" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm" value="0">
                </div>
              </div>

              <div class="pt-4 border-t border-slate-100 space-y-4">
                <div>
                  <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Upload QRIS Statis</label>
                  <div class="flex flex-col gap-2">
                    <input type="file" id="setQrisFile" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:bg-red-50 file:text-[#C00000]" onchange="handleQrisCompress(event)">
                    <input type="text" id="setQrisUrl" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm" placeholder="Atau link URL...">
                  </div>
                </div>

                <div class="flex items-center justify-between bg-slate-50 p-3 rounded-2xl border border-slate-200/60">
                  <div class="pr-2">
                    <div class="text-xs font-bold text-slate-900">Backend Laravel Aktif</div>
                    <span class="text-[10px] text-slate-400">Semua data tersimpan aman di PostgreSQL</span>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="setPreviewModeToggle" onchange="togglePreviewSetting(this.checked)" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#C00000]"></div>
                  </label>
                </div>

                <div id="apiUrlContainer" class="transition-all duration-300">
                  <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">URL Backend Aplikasi</label>
                  <input type="text" id="setAppUrl" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#C00000] rounded-xl text-sm" placeholder="URL Laravel aktif...">
                </div>
              </div>
            </div>

            <!-- Card 3: Manajemen Hak Akses Menu Dinamis -->
            <div class="lg:col-span-2 bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
              <h3 class="font-bold text-slate-950 text-base border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="bx bx-lock-open-alt text-[#C00000] text-xl"></i> Hak Akses Menu per Peran (Role)
              </h3>
              <p class="text-xs text-slate-500">Centang menu apa saja yang boleh diakses oleh masing-masing peran. (Catatan: Administrator selalu memiliki akses penuh ke seluruh sistem).</p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="rolePermissionsContainer">
                <!-- Dynamically rendered via JS -->
              </div>
            </div>

            <div class="lg:col-span-2 flex flex-col sm:flex-row justify-end gap-3 pt-2">
              <button type="button" onclick="resetDatabaseSetting()" class="w-full md:w-fit py-3 px-5 bg-rose-50 hover:bg-rose-100 text-rose-600 text-sm font-bold rounded-xl transition-all flex items-center justify-center gap-2 border border-rose-200">
                <i class="bx bx-reset text-lg"></i> Reset Database
              </button>
              <button type="submit" class="w-full md:w-fit py-3 px-6 bg-[#C00000] hover:bg-[#A00000] text-white text-sm font-bold rounded-xl shadow-lg shadow-red-900/10 transition-all flex items-center justify-center gap-2">
                <i class="bx bx-save text-lg"></i> Simpan Konfigurasi
              </button>
            </div>
          </form>

          <!-- USER MANAGEMENT SECTION -->
          <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-6 mt-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
              <div>
                <h3 class="font-bold text-slate-950 text-base flex items-center gap-2">
                  <i class="bx bx-shield-quarter text-[#C00000] text-xl"></i> Kelola Pengguna
                </h3>
              </div>
              <button onclick="openUserModal()" class="w-full md:w-fit py-2.5 px-4 bg-[#C00000] hover:bg-[#A00000] text-white text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5 shadow-sm">
                <i class="bx bx-plus-circle text-base"></i> Tambah Staff
              </button>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full border-collapse text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                  <tr><th class="p-4 pl-6">Nama Staff</th><th class="p-4">Username</th><th class="p-4">Peran</th><th class="p-4">Outlet</th><th class="p-4 pr-6 text-center">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="userTableBody"></tbody>
              </table>
            </div>
            <div id="userPagination" class="border-t border-slate-100 px-4 py-3"></div>
          </div>
        </div>
@endsection

@push('modals')
  @include('partials.modals.user')
@endpush
