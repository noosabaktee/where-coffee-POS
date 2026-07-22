<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Masuk - Where Coffee</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
    body { font-family:'Plus Jakarta Sans',sans-serif; background:radial-gradient(circle at 10% 10%,#fee2e2,transparent 30%),radial-gradient(circle at 90% 85%,#ffedd5,transparent 34%),#faf9f6; }
    .coffee-orb { animation:floatOrb 7s ease-in-out infinite; }
    .coffee-orb:nth-child(2) { animation-delay:-2.2s; }
    .steam-line { animation:steam 2.4s ease-in-out infinite; transform-origin:center bottom; }
    .steam-line:nth-child(2) { animation-delay:-.8s; }
    .steam-line:nth-child(3) { animation-delay:-1.6s; }
    @keyframes floatOrb { 0%,100%{transform:translate3d(0,0,0)} 50%{transform:translate3d(0,-14px,0)} }
    @keyframes steam { 0%{opacity:0;transform:translateY(8px)} 45%{opacity:.8} 100%{opacity:0;transform:translateY(-11px)} }
  </style>
</head>
<body class="min-h-screen text-slate-800 antialiased overflow-hidden">
  <div id="toast" class="fixed bottom-5 right-5 z-[9999] transform translate-y-[160%] opacity-0 transition-all duration-300 ease-out flex items-center gap-3 bg-slate-900/95 text-white px-5 py-3 rounded-2xl shadow-2xl max-w-sm pointer-events-none border-l-4 border-red-500">
    <i id="toastIcon" class="bx bx-check-circle text-xl text-emerald-400"></i><span id="toastMsg" class="text-sm font-medium"></span>
  </div>
  <div class="relative min-h-screen flex items-center justify-center p-4">
    <div class="coffee-orb absolute -top-16 -left-12 w-56 h-56 rounded-full bg-red-200/45"></div>
    <div class="coffee-orb absolute bottom-8 -right-12 w-72 h-72 rounded-full bg-amber-200/40"></div>
    <div class="w-full max-w-md bg-white/95 backdrop-blur rounded-[2rem] shadow-2xl shadow-red-900/15 border border-white p-8 flex flex-col items-center relative overflow-hidden">
      <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-red-800 via-red-600 to-orange-500"></div>
      <svg class="absolute -right-8 top-10 w-36 h-36 text-red-50 pointer-events-none" viewBox="0 0 120 120" fill="none" aria-hidden="true">
        <path d="M30 42h48v29c0 18-11 29-24 29S30 89 30 71V42Z" fill="currentColor"/><path d="M78 50h8c14 0 14 20 0 20h-8" stroke="currentColor" stroke-width="8" stroke-linecap="round"/>
        <path class="steam-line" d="M45 34c-8-8 8-10 0-20" stroke="#fecaca" stroke-width="4" stroke-linecap="round"/><path class="steam-line" d="M58 34c-8-8 8-10 0-20" stroke="#fecaca" stroke-width="4" stroke-linecap="round"/><path class="steam-line" d="M70 34c-8-8 8-10 0-20" stroke="#fecaca" stroke-width="4" stroke-linecap="round"/>
      </svg>
      <div id="loginLogoContainer" class="w-20 h-20 bg-gradient-to-br from-red-700 to-red-500 rounded-2xl flex items-center justify-center mb-6 text-white shadow-xl shadow-red-900/25 overflow-hidden p-1 relative z-10">
        <svg class="w-full h-full p-3" viewBox="0 0 100 100" fill="none" aria-hidden="true"><path d="M22 38h48v27c0 14-10 25-24 25S22 79 22 65V38Z" fill="white"/><path d="M70 45h8c14 0 14 18 0 18h-8" stroke="white" stroke-width="7" stroke-linecap="round"/><path d="M36 29c-7-7 7-9 0-17M49 29c-7-7 7-9 0-17M61 29c-7-7 7-9 0-17" stroke="#fecaca" stroke-width="5" stroke-linecap="round"/><ellipse cx="46" cy="54" rx="8" ry="12" fill="#C00000" transform="rotate(24 46 54)"/></svg>
      </div>
      <h1 class="text-2xl font-extrabold tracking-tight text-slate-950 mb-1">WHERE COFFEE</h1>
      <p class="text-slate-500 text-sm mb-3 text-center">Dashboard Premium – POS & Inventori</p>
      <form id="loginForm" class="w-full space-y-4">
        <div><label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Username</label><div class="relative"><i class="bx bx-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i><input type="text" id="username" required autofocus autocomplete="username" class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 focus:border-red-500 focus:bg-white outline-none rounded-xl text-sm transition-all" placeholder="Masukkan username"></div></div>
        <div><label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Kata Sandi</label><div class="relative"><i class="bx bx-lock-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i><input type="password" id="password" required autocomplete="current-password" class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 focus:border-red-500 focus:bg-white outline-none rounded-xl text-sm transition-all" placeholder="••••••••"></div></div>
        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-red-700 to-red-500 hover:from-red-800 hover:to-red-600 active:scale-[.98] transition-all text-white text-sm font-bold rounded-xl mt-2 flex items-center justify-center gap-2 shadow-lg shadow-red-900/15"><span>Masuk ke Dashboard</span><i class="bx bx-right-arrow-alt text-lg"></i></button>
      </form>
    </div>
  </div>
  <script>
    window.WhereCoffeeConfig = { authenticated:false, page:'login', routes:{ login:@json(route('login')), dashboard:@json(route('dashboard')) } };
  </script>
  <script src="{{ asset('js/where-coffee.js') }}" defer></script>
</body>
</html>
