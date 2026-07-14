(function ($) {
  'use strict';

  const i18n = (window.cbCompanyAdmin && window.cbCompanyAdmin.i18n) || {};
  let isDirty = false;

  function initColorPicker(context) {
    if (!$.fn.wpColorPicker) return;
    $(context).find('.cb-color-field').each(function () {
      if (!$(this).hasClass('wp-color-picker')) {
        $(this).wpColorPicker();
      }
    });
  }

  function initSortables(context) {
    if (!$.fn.sortable) return;
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
    $(context).find('.cb-hero-slides').sortable({
      handle: '.cb-hero-slide-handle',
      items: '> .cb-hero-slide',
      update: function () { renumberHeroSlides($(this).closest('.cb-section-card')); }
    });
    $(context).find('.cb-trust-badge-list').sortable({
      handle: '.cb-trust-badge-handle',
      items: '> .cb-trust-badge-row',
      update: function () { renumberTrustBadges($(this).closest('.cb-hero-slide')); }
    });
  }

  function markDirty() {
    isDirty = true;
    $('.cb-unsaved-status').addClass('is-visible').text(i18n.unsavedChanges || '');
  }

  function auditDuplicateIds() {
    if (!document.querySelector('.cb-admin-shell')) return;
    const seen = new Set();
    const duplicates = new Set();
    document.querySelectorAll('[id]').forEach(function (element) {
      if (!element.id) return;
      if (seen.has(element.id)) duplicates.add(element.id);
      seen.add(element.id);
    });
    if (duplicates.size) {
      console.error('CB Company duplicate IDs:', Array.from(duplicates));
    }
  }

  function openMedia(field) {
    if (!window.wp || !wp.media) return;
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
      const isAllowed = allowed.indexOf(type) !== -1;
      const isHeroSlides = type === 'hero_slider' && $(this).hasClass('cb-hero-slides-field');
      $(this).toggle(isAllowed && (group === activeGroup || (isHeroSlides && ['content', 'images', 'design'].indexOf(activeGroup) !== -1)));
      $(this).find(':input').prop('disabled', !isAllowed);
      if (isHeroSlides) {
        $(this).find('[data-hero-group]').each(function () {
          $(this).toggle($(this).data('hero-group') === activeGroup);
        });
      }
    });
    const selectedLabel = card.find('.cb-section-type-select option:selected').text();
    const customTitle = card.find('[name$="[admin_label]"]').val() || card.find('[name$="[title]"]').val();
    card.find('.cb-section-title-text').text(customTitle || selectedLabel);
    card.find('.cb-layout-summary').text(card.find('[name$="[layout_style]"]').val() || 'default');
    const slides = card.find('.cb-hero-slides > .cb-hero-slide').length;
    if (type === 'hero_slider') {
      card.find('.cb-section-summary').text(slides + ' slide');
      card.find('.cb-image-summary').text(card.find('.cb-hero-slide').first().find('[name$="[image_url]"]').val() ? i18n.imageSelected : i18n.imageMissing);
    }
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

  function renumberHeroSlides(card) {
    card.find('.cb-hero-slides > .cb-hero-slide').each(function (index) {
      $(this).find('[name]').each(function () {
        $(this).attr('name', $(this).attr('name').replace(/\[slides\]\[(?:\d+|__slide__)\]/, '[slides][' + index + ']'));
      });
      $(this).find('.cb-hero-slide-number').text(index + 1);
      $(this).find('.cb-trust-badge-template').each(function () {
        $(this).html($(this).html().replace(/\[slides\]\[(?:\d+|__slide__)\]/g, '[slides][' + index + ']'));
      });
      renumberTrustBadges($(this));
    });
    applySectionVisibility(card);
    markDirty();
  }

  function renumberTrustBadges(slide) {
    slide.find('.cb-trust-badge-list > .cb-trust-badge-row').each(function (index) {
      $(this).find('[name]').each(function () {
        $(this).attr('name', $(this).attr('name').replace(/\[trust_badges\]\[(?:\d+|__badge__)\]/, '[trust_badges][' + index + ']'));
      });
    });
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

  $(document).on('click', '.cb-initialize-builder', function () {
    $('.cb-add-section').first().trigger('click');
    $(this).closest('.cb-builder-empty').remove();
  });

  $(document).on('click', '.cb-add-hero-slide', function () {
    const field = $(this).closest('.cb-hero-slides-field');
    field.find('> .cb-hero-slides').append(field.find('> .cb-hero-slide-template').html());
    renumberHeroSlides($(this).closest('.cb-section-card'));
    initSortables(field);
  });

  $(document).on('click', '.cb-duplicate-hero-slide', function () {
    const card = $(this).closest('.cb-section-card');
    $(this).closest('.cb-hero-slide').after($(this).closest('.cb-hero-slide').clone(false));
    renumberHeroSlides(card);
  });

  $(document).on('click', '.cb-remove-hero-slide', function () {
    const card = $(this).closest('.cb-section-card');
    $(this).closest('.cb-hero-slide').remove();
    renumberHeroSlides(card);
  });

  $(document).on('click', '.cb-add-trust-badge', function () {
    const root = $(this).closest('.cb-trust-badges');
    root.find('> .cb-trust-badge-list').append(root.find('> .cb-trust-badge-template').html());
    renumberTrustBadges($(this).closest('.cb-hero-slide'));
    initSortables(root);
  });

  $(document).on('click', '.cb-remove-trust-badge', function () {
    const slide = $(this).closest('.cb-hero-slide');
    $(this).closest('.cb-trust-badge-row').remove();
    renumberTrustBadges(slide);
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
    auditDuplicateIds();
  });
})(jQuery);
