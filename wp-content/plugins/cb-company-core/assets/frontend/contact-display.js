(function () {
  'use strict';

  const config = window.cbCompanyContactDisplay || {};
  if (!config.restUrl || !window.fetch) return;

  function isWechatLabel(element) {
    const text = (element && element.textContent ? element.textContent : '').trim().toLowerCase();
    return text.includes('wechat id') || text.includes('微信 id');
  }

  function setFollowingValue(label, wechatId) {
    if (!label) return false;
    let value = label.nextElementSibling;
    if (value && ['SMALL', 'SPAN', 'CODE'].includes(value.tagName)) {
      value.textContent = wechatId;
      value.classList.remove('is-empty');
      return true;
    }
    value = document.createElement('small');
    value.textContent = wechatId;
    label.insertAdjacentElement('afterend', value);
    return true;
  }

  function updatePopup(wechatId) {
    document.querySelectorAll('#cb-wechat-qr').forEach(function (popup) {
      const code = popup.querySelector('.cb-contact-identity code');
      if (code) {
        code.textContent = wechatId;
        code.closest('.cb-contact-identity').classList.remove('is-empty');
        return;
      }
      const label = Array.from(popup.querySelectorAll('strong, small')).find(isWechatLabel);
      if (!setFollowingValue(label, wechatId)) {
        const identity = document.createElement('span');
        identity.className = 'cb-contact-identity';
        const identityLabel = document.createElement('small');
        identityLabel.textContent = 'WeChat ID';
        const identityValue = document.createElement('code');
        identityValue.textContent = wechatId;
        identity.append(identityLabel, identityValue);
        popup.appendChild(identity);
      }
      const trigger = document.querySelector('[aria-controls="cb-wechat-qr"]');
      if (trigger) trigger.setAttribute('aria-label', 'WeChat ' + wechatId);
    });
  }

  function updateContactPage(wechatId) {
    document.querySelectorAll('.cb-contact-qr-item figcaption').forEach(function (caption) {
      const label = Array.from(caption.querySelectorAll('strong, small')).find(isWechatLabel);
      if (label) setFollowingValue(label, wechatId);
    });
    document.querySelectorAll('.cb-contact-id-value').forEach(function (value) {
      value.textContent = wechatId;
      value.classList.remove('is-empty');
    });
  }

  fetch(config.restUrl, { credentials: 'same-origin', cache: 'no-store' })
    .then(function (response) {
      if (!response.ok) throw new Error('Unable to load contact settings');
      return response.json();
    })
    .then(function (data) {
      const wechatId = String(data.wechatId || '').trim();
      if (!wechatId) return;
      updatePopup(wechatId);
      updateContactPage(wechatId);
    })
    .catch(function () {
      // Server-rendered contact information remains available as a fallback.
    });
}());
