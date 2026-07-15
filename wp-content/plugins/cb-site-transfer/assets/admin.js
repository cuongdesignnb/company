(function () {
  'use strict';

  const config = window.cbSiteTransfer || {};
  const i18n = config.i18n || {};
  let currentJob = null;
  let processing = false;

  function element(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  }

  async function api(path, options) {
    const request = Object.assign({ method: 'GET', headers: {} }, options || {});
    request.headers['X-WP-Nonce'] = config.nonce;
    if (request.body && !(request.body instanceof FormData)) {
      request.headers['Content-Type'] = 'application/json';
      request.body = JSON.stringify(request.body);
    }
    const response = await fetch(config.restUrl + path, request);
    const data = await response.json();
    if (!response.ok) throw new Error(data.message || i18n.failed);
    return data;
  }

  function showMessage(container, message, type) {
    container.replaceChildren(element('p', 'cb-transfer-notice is-' + (type || 'info'), message));
  }

  function setProgress(job) {
    const progress = document.getElementById('cb-transfer-progress');
    progress.hidden = false;
    progress.querySelector('span').style.width = Math.max(0, Math.min(100, Number(job.progress || 0))) + '%';
    const label = (i18n.stepLabels && i18n.stepLabels[job.step]) || job.step || i18n.working;
    const countKeys = { terms: 'terms', attachments: 'attachments', posts: 'posts', relationships: 'posts', menus: 'menus' };
    const countKey = countKeys[job.step];
    const total = countKey && job.preflight && job.preflight.counts ? Number(job.preflight.counts[countKey] || 0) : 0;
    const current = job.offsets && countKey ? Number(job.offsets[job.step] || 0) : 0;
    progress.querySelector('p').textContent = total ? label + ' ' + Math.min(current, total) + '/' + total : label;
    document.getElementById('cb-transfer-pause').disabled = job.status !== 'running';
    document.getElementById('cb-transfer-resume').disabled = job.status !== 'paused';
    document.getElementById('cb-transfer-cancel').disabled = !['pending', 'running', 'paused'].includes(job.status);
  }

  function renderPreflight(job) {
    const container = document.getElementById('cb-transfer-preflight');
    const data = job.preflight || {};
    container.replaceChildren();
    const panel = element('div', 'cb-transfer-preflight');
    const grid = element('dl', 'cb-transfer-facts');
    const facts = [
      [i18n.packageVersion, data.package_version || ''],
      [i18n.exportedAt, data.exported_at || ''],
      [i18n.source, data.source_url || ''],
      [i18n.target, data.target_url || ''],
      [i18n.packageSize, data.package_size ? Math.ceil(Number(data.package_size) / 1024) + ' KB' : '0 KB'],
      [i18n.languages, (data.languages || []).join(', ')],
      [i18n.counts, JSON.stringify(data.counts || {})],
      [i18n.conflicts, JSON.stringify(data.conflicts || {})],
      [i18n.importPlan, JSON.stringify(data.import_plan || {})],
      [i18n.sourceVersions, JSON.stringify(data.source_versions || {})],
      [i18n.targetVersions, JSON.stringify(data.target_versions || {})],
      [i18n.environment, 'uploads: ' + (data.uploads_writable ? i18n.yes : i18n.no) + ' · ZIP: ' + (data.zip_available ? i18n.yes : i18n.no) + ' · WebP: ' + (data.webp_supported ? i18n.yes : i18n.no)]
    ];
    facts.forEach(function (fact) {
      grid.append(element('dt', '', fact[0]), element('dd', '', fact[1]));
    });
    panel.append(grid);
    (data.blocking_errors || []).forEach(function (message) { panel.append(element('p', 'cb-transfer-notice is-error', message)); });
    (data.warnings || []).forEach(function (message) { panel.append(element('p', 'cb-transfer-notice is-warning', message)); });
    container.append(panel);
    const blocked = (data.blocking_errors || []).length > 0;
    document.getElementById('cb-transfer-dry-run').disabled = blocked;
    document.getElementById('cb-transfer-start').disabled = blocked;
    setProgress(job);
  }

  async function refreshHistory() {
    const data = await api('jobs');
    const container = document.getElementById('cb-transfer-history');
    container.replaceChildren();
    if (!data.jobs.length) {
      container.append(element('p', '', i18n.noJobs));
      return;
    }
    const table = element('table', 'widefat striped');
    const head = element('thead');
    const headRow = element('tr');
    (i18n.historyHeaders || []).forEach(function (label) { headRow.append(element('th', '', label)); });
    head.append(headRow);
    const body = element('tbody');
    data.jobs.forEach(function (job) {
      const row = element('tr');
      const entityCounts = (job.report && job.report.counts) || (job.preflight && job.preflight.counts) || (job.report || {});
      row.append(
        element('td', '', job.created_at),
        element('td', '', job.user_name || ''),
        element('td', '', job.type),
        element('td', '', job.package_name || ''),
        element('td', '', job.status),
        element('td', '', JSON.stringify(entityCounts)),
        element('td', '', (job.warnings || []).join('; ') || i18n.noWarnings)
      );
      const actions = element('td');
      if (job.download_url) {
        const download = element('a', 'button button-small', i18n.downloadPackage);
        download.href = job.download_url;
        actions.append(download);
      }
      const report = element('a', 'button button-small', i18n.report);
      report.href = job.report_url;
      actions.append(report);
      row.append(actions);
      body.append(row);
    });
    table.append(head, body);
    container.append(table);
  }

  async function refreshRollbacks() {
    const data = await api('rollbacks');
    const container = document.getElementById('cb-transfer-rollbacks');
    container.replaceChildren();
    if (!data.rollbacks.length) {
      container.append(element('p', '', i18n.noRollbacks));
      return;
    }
    data.rollbacks.forEach(function (snapshot) {
      const row = element('div', 'cb-transfer-rollback-row');
      row.append(element('div', '', snapshot.created_at + ' · ' + snapshot.job_id + ' · ' + snapshot.status));
      const button = element('button', 'button button-secondary', i18n.rollback);
      button.type = 'button';
      button.disabled = snapshot.status !== 'available';
      button.addEventListener('click', async function () {
        if (!window.confirm(i18n.rollbackConfirm) || !window.confirm(i18n.rollbackConfirmAgain)) return;
        button.disabled = true;
        try {
          await api('rollbacks/' + encodeURIComponent(snapshot.rollback_id), { method: 'POST', body: { confirm: snapshot.rollback_id } });
          await Promise.all([refreshRollbacks(), refreshHistory()]);
        } catch (error) {
          window.alert(error.message);
          button.disabled = false;
        }
      });
      row.append(button);
      container.append(row);
    });
  }

  async function processJob() {
    if (!currentJob || processing || currentJob.status !== 'running') return;
    processing = true;
    try {
      const data = await api('jobs/' + encodeURIComponent(currentJob.job_id) + '/process', { method: 'POST', body: {} });
      currentJob = data.job;
      setProgress(currentJob);
      if (currentJob.status === 'running') {
        processing = false;
        window.setTimeout(processJob, 120);
      } else {
        processing = false;
        await Promise.all([refreshHistory(), refreshRollbacks()]);
      }
    } catch (error) {
      processing = false;
      showMessage(document.getElementById('cb-transfer-preflight'), error.message, 'error');
    }
  }

  async function startImport(dryRun) {
    if (!currentJob) return;
    const mode = document.getElementById('cb-transfer-mode').value;
    const data = await api('jobs/' + encodeURIComponent(currentJob.job_id) + '/start', { method: 'POST', body: { mode: mode, dry_run: dryRun } });
    currentJob = data.job;
    setProgress(currentJob);
    if (dryRun) {
      renderPreflight(currentJob);
      document.getElementById('cb-transfer-preflight').append(element('p', 'cb-transfer-notice is-success', i18n.dryRunDone));
      document.getElementById('cb-transfer-start').disabled = false;
    } else {
      processJob();
    }
  }

  async function uploadPackage(file) {
    if (!file) return;
    if (file.size > Number(config.maxPackageSize || 0)) throw new Error(i18n.failed);
    const form = new FormData();
    form.append('package', file);
    showMessage(document.getElementById('cb-transfer-preflight'), i18n.uploading, 'info');
    const data = await api('upload', { method: 'POST', body: form });
    currentJob = data.job;
    renderPreflight(currentJob);
    await refreshHistory();
  }

  document.querySelectorAll('.cb-transfer-tab').forEach(function (button) {
    button.addEventListener('click', function () {
      document.querySelectorAll('.cb-transfer-tab').forEach(function (item) { item.classList.toggle('is-active', item === button); });
      document.querySelectorAll('.cb-transfer-panel').forEach(function (panel) { panel.classList.toggle('is-active', panel.dataset.panel === button.dataset.tab); });
      if (button.dataset.tab === 'history') refreshHistory().catch(function () {});
      if (button.dataset.tab === 'rollback') refreshRollbacks().catch(function () {});
    });
  });

  document.getElementById('cb-transfer-export').addEventListener('click', async function () {
    const button = this;
    const result = document.getElementById('cb-export-result');
    const selection = {};
    document.querySelectorAll('[name^="cb_export_"]').forEach(function (input) { selection[input.name.replace('cb_export_', '')] = input.checked; });
    button.disabled = true;
    showMessage(result, i18n.exporting, 'info');
    try {
      const data = await api('export', { method: 'POST', body: selection });
      const link = element('a', 'button button-primary', i18n.downloadPackage);
      link.href = data.job.download_url;
      result.replaceChildren(link);
      await refreshHistory();
    } catch (error) {
      showMessage(result, error.message, 'error');
    } finally {
      button.disabled = false;
    }
  });

  const packageInput = document.getElementById('cb-transfer-package');
  packageInput.addEventListener('change', function () { uploadPackage(this.files[0]).catch(function (error) { showMessage(document.getElementById('cb-transfer-preflight'), error.message, 'error'); }); });
  const dropzone = document.getElementById('cb-transfer-dropzone');
  ['dragenter', 'dragover'].forEach(function (eventName) { dropzone.addEventListener(eventName, function (event) { event.preventDefault(); dropzone.classList.add('is-dragging'); }); });
  ['dragleave', 'drop'].forEach(function (eventName) { dropzone.addEventListener(eventName, function (event) { event.preventDefault(); dropzone.classList.remove('is-dragging'); }); });
  dropzone.addEventListener('drop', function (event) { uploadPackage(event.dataTransfer.files[0]).catch(function (error) { showMessage(document.getElementById('cb-transfer-preflight'), error.message, 'error'); }); });
  dropzone.addEventListener('click', function (event) { if (event.target !== packageInput) packageInput.click(); });
  dropzone.addEventListener('keydown', function (event) { if (event.key === 'Enter' || event.key === ' ') packageInput.click(); });

  document.getElementById('cb-transfer-dry-run').addEventListener('click', function () { startImport(true).catch(function (error) { window.alert(error.message); }); });
  document.getElementById('cb-transfer-start').addEventListener('click', function () { startImport(false).catch(function (error) { window.alert(error.message); }); });
  document.getElementById('cb-transfer-pause').addEventListener('click', async function () { const data = await api('jobs/' + currentJob.job_id + '/pause', { method: 'POST', body: {} }); currentJob = data.job; setProgress(currentJob); });
  document.getElementById('cb-transfer-resume').addEventListener('click', async function () { const data = await api('jobs/' + currentJob.job_id + '/resume', { method: 'POST', body: {} }); currentJob = data.job; setProgress(currentJob); processJob(); });
  document.getElementById('cb-transfer-cancel').addEventListener('click', async function () { const data = await api('jobs/' + currentJob.job_id + '/cancel', { method: 'POST', body: {} }); currentJob = data.job; setProgress(currentJob); await refreshHistory(); });

  refreshHistory().catch(function () {});
  refreshRollbacks().catch(function () {});
}());
