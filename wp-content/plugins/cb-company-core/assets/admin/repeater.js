(function () {
  'use strict';

  function renumber(repeater) {
    repeater.querySelectorAll(':scope > .cb-repeater-list > .cb-repeater-row').forEach(function (row, index) {
      row.querySelectorAll('[name]').forEach(function (input) {
        input.name = input.name.replace(/\[(?:\d+|__item__)\](?=\[[^\]]+\]$)/, '[' + index + ']');
      });
      const number = row.querySelector('.cb-repeater-number');
      if (number) number.textContent = String(index + 1);
    });
    repeater.dispatchEvent(new Event('change', { bubbles: true }));
  }

  document.addEventListener('click', function (event) {
    const add = event.target.closest('.cb-add-repeater-item');
    const duplicate = event.target.closest('.cb-duplicate-repeater');
    const remove = event.target.closest('.cb-remove-repeater');
    if (!add && !duplicate && !remove) return;
    event.preventDefault();
    const repeater = event.target.closest('.cb-repeater');
    if (!repeater) return;
    if (add) {
      const template = repeater.querySelector(':scope > .cb-repeater-template');
      repeater.querySelector(':scope > .cb-repeater-list').insertAdjacentHTML('beforeend', template.innerHTML);
    } else if (duplicate) {
      const row = duplicate.closest('.cb-repeater-row');
      row.insertAdjacentElement('afterend', row.cloneNode(true));
    } else if (remove) {
      remove.closest('.cb-repeater-row').remove();
    }
    renumber(repeater);
  });
}());
