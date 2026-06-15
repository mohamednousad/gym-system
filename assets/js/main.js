/* ===== MSP GYM — main.js ===== */

// ---- Sidebar collapse ----
(function () {
  const sidebar = document.getElementById('appSidebar');
  const main = document.getElementById('mainContent');
  const toggleBtn = document.getElementById('sidebarToggle');
  const mobileBtn = document.getElementById('mobileSidebarToggle');
  if (!sidebar) return;

  function applyCollapsed(collapsed) {
    sidebar.classList.toggle('collapsed', collapsed);
    if (main) main.classList.toggle('sidebar-collapsed', collapsed);
    localStorage.setItem('sidebar_collapsed', collapsed ? '1' : '0');
  }

  const saved = localStorage.getItem('sidebar_collapsed') === '1';
  applyCollapsed(saved);

  if (toggleBtn) toggleBtn.addEventListener('click', () => applyCollapsed(!sidebar.classList.contains('collapsed')));
  if (mobileBtn) mobileBtn.addEventListener('click', () => sidebar.classList.toggle('mobile-open'));

  document.addEventListener('click', (e) => {
    if (window.innerWidth <= 768 && sidebar.classList.contains('mobile-open')) {
      if (!sidebar.contains(e.target) && e.target !== mobileBtn) {
        sidebar.classList.remove('mobile-open');
      }
    }
  });
})();

// ---- Submenu collapse ----
document.querySelectorAll('[data-submenu]').forEach(link => {
  link.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.getElementById(this.dataset.submenu);
    if (!target) return;
    const isOpen = target.style.display === 'block';
    target.style.display = isOpen ? 'none' : 'block';
    this.setAttribute('aria-expanded', String(!isOpen));
  });
});

// ---- Modal system ----
function openModal(id) {
  const overlay = document.getElementById(id);
  if (!overlay) return;
  overlay.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeModal(id) {
  const overlay = document.getElementById(id);
  if (!overlay) return;
  overlay.classList.remove('open');
  document.body.style.overflow = '';
}

document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', function (e) {
    if (e.target === this) closeModal(this.id);
  });
});

document.querySelectorAll('[data-open-modal]').forEach(btn => {
  btn.addEventListener('click', function () {
    openModal(this.dataset.openModal);
    const populateTarget = this.dataset.populate;
    if (populateTarget) {
      const data = JSON.parse(this.dataset.data || '{}');
      populateForm(populateTarget, data);
    }
  });
});

document.querySelectorAll('[data-close-modal]').forEach(btn => {
  btn.addEventListener('click', function () {
    closeModal(this.dataset.closeModal);
  });
});

function populateForm(formId, data) {
  const form = document.getElementById(formId);
  if (!form) return;
  Object.entries(data).forEach(([key, val]) => {
    const el = form.querySelector('[name="' + key + '"]');
    if (!el) return;
    if (el.tagName === 'SELECT') el.value = val;
    else if (el.type === 'checkbox') el.checked = !!val;
    else el.value = val ?? '';
  });
}

// ---- Confirm delete ----
function confirmDelete(formId, message) {
  message = message || 'Are you sure you want to delete this record? This action cannot be undone.';
  const overlay = document.getElementById('confirmDeleteOverlay');
  if (!overlay) { document.getElementById(formId)?.submit(); return; }
  document.getElementById('confirmDeleteMsg').textContent = message;
  document.getElementById('confirmDeleteBtn').onclick = function () {
    document.getElementById(formId).submit();
    closeModal('confirmDeleteOverlay');
  };
  openModal('confirmDeleteOverlay');
}

// ---- Auto-dismiss flash ----
setTimeout(() => {
  document.querySelectorAll('.flash').forEach(el => {
    el.style.transition = 'opacity 0.5s';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 500);
  });
}, 4000);

// ---- Image preview ----
function showImageModal(src) {
  const overlay = document.getElementById('imageViewerOverlay');
  if (!overlay) return;
  document.getElementById('imageViewerImg').src = src;
  openModal('imageViewerOverlay');
}

// ---- Edit modal helpers (page-specific setup) ----
function setupEditModal(modalId, formId, data) {
  const form = document.getElementById(formId);
  if (form) populateForm(formId, data);
  openModal(modalId);
}