(function () {
  'use strict';
  const toggle = document.querySelector('.cb-menu-toggle');
  const menu = document.querySelector('.cb-main-nav');
  if (toggle && menu) {
    toggle.addEventListener('click', function () {
      const open = menu.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }
})();
