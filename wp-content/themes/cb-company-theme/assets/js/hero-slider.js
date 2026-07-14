(function () {
  'use strict';
  document.querySelectorAll('[data-cb-hero]').forEach(function (hero) {
    const slides = Array.from(hero.querySelectorAll('[data-cb-hero-slide]'));
    if (slides.length < 2) return;

    const dots = Array.from(hero.querySelectorAll('[data-cb-hero-dot]'));
    const previous = hero.querySelector('[data-cb-hero-prev]');
    const next = hero.querySelector('[data-cb-hero-next]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const autoplay = hero.dataset.autoplay === '1' && !reducedMotion;
    const pauseOnHover = hero.dataset.pauseHover === '1';
    const delay = Math.max(2000, Number(hero.dataset.delay) || 6000);
    const speed = reducedMotion ? 0 : Math.max(100, Number(hero.dataset.speed) || 500);
    let active = 0;
    let timer = null;

    hero.style.setProperty('--cb-hero-speed', speed + 'ms');

    function setFocusable(slide, enabled) {
      slide.querySelectorAll('a, button, input, select, textarea').forEach(function (element) {
        if (enabled) element.removeAttribute('tabindex');
        else element.setAttribute('tabindex', '-1');
      });
    }

    function show(index, restart) {
      active = (index + slides.length) % slides.length;
      slides.forEach(function (slide, slideIndex) {
        const isActive = slideIndex === active;
        slide.classList.toggle('is-active', isActive);
        slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        setFocusable(slide, isActive);
      });
      dots.forEach(function (dot, dotIndex) {
        const isActive = dotIndex === active;
        dot.classList.toggle('is-active', isActive);
        dot.setAttribute('aria-current', isActive ? 'true' : 'false');
      });
      if (restart) start();
    }

    function stop() {
      window.clearInterval(timer);
      timer = null;
    }

    function start() {
      stop();
      if (autoplay) timer = window.setInterval(function () { show(active + 1, false); }, delay);
    }

    if (previous) previous.addEventListener('click', function () { show(active - 1, true); });
    if (next) next.addEventListener('click', function () { show(active + 1, true); });
    dots.forEach(function (dot) {
      dot.addEventListener('click', function () { show(Number(dot.dataset.cbHeroDot), true); });
    });
    hero.addEventListener('keydown', function (event) {
      if (event.key === 'ArrowLeft') show(active - 1, true);
      if (event.key === 'ArrowRight') show(active + 1, true);
    });
    if (pauseOnHover) {
      hero.addEventListener('mouseenter', stop);
      hero.addEventListener('mouseleave', start);
      hero.addEventListener('focusin', stop);
      hero.addEventListener('focusout', start);
    }
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) stop();
      else start();
    });
    show(0, false);
    start();
  });
})();
