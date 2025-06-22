<?php

namespace ACFFilePicker;

class Field extends \acf_field {

	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/

	function __construct() {

		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/

		$this->name = 'file_picker';


		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/

		$this->label = __('File Picker', 'acf-file_picker');


		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/

		$this->category = 'choice';


		/*
		*  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
		*/

		$this->defaults = array();


		/*
		*  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
		*  var message = acf._e('file_picker', 'error');
		*/

		$this->l10n = array(
			'error'	=> __('Error! Please enter a higher value', 'acf-file_picker'),
		);


		// do not delete!
		parent::__construct();
	}


	/*
	*  render_field_settings()
	*
	*  Create extra settings for your field. These are visible when editing a field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	public function render_field_settings($field) {


		/*
		*  acf_render_field_setting
		*
		*  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
		*  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
		*
		*  More than one setting can be added by copy/paste the above code.
		*  Please note that you must also have a matching $defaults value for the field name (font_size)
		*/

		$theme_dir = $this->get_theme_directory();

		acf_render_field_setting($field, array(
			'label'			=> __('File Location', 'acf-file_picker'),
			'instructions'	=> __('Enter the path to the directory where you files are located relative to your Theme (stylesheet) directory.', 'acf-file_picker'),
			'type'			=> 'text',
			'name'			=> 'file_location',
			'prepend'		=>  $theme_dir . '/',
		));

		acf_render_field_setting($field, array(
			'label'			=> __('File Glob Pattern', 'acf-file_picker'),
			'instructions'	=> __('Optionally enter a Globbing pattern to filter files by a pattern. Operates according to the rules used by the PHP glob() function http://php.net/manual/en/function.glob.php', 'acf-file_picker'),
			'type'			=> 'text',
			'placeholder'	=> 'eg: *.{png,jpg,gif}',
			'name'			=> 'file_glob',
		));
	}


	private function get_theme_directory() {
		return substr(get_stylesheet_directory(), strlen(get_theme_root()));
	}


	private function get_file_location_directory($file_location) {
		$file_location = ltrim($file_location, '/'); // remove the first slash if the user has provided one...
		return trailingslashit(get_stylesheet_directory() . '/' . $file_location);
	}

	private function get_files_in_location($file_location, $file_glob) {


		$glob = (!empty($file_glob)) ? $file_glob : '*';


		$files = glob($file_location . $glob, GLOB_BRACE);

		if (count($files)) {
			$files = array_map(function ($filepath) use ($file_location) {
				return basename($filepath);
			}, $files);
		}

		return $files;
	}



	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field($field) {
        // Support value as array (for override), or string (legacy)
        $value = $field['value'];
        $file_location_override = '';
        $file_value = '';
        if (is_array($value)) {
            $file_value = isset($value['file']) ? $value['file'] : '';
            $file_location_override = isset($value['file_location']) ? $value['file_location'] : '';
        } else {
            $file_value = $value;
        }

        // Use override if set, else default from field settings
        $file_location_setting = isset($field['file_location']) ? $field['file_location'] : '';
        $file_location = $file_location_override !== '' ? $file_location_override : $file_location_setting;

        $file_location_dir = $this->get_file_location_directory($file_location);
        $file_uri_location = trailingslashit(get_stylesheet_directory_uri() . '/' . $file_location);
        $files = $this->get_files_in_location($file_location_dir, $field['file_glob']);

?>
        <p>
            <label>
                <?php esc_html_e('File Location (override):', 'acf-file_picker'); ?>
                <input type="text" name="<?php echo esc_attr($field['name']); ?>[file_location]" value="<?php echo esc_attr($file_location); ?>" style="width: 60%;" />
            </label>
        </p>
        <?php if (empty($files)) : ?>
			<p>No files were found in the location specified. Please check your field configuration.</p>
        <?php endif; ?>
        <select name="<?php echo esc_attr($field['name']); ?>[file]" data-dynamic-select>
            <option value="">Please select a file</option>
            <?php foreach ($files as $file) : ?>
                <option value="<?= $file ?>" data-img="<?= $file_uri_location . "/" . $file ?>" <?= ($file_value == $file) ? 'selected' : ''; ?>><?= $file ?></option>
            <?php endforeach; ?>
        </select>
<?php
    }


	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/



	function input_admin_enqueue_scripts() {
		$url = plugin_dir_url(dirname(__FILE__));
		$path = plugin_dir_path(dirname(__FILE__));

		//register & include JS
		wp_enqueue_script('acf-input-file_picker', "{$url}js/input.js", array(), filemtime("{$path}js/input.js"),array("strategy" => "defer"));
		wp_enqueue_script('dynamic-select', "{$url}js/dynamic-select.js", array('acf-input-file_picker'), filemtime("{$path}js/input.js"),array("strategy" => "defer"));


		//register & include CSS
		wp_enqueue_style('acf-input-file_picker', "{$url}css/input.css", array(), filemtime("{$path}css/input.css"),'screen');
		wp_enqueue_style('dynamic-select', "{$url}css/dynamic-select.css", array('acf-input-file_picker'), filemtime("{$path}css/dynamic-select.css"),'screen');

	}




	/*
	*  input_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
	function input_admin_head() {

	}
	*/


	/*
   	*  input_form_data()
   	*
   	*  This function is called once on the 'input' page between the head and footer
   	*  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and 
   	*  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
   	*  seen on comments / user edit forms on the front end. This function will always be called, and includes
   	*  $args that related to the current screen such as $args['post_id']
   	*
   	*  @type	function
   	*  @date	6/03/2014
   	*  @since	5.0.0
   	*
   	*  @param	$args (array)
   	*  @return	n/a
   	*/

	/*

   	function input_form_data( $args ) {

   	}

   	*/


	/*
	*  input_admin_footer()
	*
	*  This action is called in the admin_footer action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_footer)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function input_admin_footer() {

	}

	*/


	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add CSS + JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
	function field_group_admin_enqueue_scripts() {

	}
	*/


	/*
	*  field_group_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is edited.
	*  Use this action to add CSS and JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function field_group_admin_head() {

	}

	*/


	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/

	/*

	function load_value( $value, $post_id, $field ) {

		return $value;

	}

	*/


	/*
	*  update_value()
	*
	*  This filter is applied to the $value before it is saved in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/



	/*
	function update_value( $value, $post_id, $field ) {
			echo "<pre>";
			print_r($value);
			echo "</pre>";
			return $value;
		}
	*/



	/**
	 *  format_value
	 *
	 *  Add path (file_location) to returned file url
	 *
	 *  @type	filter
	 *  @since	3.6
	 *  @date	23/01/13
	 *
	 *  @param	$value (mixed) the value which was loaded from the database
	 *  @param	$post_id (mixed) the $post_id from which the value was loaded
	 *  @param	$field (array) the field array holding all the field options
	 *
	 *  @return	$value (mixed) the modified value
	 */

	function format_value($value, $post_id, $field) {
        if (empty($value)) {
            return $value;
        }
        // If value is array, use override logic
        if (is_array($value) && isset($value['file'])) {
            $file = $value['file'];
            $file_location = isset($value['file_location']) && $value['file_location'] !== '' ? $value['file_location'] : $field['file_location'];
        } else {
            $file = $value;
            $file_location = $field['file_location'];
        }
        if (empty($file)) {
            return '';
        }
        $url_prefix = trailingslashit(get_stylesheet_directory_uri() . '/' . $file_location);
        return $url_prefix . $file;
    }




	/*
	*  validate_value()
	*
	*  This filter is used to perform validation on the value prior to saving.
	*  All values are validated regardless of the field's required setting. This allows you to validate and return
	*  messages to the user if the value is not correct
	*
	*  @type	filter
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$valid (boolean) validation status based on the value and the field's required setting
	*  @param	$value (mixed) the $_POST value
	*  @param	$field (array) the field array holding all the field options
	*  @param	$input (string) the corresponding input name for $_POST value
	*  @return	$valid
	*/



	function validate_value($valid, $value, $field, $input) {
    // Support array value (with override) or string (legacy)
    if (is_array($value)) {
        $file = isset($value['file']) ? $value['file'] : '';
        $file_location = isset($value['file_location']) && $value['file_location'] !== '' ? $value['file_location'] : (isset($field['file_location']) ? $field['file_location'] : '');
    } else {
        $file = $value;
        $file_location = isset($field['file_location']) ? $field['file_location'] : '';
    }

    $directory = $this->get_file_location_directory($file_location);

    // Ensure the file actually exists...
    if ($file && !file_exists($directory . $file)) {
        $valid = __('That file does not exist or cannot be read', 'acf-file_picker');
    }

    // return
    return $valid;
}




	/*
	*  delete_value()
	*
	*  This action is fired after a value has been deleted from the db.
	*  Please note that saving a blank value is treated as an update, not a delete
	*
	*  @type	action
	*  @date	6/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (mixed) the $post_id from which the value was deleted
	*  @param	$key (string) the $meta_key which the value was deleted
	*  @return	n/a
	*/

	/*
	function delete_value( $post_id, $key ) {

	}
	*/


	/*
	*  load_field()
	*
	*  This filter is applied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/

	/*

	function load_field( $field ) {

		return $field;

	}

	*/


	/*
	*  update_field()
	*
	*  This filter is applied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/

	/*

	function update_field( $field ) {

		return $field;

	}

	*/


	/*
	*  delete_field()
	*
	*  This action is fired after a field is deleted from the database
	*
	*  @type	action
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	n/a
	*/

	/*

	function delete_field( $field ) {

	}
	*/
}



?>