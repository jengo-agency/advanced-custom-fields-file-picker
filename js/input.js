(function ($) {
  function initialize_field($el) {
    // Hide override by default
    $el.find('.acf-file-picker-location-override').hide();
    $el.find('.acf-file-picker-toggle-override').prop('checked', false);

    // Toggle on checkbox
    $el.find('.acf-file-picker-toggle-override').on('change', function () {
      if ($(this).is(':checked')) {
        $el.find('.acf-file-picker-location-override').slideDown(150);
      } else {
        $el.find('.acf-file-picker-location-override').slideUp(150);
      }
    });
  }

  /*
   *  ready append (ACF5)
   *
   *  These are 2 events which are fired during the page load
   *  ready = on page load similar to $(document).ready()
   *  append = on new DOM elements appended via repeater field
   *
   *  @type	event
   *  @date	20/07/13
   *
   *  @param	$el (jQuery selection) the jQuery element which contains the ACF fields
   *  @return	n/a
   */

  acf.add_action("ready append", function ($el) {
    // search $el for fields of type 'file_picker'
    acf.get_fields({ type: "file_picker" }, $el).each(function () {
      initialize_field($(this));
    });
  });
})(jQuery);
