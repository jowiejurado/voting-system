// Minimal, framework-agnostic modal helper
(function () {
  function openModal(modal) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
  }
  function closeModal(modal) {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('aria-hidden', 'true');

    // Reset logic (if requested)
    if (modal.hasAttribute('data-reset-on-close')) {
      const form = modal.querySelector('form');
      if (form) form.reset();
      // hard clear for text/passwords marked to clear
      modal.querySelectorAll('[data-clear-on-close]').forEach(i => { i.value = ''; });
      // optional: clear any helpers
      modal.querySelectorAll('input[type="hidden"][data-clear-on-close]').forEach(i => (i.value = ''));
    }
  }

  // Overlay click / Close buttons / Cancel buttons
  document.addEventListener('click', (e) => {
    // Open trigger
    const openBtn = e.target.closest('[data-modal-open]');
    if (openBtn) {
      const sel = openBtn.getAttribute('data-modal-open');
      const modal = document.querySelector(sel);
      if (modal) openModal(modal);
      return;
    }

    // Close trigger
    const closeBtn = e.target.closest('[data-modal-close],[data-modal-cancel]');
    if (closeBtn) {
      const modal = closeBtn.closest('[data-modal]');
      if (modal) closeModal(modal);
      return;
    }

    // Click on overlay (outside panel)
    const overlay = e.target.closest('[data-modal]');
    if (overlay && e.target === overlay) {
      closeModal(overlay);
      return;
    }
  });

  // ESC to close
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('[data-modal].flex').forEach(closeModal);
  });

  // Expose helpers
  window.Modal = { openById(id) { const m = document.getElementById(id); if (m) openModal(m); },
                   closeById(id) { const m = document.getElementById(id); if (m) closeModal(m); },
                   open: openModal, close: closeModal };
})();
