(function () {
  'use strict';

  const config = window.cbCompanyAdmin || {};
  const i18n = config.i18n || {};
  const form = document.querySelector('[data-cb-content-form]');
  if (!form) return;

  const postId = form.dataset.postId;
  const status = form.querySelector('.cb-content-save-status');
  const saveButton = form.querySelector('.cb-save-content');
  const revisionSelect = form.querySelector('.cb-content-revision');
  const restoreButton = form.querySelector('.cb-restore-content-revision');
  let dirty = false;

  function setStatus(message, state) {
    status.textContent = message;
    status.className = 'cb-content-save-status is-' + state;
  }

  function markDirty() {
    dirty = true;
    setStatus(i18n.unsavedChanges || 'Có thay đổi chưa lưu', 'dirty');
  }

  function request(path, options) {
    return fetch(config.restUrl + path, Object.assign({
      credentials: 'same-origin',
      headers: { 'X-WP-Nonce': config.nonce }
    }, options || {})).then(async function (response) {
      const data = await response.json();
      if (!response.ok) throw new Error(data.message || i18n.saveError || 'Không thể lưu thay đổi.');
      return data;
    });
  }

  form.addEventListener('input', markDirty);
  form.addEventListener('change', markDirty);
  form.addEventListener('submit', function (event) {
    event.preventDefault();
    saveButton.disabled = true;
    setStatus(i18n.saving || 'Đang lưu...', 'saving');
    request('admin/page-content/' + postId, {
      method: 'POST',
      body: new URLSearchParams(new FormData(form))
    }).then(function (data) {
      dirty = false;
      setStatus(data.message || i18n.saved || 'Đã lưu thay đổi.', 'saved');
      if (Array.isArray(data.revisions)) {
        revisionSelect.replaceChildren(new Option(i18n.revisionLabel || '', ''));
        data.revisions.forEach(function (revision) {
          revisionSelect.add(new Option(revision.label, revision.id));
        });
      }
      document.dispatchEvent(new CustomEvent('cb:toast', { detail: { message: data.message } }));
    }).catch(function (error) {
      setStatus(error.message, 'error');
    }).finally(function () {
      saveButton.disabled = false;
    });
  });

  revisionSelect.addEventListener('change', function () {
    restoreButton.disabled = !revisionSelect.value;
  });
  restoreButton.addEventListener('click', function () {
    if (!revisionSelect.value || !window.confirm(i18n.restoreConfirm || '')) return;
    restoreButton.disabled = true;
    request('admin/page-content/' + postId + '/revision', {
      method: 'POST',
      headers: { 'X-WP-Nonce': config.nonce, 'Content-Type': 'application/json' },
      body: JSON.stringify({ revision_id: revisionSelect.value })
    }).then(function () {
      dirty = false;
      window.location.reload();
    }).catch(function (error) {
      setStatus(error.message, 'error');
      restoreButton.disabled = false;
    });
  });

  window.addEventListener('beforeunload', function (event) {
    if (!dirty) return;
    event.preventDefault();
    event.returnValue = i18n.unsavedChanges || '';
  });
})();
