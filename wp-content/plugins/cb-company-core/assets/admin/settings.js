(function () {
  'use strict';

  const config = window.cbCompanyAdmin || {};
  const i18n = config.i18n || {};
  const panelCache = new Map();
  const dirtyForms = new WeakSet();
  const dirtyPanels = new Set();
  let activeController = null;
  let requestSequence = 0;

  function showStatus(form, message, state) {
    const status = form.querySelector('.cb-unsaved-status');
    if (!status) return;
    status.textContent = message || '';
    status.classList.toggle('is-visible', Boolean(message));
    status.dataset.state = state || '';
  }

  function markDirty(form, control) {
    dirtyForms.add(form);
    const panel = control && control.closest('[data-cb-template-panel]');
    if (panel && panel.dataset.cacheKey) dirtyPanels.add(panel.dataset.cacheKey);
    showStatus(form, i18n.unsavedChanges || '', 'dirty');
  }

  function setNested(target, path, value) {
    let cursor = target;
    path.forEach(function (key, index) {
      const last = index === path.length - 1;
      if (last) {
        cursor[key] = value;
        return;
      }
      const arrayNext = /^\d+$/.test(path[index + 1]);
      if (cursor[key] === undefined) cursor[key] = arrayNext ? [] : {};
      cursor = cursor[key];
    });
  }

  function collectValues(form) {
    const option = form.dataset.cbOption;
    const scope = form.dataset.cbSaveScope === 'active-panel'
      ? form.querySelector('[data-cb-template-panel]:not([hidden])')
      : form;
    const values = {};
    if (!scope || !option) return values;
    scope.querySelectorAll('input[name], select[name], textarea[name]').forEach(function (control) {
      if (control.disabled || !control.name.startsWith(option + '[')) return;
      if ((control.type === 'checkbox' || control.type === 'radio') && !control.checked) return;
      const parts = control.name.match(/[^\[\]]+/g) || [];
      if (parts.shift() !== option || !parts.length) return;
      setNested(values, parts, control.value);
    });
    return values;
  }

  function clearModuleCache(module) {
    panelCache.clear();
    try {
      Object.keys(sessionStorage).forEach(function (key) {
        if (key.startsWith('cb-admin-panel:' + config.version + ':' + module + ':')) sessionStorage.removeItem(key);
      });
    } catch (error) {
      // Storage can be unavailable in hardened browser sessions.
    }
  }

  async function saveForm(form) {
    const button = form.querySelector('.cb-save-bar button[type="submit"]');
    if (button) button.disabled = true;
    showStatus(form, i18n.saving || '', 'saving');
    try {
      const response = await fetch(config.restUrl + 'admin/settings', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': config.nonce },
        body: JSON.stringify({ module: form.dataset.cbModule, values: collectValues(form) })
      });
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error(data.message || i18n.saveError);
      const activePanel = form.querySelector('[data-cb-template-panel]:not([hidden])');
      if (activePanel && activePanel.dataset.cacheKey) dirtyPanels.delete(activePanel.dataset.cacheKey);
      if (!dirtyPanels.size || form.dataset.cbModule !== 'templates') dirtyForms.delete(form);
      clearModuleCache(form.dataset.cbModule);
      showStatus(form, data.message || i18n.saved, 'saved');
      window.setTimeout(function () {
        if (!dirtyForms.has(form)) showStatus(form, '', '');
      }, 2400);
      form.dispatchEvent(new CustomEvent('cb:settings-saved', { detail: data }));
    } catch (error) {
      showStatus(form, error.message || i18n.saveError || '', 'error');
    } finally {
      if (button) button.disabled = false;
    }
  }

  function initColorPickers(panel) {
    if (!panel || !window.jQuery || !jQuery.fn.wpColorPicker) return;
    jQuery(panel).find('.cb-color-field').each(function () {
      if (!jQuery(this).hasClass('wp-color-picker')) jQuery(this).wpColorPicker();
    });
  }

  function routeKey(route) {
    return ['templates', route.type, route.context, route.tab].join(':');
  }

  function storageKey(route) {
    return 'cb-admin-panel:' + config.version + ':' + routeKey(route);
  }

  function routeUrl(route) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', 'cb-company-templates');
    url.searchParams.set('type', route.type);
    url.searchParams.set('context', route.context);
    url.searchParams.set('tab', route.tab);
    return url.toString();
  }

  function skeleton() {
    const wrapper = document.createElement('div');
    wrapper.className = 'cb-panel-skeleton';
    wrapper.setAttribute('aria-busy', 'true');
    wrapper.innerHTML = '<div class="cb-skeleton-line cb-skeleton-title"></div><div class="cb-skeleton-grid"><div class="cb-skeleton-field"></div><div class="cb-skeleton-field"></div><div class="cb-skeleton-field"></div><div class="cb-skeleton-field"></div></div>';
    return wrapper;
  }

  function schedulePrefetch(app, routes) {
    if (!routes || !routes.length) return;
    const runner = function () {
      routes.slice(0, 2).forEach(function (route) { loadTemplateRoute(app, route, false, true); });
    };
    if ('requestIdleCallback' in window) window.requestIdleCallback(runner, { timeout: 1500 });
    else window.setTimeout(runner, 800);
  }

  function activateTemplateResponse(app, data, updateHistory) {
    const route = data.route;
    const key = routeKey(route);
    app.querySelector('[data-cb-template-type-nav]').innerHTML = data.typeNav;
    app.querySelector('[data-cb-template-context-nav]').innerHTML = data.contextNav;
    app.querySelector('[data-cb-template-tab-nav]').innerHTML = data.tabNav;
    const host = app.querySelector('[data-cb-template-panel-host]');
    let panel = host.querySelector('[data-cache-key="' + CSS.escape(key) + '"]');
    if (!panel) {
      const template = document.createElement('template');
      template.innerHTML = data.html.trim();
      panel = template.content.firstElementChild;
      panel.dataset.cacheKey = key;
      host.appendChild(panel);
    }
    host.querySelectorAll('[data-cb-template-panel]').forEach(function (item) {
      const active = item === panel;
      item.hidden = !active;
      item.classList.toggle('is-active', active);
    });
    host.querySelectorAll('.cb-panel-skeleton').forEach(function (item) { item.remove(); });
    app.dataset.type = route.type;
    app.dataset.context = route.context;
    app.dataset.tab = route.tab;
    if (updateHistory) window.history.pushState({ cbTemplateRoute: route }, '', routeUrl(route));
    initColorPickers(panel);
    panel.dispatchEvent(new CustomEvent('cb:panel-loaded', { bubbles: true, detail: { route: route } }));
    schedulePrefetch(app, data.prefetch || []);
  }

  function cachedResponse(route) {
    const key = routeKey(route);
    if (panelCache.has(key)) return panelCache.get(key);
    try {
      const stored = sessionStorage.getItem(storageKey(route));
      if (stored) {
        const data = JSON.parse(stored);
        panelCache.set(key, data);
        return data;
      }
    } catch (error) {
      return null;
    }
    return null;
  }

  async function loadTemplateRoute(app, route, updateHistory, prefetch) {
    const cached = cachedResponse(route);
    if (cached) {
      if (!prefetch) activateTemplateResponse(app, cached, updateHistory);
      return cached;
    }
    const key = routeKey(route);
    if (!prefetch && activeController) activeController.abort();
    const controller = new AbortController();
    if (!prefetch) activeController = controller;
    const sequence = prefetch ? 0 : ++requestSequence;
    const host = app.querySelector('[data-cb-template-panel-host]');
    let loader = null;
    const loaderTimer = !prefetch ? window.setTimeout(function () {
      loader = skeleton();
      host.appendChild(loader);
    }, 120) : 0;
    try {
      const url = new URL(config.restUrl + 'admin/panel');
      url.searchParams.set('module', 'templates');
      url.searchParams.set('type', route.type);
      url.searchParams.set('context', route.context);
      url.searchParams.set('tab', route.tab);
      const response = await fetch(url.toString(), { credentials: 'same-origin', headers: { 'X-WP-Nonce': config.nonce }, signal: controller.signal });
      const data = await response.json();
      if (!response.ok) throw new Error(data.message || i18n.loadError);
      panelCache.set(key, data);
      try { sessionStorage.setItem(storageKey(route), JSON.stringify(data)); } catch (error) { /* no-op */ }
      if (!prefetch && sequence === requestSequence) activateTemplateResponse(app, data, updateHistory);
      return data;
    } catch (error) {
      if (error.name !== 'AbortError' && !prefetch) {
        const form = app.closest('form');
        showStatus(form, error.message || i18n.loadError || '', 'error');
      }
      return null;
    } finally {
      if (loaderTimer) window.clearTimeout(loaderTimer);
      if (loader) loader.remove();
    }
  }

  function initTemplateApp(app) {
    const route = { type: app.dataset.type, context: app.dataset.context, tab: app.dataset.tab };
    const panel = app.querySelector('[data-cb-template-panel]');
    if (panel) panel.dataset.cacheKey = routeKey(route);
    const response = {
      html: panel ? panel.outerHTML : '',
      typeNav: app.querySelector('[data-cb-template-type-nav]').innerHTML,
      contextNav: app.querySelector('[data-cb-template-context-nav]').innerHTML,
      tabNav: app.querySelector('[data-cb-template-tab-nav]').innerHTML,
      route: route,
      prefetch: Array.from(app.querySelectorAll('[data-cb-template-tab-nav] [data-cb-template-route]')).map(function (link) {
        return { type: link.dataset.type, context: link.dataset.context, tab: link.dataset.tab };
      }).filter(function (item) { return item.tab !== route.tab; }).slice(0, 2)
    };
    panelCache.set(routeKey(route), response);
    initColorPickers(panel);
    schedulePrefetch(app, response.prefetch);
  }

  const pageSearchControllers = new WeakMap();
  function searchPages(input) {
    input.setCustomValidity('');
    const old = pageSearchControllers.get(input);
    if (old) old.abort();
    const controller = new AbortController();
    pageSearchControllers.set(input, controller);
    const select = document.getElementById(input.dataset.target);
    const selected = select.value;
    const selectedLabel = select.selectedOptions[0] ? select.selectedOptions[0].textContent : '';
    const url = new URL(config.restUrl + 'admin/pages');
    url.searchParams.set('search', input.value.trim());
    url.searchParams.set('language', input.dataset.language || '');
    fetch(url.toString(), { credentials: 'same-origin', headers: { 'X-WP-Nonce': config.nonce }, signal: controller.signal })
      .then(function (response) { return response.json().then(function (data) { return { response: response, data: data }; }); })
      .then(function (result) {
        if (!result.response.ok) throw new Error(result.data.message || i18n.loadError);
        select.innerHTML = '<option value="0">' + (i18n.unassigned || '') + '</option>';
        if (selected && selected !== '0') select.add(new Option(selectedLabel, selected, true, true));
        result.data.items.forEach(function (item) {
          if (String(item.id) !== selected) select.add(new Option(item.title + ' (' + item.status + ')', item.id));
        });
      }).catch(function (error) {
        if (error.name !== 'AbortError') input.setCustomValidity(error.message || i18n.loadError || 'Error');
      });
  }

  document.addEventListener('submit', function (event) {
    const form = event.target.closest('[data-cb-settings-form]');
    if (!form || !config.restUrl || !window.fetch) return;
    event.preventDefault();
    saveForm(form);
  });

  document.addEventListener('input', function (event) {
    const form = event.target.closest('[data-cb-settings-form]');
    if (form) markDirty(form, event.target);
    if (event.target.matches('.cb-page-search')) {
      window.clearTimeout(event.target.cbSearchTimer);
      event.target.cbSearchTimer = window.setTimeout(function () { searchPages(event.target); }, 260);
    }
  });
  document.addEventListener('change', function (event) {
    const form = event.target.closest('[data-cb-settings-form]');
    if (form) markDirty(form, event.target);
  });
  document.addEventListener('focusin', function (event) {
    if (event.target.matches('.cb-page-search') && !event.target.dataset.loaded) {
      event.target.dataset.loaded = '1';
      searchPages(event.target);
    }
  });
  document.addEventListener('click', function (event) {
    const routeLink = event.target.closest('[data-cb-template-route]');
    if (!routeLink) return;
    const app = routeLink.closest('[data-cb-template-app]');
    if (!app) return;
    event.preventDefault();
    loadTemplateRoute(app, { type: routeLink.dataset.type, context: routeLink.dataset.context, tab: routeLink.dataset.tab }, true, false);
  });
  document.addEventListener('cb:tab-activated', function (event) {
    initColorPickers(event.detail.panel);
  });

  window.addEventListener('popstate', function () {
    const app = document.querySelector('[data-cb-template-app]');
    if (!app) return;
    const url = new URL(window.location.href);
    loadTemplateRoute(app, {
      type: url.searchParams.get('type') || 'product',
      context: url.searchParams.get('context') || 'product_archive',
      tab: url.searchParams.get('tab') || 'layout'
    }, false, false);
  });
  window.addEventListener('beforeunload', function (event) {
    if (!document.querySelector('[data-cb-settings-form]') || !Array.from(document.querySelectorAll('[data-cb-settings-form]')).some(function (form) { return dirtyForms.has(form); })) return;
    event.preventDefault();
    event.returnValue = i18n.unsavedChanges || '';
  });

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cb-tab-panel.is-active').forEach(initColorPickers);
    document.querySelectorAll('[data-cb-template-app]').forEach(initTemplateApp);
  });
}());
