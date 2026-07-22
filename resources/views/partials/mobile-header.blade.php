<header class="md:hidden flex-none h-16 bg-white/95 backdrop-blur border-b border-rose-100 flex items-center justify-between px-4">
  <button onclick="toggleSidebar(true)" class="p-2 text-slate-600 hover:bg-red-50 rounded-xl">
    <i class="bx bx-menu text-2xl"></i>
  </button>
  <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
    <div class="w-8 h-8 bg-gradient-to-br from-red-700 to-red-500 rounded-lg flex items-center justify-center text-white shadow-sm">
      <i class="bx bxs-coffee text-lg"></i>
    </div>
    <span class="font-extrabold text-slate-900">Where Coffee</span>
  </a>
  <div class="w-8"></div>
</header>
