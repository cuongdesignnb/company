(function ($) {
  'use strict';

  const i18n = (window.cbCompanyAdmin && window.cbCompanyAdmin.i18n) || {};
  let isDirty = false;

  function initColorPicker(context) {
    $(context).find('.cb-color-field').each(function () {
      if (!$(this).hasClass('wp-color-picker')) {
        $(this).wpColorPicker();
      }
    });
  }

  function initSortables(context) {
    $(context).find('.cb-sections-list').sortable({
      handle: '.cb-drag-handle',
      items: '> .cb-section-card',
      update: renumberSections
    });
    $(context).find('.cb-repeater-list').sortable({
      handle: '.cb-repeater-handle',
      items: '> .cb-repeater-row',
      update: function () {
        renumberRepeater($(this).closest('.cb-repeater'));
      }
    });
  }

  function markDirty() {
    isDirty = true;
    $('.cb-unsaved-status').addClass('is-visible').text(i18n.unsavedChanges || '');
  }

  function openMedia(field) {
    const frame = wp.media({
      title: field.data('frame-title') || i18n.selectImage,
      multiple: false,
      library: { type: 'image' }
    });
    frame.on('select', function () {
      const image = frame.state().get('selection').first().toJSON();
      field.find('> .cb-image-id').val(image.id);
      field.find('> .cb-image-url').val(image.url);
      field.find('> .cb-image-preview').html($('<img>', { src: image.url, alt: '' }));
      markDirty();
    });
    frame.open();
  }

  function applySectionVisibility(card) {
    const type = card.find('.cb-section-type-select').val();
    const activeGroup = card.find('.cb-section-tab.is-active').data('tab') || 'content';
    card.attr('data-section-type', type);
    card.find('.cb-builder-field').each(function () {
      const allowed = String($(this).data('visible-for') || '').split(',');
      const group = $(this).data('field-group');
      $(this).toggle(allowed.indexOf(type) !== -1 && group === activeGroup);
    });
    const selectedLabel = card.find('.cb-section-type-select option:selected').text();
    const customTitle = card.find('[name$="[admin_label]"]').val() || card.find('[name$="[title]"]').val();
    card.find('.cb-section-title-text').text(customTitle || selectedLabel);
    card.find('.cb-layout-summary').text(card.find('[name$="[layout_style]"]').val() || 'default');
  }

  function renumberSections() {
    $('.cb-sections-list > .cb-section-card').each(function (index) {
      $(this).find('[name]').each(function () {
        const name = $(this).attr('name');
        $(this).attr('name', name.replace(/_cb_page_sections\[(?:\d+|__new__)\]/, '_cb_page_sections[' + index + ']'));
      });
      $(this).find('.cb-section-number').text(index + 1);
      $(this).find('.cb-repeater').each(function () {
        const currentBase = String($(this).attr('data-name-base') || '');
        $(this).attr('data-name-base', currentBase.replace(/_cb_page_sections\[(?:\d+|__new__)\]/, '_cb_page_sections[' + index + ']'));
      });
      $(this).find('.cb-repeater-template').each(function () {
        $(this).html($(this).html().replace(/_cb_page_sections\[(?:\d+|__new__)\]/g, '_cb_page_sections[' + index + ']'));
      });
      $(this).find('.cb-repeater').each(function () {
        renumberRepeater($(this));
      });
    });
    markDirty();
  }

  function renumberRepeater(repeater) {
    const base = repeater.closest('.cb-builder-field').find('.cb-repeater').first().data('name-base');
    repeater.find('> .cb-repeater-list > .cb-repeater-row').each(function (index) {
      $(this).find('[name]').each(function () {
        const name = $(this).attr('name');
        $(this).attr('name', name.replace(/\[(?:\d+|__item__)\](?=\[[^\]]+\]$)/, '[' + index + ']'));
      });
      $(this).find('.cb-repeater-number').text(index + 1);
    });
    if (base) {
      repeater.attr('data-name-base', base);
    }
    markDirty();
  }

  $(document).on('click', '.cb-pick-image', function () {
    openMedia($(this).closest('.cb-image-field'));
  });

  $(document).on('click', '.cb-remove-image', function () {
    const field = $(this).closest('.cb-image-field');
    field.find('> .cb-image-id, > .cb-image-url').val('');
    field.find('> .cb-image-preview').empty();
    markDirty();
  });

  $(document).on('click', '.cb-collapse-section', function () {
    const card = $(this).closest('.cb-section-card');
    const collapsed = card.toggleClass('is-collapsed').hasClass('is-collapsed');
    card.toggleClass('is-editing', !collapsed);
    $(this).find('.cb-collapse-label').text(collapsed ? i18n.expand : i18n.collapse);
    $(this).find('.dashicons').toggleClass('dashicons-arrow-down-alt2', collapsed).toggleClass('dashicons-arrow-up-alt2', !collapsed);
  });

  $(document).on('click', '.cb-section-tab', function () {
    const card = $(this).closest('.cb-section-card');
    card.find('.cb-section-tab').removeClass('is-active');
    $(this).addClass('is-active');
    applySectionVisibility(card);
  });

  $(document).on('change', '.cb-section-type-select, .cb-builder-field input, .cb-builder-field select', function () {
    const card = $(this).closest('.cb-section-card');
    if (card.length) {
      applySectionVisibility(card);
    }
  });

  $(document).on('click', '.cb-duplicate-section', function () {
    const source = $(this).closest('.cb-section-card');
    const clone = source.clone(false);
    clone.find('.wp-picker-container').each(function () {
      const input = $(this).find('.cb-color-field').first().clone().removeClass('wp-color-picker');
      $(this).replaceWith(input);
    });
    source.after(clone);
    renumberSections();
    initColorPicker(clone);
    initSortables(clone);
    applySectionVisibility(clone);
  });

  $(document).on('click', '.cb-remove-section', function () {
    if (window.confirm(i18n.removeConfirm)) {
      $(this).closest('.cb-section-card').remove();
      renumberSections();
    }
  });

  $(document).on('click', '.cb-add-section', function () {
    const template = $('#cb-section-template').html();
    const card = $(template);
    $('.cb-sections-list').append(card);
    renumberSections();
    initColorPicker(card);
    initSortables(card);
    applySectionVisibility(card);
    card.find('.cb-collapse-section').trigger('click');
  });

  $(document).on('click', '.cb-add-repeater-item', function () {
    const repeater = $(this).closest('.cb-repeater');
    const template = repeater.find('> .cb-repeater-template').html();
    repeater.find('> .cb-repeater-list').append(template);
    renumberRepeater(repeater);
    initSortables(repeater);
  });

  $(document).on('click', '.cb-duplicate-repeater', function () {
    const repeater = $(this).closest('.cb-repeater');
    $(this).closest('.cb-repeater-row').after($(this).closest('.cb-repeater-row').clone(false));
    renumberRepeater(repeater);
  });

  $(document).on('click', '.cb-remove-repeater', function () {
    const repeater = $(this).closest('.cb-repeater');
    $(this).closest('.cb-repeater-row').remove();
    renumberRepeater(repeater);
  });

  $(document).on('click', '.cb-meta-tab', function () {
    const root = $(this).closest('.cb-page-ui, .cb-meta-tabs-shell');
    const tab = $(this).data('tab');
    root.find('.cb-meta-tab').removeClass('is-active');
    $(this).addClass('is-active');
    root.find('.cb-meta-panel').removeClass('is-active').filter('[data-panel="' + tab + '"]').addClass('is-active');
  });

  $(document).on('change', '.cb-override-switch input', function () {
    $(this).closest('.cb-page-override').toggleClass('has-override', this.checked);
  });

  $(document).on('submit', 'form', function () {
    isDirty = false;
  });

  $(document).on('change input', '.cb-settings-form :input, .cb-page-builder :input, .cb-page-ui :input', markDirty);
  $(document).on('submit', '.cb-reset-form', function () {
    return window.confirm(i18n.resetConfirm);
  });

  $(document).on('click', '.cb-reset-link', function (event) {
    if (!window.confirm(i18n.resetConfirm)) {
      event.preventDefault();
    }
  });

  window.addEventListener('beforeunload', function (event) {
    if (isDirty && $('.cb-settings-form').length) {
      event.preventDefault();
      event.returnValue = i18n.unsavedChanges;
    }
  });

  $(function () {
    initColorPicker(document);
    initSortables(document);
    $('.cb-section-card').each(function () { applySectionVisibility($(this)); });
    $('.cb-page-override').each(function () {
      $(this).toggleClass('has-override', $(this).find('.cb-override-switch input').is(':checked'));
    });
  });
})(jQuery);
