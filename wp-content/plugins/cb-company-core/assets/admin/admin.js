(function ($) {
  function initColorPicker(context) {
    $(context).find('.cb-color-field').wpColorPicker();
  }

  function initSortable() {
    $('.cb-sections-list').sortable({ handle: '.cb-drag-handle', items: '.cb-section-card' });
  }

  $(document).on('click', '.cb-pick-image', function () {
    const field = $(this).closest('.cb-image-field');
    const frame = wp.media({ title: field.data('frame-title') || 'Select image', multiple: false, library: { type: 'image' } });
    frame.on('select', function () {
      const image = frame.state().get('selection').first().toJSON();
      field.find('.cb-image-id').val(image.id);
      field.find('.cb-image-url').val(image.url);
      field.find('.cb-image-preview').html('<img src="' + image.url + '" alt="">');
    });
    frame.open();
  });

  $(document).on('click', '.cb-remove-image', function () {
    const field = $(this).closest('.cb-image-field');
    field.find('.cb-image-id,.cb-image-url').val('');
    field.find('.cb-image-preview').empty();
  });

  $(document).on('click', '.cb-collapse-section', function () {
    $(this).closest('.cb-section-card').toggleClass('is-collapsed');
  });

  $(document).on('click', '.cb-duplicate-section', function () {
    const source = $(this).closest('.cb-section-card');
    const clone = source.clone(false);
    clone.find('input, textarea, select').each(function () {
      const name = $(this).attr('name');
      if (name) {
        $(this).attr('name', name.replace(/cb_homepage_sections\[(\d+)\]/, 'cb_homepage_sections[__new__]'));
      }
    });
    source.after(clone);
    renumberSections();
    initColorPicker(clone);
  });

  $(document).on('click', '.cb-remove-section', function () {
    if (window.confirm('Remove this section?')) {
      $(this).closest('.cb-section-card').remove();
      renumberSections();
    }
  });

  $(document).on('click', '.cb-add-section', function () {
    const template = $('#cb-section-template').html();
    $('.cb-sections-list').append(template);
    renumberSections();
    initColorPicker($('.cb-sections-list .cb-section-card').last());
  });

  function renumberSections() {
    $('.cb-sections-list .cb-section-card').each(function (index) {
      $(this).find('input, textarea, select').each(function () {
        const name = $(this).attr('name');
        if (name) {
          $(this).attr('name', name.replace(/cb_homepage_sections\[(?:\d+|__new__)\]/, 'cb_homepage_sections[' + index + ']'));
        }
      });
      $(this).find('.cb-section-number').text(index + 1);
    });
  }

  $(function () {
    initColorPicker(document);
    initSortable();
  });
})(jQuery);
