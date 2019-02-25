<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */

use Drone\Func;
use Drone\HTML;
use Drone\Template;
use Drone\Theme;
use Drone\Options\Option\ConditionalTags;
use Drone\Options\Option\Font;
use Drone\Shortcodes\Shortcode;

require_once get_template_directory() . '/drone/drone.php'; // 5.8.2
require_once get_template_directory() . '/inc/class-tgm-plugin-activation.php'; // 2.6.1

/**
 * Everything
 */
class Everything extends Theme
{

	/**
	 * Default sidebar width
	 *
	 * @var int
	 */
	const DEFAULT_SIDEBAR_WIDTH = 308;

	/**
	 * Vector icons font path
	 *
	 * @var string
	 */
	const ICON_FONT_PATH = 'data/img/icons/icons.svg';

	/**
	 * LayerSlider WP plugin version
	 *
	 * @var string
	 */
	const LAYERSLIDER_VERSION = '6.7.6';

	/**
	 * Master Slider plugin version
	 *
	 * @var string
	 */
	const MASTERSLIDER_VERSION = '3.2.7';

	/**
	 * Slider Revolution plugin version
	 *
	 * @var string
	 */
	const REVSLIDER_VERSION = '5.4.7.4';

	/**
	 * Store page URL
	 *
	 * @var string
	 */
	const STORE_PAGE_URL = 'https://themeforest.net/item/everything-responsive-wordpress-theme/8661152/?ref=webberwebber';

	/**
	 * Headline used status
	 *
	 * @var bool
	 */
	public static $headline_used = false;

	/**
	 * Previous posts stack
	 *
	 * @var array
	 */
	public static $posts_stack = [];

	/**
	 * Max width
	 *
	 * @var int
	 */
	protected static $max_width = 1140;

	/**
	 * Constructor
	 */
	protected function __construct()
	{
		parent::__construct();
		$this->theme->id_ = $this->theme->id; // backward compatybility (child themes)
	}

	/**
	 * Add meta options
	 *
	 * @param  object $group
	 * @param  bool   $default_visible
	 * @param  array  $default_items
	 * @return object
	 */
	protected function addMetaOptions($group, $default_visible, $default_items)
	{

		$visible = $group->addOption('boolean', 'visible', $default_visible, '', '', ['caption' => __('Visible', 'everything')]);

		return $group->addOption('group', 'items', $default_items, '', '', ['options' => [
			'date_time'  => __('Date &amp time', 'everything'),
			'date'       => __('Date', 'everything'),
			'mod_date'   => __('Modification date', 'everything'),
			'time_diff'  => __('Relative time', 'everything'),
			'comments'   => __('Comments number', 'everything'),
			'author'     => __('Author', 'everything'),
			'permalink'  => __('Permalink', 'everything')
		], 'indent' => true, 'multiple' => true, 'sortable' => true, 'owner' => $visible]);

	}

	/**
	 * Add post meta options
	 *
	 * @param  object $group
	 * @param  bool   $default_visible
	 * @param  array  $default_items
	 * @return object
	 */
	protected function addPostMetaOptions($group, $default_visible, $default_items)
	{

		$visible = $group->addOption('boolean', 'visible', $default_visible, '', '', ['caption' => __('Visible', 'everything')]);

		return $group->addOption('group', 'items', $default_items, '', '', ['options' => [
			'date_time'  => __('Date &amp time', 'everything'),
			'date'       => __('Date', 'everything'),
			'mod_date'   => __('Modification date', 'everything'),
			'time_diff'  => __('Relative time', 'everything'),
			'comments'   => __('Comments number', 'everything'),
			'categories' => __('Categories', 'everything'),
			'tags'       => __('Tags', 'everything'),
			'author'     => __('Author', 'everything'),
			'permalink'  => __('Permalink', 'everything')
		], 'indent' => true, 'multiple' => true, 'sortable' => true, 'owner' => $visible]);

	}

	/**
	 * Add social buttons options
	 *
	 * @param  object $group
	 * @param  bool   $default_visible
	 * @param  array  $default_items
	 * @return object
	 */
	protected function addSocialButtonsOptions($group, $default_visible, $default_items)
	{

		$visible = $group->addOption('boolean', 'visible', $default_visible, '', '', ['caption' => __('Visible', 'everything')]);

		return $group->addOption('group', 'items', $default_items, '', '', ['options' => [
			'facebook'   => __('Facebook', 'everything'),
			'twitter'    => __('Twitter', 'everything'),
			'googleplus' => __('Google+', 'everything'),
			'linkedin'   => __('LinkedIn', 'everything'),
			'pinterest'  => __('Pinterest', 'everything')
		], 'indent' => true, 'multiple' => true, 'sortable' => true, 'owner' => $visible]);

	}

	/**
	 * Theme options compatybility
	 *
	 * @param array  $data
	 * @param string $version
	 */
	public function onThemeOptionsCompatybility(array &$data, $version)
	{

		// 1.7
		if (version_compare($version, '1.7-alpha-3') < 0) {

			$conditional_tags_migrate = function ($data, $sidebars_widgets = false) {
				foreach ($_ = $data as $tag => $value) {
					if ($sidebars_widgets) {
						if (!preg_match('/^footer-(?P<tag>.+)-(?P<i>[0-5])$/', $tag, $footer_sidebar)) {
							continue;
						}
						$tag = $footer_sidebar['tag'];
					}
					$new_tag = false;
					if (preg_match('/^(post_type_|term_|bbpress|woocommerce)/', $tag)) { // new format
						continue;
					}
					else if (in_array($tag, ['default', 'front_page', 'blog', 'search', '404'])) { // general
						continue;
					}
					else if (in_array($tag, ['forum', 'topic'])) { // bbpress
						$new_tag = 'bbpress_' . $tag;
					}
					else if (in_array($tag, ['shop', 'cart', 'checkout', 'order_received_page', 'account_page'])) { // woocommerce
						$new_tag = 'woocommerce_' . $tag;
					}
					else if (strpos($tag, 'template_') === 0) { // template
						if (!preg_match('/.\.php$/', $tag)) {
							foreach (array_keys(Everything::instance()->theme->get_page_templates()) as $template) {
								if ($tag == Func::stringID('template_' . preg_replace('/\.php$/i', '', $template), '_')) {
									$new_tag = 'template_' . preg_replace('/\.(php)$/i', '_\1', $template);
									break;
								}
							}
						}
					}
					else if (preg_match('/^[_a-z]+_[0-9]+$/', $tag)) { // taxonomy
						if (preg_match('/^(portfolio_(category|tag)|topic_tag)_/', $tag)) {
							$new_tag = 'term_' . preg_replace('/_/', '-', $tag, 1);
						} else {
							$new_tag = 'term_' . $tag;
						}
					}
					else if (preg_match('/^[_a-z]+$/', $tag)) { // post type
						$new_tag = 'post_type_' . $tag;
					}
					if ($new_tag !== false) {
						if ($sidebars_widgets) {
							$tag = $footer_sidebar[0];
							$new_tag = "footer-{$new_tag}-{$footer_sidebar['i']}";
						}
						unset($data[$tag]);
						$data[$new_tag] = $value;
					}
				}
				return $data;
			};

			if (isset($data['general']['layout']) && is_array($data['general']['layout'])) {
				$data['general']['layout'] = $conditional_tags_migrate($data['general']['layout']);
			}
			if (isset($data['general']['max_width']) && is_array($data['general']['max_width'])) {
				$data['general']['max_width'] = $conditional_tags_migrate($data['general']['max_width']);
			}
			if (isset($data['general']['background']['background']) && is_array($data['general']['background']['background'])) {
				$data['general']['background']['background'] = $conditional_tags_migrate($data['general']['background']['background']);
			}
			if (isset($data['banner']['content']) && is_array($data['banner']['content'])) {
				$data['banner']['content'] = $conditional_tags_migrate($data['banner']['content']);
			}
			if (isset($data['nav']['secondary']['upper']) && is_array($data['nav']['secondary']['upper'])) {
				$data['nav']['secondary']['upper'] = $conditional_tags_migrate($data['nav']['secondary']['upper']);
			}
			if (isset($data['nav']['secondary']['lower']) && is_array($data['nav']['secondary']['lower'])) {
				$data['nav']['secondary']['lower'] = $conditional_tags_migrate($data['nav']['secondary']['lower']);
			}
			if (isset($data['nav']['headline']) && is_array($data['nav']['headline'])) {
				$data['nav']['headline'] = $conditional_tags_migrate($data['nav']['headline']);
			}
			if (isset($data['sidebar']['layout']) && is_array($data['sidebar']['layout'])) {
				$data['sidebar']['layout'] = $conditional_tags_migrate($data['sidebar']['layout']);
			}
			if (isset($data['footer']['layout']) && is_array($data['footer']['layout'])) {
				$data['footer']['layout'] = $conditional_tags_migrate($data['footer']['layout']);
			}

			if (($sidebars_widgets = get_option('sidebars_widgets')) !== false && is_array($sidebars_widgets)) {
				$new_sidebars_widgets = $conditional_tags_migrate($sidebars_widgets, true);
				if ($sidebars_widgets !== $new_sidebars_widgets) {
					update_option('sidebars_widgets', $new_sidebars_widgets);
				}
			}

		}

		// 2.0
		if (version_compare($version, '2.0') < 0) {

			$data['page']['inherit_parent'] = false;

		}

		// 3.0
		if (version_compare($version, '3.0-alpha-1') < 0) {

			if (isset($data['general']['background']['background']) && is_array($data['general']['background']['background'])) {
				foreach ($data['general']['background']['background'] as &$background) {
					if (isset($background['image_ex']['image1x'])) {
						$background['image'] = $background['image_ex']['image1x'];
					}
				}
				unset($background);
			}

			if (isset($data['header']['logo']['image']['image1x'])) {
				$data['header']['logo']['image']['x1'] = $data['header']['logo']['image']['image1x'];
			}
			if (isset($data['header']['logo']['image']['image2x'])) {
				$data['header']['logo']['image']['x2'] = $data['header']['logo']['image']['image2x'];
			}

		}

	}

	/**
	 * Post options compatybility
	 *
	 * @param array  $data
	 * @param string $version
	 * @param string $post_type
	 */
	public function onPostOptionsCompatybility(array &$data, $version, $post_type)
	{

		// 3.0
		if (version_compare($version, '3.0-alpha-1') < 0) {

			if (isset($data['layout']['background']['background']['image_ex']['image1x'])) {
				$data['layout']['background']['background']['image'] = $data['layout']['background']['background']['image_ex']['image1x'];
			}

		}

	}

	/**
	 * Theme setup
	 *
	 * @see \Drone\Theme::onSetupTheme()
	 */
	protected function onSetupTheme()
	{

		// Theme features
		$this->addThemeFeature([
			'x-ua-compatible',
			'nav-menu-current-item',
			'comment-form-fields-reverse-order',
			'force-img-caption-shortcode-filter',
			'social-media-api'
		]);

		$this->addThemeFeature('default-site-icon', $this->getResourceURI('data/img/favicon/' . substr(preg_replace('/[^a-z]/', '', strtolower(get_bloginfo('name'))), 0, 1) . '.ico'));

		$this->addThemeFeature('inherit-parent-post-options', array_keys(Func::arrayContent([
			'page'      => self::to('page/inherit_parent'),
			'portfolio' => self::to('portfolio/inherit_parent')
		])));

		// Editor style
		add_editor_style('data/css/wp-editor' . (!$this->debug_mode ? '.min' : '') . '.css');

		// Menus
		register_nav_menus([
			'main-desktop'      => __('Main menu (desktop)', 'everything'),
			'main-mobile'       => __('Main menu (mobile)', 'everything'),
			'additional-mobile' => __('Additional menu (mobile)', 'everything'),
			'top-bar-desktop'   => __('Top bar menu (desktop)', 'everything'),
			'top-bar-mobile'    => __('Top bar menu (mobile)', 'everything'),
			'secondary-upper'   => __('Upper secondary menu', 'everything'),
			'secondary-lower'   => __('Lower secondary menu', 'everything')
		]);

		// Images
		add_theme_support('post-thumbnails');

		add_image_size('logo', 0, 60);

		$thumbnail_size = self::to('post/thumbnail/size');
		add_image_size('post-thumbnail', $thumbnail_size['width'], $thumbnail_size['height'], true);
		add_image_size('post-thumbnail-mini', 56, 56*2);
		add_image_size('post-thumbnail-mini-crop', 56, 56, true);

		add_image_size('column-1', 1090);
		add_image_size('column-2', 535);
		add_image_size('column-3', 350);
		add_image_size('column-4', 258);

		$max_width = self::to_('general/max_width')->value('default');
		add_image_size('max-width', $max_width, round($max_width*1.5));
		add_image_size('full-hd', 1920, 2880);

		$this->addThemeFeature('retina-image-size', [
			'post-thumbnail',
			'post-thumbnail-mini',
			'post-thumbnail-mini-crop',
			'shop_thumbnail',
			'shop_catalog',
			'shop_single'
		]);

		// Post formats
		add_theme_support('post-formats', [
			'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video'
		]);

		// Shortcodes
		$this->addThemeFeature([
			'shortcode-search',
			'shortcode-contact',
			'shortcode-page',
			'shortcode-no-format'
		]);

		// Classes
		Font::$always_used = ['Open Sans', 'Montserrat'];

		// bbPress
		if (self::isPluginActive('bbpress')) {
			$func = function ($show) { return is_bbpress() ? false : $show; };
			add_filter('everything_meta_display', $func, 20);
			add_filter('everything_social_buttons_display', $func, 20);
		}

		// Breadcrumb trail
		add_theme_support('breadcrumb-trail');

		// Captcha
		if (self::isPluginActive('captcha')) {
			if (has_action('comment_form_after_fields', 'cptch_comment_form_wp3')) {
				remove_action('comment_form_after_fields', 'cptch_comment_form_wp3', 1);
				remove_action('comment_form_logged_in_after', 'cptch_comment_form_wp3', 1);
				add_filter('comment_form_field_comment', function ($comment_field) {
					$captcha = Func::functionGetOutputBuffer('cptch_comment_form_wp3');
					$captcha = preg_replace('#<br( /)?>#', '', $captcha);
					return $comment_field . $captcha;
				});
			}
		}

		// WooCommerce
		if (self::isPluginActive('woocommerce')) {

			add_theme_support('woocommerce');

			remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
			remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
			remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
			remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);

			remove_action('woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10);
			remove_action('woocommerce_after_subcategory', 'woocommerce_template_loop_category_link_close', 10);
			remove_action('woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10);
			remove_action('woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10);

			remove_action('woocommerce_review_before', 'woocommerce_review_display_gravatar', 10);

			remove_action('woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10);
			remove_action('woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20);

		}

		// WooCommerce Brands
		if (self::isPluginActive('woocommerce-brands')) {
			remove_action('woocommerce_product_meta_end', [$GLOBALS['WC_Brands'], 'show_brand']);
		}

		// WPML
		if (self::isPluginActive('wpml')) {
			define('ICL_DONT_LOAD_LANGUAGE_SELECTOR_CSS', true);
		}

		// Illegal
		if (!is_admin() && self::isIllegal()) {
			self::to_('footer/end_note/right')->value = self::to_('footer/end_note/right')->default;
		}

	}

	/**
	 * Initialization
	 *
	 * @see \Drone\Theme::onInit()
	 */
	public function onInit()
	{

		// Gallery
 		register_post_type('gallery', apply_filters('everything_register_post_type_gallery_args', [
			'label'       => __('Galleries', 'everything'),
			'description' => __('Galleries', 'everything'),
			'public'      => true,
			'menu_icon'   => 'dashicons-images-alt2',
			'supports'    => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions'],
			'rewrite'     => ['slug' => self::to('gallery/slug')],
			'labels'      => [
				'name'               => __('Galleries', 'everything'),
				'singular_name'      => __('Gallery', 'everything'),
				'add_new'            => _x('Add New', 'gallery', 'everything'),
				'all_items'          => __('All Galleries', 'everything'),
				'add_new_item'       => __('Add New Gallery', 'everything'),
				'edit_item'          => __('Edit Gallery', 'everything'),
				'new_item'           => __('New Gallery', 'everything'),
				'view_item'          => __('View Gallery', 'everything'),
				'search_items'       => __('Search Galleries', 'everything'),
				'not_found'          => __('No Galleries found', 'everything'),
				'not_found_in_trash' => __('No Galleries found in Trash', 'everything'),
				'menu_name'          => __('Galleries', 'everything')
			]
		]));

 		// Portfolio
		register_post_type('portfolio', apply_filters('everything_register_post_type_portfolio_args', [
			'label'        => __('Portfolios', 'everything'),
			'description'  => __('Portfolios', 'everything'),
			'public'       => true,
			'menu_icon'    => 'dashicons-exerpt-view',
			'hierarchical' => true,
			'supports'     => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes'],
			'rewrite'      => ['slug' => self::to('portfolio/slug')],
			'labels'       => [
				'name'               => __('Portfolios', 'everything'),
				'singular_name'      => __('Portfolio', 'everything'),
				'add_new'            => _x('Add New', 'portfolio', 'everything'),
				'all_items'          => __('All Portfolios', 'everything'),
				'add_new_item'       => __('Add New Portfolio', 'everything'),
				'edit_item'          => __('Edit Portfolio', 'everything'),
				'new_item'           => __('New Portfolio', 'everything'),
				'view_item'          => __('View Portfolio', 'everything'),
				'search_items'       => __('Search Portfolios', 'everything'),
				'not_found'          => __('No Portfolios found', 'everything'),
				'not_found_in_trash' => __('No Portfolios found in Trash', 'everything'),
				'menu_name'          => __('Portfolios', 'everything')
			]
		]));
		register_taxonomy('portfolio-category', 'portfolio', apply_filters('everything_register_taxonomy_portfolio_category_args', [
			'label'        => __('Categories', 'everything'),
			'hierarchical' => true,
			'rewrite'      => ['slug' => self::to('portfolio/slug') . '-category']
		]));
		register_taxonomy('portfolio-tag', 'portfolio', apply_filters('everything_register_taxonomy_portfolio_tag_args', [
			'label'        => __('Tags', 'everything'),
			'hierarchical' => false,
			'rewrite'      => ['slug' => self::to('portfolio/slug') . '-tag']
		]));

	}

	/**
	 * Widgets initialization
	 *
	 * @see \Drone\Theme::onWidgetsInit()
	 */
	public function onWidgetsInit()
	{

		// Built-in sidebars
		foreach (self::to_('sidebar/list/builtin')->childs() as $id => $sidebar) {
			register_sidebar([
				'id'            => $id,
				'name'          => $sidebar->label,
				'before_widget' => '<section id="%1$s" class="section widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="title">',
				'after_title'   => '</h2>'
			]);
		}

		// Additional sidebars
		foreach (self::to('sidebar/list/additional') as $id => $sidebar) {
			register_sidebar([
				'id'            => $id,
				'name'          => $sidebar['id'],
				'before_widget' => '<section id="%1$s" class="section widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="title">',
				'after_title'   => '</h2>'
			]);
		}

		// Footer sidebars
		if (is_admin()) {
			$tags = ConditionalTags::getTagsList();
		}
		foreach (self::to('footer/layout') as $tag => $layout) {

			for ($i = 0; $i < count(self::stringToColumns($layout)); $i++) {

				if ($tag == 'default') {
					$name = sprintf(__('Footer column %d', 'everything'), $i+1);
				} else {
					$name = sprintf(__('(%1$s) Footer column %2$d', 'everything'), isset($tags[$tag]) ? $tags[$tag]['caption'] : '~' . $tag, $i+1);
				}

				register_sidebar([
					'name'          => $name,
					'id'            => "footer-{$tag}-{$i}",
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="title">',
					'after_title'   => '</h2>'
				]);

			}

		}

		// Widgets
		$this->addThemeFeature([
			'widget-unwrapped-text',
			'widget-contact',
			'widget-posts-list',
			'widget-twitter',
			'widget-flickr',
			'widget-instagram',
			'widget-facebook-like-box',
			'widget-page'
		]);

		// WooCommerce
		if (self::isPluginActive('woocommerce')) {

			require $this->template_dir . '/inc/woocommerce-widgets.php';

			foreach ([
				'WC_Widget_Best_Sellers',
				'WC_Widget_Cart',
				'WC_Widget_Featured_Products',
				'WC_Widget_Layered_Nav_Filters',
				'WC_Widget_Layered_Nav',
				'WC_Widget_Onsale',
				'WC_Widget_Price_Filter',
				'WC_Widget_Product_Categories',
				'WC_Widget_Product_Search',
				'WC_Widget_Product_Tag_Cloud',
				'WC_Widget_Products',
				'WC_Widget_Random_Products',
				'WC_Widget_Recent_Products',
				'WC_Widget_Recent_Reviews',
				'WC_Widget_Recently_Viewed',
				'WC_Widget_Top_Rated_Products'
			] as $class) {
				if (class_exists($class)) {
					unregister_widget($class);
					register_widget('Everything_' . $class);
				}
			}

		}

		// WooCommerce Brands
		if (self::isPluginActive('woocommerce-brands')) {

			require $this->template_dir . '/inc/woocommerce-brands-widgets.php';

			foreach ([
				'WC_Widget_Brand_Nav'
			] as $class) {
				if (class_exists($class)) {
					unregister_widget($class);
					register_widget('Everything_' . $class);
				}
			}

		}

	}

	/**
	 * @internal action: tgmpa_register
	 */
	public function actionTGMPARegister()
	{

		tgmpa(
			[
				[
				    'name'               => 'LayerSlider',
				    'slug'               => 'LayerSlider',
				    'source'             => $this->template_dir . '/plugins/layerslider.zip',
				    'required'           => false,
				    'version'            => self::LAYERSLIDER_VERSION,
				    'force_activation'   => false,
				    'force_deactivation' => true
				],
				[
				    'name'               => 'Master Slider',
				    'slug'               => 'masterslider',
				    'source'             => $this->template_dir . '/plugins/masterslider.zip',
				    'required'           => false,
				    'version'            => self::MASTERSLIDER_VERSION,
				    'force_activation'   => false,
				    'force_deactivation' => true
				],
				[
				    'name'               => 'Slider Revolution',
				    'slug'               => 'revslider',
				    'source'             => $this->template_dir . '/plugins/revslider.zip',
				    'required'           => false,
				    'version'            => self::REVSLIDER_VERSION,
				    'force_activation'   => false,
				    'force_deactivation' => true
				]
			],
			[
				'menu'        => $this->theme->id . '-install-plugins',
				'parent_slug' => $this->theme->id . '-general'
			]
		);

	}

	/**
	 * @internal action: wp_enqueue_scripts
	 */
	public function actionWPEnqueueScripts()
	{

		// Debug
		$this->beginMarker(__METHOD__);

		$min_sufix = !$this->debug_mode ? '.min' : '';
		$ver = $this->base_theme->version;

		// Main style
		wp_enqueue_style('everything-style', $this->template_uri . "/data/css/style{$min_sufix}.css", [], $ver);

		// Color scheme
		wp_enqueue_style('everything-scheme', $this->template_uri . '/data/css/' . self::to('general/scheme') . $min_sufix . '.css', [], $ver);

		// Responsive design
		if (self::to('general/responsive')) {
			wp_enqueue_style('everything-mobile', $this->template_uri . "/data/css/mobile{$min_sufix}.css", [], $ver, 'only screen and (max-width: 767px)');
		}

		// Stylesheet
		wp_enqueue_style('everything-stylesheet', get_stylesheet_uri());

		// Leading color
		$this->addDocumentStyle(sprintf(
<<<'EOS'
			a,
			a.alt:hover,
			.alt a:hover,
			h1 a:hover, h2 a:hover, h3 a:hover, h4 a:hover, h5 a:hover, h6 a:hover,
			.color,
			.toggles > div > h3:hover > i,
			.nav-menu a:hover,
			.nav-menu .current > a, .nav-menu .current > a:hover,
			.mobile-nav-menu a:hover,
			.mobile-nav-menu .current > a, .mobile-nav-menu .current > a:hover,
			.aside-nav-menu a:hover,
			.aside-nav-menu .current:not(.current-menu-parent):not(.current-menu-ancestor) > a, .aside-nav-menu .current:not(.current-menu-parent):not(.current-menu-ancestor) > a:hover {
				color: %1$s;
			}

			mark,
			.background-color,
			.sy-pager li.sy-active a {
				background-color: %1$s;
			}

			.zoom-hover > .zoom-hover-overlay {
				background-color: rgba(%2$s, 0.75);
			}

			blockquote.bar,
			.sticky:before {
				border-color: %1$s;
			}
EOS
			,
			self::to('general/color'),
			implode(', ', array_map('hexdec', str_split(substr(self::to('general/color'), 1), 2)))
		));

		// Comment reply
		if (is_singular() && comments_open() && get_option('thread_comments')) {
			wp_enqueue_script('comment-reply');
		}

		// Main script
		wp_enqueue_script('everything-script', $this->template_uri . "/data/js/everything{$min_sufix}.js", ['jquery'], $ver, true);

		// Configuration
		$this->addDocumentScript(sprintf('everythingConfig = %s;', json_encode([
			'fancyboxOptions' => self::to('site/image/fancybox_horizontal_fit_only') ? ['maxWidth' => '100%', 'fitToView' => false] : [],
			'zoomHoverIcons'  => array_map(function ($s) { return 'icon-' . $s; }, self::to_('site/hover_icons')->toArray()),
			'captions'        => ['bricksAllButton' => __('all', 'everything')]
		])));

		// Max. width style
		if (!self::to_('general/max_width')->isDefault()) {
			self::$max_width = self::to_('general/max_width')->value();
			$this->addDocumentStyle(sprintf(
<<<'EOS'
				.layout-boxed .outer-container, .container {
					max-width: %dpx;
				}
EOS
			, self::$max_width));
		}

		// Colors styles
		foreach (self::to_('color')->childs() as $name => $group) {
			if ($group->child('enabled')->value) {
				$color = $group->child($name);
				$css = str_replace('%color', $color->value, $color->tag);
				if (strpos($css, '%rgba') !== false) {
					$css = preg_replace_callback('/%rgba0([0-9]{2})/', function ($m) use ($color) {
						return Func::cssHexToRGBA($color->value, $m[1]/100);
					}, $css);
				}
				if (strpos($css, '%darken') !== false) {
					$hsl = Func::colorRGBToHSL(Func::cssColorToDec($color->value));
					$hsl[2] = max($hsl[2]-0.12, 0);
					$css = str_replace('%darken', Func::cssDecToColor(Func::colorHSLToRGB($hsl)), $css);
				}
				$this->addDocumentStyle($css);
			}
		}

		// Header style
		if (self::to_('header/style/settings')->value('floated')) {
			$this->addDocumentStyle(sprintf('#header:before { opacity: %.2f; }', self::to('header/style/opacity')/100));
		}

		// Content
		if (!is_null($background = self::io_('layout/background/background', 'general/background/background', '__hidden_ns', '__hidden'))) {
			if ($background instanceof ConditionalTags) {
				$background = $background->option();
			}
			if (!$background->option('opacity')->isDefault()) {
				$this->addDocumentStyle(sprintf(
<<<'EOS'
					.layout-boxed #content aside.aside .aside-nav-menu .current:not(.current-menu-parent):not(.current-menu-ancestor) > a:before {
						display: none;
					}
					#content:before {
						opacity: %.2f;
					}
EOS
				, $background->value('opacity')/100));
			}
		}

		// Fonts styles
		foreach (Font::instances() as $font) {
			if ($font->isVisible() && !is_null($font->tag)) {
				foreach ((array)$font->tag as $selector) {
					$this->addDocumentStyle($font->css($selector));
				}
			}
		}

		// MediaElement.js progress bar color
		$this->addDocumentStyle(sprintf(
<<<'EOS'
			.mejs-container .mejs-controls .mejs-time-rail .mejs-time-current {
				background-color: %s;
			}
EOS
		, self::to('general/color')));

		// Google Chrome fonts fix
		if (self::to('advanced/chrome_fonts_fix')) {
			$this->addDocumentStyle(
<<<'EOS'
				@media screen and (-webkit-min-device-pixel-ratio: 0) {
					* {
						-webkit-font-smoothing: antialiased;
						-webkit-text-stroke: 0.1pt;
					}
					h1, h2, h3, .button.big *, .button.huge *, #header * {
						-webkit-text-stroke: 0.2pt;
					}
				}
EOS
			);
		}

		// List widgets script
		if (is_active_widget(false, false, 'pages') ||
			is_active_widget(false, false, 'archives') ||
			is_active_widget(false, false, 'categories') ||
			is_active_widget(false, false, 'recent-posts') ||
			is_active_widget(false, false, 'recent-comments') ||
			is_active_widget(false, false, 'bbp_forums_widget') ||
			is_active_widget(false, false, 'bbp_replies_widget') ||
			is_active_widget(false, false, 'bbp_topics_widget') ||
			is_active_widget(false, false, 'bbp_views_widget')) {
			$this->addDocumentJQueryScript(
<<<'EOS'
				$('.widget_pages, .widget_archive, .widget_categories, .widget_recent_entries, .widget_recent_comments, .widget_display_forums, .widget_display_replies, .widget_display_topics, .widget_display_views').each(function() {
					$('ul', this).addClass('fancy alt');
					$('li', this).prepend($('<i />', {'class': 'icon-right-open'}));
					if ($(this).closest('#content').length > 0) {
						$('li > .icon-right-open', this).addClass('color');
					}
				});
EOS
			);
		}

		// Menu widgets script
		if (is_active_widget(false, false, 'meta') ||
			is_active_widget(false, false, 'nav_menu')) {
			$this->addDocumentJQueryScript(
<<<'EOS'
				$('.widget_meta, .widget_nav_menu').each(function() {
					if ($(this).is('#content .widget')) {
						$('> div:has(> ul)', this).replaceWith(function() { return $(this).contents(); });
						$('ul:first', this).wrap('<nav class="aside-nav-menu"></nav>');
					} else {
						$('ul', this).addClass('fancy alt');
						$('li', this).prepend($('<i />', {'class': 'icon-right-open'}));
					}
				});
EOS
			);
		}

		// Calendar widget
		if (is_active_widget(false, false, 'calendar')) {
			$this->addDocumentJQueryScript(
<<<'EOS'
				$('.widget_calendar #calendar_wrap > table').unwrap();
EOS
			);
		}

		// Tag cloud widget
		if (is_active_widget(false, false, 'tag_cloud')) {
			$this->addDocumentJQueryScript(
<<<'EOS'
				$('.widget_tag_cloud .tagcloud').wrapInner('<div />').find('a').addClass('button small').css('font-size', '');
EOS
			);
		}

		// bbPress
		if (self::isPluginActive('bbpress') && is_active_widget(false, false, 'bbp_replies_widget')) {
			$this->addDocumentJQueryScript(
<<<'EOS'
				$('.widget_display_replies li > div').addClass('small');
EOS
			);
		}

		// Disqus Comment System
		if (self::isPluginActive('disqus')) {
			$this->addDocumentJQueryScript(
<<<'EOS'
				$('#disqus_thread').addClass('section');
EOS
			);
		}

		// WooCommerce
		if (self::isPluginActive('woocommerce')) {
			if (!is_null($color = self::to('woocommerce/cart/color', '__default'))) {
				$this->addDocumentStyle(sprintf(
<<<'EOS'
					i.icon-woocommerce-cart {
						color: %s;
					}
EOS
					,
					$color
				));
			}
			$this->addDocumentStyle(sprintf(
<<<'EOS'
				.widget_price_filter .ui-slider .ui-slider-range,
				.widget_price_filter .ui-slider .ui-slider-handle {
					background-color: %s;
				}
EOS
				,
				self::to('general/color')
			));
			if (self::to('woocommerce/onsale/custom')) {
				$this->addDocumentStyle(sprintf(
<<<'EOS'
					.woocommerce .onsale,
					.woocommerce-page .onsale {
						background: %s;
						color: %s;
					}
EOS
					,
					self::to('woocommerce/onsale/background'),
					self::to('woocommerce/onsale/color')
				));
			}
			if (self::to('woocommerce/rating/custom')) {
				$this->addDocumentStyle(sprintf(
<<<'EOS'
					.woocommerce .rating i:not(.pad),
					.woocommerce-page .rating i:not(.pad) {
						color: %s;
					}
EOS
					,
					self::to('woocommerce/rating/color')
				));
			}
			$this->addDocumentJQueryScript(
<<<'EOS'

				$('.comment-form-rating p.stars').each(function() {
					var _this = this;
					$(this)
						.addClass('rating')
						.on('click', 'a', function() {
							$('a', _this).removeClass('pad');
							$(this).nextAll().addClass('pad');
						})
						.find('a')
							.addClass('icon-rating pad');
				});

EOS
			, true);
			if (ConditionalTags::is('account_page')) {
				$this->addDocumentJQueryScript(
<<<'EOS'
					$('.woocommerce .my_account_orders .order-actions .button').addClass('small');
EOS
				);
			}
		}

		// WooCommerce Brands
		if (self::isPluginActive('woocommerce-brands')) {
			wp_dequeue_style('brands-styles');
		}

		// WPML
		if (self::isPluginActive('wpml')) {
			if (is_active_widget(false, false, 'icl_lang_sel_widget')) {
				$this->addDocumentJQueryScript(
<<<'EOS'
					$('.widget_icl_lang_sel_widget').each(function() {
						$('ul', this).unwrap().addClass('simple alt');
						$('img', this).addClass('icon');
					});
EOS
				);
			}
		}

		// Debug
		$this->endMarker(__METHOD__);

	}

	/**
	 * @internal action: pre_get_posts
	 *
	 * @param object $query
	 */
	public function actionPreGetPosts($query)
	{
		if ($query->is_main_query() && ($query->is_tax('portfolio-category') || $query->is_tax('portfolio-tag'))) {
			$query->query_vars['posts_per_page'] = self::to('portfolio/archive/count');
		}
	}

	/**
	 * @internal action: the_post
	 *
	 * @param \WP_Query $query
	 */
	public function actionThePost(&$post)
	{
		self::$posts_stack[] = $post->ID;
	}

	/**
	 * @internal action: comment_form_before_fields
	 */
	public function actionCommentFormBeforeFields()
	{
		echo '<div class="columns alt-mobile"><ul>';
	}

	/**
	 * @internal action: comment_form_after_fields
	 */
	public function actionCommentFormAfterFields()
	{
		echo '</ul></div>';
	}

	/**
	 * @internal action: layerslider_ready
	 */
	public function actionLayersliderReady()
	{
		$GLOBALS['lsAutoUpdateBox'] = false;
	}

	/**
	 * @internal action: woocommerce_before_shop_loop_item_title
	 * @since {since}
	 */
	public function actionWoocommerceBeforeShopLoopItemTitle()
	{

		echo Template::instance(<<<'EOT'
			<figure class="thumbnail featured full-width">
				<a href=":permalink" :img_attrs>
					:thumbnail
					:thumbnail_hover
				</a>
			</figure>
EOT
		, [
			'permalink'       => get_permalink(),
			'img_attrs'       => self::getImageAttrs('a', ['border' => false, 'hover' => self::to('woocommerce/shop/image_hover'), 'fancybox' => false], 'html'),
			'thumbnail'       => woocommerce_get_product_thumbnail(),
			'thumbnail_hover' => function () {
				if (self::to('woocommerce/shop/image_hover') == 'image') {
					$attachment_ids = $GLOBALS['product']->get_gallery_image_ids();
					if (isset($attachment_ids[0])) {
						$image_size = apply_filters('single_product_archive_thumbnail_size', 'shop_catalog');
						return wp_get_attachment_image($attachment_ids[0], $image_size);
					}
				}
			}
		]);

	}

	/**
	 * @internal action: woocommerce_shop_loop_item_title
	 */
	public function actionWoocommerceShopLoopItemTitle()
	{
		$html = HTML::h3();
		$html->addNew('a')
			->href(get_the_permalink())
			->add(get_the_title());
		echo $html;
	}

	/**
	 * @internal action: woocommerce_before_subcategory_title
	 *
	 * @param \WP_Term $category
	 */
	public function actionWoocommerceBeforeSubcategoryTitle($category)
	{
		$thumbnail = HTML::figure()
			->class('featured full-width');
		$thumbnail->addNew('a')
			->attr(self::getImageAttrs('a', ['fancybox' => false]))
			->href(get_term_link($category->slug, 'product_cat'))
			->add(Func::functionGetOutputBuffer('woocommerce_subcategory_thumbnail', $category));
		echo $thumbnail;
	}

	/**
	 * @internal filter: woocommerce_shop_loop_subcategory_title
	 *
	 * @param \WP_Term $category
	 */
	public function actionWoocommerceShopLoopSubcategoryTitle($category)
	{
		$title = HTML::h3();
		$a = $title->addNew('a')
			->href(get_term_link($category->slug, 'product_cat'))
			->add($category->name);
		if ($category->count > 0) {
			$a->addNew('small')->add(' (', $category->count, ')');
		}
		echo $title;
	}

	/**
	 * @internal action: woocommerce_product_thumbnails, 15
	 */
	public function actionWoocommerceProductThumbnailsBefore()
	{
		echo '<div class="columns"><ul>';
	}

	/**
	 * @internal action: woocommerce_product_thumbnails, 25
	 */
	public function actionWoocommerceProductThumbnailsAfter()
	{
		echo '</ul></div>';
	}

	/**
	 * @internal action: woocommerce_single_product_summary, 35
	 */
	public function actionWoocommerceSingleProductSummary()
	{

		if (!self::isPluginActive('woocommerce-brands') || !self::to('woocommerce/product/brands')) {
			return;
		}

		// Brand
		$brands = wp_get_post_terms(get_the_ID(), 'product_brand', ['fields' => 'ids']);

		if (count($brands) == 0) {
			return;
		}
		$brand = get_term($brands[0], 'product_brand');

		// Validation
		if (!$brand->description) {
			return;
		}

		// HTML
		$html = HTML::make();
		$html->addNew('hr');

		// Thumbnail
		if ($thumbnail_id = get_woocommerce_term_meta($brand->term_id, 'thumbnail_id', true)) {
			$html->addNew('figure')
				->class('alignleft')
				->addNew('a')
					->attr(self::getImageAttrs('a', ['border' => false, 'hover' => '']))
					->href(get_term_link($brand, 'product_brand'))
					->title($brand->name)
					->add(wp_get_attachment_image($thumbnail_id, 'logo'));
		}

		// Description
		$html->add(wpautop(wptexturize($brand->description)));

		echo $html;

	}

	/**
	 * @internal filter: woocommerce_review_before
	 *
	 * @param \WP_Comment $comment
	 */
	public function actionWoocommerceReviewBefore($comment)
	{
		echo '<figure class="alignleft fixed inset-border">';
			woocommerce_review_display_gravatar($comment);
		echo '</figure>';
	}

	/**
	 * @internal action: woocommerce_widget_shopping_cart_buttons
	 * @since {since}
	 */
	public function actionWoocommerceWidgetShoppingCartButtons()
	{
		echo Template::instance(<<<'EOT'
			<a href=":cart_url" class="button small wc-forward"><span>:view_cart</span></a>
			<a href=":checkout_url" class="button small checkout wc-forward" style="border-color: #129a00; color: #129a00;"><span>:checkout</span><i class="icon-right-bold"></i></a>
EOT
		, [
			'cart_url'     => wc_get_cart_url(),
			'checkout_url' => wc_get_checkout_url(),
			'view_cart'    => __('View Cart', 'woocommerce'),
			'checkout'     => __('Checkout', 'woocommerce')
		]);
	}

	/**
	 * @internal filter: image_size_names_choose, 100
	 *
	 * @param  array $sizes
	 * @return array
	 */
	public function filterImageSizeNamesChoose($sizes)
	{
		$sizes = Func::arrayInsert($sizes, ['logo' => __('Logo', 'everything')], 'thumbnail');
		$sizes = Func::arrayInsert($sizes, ['max-width' => __('Site width', 'everything')], 'full');
		return $sizes;
	}

	/**
	 * @internal filter: body_class
	 *
	 * @param  array $classes
	 * @return array
	 */
	public function filterBodyClass($classes)
	{
		if (self::isPluginActive('wpml')) {
			$classes[] = 'lang-' . ICL_LANGUAGE_CODE;
		}
		$classes[] = 'layout-' . self::to_('general/layout')->value();
		$classes[] = 'scheme-' . self::to('general/scheme');
		return $classes;
	}

	/**
	 * @internal filter: post_class, 20
	 *
	 * @param  array  $classes
	 * @param  string $class
	 * @param  int    $post_id
	 * @return array
	 */
	public function filterPostClass($classes, $class, $post_id)
	{
		if (self::isPluginActive('woocommerce') && isset($GLOBALS['woocommerce_loop']) && (!is_product() || !empty($GLOBALS['woocommerce_loop']['name'])) && (!is_search() || get_query_var('post_type') == 'product')) {
			$columns = isset($GLOBALS['woocommerce_loop']['columns']) ? $GLOBALS['woocommerce_loop']['columns'] : apply_filters('loop_shop_columns', 4);
			$classes[] = 'col-1-' . $columns;
		}
		return $classes;
	}

	/**
	 * @internal filter: wp_nav_menu_items
	 * @internal filter: wp_list_pages
	 *
	 * @param  string $items
	 * @param  array  $args
	 * @return string
	 */
	public function filterWPNavMenuItems($items, $args)
	{

		// Theme location
		if (isset($args->theme_location)) {
			$theme_location = $args->theme_location;
		} else if (isset($args['theme_location'])) {
			$theme_location = $args['theme_location'];
		} else {
			return $items;
		}

		// Icons
		$items = preg_replace_callback('#<li(?P<li_attrs>[^>]*)>\s*<a(?P<a_attrs>[^>]*)>(?P<label>[^<>]*?)</a>#i', function ($matches) {
			if (!preg_match('/[ "](?P<class>icon-(?P<icon>[-_a-z0-9]+))[ "]/', $matches['li_attrs'], $class_matches)) {
				return $matches[0];
			}
			return sprintf(
				'<li%s><a%s>%s%s</a>',
				str_replace($class_matches['class'], '', $matches['li_attrs']),
				$matches['a_attrs'],
				Everything::getShortcodeOutput(
					strpos($class_matches['icon'], '_') === false ? 'vector_icon' : 'image_icon',
					['name' => str_replace('_', '/', $class_matches['icon'])]
				),
				$matches['label']
			);
		}, $items);

		// Result
		return $items;

	}

	/**
	 * @internal filter: get_search_form
	 *
	 * @return string
	 */
	public function filterGetSearchForm()
	{

		$search_form = HTML::form()
			->method('get')
			->action(home_url('/'))
			->class('search')
			->role('search');

		$search_form->addNew('input')
			->type('search')
			->name('s')
			->value(get_search_query())
			->placeholder(__('Search site', 'everything') . '&hellip;');

		$search_form->addNew('button')
			->type('submit')
			->addNew('i')
				->class('icon-search');

		return $search_form->toHTML();

	}

	/**
	 * @internal filter: max_srcset_image_width
	 *
	 * @return int
	 */
	public function filterMaxSrcsetImageWidth()
	{
		return 1920*2;
	}

	/**
	 * @internal filter: get_calendar
	 *
	 * @param  string $calendar_output
	 * @return string
	 */
	public function filterGetCalendar($calendar_output)
	{
		return str_replace('<table ', '<table class="fixed" ', $calendar_output);
	}

	/**
	 * @internal filter: the_content, 1
	 *
	 * @param  string $content
	 * @return string
	 */
	public function filterTheContent1($content)
	{

		// More anchor
		if (stripos($content, 'id="more-') !== false) {
			$content = preg_replace('#^\s*<span id="more-[0-9]+"></span>\s*#i', '', $content);
		}

		// Align-none
		if (stripos($content, 'alignnone') !== false) {
			$content = preg_replace(
				'#(<p([^>]*)>)?(( *(<a[^>]*>)?<img[^>]*class="[^"]*alignnone[^"]*"[^>]*>(</a>)? *){2,})(</p>)?#i',
				'<div class="figuregroup"\2>\3</div>',
				$content
			);
		}

		return $content;

	}

	/**
	 * @internal filter: the_content
	 *
	 * @param  string $content
	 * @return string
	 */
	public function filterTheContent($content)
	{
		if (stripos($content, 'iframe') !== false || stripos($content, 'embed') !== false) {
			$content = preg_replace('#(<p>)?(<(iframe|embed).*?></\3>)(</p>)?#i', '<div class="embed">\2</div>', $content);
		}
		return $content;
	}

	/**
	 * @internal filter: wp_trim_excerpt
	 *
	 * @param  string $text
	 * @return string
	 */
	public function filterWPTrimExcerpt($text)
	{
		if (is_feed() || is_attachment() || trim($text) == '') {
			return $text;
		}
		return sprintf('%s <a href="%s" class="more-link">%s</a>', $text, get_permalink(), self::getReadMore());
	}

	/**
	 * @internal filter: the_password_form
	 *
	 * @param  string $html
	 * @return string
	 */
	public function filterThePasswordForm($html)
	{
		return str_replace('</label> <input ', '</label><input ', $html);
	}

	/**
	 * @internal filter: comment_form_defaults
	 *
	 * @param  array $defaults
	 * @return array
	 */
	public function filterCommentFormDefaults($defaults)
	{

		$commenter = wp_get_current_commenter();

		return array_merge($defaults, [
			'fields'               => [
				'author' => '<li class="col-1-3"><input class="full-width" type="text" name="author" placeholder="' . __('Name', 'everything') . '*" value="' . esc_attr($commenter['comment_author']) . '" required /></li>',
				'email'  => '<li class="col-1-3"><input class="full-width" type="email" name="email" placeholder="' . __('E-mail', 'everything') . ' (' . __('not published', 'everything') . ')*" value="' . esc_attr($commenter['comment_author_email']) . '" required /></li>',
				'url'    => '<li class="col-1-3"><input class="full-width" type="text" name="url" placeholder="' . __('Website', 'everything') . '" value="' . esc_attr($commenter['comment_author_url']) . '" /></li>'
			],
			'comment_field'        => '<p><textarea class="full-width" name="comment" placeholder="' . __('Message', 'everything') . '" required></textarea></p>',
			'must_log_in'          => str_replace('<p class="must-log-in">', '<p class="must-log-in small">', $defaults['must_log_in']),
			'logged_in_as'         => str_replace('<p class="logged-in-as">', '<p class="logged-in-as small">', $defaults['logged_in_as']),
			'comment_notes_before' => '',
			'comment_notes_after'  => '',
			'title_reply'          => __('Leave a comment', 'everything'),
			'title_reply_to'       => __('Leave a reply to %s', 'everything'),
			'cancel_reply_link'    => __('Cancel reply', 'everything'),
			'label_submit'         => __('Send &rsaquo;', 'everything'),
			'format'               => 'html5'
		]);

	}

	/**
	 * @internal filter: wp_video_extensions
	 *
	 * @param  array $exts
	 * @return array
	 */
	public function filterWPVideoExtensions($exts)
	{
		$exts[] = 'ogg';
		return $exts;
	}

	/**
	 * @internal filter: wp_audio_shortcode
	 * @internal filter: wp_video_shortcode
	 *
	 * @param  string $html
	 * @return string
	 */
	public function filterWPAudioVideoShortcode($html)
	{
		$class = preg_match('/<(audio|video) /i', $html, $m) ? strtolower($m[1]) : '';
		return "<div class=\"embed {$class}\">" . preg_replace('#^<div.*?>(.+?)</div>$#i', '\1', $html) . '</div>';
	}

	/**
	 * @internal filter: get_previous_post_where
	 * @internal filter: get_next_post_where
	 *
	 * @param  string $query
	 * @return string
	 */
	public function filterGetAdjacentPostWhere($query)
	{
		if (is_singular('portfolio')) {
			$query .= $GLOBALS['wpdb']->prepare(' AND p.post_parent = %d', $GLOBALS['post']->post_parent);
		}
		return $query;
	}

	/**
	 * @internal filter: everything_headline
	 *
	 * @param  string $headline
	 * @return string
	 */
	public function filterBBPEverythingHeadline($headline)
	{
		if (self::isPluginActive('bbpress') && is_bbpress()) {
			switch ($headline) {
				case 'mixed':      return self::isPluginActive('breadcrumb-navxt', 'breadcrumb-trail') ? 'breadcrumbs' : 'none';
				case 'navigation': return 'none';
			}
		}
		return $headline;
	}

	/**
	 * @internal filter: bbp_get_breadcrumb
	 *
	 * @param  string $trail
	 * @param  array  $crumbs
	 * @param  array  $r
	 * @return bool
	 */
	public function filterBBPGetBreadcrumb($trail, $crumbs, $r)
	{
		return !trim($r['sep']) ? $trail : '';
	}

	/**
	 * @internal filter: masterslider_disable_auto_update
	 *
	 * @return bool
	 */
	public function filterMastersliderDisableAutoUpdate()
	{
		return true;
	}

	/**
	 * @internal filter: masterslider_panel_default_setting
	 *
	 * @param  array $options
	 * @return array
	 */
	public function filterMastersliderPanelDefaultSetting($options)
	{
		$options['width']      = self::to_('general/max_width')->value('default');
		$options['height']     = round($options['width'] / 1.7778);
		$options['layout']     = self::to_('general/layout')->value('default') == 'open' ? 'fullwidth' : 'boxed';
		$options['autoHeight'] = self::to_('general/layout')->value('default') == 'boxed';
		return $options;
	}

	/**
	 * @internal filter: everything_headline
	 *
	 * @param  string $headline
	 * @return string
	 */
	public function filterWoocommerceEverythingHeadline($headline)
	{
		if (self::isPluginActive('woocommerce') && is_product()) {
			switch ($headline) {
				case 'mixed':      return self::isPluginActive('breadcrumb-navxt', 'breadcrumb-trail') ? 'breadcrumbs' : 'none';
				case 'navigation': return 'none';
			}
		}
		return $headline;
	}

	/**
	 * @internal filter: everything_author_bio_display
	 * @internal filter: everything_social_buttons_display
	 * @internal filter: everything_meta_display
	 *
	 * @param  bool $show
	 * @return bool
	 */
	public function filterWoocommerceEverythingDisplay($show)
	{
		if (self::isPluginActive('woocommerce') && (is_cart() || is_checkout() || is_account_page() || is_order_received_page())) {
			return false;
		}
		return $show;
	}

	/**
	 * @internal filter: woocommerce_enqueue_styles
	 *
	 * @return boolean
	 */
	public function filterWoocommerceEnqueueStyles()
	{
		return false;
	}

	/**
	 * @internal filter: loop_shop_columns
	 *
	 * @return int
	 */
	public function filterWoocommerceLoopShopColumns()
	{
		return self::to('woocommerce/shop/columns');
	}

	/**
	 * @internal filter: loop_shop_per_page
	 *
	 * @return int
	 */
	public function filterWoocommerceLoopShopPerPage()
	{
		return self::to('woocommerce/shop/per_page');
	}

	/**
	 * @internal filter: woocommerce_output_related_products_args
	 *
	 * @param  array $args
	 * @return array
	 */
	public function filterWoocommerceOutputRelatedProductsArgs($args)
	{
		$args['posts_per_page'] = self::to('woocommerce/related_products/total');
		return $args;
	}

	/**
	 * @internal filter: woocommerce_related_products_columns
	 *
	 * @param  int $columns
	 * @return int
	 */
	public function filterWoocommereRelatedProductsColumns($columns)
	{
		return self::to('woocommerce/related_products/columns');
	}

	/**
	 * @internal filter: woocommerce_cross_sells_total
	 *
	 * @param  int $posts_per_page
	 * @return int
	 */
	public function filterWoocommercCrossSellsTotal($posts_per_page)
	{
		return self::to('woocommerce/cross_sells/total');
	}

	/**
	 * @internal filter: woocommerce_cross_sells_columns
	 *
	 * @param  int $columns
	 * @return int
	 */
	public function filterWoocommercCrossSellsColumns($columns)
	{
		return self::to('woocommerce/cross_sells/columns');
	}

	/**
	 * @internal filter: woocommerce_product_thumbnails_columns
	 *
	 * @param  int $columns
	 * @return int
	 */
	public function filterWoocommerceProductThumbnailsColumns($columns)
	{
		return self::to('woocommerce/product/thumbnails_columns');
	}

	/**
	 * @internal filter: product_cat_class
	 *
	 * @param  array $classes
	 * @return array
	 */
	public function filterWoocommerceProductCatClass($classes)
	{
		if (isset($GLOBALS['woocommerce_loop']['columns'])) {
			$classes[] = 'col-1-' . $GLOBALS['woocommerce_loop']['columns'];
		}
		return $classes;
	}

	/**
	 * @internal filter: woocommerce_show_page_title
	 *
	 * @return bool
	 */
	public function filterWoocommerceShowPageTitle()
	{
		return !self::$headline_used && !self::to('page/hide_title');
	}

	/**
	 * @internal filter: woocommerce_single_product_image_thumbnail_html
	 *
	 * @param  string $html
	 * @param  int    $attachment_id
	 * @return string
	 */
	public function filterWoocommerceSingleProductImageThumbnailHtml($html, $attachment_id)
	{

		if (strpos($html, 'woocommerce-product-gallery__image--placeholder') === false) {

			$caption = self::woocommerceGetThumbnailCaption($attachment_id);

			$attrs = self::getImageAttrs('a', [], 'html') . ' data-fancybox-group="product-gallery" data-fancybox-title="' . esc_attr($caption) . '"';
			$html = str_replace('<a ', "<a {$attrs} ", $html);

			if (strpos($html, 'wp-post-image') === false) { // not main image
				$columns = apply_filters('woocommerce_product_thumbnails_columns', 4);
				$html = '<li class="col-1-' . $columns . '">' . $html . '</li>';
			}

		} else {

			$attrs = self::getImageAttrs('div', ['class' => 'woocommerce-product-gallery__image--placeholder'], 'html');
			$html = preg_replace('/<div class="[^"]*"/', "<div {$attrs}", $html);

		}

		return $html;

	}

	/**
	 * @internal filter: woocommerce_placeholder_img_src
	 *
	 * @return string
	 */
	public function filterWoocommercePlaceholderImgSrc()
	{
		return $this->template_uri . '/data/img/woocommerce/placeholder.jpg';
	}

	/**
	 * @internal filter: woocommerce_placeholder_img
	 *
	 * @param  string $html
	 * @return string
	 */
	public function filterWoocommercePlaceholderImg($html)
	{
		return str_replace('<img ', '<img data-image1496="' . $this->template_uri . '/data/img/woocommerce/placeholder@2x.jpg" ', $html);
	}

	/**
	 * @internal filter: woocommerce_product_get_rating_html
	 *
	 * @param  string  $html
	 * @param  float   $avg_rating
	 * @param  int     $count
	 * @return string
	 */
	public function filterWoocommerceProductGetRatingHTML($html, $avg_rating, $count)
	{
		$stars  = self::getShortcodeOutput('rating', ['rate' => $avg_rating . '/5', 'advanced_tag' => '']);
		$rating = HTML::span()->class('rating');
		if ($avg_rating > 0) {
			$rating
				->title(sprintf(__('%s out of 5', 'woocommerce'), $avg_rating))
				->add($stars);
		} else {
			$rating->add(str_replace('icon-rating-empty', 'icon-rating-empty pad', $stars));
		}
		return $rating->toHTML();
	}

	/**
	 * @internal filter: woocommerce_review_gravatar_size
	 *
	 * @return int
	 */
	public function filterWoocommerceReviewGravatarSize()
	{
		return 50;
	}

	/**
	 * @internal filter: wc_add_to_cart_message_html
	 *
	 * @param  string $html
	 * @return string
	 */
	public function filterWoocommerceAddToCartMessageHTML($html)
	{
		return str_replace('class="button ', 'class="button small ', $html);
	}

	/**
	 * @internal filter: woocommerce_loop_add_to_cart_link
	 *
	 * @param  string $html
	 * @return string
	 */
	public function filterWoocommerceLoopAddToCartLink($html)
	{
		return str_replace('class="button ', 'class="button small ', $html);
	}

	/**
	 * @internal filter: woocommerce_order_button_html
	 *
	 * @param  string $html
	 * @return string
	 */
	public function filterWoocommerceOrderButtonHTML($html)
	{
		if (preg_match('/value="(.*?)"/', $html, $matches)) {
			return '<button type="submit" class="big" name="woocommerce_checkout_place_order" id="place_order" style="border-color: #129a00; color: #129a00;"><span>' . $matches[1] . '</span><i class="icon-right-bold"></i></button>';
		} else {
			return $html;
		}
	}

	/**
	 * @internal filter: get_product_search_form
	 *
	 * @param  string $html
	 * @return string
	 */
	public function filterWoocommerceGetProductSearchForm($html)
	{
		return preg_replace('/\s*\n\s*/', '', $html);
	}

	/**
	 * @internal filter: woocommerce_account_menu_item_classes
	 *
	 * @param  array $classes
	 * @return array
	 */
	public function filterWoocommerceAccountMenuItemClasses($classes)
	{
		if (in_array('is-active', $classes)) {
			$classes[] = 'active';
		}
		return $classes;
	}

	/**
	 * @internal filter: woocommerce_add_to_cart_fragments
	 *
	 * @param  array $fragments
	 * @return array
	 */
	public function filterWoocommerceAddToCartFragments($fragments)
	{
		$fragments['.cart-info.small'] = self::woocommerceGetCartInfo('small');
		$fragments['.cart-info.big']   = self::woocommerceGetCartInfo('big', self::to('header/cart/visible', '__hidden', []));
		return $fragments;
	}

	/**
	 * @internal filter: woocommerce_cart_item_remove_link
	 * @since {since}
	 *
	 * @param  string $html
	 * @return string
	 */
	public function filterWoocommerceCartItemRemoveLink($html)
	{
		return str_replace(['class="remove"', '&times;'], ['class="remove alt"', '<i class="icon-cancel-circled"></i>'], $html);
	}

	/**
	 * @internal filter: products_shortcode_tag
	 *
	 * @param  string $tag
	 * @return string
	 */
	public function filterWoocommerceProductsShortcodeTag($tag)
	{

		add_shortcode($tag, function ($atts) {

			$wc_get_template_part_filter = function ($template, $slug, $name) {

				if ($slug != 'content' || $name != 'product') {
					return $template;
				}

				$output = Func::functionGetOutputBuffer('wc_get_template', 'content-widget-product.php', ['show_rating' => true]);
				$output = str_replace('<figure class="alignright fixed">', '<figure class="alignleft fixed">', $output);

				echo $output;
				return '';

			};

			add_filter('wc_get_template_part', $wc_get_template_part_filter, 10, 3);
			$output = WC_Shortcodes::products($atts);
			remove_filter('wc_get_template_part', $wc_get_template_part_filter);

			$atts = shortcode_atts([
				'columns' => 3
			], $atts, 'products');

			if (!absint($atts['columns'])) {
				$atts['columns'] = 3;
			}

			$output = str_replace('<ul class="', '<ul class="posts-list ', $output);
			$output = str_replace('<li>', '<li class="col-1-' . $atts['columns'] . '">', $output);

			return $output;

		});

		return '__' . $tag;

	}

	/**
	 * @internal filter: drone_contact_form_template
	 *
	 * @param  string $template
	 * @return string
	 */
	public function filterContactFormTemplate($template)
	{
		return <<<'EOT'
			<form class=":class" action=":action" method="post">
				<input type="hidden" name="action" value=":action_id" />
				:fields
				<p>
					<input type="submit" value=":submit_label&nbsp;&rsaquo;" />
					<i class="icon-arrows-ccw load"></i>
					<span class="msg small"></span>
				</p>
			</form>
EOT;
	}

	/**
	 * @internal filter: drone_contact_form_field_template
	 *
	 * @param  string $template
	 * @param  string $name
	 * @return string
	 */
	public function filterContactFormFieldTemplate($template, $name)
	{

		switch ($name) {
			case 'message':
				$template = '<textarea class="full-width" name=":name" placeholder=":label" required></textarea>';
				break;
		}

		return "<p>{$template}</p>";

	}

	/**
	 * @internal filter: drone_breadcrumbs_output
	 *
	 * @param  \Drone\HTML $output
	 * @return \Drone\HTML
	 */
	public function filterBreadcrumbsOutput($output)
	{
		return $output->addClass('breadcrumbs alt');
	}

	/**
	 * @internal filter: drone_post_options_id
	 *
	 * @param  string $id
	 * @return string
	 */
	public function filterPostOptionsID($id)
	{
		return '_' . $this->theme->id_; // backward compatybility
	}

	/**
	 * @internal filter: drone_widget_id_base
	 *
	 * @param  string $id_base
	 * @param  string $id
	 * @return string
	 */
	public function filterWidgetIDBase($id_base, $id)
	{
		return $this->theme->id . '-' . str_replace('_', '-', $id); // backward compatybility
	}

	/**
	 * @internal action: drone_widget_posts_list_on_setup_options
	 *
	 * @param object $options
	 * @param object $widget
	 */
	public function actionWidgetPostsListOnSetupOptions($options, $widget)
	{
		$options->addOption('select', 'orientation', 'scrollable', __('Orientation', 'everything'), '', ['options' => [
			'vertical'   => __('Vertical', 'everything'),
			'scrollable' => __('Scrollable', 'everything')
		]], 'count');
		$options->addOption('boolean', 'thumbnail', true, '', '', ['caption' => __('Show thumbnail', 'everything')], 'author');
		$options->addOption('boolean', 'date', true, '', '', ['caption' => __('Show date', 'everything')], 'author');
		$options->deleteChild('author');
	}

	/**
	 * @internal filter: drone_widget_posts_list_template
	 *
	 * @param  string $template
	 * @param  object $widget
	 * @return string
	 */
	public function filterWidgetPostsListTemplate($template, $widget)
	{
		return Template::instance(<<<'EOT'
			<ul class="posts-list[if:scrollable] scroller[endif]">
				\:posts
			</ul>
EOT
		, [
			'scrollable' => $widget->wo('orientation') == 'scrollable'
		]);
	}

	/**
	 * @internal filter: drone_widget_posts_list_post_template
	 *
	 * @param  string $template
	 * @param  object $widget
	 * @param  object $post
	 * @return string
	 */
	public function filterWidgetPostsListPostTemplate($template, $widget, $post)
	{
		return Template::instance(<<<'EOT'
			<li>
				[if:thumbnail]
					<figure class="alignleft fixed">
						<a href="\:permalink" :img_attrs>
							:thumbnail
						</a>
					</figure>
				[endif]
				<h3>
					<a href="\:permalink" title="\:title">\:display_title</a>
				</h3>
				[if:date]
					<p class="small">:date</p>
				[endif]
				[if\:comments]
					<p class="small">\:comments_text</p>
				[endif]
			</li>
EOT
		, [
			'thumbnail' => $widget->wo('thumbnail') ? get_the_post_thumbnail($post->ID, apply_filters('everything_image_size', 'post-thumbnail-mini-crop', 'widget_posts_list')) : false,
			'date'      => $widget->wo('date') ? function () use ($post) {
				$GLOBALS['post'] = $post;
				$date = get_the_date();
				wp_reset_postdata();
				return $date;
			} : false,
			'img_attrs' => self::getImageAttrs('a', ['border' => true, 'hover' => '', 'fanbcybox' => false], 'html')
		]);
	}

	/**
	 * @internal action: drone_widget_twitter_on_setup_options
	 *
	 * @param object $options
	 * @param object $widget
	 */
	public function actionWidgetTwitterOnSetupOptions($options, $widget)
	{
		$options->addOption('select', 'orientation', 'vertical', __('Orientation', 'everything'), '', ['options' => [
			'vertical'   => __('Vertical', 'everything'),
			'horizontal' => __('Horizontal', 'everything'),
			'scrollable' => __('Scrollable', 'everything')
		]], 'count');
		$options->addOption('boolean', 'follow_me_button', true, '', '', ['caption' => __('Add "follow me" button', 'everything')], 'oauth');
	}

	/**
	 * @internal filter: drone_widget_twitter_template
	 *
	 * @param  string $template
	 * @param  object $widget
	 * @return string
	 */
	public function filterWidgetTwitterTemplate($template, $widget)
	{
		return Template::instance(<<<'EOT'
			<div class="twitter">
				[if:horizontal]<div class="columns">[endif]
					<ul[if:scrollable] class="scroller"[endif]>
						\:tweets
					</ul>
				[if:horizontal]</div>[endif]
				[if:follow_me_button_url]
					<p><a href=":follow_me_button_url" class="button small">:follow_me &rsaquo;</a></p>
				[endif]
			</div>
EOT
		, [
			'horizontal'           => $widget->wo('orientation') == 'horizontal',
			'scrollable'           => $widget->wo('orientation') == 'scrollable',
			'follow_me_button_url' => $widget->wo('follow_me_button') ? 'https://twitter.com/' . $widget->wo('username') : false,
			'follow_me'            => __('follow me', 'everything')
		]);
	}

	/**
	 * @internal filter: drone_widget_twitter_tweet_template
	 *
	 * @return string
	 */
	public function filterWidgetTwitterTweetTemplate()
	{
		return <<<'EOT'
			<li>
				<i class="icon-twitter"></i>
				:content<br />
				<small class="alt"><a href=":url">:time_diff</a></small>
			</li>
EOT;
	}

	/**
	 * @internal filter: drone_widget_twitter_html
	 *
	 * @param  string $html
	 * @param  object $widget
	 * @return string
	 */
	public function filterWidgetTwitterHTML($html, $widget)
	{
		if ($widget->wo('orientation') == 'horizontal') {
			$columns = preg_match_all('#<li>.*?</li>#s', $html);
			$html = str_replace('<li>', '<li class="col-1-' . $columns . '">', $html);
		}
		return $html;
	}

	/**
	 * @internal filter: drone_widget_flickr_template
	 * @internal filter: drone_widget_instagram_template
	 *
	 * @param  string $template
	 * @return string
	 */
	public function filterWidgetFlickrInstagramTemplate($template)
	{
		return '<div class="flickr">' . $template . '</div>';
	}

	/**
	 * @internal filter: drone_widget_flickr_photo_template
	 * @internal filter: drone_widget_instagram_photo_template
	 *
	 * @param  string $template
	 * @param  object $widget
	 * @param  object $photo
	 * @return string
	 */
	public function filterWidgetFlickrInstagramPhotoTemplate($template, $widget, $photo)
	{
		$img_attrs = self::getImageAttrs('a', ['border' => false], 'html');
		return <<<"EOT"
			<li>
				<a href=":url" rel=":id" data-fancybox-title=":title" {$img_attrs}>
					<img src=":src" width="63" height="63" alt=":title" />
				</a>
			</li>
EOT;
	}

	/**
	 * Begin content layer
	 *
	 * @param  string $content
	 * @return string
	 */
	public static function beginContent()
	{

		if (is_page_template('tpl-hidden-content.php')) {
			return '';
		}

		// Content
		$content = HTML::make();
		$main = $content->addNew('div')->class('main');

		// Layout
		$layout = self::io('layout/layout/layout', 'sidebar/layout', '__hidden_ns');
		$layout = apply_filters('everything_layout', $layout);

		// Sidebars
		$pad   = ['left' => 0, 'right' => 0];
		$style = '';
		$side  = 'left';

		foreach ($layout as $num => $sidebar) {

			if ($sidebar == '#') {

				$side = 'right';

			} else if ($sidebar) {

				$sidebar = apply_filters('everything_sidebar', $sidebar, 'aside');

				$width = self::to(['sidebar/list/builtin/' . $sidebar], null, self::to_('sidebar/list/additional')->value($sidebar));
				if (is_array($width)) {
					$width = $width['width'];
				}

				$id = "aside-{$side}-{$num}";
				$pad[$side] += $width;
				$GLOBALS['content_width'] = $width - 50;

				if ($side == 'left' && $width != self::DEFAULT_SIDEBAR_WIDTH) {
					$style .= <<<"EOS"
						.layout-boxed #content #{$id}.alpha:before {
							margin-left: {$width}px;
						}
EOS;
				}

				$aside = HTML::aside()
					->id($id)
					->addClass('aside', $side == 'left' ? 'alpha' : 'beta')
					->css('width', $width)
					->add(Func::functionGetOutputBuffer('dynamic_sidebar', $sidebar));

				if ($side == 'left' && $layout[0] && $layout[1] == '#' && $layout[2]) { // left-content-right
					$content->insert($aside);
				} else if ($side == 'right' && $layout[0] == '#') { // content-right-right
					$content->insert($aside, 1);
				} else {
					$content->add($aside);
				}

			}

		}

		$main->addClass($pad['right'] ? 'alpha' : ($pad['left'] ? 'beta' : ''));
		$main->style = sprintf('padding: 0 %2$dpx 0 %1$dpx; margin: 0 -%2$dpx 0 -%1$dpx;', $pad['left'], $pad['right']);

		if ($style) {
			$content->insertNew('style')->add(Func::minify('css', $style));
		}

		// Content width
		$GLOBALS['content_width'] = apply_filters('everything_content_width', self::$max_width - array_sum($pad) - 50);

		// Content
		ob_start(function ($buffer) use ($content, $main) {
			$main->add($buffer);
			return $content->toHTML();
		});

	}

	/**
	 * End content layer
	 */
	public static function endContent()
	{
		if (!is_page_template('tpl-hidden-content.php')) {
			ob_end_flush();
		}
	}

	/**
	 * Columns from string definition
	 *
	 * @param  string $s
	 * @return array
	 */
	public static function stringToColumns($s)
	{

		$s = str_replace(' ', '', $s);

		if (!$s) {
			return [];
		}

		return array_map(function ($s) {
			list ($span, $total) = strpos($s, '/') === false ? str_split($s) : explode('/', $s);
			return [
				'span'  => $span,
				'total' => $total,
				'width' => $span/$total,
				'class' => sprintf('col-%d-%d', $span, $total)
			];
		}, preg_split('/[_\+]/', $s));

	}

	/**
	 * Get image attributes
	 *
	 * @param  string $tag
	 * @param  array  $atts
	 * @param  string $format
	 * @return array
	 */
	public static function getImageAttrs($tag, $atts = [], $format = 'array')
	{

		// Image settings
		$settings = self::to_('site/image/settings');

		// Attributes
		extract(array_merge($defaults = [
			'border'   => $settings->value('border'),
			'hover'    => $settings->value('hover') ? 'zoom' : '',
			'fancybox' => $settings->value('fancybox'),
			'class'    => []
		], $atts));

		// Border
		$border = $border === 'inherit' ? $defaults['border'] : Func::stringToBool($border);

		// Hover
		if ($hover === 'inherit' || !in_array($hover, ['', 'zoom', 'image', 'grayscale'], true)) {
			$hover = $defaults['hover'];
		}

		// Fancybox
		$fancybox = $fancybox === 'inherit' ? $defaults['fancybox'] : Func::stringToBool($fancybox);

		// Properties
		$attrs = ['class' => (array)$class];

		if ($border) {
			$attrs['class'][] = 'inset-border';
		}
		if ($tag == 'a') {
			if ($hover) {
				$attrs['class'][] = $hover . '-hover';
			}
			if ($fancybox) {
				$attrs['class'][] = 'fb';
			}
		}

		$attrs['class'] = implode(' ', $attrs['class']);

		// Output
		switch ($format) {
			case 'html': return Func::arraySerialize($attrs, 'html');
			default:     return $attrs;
		}

	}

	/**
	 * Image attributes
	 *
	 * @param string $tag
	 * @param array  $atts
	 */
	public static function imageAttrs($tag, $atts = [])
	{
		echo self::getImageAttrs($tag, $atts, 'html');
	}

	/**
	 * Current blog style
	 *
	 * @return string
	 */
	public static function getBlogStyle()
	{
		return Shortcode::inShortcode('blog') ? Shortcode::instance('blog')->so('style') : self::to('site/blog/style');
	}

	/**
	 * Post format icon
	 *
	 * @return string
	 */
	public static function getPostIcon()
	{
		if (self::to('post/hide_icons')) {
			return;
		}
		if (($post_format = get_post_format()) === false) {
			return;
		}
		$icons = apply_filters('everything_post_formats_icons', [
			'aside'   => 'doc-text',
			'audio'   => 'mic',
			'chat'    => 'chat',
			'gallery' => 'picture',
			'image'   => 'camera',
			'link'    => 'link',
			'quote'   => 'quote',
			'status'  => 'comment',
			'video'   => 'youtube-alt'
		]);
		if (!isset($icons[$post_format])) {
			return;
		}
		return HTML::i()->class('icon-' . $icons[$post_format])->toHTML();
	}

	/**
	 * Read more phrase and icon
	 *
	 * @return string
	 */
	public static function getReadMore()
	{
		$readmore = self::to_([get_post_type() . '/readmore', 'post/readmore']);
		$html = HTML::make()->add($readmore->value('phrase'));
		if ($readmore->value('icon')) {
			$html->addNew('i')->class('icon-' . $readmore->value('icon'));
		}
		return $html->toHTML();
	}

	/**
	 * Paginate links
	 *
	 * @param  string   $name
	 * @param  WP_Query $query
	 * @return string
	 */
	public static function getPaginateLinks($name, $query = null)
	{

		if (!apply_filters('everything_pagination_display', true, $name)) {
			return '';
		}

		// Paginate links
		switch ($name) {

			// Page
			case 'page':
				if (!is_singular()) {
					return '';
				}
				$pagination = wp_link_pages([
					'before'           => ' ',
					'after'            => ' ',
					'next_or_number'   => rtrim(self::to('site/page_pagination'), 's'),
					'previouspagelink' => '<i class="icon-left-open"></i><span>' . __('Previous page', 'everything') . '</span>',
					'nextpagelink'     => '<span>' . __('Next page', 'everything') . '</span><i class="icon-right-open"></i>',
					'echo'             => false
				]);
				$pagination = str_replace('<a ', '<a class="button small" ', $pagination);
				$pagination = preg_replace('/ ([0-9]+) /',' <span class="button small active">\1</span> ', $pagination);
				break;

			// Comment
			case 'comments':
				if (!is_singular()) {
					return '';
				}
				$pagination = paginate_comments_links([
					'prev_next' => self::to('site/comments/pagination') == 'numbers_navigation',
					'prev_text' => '<i class="icon-left-open"></i>',
					'next_text' => '<i class="icon-right-open"></i>',
					'echo'      => false
				]);
				$pagination = str_replace(['page-numbers', 'current'], ['button small', 'active'], $pagination);
				break;

			// Default
			default:
				$args = [
					'prev_next' => self::to('site/pagination') == 'numbers_navigation',
					'prev_text' => '<i class="icon-left-open"></i>',
					'next_text' => '<i class="icon-right-open"></i>',
					'end_size'  => 1,
					'mid_size'  => 2
				];
				if ($name == 'woocommerce') {
					$args['base'] = esc_url_raw(str_replace('99999999', '%#%', remove_query_arg('add-to-cart', htmlspecialchars_decode(get_pagenum_link(99999999)))));
				}
				$pagination = Func::wpPaginateLinks($args, $query);
				$pagination = preg_replace_callback(
						'/class=[\'"](?P<dir>prev |next )?page-numbers(?P<current> current)?[\'"]()/i',
						function ($m) { return "class=\"{$m['dir']}button small" . str_replace('current', 'active', $m['current']) . '"'; },
						$pagination
				);

		}

		if (!$pagination) {
			return '';
		}

		return HTML::div()->class('pagination')->add($pagination)->toHTML();

	}

	/**
	 * Navigation menu
	 *
	 * @param string $theme_location
	 * @param int    $menu
	 * @param int    $depth
	 */
	public static function navMenu($theme_location, $menu = null, $depth = 0)
	{
		echo wp_nav_menu([
			'theme_location' => $theme_location,
			'menu'           => apply_filters('everything_menu', $menu, $theme_location),
			'depth'          => $depth,
			'container'      => '',
			'menu_id'        => '',
			'menu_class'     => '',
			'echo'           => false,
			'fallback_cb'    => function () use ($theme_location, $depth) {
				return '<ul>' . wp_list_pages([
					'theme_location' => $theme_location,
					'title_li'       => '',
					'depth'          => $depth,
					'echo'           => false
				]) . '</ul>';
			}
		]);
	}


	/**
	 * Languages menus
	 */
	public static function langMenu()
	{

		if (count($langs = icl_get_languages('skip_missing=0&orderby=custom')) == 0) {
			return;
		}

		$html = HTML::ul();
		$main = $html->addNew('li');
		$sub  = $main->addNew('ul');

		foreach ($langs as $lang) {

			$li = $sub->addNew('li');

			$a = $li->addNew('a')
				->href($lang['url'])
				->title($lang['native_name'])
				->add(
					$lang['native_name'],
					HTML::span()->class('flag-' . $lang['language_code'])
				);

			if ($lang['active']) {
				$li->class = 'current';
				$main->insertNew('a')
					->href('#')
					->title($lang['native_name'])
					->add(
						HTML::span()->class('flag-' . $lang['language_code']),
						HTML::i()->class('icon-down-open')
					);
			}

		}

		echo $html;

	}

	/**
	 * Title
	 */
	public static function title()
	{

		if (self::isPluginActive('bbpress') && is_bbpress()) {
			if (!self::$headline_used) {
				echo '<h1 class="title entry-title">' . get_the_title() . '</h1>';
			}
		}

		else if (!is_singular()) {
			echo '<h1 class="title entry-title"><a href="' . esc_url(apply_filters('the_permalink', get_permalink())) . '" rel="bookmark">' . self::getPostIcon() . get_the_title() . '</a></h1>';
		}

		else if (!self::$headline_used && !self::io('layout/page/hide_title/hide_title', [get_post_type() . '/hide_title', 'page/hide_title'], '__hidden')) {
			echo '<h1 class="title entry-title">' . self::getPostIcon() . get_the_title() . '</h1>';
		}

	}

	/**
	 * Social buttons
	 */
	public static function socialButtons()
	{

		if (is_search()) {
			return;
		}

		$items = self::to_([
			sprintf('%s/social_buttons/%s/items', get_post_type(), is_singular() ? 'single' : 'list'),
			sprintf('%s/social_buttons/items', get_post_type()),
			'page/social_buttons/items'
		]);

		if (!$items->value || !apply_filters('everything_social_buttons_display', (bool)self::po('layout/page/social_buttons/social_buttons', '__hidden', $items->isVisible()))) {
			return;
		}

		$html = is_singular() ? HTML::section()->class('section') : HTML::make();

		foreach (array_keys($items->options) as $item) {
			$media['media_' . $item] = $items->value($item);
		}
		$html->add(self::getShortcodeOutput('social_buttons', ['size' => 'small']+$media));

		echo $html;

	}

	/**
	 * Meta
	 *
	 * @param string $position
	 */
	public static function meta($position = 'after')
	{

		if (is_search()) {
			return;
		}

		if ($position == 'before' && (is_single() || get_post_type() != 'post')) {
			return;
		}

		if ($position == 'before') {
			$items = self::to_('post/meta/before/items');
		} else {
			$items = self::to_([
				sprintf('%s/meta/%s/items', get_post_type(), is_singular() ? 'single' : 'list'),
				sprintf('%s/meta/items', get_post_type()),
				'page/meta/items'
			]);
		}

		if (!$items->value || !apply_filters('everything_meta_display', (bool)self::po('layout/page/meta/meta', '__hidden', $items->isVisible()), $position)) {
			return;
		}

		$portfolio = get_post_type() == 'portfolio';

		$html = is_singular() ? HTML::section()->class('section') : HTML::make();

		$ul = $html->addNew('ul')->class('meta alt');
		foreach ((array)$items->value as $item) {
			switch ($item) {
				case 'date_time':
					if ($portfolio) {
						$ul->add(self::getMetaTemplate('<li class="published updated"><i class="icon-clock"></i>:date_time</li>'));
					} else {
						$ul->add(self::getMetaTemplate('<li class="published updated"><a href=":date_month_link" title=":0"><i class="icon-clock"></i>:date_time</a></li>', sprintf(__('View all posts from %s', 'everything'), get_the_date('F'))));
					}
					break;
				case 'date':
					if ($portfolio) {
						$ul->add(self::getMetaTemplate('<li class="published updated"><i class="icon-clock"></i>:date</li>'));
					} else {
						$ul->add(self::getMetaTemplate('<li class="published updated"><a href=":date_month_link" title=":0"><i class="icon-clock"></i>:date</a></li>', sprintf(__('View all posts from %s', 'everything'), get_the_date('F'))));
					}
					break;
				case 'mod_date':
					$ul->add(self::getMetaTemplate('<li class="updated"><a href=":link" title=":title"><i class="icon-clock"></i>:date_modified</a></li>'));
					break;
				case 'time_diff':
					$ul->add(self::getMetaTemplate('<li><a href=":link" title=":title"><i class="icon-clock"></i>:time_diff</a></li>'));
					break;
				case 'comments':
					$ul->add(self::getMetaTemplate('<li>[if:!0]<a href=":comments_link" title=":comments_number_text">[endif]<i class="icon-comment"></i>:comments_number_text[if:!0]</a>[endif]</li>'), self::isPluginActive('disqus'));
					break;
				case 'author':
					$ul->add(self::getMetaTemplate('<li><a href=":author_link" title=":author_name"><i class="icon-user"></i>:author_name</a></li>'));
					break;
				case 'categories':
					if ($portfolio) {
						$ul->add(get_the_term_list(get_the_ID(), 'portfolio-category', '<li><i class="icon-list"></i>', ', ', '</li>'));
					} else {
						$ul->add(self::getMetaTemplate('[if:category_list]<li><i class="icon-list"></i>:category_list</li>[endif]'));
					}
					break;
				case 'tags':
					if ($portfolio) {
						$ul->add(get_the_term_list(get_the_ID(), 'portfolio-tag', '<li><i class="icon-tag"></i>', ', ', '</li>'));
					} else {
						$ul->add(self::getMetaTemplate('[if:tags_list]<li><i class="icon-tag"></i>:tags_list</li>[endif]'));
					}
					break;
				case 'permalink':
					$ul->add(self::getMetaTemplate('<li><a href=":link" title=":title"><i class="icon-link"></i>:0</a></li>', __('Permalink', 'everything')));
					break;
			}
		}

		if ($position == 'after' || $ul->count() > 0) {
			echo $html;
		}

	}

	/**
	 * Cart info
	 *
	 * @param  string $type
	 * @param  array  $visible
	 * @return string
	 */
	public static function woocommerceGetCartInfo($type, $visible = ['desktop', 'mobile'])
	{

		if (count($visible) == 0) {
			return '';
		}

		// HTML
		$a = HTML::a()
			->href(wc_get_cart_url())
			->title(__('Cart', 'everything'))
			->addClass('cart-info', $type);

		// Visibility
		if (count($visible) == 1) {
			$a->addClass($visible[0] . '-only');
		}

		// Icon
		$a->addNew('i')
			->addClass('icon-woocommerce-cart', 'icon-' . self::to('woocommerce/cart/icon'));

		// Content
		if (self::to_('header/cart/content')->value('count')) {
			$a->addNew('span')
				->class('count')
				->add($GLOBALS['woocommerce']->cart->get_cart_contents_count());
		}
		if (self::to_('header/cart/content')->value('total') && $GLOBALS['woocommerce']->cart->get_cart_contents_count()) {
			$cart_total = strip_tags($GLOBALS['woocommerce']->cart->get_cart_total());
			$cart_total = preg_replace('/(' . preg_quote(get_option('woocommerce_price_decimal_sep'), '/') . ')([0-9]+)/', '\1<small>\2</small>', $cart_total);
			$a->addNew('span')
				->class('total')
				->add($cart_total);
		}

		return $a->toHTML();

	}

	/**
	 * Get thumbnail caption for WooCommerce product image
	 *
	 * @param  int    $attachment_id
	 * @return string
	 */
	public static function woocommerceGetThumbnailCaption($attachment_id)
	{

		if (!self::to('woocommerce/product/captions')) {
			return '';
		}

		$attachment = get_post($attachment_id);

		switch (self::to('woocommerce/product/captions')) {
			case 'title':
				return trim($attachment->post_title);
			case 'caption':
				return trim($attachment->post_excerpt);
			case 'caption_title':
				$caption = trim($attachment->post_excerpt) or $caption = trim($attachment->post_title);
				return $caption;
		}

	}

	/**
	 * Parse/fix WooCommerce widget list
	 *
	 * @param  string $s
	 * @return string
	 */
	public static function woocommerceWidgetParseList($s)
	{
		return preg_replace('#<ul class="([^"]*product_list_widget[^"]*)">#i', '<ul class="\1 posts-list">', $s);
	}

	/**
	 * Parse/fix WooCommerce widget navigation
	 *
	 * @param  string $s
	 * @return string
	 */
	public static function woocommerceWidgetparseNav($s)
	{
		$s = preg_replace('#<ul[^<>]*>.*</ul>#is', '<nav class="aside-nav-menu">\0</nav>', $s);
		$s = preg_replace('#(<a(?: rel="[^"]*")? href="[^"]*">)([^<>]*)(</a>)\s*<(span|small) class="count">\(?([0-9]+)\)?</\4>#i', '\1\2 <small>(\5)</small>\3', $s);
		return $s;
	}

}

Everything::instance();