<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */

if (!defined('ABSPATH')) {
	exit;
}

if (class_exists('WC_Widget_Brand_Nav')):

class Everything_WC_Widget_Brand_Nav extends WC_Widget_Brand_Nav
{

	function widget($args, $instance)
	{

		ob_start();
		parent::widget($args, $instance);
		$output = ob_get_clean();

		echo Everything::woocommerceWidgetParseNav($output);

	}

}

endif;