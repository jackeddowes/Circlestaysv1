<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */

use Drone\Func;
use Drone\Options\Option\ImageSelect;

if (!defined('ABSPATH')) {
	exit;
}

/*
 * General
 */
$general = $theme_options->addGroup('general', __('General', 'everything'));

$general->addOption('conditional_tags', 'layout', 'open', __('Layout type', 'everything'), '', ['type' => 'group', 'options' => [
	'open'  => __('Open', 'everything'),
	'boxed' => __('Boxed', 'everything')
]]);

$general->addOption('conditional_tags', 'max_width', Everything::$max_width, __('Maximum width', 'everything'), '', ['type' => 'number', 'min' => 768, 'unit' => 'px']);

$general->addOption('boolean', 'responsive', true, __('Responsive design', 'everything'), '', ['caption' => __('Enabled', 'everything')]);

$general->addOption('group', 'scheme', 'bright', __('Color scheme', 'everything'), '', ['options' => [
	'bright' => __('Bright', 'everything'),
	'dark'   => __('Dark', 'everything')
]]);

$general->addOption('color', 'color', '#0a70a6', __('Leading color', 'everything'));

$general->addEnabledOption('conditional_tags', 'background', false, ['image' => 0, 'color' => self::to_('general/layout')->value('default') == 'open' ? '#ffffff' : '#d8d8d8', 'alignment' => 'cover', 'position' => 'center top', 'attachment' => 'fixed', 'stripes' => false, 'opacity' => 100], __('Background', 'everything'), __('Use custom background', 'everything'), sprintf(__('For open layout type, recommended settings are %1$s and %2$s.', 'everything'), __('Fit (contain)', 'everything'), __('Scroll', 'everything')), ['type' => 'background', 'image_size' => 'full-hd']);

/*
 * Header
 */
$header = $theme_options->addGroup('header', __('Header', 'everything'));

$style = $header->addGroup('style', __('Style', 'everything'));
	$settings = $style->addOption('group', 'settings', [], '', '', ['multiple' => true, 'options' => [
		'centered' => __('Centered', 'everything'),
		'fixed'    => __('Sticky', 'everything'),
		'floated'  => __('Transparent', 'everything')
	]]);
	$style->addOption('number', 'opacity', 70, __('Opacity', 'everything'), '', ['min' => 0, 'max' => 100, 'unit' => '%', 'owner' => $settings, 'owner_value' => 'floated', 'indent' => true]);

$logo = $header->addGroup('logo', __('Logo', 'everything'));
	$logo->addOption('retina_image', 'image', ['x1' => 0, 'x2' => 0]);
	$logo->addOption('boolean', 'shrunken', false, '', '', ['caption' => __('Shrunken', 'everything'), 'owner' => $settings, 'owner_value' => 'fixed']);

$main_menu = $header->addGroup('main_menu', __('Main menu', 'everything'));
	$main_menu->addOption('group', 'visible', ['desktop', 'mobile'], __('Show on', 'everything'), '', ['multiple' => true, 'options' => [
		'desktop' => __('Desktop devices', 'everything'),
		'mobile'  => __('Mobile devices', 'everything')
	]]);

$cart = $header->addGroup('cart', __('Cart icon', 'everything'));
$cart->included = self::isPluginActive('woocommerce');
	$cart->addOption('group', 'visible', ['desktop', 'mobile'], __('Show on', 'everything'), '', ['multiple' => true, 'options' => [
		'desktop' => __('Desktop devices', 'everything'),
		'mobile'  => __('Mobile devices', 'everything')
	]]);
	$cart->addOption('group', 'content', ['count', 'total'],  __('Content', 'everything'), '', ['options' => [
		'count' => __('Number of products', 'everything'),
		'total' => __('Price of products', 'everything')
	], 'multiple' => true]);

$top_bar = $header->addGroup('top_bar', __('Top bar', 'everything'),
	(!self::isPluginActive('woocommerce') ? sprintf(__('The <a href="%s">WooCommerce plugin</a> is required for shop features.', 'everything'), 'https://woocommerce.com/') . '<br />' : '') .
	(!self::isPluginActive('polylang', 'wpml') ? sprintf(__('For multi-language site, a <a href="%s">WPML plugin</a> is required.', 'everything'), self::WPML_REFERRAL_URL) : '')
);
	$default  = ['tagline', '_', 'search', 'cart', 'menu', 'lang_menu'];
	$disabled = ['_'];
	if (!self::isPluginActive('woocommerce')) {
		unset($default[array_search('cart', $default)]);
		$disabled[] = 'cart';
	}
	if (!self::isPluginActive('polylang', 'wpml')) {
		unset($default[array_search('lang_menu', $default)]);
		$disabled[] = 'lang_menu';
	}
	foreach (['desktop' => __('On desktop devices', 'everything'), 'mobile'  => __('On mobile devices', 'everything')] as $device => $label) {
		$group = $top_bar->addGroup($device, $label);
		$visible = $group->addOption('boolean', 'visible', true, '', '', ['caption' => __('Visible', 'everything')]);
		$group->addOption('group', 'items', $default, '', '', ['options' => [
			'tagline'   => __('Tagline', 'everything'),
			'_'         => sprintf('&lsaquo; %s ... %s &rsaquo;', __('left side', 'everything'), __('right side', 'everything')),
			'search'    => __('Search form', 'everything'),
			'cart'      => __('Cart icon', 'everything'),
			'menu'      => __('Menu', 'everything'),
			'lang_menu' => __('Language menu', 'everything')
		], 'on_html' => function ($option, &$html) {
			foreach ($html->childs() as $child) {
				if ($child->tag == 'label' && $child->child(0)->value == '_') {
					$child->style = 'font-weight: bold;';
					$child->child(1)->style = 'display: none;';
				}
			}
		}, 'multiple' => true, 'sortable' => true, 'style' => 'horizontal', 'disabled' => $disabled, 'owner' => $visible, 'indent' => true]);
	}
	$top_bar->addOption('group', 'settings', [], '', '', ['multiple' => true, 'options' => [
		'fixed'         => __('Sticky', 'everything'),
		'mobile_toggle' => __('Show mobile main menu button in top bar', 'everything')
	]]);

$tagline = $header->addGroup('tagline', __('Tagline', 'everything'));
	$enabled = $tagline->addOption('boolean', 'enabled', false, '', '', ['caption' => __('Custom', 'everything')]);
	$tagline->addOption('text', 'text', get_option('blogdescription'), '', '', ['owner' => $enabled, 'indent' => true]);
	$mobile = $tagline->addGroup('mobile');
		$mobile->owner = $enabled;
		$enabled = $mobile->addOption('boolean', 'enabled', false, '', '', ['caption' => __('Alternative for mobile devices', 'everything'), 'owner' => $enabled]);
		$mobile->addOption('text', 'text', get_option('blogdescription'), '', '', ['owner' => $enabled, 'indent' => true]);

/*
 * Banner
 */
$banner = $theme_options->addGroup('banner', __('Banner', 'everything'));

$banner_default = ['type' => '', 'height' => 200, 'image' => 0, 'slider' => '', 'map' => '', 'page' => 0, 'embed' => '', 'custom' => ''];
if (!self::isPluginActive('layerslider', 'masterslider', 'revslider')) {
	unset($banner_default['slider']);
}
if (!self::isPluginActive('wild-googlemap', 'wp-google-map-plugin')) {
	unset($banner_default['map']);
}
$banner->addOption('conditional_tags', 'content', $banner_default, __('Content', 'everything'), '', ['type' => 'banner']);

/*
 * Navigation
 */
$nav = $theme_options->addGroup('nav', __('Navigation', 'everything'));

$secondary = $nav->addGroup('secondary');
	$secondary->addOption('conditional_tags', 'upper', true, __('Upper secondary menu', 'everything'), '', ['type' => 'boolean', 'caption' => __('Visible', 'everything')]);
	$secondary->addOption('conditional_tags', 'lower', true, __('Lower secondary menu', 'everything'), '', ['type' => 'boolean', 'caption' => __('Visible', 'everything')]);

$breadcrumbs = self::isPluginActive('breadcrumb-navxt', 'breadcrumb-trail');
$nav->addOption('conditional_tags', 'headline', $breadcrumbs ? 'breadcrumbs' : 'navigation', __('Headline', 'everything'), '', ['type' => 'group', 'options' => [
	''            => __('Hide', 'everything'),
	'none'        => __('None (title only)', 'everything'),
	'breadcrumbs' => __('Breadcrumbs', 'everything'),
	'mixed'       => __('Navigation (if possible) or breadcrumbs', 'everything'),
	'navigation'  => __('Only navigation (if possible)', 'everything')
], 'disabled' => !$breadcrumbs ? ['breadcrumbs', 'mixed'] : []]);

$nav->addOption('boolean', 'in_same_cat', true, __('Navigation', 'everything'), '', ['caption' => __('Next &amp; Previous buttons navigate in current category only', 'everything')]);

/*
 * Sidebar
 */
$sidebar = $theme_options->addGroup('sidebar', __('Sidebars', 'everything'));

$list = $sidebar->addGroup('list', __('Available sidebars', 'everything'), __('Usable width is 50px smaller because of paddings.', 'everything'));
	$builtin = $list->addGroup('builtin');
		$builtin->addOption('number', 'primary', Everything::DEFAULT_SIDEBAR_WIDTH, '1. ' . _x('Primary', 'sidebar', 'everything'), '', ['min' => 60, 'max' => 600, 'unit' => 'px']);
		$builtin->addOption('number', 'secondary', Everything::DEFAULT_SIDEBAR_WIDTH, '2. ' . _x('Secondary', 'sidebar', 'everything'), '', ['min' => 60, 'max' => 600, 'unit' => 'px']);
	$list->addOption('collection', 'additional', ['id' => __('New sidebar', 'everything'), 'width' => Everything::DEFAULT_SIDEBAR_WIDTH], '', '', ['type' => 'sidebar', 'unique_index' => true, 'index_prefix' => 'additional-', 'on_html' => function ($option, &$html) {
		$html->child(0)->start = 3;
	}]);

$sidebar_options =
	['' => __('(None)', 'everything')] +
	apply_filters('everything_sidebars',
		array_map(function ($sidebar) { return preg_replace('/^[0-9]\. /', '', $sidebar->label); }, self::to_('sidebar/list/builtin')->childs()) +
		array_map(function ($sidebar) { return $sidebar['id']; }, self::to('sidebar/list/additional'))
	);
$sidebar->addOption('conditional_tags', 'layout', ['#', '', 'primary'], __('Layout', 'everything'), '', ['type' => 'layout', 'options' => $sidebar_options]);

/*
 * Footer
 */
$footer = $theme_options->addGroup('footer', __('Footer', 'everything'));

$custom_layouts = apply_filters('everything_footer_custom_layouts', []);
$footer->addOption('conditional_tags', 'layout', '14_14_14_14', __('Layout', 'everything'), __('You can specify footer content in Appearance / Widgets.', 'everything'), ['type' => 'select', 'options' => [
	'11'                => __('Full width', 'everything'),
	'12_12'             => __('Two columns', 'everything'),
	'13_13_13'          => __('Three columns', 'everything'),
	'14_14_14_14'       => __('Four columns', 'everything'),
	'15_15_15_15_15'    => __('Five columns', 'everything'),
	'16_16_16_16_16_16' => __('Six columns', 'everything'),
	'14_34' => '25% + 75%',
	'34_14' => '75% + 25%',
	'14_14_12' => '25% + 25% + 50%',
	'12_14_14' => '50% + 25% + 25%',
	'14_12_14' => '25% + 50% + 25%',
	''         => __('Disabled', 'everything')
]+$custom_layouts, 'groups' => [
	__('Basic', 'everything')         => ['11', '12_12', '13_13_13', '14_14_14_14', '15_15_15_15_15', '16_16_16_16_16_16'],
	__('Two columns', 'everything')   => ['14_34', '34_14'],
	__('Three columns', 'everything') => ['14_14_12', '12_14_14', '14_12_14'],
	__('Other', 'everything')         => ['']+array_keys($custom_layouts)
]]);

$end_note = $footer->addGroup('end_note', __('End note', 'everything'));
	$visible = $end_note->addOption('boolean', 'visible', true, '', '', ['caption' => __('Show end note', 'everything')]);
	$left = $end_note->addOption('memo', 'left', sprintf(__('&copy; Copyright %s', 'everything'), date('Y')) . "\n" . sprintf(__('%1$s by <a href="%3$s">%2$s</a>', 'everything'), get_bloginfo('name'), wp_get_current_user()->display_name, esc_url(home_url('/'))), __('Left', 'everything'), '', [
		'owner'   => $visible,
		'on_html' => function ($option, $html) {
			$html->style = 'height: 70px;';
		}
	]);
	$right = $end_note->addOption('memo', 'right', sprintf(__('powered by %s WordPress theme', 'everything'), '<a href="' . Everything::STORE_PAGE_URL . '">Everything</a>'), __('Right', 'everything'), '', [
		'owner'   => $visible,
		'on_html' => function ($option, $html) {
			$html->style = 'height: 70px;';
		}
	]);

/*
 * Site
 */
$site = $theme_options->addGroup('site', __('Site', 'everything'));

$blog = $site->addGroup('blog', __('Blog style', 'everything'));
	$style = $blog->addOption('group', 'style', 'classic', '', '', ['options' => [
		'classic' => __('Classic', 'everything'),
		'bricks'  => __('Columns', 'everything')
	]]);
	$blog->addOption('number', 'columns', 2, '', '', ['min' => 1, 'max' => 8, 'owner' => $style, 'owner_value' => 'bricks', 'indent' => true]);
	$filter = $blog->addGroup('filter');
		$visible = $filter->addOption('boolean', 'visible', false, '', '', ['caption' => __('Display filter', 'everything'), 'owner' => $style, 'owner_value' => 'bricks']);
		$filter->addOption('select', 'filter', 'category', '', '', ['options' => [
			'category' => __('Category', 'everything'),
			'post_tag' => __('Tag', 'everything')
		], 'indent' => true, 'owner' => $visible]);

$image = $site->addGroup('image', __('Images', 'everything'));
	$settings = $image->addOption('group', 'settings', ['hover', 'fancybox'], '', '', ['options' => [
		'border'   => __('Border', 'everything'),
		'hover'    => __('Hover effect', 'everything'),
		'fancybox' => __('Open in FancyBox', 'everything')
	], 'multiple' => true]);
	$image->addOption('boolean', 'fancybox_horizontal_fit_only', false, '', '', ['caption' => __('Fit images to screen horizontally only', 'everything'), 'owner' => $settings, 'owner_value' => 'fancybox', 'indent' => true]);

$hover_icons = $site->addGroup('hover_icons', __('Image hover effect icons', 'everything'), __('Depending on link type.', 'everything'));
	$hover_icons_func = function () { return ImageSelect::cssToOptions('data/img/icons/icons.css'); };
	$hover_icons->addOption('image_select', 'default', 'plus-circled', __('Default', 'everything'), '', ['options' => $hover_icons_func, 'font_path' => Everything::ICON_FONT_PATH]);
	$hover_icons->addOption('image_select', 'image', 'search', __('Images', 'everything'), '', ['options' => $hover_icons_func, 'font_path' => Everything::ICON_FONT_PATH]);
	$hover_icons->addOption('image_select', 'mail', 'mail', __('E-mail addresses', 'everything'), '', ['options' => $hover_icons_func, 'font_path' => Everything::ICON_FONT_PATH]);
	$hover_icons->addOption('image_select', 'title', 'arrow-line-right', __('Links with title', 'everything'), '', ['options' => $hover_icons_func, 'font_path' => Everything::ICON_FONT_PATH]);

$site->addOption('select', 'pagination', 'numbers_navigation', __('Pagination', 'everything'), '', ['options' => [
	'numbers'            => __('Numbers', 'everything'),
	'numbers_navigation' => __('Numbers + navigation', 'everything')
]]);

$site->addOption('select', 'page_pagination', 'numbers', __('Page break pagination', 'everything'), '', ['options' => [
	'numbers'    => __('Numbers', 'everything'),
	'navigation' => __('Navigation', 'everything')
]]);

$comments = $site->addGroup('comments', __('Comments', 'everything'));
	$comments->addOption('select', 'date_format', 'relative', __('Date', 'everything'), __('If you select absolute, you can specify one of methods in Settings / General.', 'everything'), ['options' => [
		''         => __('None', 'everything'),
		'relative' => __('Relative', 'everything'),
		'absolute' => __('Absolute', 'everything')
	]]);
	$comments->addOption('select', 'pagination', 'numbers_navigation', __('Pagination', 'everything'), '', ['options' => [
		'numbers'            => __('Numbers', 'everything'),
		'numbers_navigation' => __('Numbers + navigation', 'everything')
	]]);

/*
 * Color
 */
$color = $theme_options->addGroup('color', __('Colors', 'everything'));

$color->addEnabledOption(
	'color', 'top_bar', false, '#ffffff',
	__('Top bar', 'everything'), __('Custom', 'everything'), '', ['tag' => '#top-bar, #top-bar .top-nav-menu li ul { background-color: %color; }']
);

$color->addEnabledOption(
	'color', 'header', false, '#ffffff',
	__('Header', 'everything'), __('Custom', 'everything'), '', ['tag' => '#header:before, .mobile-nav-menu { background-color: %color; }']
);

$color->addEnabledOption(
	'color', 'nav_main', false, '#ffffff',
	__('Main menu', 'everything'), __('Custom', 'everything'), __('Affects sub-menu only.', 'everything'), ['tag' => <<<'EOS'
		.nav-menu.main li ul { background-color: %color; }
		.nav-menu.main li ul:after { border-bottom-color: %color; }
		.nav-menu.main li li ul:after { border-right-color: %color; }
		.nav-menu.main li li ul.left:after { border-left-color: %color; }
		.nav-menu.main li ul, .nav-menu.main li.level-0.mega > ul > li:before { border-color: %darken; }
		.nav-menu.main li ul:before { border-bottom-color: %darken; }
		.nav-menu.main li li ul:before { border-right-color: %darken; }
		.nav-menu.main li li ul.left:before { border-left-color: %darken; }
EOS
	]
);

$color->addEnabledOption(
	'color', 'nav_secondary', false, '#ffffff',
	__('Secondary menu', 'everything'), __('Custom', 'everything'), '', ['tag' => '.nav-menu.secondary, .nav-menu.secondary li ul { background-color: %color; }']
);

$color->addEnabledOption(
	'color', 'headline', false, '#ffffff',
	__('Headline', 'everything'), __('Custom', 'everything'), '', ['tag' => '#headline { background-color: %rgba060; }']
);

$color->addEnabledOption(
	'color', 'content', false, '#ffffff',
	__('Content', 'everything'), __('Custom', 'everything'), '', ['tag' => <<<'EOS'
		.layout-boxed #content aside.aside .aside-nav-menu .current:not(.current-menu-parent):not(.current-menu-ancestor) > a:before { border-color: %color; }
		#content:before { background-color: %color; }
EOS
	]
);

$color->addEnabledOption(
	'color', 'hover', false, '#ffffff',
	__('Hover effect', 'everything'), __('Custom', 'everything'), '', ['tag' => '.zoom-hover > .zoom-hover-overlay { background-color: %rgba075; }']
);

$color->addEnabledOption(
	'color', 'footer', false, '#ffffff',
	__('Footer', 'everything'), __('Custom', 'everything'), '', ['tag' => '#footer { background-color: %color; }']
);

$color->addEnabledOption(
	'color', 'end_note', false, '#ffffff',
	__('End note', 'everything'), __('Custom', 'everything'), '', ['tag' => '#end-note { background-color: %color; }']
);

/*
 * Font
 */
$font = $theme_options->addGroup('font', __('Fonts', 'everything'));

$font->addEnabledOption(
	'font', 'body',
	false, ['family' => 'Open Sans', 'color' => '', 'size' => 14, 'line_height' => 28],
	_x('Main', 'font', 'everything'), __('Custom', 'everything'), '',
	['tag' => 'body, input, select, textarea', 'line_height_unit' => 'px']
);

$font->addEnabledOption(
	'font', 'content',
	false, ['family' => 'Open Sans', 'color' => '', 'size' => 14, 'line_height' => 28],
	__('Content', 'everything'), __('Custom', 'everything'), '',
	['tag' => '#content', 'line_height_unit' => 'px']
);

$font->addEnabledOption(
	'font', 'top_bar',
	false, ['family' => 'Montserrat', 'color' => '', 'size' => 12, 'styles' => []],
	__('Top bar', 'everything'), __('Custom', 'everything'), '',
	['tag' => '#top-bar']
);
$font->addEnabledOption(
	'font', 'logo',
	false, ['family' => 'Montserrat', 'color' => '', 'size' => 29, 'line_height' => 30, 'styles' => ['bold']],
	__('Logo', 'everything'), __('Custom', 'everything'), '',
	['tag' => '#logo a, #logo a:hover', 'line_height_unit' => 'px']
);

$nav = $font->addGroup('nav', __('Navigation', 'everything'));
	$nav->addEnabledOption(
		'font', 'main',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 14, 'styles' => []],
		 _x('Main', 'navigation', 'everything'), __('Custom', 'everything'), '',
		['tag' => ['.nav-menu.main ul, .nav-menu.main li:not(.current) > a:not(:hover), #mobile-section-toggle, .mobile-nav-menu.main ul, .mobile-nav-menu.main li:not(.current) > a:not(:hover)', '.lt-ie9 .nav-menu.main li > a, .lt-ie9 .mobile-nav-menu.main li > a']]
	);
	$nav->addEnabledOption(
		'font', 'secondary',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 12, 'styles' => []],
		_x('Secondary', 'navigation', 'everything'), __('Custom', 'everything'), '',
		['tag' => ['.nav-menu.secondary ul, .nav-menu.secondary li:not(.current) > a:not(:hover)', '.lt-ie9 .nav-menu.secondary li > a']]
	);

$headline = $font->addGroup('headline', __('Page headline', 'everything'));
	$headline->addEnabledOption(
		'font', 'title',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 18, 'styles' => []],
		__('Title', 'everything'), __('Custom', 'everything'), '',
		['tag' => '#headline h1']
	);
	$headline->addEnabledOption(
		'font', 'breadcrumbs',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 11, 'styles' => []],
		__('Breadcrumbs', 'everything'), __('Custom', 'everything'), '',
		['tag' => '#headline .breadcrumbs']
	);

$widget_title = $font->addGroup('widget_title', __('Widget title', 'everything'));
	$widget_title->addEnabledOption(
		'font', 'top',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 18, 'line_height' => 140.0, 'styles' => []],
		__('In sidebar', 'everything'), __('Custom', 'everything'), '',
		['tag' => '#content .widget > .title']
	);
	$widget_title->addEnabledOption(
		'font', 'bottom',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 18, 'line_height' => 140.0, 'styles' => []],
		__('In footer', 'everything'), __('Custom', 'everything'), '',
		['tag' => '#footer .widget > .title']
	);

$post = $font->addGroup('post', __('Post/page', 'everything'));
	$post->addEnabledOption(
		'font', 'title',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 22, 'line_height' => 130.0, 'styles' => []],
		__('Title', 'everything'), __('Custom', 'everything'), '',
		['tag' => '.post .title, h1.page-title, .product h1.product_title']
	);
	$post->addEnabledOption(
		'font', 'meta',
		false, ['family' => 'Open Sans', 'color' => '', 'size' => 11, 'line_height' => 24, 'styles' => []],
		__('Meta', 'everything'), __('Custom', 'everything'), '',
		['tag' => '.meta:not(.social)', 'line_height_unit' => 'px']
	);

$h = $font->addGroup('h', __('Headlines', 'everything'));
	$h->addEnabledOption(
		'font', 'h1',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 22, 'line_height' => 130.0, 'styles' => []],
		'H1', __('Custom', 'everything'), '',
		['tag' => 'h1']
	);
	$h->addEnabledOption(
		'font', 'h2',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 18, 'line_height' => 140.0, 'styles' => []],
		'H2', __('Custom', 'everything'), '',
		['tag' => 'h2']
	);
	$h->addEnabledOption(
		'font', 'h3',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 15, 'line_height' => 160.0, 'styles' => []],
		'H3', __('Custom', 'everything'), '',
		['tag' => 'h3']
	);
	$h->addEnabledOption(
		'font', 'h4',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 14, 'line_height' => 160.0, 'styles' => ['bold']],
		'H4', __('Custom', 'everything'), '',
		['tag' => 'h4']
	);
	$h->addEnabledOption(
		'font', 'h5',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 12, 'line_height' => 160.0, 'styles' => ['bold']],
		'H5', __('Custom', 'everything'), '',
		['tag' => 'h5']
	);
	$h->addEnabledOption(
		'font', 'h6',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 12, 'line_height' => 160.0, 'styles' => ['bold']],
		'H6', __('Custom', 'everything'), '',
		['tag' => 'h6']
	);

$button = $font->addGroup('button', __('Buttons', 'everything'));
	$button->addEnabledOption(
		'font', 'small',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 14, 'styles' => ['bold']],
		__('Small', 'everything'), __('Custom', 'everything'), '',
		['tag' => 'input[type="submit"].small, input[type="reset"].small, input[type="button"].small, button.small, .button.small']
	);
	$button->addEnabledOption(
		'font', 'normal',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 14, 'styles' => ['bold']],
		__('Normal', 'everything'), __('Custom', 'everything'), '',
		['tag' => 'input[type="submit"]:not(.small):not(.big):not(.huge), input[type="reset"]:not(.small):not(.big):not(.huge), input[type="button"]:not(.small):not(.big):not(.huge), button:not(.small):not(.big):not(.huge), .button:not(.small):not(.big):not(.huge)']
	);
	$button->addEnabledOption(
		'font', 'big',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 18, 'styles' => ['bold']],
		__('Big', 'everything'), __('Custom', 'everything'), '',
		['tag' => 'input[type="submit"].big, input[type="reset"].big, input[type="button"].big, button.big, .button.big']
	);
	$button->addEnabledOption(
		'font', 'huge',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 36, 'styles' => ['bold']],
		__('Huge', 'everything'), __('Custom', 'everything'), '',
		['tag' => 'input[type="submit"].huge, input[type="reset"].huge, input[type="button"].huge, button.huge, .button.huge']
	);

$other = $font->addGroup('other', __('Other', 'everything'));
	$other->addEnabledOption(
		'font', 'hr',
		false, ['family' => 'Montserrat', 'color' => '', 'size' => 14, 'styles' => ['bold']],
		__('Horizontal line', 'everything'), __('Custom', 'everything'), '',
		['tag' => '.hr h4']
	);
	$other->addEnabledOption(
		'font', 'quote',
		false, ['family' => 'Open Sans', 'color' => '', 'size' => 18, 'line_height' => 166.0, 'styles' => ['italic']],
		__('Quote', 'everything'), __('Custom', 'everything'), '',
		['tag' => 'blockquote']
	);
	$other->addEnabledOption(
		'font', 'message',
		false, ['family' => 'Open Sans', 'color' => '', 'size' => 15, 'line_height' => 160.0, 'styles' => ['italic']],
		__('Message box', 'everything'), __('Custom', 'everything'), '',
		['tag' => '.message']
	);
	$other->addEnabledOption(
		'font', 'code',
		false, ['family' => 'Lucida Console, Monaco, monospace', 'color' => '', 'size' => 11, 'line_height' => 20, 'styles' => []],
		__('Code', 'everything'), __('Custom', 'everything'), '',
		['tag' => 'pre', 'line_height_unit' => 'px']
	);

$font->addEnabledOption(
	'font', 'footer',
	false, ['family' => 'Open Sans', 'color' => '', 'size' => 13, 'line_height' => 24, 'styles' => []],
	__('Footer', 'everything'), __('Custom', 'everything'), '',
	['tag' => '#footer .widget', 'line_height_unit' => 'px']
);

$font->addEnabledOption(
	'font', 'end_note',
	false, ['family' => 'Montserrat', 'color' => '', 'size' => 12, 'styles' => []],
	__('End note', 'everything'), __('Custom', 'everything'), '',
	['tag' => '#end-note']
);

$font->addOption('collection', 'custom', ['id' => __('New font', 'everything'), 'family' => 'Open Sans', 'color' => '', 'size' => 13, 'line_height' => 150.0], __('Custom', 'everything'), '', ['type' => 'custom_font', 'unique_index' => true]);

/*
 * Post
 */
$post = $theme_options->addGroup('post', __('Posts', 'everything'));

$thumbnail = $post->addGroup('thumbnail', __('Featured image', 'everything'));
	$thumbnail->addOption('group', 'align', 'left', __('Align', 'everything'), '', ['options' => [
		'left'  => __('Left', 'everything'),
		'right' => __('Right', 'everything')
	]]);
	$thumbnail->addOption('size', 'size', ['width' => 238, 'height' => 238], __('Size', 'everything'), '', ['min' => 20, 'max' => 1000]);

$post->addOption('boolean', 'hide_icons', false, __('Format posts icons', 'everything'), '', ['caption' => __('Hide post format icon', 'everything')]);

$post->addOption('boolean', 'hide_title', false, __('Title', 'everything'), '', ['caption' => __('Hide title in content area', 'everything')]);

$readmore = $post->addGroup('readmore', __('Read more', 'everything'));
	$readmore->addOption('text', 'phrase', __('Read more', 'everything'), __('Phrase', 'everything'));
	$readmore->addOption('image_select', 'icon', 'arrow-line-right', __('Icon', 'everything'), '', ['required' => false, 'options' => function () { return ['' => ''] + ImageSelect::cssToOptions('data/img/icons/icons.css'); }, 'font_path' => Everything::ICON_FONT_PATH]);

$post->addOption('boolean', 'strip_teaser', false, __('Teaser', 'everything'), '', ['caption' => __('Hide content before the more tag inside post', 'everything')]);

$post->addOption('boolean', 'author_bio', false, __('Author details', 'everything'), '', ['caption' => __('Show author details inside post', 'everything')]);

$meta = $post->addGroup('meta', __('Meta', 'everything'));
	$before = $meta->addGroup('before', __('Before post', 'everything'));
		$this->addPostMetaOptions($before, true, 'date')->multiple = false;
	$list = $meta->addGroup('list', __('On posts list', 'everything'));
		$this->addPostMetaOptions($list, true, ['date', 'comments', 'categories']);
	$single = $meta->addGroup('single', __('Inside post', 'everything'));
		$this->addPostMetaOptions($single, true, ['date', 'comments', 'categories']);

$social_buttons = $post->addGroup('social_buttons', __('Social buttons', 'everything'));
	$list = $social_buttons->addGroup('list', __('On posts list', 'everything'));
		$this->addSocialButtonsOptions($list, false, ['facebook', 'twitter', 'googleplus']);
	$single = $social_buttons->addGroup('single', __('Inside post', 'everything'));
		$this->addSocialButtonsOptions($single, true, ['facebook', 'twitter', 'googleplus']);

$post->addOption('boolean', 'comments', true, __('Comments', 'everything'), '', ['caption' => __('Allow comments', 'everything')]);

/*
 * Format posts
 */
$format_posts = $theme_options->addGroup('format_posts', __('Format posts', 'everything'));

$standard = $format_posts->addGroup('standard', __('Standard post', 'everything'));
	$standard->addOption('group', 'thumbnail', ['list'], __('Show featured image', 'everything'), '', ['options' => [
		'list'   => __('On posts list', 'everything'),
		'single' => __('Inside post', 'everything')
	], 'multiple' => true]);
	$standard->addOption('group', 'content', 'excerpt_content', __('Content on posts list', 'everything'), __('Regular content means everything before the "Read more" tag.', 'everything'), ['options' => [
		'content'         => __('Regular content', 'everything'),
		'excerpt_content' => __('Excerpt or regular content', 'everything'),
		'excerpt'         => __('Excerpt', 'everything'),
		''                => __('None', 'everything')
	]]);

$aside = $format_posts->addGroup('aside', __('Aside post', 'everything'));
	$aside->addOption('group', 'thumbnail', ['list'], __('Show featured image', 'everything'), '', ['options' => [
		'list'   => __('On posts list', 'everything'),
		'single' => __('Inside post', 'everything')
	], 'multiple' => true]);
	$aside->addOption('group', 'content', 'excerpt_content', __('Content on posts list', 'everything'), '', ['options' => [
		'content'         => __('Regular content', 'everything'),
		'excerpt_content' => __('Excerpt or regular content', 'everything'),
		'excerpt'         => __('Excerpt', 'everything'),
		''                => __('None', 'everything')
	]]);

$audio = $format_posts->addGroup('audio', __('Audio post', 'everything'));
	$audio->addOption('group', 'thumbnail', [], __('Show featured image', 'everything'), '', ['options' => [
		'list'   => __('On posts list', 'everything'),
		'single' => __('Inside post', 'everything')
	], 'multiple' => true]);
	$audio->addOption('group', 'content', 'excerpt_content', __('Content on posts list', 'everything'), '', ['options' => [
		'content'         => __('Regular content', 'everything'),
		'excerpt_content' => __('Excerpt or regular content', 'everything'),
		'excerpt'         => __('Excerpt', 'everything'),
		''                => __('None', 'everything')
	]]);

$chat = $format_posts->addGroup('chat', __('Chat post', 'everything'));
	$chat->addOption('group', 'thumbnail', [], __('Show featured image', 'everything'), '', ['options' => [
		'list'   => __('On posts list', 'everything'),
		'single' => __('Inside post', 'everything')
	], 'multiple' => true]);
	$chat->addOption('group', 'content', 'excerpt_content', __('Content on posts list', 'everything'), '', ['options' => [
		'content'         => __('Regular content', 'everything'),
		'excerpt_content' => __('Excerpt or regular content', 'everything'),
		'excerpt'         => __('Excerpt', 'everything'),
		''                => __('None', 'everything')
	]]);

$gallery = $format_posts->addGroup('gallery', __('Gallery post', 'everything'));
	$gallery->addOption('group', 'thumbnail', [], __('Show featured image', 'everything'), '', ['options' => [
		'list'   => __('On posts list', 'everything'),
		'single' => __('Inside post', 'everything')
	], 'multiple' => true]);
	$gallery->addOption('group', 'content', 'excerpt_content', __('Content on posts list', 'everything'), '', ['options' => [
		'content'         => __('Regular content', 'everything'),
		'excerpt_content' => __('Excerpt or regular content', 'everything'),
		'excerpt'         => __('Excerpt', 'everything'),
		''                => __('None', 'everything')
	]]);

$image = $format_posts->addGroup('image', __('Image post', 'everything'));
	$image->addOption('group', 'thumbnail', ['list', 'single'], __('Show featured image', 'everything'), '', ['options' => [
		'list'   => __('On posts list', 'everything'),
		'single' => __('Inside post', 'everything')
	], 'multiple' => true]);
	$image->addOption('group', 'link', 'fancybox', __('Featured image click action', 'everything'), __('Click action refers to posts list only. Inside posts, clicked featured images always open in Fancybox window.', 'everything'), ['options' => [
		'post'     => __('Go to post', 'everything'),
		'fancybox' => __('Open image in Fancybox', 'everything')
	]]);
	$image->addOption('group', 'content', 'excerpt_content', __('Content on posts list', 'everything'), '', ['options' => [
		'content'         => __('Regular content', 'everything'),
		'excerpt_content' => __('Excerpt or regular content', 'everything'),
		'excerpt'         => __('Excerpt', 'everything'),
		''                => __('None', 'everything')
	]]);

$link = $format_posts->addGroup('link', __('Link post', 'everything'));
	$link->addOption('group', 'thumbnail', [], __('Show featured image', 'everything'), '', ['options' => [
		'list'   => __('On posts list', 'everything'),
		'single' => __('Inside post', 'everything')
	], 'multiple' => true]);
	$link->addOption('group', 'content', 'excerpt_content', __('Content on posts list', 'everything'), '', ['options' => [
		'content'         => __('Regular content', 'everything'),
		'excerpt_content' => __('Excerpt or regular content', 'everything'),
		'excerpt'         => __('Excerpt', 'everything'),
		''                => __('None', 'everything')
	]]);

$quote = $format_posts->addGroup('quote', __('Quote post', 'everything'));
	$quote->addOption('group', 'thumbnail', [], __('Show featured image', 'everything'), '', ['options' => [
		'list'   => __('On posts list', 'everything'),
		'single' => __('Inside post', 'everything')
	], 'multiple' => true]);
	$quote->addOption('group', 'content', 'excerpt_content', __('Content on posts list', 'everything'), '', ['options' => [
		'content'         => __('Regular content', 'everything'),
		'excerpt_content' => __('Excerpt or regular content', 'everything'),
		'excerpt'         => __('Excerpt', 'everything'),
		''                => __('None', 'everything')
	]]);

$status = $format_posts->addGroup('status', __('Status post', 'everything'));
	$status->addOption('group', 'thumbnail', [], __('Show featured image', 'everything'), '', ['options' => [
		'list'   => __('On posts list', 'everything'),
		'single' => __('Inside post', 'everything')
	], 'multiple' => true]);
	$status->addOption('group', 'content', 'excerpt_content', __('Content on posts list', 'everything'), '', ['options' => [
		'content'         => __('Regular content', 'everything'),
		'excerpt_content' => __('Excerpt or regular content', 'everything'),
		'excerpt'         => __('Excerpt', 'everything'),
		''                => __('None', 'everything')
	]]);

$video = $format_posts->addGroup('video', __('Video post', 'everything'));
	$video->addOption('group', 'thumbnail', [], __('Show featured image', 'everything'), '', ['options' => [
		'list'   => __('On posts list', 'everything'),
		'single' => __('Inside post', 'everything')
	], 'multiple' => true]);
	$video->addOption('group', 'content', 'excerpt_content', __('Content on posts list', 'everything'), '', ['options' => [
		'content'         => __('Regular content', 'everything'),
		'excerpt_content' => __('Excerpt or regular content', 'everything'),
		'excerpt'         => __('Excerpt', 'everything'),
		''                => __('None', 'everything')
	]]);

/*
 * Page
 */
$page = $theme_options->addGroup('page', __('Pages', 'everything'));

$page->addOption('boolean', 'hide_title', false, __('Title', 'everything'), '', ['caption' => __('Hide title in content area', 'everything')]);

$page->addOption('boolean', 'author_bio', false, __('Author details', 'everything'), '', ['caption' => __('Show author details', 'everything')]);

$meta = $page->addGroup('meta', __('Meta', 'everything'));
	$this->addMetaOptions($meta, false, ['author', 'permalink']);

$social_buttons = $page->addGroup('social_buttons', __('Social buttons', 'everything'));
	$this->addSocialButtonsOptions($social_buttons, true, ['facebook', 'twitter', 'googleplus']);

$page->addOption('boolean', 'comments', true, __('Comments', 'everything'), '', ['caption' => __('Allow comments', 'everything')]);

$page->addOption('boolean', 'inherit_parent', true, __('Layout options', 'everything'), '', ['caption' => __('Inherit parent page layout options in child pages', 'everything')]);

/*
 * Attachment
 */
$attachment = $theme_options->addGroup('attachment', __('Attachment pages', 'everything'));

$attachment->addOption('boolean', 'hide_title', false, __('Title', 'everything'), '', ['caption' => __('Hide title in content area', 'everything')]);

$attachment->addOption('boolean', 'author_bio', false, __('Author details', 'everything'), '', ['caption' => __('Show author details', 'everything')]);

$meta = $attachment->addGroup('meta', __('Meta', 'everything'));
	$this->addMetaOptions($meta, false, ['date_time', 'permalink']);

$social_buttons = $attachment->addGroup('social_buttons', __('Social buttons', 'everything'));
	$this->addSocialButtonsOptions($social_buttons, false, ['facebook', 'twitter', 'googleplus']);

$attachment->addOption('boolean', 'comments', false, __('Comments', 'everything'), '', ['caption' => __('Allow comments', 'everything')]);

/*
 * Gallery
 */
$gallery = $theme_options->addGroup('gallery', __('Galleries', 'everything'));

$gallery->addOption('codeline', 'slug', 'gallery', __('Slug', 'everything'), __('For the changes to take effect, go to Settings/Permalinks.', 'everything'), ['required' => true, 'allowed_chars' => '-_a-zA-Z0-9']);

$gallery->addOption('boolean', 'hide_title', false, __('Title', 'everything'), '', ['caption' => __('Hide title in content area', 'everything')]);

$gallery->addOption('boolean', 'author_bio', false, __('Author details', 'everything'), '', ['caption' => __('Show author details', 'everything')]);

$meta = $gallery->addGroup('meta', __('Meta', 'everything'));
	$this->addMetaOptions($meta, false, ['author', 'permalink']);

$social_buttons = $gallery->addGroup('social_buttons', __('Social buttons', 'everything'));
	$this->addSocialButtonsOptions($social_buttons, true, ['facebook', 'twitter', 'googleplus']);

$gallery->addOption('boolean', 'comments', true, __('Comments', 'everything'), '', ['caption' => __('Allow comments', 'everything')]);

/*
 * Portfolio
 */
$portfolio = $theme_options->addGroup('portfolio', __('Portfolios', 'everything'));

$portfolio->addOption('codeline', 'slug', 'portfolio', __('Slug', 'everything'), __('For the changes to take effect, go to Settings/Permalinks.', 'everything'), ['required' => true, 'allowed_chars' => '-_a-zA-Z0-9']);

$archive = $portfolio->addGroup('archive', __('Archive layout', 'everything'));
	$archive->addOption('number', 'count', 12, __('Number of items per page', 'everything'), '', ['min' => 1]);
	$archive->addOption('number', 'columns', 4, __('Number of columns', 'everything'), '', ['min' => 1, 'max' => 4]);
	$archive->addOption('boolean', 'title', true, __('Title', 'everything'), '', ['caption' => __('Show title', 'everything')]);
	$content = $archive->addGroup('content', __('Content', 'everything'));
		$visible = $content->addOption('boolean', 'visible', true, '', '', ['caption' => __('Show content', 'everything')]);
		$content->addOption('group', 'content', 'excerpt_content', '', __('Regular content means everything before the "Read more" tag.', 'everything'), ['options' => [
			'content'         => __('Regular content', 'everything'),
			'excerpt_content' => __('Excerpt or regular content', 'everything'),
			'excerpt'         => __('Excerpt', 'everything')
		], 'indent' => true, 'owner' => $visible]);
	$taxonomy = $archive->addGroup('taxonomy', __('Taxonomy', 'everything'));
		$visible = $taxonomy->addOption('boolean', 'visible', true, '', '', ['caption' => __('Show taxonomies', 'everything')]);
		$taxonomy->addOption('select', 'taxonomy', 'tag', '', '', ['options' => [
			'category' => __('Categories', 'everything'),
			'tag'      => __('Tags', 'everything')
		], 'indent' => true, 'owner' => $visible]);

$portfolio->addOption('boolean', 'hide_title', false, __('Title', 'everything'), '', ['caption' => __('Hide title in content area', 'everything')]);

$readmore = $portfolio->addGroup('readmore', __('Read more', 'everything'));
	$readmore->addOption('text', 'phrase', __('Read more', 'everything'), __('Phrase', 'everything'));
	$readmore->addOption('image_select', 'icon', 'arrow-line-right', __('Icon', 'everything'), '', ['required' => false, 'options' => function () { return ['' => ''] + ImageSelect::cssToOptions('data/img/icons/icons.css'); }, 'font_path' => Everything::ICON_FONT_PATH]);

$portfolio->addOption('boolean', 'strip_teaser', false, __('Teaser', 'everything'), '', ['caption' => __('Hide content before the more tag inside portfolio page', 'everything')]);

$portfolio->addOption('boolean', 'author_bio', false, __('Author details', 'everything'), '', ['caption' => __('Show author details', 'everything')]);

$meta = $portfolio->addGroup('meta', __('Meta', 'everything'));
	$this->addPostMetaOptions($meta, true, ['tags']);

$social_buttons = $portfolio->addGroup('social_buttons', __('Social buttons', 'everything'));
	$this->addSocialButtonsOptions($social_buttons, true, ['facebook', 'twitter', 'googleplus']);

$portfolio->addOption('boolean', 'comments', true, __('Comments', 'everything'), '', ['caption' => __('Allow comments', 'everything')]);

$portfolio->addOption('boolean', 'inherit_parent', false, __('Layout options', 'everything'), '', ['caption' => __('Inherit parent portfolio layout options in child portfolios', 'everything')]);

/*
 * WooCommerce
 */
$woocommerce = $theme_options->addGroup('woocommerce', __('WooCommerce', 'everything'));
$woocommerce->included = self::isPluginActive('woocommerce');

$shop = $woocommerce->addGroup('shop', __('Shop', 'everything'));
	$shop->addOption('number', 'columns', 4, __('Columns', 'everything'), '', ['min' => 1, 'max' => 8]);
	$shop->addOption('number', 'per_page', 8, __('Products per page', 'everything'), '', ['min' => 1]);
	$shop->addOption('select', 'pagination', 'numbers_navigation', __('Pagination', 'everything'), '', ['options' => [
		'numbers'            => __('Numbers', 'everything'),
		'numbers_navigation' => __('Numbers + navigation', 'everything')
	]]);
	$shop->addOption('select', 'image_hover', 'image', __('Images hover effect', 'everything'), '', ['options' => [
		'inherit'   => __('Inherit', 'everything'),
		''          => __('None', 'everything'),
		'zoom'      => __('Default', 'everything'),
		'grayscale' => __('Grayscale', 'everything'),
		'image'     => __('Second gallery image', 'everything')
	]]);

$product = $woocommerce->addGroup('product', __('Product', 'everything'));
	$product->addOption('group', 'image_size', '12_12', __('Image &amp; gallery width', 'everything'), '', ['options' => [
		'14_34' => '25%',
		'13_23' => '33%',
		'12_12' => '50%'
	]]);
	$product->addOption('number', 'thumbnails_columns', 3, __('Gallery thumbnails columns', 'everything'), '', ['min' => 1, 'max' => 6]);
	$product->addOption('group', 'captions', 'title', __('Gallery captions', 'everything'), '', ['options' => [
		''              => __('None', 'everything'),
		'title'         => __('Image title', 'everything'),
		'caption'       => __('Image caption', 'everything'),
		'caption_title' => __('Image caption or title', 'everything')
	]]);
	$product->addOption('boolean', 'brands', self::isPluginActive('woocommerce-brands'), __('Brand', 'everything'), '', ['caption' => __('Show brand description', 'everything'), 'disabled' => !self::isPluginActive('woocommerce-brands')]);
	$social_buttons = $product->addGroup('social_buttons', __('Social buttons', 'everything'));
		$this->addSocialButtonsOptions($social_buttons, false, ['facebook', 'twitter', 'googleplus']);
	$meta = $product->addGroup('meta', __('Meta', 'everything'));
		$visible = $meta->addOption('boolean', 'visible', true, '', '', ['caption' => __('Visible', 'everything')]);
		$meta->addOption('group', 'items', self::isPluginActive('woocommerce-brands') ? ['sku', 'categories', 'tags', 'brands'] : ['sku', 'categories', 'tags'], '', '', ['options' => [
			'sku'        => __('SKU', 'everything'),
			'categories' => __('Categories', 'everything'),
			'tags'       => __('Tags', 'everything'),
			'brands'     => __('Brands', 'everything'),
		], 'disabled' => self::isPluginActive('woocommerce-brands') ? [] : ['brands'], 'indent' => true, 'multiple' => true, 'sortable' => true, 'owner' => $visible]);

$related_products = $woocommerce->addGroup('related_products', __('Related products', 'everything'));
	$related_products->addOption('number', 'total', 4, __('Products', 'everything'), '', ['min' => 0]);
	$related_products->addOption('number', 'columns', 4, __('Columns', 'everything'), '', ['min' => 1, 'max' => 8]);

$cross_sells = $woocommerce->addGroup('cross_sells', __('Cross sells', 'everything'));
	$cross_sells->addOption('number', 'total', 4, __('Products', 'everything'), '', ['min' => 0]);
	$cross_sells->addOption('number', 'columns', 4, __('Columns', 'everything'), '', ['min' => 1, 'max' => 8]);

$cart = $woocommerce->addGroup('cart', __('Cart icon', 'everything'));
	$cart->addOption('image_select', 'icon', 'cart-1', __('Image', 'everything'), '', ['options' => array_intersect_key(
		ImageSelect::cssToOptions('data/img/icons/icons.css'),
		array_flip(['cart-1', 'cart-2', 'cart-3', 'bag', 'bag-1', 'bag-2', 'bag-3', 'bag-4', 'basket'])
	), 'font_path' => Everything::ICON_FONT_PATH, 'style' => 'horizontal']);
	$cart->addOption('color', 'color', '', __('Color', 'everything'), '', ['required' => false, 'placeholder' => __('default', 'everything')]);

$onsale = $woocommerce->addGroup('onsale', __('Sale label style', 'everything'));
	$custom = $onsale->addOption('boolean', 'custom', false, '', '', ['caption' => __('Custom', 'everything')]);
	$onsale->addOption('color', 'background', '#2d4b7e', __('Background', 'everything'), '', ['owner' => $custom, 'indent' => true]);
	$onsale->addOption('color', 'color', '#ffffff', __('Color', 'everything'), '', ['owner' => $custom, 'indent' => true]);

$rating = $woocommerce->addGroup('rating', __('Ratings style', 'everything'));
$rating->included = Func::stringToBool(get_option('woocommerce_enable_review_rating'));
	$custom = $rating->addOption('boolean', 'custom', false, '', '', ['caption' => __('Custom', 'everything')]);
	$rating->addOption('color', 'color', '#ffba00', __('Color', 'everything'), '', ['owner' => $custom, 'indent' => true]);

/*
 * Not found
 */
$not_found = $theme_options->addGroup('not_found', __('404 page', 'everything'));

$default = sprintf(
	"<h2>%s</h2>\n%s\n\n[search]",
	__('Are you lost?', 'everything'),
	sprintf(__('This is 404 page - it seems you\'ve encountered a dead link or missing page. You can use search form below to find what you\'re looking for or go to a <a href="%s">homepage</a>.', 'everything'), esc_url(home_url('/')))
);
$not_found->addOption('editor', 'content', $default, __('Content', 'everything'));

/*
 * Contact form
 */
$this->addThemeFeature('option-contact-form');

/*
 * Advanced
 */
$advanced = $theme_options->addGroup('advanced', __('Advanced', 'everything'));

$this->addThemeFeature(['option-custom-css', 'option-custom-js'], ['group' => $advanced]);

$advanced->addOption('boolean', 'chrome_fonts_fix', false, __('Google Fonts', 'everything'), __('This option is not recommended since Chrome version 37 and Opera version 25 were released.', 'everything'), ['caption' => __('Enhance fonts in Chrome and Opera browsers', 'everything')]);

/*
 * Other
 */
$other = $theme_options->addGroup('other', __('Other', 'everything'));

$this->addThemeFeature(['option-tracking-code', 'option-feed-url', 'option-ogp'], ['group' => $other]);

/*
 * Portfolio post options
 */
$portfolio_options = $this->getPostOptions('portfolio');

$portfolio = $portfolio_options->addGroup('portfolio', __('Portfolio', 'everything'), '', 'normal');
	$link = $portfolio->addGroup('link', __('Link to', 'everything'));
		$type = $link->addOption('group', 'type', 'internal', '', '', ['options' => [
			'internal' => __('Portfolio page', 'everything'),
			'external' => __('External page', 'everything')
		]]);
		$link->addOption('codeline', 'url', '', __('URL', 'everything'), '', ['owner' => $type, 'owner_value' => 'external', 'indent' => true]);

/*
 * Layout post options
 */
$nav_menus = Func::wpTermsList('nav_menu');
$this->foreachPostOptions(['post', 'page', 'gallery', 'portfolio', 'product'], function ($post_type, $post_options) use ($breadcrumbs, $nav_menus, $sidebar_options) {

	$layout = $post_options->addGroup('layout', __('Layout', 'everything'));

	$layout->addEnabledOption('background', 'background', false, Everything::to_('general/background/background')->default, __('Background', 'everything'), __('Custom', 'everything'), '', ['image_size' => 'full-hd']);

	$layout->addEnabledOption('banner', 'banner', false, Everything::to_('banner/content')->default, __('Banner', 'everything'), __('Custom', 'everything'));

	$nav = $layout->addGroup('nav_secondary', __('Secondary menu', 'everything'));
		$nav->addEnabledOption('select', 'upper', false, 'true', __('Upper', 'everything'), __('Custom', 'everything'), '', ['options' => [
			'true'    => __('Show', 'everything'),
			''        => __('Hide', 'everything')
		]+$nav_menus, 'groups' => [
			__('Custom menu', 'everything') => array_keys($nav_menus)
		]]);
		$nav->addEnabledOption('select', 'lower', false, 'true', __('Lower', 'everything'), __('Custom', 'everything'), '', ['options' => [
			'true'    => __('Show', 'everything'),
			''        => __('Hide', 'everything')
		]+$nav_menus, 'groups' => [
			__('Custom menu', 'everything') => array_keys($nav_menus)
		]]);

	$headline = $layout->addEnabledOption('group', 'headline', false, Everything::to_('nav/headline')->default, __('Headline', 'everything'), __('Custom', 'everything'), '', ['options' => [
		''            => __('Hide', 'everything'),
		'none'        => __('None (title only)', 'everything'),
		'breadcrumbs' => __('Breadcrumbs', 'everything'),
		'navigation'  => __('Navigation (if possible)', 'everything')
	], 'disabled' => !$breadcrumbs ? ['breadcrumbs'] : []]);

	$layout->addEnabledOption('layout', 'layout', false, Everything::to_('sidebar/layout')->default, __('Sidebar', 'everything'), __('Custom', 'everything'), '', ['options' => $sidebar_options]);

	if ($post_type != 'product') {
		$page = $layout->addGroup('page', __('Page', 'everything'));
			$page->addEnabledOption('group', 'hide_title', false, '', __('Title', 'everything'), __('Custom', 'everything'), '', ['options' => [
				''        => __('Show', 'everything'),
				'true'    => __('Hide', 'everything') // it's correct
			]]);
			$page->addEnabledOption('group', 'author_bio', false, 'true', __('Author details', 'everything'), __('Custom', 'everything'), '', ['options' => [
				'true'    => __('Show', 'everything'),
				''        => __('Hide', 'everything')
			]]);
			$page->addEnabledOption('group', 'meta', false, 'true', __('Meta', 'everything'), __('Custom', 'everything'), '', ['options' => [
				'true'    => __('Show', 'everything'),
				''        => __('Hide', 'everything')
			]]);
			$page->addEnabledOption('group', 'social_buttons', false, 'true', __('Social buttons', 'everything'), __('Custom', 'everything'), '', ['options' => [
				'true'    => __('Show', 'everything'),
				''        => __('Hide', 'everything')
			]]);
	}

});