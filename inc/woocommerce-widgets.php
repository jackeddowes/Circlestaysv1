<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */

if (!defined('ABSPATH')) {
	exit;
}

if (class_exists('WC_Widget_Cart')):

class Everything_WC_Widget_Cart extends WC_Widget_Cart
{

	function widget($args, $instance)
	{

		ob_start();
		parent::widget($args, $instance);
		$output = ob_get_clean();

		extract($args);

		if ($instance['show_cart_icon']) {
			$output = str_replace($before_title, $before_title . Everything::getShortcodeOutput('vector_icon', ['name' => Everything::to('woocommerce/cart/icon'), 'advanced_class' => 'icon-woocommerce-cart', 'advanced_style' => 'margin-right: 0.4em;']), $output);
		}

		echo $output;

	}

	function update($new_instance, $old_instance)
	{
		$instance = parent::update($new_instance, $old_instance);
		$instance['show_cart_icon'] = empty($new_instance['show_cart_icon']) ? 0 : 1;
		return $instance;
	}

	function form($instance)
	{
		parent::form($instance);
		$show_cart_icon = empty($instance['show_cart_icon']) ? 0 : 1;
		?>
			<p><input type="checkbox" class="checkbox" id="<?php echo esc_attr($this->get_field_id('show_cart_icon')); ?>" name="<?php echo esc_attr($this->get_field_name('show_cart_icon')); ?>"<?php checked($show_cart_icon); ?> />
			<label for="<?php echo $this->get_field_id('show_cart_icon'); ?>"><?php _e('Display cart icon', 'woocommerce'); ?></label></p>
		<?php
	}

}

endif;

if (class_exists('WC_Widget_Layered_Nav_Filters')):

class Everything_WC_Widget_Layered_Nav_Filters extends WC_Widget_Layered_Nav_Filters
{

	function widget($args, $instance)
	{

		ob_start();
		parent::widget($args, $instance);
		$output = ob_get_clean();

		$output = preg_replace('#<ul>(.*?)</ul>#i', '<p>\1</p>', $output);
		$output = preg_replace('#<li[^<>]*>(<a[^<>]*>)(.*?)(</a>)</li>#i', '<mark>\1<i class="icon-cancel"></i><i class="icon-cancel-circled"></i> \2\3</mark> ', $output);

		echo $output;

	}

}

endif;

if (class_exists('WC_Widget_Layered_Nav')):

class Everything_WC_Widget_Layered_Nav extends WC_Widget_Layered_Nav
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

if (class_exists('WC_Widget_Price_Filter')):

class Everything_WC_Widget_Price_Filter extends WC_Widget_Price_Filter
{
}

endif;

if (class_exists('WC_Widget_Product_Categories')):

class Everything_WC_Widget_Product_Categories extends WC_Widget_Product_Categories
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

if (class_exists('WC_Widget_Product_Search')):

class Everything_WC_Widget_Product_Search extends WC_Widget_Product_Search
{

	function widget($args, $instance)
	{

		ob_start();
		parent::widget($args, $instance);
		$output = ob_get_clean();

		$output = str_replace('<form ', '<form class="search" ', $output);
		$output = preg_replace('#<input type="submit"([^<>]*)>#i', '<button type="submit"\1><i class="icon-search"></i></button>', $output);

		echo $output;

	}

}

endif;

if (class_exists('WC_Widget_Product_Tag_Cloud')):

class Everything_WC_Widget_Product_Tag_Cloud extends WC_Widget_Product_Tag_Cloud
{

	function widget($args, $instance)
	{

		ob_start();
		parent::widget($args, $instance);
		$output = ob_get_clean();

		$output = str_replace('<div class="tagcloud">', '<div class="tagcloud alt">', $output);

		echo $output;

	}

}

endif;

if (class_exists('WC_Widget_Products')):

class Everything_WC_Widget_Products extends WC_Widget_Products
{

	function widget($args, $instance)
	{

		ob_start();
		parent::widget($args, $instance);
		$output = ob_get_clean();

		echo Everything::woocommerceWidgetParseList($output);

	}

}

endif;

if (class_exists('WC_Widget_Recent_Reviews')):

class Everything_WC_Widget_Recent_Reviews extends WC_Widget_Recent_Reviews
{

	function widget($args, $instance)
	{

		ob_start();
		parent::widget($args, $instance);
		$output = ob_get_clean();

		$output = Everything::woocommerceWidgetParseList($output);

		$output = preg_replace_callback('#' .
			'<li>' .
				'<a[^>]*?href="(?P<url>[^"]*)"[^>]*?>\s*(?P<thumbnail><img[^>]*>)(?P<title>[^<>]*)</a>' .
				'<div[^>]*class="star-rating"[^>]*><span[^>]*><strong class="rating">(?P<average>[,\.0-9]+)</strong>[^>]*</span></div>' .
				'<span class="reviewer">(?P<author>[^<>]*)</span>' .
			'</li>' .
		'#i', function ($m) {
			$html = \Drone\HTML::li();
			$html->addNew('figure')
				->class('alignright fixed')
				->addNew('a')
					->attr(Everything::getImageAttrs('a', ['border' => true, 'hover' => '', 'fanbcybox' => false]))
					->href($m['url'])
					->title($m['title'])
					->add($m['thumbnail']);
			$html->addNew('h3')
				->addNew('a')
					->href($m['url'])
					->title($m['title'])
					->add($m['title']);
			$html->add(Everything::getShortcodeOutput('rating', ['rate' => $m['average'] . '/5', 'author' => $m['author']]));
			return $html->toHTML();
		}, $output);

		echo $output;

	}

}

endif;

if (class_exists('WC_Widget_Recently_Viewed')):

class Everything_WC_Widget_Recently_Viewed extends WC_Widget_Recently_Viewed
{

	function widget($args, $instance)
	{

		ob_start();
		parent::widget($args, $instance);
		$output = ob_get_clean();

		echo Everything::woocommerceWidgetParseList($output);

	}

}

endif;

if (class_exists('WC_Widget_Top_Rated_Products')):

class Everything_WC_Widget_Top_Rated_Products extends WC_Widget_Top_Rated_Products
{

	function widget($args, $instance)
	{

		ob_start();
		parent::widget($args, $instance);
		$output = ob_get_clean();

		echo Everything::woocommerceWidgetParseList($output);

	}

}

endif;