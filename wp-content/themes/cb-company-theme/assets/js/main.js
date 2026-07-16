(function () {
  'use strict';

  const toggle = document.querySelector('.cb-menu-toggle');
  const menu = document.querySelector('.cb-main-nav');
  if (toggle && menu) {
    toggle.addEventListener('click', function () {
      const open = menu.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      document.body.classList.toggle('cb-menu-open', open);
    });
  }

  document.querySelectorAll('[data-cb-gallery]').forEach(function (gallery) {
    const track = gallery.querySelector('[data-cb-gallery-track]');
    const items = Array.from(gallery.querySelectorAll('.cb-showroom-item'));
    let active = 0;
    if (!track || items.length < 2) return;

    function show(index) {
      active = (index + items.length) % items.length;
      items.forEach(function (item, itemIndex) {
        item.classList.toggle('is-active', itemIndex === active);
      });
      items[active].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
    gallery.querySelector('[data-cb-gallery-prev]')?.addEventListener('click', function () { show(active - 1); });
    gallery.querySelector('[data-cb-gallery-next]')?.addEventListener('click', function () { show(active + 1); });
  });

  document.querySelectorAll('[data-cb-filter-toggle]').forEach(function (button) {
    const panel = button.parentElement.querySelector('[data-cb-filter-panel]');
    if (!panel) return;
    button.addEventListener('click', function () {
      const open = panel.classList.toggle('is-open');
      button.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });

  document.querySelectorAll('[data-cb-product-gallery]').forEach(function (gallery) {
    const main = gallery.querySelector('.cb-product-main-image');
    gallery.querySelectorAll('[data-cb-product-thumb]').forEach(function (button) {
      button.addEventListener('click', function () {
        if (main) main.src = button.dataset.cbProductThumb;
        gallery.querySelectorAll('[data-cb-product-thumb]').forEach(function (thumb) { thumb.classList.remove('is-active'); });
        button.classList.add('is-active');
      });
    });
  });

  function loadCertificateBrowser(url, updateHistory) {
    const browser = document.querySelector('[data-cb-certificate-browser]');
    if (!browser) return Promise.resolve();
    browser.classList.add('is-loading');
    browser.setAttribute('aria-busy', 'true');
    return fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (response) {
        if (!response.ok) throw new Error('Certificate request failed');
        return response.text();
      })
      .then(function (html) {
        const page = new DOMParser().parseFromString(html, 'text/html');
        const next = page.querySelector('[data-cb-certificate-browser]');
        if (!next) throw new Error('Certificate browser missing');
        browser.replaceWith(next);
        if (updateHistory) window.history.pushState({ cbCertificates: true }, '', url);
        next.scrollIntoView({ behavior: 'smooth', block: 'start' });
      })
      .catch(function () {
        window.location.assign(url);
      });
  }

  document.addEventListener('click', function (event) {
    const link = event.target.closest('[data-cb-certificate-filter], [data-cb-certificate-browser] .cb-pagination a');
    if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    event.preventDefault();
    loadCertificateBrowser(link.href, true);
  });

  window.addEventListener('popstate', function () {
    if (document.querySelector('[data-cb-certificate-browser]')) loadCertificateBrowser(window.location.href, false);
  });

  const certificateDialog = document.querySelector('[data-cb-lightbox]');
  const certificateTrigger = document.querySelector('[data-cb-lightbox-open]');
  if (certificateDialog && certificateTrigger && typeof certificateDialog.showModal === 'function') {
    const closeButton = certificateDialog.querySelector('[data-cb-lightbox-close]');
    certificateTrigger.addEventListener('click', function () {
      certificateDialog.showModal();
      closeButton?.focus();
    });
    closeButton?.addEventListener('click', function () { certificateDialog.close(); });
    certificateDialog.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        event.preventDefault();
        certificateDialog.close();
      }
    });
    certificateDialog.addEventListener('click', function (event) {
      if (event.target === certificateDialog) certificateDialog.close();
    });
    certificateDialog.addEventListener('close', function () { certificateTrigger.focus(); });
  }

  document.querySelectorAll('.cb-about-mobile-nav nav a').forEach(function (link) {
    link.addEventListener('click', function () {
      const details = link.closest('details');
      if (details && link.hash) details.open = false;
    });
  });
})();
