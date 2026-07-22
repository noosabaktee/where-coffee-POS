<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $pageTitle ?? 'Where Coffee' }} - Premium POS & Inventory</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background: radial-gradient(circle at top left, #fff1f2 0, #faf9f6 34%, #fff7ed 100%); }
    table { white-space: nowrap; }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 8px; }
    ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    main .bg-white.rounded-3xl, main .bg-gradient-to-r.rounded-3xl { transition: transform .28s ease, box-shadow .28s ease, border-color .28s ease; }
    main .bg-white.rounded-3xl:hover, .hover-lift:hover { transform: translateY(-3px); box-shadow: 0 18px 45px -28px rgba(15,23,42,.35); border-color: rgba(192,0,0,.14); }
    button { position: relative; overflow: hidden; }
    .button-pressed { transform: scale(.97) !important; }
    .view-enter { animation: viewEnter .42s cubic-bezier(.22,1,.36,1); }
    .cart-item-enter { animation: cartEnter .28s ease-out both; }
    .coffee-orb { animation: floatOrb 7s ease-in-out infinite; filter: blur(.2px); }
    .coffee-orb:nth-child(2) { animation-delay: -2.2s; }
    .coffee-orb:nth-child(3) { animation-delay: -4.4s; }
    .steam-line { animation: steam 2.4s ease-in-out infinite; transform-origin: center bottom; }
    .steam-line:nth-child(2) { animation-delay: -.8s; }
    .steam-line:nth-child(3) { animation-delay: -1.6s; }
    .skeleton-block { position: relative; overflow: hidden; background: linear-gradient(110deg, #eef2f7 8%, #e2e8f0 18%, #eef2f7 33%); background-size: 220% 100%; border: 1px solid rgba(226,232,240,.72); }
    .skeleton-block::after { content: ''; position: absolute; inset: 0; transform: translateX(-100%); background: linear-gradient(90deg, transparent, rgba(255,255,255,.9), transparent); animation: skeletonShimmer 1.35s infinite; }
    #appContainer { background: linear-gradient(135deg, #fff7f7 0%, #f8fafc 46%, #fffaf2 100%); }
    .app-main-surface { background-image: radial-gradient(circle at 8% 6%, rgba(239,68,68,.08), transparent 27%), radial-gradient(circle at 92% 12%, rgba(245,158,11,.10), transparent 24%), linear-gradient(180deg, rgba(255,255,255,.42), rgba(248,250,252,.82)); }
    .login-panel { background: linear-gradient(155deg, rgba(255,255,255,.98), rgba(255,247,237,.96)); box-shadow: 0 28px 80px -35px rgba(127,29,29,.42); }
    .login-logo-glow { box-shadow: 0 18px 38px -14px rgba(192,0,0,.62), inset 0 1px 0 rgba(255,255,255,.24); background: linear-gradient(145deg, #e00000, #a90000); }
    .color-card-red { background: linear-gradient(145deg, #fff, #fff1f2); border-color: #ffe4e6 !important; }
    .color-card-emerald { background: linear-gradient(145deg, #fff, #ecfdf5); border-color: #d1fae5 !important; }
    .color-card-amber { background: linear-gradient(145deg, #fff, #fffbeb); border-color: #fef3c7 !important; }
    .color-card-indigo { background: linear-gradient(145deg, #fff, #eef2ff); border-color: #e0e7ff !important; }
    .accent-bar { position: relative; overflow: hidden; }
    .accent-bar::before { content: ''; position: absolute; inset: 0 auto 0 0; width: 5px; border-radius: 999px; background: var(--accent, #C00000); }
    .sidebar-surface { background: linear-gradient(180deg, #ffffff 0%, #fffafb 48%, #fff7ed 100%); box-shadow: 14px 0 45px -35px rgba(127,29,29,.35); }
    .profile-card { background: linear-gradient(135deg, #fff1f2, #fff 58%, #fff7ed); border: 1px solid #ffe4e6; }
    .outlet-card-gradient { background: linear-gradient(145deg, #ffffff, #fff7ed); }
    .member-results { box-shadow: 0 20px 50px -24px rgba(15,23,42,.45); }
    .member-result-active, .member-result:hover { background: linear-gradient(90deg, #fff1f2, #fff7ed); }
    @keyframes viewEnter { from { opacity:0; transform:translateY(10px) scale(.995); } to { opacity:1; transform:none; } }
    @keyframes cartEnter { from { opacity:0; transform:translateX(10px); } to { opacity:1; transform:none; } }
    @keyframes floatOrb { 0%,100% { transform:translate3d(0,0,0) rotate(0deg); } 50% { transform:translate3d(0,-14px,0) rotate(5deg); } }
    @keyframes steam { 0% { opacity:0; transform:translateY(8px) scaleY(.8); } 45% { opacity:.8; } 100% { opacity:0; transform:translateY(-11px) scaleY(1.15); } }
    @keyframes skeletonShimmer { 100% { transform: translateX(100%); } }
    .coffee-loading-card { animation: coffeeCardIn .32s cubic-bezier(.22,1,.36,1) both; }
    .coffee-cup-float { animation: coffeeCupFloat 2.4s ease-in-out infinite; }
    .action-steam { opacity: 0; transform-origin: center bottom; animation: actionSteamRise 2.2s ease-in-out infinite; }
    .action-steam-two { animation-delay: -.75s; }
    .action-steam-three { animation-delay: -1.45s; }
    .coffee-liquid-wave { animation: coffeeWave 1.8s ease-in-out infinite alternate; }
    .coffee-bean-orbit { animation: beanOrbit 2.8s ease-in-out infinite; }
    .coffee-bean-two { animation-delay: -1.4s; }
    .coffee-loading-dot { animation: loadingDot 1s ease-in-out infinite; }
    .coffee-loading-dot:nth-child(2) { animation-delay: .15s; }
    .coffee-loading-dot:nth-child(3) { animation-delay: .3s; }
    @keyframes coffeeCardIn { from { opacity:0; transform:translateY(12px) scale(.96); } to { opacity:1; transform:none; } }
    @keyframes coffeeCupFloat { 0%,100% { transform:translateX(-50%) translateY(0) rotate(-1deg); } 50% { transform:translateX(-50%) translateY(-5px) rotate(1deg); } }
    @keyframes actionSteamRise { 0% { opacity:0; transform:translateY(14px) translateX(0) scaleY(.65) rotate(0deg); } 28% { opacity:.85; } 70% { opacity:.55; } 100% { opacity:0; transform:translateY(-22px) translateX(8px) scaleY(1.25) rotate(8deg); } }
    @keyframes coffeeWave { from { transform:translateX(-35%) scaleX(.9); opacity:.45; } to { transform:translateX(95%) scaleX(1.2); opacity:.8; } }
    @keyframes beanOrbit { 0%,100% { transform:translateY(0) rotate(-18deg); } 50% { transform:translateY(-8px) rotate(18deg); } }
    @keyframes loadingDot { 0%,100% { transform:translateY(0) scale(.8); opacity:.45; } 50% { transform:translateY(-5px) scale(1.15); opacity:1; } }
    @media (prefers-reduced-motion: reduce) { *,*::before,*::after { animation-duration:.01ms !important; animation-iteration-count:1 !important; scroll-behavior:auto !important; transition-duration:.01ms !important; } }
  </style>
  @stack('head')
</head>
<body class="text-slate-800 antialiased overflow-hidden">
  @include('partials.feedback')
  <div id="appContainer" class="w-full h-[100dvh] flex overflow-hidden relative">
    @include('partials.sidebar')
    <div class="app-main-surface flex-1 flex flex-col overflow-hidden relative w-full">
      @include('partials.mobile-header')
      <main class="flex-1 overflow-y-auto p-4 md:p-6">
        @include('partials.skeleton')
        <div id="pageContent" class="hidden view-enter">
          @yield('content')
        </div>
      </main>
    </div>
  </div>

  @stack('modals')

  <script>
    window.WhereCoffeeConfig = {
      authenticated: true,
      page: @json($pageId ?? 'dashboard'),
      routes: {
        login: @json(route('login')),
        dashboard: @json(route('dashboard')),
        analytic: @json(route('analytics')),
        pos: @json(route('pos')),
        inventori: @json(route('inventory')),
        laporan: @json(route('reports')),
        biaya: @json(route('expenses')),
        kategori: @json(route('categories')),
        crm: @json(route('crm')),
        outlets: @json(route('outlets')),
        setting: @json(route('settings'))
      }
    };
  </script>
  <script src="{{ asset('js/where-coffee.js') }}" defer></script>
  @stack('scripts')
</body>
</html>
