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

  function enhanceSectionCards(context) {
    $(context).find('.cb-section-card').each(function () {
      const card = $(this);
      const looseThumb = card.children('.cb-section-thumb').first();
      if (looseThumb.length && !card.find('.cb-section-head > .cb-section-thumb').length) {
        looseThumb.insertAfter(card.find('.cb-drag-handle').first());
      }
      const previewUrl = card.attr('data-preview-url');
      if (previewUrl && !card.find('.cb-preview-section').length) {
        card.find('.cb-section-actions').prepend('<a class="button cb-preview-section" href="' + previewUrl + '" target="_blank" rel="noopener"><span class="dashicons dashicons-visibility" aria-hidden="true"></span> Xem trên trang</a>');
      }
      const count = card.attr('data-item-count');
      if (count && !card.find('.cb-item-count-summary').length) {
        card.find('.cb-section-title small').append(' · <span class="cb-item-count-summary">' + count + ' mục</span>');
      }
    });
  }

  function openSectionFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const section = params.get('section');
    const tab = params.get('tab') || 'content';
    if (section === null) return;
    const card = $('.cb-section-card[data-section-index="' + section + '"]').first();
    if (!card.length) return;
    if (card.hasClass('is-collapsed')) {
      card.find('.cb-collapse-section').first().trigger('click');
    }
    const tabButton = card.find('.cb-section-tab[data-tab="' + tab + '"]').first();
    if (tabButton.length) {
      tabButton.trigger('click');
    }
    card.addClass('cb-section-deep-link-target');
    $('html, body').animate({ scrollTop: Math.max(0, card.offset().top - 96) }, 300);
    window.setTimeout(function () {
      card.removeClass('cb-section-deep-link-target');
    }, 3000);
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

  function openFileMedia(field) {
    if (!window.wp || !wp.media) return;
    const frame = wp.media({
      title: i18n.selectFile || 'Chọn tệp PDF',
      button: { text: i18n.useFile || 'Dùng tệp này' },
      multiple: false,
      library: { type: field.find('.cb-pick-file').data('media-type') || 'application/pdf' }
    });
    frame.on('select', function () {
      const file = frame.state().get('selection').first().toJSON();
      field.find('.cb-file-id').val(file.id);
      field.find('.cb-file-url').val(file.url).trigger('change');
      field.find('.cb-current-file').remove();
      $('<a>', {
        class: 'cb-current-file',
        href: file.url,
        target: '_blank',
        rel: 'noopener',
        text: file.filename || file.title || file.url
      }).insertBefore(field.find('.cb-media-actions'));
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
      $(this).attr('data-section-index', index);
      const previewUrl = $(this).attr('data-preview-url');
      if (previewUrl) {
        const updatedUrl = previewUrl.replace(/([?&]cb_focus_section=)[^&]*/, '$1' + index);
        $(this).attr('data-preview-url', updatedUrl);
        $(this).find('.cb-preview-section').attr('href', updatedUrl);
      }
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

  $(document).on('click', '.cb-pick-file', function () {
    openFileMedia($(this).closest('.cb-admin-file-field'));
  });

  $(document).on('click', '.cb-remove-file', function () {
    const field = $(this).closest('.cb-admin-file-field');
    field.find('.cb-file-id, .cb-file-url').val('').trigger('change');
    field.find('.cb-current-file').remove();
    markDirty();
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

  $(document).on('click', '.cb-add-section-preset', function () {
    const type = $(this).data('section-type') || 'hero_slider';
    $('.cb-add-section').first().trigger('click');
    const card = $('.cb-sections-list > .cb-section-card').last();
    card.find('.cb-section-type-select').val(type).trigger('change');
    applySectionVisibility(card);
    enhanceSectionCards(card);
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
    enhanceSectionCards(document);
    $('.cb-section-card').each(function () { applySectionVisibility($(this)); });
    $('.cb-page-override').each(function () {
      $(this).toggleClass('has-override', $(this).find('.cb-override-switch input').is(':checked'));
    });
    auditDuplicateIds();
    openSectionFromUrl();
  });
})(jQuery);
