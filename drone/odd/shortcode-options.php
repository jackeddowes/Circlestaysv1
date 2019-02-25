<?php








namespace Drone;

if (!defined('ABSPATH')) {
	exit;
}






function shortcode_options_locale() {
	return [
		'title'       => __('Insert/edit shortcode', 'everything'),
		'no_controls' => __("This shortcode doesn't have any options", 'everything')
	];
}

$strings = sprintf(
	"tinyMCE.addI18n('%s.drone_shortcode_options', %s);\n",
	\_WP_Editors::$mce_locale,
	json_encode(shortcode_options_locale())
);