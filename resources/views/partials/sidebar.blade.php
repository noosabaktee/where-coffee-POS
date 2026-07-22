@php
  $user = auth()->user();
  $menuItems = [
    ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bx-grid-alt', 'route' => 'dashboard', 'permission' => 'menu.dashboard'],
    ['id' => 'analytic', 'label' => 'Analisis Bisnis', 'icon' => 'bx-bar-chart-square', 'route' => 'analytics', 'permission' => 'menu.analytic'],
    ['id' => 'pos', 'label' => 'Sistem Kasir (POS)', 'icon' => 'bx-desktop', 'route' => 'pos', 'permission' => 'menu.pos'],
    ['id' => 'inventori', 'label' => 'Manajemen Stok', 'icon' => 'bx-box', 'route' => 'inventory', 'permission' => 'menu.inventori'],
    ['id' => 'laporan', 'label' => 'Keuangan & Laporan', 'icon' => 'bx-wallet', 'route' => 'reports', 'permission' => 'menu.laporan'],
    ['id' => 'biaya', 'label' => 'Biaya Operasional', 'icon' => 'bx-spreadsheet', 'route' => 'expenses', 'permission' => 'menu.biaya'],
    ['id' => 'kategori', 'label' => 'Master Kategori', 'icon' => 'bx-category', 'route' => 'categories', 'permission' => 'menu.kategori'],
    ['id' => 'crm', 'label' => 'Manajemen CRM', 'icon' => 'bx-user-voice', 'route' => 'crm', 'permission' => 'menu.crm'],
    ['id' => 'setting', 'label' => 'Pengaturan Toko', 'icon' => 'bx-cog', 'route' => 'settings', 'permission' => 'menu.setting'],
  ];
@endphp

<aside id="sidebar" class="sidebar-surface fixed md:relative inset-y-0 left-0 z-50 w-64 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col h-full border-r border-rose-100/70">
  <div class="h-20 flex items-center px-6 border-b border-rose-100 gap-3 flex-none">
    <div id="sidebarLogoContainer" class="login-logo-glow w-10 h-10 rounded-xl flex items-center justify-center text-white overflow-hidden p-0.5 flex-none">
      <svg class="w-full h-full p-2" viewBox="0 0 100 100" fill="none" aria-hidden="true">
        <path d="M22 38h48v27c0 14-10 25-24 25S22 79 22 65V38Z" fill="white"/>
        <path d="M70 45h8c14 0 14 18 0 18h-8" stroke="white" stroke-width="7" stroke-linecap="round"/>
        <path d="M36 29c-7-7 7-9 0-17M49 29c-7-7 7-9 0-17M61 29c-7-7 7-9 0-17" stroke="#fecaca" stroke-width="5" stroke-linecap="round"/>
        <ellipse cx="46" cy="54" rx="8" ry="12" fill="#C00000" transform="rotate(24 46 54)"/>
      </svg>
    </div>
    <div class="min-w-0">
      <h1 id="sidebarStoreName" class="font-extrabold text-slate-950 tracking-tight leading-none text-base truncate">Where Coffee</h1>
      <span class="text-[9px] font-bold tracking-wider text-red-600 uppercase">Premium POS</span>
    </div>
  </div>

  <nav class="flex-1 px-4 py-5 space-y-1.5 overflow-y-auto">
    @foreach ($menuItems as $item)
      @php
        $canShow = $user->hasRole('Administrator') || $user->can($item['permission']);
        $active = request()->routeIs($item['route']);
      @endphp
      @if ($canShow)
        <a href="{{ route($item['route']) }}" id="nav-{{ $item['id'] }}"
           class="w-full flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm transition-all {{ $active ? 'font-bold text-white bg-gradient-to-r from-red-700 to-red-500 shadow-lg shadow-red-900/15' : 'font-medium text-slate-500 hover:text-red-700 hover:bg-red-50' }}">
          <i class="bx {{ $item['icon'] }} text-lg"></i>
          <span>{{ $item['label'] }}</span>
        </a>
      @endif
    @endforeach

    <div class="pt-4 border-t border-rose-100 mt-4">
      <div class="px-4 text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-2">Kelola Toko</div>
      <div class="px-4 mb-3">
        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Outlet Aktif</label>
        <select id="activeOutlet" onchange="switchOutlet(this.value)" class="w-full bg-white/90 border border-rose-100 rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-700 focus:border-red-400 outline-none shadow-sm">
          <option value="">Memuat outlet...</option>
        </select>
      </div>

      @if ($user->hasRole('Administrator'))
        <a href="{{ route('outlets') }}" id="nav-outlets"
           class="w-full flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm transition-all {{ request()->routeIs('outlets') ? 'font-bold text-white bg-gradient-to-r from-orange-600 to-red-600 shadow-lg shadow-red-900/15' : 'font-medium text-slate-500 hover:text-orange-700 hover:bg-orange-50' }}">
          <i class="bx bx-store-alt text-lg"></i><span>Kelola Cabang</span>
        </a>
      @endif
    </div>
  </nav>

  <div class="p-4 border-t border-rose-100 flex-none">
    <div class="profile-card flex items-center gap-3 p-3 rounded-2xl min-w-0">
      <div id="sidebarProfileContent" class="flex items-center gap-2.5 min-w-0 flex-1">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-700 to-red-500 text-white flex items-center justify-center font-extrabold text-sm flex-none shadow-md">
          {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div class="min-w-0 flex-1">
          <div class="text-sm font-extrabold text-slate-950 truncate" title="{{ $user->name }}">{{ $user->name }}</div>
          <span class="block text-[10px] font-medium text-slate-400 truncate">{{ $user->getRoleNames()->first() ?? 'Staff' }}</span>
        </div>
      </div>
      <button onclick="handleLogout()" class="w-9 h-9 flex-none flex items-center justify-center hover:bg-red-100 hover:text-red-700 text-slate-400 rounded-xl transition-all" title="Keluar">
        <i class="bx bx-log-out text-lg"></i>
      </button>
    </div>
  </div>
</aside>

<div id="sidebarBackdrop" onclick="toggleSidebar(false)" class="bg-black/40 fixed inset-0 z-40 md:hidden hidden"></div>
