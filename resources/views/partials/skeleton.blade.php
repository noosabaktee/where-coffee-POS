<div id="pageSkeleton" class="space-y-6" aria-live="polite" aria-busy="true" aria-label="Memuat halaman">
  @if (($pageId ?? '') === 'pos')
    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
      <section class="min-w-0 rounded-3xl border border-slate-100 bg-white p-4 shadow-sm sm:p-6 lg:col-span-7">
        <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_11rem]">
          <div class="skeleton-block h-12 rounded-xl"></div>
          <div class="skeleton-block h-12 rounded-xl"></div>
        </div>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
          @foreach (range(1, 6) as $item)
            <article class="min-w-0 overflow-hidden rounded-2xl border border-slate-100 bg-white p-3">
              <div class="skeleton-block aspect-[4/3] w-full rounded-xl"></div>
              <div class="mt-3 space-y-2">
                <div class="skeleton-block h-3 w-2/5 rounded-full"></div>
                <div class="skeleton-block h-4 w-4/5 rounded-full"></div>
                <div class="skeleton-block h-5 w-1/2 rounded-lg"></div>
              </div>
            </article>
          @endforeach
        </div>
      </section>

      <section class="min-w-0 rounded-3xl border border-slate-100 bg-white p-4 shadow-sm sm:p-6 lg:col-span-5">
        <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-4">
          <div class="skeleton-block h-5 w-40 max-w-[65%] rounded-lg"></div>
          <div class="skeleton-block h-3 w-20 rounded-full"></div>
        </div>
        <div class="flex min-h-40 items-center justify-center py-6">
          <div class="space-y-3 text-center">
            <div class="skeleton-block mx-auto h-14 w-14 rounded-2xl"></div>
            <div class="skeleton-block h-3 w-32 rounded-full"></div>
          </div>
        </div>
        <div class="space-y-4 rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
          <div class="skeleton-block h-11 w-full rounded-xl"></div>
          <div class="space-y-3">
            @foreach (range(1, 5) as $row)
              <div class="flex items-center justify-between gap-4">
                <div class="skeleton-block h-3 w-24 rounded-full"></div>
                <div class="skeleton-block h-4 w-20 rounded-full"></div>
              </div>
            @endforeach
          </div>
          <div class="skeleton-block h-12 w-full rounded-xl"></div>
          <div class="skeleton-block h-12 w-full rounded-xl"></div>
        </div>
      </section>
    </div>
  @elseif (in_array(($pageId ?? ''), ['inventori', 'laporan', 'biaya', 'kategori', 'crm', 'outlets', 'setting'], true))
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="min-w-0 space-y-3">
        <div class="skeleton-block h-7 w-56 max-w-[75vw] rounded-xl"></div>
        <div class="skeleton-block h-3 w-80 max-w-[82vw] rounded-full"></div>
      </div>
      <div class="skeleton-block h-11 w-full rounded-xl sm:w-40"></div>
    </div>

    <div class="rounded-3xl border border-slate-100 bg-white p-4 shadow-sm sm:p-5">
      <div class="skeleton-block h-12 w-full rounded-xl"></div>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
      <div class="hidden grid-cols-[1.35fr_1fr_1fr_1fr_7rem] gap-5 border-b border-slate-100 bg-slate-50 px-5 py-4 md:grid">
        @foreach (range(1, 5) as $column)
          <div class="skeleton-block h-3 rounded-full"></div>
        @endforeach
      </div>
      <div class="hidden md:block">
        @foreach (range(1, 7) as $row)
          <div class="grid grid-cols-[1.35fr_1fr_1fr_1fr_7rem] gap-5 border-b border-slate-100 px-5 py-4 last:border-0">
            <div class="skeleton-block h-4 rounded-full"></div>
            <div class="skeleton-block h-4 rounded-full"></div>
            <div class="skeleton-block h-4 rounded-full"></div>
            <div class="skeleton-block h-4 rounded-full"></div>
            <div class="flex justify-end gap-2"><div class="skeleton-block h-8 w-8 rounded-lg"></div><div class="skeleton-block h-8 w-8 rounded-lg"></div></div>
          </div>
        @endforeach
      </div>
      <div class="space-y-3 p-4 md:hidden">
        @foreach (range(1, 5) as $row)
          <div class="rounded-2xl border border-slate-100 p-4">
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0 flex-1 space-y-3">
                <div class="skeleton-block h-4 w-3/4 rounded-full"></div>
                <div class="skeleton-block h-3 w-1/2 rounded-full"></div>
                <div class="skeleton-block h-3 w-2/3 rounded-full"></div>
              </div>
              <div class="skeleton-block h-9 w-20 flex-none rounded-xl"></div>
            </div>
          </div>
        @endforeach
      </div>
      <div class="flex items-center justify-between gap-4 border-t border-slate-100 px-5 py-4">
        <div class="skeleton-block h-3 w-36 rounded-full"></div>
        <div class="flex gap-2"><div class="skeleton-block h-8 w-8 rounded-lg"></div><div class="skeleton-block h-8 w-8 rounded-lg"></div><div class="skeleton-block h-8 w-8 rounded-lg"></div></div>
      </div>
    </div>
  @elseif (($pageId ?? '') === 'dashboard')
    <section class="rounded-3xl border border-red-100 bg-white p-5 shadow-sm md:p-6">
      <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex min-w-0 items-center gap-3"><div class="skeleton-block h-12 w-12 flex-none rounded-2xl"></div><div class="min-w-0 flex-1 space-y-3"><div class="skeleton-block h-7 w-72 max-w-[70vw] rounded-xl"></div><div class="skeleton-block h-3 w-96 max-w-[76vw] rounded-full"></div></div></div>
        <div class="skeleton-block h-9 w-44 rounded-full"></div>
      </div>
      <div class="mt-6 grid grid-cols-1 gap-3 rounded-2xl border border-slate-100 p-4 sm:grid-cols-2 xl:grid-cols-[1fr_1fr_20rem]">
        <div class="skeleton-block h-12 rounded-xl"></div><div class="skeleton-block h-12 rounded-xl"></div><div class="flex gap-2"><div class="skeleton-block h-12 flex-1 rounded-xl"></div><div class="skeleton-block h-12 flex-1 rounded-xl"></div></div>
      </div>
    </section>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">@foreach (range(1,4) as $item)<div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm"><div class="flex items-start justify-between"><div class="w-2/3 space-y-3"><div class="skeleton-block h-3 rounded-full"></div><div class="skeleton-block h-7 rounded-lg"></div><div class="skeleton-block h-3 w-4/5 rounded-full"></div></div><div class="skeleton-block h-11 w-11 rounded-2xl"></div></div></div>@endforeach</div>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">@foreach (range(1,4) as $item)<div class="rounded-2xl border border-slate-100 bg-white p-4"><div class="skeleton-block h-3 w-2/3 rounded-full"></div><div class="skeleton-block mt-3 h-6 w-4/5 rounded-lg"></div><div class="skeleton-block mt-2 h-3 w-1/2 rounded-full"></div></div>@endforeach</div>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3"><div class="rounded-3xl border border-slate-100 bg-white p-6 xl:col-span-2"><div class="skeleton-block h-5 w-64 rounded-lg"></div><div class="skeleton-block mt-5 h-72 rounded-2xl"></div></div><div class="rounded-3xl border border-slate-100 bg-white p-6"><div class="skeleton-block h-5 w-44 rounded-lg"></div><div class="mt-5 space-y-3">@foreach (range(1,4) as $row)<div class="skeleton-block h-14 rounded-2xl"></div>@endforeach</div></div></div>
  @elseif (($pageId ?? '') === 'analytic')
    <section class="rounded-3xl border border-indigo-100 bg-white p-5 shadow-sm md:p-6"><div class="flex items-center gap-3"><div class="skeleton-block h-12 w-12 rounded-2xl"></div><div class="space-y-3"><div class="skeleton-block h-7 w-64 max-w-[70vw] rounded-xl"></div><div class="skeleton-block h-3 w-96 max-w-[76vw] rounded-full"></div></div></div><div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-[1fr_1fr_20rem]"><div class="skeleton-block h-12 rounded-xl"></div><div class="skeleton-block h-12 rounded-xl"></div><div class="skeleton-block h-12 rounded-xl"></div></div></section>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 xl:grid-cols-8">@foreach (range(1,8) as $item)<div class="rounded-2xl border border-slate-100 bg-white p-4"><div class="skeleton-block h-3 w-3/4 rounded-full"></div><div class="skeleton-block mt-3 h-6 rounded-lg"></div></div>@endforeach</div>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3"><div class="rounded-3xl border border-slate-100 bg-white p-6 xl:col-span-2"><div class="skeleton-block h-5 w-64 rounded-lg"></div><div class="skeleton-block mt-5 h-80 rounded-2xl"></div></div><div class="rounded-3xl border border-slate-100 bg-white p-6"><div class="skeleton-block h-5 w-44 rounded-lg"></div><div class="skeleton-block mt-5 h-72 rounded-full"></div></div></div>
  @else
    <div class="space-y-4"><div class="skeleton-block h-8 w-64 rounded-xl"></div><div class="skeleton-block h-4 w-96 max-w-full rounded-full"></div><div class="skeleton-block h-72 rounded-3xl"></div></div>
  @endif
</div>
