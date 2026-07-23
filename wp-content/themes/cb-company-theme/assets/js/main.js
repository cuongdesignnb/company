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

  const submenuToggles = Array.from(document.querySelectorAll('.cb-main-nav .cb-submenu-toggle'));
  function closeSubmenus(except) {
    submenuToggles.forEach(function (button) {
      const item = button.closest('.menu-item-has-children');
      if (!item || item === except) return;
      item.classList.remove('is-submenu-open');
      button.setAttribute('aria-expanded', 'false');
    });
  }
  submenuToggles.forEach(function (button) {
    button.addEventListener('click', function () {
      const item = button.closest('.menu-item-has-children');
      if (!item) return;
      const open = !item.classList.contains('is-submenu-open');
      closeSubmenus(item);
      item.classList.toggle('is-submenu-open', open);
      button.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });
  document.addEventListener('click', function (event) {
    if (!event.target.closest('.cb-main-nav')) closeSubmenus();
  });
  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    const expanded = document.querySelector('.cb-main-nav .cb-submenu-toggle[aria-expanded="true"]');
    closeSubmenus();
    expanded?.focus();
  });

  document.querySelectorAll('[data-cb-gallery]').forEach(function (gallery) {
    const track = gallery.querySelector('[data-cb-gallery-track]');
    const items = Array.from(gallery.querySelectorAll('.cb-showroom-item'));
    const dots = Array.from(gallery.querySelectorAll('[data-cb-gallery-dot]'));
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const autoplay = gallery.dataset.autoplay !== '0' && !reducedMotion;
    const delay = Math.max(2800, Number(gallery.dataset.delay) || 4800);
    let active = 0;
    let timer = null;
    let scrollTimer = null;
    let inViewport = !('IntersectionObserver' in window);
    if (!track || items.length < 2) return;

    function setActive(index) {
      active = (index + items.length) % items.length;
      items.forEach(function (item, itemIndex) {
        const isActive = itemIndex === active;
        item.classList.toggle('is-active', isActive);
        item.setAttribute('aria-hidden', isActive ? 'false' : 'true');
      });
      dots.forEach(function (dot, dotIndex) {
        const isActive = dotIndex === active;
        dot.classList.toggle('is-active', isActive);
        dot.setAttribute('aria-current', isActive ? 'true' : 'false');
      });
    }

    function show(index, restart) {
      setActive(index);
      const item = items[active];
      const targetLeft = item.offsetLeft - Math.max(0, (track.clientWidth - item.clientWidth) / 2);
      track.scrollTo({ left: Math.max(0, targetLeft), behavior: reducedMotion ? 'auto' : 'smooth' });
      if (restart) start();
    }

    function stop() {
      window.clearInterval(timer);
      timer = null;
    }

    function start() {
      stop();
      if (autoplay && inViewport && !document.hidden) {
        timer = window.setInterval(function () { show(active + 1, false); }, delay);
      }
    }

    gallery.querySelector('[data-cb-gallery-prev]')?.addEventListener('click', function () { show(active - 1, true); });
    gallery.querySelector('[data-cb-gallery-next]')?.addEventListener('click', function () { show(active + 1, true); });
    dots.forEach(function (dot) {
      dot.addEventListener('click', function () { show(Number(dot.dataset.cbGalleryDot), true); });
    });
    track.addEventListener('scroll', function () {
      window.clearTimeout(scrollTimer);
      scrollTimer = window.setTimeout(function () {
        const center = track.scrollLeft + track.clientWidth / 2;
        const nearest = items.reduce(function (best, item, index) {
          const distance = Math.abs(item.offsetLeft + item.clientWidth / 2 - center);
          return distance < best.distance ? { index: index, distance: distance } : best;
        }, { index: active, distance: Number.POSITIVE_INFINITY });
        setActive(nearest.index);
      }, 100);
    }, { passive: true });
    gallery.addEventListener('mouseenter', stop);
    gallery.addEventListener('mouseleave', start);
    gallery.addEventListener('focusin', stop);
    gallery.addEventListener('focusout', start);
    track.addEventListener('pointerdown', stop, { passive: true });
    track.addEventListener('pointerup', start, { passive: true });
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) stop();
      else start();
    });
    if ('IntersectionObserver' in window) {
      const galleryObserver = new IntersectionObserver(function (entries) {
        inViewport = entries.some(function (entry) { return entry.isIntersecting; });
        if (inViewport) start();
        else stop();
      }, { threshold: 0.18 });
      galleryObserver.observe(gallery);
    }
    setActive(0);
    start();
  });

  const homeMain = document.querySelector('.cb-home-main');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (homeMain && !reducedMotion) {
    const revealSections = Array.from(homeMain.querySelectorAll(':scope > .cb-section:not(.cb-hero-slider):not(.cb-company-stats)'));
    document.body.classList.add('cb-motion-ready');
    if ('IntersectionObserver' in window) {
      const revealObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add('cb-in-view');
          revealObserver.unobserve(entry.target);
        });
      }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
      revealSections.forEach(function (section) { revealObserver.observe(section); });
    } else {
      revealSections.forEach(function (section) { section.classList.add('cb-in-view'); });
    }
  }

  document.querySelectorAll('[data-cb-counter]').forEach(function (counter) {
    const rawValue = counter.dataset.cbValue || '';
    const suffix = counter.dataset.cbSuffix || '';
    const normalized = rawValue.replace(/[\s,]/g, '').replace(/[^\d.-]/g, '');
    const target = Number(normalized);
    if (!Number.isFinite(target) || reducedMotion) return;
    const decimalMatch = rawValue.match(/\.(\d+)/);
    const decimals = decimalMatch ? decimalMatch[1].length : 0;
    const formatter = new Intl.NumberFormat(document.documentElement.lang || 'en', {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals,
      useGrouping: /[,\s]/.test(rawValue) || Math.abs(target) >= 1000
    });
    let started = false;

    function animate() {
      if (started) return;
      started = true;
      const duration = 1500;
      const startedAt = window.performance.now();
      counter.textContent = formatter.format(0) + suffix;
      function frame(now) {
        const progress = Math.min(1, (now - startedAt) / duration);
        const eased = 1 - Math.pow(1 - progress, 3);
        counter.textContent = formatter.format(target * eased) + suffix;
        if (progress < 1) window.requestAnimationFrame(frame);
        else counter.textContent = formatter.format(target) + suffix;
      }
      window.requestAnimationFrame(frame);
    }

    if ('IntersectionObserver' in window) {
      const counterObserver = new IntersectionObserver(function (entries) {
        if (!entries.some(function (entry) { return entry.isIntersecting; })) return;
        animate();
        counterObserver.disconnect();
      }, { threshold: 0.45 });
      counterObserver.observe(counter);
    } else {
      animate();
    }
  });

  document.querySelectorAll('[data-cb-filter-toggle]').forEach(function (button) {
    const panel = button.parentElement.querySelector('[data-cb-filter-panel]');
    if (!panel) return;
    button.addEventListener('click', function () {
      const open = panel.classList.toggle('is-open');
      button.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });

  const contactQrLinks = Array.from(document.querySelectorAll('[data-cb-contact-qr]'));
  const contactQrMobile = window.matchMedia('(max-width: 768px)');
  let contactQrBackdrop = null;
  if (contactQrLinks.length) {
    contactQrBackdrop = document.createElement('button');
    contactQrBackdrop.type = 'button';
    contactQrBackdrop.className = 'cb-contact-qr-backdrop';
    contactQrBackdrop.setAttribute('aria-label', 'Close QR code');
    document.body.appendChild(contactQrBackdrop);
  }
  function syncContactQrModal() {
    const open = contactQrMobile.matches && contactQrLinks.some(function (link) {
      return link.classList.contains('is-open');
    });
    document.body.classList.toggle('cb-contact-qr-open', open);
    contactQrBackdrop?.classList.toggle('is-visible', open);
  }
  function closeContactQr(except) {
    contactQrLinks.forEach(function (link) {
      if (link === except) return;
      link.classList.remove('is-open');
      link.setAttribute('aria-expanded', 'false');
    });
    syncContactQrModal();
  }
  contactQrLinks.forEach(function (link) {
    link.addEventListener('click', function (event) {
      if (!contactQrMobile.matches) return;
      if (!link.classList.contains('is-open')) {
        event.preventDefault();
        closeContactQr(link);
        link.classList.add('is-open');
        link.setAttribute('aria-expanded', 'true');
        syncContactQrModal();
      } else {
        closeContactQr();
      }
    });
  });
  contactQrBackdrop?.addEventListener('click', function () { closeContactQr(); });
  document.addEventListener('click', function (event) {
    if (!event.target.closest('[data-cb-contact-qr]')) closeContactQr();
  });
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closeContactQr();
  });
  contactQrMobile.addEventListener?.('change', function () { closeContactQr(); });

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

  const aboutLinks = Array.from(document.querySelectorAll('.cb-about-desktop-nav nav a[href^="#"]'));
  if (aboutLinks.length && 'IntersectionObserver' in window) {
    const targets = aboutLinks.map(function (link) { return document.querySelector(link.hash); }).filter(Boolean);
    const observer = new IntersectionObserver(function (entries) {
      const visible = entries.filter(function (entry) { return entry.isIntersecting; }).sort(function (a, b) { return b.intersectionRatio - a.intersectionRatio; });
      if (!visible.length) return;
      aboutLinks.forEach(function (link) {
        const active = link.hash === '#' + visible[0].target.id;
        link.classList.toggle('is-active', active);
        if (active) link.setAttribute('aria-current', 'location');
        else link.removeAttribute('aria-current');
      });
    }, { rootMargin: '-18% 0px -62% 0px', threshold: [0.05, 0.35] });
    targets.forEach(function (target) { observer.observe(target); });
  }
})();
