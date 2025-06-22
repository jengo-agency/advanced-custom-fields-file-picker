<?php

/*
Plugin Name: Advanced Custom Fields: File Picker
Plugin URI: https://github.com/getdave/advanced-custom-fields-file-picker
Description: Creates a new field type which allows you to pick files from a directory. Typically useful for picking SVG icons.
Version: 1.1.0
Author: David Smith + Arnaud Lapiere
Author URI: www.aheadcreative.co.uk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


// 1. set text domain
// Reference: https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
load_plugin_textdomain( 'acf-file_picker', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );


// 2. Include field type for ACF5
// $version = 5 and can be ignored until ACF6 exists
add_action('acf/include_field_types', function ( $version ) {
	require_once('classes/acf-file_picker-v5.php');
	// create field
	new ACFFilePicker\Field();
});


add_action('acf/register_fields', 'register_fields_file_picker');
