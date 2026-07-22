<div id="toast" class="fixed bottom-5 right-5 z-[9999] transform translate-y-[160%] opacity-0 transition-all duration-300 ease-out flex items-center gap-3 bg-slate-900/95 backdrop-blur text-white px-5 py-3 rounded-2xl shadow-2xl max-w-sm pointer-events-none border-l-4 border-red-500">
  <i id="toastIcon" class="bx bx-check-circle text-xl text-emerald-400"></i>
  <span id="toastMsg" class="text-sm font-medium">Sukses melakukan aksi!</span>
</div>

<div id="confirmModal" class="fixed inset-0 z-[9999] bg-black/50 items-center justify-center p-4 hidden flex">
  <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-slate-100 transform scale-95 transition-all">
    <div id="confirmModalIcon" class="w-12 h-12 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mb-4 text-2xl"><i class="bx bx-help-circle"></i></div>
    <h3 id="confirmModalTitle" class="text-lg font-bold text-slate-900 mb-2">Apakah kamu yakin?</h3>
    <p id="confirmModalText" class="text-sm text-slate-500 mb-6">Aksi ini tidak dapat dibatalkan setelah diproses.</p>
    <div class="flex gap-3">
      <button id="confirmCancelBtn" class="flex-1 py-3 bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold rounded-xl text-sm transition-all">Batal</button>
      <button id="confirmOkBtn" class="flex-1 py-3 bg-[#C00000] hover:bg-[#A00000] text-white font-semibold rounded-xl text-sm transition-all shadow-md shadow-red-900/10">Ya, Lanjutkan</button>
    </div>
  </div>
</div>

<div id="actionLoadingModal" class="fixed inset-0 z-[10020] hidden items-center justify-center bg-slate-950/50 p-4 backdrop-blur-[3px]" role="status" aria-live="polite" aria-busy="true">
  <div class="coffee-loading-card relative w-full max-w-sm overflow-hidden rounded-[2rem] border border-white/80 bg-gradient-to-br from-white via-red-50 to-amber-50 p-7 text-center shadow-2xl">
    <div class="pointer-events-none absolute -right-12 -top-12 h-36 w-36 rounded-full bg-red-200/40 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-14 -left-10 h-32 w-32 rounded-full bg-amber-200/45 blur-3xl"></div>

    <div class="relative mx-auto mb-5 h-32 w-40" aria-hidden="true">
      <div class="action-steam action-steam-one absolute left-[54px] top-0 h-14 w-5 rounded-full border-l-[5px] border-red-300/80"></div>
      <div class="action-steam action-steam-two absolute left-[77px] top-[-5px] h-16 w-5 rounded-full border-l-[5px] border-amber-300/80"></div>
      <div class="action-steam action-steam-three absolute left-[99px] top-1 h-14 w-5 rounded-full border-l-[5px] border-red-200/90"></div>

      <div class="coffee-cup-float absolute bottom-3 left-1/2 -translate-x-1/2">
        <div class="relative h-[68px] w-[92px] rounded-b-[34px] rounded-t-[14px] bg-gradient-to-br from-red-700 to-red-500 shadow-xl shadow-red-900/25">
          <div class="absolute left-[10px] right-[10px] top-[7px] h-[15px] overflow-hidden rounded-full bg-amber-950 shadow-inner">
            <span class="coffee-liquid-wave absolute inset-y-0 left-0 w-1/2 rounded-full bg-amber-700/60"></span>
          </div>
          <div class="absolute -right-[29px] top-[17px] h-[35px] w-[36px] rounded-r-[22px] border-[9px] border-l-0 border-red-500"></div>
          <div class="absolute bottom-[10px] left-1/2 h-5 w-8 -translate-x-1/2 rounded-full bg-white/10"></div>
        </div>
        <div class="mx-auto mt-2 h-[7px] w-[120px] rounded-full bg-gradient-to-r from-transparent via-slate-300 to-transparent opacity-80"></div>
      </div>

      <i class="bx bxs-coffee-bean coffee-bean-orbit coffee-bean-one absolute bottom-0 left-3 text-xl text-amber-800"></i>
      <i class="bx bxs-coffee-bean coffee-bean-orbit coffee-bean-two absolute bottom-4 right-2 text-lg text-red-800"></i>
    </div>

    <h3 class="relative text-base font-extrabold tracking-tight text-slate-950">Sedang meracik prosesmu...</h3>
    <p id="actionLoadingText" class="relative mt-1.5 text-xs font-medium leading-5 text-slate-500">Sedang memproses data...</p>
    <div class="relative mx-auto mt-5 flex w-24 items-center justify-center gap-1.5">
      <span class="coffee-loading-dot h-2 w-2 rounded-full bg-red-600"></span>
      <span class="coffee-loading-dot h-2 w-2 rounded-full bg-amber-500"></span>
      <span class="coffee-loading-dot h-2 w-2 rounded-full bg-red-400"></span>
    </div>
  </div>
</div>
