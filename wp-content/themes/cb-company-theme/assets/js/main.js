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
})();
