(function () {
  'use strict';

  const config = window.cbCompanyAdmin || {};
  const i18n = config.i18n || {};

  function applyImage(field, image) {
    const id = field.querySelector('.cb-image-id');
    const url = field.querySelector('.cb-image-url');
    const preview = field.querySelector(':scope > .cb-image-preview');
    if (id) id.value = image.id;
    if (url) {
      url.value = image.url;
      url.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (preview) {
      preview.innerHTML = '';
      const img = document.createElement('img');
      img.src = image.url;
      img.alt = '';
      preview.appendChild(img);
    }
  }

  function openLightPicker(field) {
    const modal = document.createElement('div');
    modal.className = 'cb-media-modal';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-label', field.dataset.frameTitle || i18n.selectImage || '');
    modal.innerHTML = '<div class="cb-media-backdrop"></div><div class="cb-media-dialog"><header><h2></h2><button type="button" class="button-link cb-media-close"></button></header><div class="cb-media-toolbar"><input type="search" class="regular-text cb-media-search"><label class="button cb-media-upload"><span></span><input type="file" accept="image/*" hidden></label></div><p class="cb-media-status" aria-live="polite"></p><div class="cb-media-grid"></div></div>';
    modal.querySelector('h2').textContent = field.dataset.frameTitle || i18n.selectImage || '';
    modal.querySelector('.cb-media-close').textContent = i18n.close || '';
    modal.querySelector('.cb-media-search').placeholder = i18n.searchImages || '';
    modal.querySelector('.cb-media-upload span').textContent = i18n.uploadImage || '';
    const grid = modal.querySelector('.cb-media-grid');
    const status = modal.querySelector('.cb-media-status');
    let controller = null;

    function close() {
      if (controller) controller.abort();
      modal.remove();
    }

    function render(items) {
      grid.innerHTML = '';
      if (!items.length) status.textContent = i18n.noImages || '';
      items.forEach(function (item) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'cb-media-item';
        button.setAttribute('aria-label', (i18n.chooseImage || '') + ': ' + (item.alt_text || item.title.rendered));
        const image = document.createElement('img');
        image.src = item.media_details && item.media_details.sizes && item.media_details.sizes.thumbnail
          ? item.media_details.sizes.thumbnail.source_url
          : item.source_url;
        image.alt = item.alt_text || '';
        const label = document.createElement('span');
        label.textContent = item.title.rendered || '';
        button.append(image, label);
        button.addEventListener('click', function () {
          applyImage(field, { id: item.id, url: item.source_url });
          close();
        });
        grid.appendChild(button);
      });
    }

    function load(search) {
      if (controller) controller.abort();
      controller = new AbortController();
      status.textContent = i18n.loading || '';
      const url = new URL(config.mediaRestUrl);
      url.searchParams.set('media_type', 'image');
      url.searchParams.set('per_page', '24');
      url.searchParams.set('orderby', 'date');
      url.searchParams.set('order', 'desc');
      if (search) url.searchParams.set('search', search);
      fetch(url.toString(), { credentials: 'same-origin', headers: { 'X-WP-Nonce': config.nonce }, signal: controller.signal })
        .then(function (response) { if (!response.ok) throw new Error(i18n.loadError); return response.json(); })
        .then(function (items) { status.textContent = ''; render(items); })
        .catch(function (error) { if (error.name !== 'AbortError') status.textContent = error.message || i18n.loadError || ''; });
    }

    modal.querySelector('.cb-media-close').addEventListener('click', close);
    modal.querySelector('.cb-media-backdrop').addEventListener('click', close);
    modal.addEventListener('keydown', function (event) { if (event.key === 'Escape') close(); });
    const search = modal.querySelector('.cb-media-search');
    search.addEventListener('input', function () {
      window.clearTimeout(search.cbTimer);
      search.cbTimer = window.setTimeout(function () { load(search.value.trim()); }, 260);
    });
    modal.querySelector('input[type="file"]').addEventListener('change', function (event) {
      const file = event.target.files[0];
      if (!file) return;
      status.textContent = i18n.loading || '';
      fetch(config.mediaRestUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-WP-Nonce': config.nonce,
          'Content-Type': file.type || 'application/octet-stream',
          'Content-Disposition': 'attachment; filename="' + encodeURIComponent(file.name) + '"'
        },
        body: file
      }).then(function (response) { if (!response.ok) throw new Error(i18n.loadError); return response.json(); })
        .then(function (item) { applyImage(field, { id: item.id, url: item.source_url }); close(); })
        .catch(function (error) { status.textContent = error.message || i18n.loadError || ''; });
    });
    document.body.appendChild(modal);
    search.focus();
    load('');
  }

  document.addEventListener('click', function (event) {
    const pick = event.target.closest('.cb-pick-image');
    const remove = event.target.closest('.cb-remove-image');
    if (!pick && !remove) return;
    const field = event.target.closest('.cb-image-field');
    if (!field) return;
    event.preventDefault();
    if (remove) {
      field.querySelectorAll('.cb-image-id, .cb-image-url').forEach(function (input) {
        input.value = '';
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });
      const preview = field.querySelector(':scope > .cb-image-preview');
      if (preview) preview.innerHTML = '';
      return;
    }
    if (!window.wp || !wp.media) {
      openLightPicker(field);
      return;
    }
    const frame = wp.media({
      title: field.dataset.frameTitle || (window.cbCompanyAdmin.i18n || {}).selectImage,
      multiple: false,
      library: { type: 'image' }
    });
    frame.on('select', function () {
      const image = frame.state().get('selection').first().toJSON();
      applyImage(field, { id: image.id, url: image.url });
    });
    frame.open();
  });

  document.addEventListener('input', function (event) {
    if (!event.target.matches('.cb-image-url')) return;
    const field = event.target.closest('.cb-image-field');
    if (!field) return;
    const id = field.querySelector('.cb-image-id');
    const preview = field.querySelector(':scope > .cb-image-preview');
    const url = event.target.value.trim();
    if (id) id.value = '';
    if (preview) {
      preview.innerHTML = '';
      if (url) {
        const image = document.createElement('img');
        image.src = url;
        image.alt = '';
        preview.appendChild(image);
      }
    }
  });
}());
