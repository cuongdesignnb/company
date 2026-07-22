(function () {
  'use strict';

  const config = window.cbCompanyFrontendEdit || {};
  const i18n = config.i18n || {};
  const editable = Array.from(document.querySelectorAll('[data-cb-editable-section="1"]'));
  if (!editable.length || !config.restUrl || !config.nonce) return;

  const state = {
    activeIndex: null,
    activeTab: 'content',
    section: null,
    schema: null,
    sections: [],
    dirty: false
  };

  document.documentElement.classList.add('cb-qe-active');

  const backdrop = document.createElement('div');
  backdrop.className = 'cb-qe-backdrop';
  backdrop.addEventListener('click', closeDrawer);
  document.body.appendChild(backdrop);

  const drawer = document.createElement('aside');
  drawer.className = 'cb-qe-drawer';
  drawer.setAttribute('aria-hidden', 'true');
  drawer.innerHTML = [
    '<div class="cb-qe-drawer-inner">',
    '<header class="cb-qe-drawer-header"><div><h2></h2><small></small></div><button class="cb-qe-close" type="button" aria-label="' + esc(i18n.cancel || 'Close') + '">×</button></header>',
    '<nav class="cb-qe-tabs" aria-label="' + esc(i18n.quickEdit || 'Quick edit') + '"></nav>',
    '<div class="cb-qe-body"></div>',
    '<footer class="cb-qe-drawer-footer"><button type="button" class="cb-qe-button cb-qe-button-primary" data-action="save">' + esc(i18n.save || 'Save') + '</button><button type="button" class="cb-qe-button" data-action="duplicate">' + esc(i18n.duplicate || 'Duplicate') + '</button><button type="button" class="cb-qe-button cb-qe-button-danger" data-action="delete">' + esc(i18n.remove || 'Remove') + '</button><a class="cb-qe-button" data-action="advanced" href="' + esc(config.adminUrl || '#') + '">' + esc(i18n.advanced || 'Advanced') + '</a><span class="cb-qe-status" aria-live="polite"></span></footer>',
    '</div>'
  ].join('');
  document.body.appendChild(drawer);

  const floating = document.createElement('button');
  floating.type = 'button';
  floating.className = 'cb-qe-floating';
  floating.textContent = i18n.sections || 'Sections';
  floating.addEventListener('click', openSectionList);
  document.body.appendChild(floating);

  drawer.querySelector('.cb-qe-close').addEventListener('click', closeDrawer);
  drawer.querySelector('[data-action="save"]').addEventListener('click', saveSection);
  drawer.querySelector('[data-action="duplicate"]').addEventListener('click', duplicateSection);
  drawer.querySelector('[data-action="delete"]').addEventListener('click', deleteSection);

  const adminToggle = document.querySelector('#wp-admin-bar-cb-company-quick-edit-toggle a, .cb-company-quick-edit-toggle a');
  if (adminToggle) {
    adminToggle.addEventListener('click', function (event) {
      event.preventDefault();
      openSectionList();
    });
  }

  editable.forEach(function (section) {
    const toolbar = document.createElement('div');
    toolbar.className = 'cb-qe-toolbar';
    toolbar.innerHTML = [
      '<button type="button" data-tab="content">' + esc(i18n.edit || 'Edit') + '</button>',
      '<button type="button" data-tab="images">' + esc(i18n.images || 'Images') + '</button>',
      '<button type="button" data-tab="design">' + esc(i18n.design || 'Design') + '</button>',
      '<a href="' + esc(advancedUrl(section.dataset.cbSectionIndex, 'content')) + '">' + esc(i18n.advanced || 'Advanced') + '</a>'
    ].join('');
    toolbar.querySelectorAll('button[data-tab]').forEach(function (button) {
      button.addEventListener('click', function (event) {
        event.preventDefault();
        openSection(Number(section.dataset.cbSectionIndex), button.dataset.tab || 'content');
      });
    });
    section.insertBefore(toolbar, section.firstChild);
  });

  fetchSections();
  const initialFocus = new URLSearchParams(window.location.search).get('cb_focus_section');
  if (initialFocus !== null && initialFocus !== '') {
    window.setTimeout(function () {
      const target = document.querySelector('[data-cb-section-index="' + initialFocus + '"]');
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        openSection(Number(initialFocus), 'content');
      }
    }, 300);
  }

  async function request(path, options) {
    const response = await fetch(config.restUrl + path, Object.assign({
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': config.nonce
      },
      credentials: 'same-origin'
    }, options || {}));
    const data = await response.json().catch(function () { return {}; });
    if (!response.ok) {
      throw new Error(data.message || i18n.error || 'Request failed');
    }
    return data;
  }

  async function fetchSections() {
    try {
      const data = await request('/sections');
      state.sections = data.sections || [];
    } catch (error) {
      setStatus(error.message, true);
    }
  }

  async function openSection(index, tab) {
    state.activeIndex = index;
    state.activeTab = tab || 'content';
    state.section = null;
    state.schema = null;
    state.dirty = false;
    highlightSection(index);
    openDrawer();
    renderLoading();
    try {
      const data = await request('/section/' + index);
      state.section = data.section || {};
      state.schema = data.schema || {};
      state.sections = data.sections || state.sections;
      renderDrawer();
    } catch (error) {
      renderError(error.message);
    }
  }

  function openSectionList() {
    openDrawer();
    state.activeIndex = null;
    drawer.querySelector('h2').textContent = i18n.sections || 'Sections';
    drawer.querySelector('small').textContent = i18n.helpText || '';
    drawer.querySelector('.cb-qe-tabs').innerHTML = '';
    const body = drawer.querySelector('.cb-qe-body');
    body.innerHTML = '<div class="cb-qe-section-list"></div>';
    const list = body.querySelector('.cb-qe-section-list');
    const sections = state.sections.length ? state.sections : editable.map(function (element) {
      return {
        index: Number(element.dataset.cbSectionIndex),
        label: element.dataset.cbSectionLabel || element.dataset.cbSectionType,
        type: element.dataset.cbSectionType,
        thumbnail: ''
      };
    });
    sections.forEach(function (section) {
      const button = document.createElement('button');
      button.type = 'button';
      button.innerHTML = (section.thumbnail ? '<img src="' + esc(section.thumbnail) + '" alt="">' : '<span class="cb-qe-section-thumb"></span>') + '<span><strong>' + esc(section.label || section.type) + '</strong><small>' + esc(section.type || '') + '</small></span>';
      button.addEventListener('click', function () { openSection(section.index, 'content'); });
      list.appendChild(button);
    });
  }

  function openDrawer() {
    drawer.classList.add('is-open');
    backdrop.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
  }

  function closeDrawer() {
    drawer.classList.remove('is-open');
    backdrop.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');
    clearHighlights();
  }

  function renderLoading() {
    drawer.querySelector('h2').textContent = i18n.loading || 'Loading...';
    drawer.querySelector('small').textContent = '';
    drawer.querySelector('.cb-qe-tabs').innerHTML = '';
    drawer.querySelector('.cb-qe-body').innerHTML = '<p class="cb-qe-help">' + esc(i18n.loading || 'Loading...') + '</p>';
    setStatus('');
  }

  function renderError(message) {
    drawer.querySelector('.cb-qe-body').innerHTML = '<p class="cb-qe-help">' + esc(message || i18n.error || 'Error') + '</p>';
  }

  function renderDrawer() {
    const section = state.section || {};
    const schema = state.schema || {};
    drawer.querySelector('h2').textContent = section.admin_label || section.title || getSectionLabel(state.activeIndex) || i18n.quickEdit || 'Quick edit';
    drawer.querySelector('small').textContent = (schema.type || section.type || '') + ' · ' + (i18n.helpText || '');
    drawer.querySelector('[data-action="advanced"]').setAttribute('href', advancedUrl(state.activeIndex, state.activeTab));
    renderTabs();
    renderPanels();
    setStatus('');
  }

  function renderTabs() {
    const tabs = [
      ['content', i18n.content || 'Content'],
      ['images', i18n.images || 'Images'],
      ['design', i18n.design || 'Design']
    ];
    const nav = drawer.querySelector('.cb-qe-tabs');
    nav.innerHTML = '';
    tabs.forEach(function (tab) {
      const button = document.createElement('button');
      button.type = 'button';
      button.textContent = tab[1];
      button.className = tab[0] === state.activeTab ? 'is-active' : '';
      button.addEventListener('click', function () {
        state.activeTab = tab[0];
        renderDrawer();
      });
      nav.appendChild(button);
    });
  }

  function renderPanels() {
    const body = drawer.querySelector('.cb-qe-body');
    body.innerHTML = '';
    ['content', 'images', 'design'].forEach(function (group) {
      const panel = document.createElement('div');
      panel.className = 'cb-qe-panel' + (group === state.activeTab ? ' is-active' : '');
      panel.dataset.panel = group;
      renderGroup(panel, group);
      body.appendChild(panel);
    });
  }

  function renderGroup(panel, group) {
    const fields = (state.schema.fields || []).filter(function (field) { return field.group === group; });
    fields.forEach(function (field) {
      panel.appendChild(renderField(field, state.section, function (key, value) {
        setSectionValue(state.section, key, value);
      }));
    });
    if (group === 'content' && state.schema.heroSlideFields && state.schema.heroSlideFields.length && Array.isArray(state.section.slides)) {
      appendGroupTitle(panel, 'Slides');
      state.section.slides.forEach(function (slide, index) {
        const row = document.createElement('div');
        row.className = 'cb-qe-row';
        appendGroupTitle(row, 'Slide ' + (index + 1));
        state.schema.heroSlideFields.forEach(function (field) {
          if (field.type === 'image') return;
          row.appendChild(renderField(field, slide, function (key, value) {
            slide[key] = value;
          }));
        });
        panel.appendChild(row);
      });
    }
    if (group === 'images' && state.schema.heroSlideFields && state.schema.heroSlideFields.length && Array.isArray(state.section.slides)) {
      appendGroupTitle(panel, 'Slides');
      state.section.slides.forEach(function (slide, index) {
        const row = document.createElement('div');
        row.className = 'cb-qe-row';
        appendGroupTitle(row, 'Slide ' + (index + 1));
        state.schema.heroSlideFields.filter(function (field) { return field.type === 'image'; }).forEach(function (field) {
          row.appendChild(renderField(field, slide, function (key, value) {
            setImageValue(slide, key, value);
          }));
        });
        panel.appendChild(row);
      });
    }
    if (group === 'content' && state.schema.itemFields && state.schema.itemFields.length && Array.isArray(state.section.items)) {
      appendGroupTitle(panel, 'Items');
      state.section.items.forEach(function (item, index) {
        const row = document.createElement('div');
        row.className = 'cb-qe-row';
        appendGroupTitle(row, 'Item ' + (index + 1));
        state.schema.itemFields.forEach(function (field) {
          row.appendChild(renderField(field, item, function (key, value) {
            if (field.type === 'image') {
              setImageValue(item, 'image', value);
            } else {
              item[key] = value;
            }
          }));
        });
        panel.appendChild(row);
      });
    }
    if (!panel.children.length) {
      panel.innerHTML = '<p class="cb-qe-help">' + esc(i18n.helpText || '') + '</p>';
    }
  }

  function renderField(field, source, onChange) {
    if (field.type === 'image') {
      return renderImageField(field, source, onChange);
    }
    const label = document.createElement('label');
    label.className = field.type === 'checkbox' ? 'cb-qe-checkbox' : 'cb-qe-field';
    if (field.type !== 'checkbox') {
      label.innerHTML = '<span>' + esc(field.label || field.key) + '</span>';
    }
    let input;
    const value = String(source[field.key] == null ? '' : source[field.key]);
    if (field.type === 'textarea') {
      input = document.createElement('textarea');
      input.value = value;
    } else if (field.type === 'select') {
      input = document.createElement('select');
      Object.keys(field.choices || {}).forEach(function (choice) {
        const option = document.createElement('option');
        option.value = choice;
        option.textContent = field.choices[choice];
        option.selected = choice === value;
        input.appendChild(option);
      });
    } else if (field.type === 'checkbox') {
      input = document.createElement('input');
      input.type = 'checkbox';
      input.checked = value === '1';
      label.appendChild(input);
      label.appendChild(document.createTextNode(' ' + (field.label || field.key)));
    } else {
      input = document.createElement('input');
      input.type = field.type === 'url' ? 'url' : (field.type === 'number' ? 'number' : 'text');
      input.value = value;
    }
    if (field.type !== 'checkbox') {
      label.appendChild(input);
    }
    input.addEventListener('input', function () {
      state.dirty = true;
      onChange(field.key, field.type === 'checkbox' ? (input.checked ? '1' : '0') : input.value);
    });
    input.addEventListener('change', function () {
      state.dirty = true;
      onChange(field.key, field.type === 'checkbox' ? (input.checked ? '1' : '0') : input.value);
    });
    return label;
  }

  function renderImageField(field, source, onChange) {
    const key = field.key;
    const idKey = key === 'image' ? 'image_id' : key + '_id';
    const urlKey = key === 'image' ? 'image_url' : key + '_url';
    const wrap = document.createElement('div');
    wrap.className = 'cb-qe-field cb-qe-image';
    wrap.innerHTML = '<span>' + esc(field.label || key) + '</span><div class="cb-qe-image-preview">' + (source[urlKey] ? '<img src="' + esc(source[urlKey]) + '" alt="">' : '<small>' + esc(i18n.selectImage || 'Select image') + '</small>') + '</div><label class="cb-qe-image-url"><span>' + esc(i18n.manualImageUrl || 'Or enter image URL') + '</span><input type="url" inputmode="url" value="' + esc(source[urlKey] || '') + '" placeholder="https://example.com/image.webp"></label><div class="cb-qe-image-actions"><button type="button" class="cb-qe-button" data-pick>' + esc(i18n.selectImage || 'Select image') + '</button><button type="button" class="cb-qe-button cb-qe-button-danger" data-remove>' + esc(i18n.removeImage || 'Remove image') + '</button></div>';
    const urlInput = wrap.querySelector('.cb-qe-image-url input');
    urlInput.addEventListener('input', function () {
      const value = { id: 0, url: urlInput.value.trim() };
      onChange(key, value);
      source[idKey] = 0;
      source[urlKey] = value.url;
      wrap.querySelector('.cb-qe-image-preview').innerHTML = value.url
        ? '<img src="' + esc(value.url) + '" alt="">'
        : '<small>' + esc(i18n.selectImage || 'Select image') + '</small>';
      state.dirty = true;
    });
    wrap.querySelector('[data-pick]').addEventListener('click', function () {
      if (!window.wp || !wp.media) return;
      const frame = wp.media({ title: i18n.selectImage || 'Select image', multiple: false, library: { type: 'image' } });
      frame.on('select', function () {
        const image = frame.state().get('selection').first().toJSON();
        const value = { id: image.id || 0, url: image.url || '' };
        onChange(key, value);
        source[idKey] = value.id;
        source[urlKey] = value.url;
        urlInput.value = value.url;
        wrap.querySelector('.cb-qe-image-preview').innerHTML = '<img src="' + esc(value.url) + '" alt="">';
        state.dirty = true;
      });
      frame.open();
    });
    wrap.querySelector('[data-remove]').addEventListener('click', function () {
      const value = { id: 0, url: '' };
      onChange(key, value);
      source[idKey] = 0;
      source[urlKey] = '';
      urlInput.value = '';
      wrap.querySelector('.cb-qe-image-preview').innerHTML = '<small>' + esc(i18n.selectImage || 'Select image') + '</small>';
      state.dirty = true;
    });
    return wrap;
  }

  async function saveSection() {
    if (state.activeIndex === null || !state.section) return;
    setStatus(i18n.saving || 'Saving...');
    try {
      await request('/section/' + state.activeIndex, {
        method: 'PATCH',
        body: JSON.stringify({ section: state.section })
      });
      setStatus(i18n.saved || 'Saved');
      window.location.reload();
    } catch (error) {
      setStatus(error.message || i18n.error || 'Error', true);
    }
  }

  async function duplicateSection() {
    if (state.activeIndex === null) return;
    setStatus(i18n.saving || 'Saving...');
    try {
      await request('/section/' + state.activeIndex + '/duplicate', { method: 'POST', body: '{}' });
      window.location.reload();
    } catch (error) {
      setStatus(error.message || i18n.error || 'Error', true);
    }
  }

  async function deleteSection() {
    if (state.activeIndex === null || !window.confirm(i18n.removeConfirm || 'Remove section?')) return;
    setStatus(i18n.saving || 'Saving...');
    try {
      await request('/section/' + state.activeIndex, { method: 'DELETE' });
      window.location.reload();
    } catch (error) {
      setStatus(error.message || i18n.error || 'Error', true);
    }
  }

  function setSectionValue(section, key, value) {
    if (value && typeof value === 'object' && 'url' in value) {
      setImageValue(section, key, value);
      return;
    }
    section[key] = value;
  }

  function setImageValue(source, key, value) {
    const idKey = key === 'image' ? 'image_id' : key + '_id';
    const urlKey = key === 'image' ? 'image_url' : key + '_url';
    source[idKey] = value.id || 0;
    source[urlKey] = value.url || '';
  }

  function appendGroupTitle(parent, text) {
    const title = document.createElement('strong');
    title.className = 'cb-qe-group-title';
    title.textContent = text;
    parent.appendChild(title);
  }

  function setStatus(message, isError) {
    const status = drawer.querySelector('.cb-qe-status');
    if (!status) return;
    status.textContent = message || '';
    status.style.color = isError ? '#b4232a' : '';
  }

  function advancedUrl(index, tab) {
    const separator = (config.adminUrl || '').indexOf('?') === -1 ? '?' : '&';
    return (config.adminUrl || '#') + separator + 'section=' + encodeURIComponent(index == null ? '' : index) + '&tab=' + encodeURIComponent(tab || 'content');
  }

  function highlightSection(index) {
    clearHighlights();
    const target = document.querySelector('[data-cb-section-index="' + index + '"]');
    if (target) target.classList.add('cb-qe-target');
  }

  function clearHighlights() {
    document.querySelectorAll('.cb-qe-target').forEach(function (element) {
      element.classList.remove('cb-qe-target');
    });
  }

  function getSectionLabel(index) {
    const found = state.sections.find(function (section) { return Number(section.index) === Number(index); });
    return found ? found.label : '';
  }

  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (char) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
    });
  }
})();
