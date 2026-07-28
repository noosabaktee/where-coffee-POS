'use strict';

let products = [];
let transactions = [];
let cart = [];
let customers = [];
let expenses = [];
let categories = [];
let users = [];
let outlets = [];
let currentUser = null;
let currentOutlet = null;
let currentPaymentMode = 'Tunai';
let selectedCustomerId = '';
let isPointsApplied = false;
let customerSearchTimer = null;
let customerSearchResults = [];
let customerSearchActiveIndex = -1;
let customerSearchRequest = 0;
let salesChartInstance = null;
let categoryChartInstance = null;
let cashflowChartInstance = null;
let paymentMixChartInstance = null;
let analyticsPaymentChartInstance = null;
let topProductsChartInstance = null;
let peakHoursChartInstance = null;
let dashboardMetrics = {};
let roleMenus = {};
let availableMenus = {};
let pendingConfirmAction = null;
let isBootstrapping = false;
const tablePagination = {
  inventory: { page: 1, perPage: 10, signature: '' },
  reports: { page: 1, perPage: 10, signature: '' },
  crm: { page: 1, perPage: 10, signature: '' },
  expenses: { page: 1, perPage: 10, signature: '' },
  categories: { page: 1, perPage: 10, signature: '' },
  outlets: { page: 1, perPage: 10, signature: '' },
  users: { page: 1, perPage: 10, signature: '' },
};
let config = {
  storeName: 'Where Coffee',
  address: '',
  phone: '',
  taxRate: 10,
  serviceCharge: 0,
  qrisImage: '',
  storeLogo: '',
  receiptFooter: 'Terima kasih atas kunjungan Anda',
  pointsPerAmount: 10000,
  pointValue: 500,
};

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
const byId = (id) => document.getElementById(id);

async function api(url, options = {}) {
  const method = options.method || 'GET';
  const headers = {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrfToken(),
    ...(options.headers || {}),
  };

  const fetchOptions = {
    credentials: 'same-origin',
    ...options,
    method,
    headers,
  };

  if (options.body && !(options.body instanceof FormData) && typeof options.body !== 'string') {
    fetchOptions.body = JSON.stringify(options.body);
    fetchOptions.headers['Content-Type'] = 'application/json';
  }

  const response = await fetch(url, fetchOptions);
  if (response.status === 204) return null;

  const contentType = response.headers.get('content-type') || '';
  const data = contentType.includes('application/json') ? await response.json() : await response.text();

  if (!response.ok) {
    if (response.status === 419) {
      window.location.reload();
      throw new Error('Sesi keamanan telah kedaluwarsa.');
    }
    const validation = data?.errors ? Object.values(data.errors).flat().join('\n') : null;
    const error = new Error(validation || data?.message || 'Permintaan tidak dapat diproses.');
    error.status = response.status;
    error.payload = data;
    throw error;
  }

  return data;
}

function setPageLoading(show, message = 'Memuat halaman...') {
  const skeleton = byId('pageSkeleton');
  const content = byId('pageContent');
  if (skeleton) {
    skeleton.classList.toggle('hidden', !show);
    skeleton.setAttribute('aria-busy', show ? 'true' : 'false');
    skeleton.setAttribute('aria-label', message);
  }
  if (content) content.classList.toggle('hidden', show);
}

function setActionLoading(show, message = 'Sedang memproses data...') {
  const modal = byId('actionLoadingModal');
  if (!modal) return;
  const text = byId('actionLoadingText');
  if (text) text.textContent = message;
  modal.classList.toggle('hidden', !show);
  modal.classList.toggle('flex', show);
  document.body.classList.toggle('cursor-wait', show);
}

async function withActionLoading(message, action) {
  setActionLoading(true, message);
  try {
    return await action();
  } finally {
    setActionLoading(false);
  }
}

function showToast(message, type = 'success') {
  const toast = byId('toast');
  const icon = byId('toastIcon');
  if (!toast || !icon) return;
  byId('toastMsg').textContent = message;
  toast.className = `fixed bottom-5 right-5 z-[9999] transform translate-y-0 opacity-100 transition-all duration-300 ease-out flex items-center gap-3 bg-slate-900/95 backdrop-blur text-white px-5 py-3 rounded-2xl shadow-2xl max-w-sm pointer-events-none border-l-4 ${type === 'success' ? 'border-emerald-400' : 'border-rose-400'}`;
  icon.className = `bx ${type === 'success' ? 'bx-check-circle text-emerald-400' : 'bx-error-circle text-rose-400'} text-xl`;
  window.clearTimeout(showToast.timer);
  showToast.timer = window.setTimeout(() => {
    toast.classList.add('translate-y-[160%]', 'opacity-0');
    toast.classList.remove('translate-y-0', 'opacity-100');
  }, 3300);
}
function showCustomConfirm(message, onConfirm, options = {}) {
  const modal = byId('confirmModal');
  if (!modal) return;
  if (options.html) byId('confirmModalText').innerHTML = message;
  else byId('confirmModalText').textContent = message;
  byId('confirmModalTitle').textContent = options.title || 'Apakah kamu yakin?';
  byId('confirmOkBtn').textContent = options.confirmText || 'Ya, Lanjutkan';
  byId('confirmCancelBtn').textContent = options.cancelText || 'Batal';
  const icon = byId('confirmModalIcon');
  if (icon) icon.innerHTML = `<i class="bx ${options.icon || 'bx-help-circle'}"></i>`;
  pendingConfirmAction = onConfirm;
  modal.classList.remove('hidden');
}

function formatIDR(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(Number(value || 0));
}

function escapeHtml(value = '') {
  return String(value).replace(/[&<>'"]/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
  }[character]));
}

function imageSource(value) {
  if (!value) return '';
  if (String(value).startsWith('data:image/')) return value;
  try {
    return new URL(value, window.location.origin).href;
  } catch (_) {
    return value;
  }
}

function imageValueChanged(nextValue, currentValue) {
  return Boolean(nextValue) && imageSource(nextValue) !== imageSource(currentValue);
}

function parseFormattedNumber(value) {
  const digits = String(value ?? '').replace(/[^0-9]/g, '');
  return digits ? Number(digits) : 0;
}

function formatPlainNumber(value) {
  return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(value || 0));
}

function readNumberInput(id) {
  const input = byId(id);
  if (!input) return 0;
  let value = parseFormattedNumber(input.value);
  if (input.dataset.min !== undefined) value = Math.max(Number(input.dataset.min), value);
  if (input.dataset.max !== undefined) value = Math.min(Number(input.dataset.max), value);
  return value;
}

function setNumberInputValue(id, value) {
  const input = byId(id);
  if (!input) return;
  input.value = value === '' || value === null || value === undefined ? '' : formatPlainNumber(value);
}

function initializeFormattedNumberInputs() {
  document.querySelectorAll('[data-number-format]').forEach((input) => {
    if (input.value !== '') input.value = formatPlainNumber(parseFormattedNumber(input.value));
    input.addEventListener('input', () => {
      const raw = parseFormattedNumber(input.value);
      const max = input.dataset.max !== undefined ? Number(input.dataset.max) : null;
      const limited = max !== null ? Math.min(max, raw) : raw;
      input.value = input.value === '' ? '' : formatPlainNumber(limited);
    });
    input.addEventListener('blur', () => {
      const min = input.dataset.min !== undefined ? Number(input.dataset.min) : null;
      if (input.value === '' && !input.required) return;
      let value = parseFormattedNumber(input.value);
      if (min !== null) value = Math.max(min, value);
      input.value = formatPlainNumber(value);
    });
  });
}

function pageSlice(key, items, signature = '') {
  const state = tablePagination[key];
  if (!state) return items;
  if (state.signature !== signature) {
    state.signature = signature;
    state.page = 1;
  }
  const totalPages = Math.max(1, Math.ceil(items.length / state.perPage));
  state.page = Math.min(state.page, totalPages);
  const start = (state.page - 1) * state.perPage;
  return items.slice(start, start + state.perPage);
}

function renderTablePagination(key, totalItems, containerId) {
  const container = byId(containerId);
  const state = tablePagination[key];
  if (!container || !state) return;
  const totalPages = Math.max(1, Math.ceil(totalItems / state.perPage));
  if (totalItems <= state.perPage) {
    container.innerHTML = `<div class="text-[11px] text-slate-400">Menampilkan ${totalItems} data</div>`;
    return;
  }
  const first = (state.page - 1) * state.perPage + 1;
  const last = Math.min(state.page * state.perPage, totalItems);
  const pages = [];
  for (let page = Math.max(1, state.page - 2); page <= Math.min(totalPages, state.page + 2); page += 1) pages.push(page);
  container.innerHTML = `<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div class="text-[11px] text-slate-400">Menampilkan ${first}–${last} dari ${totalItems} data</div>
    <div class="flex items-center gap-1">
      <button type="button" onclick="changeTablePage('${key}', ${state.page - 1})" ${state.page <= 1 ? 'disabled' : ''} class="w-8 h-8 rounded-lg border border-slate-200 text-slate-500 hover:border-red-200 hover:text-red-600 disabled:opacity-35"><i class="bx bx-chevron-left"></i></button>
      ${pages.map((page) => `<button type="button" onclick="changeTablePage('${key}', ${page})" class="min-w-8 h-8 px-2 rounded-lg text-xs font-bold ${page === state.page ? 'bg-[#C00000] text-white' : 'border border-slate-200 text-slate-600 hover:border-red-200 hover:text-red-600'}">${page}</button>`).join('')}
      <button type="button" onclick="changeTablePage('${key}', ${state.page + 1})" ${state.page >= totalPages ? 'disabled' : ''} class="w-8 h-8 rounded-lg border border-slate-200 text-slate-500 hover:border-red-200 hover:text-red-600 disabled:opacity-35"><i class="bx bx-chevron-right"></i></button>
    </div>
  </div>`;
}

function changeTablePage(key, page) {
  const state = tablePagination[key];
  if (!state) return;
  state.page = Math.max(1, Number(page || 1));
  const renderers = { inventory: renderInventory, reports: renderReport, crm: renderCRM, expenses: renderExpenses, categories: renderCategories, outlets: renderOutlets, users: renderUsers };
  renderers[key]?.();
  document.querySelector(`#${key === 'reports' ? 'view-laporan' : key === 'expenses' ? 'view-biaya' : key === 'categories' ? 'view-kategori' : key === 'inventory' ? 'view-inventori' : key === 'users' ? 'view-setting' : `view-${key}`}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function getProductImage(product = {}) {
  if (product.image) return product.image;
  const barcode = String(product.barcode || '').toUpperCase();
  const category = String(product.category || '').toLowerCase();
  if (barcode.includes('ESP')) return '/images/menu/espresso.webp';
  if (barcode.includes('AME') || barcode.includes('SCL')) return '/images/menu/iced-americano.webp';
  if (barcode.includes('LAT') || barcode.includes('PLL')) return '/images/menu/latte.webp';
  if (barcode.includes('CAP')) return '/images/menu/cappuccino.webp';
  if (barcode.includes('CHO') || barcode.includes('MAT') || category.includes('non-coffee')) return '/images/menu/chocolate.webp';
  if (barcode.includes('TEA') || category.includes('tea')) return '/images/menu/tea.webp';
  if (barcode.includes('PST') || category.includes('pastry')) return '/images/menu/croissant.webp';
  if (barcode.includes('FOD') || barcode.includes('SNK') || category.includes('main') || category.includes('snack')) return '/images/menu/rice-bowl.webp';
  return '/images/menu/latte.webp';
}
function getActiveOutlets() {
  return outlets.filter((outlet) => outlet.is_active !== false);
}

function mapSettings(settings = {}) {
  config = {
    storeName: settings.storeName || 'Where Coffee',
    address: settings.address || '',
    phone: settings.phone || '',
    taxRate: Number(settings.taxRate || 0),
    serviceCharge: Number(settings.serviceCharge || 0),
    qrisImage: settings.qrisImage || '',
    storeLogo: settings.storeLogo || '',
    receiptFooter: settings.receiptFooter || 'Terima kasih atas kunjungan Anda',
    pointsPerAmount: Number(settings.pointsPerAmount || 10000),
    pointValue: Number(settings.pointValue || 500),
  };
}

async function bootstrapApplication({ quiet = false } = {}) {
  if (isBootstrapping) return;
  isBootstrapping = true;
  if (!quiet) setPageLoading(true);

  try {
    const page = window.WhereCoffeeConfig?.page || 'dashboard';
    const data = await api(`/api/bootstrap?page=${encodeURIComponent(page)}`);
    const urlPeriod = new URLSearchParams(window.location.search);
    const urlFrom = urlPeriod.get('from');
    const urlTo = urlPeriod.get('to');
    if (['dashboard', 'analytic'].includes(page) && /^\d{4}-\d{2}-\d{2}$/.test(urlFrom || '') && /^\d{4}-\d{2}-\d{2}$/.test(urlTo || '')) {
      data.metrics = await api(`/api/dashboard?from=${encodeURIComponent(urlFrom)}&to=${encodeURIComponent(urlTo)}`);
    }
    currentUser = data.user;
    currentOutlet = data.current_outlet;
    outlets = data.outlets || [];
    products = data.products || [];
    categories = data.categories || [];
    customers = data.customers || [];
    expenses = data.expenses || [];
    transactions = data.transactions || [];
    users = data.users || [];
    dashboardMetrics = data.metrics || {};
    roleMenus = data.role_menus || {};
    availableMenus = data.available_menus || {};
    mapSettings(data.settings);

    populateOutletSelector();
    applyUserRolePermissions(currentUser);
    loadConfiguration();
    refreshAllUI();
    if (byId('syncStatus')) byId('syncStatus').textContent = `Sinkron • ${currentOutlet.name}`;
  } catch (error) {
    if (error.status === 401) {
      window.location.href = window.WhereCoffeeConfig?.routes?.login || '/login';
      return;
    }
    showToast(error.message, 'error');
  } finally {
    if (!quiet) setPageLoading(false);
    isBootstrapping = false;
  }
}

function populateOutletSelector() {
  const select = byId('activeOutlet');
  if (!select) return;
  const activeOutlets = getActiveOutlets();
  select.innerHTML = activeOutlets.map((outlet) => `<option value="${outlet.id}">${escapeHtml(outlet.name)}</option>`).join('');
  select.value = String(currentOutlet.id);
  select.disabled = !currentUser.permissions.includes('outlets.switch') && currentUser.role !== 'Administrator';

  const userOutlet = byId('usrOutlet');
  if (userOutlet) {
    userOutlet.innerHTML = getActiveOutlets().map((outlet) => `<option value="${outlet.id}">${escapeHtml(outlet.name)}</option>`).join('');
  }
}

async function switchOutlet(outletId) {
  if (!outletId || Number(outletId) === Number(currentOutlet?.id)) return;
  try {
    setActionLoading(true, 'Mengganti outlet...');
    await api('/api/context/outlet', { method: 'PUT', body: { outlet_id: Number(outletId) } });
    cart = [];
    window.location.reload();
  } catch (error) {
    showToast(error.message, 'error');
    populateOutletSelector();
    setActionLoading(false);
  }
}

function applyUserRolePermissions(user) {
  if (!user) return;
  const container = byId('sidebarProfileContent');
  if (container) {
    const initials = user.name.split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part[0]).join('').toUpperCase() || 'WC';
    container.innerHTML = `<div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-700 to-red-500 text-white flex items-center justify-center font-extrabold text-sm flex-none shadow-md">${escapeHtml(initials)}</div>
      <div class="min-w-0 flex-1"><div class="text-sm font-extrabold text-slate-950 truncate" title="${escapeHtml(user.name)}">${escapeHtml(user.name)}</div><span class="block text-[10px] font-medium text-slate-400 truncate">${escapeHtml(user.role)}${user.outlet ? ` • ${escapeHtml(user.outlet)}` : ''}</span></div>`;
  }
}

function changeView(viewId) {
  const destination = window.WhereCoffeeConfig?.routes?.[viewId];
  if (destination) window.location.href = destination;
}

function toggleSidebar(show) {
  const sidebar = byId('sidebar');
  const backdrop = byId('sidebarBackdrop');
  if (show) {
    sidebar?.classList.remove('-translate-x-full');
    backdrop?.classList.remove('hidden');
  } else if (window.innerWidth < 768) {
    sidebar?.classList.add('-translate-x-full');
    backdrop?.classList.add('hidden');
  }
}

function loadConfiguration() {
  const values = {
    setStoreName: config.storeName,
    setStoreAddress: config.address,
    setStorePhone: config.phone,
    setTaxRate: config.taxRate,
    setServiceCharge: config.serviceCharge,
    setQrisUrl: config.qrisImage,
    setStoreLogoUrl: config.storeLogo,
  };
  Object.entries(values).forEach(([id, value]) => { if (byId(id)?.hasAttribute('data-number-format')) setNumberInputValue(id, value); else if (byId(id)) byId(id).value = value || ''; });
  if (byId('setPreviewModeToggle')) {
    byId('setPreviewModeToggle').checked = false;
    byId('setPreviewModeToggle').disabled = true;
  }
  if (byId('setAppUrl')) {
    byId('setAppUrl').value = window.location.origin;
    byId('setAppUrl').disabled = true;
  }
  updateAppLogos();
  renderRolePermissionsForm();
}

function updateAppLogos() {
  const defaultSvg = `<svg class="w-full h-full p-2" viewBox="0 0 100 100" fill="none" aria-label="Logo kopi"><path d="M23 39h49v23c0 17-10 28-24.5 28S23 79 23 62V39Z" fill="white"/><path d="M72 46h7c14 0 14 19 0 19h-7" stroke="white" stroke-width="7" stroke-linecap="round"/><path d="M37 31c-7-7 7-10 0-20M50 31c-7-7 7-10 0-20M63 31c-7-7 7-10 0-20" stroke="#fecaca" stroke-width="5" stroke-linecap="round"/></svg>`;
  const logoSource = imageSource(config.storeLogo);
  ['loginLogoContainer', 'sidebarLogoContainer'].forEach((id) => {
    const element = byId(id);
    if (!element) return;
    element.innerHTML = logoSource ? `<img src="${escapeHtml(logoSource)}" class="w-full h-full object-cover" alt="Logo toko">` : defaultSvg;
    element.querySelector('img')?.addEventListener('error', () => { element.innerHTML = defaultSvg; }, { once: true });
  });
  updateReceiptLogo(defaultSvg, logoSource);
  if (byId('loginStoreName')) byId('loginStoreName').textContent = config.storeName.toUpperCase();
  // if (byId('sidebarStoreName')) byId('sidebarStoreName').textContent = config.storeName;
}

function updateReceiptLogo(defaultSvg, logoSource = imageSource(config.storeLogo)) {
  const element = byId('recLogoContainer');
  if (!element) return;
  const fallback = `<span class="inline-flex h-14 w-14 items-center justify-center overflow-hidden rounded-lg bg-[#C00000] text-white">${defaultSvg}</span>`;
  element.innerHTML = logoSource
    ? `<span class="inline-flex h-14 w-14 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-white p-1"><img src="${escapeHtml(logoSource)}" class="h-full w-full object-contain" alt="Logo toko"></span>`
    : fallback;
  element.querySelector('img')?.addEventListener('error', () => { element.innerHTML = fallback; }, { once: true });
}
function renderRolePermissionsForm() {
  const container = byId('rolePermissionsContainer');
  if (!container) return;
  container.innerHTML = '';
  ['Kasir', 'Outlet'].forEach((role) => {
    const roleDiv = document.createElement('div');
    roleDiv.className = 'bg-slate-50 p-4 rounded-2xl border border-slate-200';
    const selected = roleMenus[role] || [];
    const checkboxes = Object.entries(availableMenus).filter(([id]) => id !== 'outlets').map(([id, menu]) => `
      <label class="flex items-center gap-2.5 cursor-pointer group">
        <input type="checkbox" value="${id}" class="perm-cb-${role} w-4 h-4 text-[#C00000] bg-white border-slate-300 rounded focus:ring-[#C00000]" ${selected.includes(id) ? 'checked' : ''}>
        <span class="text-[11px] font-bold text-slate-600 group-hover:text-slate-900 uppercase">${escapeHtml(menu.label)}</span>
      </label>`).join('');
    roleDiv.innerHTML = `<h4 class="text-sm font-bold text-slate-800 mb-3 border-b border-slate-200 pb-2 flex items-center gap-2"><i class="bx bx-user-pin text-slate-400"></i> Peran: ${role}</h4><div class="grid grid-cols-2 gap-3">${checkboxes}</div>`;
    container.appendChild(roleDiv);
  });
}

function togglePreviewSetting() {
  showToast('Aplikasi sudah menggunakan backend Laravel dan PostgreSQL.', 'success');
}

async function saveSettings(event) {
  event.preventDefault();
  const logoValue = byId('setStoreLogoUrl').value.trim();
  const qrisValue = byId('setQrisUrl').value.trim();
  const body = {
    store_name: byId('setStoreName').value.trim(),
    address: byId('setStoreAddress').value.trim(),
    phone: byId('setStorePhone').value.trim(),
    tax_rate: readNumberInput('setTaxRate'),
    service_charge_rate: readNumberInput('setServiceCharge'),
  };
  if (imageValueChanged(logoValue, config.storeLogo)) {
    if (logoValue.startsWith('data:image/')) body.logo_data = logoValue;
    else if (/^https?:\/\//i.test(logoValue)) body.logo_url = logoValue;
  }
  if (imageValueChanged(qrisValue, config.qrisImage)) {
    if (qrisValue.startsWith('data:image/')) body.qris_data = qrisValue;
    else if (/^https?:\/\//i.test(qrisValue)) body.qris_url = qrisValue;
  }

  try {
    setActionLoading(true, 'Menyimpan pengaturan...');
    const response = await api('/api/settings', { method: 'PUT', body });
    mapSettings(response.data);
    updateAppLogos();

    for (const role of ['Kasir', 'Outlet']) {
      const menus = Array.from(document.querySelectorAll(`.perm-cb-${role}:checked`)).map((checkbox) => checkbox.value);
      await api(`/api/roles/${encodeURIComponent(role)}/menus`, { method: 'PUT', body: { menus } });
      roleMenus[role] = menus;
    }
    showToast(response.message);
    await bootstrapApplication({ quiet: true });
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    setActionLoading(false);
  }
}

async function resetDatabaseSetting() {
  showCustomConfirm('Reset seluruh data operasional demo? Pengguna, outlet, dan hak akses tetap dipertahankan.', async () => {
    try {
      setActionLoading(true, 'Mengembalikan data demo...');
      const response = await api('/api/maintenance/reset-demo', { method: 'POST', body: {} });
      showToast(response.message);
      await bootstrapApplication({ quiet: true });
    } catch (error) {
      showToast(error.message, 'error');
    } finally {
      setActionLoading(false);
    }
  });
}

function handleImageCompress(event) {
  compressFileBase(event, 900, 700, (base64) => {
    byId('pImgUrl').value = base64;
    updateProductImagePreview(base64);
  });
}
function handleQrisCompress(event) { compressFileBase(event, 600, 600, (base64) => { byId('setQrisUrl').value = base64; }); }
function handleLogoCompress(event) { compressFileBase(event, 500, 500, (base64) => { byId('setStoreLogoUrl').value = base64; }); }

function updateProductImagePreview(value = '') {
  const preview = byId('pImgPreview');
  const empty = byId('pImgPreviewEmpty');
  const emptyText = byId('pImgPreviewEmptyText');
  if (!preview || !empty) return;

  const source = imageSource(String(value).trim());
  if (!source) {
    preview.removeAttribute('src');
    preview.classList.add('hidden');
    empty.classList.remove('hidden');
    if (emptyText) emptyText.textContent = 'Preview gambar akan tampil di sini';
    return;
  }

  preview.onload = () => {
    preview.classList.remove('hidden');
    empty.classList.add('hidden');
  };
  preview.onerror = () => {
    preview.classList.add('hidden');
    empty.classList.remove('hidden');
    if (emptyText) emptyText.textContent = 'Gambar tidak dapat dimuat';
  };
  preview.src = source;
}

function compressFileBase(event, maxWidth, maxHeight, callback) {
  const file = event.target.files?.[0];
  if (!file) return;
  if (!file.type.startsWith('image/')) return showToast('File harus berupa gambar.', 'error');
  const reader = new FileReader();
  reader.onload = (readerEvent) => {
    const image = new Image();
    image.onload = () => {
      const canvas = document.createElement('canvas');
      let width = image.width;
      let height = image.height;
      const ratio = Math.min(maxWidth / width, maxHeight / height, 1);
      width = Math.round(width * ratio);
      height = Math.round(height * ratio);
      canvas.width = width;
      canvas.height = height;
      canvas.getContext('2d').drawImage(image, 0, 0, width, height);
      callback(canvas.toDataURL('image/jpeg', 0.82));
      showToast('Gambar siap diunggah.');
    };
    image.src = readerEvent.target.result;
  };
  reader.readAsDataURL(file);
}

function populateCategoryDropdowns() {
  const options = categories.filter((category) => category.is_active !== false).map((category) => `<option value="${category.id}">${escapeHtml(category.name)}</option>`).join('');
  const pos = byId('posFilterCategory');
  const inventory = byId('inventoryCategoryFilter');
  const modal = byId('pCategory');
  if (pos) {
    const value = pos.value;
    pos.innerHTML = `<option value="">Semua Kategori</option>${options}`;
    pos.value = value;
  }
  if (inventory) {
    const value = inventory.value;
    inventory.innerHTML = `<option value="">Semua Kategori</option>${options}`;
    inventory.value = value;
  }
  if (modal) modal.innerHTML = options;
}

function refreshAllUI() {
  populateCategoryDropdowns();
  renderDashboard();
  renderAnalytics();
  renderPOS();
  renderCart();
  renderInventory();
  renderReport();
  renderCRM();
  renderExpenses();
  renderCategories();
  renderOutlets();
  renderUsers();
  renderProactiveInsights();
}

function setText(id, value) {
  const element = byId(id);
  if (element) element.textContent = value;
}

function growthPresentation(value) {
  const number = Number(value || 0);
  if (number > 0) return { text: `↑ ${formatPlainNumber(Math.abs(number))}% dari periode sebelumnya`, className: 'text-emerald-600' };
  if (number < 0) return { text: `↓ ${formatPlainNumber(Math.abs(number))}% dari periode sebelumnya`, className: 'text-rose-600' };
  return { text: 'Stabil dibanding periode sebelumnya', className: 'text-slate-400' };
}

function setGrowthText(id, value) {
  const element = byId(id);
  if (!element) return;
  const presentation = growthPresentation(value);
  element.textContent = presentation.text;
  element.className = `mt-3 text-xs font-bold ${presentation.className}`;
}

function initializePeriodControls() {
  const period = dashboardMetrics.period || {};
  const from = byId('periodFrom');
  const to = byId('periodTo');
  if (from) {
    from.value = period.from || from.value;
    from.max = period.to || '';
  }
  if (to) {
    to.value = period.to || to.value;
    to.max = new Date().toLocaleDateString('en-CA');
  }
  setText('activePeriodLabel', period.label || '-');
}

function setAnalysisPreset(preset) {
  const today = new Date();
  const start = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  if (preset === '7d') start.setDate(start.getDate() - 6);
  else if (preset === '30d') start.setDate(start.getDate() - 29);
  else if (preset === '90d') start.setDate(start.getDate() - 89);
  else if (preset === 'month') start.setDate(1);
  const format = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  };
  if (byId('periodFrom')) byId('periodFrom').value = format(start);
  if (byId('periodTo')) byId('periodTo').value = format(today);
}

async function applyDashboardPeriod() {
  const from = byId('periodFrom')?.value || '';
  const to = byId('periodTo')?.value || '';
  if (!from || !to) return showToast('Tanggal awal dan akhir wajib dipilih.', 'error');
  if (from > to) return showToast('Tanggal awal tidak boleh melewati tanggal akhir.', 'error');

  await withActionLoading('Menyeduh analisis bisnis untuk periode pilihanmu...', async () => {
    try {
      dashboardMetrics = await api(`/api/dashboard?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`);
      const url = new URL(window.location.href);
      url.searchParams.set('from', from);
      url.searchParams.set('to', to);
      window.history.replaceState({}, '', url);
      renderDashboard();
      renderAnalytics();
      renderProactiveInsights();
      showToast('Analisis periode berhasil diperbarui.');
    } catch (error) {
      showToast(error.message, 'error');
    }
  });
}

function renderDashboard() {
  const summary = dashboardMetrics.summary || {};
  const comparison = dashboardMetrics.comparison || {};
  const month = dashboardMetrics.month || {};
  const trend = dashboardMetrics.trend || [];
  initializePeriodControls();

  setText('periodRevenue', formatIDR(summary.revenue || 0));
  setText('periodNetProfit', formatIDR(summary.net_profit || 0));
  setText('periodTransactionCount', formatPlainNumber(summary.transaction_count || 0));
  setText('periodAverageBasket', formatIDR(summary.average_basket || 0));
  setGrowthText('periodRevenueGrowth', comparison.revenue);
  setGrowthText('periodNetProfitGrowth', comparison.net_profit);
  setGrowthText('periodTransactionGrowth', comparison.transaction_count);
  setGrowthText('periodAverageGrowth', comparison.average_basket);

  setText('monthRevenue', formatIDR(month.revenue || 0));
  setText('monthNetProfit', formatIDR(month.net_profit || 0));
  setText('monthProjection', formatIDR(month.projection || 0));
  setText('periodItemsSold', `${formatPlainNumber(summary.items_sold || 0)} item`);
  setText('periodMemberRate', `${summary.member_rate || 0}% transaksi member`);
  const monthGrowth = growthPresentation(month.revenue_growth);
  const monthGrowthElement = byId('monthRevenueGrowth');
  if (monthGrowthElement) {
    monthGrowthElement.textContent = monthGrowth.text.replace('periode sebelumnya', 'bulan lalu');
    monthGrowthElement.className = `mt-1 text-[10px] font-bold ${monthGrowth.className}`;
  }
  setText('monthNetMargin', `Margin bersih ${month.net_margin || 0}%`);
  setText('monthProjectionInfo', `${month.elapsed_days || 0}/${month.days_in_month || 0} hari berjalan`);
  setText('trendGranularityLabel', (dashboardMetrics.period?.days || 0) <= 45 ? 'Tren harian' : ((dashboardMetrics.period?.days || 0) <= 180 ? 'Tren mingguan' : 'Tren bulanan'));

  const lowStock = products.filter((product) => product.is_active !== false && product.stock <= product.minStock).sort((a, b) => a.stock - b.stock);
  setText('lowStockBadge', `${lowStock.length} Produk`);
  const list = byId('dashLowStockList');
  if (list) {
    list.innerHTML = lowStock.length ? lowStock.slice(0, 7).map((product) => `
      <div class="flex items-center justify-between rounded-2xl border border-red-100 bg-red-50/60 p-3 hover-lift">
        <div class="min-w-0"><p class="truncate text-xs font-bold text-slate-800">${escapeHtml(product.name)}</p><span class="text-[10px] text-slate-400">Minimum ${formatPlainNumber(product.minStock)}</span></div>
        <span class="text-xs font-extrabold ${product.stock <= 0 ? 'text-rose-700' : 'text-red-600'}">${formatPlainNumber(product.stock)}</span>
      </div>`).join('') : '<div class="rounded-2xl bg-emerald-50 p-6 text-center text-xs text-emerald-600"><i class="bx bx-check-shield mb-1 block text-2xl"></i>Semua stok aman</div>';
  }

  if (salesChartInstance) salesChartInstance.destroy();
  const chart = byId('salesChart');
  if (chart && window.Chart) {
    salesChartInstance = new Chart(chart, {
      type: 'line',
      data: {
        labels: trend.map((entry) => entry.label),
        datasets: [
          { label: 'Pendapatan', data: trend.map((entry) => entry.revenue), borderColor: '#C00000', backgroundColor: 'rgba(192,0,0,.08)', fill: true, tension: .38, pointRadius: trend.length > 40 ? 1 : 3, pointHoverRadius: 6, borderWidth: 2.5 },
          { label: 'Laba Bersih', data: trend.map((entry) => entry.net_profit), borderColor: '#059669', backgroundColor: 'rgba(5,150,105,.05)', tension: .38, pointRadius: trend.length > 40 ? 1 : 3, borderWidth: 2 },
          { label: 'Biaya', data: trend.map((entry) => entry.expenses), borderColor: '#f59e0b', borderDash: [6, 5], tension: .3, pointRadius: 0, borderWidth: 2 },
        ],
      },
      options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, animation: { duration: 850 }, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 18 } }, tooltip: { callbacks: { label: (context) => `${context.dataset.label}: ${formatIDR(context.raw)}` } } }, scales: { y: { beginAtZero: true, ticks: { callback: (value) => value >= 1000000 ? `${Math.round(value / 1000000)} jt` : `${Math.round(value / 1000)} rb` }, grid: { color: 'rgba(148,163,184,.12)' } }, x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } } } },
    });
  }

  const topProducts = dashboardMetrics.top_products || [];
  const topContainer = byId('dashboardTopProducts');
  if (topContainer) {
    const maxRevenue = Math.max(1, ...topProducts.map((item) => Number(item.revenue || 0)));
    topContainer.innerHTML = topProducts.length ? topProducts.slice(0, 5).map((item, index) => `
      <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-3.5">
        <div class="flex items-center gap-3"><span class="flex h-8 w-8 flex-none items-center justify-center rounded-xl ${index === 0 ? 'bg-amber-100 text-amber-700' : 'bg-white text-slate-500'} text-xs font-extrabold">${index + 1}</span><div class="min-w-0 flex-1"><div class="flex items-center justify-between gap-3"><p class="truncate text-xs font-extrabold text-slate-800">${escapeHtml(item.name)}</p><span class="text-xs font-extrabold text-red-700">${formatIDR(item.revenue)}</span></div><div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200"><div class="h-full rounded-full bg-gradient-to-r from-red-700 to-amber-500" style="width:${Math.max(5, (Number(item.revenue) / maxRevenue) * 100)}%"></div></div><p class="mt-1.5 text-[10px] text-slate-400">${formatPlainNumber(item.quantity)} item • Profit ${formatIDR(item.profit)}</p></div></div>
      </div>`).join('') : '<div class="rounded-2xl bg-slate-50 p-8 text-center text-xs text-slate-400">Belum ada data menu pada periode ini.</div>';
  }

  if (paymentMixChartInstance) paymentMixChartInstance.destroy();
  const paymentCanvas = byId('paymentMixChart');
  const paymentMix = dashboardMetrics.payment_mix || [];
  if (paymentCanvas && window.Chart) {
    paymentMixChartInstance = new Chart(paymentCanvas, {
      type: 'doughnut',
      data: { labels: paymentMix.map((row) => row.label), datasets: [{ data: paymentMix.map((row) => row.value), backgroundColor: ['#C00000', '#4f46e5', '#f59e0b', '#059669', '#64748b'], borderColor: '#fff', borderWidth: 4, hoverOffset: 8 }] },
      options: { responsive: true, maintainAspectRatio: false, cutout: '66%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 9, padding: 16 } }, tooltip: { callbacks: { label: (context) => `${context.label}: ${formatIDR(context.raw)}` } } } },
    });
  }
}

function renderAnalytics() {
  const summary = dashboardMetrics.summary || {};
  const trend = dashboardMetrics.trend || [];
  const contributions = dashboardMetrics.category_contribution || [];
  const topProducts = dashboardMetrics.top_products || [];
  const peakHours = dashboardMetrics.peak_hours || [];
  const paymentMix = dashboardMetrics.payment_mix || [];
  initializePeriodControls();

  setText('anRevenue', formatIDR(summary.revenue || 0));
  setText('anNetProfit', formatIDR(summary.net_profit || 0));
  setText('anTotalExpenses', formatIDR(summary.expenses || 0));
  setText('anAvgBasket', formatIDR(summary.average_basket || 0));
  setText('anGrossMargin', `${summary.gross_margin || 0}%`);
  setText('anProfitRatio', `${summary.net_margin || 0}%`);
  setText('anMemberRate', `${summary.member_rate || 0}%`);
  setText('anRepeatRate', `${summary.repeat_customer_rate || 0}%`);
  setText('anExpenseRatio', `${summary.expense_ratio || 0}%`);
  setText('anAverageItems', `${summary.average_items || 0}`);
  setText('anTaxCollected', formatIDR(summary.tax_collected || 0));
  setText('anDiscountTotal', formatIDR(summary.discount_total || 0));

  if (categoryChartInstance) categoryChartInstance.destroy();
  const categoryCanvas = byId('categoryChart');
  if (categoryCanvas && window.Chart) {
    categoryChartInstance = new Chart(categoryCanvas, {
      type: 'doughnut',
      data: { labels: contributions.map((row) => row.label), datasets: [{ data: contributions.map((row) => row.value), backgroundColor: ['#C00000', '#e11d48', '#f97316', '#f59e0b', '#4f46e5', '#059669', '#64748b'], borderColor: '#fff', borderWidth: 4, hoverOffset: 8 }] },
      options: { responsive: true, maintainAspectRatio: false, cutout: '66%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 9, usePointStyle: true, padding: 14 } }, tooltip: { callbacks: { label: (context) => `${context.label}: ${formatIDR(context.raw)}` } } } },
    });
  }

  if (cashflowChartInstance) cashflowChartInstance.destroy();
  const cashflowCanvas = byId('cashflowChart');
  if (cashflowCanvas && window.Chart) {
    cashflowChartInstance = new Chart(cashflowCanvas, {
      type: 'line',
      data: { labels: trend.map((row) => row.label), datasets: [
        { label: 'Pendapatan', data: trend.map((row) => row.revenue), borderColor: '#C00000', backgroundColor: 'rgba(192,0,0,.08)', fill: true, tension: .38, pointRadius: trend.length > 40 ? 1 : 3, borderWidth: 2.5 },
        { label: 'Laba Kotor', data: trend.map((row) => row.gross_profit), borderColor: '#4f46e5', tension: .35, pointRadius: trend.length > 40 ? 1 : 3, borderWidth: 2 },
        { label: 'Biaya', data: trend.map((row) => row.expenses), borderColor: '#f59e0b', borderDash: [5, 5], tension: .3, pointRadius: 0, borderWidth: 2 },
        { label: 'Laba Bersih', data: trend.map((row) => row.net_profit), borderColor: '#059669', tension: .35, pointRadius: trend.length > 40 ? 1 : 3, borderWidth: 2 },
      ] },
      options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 16 } }, tooltip: { callbacks: { label: (context) => `${context.dataset.label}: ${formatIDR(context.raw)}` } } }, scales: { y: { beginAtZero: true, ticks: { callback: (value) => value >= 1000000 ? `${Math.round(value / 1000000)} jt` : `${Math.round(value / 1000)} rb` }, grid: { color: 'rgba(148,163,184,.12)' } }, x: { grid: { display: false }, ticks: { maxTicksLimit: 12, maxRotation: 0 } } } },
    });
  }

  if (topProductsChartInstance) topProductsChartInstance.destroy();
  const topCanvas = byId('topProductsChart');
  if (topCanvas && window.Chart) {
    topProductsChartInstance = new Chart(topCanvas, {
      type: 'bar',
      data: { labels: topProducts.slice(0, 6).map((row) => row.name), datasets: [{ label: 'Omzet', data: topProducts.slice(0, 6).map((row) => row.revenue), backgroundColor: ['#C00000', '#dc2626', '#e11d48', '#f97316', '#f59e0b', '#4f46e5'], borderRadius: 8 }] },
      options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: (context) => formatIDR(context.raw) } } }, scales: { x: { beginAtZero: true, ticks: { callback: (value) => `${Math.round(value / 1000)} rb` }, grid: { color: 'rgba(148,163,184,.12)' } }, y: { grid: { display: false }, ticks: { autoSkip: false } } } },
    });
  }

  if (peakHoursChartInstance) peakHoursChartInstance.destroy();
  const peakCanvas = byId('peakHoursChart');
  if (peakCanvas && window.Chart) {
    peakHoursChartInstance = new Chart(peakCanvas, {
      type: 'line',
      data: { labels: peakHours.map((row) => row.label), datasets: [{ label: 'Jumlah Transaksi', data: peakHours.map((row) => row.count), borderColor: '#4f46e5', backgroundColor: 'rgba(79,70,229,.12)', fill: true, tension: .42, pointBackgroundColor: '#fff', pointBorderColor: '#4f46e5', pointRadius: 3, pointHoverRadius: 6 }] },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { afterLabel: (context) => `Omzet: ${formatIDR(peakHours[context.dataIndex]?.revenue || 0)}` } } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(148,163,184,.12)' } }, x: { grid: { display: false } } } },
    });
  }

  if (analyticsPaymentChartInstance) analyticsPaymentChartInstance.destroy();
  const analyticsPaymentCanvas = byId('analyticsPaymentChart');
  if (analyticsPaymentCanvas && window.Chart) {
    analyticsPaymentChartInstance = new Chart(analyticsPaymentCanvas, {
      type: 'polarArea',
      data: { labels: paymentMix.map((row) => row.label), datasets: [{ data: paymentMix.map((row) => row.count), backgroundColor: ['rgba(192,0,0,.75)', 'rgba(79,70,229,.75)', 'rgba(245,158,11,.75)', 'rgba(5,150,105,.75)', 'rgba(100,116,139,.75)'], borderWidth: 0 }] },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } }, tooltip: { callbacks: { label: (context) => `${context.label}: ${context.raw} transaksi` } } }, scales: { r: { ticks: { display: false }, grid: { color: 'rgba(148,163,184,.12)' } } } },
    });
  }

  const insightContainer = byId('analyticsInsightList');
  if (insightContainer) {
    insightContainer.innerHTML = (dashboardMetrics.insights || []).map((insight) => `<div class="rounded-2xl border border-indigo-100 bg-indigo-50/50 p-3"><p class="text-[10px] font-extrabold uppercase text-indigo-600">${escapeHtml(insight.title)}</p><p class="mt-1 text-xs leading-5 text-slate-600">${escapeHtml(insight.message)}</p></div>`).join('');
  }
}

function renderProactiveInsights() {
  const container = byId('proactiveInsightsList');
  if (!container) return;
  const insights = dashboardMetrics.insights || [];
  const styles = {
    warning: ['bg-amber-50 border-amber-200', 'text-amber-600', 'bx-error'],
    danger: ['bg-rose-50 border-rose-200', 'text-rose-600', 'bx-trending-down'],
    success: ['bg-emerald-50 border-emerald-200', 'text-emerald-600', 'bx-shield'],
    info: ['bg-slate-50 border-slate-200', 'text-slate-600', 'bx-line-chart'],
  };
  container.innerHTML = insights.map((insight) => {
    const style = styles[insight.type] || styles.info;
    return `<div class="p-4 border rounded-2xl flex gap-3 ${style[0]} hover-lift"><div class="w-8 h-8 rounded-xl ${style[1]} bg-white flex items-center justify-center"><i class="bx ${style[2]}"></i></div><div><h4 class="font-bold text-xs uppercase mb-1">${escapeHtml(insight.title)}</h4><p class="text-xs text-slate-700">${escapeHtml(insight.message)}</p></div></div>`;
  }).join('');
}

function renderPOS() {
  const grid = byId('posProductsGrid');
  if (!grid) return;
  const query = (byId('posSearch')?.value || '').toLowerCase();
  const categoryId = byId('posFilterCategory')?.value || '';
  const filtered = products.filter((product) => product.is_active !== false && product.name.toLowerCase().includes(query) && (!categoryId || String(product.category_id) === String(categoryId)));
  grid.innerHTML = filtered.length ? filtered.map((product) => {
    const unavailable = product.stock <= 0;
    const productImage = getProductImage(product);
    return `<button type="button" onclick="addToCart(${product.id})" ${unavailable ? 'disabled' : ''} class="group text-left bg-slate-50 hover:bg-white border border-slate-100 hover:border-red-200 rounded-2xl p-3 transition-all hover:-translate-y-1 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
      <div class="h-28 rounded-xl overflow-hidden bg-gradient-to-br from-red-50 to-amber-50 mb-3 relative">
        <img src="${escapeHtml(productImage)}" alt="${escapeHtml(product.name)}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        <span class="absolute top-2 right-2 px-2 py-1 rounded-lg text-[9px] font-bold ${product.stock <= product.minStock ? 'bg-red-600 text-white' : 'bg-white/90 text-slate-600'}">Stok ${product.stock}</span>
      </div>
      <p class="text-[10px] font-semibold text-red-600 uppercase mb-1">${escapeHtml(product.category || '')}</p>
      <h4 class="text-xs font-bold text-slate-900 line-clamp-2 min-h-[32px]">${escapeHtml(product.name)}</h4>
      <p class="text-sm font-extrabold text-slate-950 mt-2">${formatIDR(product.price)}</p>
    </button>`;
  }).join('') : '<div class="col-span-full p-10 text-center text-slate-400"><i class="bx bx-search-alt text-4xl block mb-2"></i>Menu tidak ditemukan</div>';
}

function addToCart(productId) {
  const product = products.find((item) => Number(item.id) === Number(productId));
  if (!product || product.stock <= 0) return showToast('Stok produk habis.', 'error');
  const existing = cart.find((item) => Number(item.id) === Number(productId));
  if (existing) {
    if (existing.qty >= product.stock) return showToast('Jumlah melebihi stok tersedia.', 'error');
    existing.qty += 1;
  } else {
    cart.push({ ...product, qty: 1 });
  }
  renderCart();
}

function renderCart() {
  const list = byId('cartList');
  if (!list) return;
  list.innerHTML = cart.length ? cart.map((item, index) => { const itemImage = getProductImage(item); return `
    <div class="py-3 flex items-center gap-3 cart-item-enter">
      <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center flex-none overflow-hidden"><img src="${escapeHtml(itemImage)}" class="w-full h-full object-cover"></div>
      <div class="flex-1 min-w-0"><p class="text-xs font-bold text-slate-900 truncate">${escapeHtml(item.name)}</p><p class="text-[10px] text-slate-400">${formatIDR(item.price)}</p></div>
      <div class="flex items-center gap-1 bg-slate-50 rounded-xl p-1"><button onclick="updateCartQty(${index}, -1)" class="w-7 h-7 rounded-lg hover:bg-white">−</button><span class="w-6 text-center text-xs font-bold">${item.qty}</span><button onclick="updateCartQty(${index}, 1)" class="w-7 h-7 rounded-lg hover:bg-white">+</button></div>
      <button onclick="removeFromCart(${index})" class="p-1.5 text-slate-300 hover:text-red-500"><i class="bx bx-x"></i></button>
    </div>`; }).join('') : '<div class="py-10 text-center text-slate-400"><i class="bx bx-cart text-4xl block mb-2"></i><p class="text-xs">Keranjang masih kosong</p></div>';
  calculateCartTotal();
}

function updateCartQty(index, offset) {
  const item = cart[index];
  const product = products.find((productRow) => Number(productRow.id) === Number(item.id));
  const next = item.qty + offset;
  if (next <= 0) return removeFromCart(index);
  if (next > product.stock) return showToast('Jumlah melebihi stok tersedia.', 'error');
  item.qty = next;
  renderCart();
}

function removeFromCart(index) { cart.splice(index, 1); renderCart(); }
function clearCart() { cart = []; clearSelectedPOSCustomer({ silent: true }); renderCart(); }

function cartCalculation() {
  const subtotal = cart.reduce((sum, item) => sum + Number(item.price) * item.qty, 0);
  const discountPercentage = Math.min(100, Math.max(0, readNumberInput('cartDiscount')));
  const discountAmount = subtotal * (discountPercentage / 100);
  const afterDiscount = subtotal - discountAmount;
  const serviceAmount = afterDiscount * (config.serviceCharge / 100);
  const taxAmount = (afterDiscount + serviceAmount) * (config.taxRate / 100);
  let pointsDiscount = 0;
  let pointsRedeemed = 0;
  const customer = customers.find((item) => Number(item.id) === Number(selectedCustomerId));
  const beforePoints = afterDiscount + serviceAmount + taxAmount;
  if (customer && isPointsApplied && customer.points >= 20) {
    pointsRedeemed = Math.min(customer.points, Math.floor(beforePoints / Math.max(1, config.pointValue)));
    pointsDiscount = Math.min(beforePoints, pointsRedeemed * config.pointValue);
  }
  const total = Math.max(0, beforePoints - pointsDiscount);
  return { subtotal, discountPercentage, discountAmount, serviceAmount, taxAmount, pointsDiscount, pointsRedeemed, total };
}

function calculateCartTotal() {
  const calc = cartCalculation();
  if (byId('cartSubtotal')) byId('cartSubtotal').textContent = formatIDR(calc.subtotal);
  if (byId('cartServiceLabel')) byId('cartServiceLabel').textContent = `Service Charge (${config.serviceCharge}%)`;
  if (byId('cartServiceAmount')) byId('cartServiceAmount').textContent = formatIDR(calc.serviceAmount);
  if (byId('cartTaxLabel')) byId('cartTaxLabel').textContent = `Pajak (${config.taxRate}%)`;
  if (byId('cartTaxAmount')) byId('cartTaxAmount').textContent = formatIDR(calc.taxAmount);
  if (byId('cartPointsDiscount')) byId('cartPointsDiscount').textContent = `-${formatIDR(calc.pointsDiscount)}`;
  byId('cartPointsRow')?.classList.toggle('hidden', calc.pointsDiscount <= 0);
  if (byId('cartTotal')) byId('cartTotal').textContent = formatIDR(calc.total);
  calculateChange();
  if (currentPaymentMode === 'QRIS') generateQRISCode();
}

function calculateChange() {
  const total = cartCalculation().total;
  const paid = readNumberInput('cashInput');
  if (byId('cashChange')) byId('cashChange').textContent = formatIDR(Math.max(0, paid - total));
}

function setPaymentMode(mode) {
  currentPaymentMode = mode;
  const cashBtn = byId('payModeCash');
  const qrisBtn = byId('payModeQRIS');
  const active = 'py-2.5 px-3 bg-[#C00000] text-white font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 transition-all';
  const inactive = 'py-2.5 px-3 bg-white border border-slate-200 text-slate-600 font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 transition-all';
  cashBtn.className = mode === 'Tunai' ? active : inactive;
  qrisBtn.className = mode === 'QRIS' ? active : inactive;
  byId('cashPayArea').classList.toggle('hidden', mode !== 'Tunai');
  byId('qrisPayArea').classList.toggle('hidden', mode !== 'QRIS');
  if (mode === 'QRIS') generateQRISCode();
}

function generateQRISCode() {
  const container = byId('qrisContainer');
  if (!container) return;
  const source = imageSource(config.qrisImage || '/images/qris/where-coffee-qris.png');
  container.innerHTML = `<button type="button" onclick="openQrisModal()" class="h-full w-full rounded-xl bg-white p-2 transition-all hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-red-100" aria-label="Perbesar QRIS untuk scan"><img src="${escapeHtml(source)}" class="h-full w-full object-contain" alt="QRIS statis Where Coffee"></button>`;
}

function openQrisModal() {
  const modal = byId('qrisPreviewModal');
  const image = byId('qrisPreviewImage');
  if (!modal || !image) return;
  const source = imageSource(config.qrisImage || '/images/qris/where-coffee-qris.png');
  image.src = source;
  modal.classList.remove('hidden');
  window.setTimeout(() => modal.querySelector('button[aria-label="Tutup QRIS"]')?.focus(), 50);
}

function closeQrisModal() {
  const modal = byId('qrisPreviewModal');
  if (!modal) return;
  modal.classList.add('hidden');
  const image = byId('qrisPreviewImage');
  if (image) image.removeAttribute('src');
}

function handleQrisModalBackdrop(event) {
  if (!event.target.closest('[data-qris-preview-panel]')) closeQrisModal();
}

function simulateQRISSuccess() {
  confirmCheckout();
}

function confirmCheckout() {
  if (!cart.length) return showToast('Keranjang masih kosong.', 'error');
  const calc = cartCalculation();
  const amountPaid = currentPaymentMode === 'Tunai' ? readNumberInput('cashInput') : calc.total;
  if (currentPaymentMode === 'Tunai' && amountPaid < calc.total) return showToast('Pembayaran tunai masih kurang.', 'error');
  const customer = customers.find((item) => Number(item.id) === Number(selectedCustomerId));
  const details = `<div class="mt-3 rounded-2xl border border-slate-100 bg-slate-50 p-3 text-xs">
    <div class="flex justify-between gap-4"><span>Total tagihan</span><strong class="text-red-700">${formatIDR(calc.total)}</strong></div>
    <div class="mt-1 flex justify-between gap-4"><span>Metode</span><strong>${escapeHtml(currentPaymentMode)}</strong></div>
    <div class="mt-1 flex justify-between gap-4"><span>Member</span><strong class="max-w-[180px] truncate">${customer ? escapeHtml(customer.name) : 'Non-member'}</strong></div>
  </div>`;
  showCustomConfirm(`Pastikan pembayaran sudah diterima sebelum transaksi diproses.${details}`, processCheckout, {
    title: 'Konfirmasi pembayaran',
    confirmText: 'Ya, Proses Pembayaran',
    icon: 'bx-check-shield',
    html: true,
  });
}

async function processCheckout() {
  if (!cart.length) return showToast('Keranjang masih kosong.', 'error');
  const calc = cartCalculation();
  const amountPaid = currentPaymentMode === 'Tunai' ? readNumberInput('cashInput') : calc.total;
  if (currentPaymentMode === 'Tunai' && amountPaid < calc.total) return showToast('Pembayaran tunai masih kurang.', 'error');

  const payload = {
    items: cart.map((item) => ({ product_id: Number(item.id), quantity: item.qty })),
    customer_id: selectedCustomerId ? Number(selectedCustomerId) : null,
    discount_percentage: calc.discountPercentage,
    payment_method: currentPaymentMode,
    amount_paid: amountPaid,
    use_points: isPointsApplied,
  };

  try {
    setActionLoading(true, 'Memproses pembayaran...');
    const response = await api('/api/transactions', { method: 'POST', body: payload });
    cart = [];
    clearSelectedPOSCustomer({ silent: true });
    setNumberInputValue('cartDiscount', 0);
    setNumberInputValue('cashInput', '');
    await bootstrapApplication({ quiet: true });
    setActionLoading(false);
    openInvoiceModal(response.data);
    showToast(response.message);
  } catch (error) {
    setActionLoading(false);
    showToast(error.message, 'error');
  }
}

function closePOSCustomerResults() {
  const results = byId('posCustomerResults');
  const input = byId('posCustomerSearch');
  results?.classList.add('hidden');
  input?.setAttribute('aria-expanded', 'false');
  customerSearchActiveIndex = -1;
}

function renderPOSCustomerResults(message = '') {
  const container = byId('posCustomerResults');
  const input = byId('posCustomerSearch');
  if (!container || !input) return;

  if (message) {
    container.innerHTML = `<div class="px-3 py-4 text-center text-[11px] text-slate-400">${escapeHtml(message)}</div>`;
  } else if (!customerSearchResults.length) {
    container.innerHTML = '<div class="px-3 py-4 text-center text-[11px] text-slate-400">Member tidak ditemukan.</div>';
  } else {
    container.innerHTML = customerSearchResults.map((customer, index) => `
      <button type="button" data-member-index="${index}" onclick="choosePOSCustomer(${customer.id})" class="member-result ${index === customerSearchActiveIndex ? 'member-result-active' : ''} w-full flex items-center gap-3 rounded-xl px-3 py-2.5 text-left transition-all">
        <div class="w-9 h-9 flex-none rounded-xl bg-gradient-to-br from-red-100 to-orange-100 text-red-700 flex items-center justify-center font-extrabold text-xs">${escapeHtml(customer.name.substring(0, 2).toUpperCase())}</div>
        <div class="min-w-0 flex-1"><div class="text-xs font-extrabold text-slate-900 truncate">${escapeHtml(customer.name)}</div><div class="text-[10px] text-slate-400 truncate">${escapeHtml(customer.member_code || 'Member')} • ${escapeHtml(customer.tier || 'Bronze')}</div></div>
        <div class="text-right flex-none"><div class="text-[10px] font-extrabold text-red-700">${formatPlainNumber(customer.points || 0)} poin</div><div class="text-[9px] text-slate-400">Pilih</div></div>
      </button>`).join('');
  }

  container.classList.remove('hidden');
  input.setAttribute('aria-expanded', 'true');
}

function openPOSCustomerResults() {
  const input = byId('posCustomerSearch');
  if (!input || selectedCustomerId) return;
  const query = input.value.trim();
  if (query.length < 2) return renderPOSCustomerResults('Ketik minimal 2 karakter untuk mencari member.');
  if (customerSearchResults.length) renderPOSCustomerResults();
  else searchPOSCustomers();
}

function searchPOSCustomers() {
  const input = byId('posCustomerSearch');
  if (!input) return;

  const query = input.value.trim();
  const selected = customers.find((customer) => Number(customer.id) === Number(selectedCustomerId));
  if (selected && query !== selected.name) {
    selectedCustomerId = '';
    isPointsApplied = false;
    updateSelectedCustomerUI();
  }

  window.clearTimeout(customerSearchTimer);
  customerSearchResults = [];
  customerSearchActiveIndex = -1;

  if (query.length < 2) {
    return query.length ? renderPOSCustomerResults('Ketik minimal 2 karakter untuk mencari member.') : closePOSCustomerResults();
  }

  renderPOSCustomerResults('Mencari member...');
  const requestNumber = ++customerSearchRequest;
  customerSearchTimer = window.setTimeout(async () => {
    try {
      const results = await api(`/api/customers/search?q=${encodeURIComponent(query)}`);
      if (requestNumber !== customerSearchRequest || byId('posCustomerSearch')?.value.trim() !== query) return;
      customerSearchResults = Array.isArray(results) ? results : [];
      customerSearchActiveIndex = customerSearchResults.length ? 0 : -1;
      renderPOSCustomerResults();
    } catch (error) {
      if (requestNumber !== customerSearchRequest) return;
      renderPOSCustomerResults(error.message || 'Pencarian member gagal.');
    }
  }, 250);
}

function handlePOSCustomerSearchKeydown(event) {
  const container = byId('posCustomerResults');
  if (!container || container.classList.contains('hidden')) {
    if (event.key === 'ArrowDown') openPOSCustomerResults();
    return;
  }

  if (event.key === 'Escape') {
    event.preventDefault();
    closePOSCustomerResults();
    return;
  }
  if (!customerSearchResults.length) return;
  if (event.key === 'ArrowDown') {
    event.preventDefault();
    customerSearchActiveIndex = (customerSearchActiveIndex + 1) % customerSearchResults.length;
    renderPOSCustomerResults();
  } else if (event.key === 'ArrowUp') {
    event.preventDefault();
    customerSearchActiveIndex = (customerSearchActiveIndex - 1 + customerSearchResults.length) % customerSearchResults.length;
    renderPOSCustomerResults();
  } else if (event.key === 'Enter' && customerSearchActiveIndex >= 0) {
    event.preventDefault();
    choosePOSCustomer(customerSearchResults[customerSearchActiveIndex].id);
  }
}

function choosePOSCustomer(customerId) {
  const customer = customerSearchResults.find((item) => Number(item.id) === Number(customerId))
    || customers.find((item) => Number(item.id) === Number(customerId));
  if (!customer) return;
  if (!customers.some((item) => Number(item.id) === Number(customer.id))) customers.push(customer);

  selectedCustomerId = String(customer.id);
  isPointsApplied = false;
  if (byId('posCustomerSearch')) byId('posCustomerSearch').value = customer.name;
  closePOSCustomerResults();
  updateSelectedCustomerUI();
  calculateCartTotal();
}

function selectPOSCustomer(customerId) {
  if (customerId) choosePOSCustomer(customerId);
  else updateSelectedCustomerUI();
}

function clearSelectedPOSCustomer({ silent = false } = {}) {
  selectedCustomerId = '';
  isPointsApplied = false;
  customerSearchResults = [];
  customerSearchActiveIndex = -1;
  if (byId('posCustomerSearch')) byId('posCustomerSearch').value = '';
  closePOSCustomerResults();
  updateSelectedCustomerUI();
  calculateCartTotal();
  if (!silent) byId('posCustomerSearch')?.focus();
}

function updateSelectedCustomerUI() {
  const customer = customers.find((item) => Number(item.id) === Number(selectedCustomerId));
  const button = byId('usePointsBtn');
  const info = byId('memberBonusInfo');
  const summary = byId('selectedCustomerSummary');
  const clearButton = byId('clearSelectedCustomerBtn');

  if (customer) {
    info && (info.textContent = `Tier ${customer.tier}`);
    if (summary) {
      summary.innerHTML = `<div class="flex items-center justify-between gap-3"><span><strong>${escapeHtml(customer.name)}</strong> • ${escapeHtml(customer.member_code || 'Member')}</span><span class="font-extrabold">${formatPlainNumber(customer.points || 0)} poin</span></div>`;
      summary.classList.remove('hidden');
    }
    clearButton?.classList.remove('hidden');
    clearButton?.classList.add('flex');
  } else {
    if (info) info.textContent = '';
    summary?.classList.add('hidden');
    clearButton?.classList.add('hidden');
    clearButton?.classList.remove('flex');
    isPointsApplied = false;
  }

  if (button) {
    button.className = `${isPointsApplied ? 'bg-[#C00000] text-white' : 'bg-slate-200 text-slate-600'} px-3 py-2.5 font-bold text-xs rounded-xl transition-all whitespace-nowrap ${customer ? '' : 'hidden'}`;
    button.textContent = isPointsApplied ? 'Poin Dipakai' : 'Pakai Poin';
  }
}

function toggleUseCustomerPoints() {
  const customer = customers.find((item) => Number(item.id) === Number(selectedCustomerId));
  if (!customer) return;
  if (customer.points < 20) return showToast('Minimal 20 poin untuk ditukarkan.', 'error');
  isPointsApplied = !isPointsApplied;
  updateSelectedCustomerUI();
  calculateCartTotal();
  const calc = cartCalculation();
  showToast(isPointsApplied ? `${calc.pointsRedeemed} poin akan digunakan.` : 'Penggunaan poin dibatalkan.');
}

function renderInventory() {
  const body = byId('inventoryTableBody');
  if (!body) return;
  const query = (byId('inventorySearch')?.value || '').toLowerCase();
  const categoryId = byId('inventoryCategoryFilter')?.value || '';
  const status = byId('inventoryStatusFilter')?.value || 'active';
  const filtered = products.filter((product) => {
    const matchesQuery = product.name.toLowerCase().includes(query) || product.barcode.toLowerCase().includes(query) || product.sku.toLowerCase().includes(query);
    const matchesCategory = !categoryId || String(product.category_id) === String(categoryId);
    const matchesStatus = status === 'all' || (status === 'active' ? product.is_active !== false : product.is_active === false);
    return matchesQuery && matchesCategory && matchesStatus;
  });
  const visible = pageSlice('inventory', filtered, `${query}|${categoryId}|${status}`);
  byId('inventoryEmptyState')?.classList.toggle('hidden', filtered.length > 0);
  body.innerHTML = visible.map((product) => {
    const low = product.stock <= product.minStock;
    const out = product.stock <= 0;
    const isActive = product.is_active !== false;
    const statusAction = isActive
      ? `<button type="button" onclick="deleteProduct(${product.id})" title="Hapus atau nonaktifkan produk" aria-label="Hapus atau nonaktifkan ${escapeHtml(product.name)}" class="p-2 bg-slate-50 hover:bg-red-50 hover:text-red-600 text-slate-400 rounded-xl"><i class="bx bx-trash"></i></button>`
      : `<button type="button" onclick="activateProduct(${product.id})" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 px-3 py-2 font-bold text-emerald-700 transition-colors hover:bg-emerald-100"><i class="bx bx-check-circle text-base"></i><span>Aktifkan</span></button>`;
    return `<tr class="hover:bg-slate-50 transition-all text-xs ${isActive ? '' : 'bg-slate-50/60 text-slate-500'}">
      <td class="p-4 pl-6"><div class="font-bold text-slate-900">${escapeHtml(product.sku)}</div><div class="font-mono text-[10px] text-slate-400">${escapeHtml(product.barcode)}</div></td>
      <td class="p-4 font-bold text-slate-800">${escapeHtml(product.name)}</td><td class="p-4">${escapeHtml(product.category || '')}</td>
      <td class="p-4 text-right">${formatIDR(product.capital)}</td><td class="p-4 text-right font-bold">${formatIDR(product.price)}</td>
      <td class="p-4 text-center font-extrabold ${out ? 'text-rose-700' : low ? 'text-red-600' : 'text-slate-800'}">${formatPlainNumber(product.stock)}</td>
      <td class="p-4 text-center"><span class="px-2.5 py-1 rounded-lg text-[10px] font-bold ${out ? 'bg-rose-100 text-rose-700' : low ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700'}">${out ? 'Habis' : low ? 'Menipis' : 'Aman'}</span></td>
      <td class="p-4 text-center"><span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[10px] font-bold ${isActive ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-200 text-slate-600'}"><span class="h-1.5 w-1.5 rounded-full ${isActive ? 'bg-emerald-500' : 'bg-slate-400'}"></span>${isActive ? 'Aktif' : 'Nonaktif'}</span></td>
      <td class="p-4 pr-6 text-center"><div class="flex items-center justify-center gap-1"><button type="button" onclick="editProduct(${product.id})" title="Edit produk" aria-label="Edit ${escapeHtml(product.name)}" class="p-2 bg-slate-50 hover:bg-amber-50 hover:text-amber-600 text-slate-400 rounded-xl"><i class="bx bx-edit"></i></button>${statusAction}</div></td>
    </tr>`;
  }).join('');
  renderTablePagination('inventory', filtered.length, 'inventoryPagination');
}

function openProductModal() {
  byId('productForm').reset();
  setNumberInputValue('pCapital', 0);
  setNumberInputValue('pPrice', 0);
  setNumberInputValue('pStock', 0);
  setNumberInputValue('pMinStock', 5);
  byId('pId').value = '';
  byId('pImgUrl').value = '';
  updateProductImagePreview();
  byId('modalTitle').textContent = 'Tambah Menu';
  byId('productModal').classList.remove('hidden');
}
function closeProductModal() {
  byId('productModal').classList.add('hidden');
  updateProductImagePreview();
}
function editProduct(id) {
  const product = products.find((item) => Number(item.id) === Number(id));
  if (!product) return;
  byId('productForm').reset();
  byId('pId').value = product.id;
  byId('pBarcode').value = product.barcode;
  byId('pName').value = product.name;
  byId('pCategory').value = product.category_id;
  setNumberInputValue('pCapital', product.capital);
  setNumberInputValue('pPrice', product.price);
  setNumberInputValue('pStock', product.stock);
  setNumberInputValue('pMinStock', product.minStock);
  byId('pImgUrl').value = product.image || '';
  updateProductImagePreview(product.image || '');
  byId('modalTitle').textContent = 'Edit Menu';
  byId('productModal').classList.remove('hidden');
}

async function saveProduct(event) {
  event.preventDefault();
  const id = byId('pId').value;
  const imageValue = byId('pImgUrl').value.trim();
  const payload = {
    category_id: Number(byId('pCategory').value),
    barcode: byId('pBarcode').value.trim(),
    name: byId('pName').value.trim(),
    cost_price: readNumberInput('pCapital'),
    selling_price: readNumberInput('pPrice'),
    stock: readNumberInput('pStock'),
    min_stock: readNumberInput('pMinStock'),
    unit: 'porsi',
  };
  if (imageValue.startsWith('data:image/')) payload.image_data = imageValue;
  else if (/^https?:\/\//i.test(imageValue)) payload.image_url = imageValue;
  try {
    setActionLoading(true, 'Menyimpan menu...');
    const response = await api(id ? `/api/products/${id}` : '/api/products', { method: id ? 'PUT' : 'POST', body: payload });
    showToast(response.message);
    closeProductModal();
    await bootstrapApplication({ quiet: true });
  } catch (error) { showToast(error.message, 'error'); } finally { setActionLoading(false); }
}

function deleteProduct(id) {
  showCustomConfirm('Hapus menu ini? Produk dengan riwayat transaksi akan dinonaktifkan agar laporan tetap konsisten.', async () => {
    try {
      setActionLoading(true, 'Menghapus menu...');
      const response = await api(`/api/products/${id}`, { method: 'DELETE' });
      await bootstrapApplication({ quiet: true });
      showToast(response.message);
    } catch (error) {
      showToast(error.message, 'error');
    } finally {
      setActionLoading(false);
    }
  }, { confirmText: 'Ya, Proses', icon: 'bx-trash' });
}

function activateProduct(id) {
  const product = products.find((item) => Number(item.id) === Number(id));
  if (!product) return;
  showCustomConfirm(`Aktifkan kembali ${product.name}? Produk akan kembali tersedia di halaman kasir.`, async () => {
    try {
      setActionLoading(true, 'Mengaktifkan menu...');
      const response = await api(`/api/products/${id}`, { method: 'PUT', body: { is_active: true } });
      await bootstrapApplication({ quiet: true });
      showToast(response.message);
    } catch (error) {
      showToast(error.message, 'error');
    } finally {
      setActionLoading(false);
    }
  }, { title: 'Aktifkan produk?', confirmText: 'Ya, Aktifkan', icon: 'bx-check-circle' });
}

function renderCategories() {
  const body = byId('categoryTableBody');
  if (!body) return;
  const query = (byId('categorySearch')?.value || '').toLowerCase();
  const filtered = categories.filter((category) => category.name.toLowerCase().includes(query));
  const visible = pageSlice('categories', filtered, query);
  body.innerHTML = visible.length ? visible.map((category) => `<tr class="hover:bg-slate-50 transition-all text-xs"><td class="p-4 pl-6 font-bold text-slate-900">${escapeHtml(category.code)}</td><td class="p-4 font-bold text-slate-800">${escapeHtml(category.name)}</td><td class="p-4 pr-6 text-center"><button onclick="editCategory(${category.id})" class="p-2 bg-slate-50 text-slate-400 rounded-xl"><i class="bx bx-edit"></i></button> <button onclick="deleteCategory(${category.id})" class="p-2 bg-slate-50 text-slate-400 rounded-xl"><i class="bx bx-trash"></i></button></td></tr>`).join('') : '<tr><td colspan="3" class="p-8 text-center text-slate-400">Kategori tidak ditemukan</td></tr>';
  renderTablePagination('categories', filtered.length, 'categoryPagination');
}
function openCategoryModal() { byId('categoryForm').reset(); byId('catId').value = ''; byId('categoryModalTitle').textContent = 'Tambah Kategori Baru'; byId('categoryModal').classList.remove('hidden'); }
function closeCategoryModal() { byId('categoryModal').classList.add('hidden'); }
function editCategory(id) { const category = categories.find((item) => Number(item.id) === Number(id)); if (!category) return; byId('catId').value = category.id; byId('catName').value = category.name; byId('categoryModalTitle').textContent = 'Edit Kategori'; byId('categoryModal').classList.remove('hidden'); }
async function saveCategory(event) {
  event.preventDefault();
  const id = byId('catId').value;
  try {
    setActionLoading(true, id ? 'Memperbarui kategori...' : 'Menambahkan kategori...');
    const response = await api(id ? `/api/categories/${id}` : '/api/categories', { method: id ? 'PUT' : 'POST', body: { name: byId('catName').value.trim() } });
    closeCategoryModal();
    await bootstrapApplication({ quiet: true });
    showToast(response.message);
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    setActionLoading(false);
  }
}
function deleteCategory(id) {
  showCustomConfirm('Hapus kategori ini?', async () => {
    try {
      setActionLoading(true, 'Menghapus kategori...');
      const response = await api(`/api/categories/${id}`, { method: 'DELETE' });
      await bootstrapApplication({ quiet: true });
      showToast(response.message);
    } catch (error) {
      showToast(error.message, 'error');
    } finally {
      setActionLoading(false);
    }
  });
}

function renderOutlets() {
  const body = byId('outletTableBody');
  if (!body) return;
  const query = (byId('outletSearch')?.value || '').toLowerCase();
  const filtered = outlets.filter((outlet) => [outlet.code, outlet.name, outlet.address, outlet.phone].join(' ').toLowerCase().includes(query));
  const visible = pageSlice('outlets', filtered, query);

  if (byId('outletTotalCount')) byId('outletTotalCount').textContent = formatPlainNumber(outlets.length);
  if (byId('outletActiveCount')) byId('outletActiveCount').textContent = formatPlainNumber(getActiveOutlets().length);
  if (byId('outletStaffCount')) byId('outletStaffCount').textContent = formatPlainNumber(outlets.reduce((sum, outlet) => sum + Number(outlet.users_count || 0), 0));

  body.innerHTML = visible.length ? visible.map((outlet) => {
    const isCurrent = Number(currentOutlet?.id) === Number(outlet.id);
    const statusClass = outlet.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200';
    return `<tr class="hover:bg-red-50/35 transition-all text-xs">
      <td class="p-4 pl-6"><div class="flex items-center gap-3"><div class="w-10 h-10 rounded-2xl ${outlet.is_active ? 'bg-gradient-to-br from-red-600 to-orange-500 text-white' : 'bg-slate-200 text-slate-500'} flex items-center justify-center shadow-sm"><i class="bx bx-store-alt text-lg"></i></div><div><div class="font-extrabold text-slate-900">${escapeHtml(outlet.name)}</div><div class="mt-1 flex items-center gap-1.5"><span class="font-mono text-[9px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded">${escapeHtml(outlet.code)}</span>${isCurrent ? '<span class="text-[9px] font-bold text-indigo-600">• Aktif dilihat</span>' : ''}</div></div></div></td>
      <td class="p-4"><div class="font-semibold text-slate-700">${escapeHtml(outlet.phone || '-')}</div><div class="text-[10px] text-slate-400 max-w-[270px] truncate mt-1" title="${escapeHtml(outlet.address || '-')}">${escapeHtml(outlet.address || '-')}</div></td>
      <td class="p-4 text-center font-extrabold text-indigo-700">${formatPlainNumber(outlet.users_count || 0)}</td><td class="p-4 text-center font-extrabold text-amber-700">${formatPlainNumber(outlet.products_count || 0)}</td><td class="p-4 text-center font-extrabold text-emerald-700">${formatPlainNumber(outlet.transactions_count || 0)}</td>
      <td class="p-4 text-center"><span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border text-[10px] font-bold ${statusClass}"><span class="w-1.5 h-1.5 rounded-full ${outlet.is_active ? 'bg-emerald-500' : 'bg-slate-400'}"></span>${outlet.is_active ? 'Aktif' : 'Nonaktif'}</span></td>
      <td class="p-4 pr-6 text-center"><button onclick="editOutlet(${outlet.id})" class="p-2 bg-amber-50 hover:bg-amber-100 text-amber-600 rounded-xl" title="Edit"><i class="bx bx-edit"></i></button> <button onclick="deleteOutlet(${outlet.id})" class="p-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl" title="Hapus"><i class="bx bx-trash"></i></button></td>
    </tr>`;
  }).join('') : '<tr><td colspan="7" class="p-10 text-center text-slate-400"><i class="bx bx-store-alt text-4xl block mb-2"></i>Cabang tidak ditemukan</td></tr>';
  renderTablePagination('outlets', filtered.length, 'outletPagination');
}
function openOutletModal() {
  byId('outletForm').reset();
  byId('outletId').value = '';
  byId('outletTimezone').value = 'Asia/Jakarta';
  byId('outletIsActive').checked = true;
  byId('outletModalTitle').textContent = 'Tambah Cabang Baru';
  byId('outletModal').classList.remove('hidden');
}

function closeOutletModal() { byId('outletModal').classList.add('hidden'); }

function editOutlet(id) {
  const outlet = outlets.find((item) => Number(item.id) === Number(id));
  if (!outlet) return;
  byId('outletId').value = outlet.id;
  byId('outletCode').value = outlet.code || '';
  byId('outletName').value = outlet.name || '';
  byId('outletAddress').value = outlet.address || '';
  byId('outletPhone').value = outlet.phone || '';
  byId('outletTimezone').value = outlet.timezone || 'Asia/Jakarta';
  byId('outletIsActive').checked = outlet.is_active !== false;
  byId('outletModalTitle').textContent = 'Edit Cabang';
  byId('outletModal').classList.remove('hidden');
}

async function saveOutlet(event) {
  event.preventDefault();
  const id = byId('outletId').value;
  const payload = {
    code: byId('outletCode').value.trim().toUpperCase(),
    name: byId('outletName').value.trim(),
    address: byId('outletAddress').value.trim(),
    phone: byId('outletPhone').value.trim(),
    timezone: byId('outletTimezone').value,
    is_active: byId('outletIsActive').checked,
  };

  try {
    setActionLoading(true, 'Menyimpan cabang...');
    const response = await api(id ? `/api/outlets/${id}` : '/api/outlets', { method: id ? 'PUT' : 'POST', body: payload });
    showToast(response.message);
    closeOutletModal();
    await bootstrapApplication({ quiet: true });
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    setActionLoading(false);
  }
}

function deleteOutlet(id) {
  const outlet = outlets.find((item) => Number(item.id) === Number(id));
  if (!outlet) return;
  showCustomConfirm(`Hapus cabang ${outlet.name}? Cabang yang sudah memiliki transaksi harus dinonaktifkan, bukan dihapus.`, async () => {
    try {
      setActionLoading(true, 'Memproses cabang...');
      const response = await api(`/api/outlets/${id}`, { method: 'DELETE' });
      showToast(response.message);
      await bootstrapApplication({ quiet: true });
    } catch (error) {
      showToast(error.message, 'error');
    } finally {
      setActionLoading(false);
    }
  });
}

function renderCRM() {
  const body = byId('crmTableBody');
  if (!body) return;
  const query = (byId('crmSearch')?.value || '').toLowerCase();
  const filtered = customers.filter((customer) => customer.name.toLowerCase().includes(query) || customer.phone.includes(query) || customer.member_code.toLowerCase().includes(query));
  const visible = pageSlice('crm', filtered, query);
  body.innerHTML = visible.length ? visible.map((customer) => `<tr class="hover:bg-slate-50 text-xs"><td class="p-4 pl-6 font-bold">${escapeHtml(customer.member_code)}</td><td class="p-4 font-bold">${escapeHtml(customer.name)}</td><td class="p-4">${escapeHtml(customer.phone)}</td><td class="p-4"><span class="px-2 py-1 bg-slate-100 rounded">${escapeHtml(customer.tier)}</span></td><td class="p-4 text-center font-bold">${formatPlainNumber(customer.points)}</td><td class="p-4 pr-6 text-center"><button onclick="editCustomer(${customer.id})" class="p-2 bg-slate-50 text-slate-400 rounded-xl"><i class="bx bx-edit"></i></button> <button onclick="deleteCustomer(${customer.id})" class="p-2 bg-slate-50 text-slate-400 rounded-xl"><i class="bx bx-trash"></i></button></td></tr>`).join('') : '<tr><td colspan="6" class="p-8 text-center text-slate-400">Member tidak ditemukan</td></tr>';
  renderTablePagination('crm', filtered.length, 'crmPagination');
}
function openCustomerModal() { byId('customerForm').reset(); setNumberInputValue('custPoints', 0); byId('custId').value = ''; byId('customerModalTitle').textContent = 'Tambah Member'; byId('customerModal').classList.remove('hidden'); }
function closeCustomerModal() { byId('customerModal').classList.add('hidden'); }
function editCustomer(id) { const customer = customers.find((item) => Number(item.id) === Number(id)); if (!customer) return; byId('custId').value = customer.id; byId('custName').value = customer.name; byId('custPhone').value = customer.phone; byId('custTier').value = customer.tier; setNumberInputValue('custPoints', customer.points); byId('customerModalTitle').textContent = 'Edit Member'; byId('customerModal').classList.remove('hidden'); }
async function saveCustomer(event) {
  event.preventDefault();
  const id = byId('custId').value;
  const payload = { name: byId('custName').value.trim(), phone: byId('custPhone').value.trim(), tier: byId('custTier').value, points: readNumberInput('custPoints') };
  try {
    setActionLoading(true, id ? 'Memperbarui member...' : 'Menambahkan member...');
    const response = await api(id ? `/api/customers/${id}` : '/api/customers', { method: id ? 'PUT' : 'POST', body: payload });
    closeCustomerModal();
    await bootstrapApplication({ quiet: true });
    showToast(response.message);
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    setActionLoading(false);
  }
}
function deleteCustomer(id) {
  showCustomConfirm('Hapus member ini?', async () => {
    try {
      setActionLoading(true, 'Menghapus member...');
      const response = await api(`/api/customers/${id}`, { method: 'DELETE' });
      await bootstrapApplication({ quiet: true });
      showToast(response.message);
    } catch (error) {
      showToast(error.message, 'error');
    } finally {
      setActionLoading(false);
    }
  });
}

function renderExpenses() {
  const body = byId('expenseTableBody');
  if (!body) return;
  const query = (byId('expenseSearch')?.value || '').toLowerCase();
  const filtered = expenses.filter((expense) => expense.desc.toLowerCase().includes(query) || expense.category.toLowerCase().includes(query) || expense.expense_number.toLowerCase().includes(query));
  const visible = pageSlice('expenses', filtered, query);
  body.innerHTML = visible.length ? visible.map((expense) => `<tr class="hover:bg-slate-50 text-xs"><td class="p-4 pl-6 font-bold">${escapeHtml(expense.expense_number)}</td><td class="p-4">${escapeHtml(expense.date)}</td><td class="p-4"><span class="px-2 py-1 bg-slate-100 rounded">${escapeHtml(expense.category)}</span></td><td class="p-4 truncate max-w-[220px]" title="${escapeHtml(expense.desc)}">${escapeHtml(expense.desc)}</td><td class="p-4 text-right font-bold text-[#C00000]">${formatIDR(expense.amount)}</td><td class="p-4 pr-6 text-center"><button onclick="editExpense(${expense.id})" class="p-2 bg-slate-50 text-slate-400 rounded-xl"><i class="bx bx-edit"></i></button> <button onclick="deleteExpense(${expense.id})" class="p-2 bg-slate-50 text-slate-400 rounded-xl"><i class="bx bx-trash"></i></button></td></tr>`).join('') : '<tr><td colspan="6" class="p-8 text-center text-slate-400">Pengeluaran tidak ditemukan</td></tr>';
  renderTablePagination('expenses', filtered.length, 'expensePagination');
}
function openExpenseModal() { byId('expenseForm').reset(); setNumberInputValue('expAmount', 0); byId('expId').value = ''; byId('expenseModalTitle').textContent = 'Catat Pengeluaran'; byId('expenseModal').classList.remove('hidden'); }
function closeExpenseModal() { byId('expenseModal').classList.add('hidden'); }
function editExpense(id) { const expense = expenses.find((item) => Number(item.id) === Number(id)); if (!expense) return; byId('expId').value = expense.id; byId('expCategory').value = expense.category; byId('expDesc').value = expense.desc; setNumberInputValue('expAmount', expense.amount); byId('expenseModalTitle').textContent = 'Edit Pengeluaran'; byId('expenseModal').classList.remove('hidden'); }
async function saveExpense(event) {
  event.preventDefault();
  const id = byId('expId').value;
  const payload = { category: byId('expCategory').value, description: byId('expDesc').value.trim(), amount: readNumberInput('expAmount'), payment_method: 'Tunai' };
  try {
    setActionLoading(true, id ? 'Memperbarui pengeluaran...' : 'Menyimpan pengeluaran...');
    const response = await api(id ? `/api/expenses/${id}` : '/api/expenses', { method: id ? 'PUT' : 'POST', body: payload });
    closeExpenseModal();
    await bootstrapApplication({ quiet: true });
    showToast(response.message);
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    setActionLoading(false);
  }
}
function deleteExpense(id) {
  showCustomConfirm('Hapus pengeluaran ini?', async () => {
    try {
      setActionLoading(true, 'Menghapus pengeluaran...');
      const response = await api(`/api/expenses/${id}`, { method: 'DELETE' });
      await bootstrapApplication({ quiet: true });
      showToast(response.message);
    } catch (error) {
      showToast(error.message, 'error');
    } finally {
      setActionLoading(false);
    }
  });
}

function renderUsers() {
  const body = byId('userTableBody');
  if (!body) return;
  const visible = pageSlice('users', users, String(users.length));
  body.innerHTML = visible.length ? visible.map((user) => `<tr class="hover:bg-slate-50 transition-all"><td class="p-4 pl-6 font-bold text-slate-900">${escapeHtml(user.name)}</td><td class="p-4 font-mono text-slate-500">${escapeHtml(user.username)}</td><td class="p-4"><span class="px-2 py-1 bg-slate-100 rounded text-xs font-bold">${escapeHtml(user.role)}</span></td><td class="p-4">${escapeHtml(user.outlet)}</td><td class="p-4 pr-6 text-center"><button onclick="editUser(${user.id})" class="p-2 bg-slate-50 text-slate-400 rounded-xl"><i class="bx bx-edit"></i></button>${user.id !== currentUser.id ? ` <button onclick="deleteUser(${user.id})" class="p-2 bg-slate-50 text-slate-400 rounded-xl"><i class="bx bx-trash"></i></button>` : ''}</td></tr>`).join('') : '<tr><td colspan="5" class="p-8 text-center text-slate-400">Tidak ada pengguna</td></tr>';
  renderTablePagination('users', users.length, 'userPagination');
}
function toggleUserOutletConstraint() { const isAdmin = byId('usrRole').value === 'Administrator'; byId('usrOutletSelectWrapper').classList.toggle('hidden', isAdmin); }
function openUserModal() { byId('userForm').reset(); byId('userId').value = ''; byId('usrPassword').required = true; byId('userModalTitle').textContent = 'Tambah Pengguna'; populateOutletSelector(); toggleUserOutletConstraint(); byId('userModal').classList.remove('hidden'); }
function closeUserModal() { byId('userModal').classList.add('hidden'); }
function editUser(id) { const user = users.find((item) => Number(item.id) === Number(id)); if (!user) return; byId('userId').value = user.id; byId('usrName').value = user.name; byId('usrUsername').value = user.username; byId('usrPassword').value = ''; byId('usrPassword').required = false; byId('usrRole').value = user.role; populateOutletSelector(); if (user.outlet_id) byId('usrOutlet').value = user.outlet_id; toggleUserOutletConstraint(); byId('userModalTitle').textContent = 'Edit Pengguna'; byId('userModal').classList.remove('hidden'); }
async function saveUser(event) {
  event.preventDefault();
  const id = byId('userId').value;
  const payload = { name: byId('usrName').value.trim(), username: byId('usrUsername').value.trim().toLowerCase(), role: byId('usrRole').value, outlet_id: byId('usrRole').value === 'Administrator' ? null : Number(byId('usrOutlet').value), is_active: true };
  if (byId('usrPassword').value) payload.password = byId('usrPassword').value;
  try {
    setActionLoading(true, id ? 'Memperbarui pengguna...' : 'Menambahkan pengguna...');
    const response = await api(id ? `/api/users/${id}` : '/api/users', { method: id ? 'PUT' : 'POST', body: payload });
    closeUserModal();
    await bootstrapApplication({ quiet: true });
    showToast(response.message);
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    setActionLoading(false);
  }
}
function deleteUser(id) {
  showCustomConfirm('Hapus pengguna ini?', async () => {
    try {
      setActionLoading(true, 'Menghapus pengguna...');
      const response = await api(`/api/users/${id}`, { method: 'DELETE' });
      await bootstrapApplication({ quiet: true });
      showToast(response.message);
    } catch (error) {
      showToast(error.message, 'error');
    } finally {
      setActionLoading(false);
    }
  });
}

function renderReport() {
  const body = byId('reportTableBody');
  if (!body) return;
  const query = (byId('reportSearch')?.value || '').toLowerCase();
  const filtered = transactions.filter((transaction) => transaction.invoice_number.toLowerCase().includes(query) || transaction.items.some((item) => item.name.toLowerCase().includes(query)));
  const visible = pageSlice('reports', filtered, query);
  body.innerHTML = visible.length ? visible.map((transaction) => {
    const summary = transaction.items.map((item) => `${item.name} (x${item.qty})`).join(', ');
    return `<tr class="hover:bg-slate-50 text-xs"><td class="p-4 pl-6 font-bold font-mono">${escapeHtml(transaction.invoice_number)}</td><td class="p-4 text-slate-500">${escapeHtml(transaction.time)}</td><td class="p-4 truncate max-w-[190px]" title="${escapeHtml(summary)}">${escapeHtml(summary)}</td><td class="p-4 text-right">${formatIDR(transaction.subtotal)}</td><td class="p-4 text-center"><span class="px-2 py-0.5 bg-red-50 text-[#C00000] rounded">${transaction.discount}%</span></td><td class="p-4 text-right font-extrabold">${formatIDR(transaction.total)}</td><td class="p-4 text-right text-emerald-600 font-extrabold">${formatIDR(transaction.profit)}</td><td class="p-4 pr-6 text-center"><button onclick="openInvoiceModal(transactions.find(item => item.id === ${transaction.id}))" class="p-2 bg-slate-50 text-slate-400 rounded-xl"><i class="bx bx-printer"></i></button></td></tr>`;
  }).join('') : '<tr><td colspan="8" class="p-8 text-center text-slate-400">Transaksi tidak ditemukan</td></tr>';
  renderTablePagination('reports', filtered.length, 'reportPagination');
}
async function exportToExcel() {
  try {
    setActionLoading(true, 'Menyiapkan laporan Excel...');
    const response = await fetch('/api/transactions/export', {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken(),
      },
    });

    if (!response.ok) {
      const payload = await response.json().catch(() => ({}));
      throw new Error(payload.message || 'Laporan Excel gagal dibuat.');
    }

    const blob = await response.blob();
    const disposition = response.headers.get('content-disposition') || '';
    const utf8Match = disposition.match(/filename\*=UTF-8''([^;]+)/i);
    const plainMatch = disposition.match(/filename="?([^";]+)"?/i);
    const filename = decodeURIComponent(utf8Match?.[1] || plainMatch?.[1] || `where-coffee-${Date.now()}.xlsx`);
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.setTimeout(() => URL.revokeObjectURL(url), 1000);
    showToast('Laporan Excel berhasil diunduh.');
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    setActionLoading(false);
  }
}

function openInvoiceModal(transaction) {
  if (!transaction) return;
  updateAppLogos();
  byId('recStoreName').textContent = config.storeName.toUpperCase();
  byId('recOutlet').textContent = `Cabang: ${currentOutlet?.name || ''}`;
  byId('recStoreAddress').textContent = config.address;
  byId('recStorePhone').textContent = `Telp: ${config.phone || '-'}`;
  byId('recTxId').textContent = transaction.invoice_number;
  byId('recPayMode').textContent = transaction.payMode;
  byId('recTime').textContent = transaction.time;
  byId('recSubtotal').textContent = formatIDR(transaction.subtotal);
  byId('recDiscount').textContent = `${transaction.discount}%`;
  byId('recTotal').textContent = formatIDR(transaction.total);
  byId('recPay').textContent = formatIDR(transaction.pay);
  byId('recChange').textContent = formatIDR(transaction.change);
  byId('recItemsList').innerHTML = transaction.items.map((item) => `<div><div class="font-bold">${escapeHtml(item.name)}</div><div class="flex justify-between"><span>${item.qty} x ${formatIDR(item.price)}</span><span>${formatIDR(item.qty * item.price)}</span></div></div>`).join('');
  const extras = [];
  if (transaction.service_charge_amount > 0) extras.push(`<div class="flex justify-between"><span>Service (${transaction.service_charge_percentage}%):</span><span>${formatIDR(transaction.service_charge_amount)}</span></div>`);
  if (transaction.tax_amount > 0) extras.push(`<div class="flex justify-between"><span>Pajak (${transaction.tax_percentage}%):</span><span>${formatIDR(transaction.tax_amount)}</span></div>`);
  if (transaction.points_discount_amount > 0) extras.push(`<div class="flex justify-between"><span>Poin (${transaction.points_redeemed}):</span><span>-${formatIDR(transaction.points_discount_amount)}</span></div>`);
  byId('recExtraCalculations').innerHTML = extras.join('');
  const footer = document.querySelector('#printableReceipt .text-center.pt-3 p');
  if (footer) footer.textContent = config.receiptFooter;
  const modal = byId('invoiceModal');
  modal.classList.remove('hidden');
  window.setTimeout(() => modal.querySelector('button[aria-label="Tutup invoice"]')?.focus(), 50);
}
function closeInvoiceModal() {
  byId('invoiceModal')?.classList.add('hidden');
}

function handleInvoiceBackdrop(event) {
  if (!event.target.closest('[data-invoice-panel]')) closeInvoiceModal();
}

async function handleLogout() {
  setActionLoading(true, 'Mengakhiri sesi...');
  try {
    await api('/logout', { method: 'POST', body: {} });
  } catch (_) {
    // The session may already have expired; continue to the login route.
  }
  currentUser = null;
  cart = [];
  window.location.href = window.WhereCoffeeConfig?.routes?.login || '/login';
}

function installInteractiveEffects() {
  document.addEventListener('pointerdown', (event) => {
    const button = event.target.closest('button');
    if (!button || button.disabled) return;
    button.classList.add('button-pressed');
    window.setTimeout(() => button.classList.remove('button-pressed'), 180);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  installInteractiveEffects();
  initializeFormattedNumberInputs();
  updateAppLogos();

  const cancelConfirm = byId('confirmCancelBtn');
  const acceptConfirm = byId('confirmOkBtn');
  if (cancelConfirm) {
    cancelConfirm.onclick = () => {
      byId('confirmModal')?.classList.add('hidden');
      pendingConfirmAction = null;
    };
  }
  if (acceptConfirm) {
    acceptConfirm.onclick = async () => {
      const action = pendingConfirmAction;
      byId('confirmModal')?.classList.add('hidden');
      pendingConfirmAction = null;
      if (action) await action();
    };
  }

  document.addEventListener('click', (event) => {
    const wrapper = byId('memberSearchWrapper');
    if (wrapper && !wrapper.contains(event.target)) closePOSCustomerResults();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !byId('invoiceModal')?.classList.contains('hidden')) closeInvoiceModal();
    if (event.key === 'Escape' && !byId('qrisPreviewModal')?.classList.contains('hidden')) closeQrisModal();
    if (event.key === 'Escape' && !byId('confirmModal')?.classList.contains('hidden')) {
      byId('confirmModal')?.classList.add('hidden');
      pendingConfirmAction = null;
    }
  });

  const loginForm = byId('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const button = event.currentTarget.querySelector('button[type="submit"]');
      const original = button.innerHTML;
      button.disabled = true;
      button.innerHTML = '<i class="bx bx-loader-alt animate-spin text-lg"></i><span>Memverifikasi...</span>';
      try {
        const response = await api('/login', { method: 'POST', body: { username: byId('username').value.trim(), password: byId('password').value } });
        showToast(response.message);
        window.setTimeout(() => {
          window.location.href = response.redirect || window.WhereCoffeeConfig?.routes?.dashboard || '/dashboard';
        }, 250);
      } catch (error) {
        showToast(error.message, 'error');
        button.disabled = false;
        button.innerHTML = original;
      }
    });
  }

  if (window.WhereCoffeeConfig?.authenticated) bootstrapApplication();
});
