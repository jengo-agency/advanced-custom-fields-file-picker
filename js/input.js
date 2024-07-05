(function ($) {
  function initialize_field($el) {
    //$el.doStuff();
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
