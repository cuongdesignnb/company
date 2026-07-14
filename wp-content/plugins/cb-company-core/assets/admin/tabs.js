(function () {
  'use strict';

  function activateTab(root, tabKey, updateUrl) {
    const started = window.performance ? performance.now() : 0;
    const tab = root.querySelector('[data-cb-tab="' + CSS.escape(tabKey) + '"]');
    const panel = root.querySelector('[data-cb-panel="' + CSS.escape(tabKey) + '"]');
    if (!tab || !panel) return;

    root.querySelectorAll('[data-cb-tab]').forEach(function (item) {
      const active = item === tab;
      item.classList.toggle('is-active', active);
      item.setAttribute('aria-selected', active ? 'true' : 'false');
      item.setAttribute('tabindex', active ? '0' : '-1');
    });
    root.querySelectorAll('[data-cb-panel]').forEach(function (item) {
      const active = item === panel;
      item.classList.toggle('is-active', active);
      item.hidden = !active;
    });

    const reset = root.closest('form') && root.closest('form').querySelector('.cb-reset-link');
    if (reset) {
      const resetUrl = new URL(reset.href);
      resetUrl.searchParams.set('tab', tabKey);
      reset.href = resetUrl.toString();
    }
    if (updateUrl) {
      const url = new URL(window.location.href);
      url.searchParams.set('tab', tabKey);
      window.history.pushState({ cbTab: tabKey }, '', url.toString());
    }
    root.dispatchEvent(new CustomEvent('cb:tab-activated', {
      bubbles: true,
      detail: { tab: tabKey, panel: panel }
    }));
    if (started) root.dataset.cbLastTabMs = String(Math.round((performance.now() - started) * 100) / 100);
  }

  document.addEventListener('click', function (event) {
    const tab = event.target.closest('[data-cb-tab]');
    if (!tab) return;
    const root = tab.closest('[data-cb-tabs-root]');
    if (!root) return;
    event.preventDefault();
    activateTab(root, tab.dataset.cbTab, true);
  });

  document.addEventListener('keydown', function (event) {
    const tab = event.target.closest('[data-cb-tab]');
    if (!tab || !['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
    const root = tab.closest('[data-cb-tabs-root]');
    const tabs = Array.from(root.querySelectorAll('[data-cb-tab]'));
    let index = tabs.indexOf(tab);
    if (event.key === 'ArrowRight') index = (index + 1) % tabs.length;
    if (event.key === 'ArrowLeft') index = (index - 1 + tabs.length) % tabs.length;
    if (event.key === 'Home') index = 0;
    if (event.key === 'End') index = tabs.length - 1;
    event.preventDefault();
    tabs[index].focus();
    activateTab(root, tabs[index].dataset.cbTab, true);
  });

  window.addEventListener('popstate', function () {
    const tabKey = new URL(window.location.href).searchParams.get('tab');
    if (!tabKey) return;
    document.querySelectorAll('[data-cb-tabs-root]').forEach(function (root) {
      if (root.querySelector('[data-cb-tab="' + CSS.escape(tabKey) + '"]')) activateTab(root, tabKey, false);
    });
  });

  window.cbAdminTabs = { activate: activateTab };
}());
