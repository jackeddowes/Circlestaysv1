<?php








namespace Drone;

use Drone\Shortcodes\Shortcode;











class Theme
{






	const UPDATE_URL = 'https://themes.webberwebber.com/update/';






	const UPDATE_INTERVAL = 12;







	const ACTIVATION_TIME_ILLEGAL_SHIFT = 30;







	const OPTIONS_SETUP_FILENAME = 'options-setup.php';







	const OPTIONS_INC_FILENAME = 'options.php';







	const SHORTCODES_INC_FILENAME = 'shortcodes.php';







	const WIDGETS_INC_FILENAME = 'widgets.php';







	const OPTIONS_PAGE_TYPE = 'menu';







	const OPTIONS_SUBPAGE_TYPE = 'submenu';







	const WP_FUNCTIONS_FILENAME = 'functions.php';







	const WP_LANGUAGES_DIRECTORY = 'languages';







	const WP_THEME_OPTIONS_URI = 'options.php';







	const WP_AJAX_URI = 'admin-ajax.php';







	const WP_FILTER_PRIORITY_DEFAULT = 10;







	const WP_TRANSIENT_PREFIX = '_transient_';







	const WP_TRANSIENT_NAME_MAX_LENGTH = 45;







	const HOMEPAGE_URL = 'https://webberwebber.com/';







	const WPML_REFERRAL_URL = 'https://wpml.org/?aid=25858&affiliate_key=H0NWEUimxymp';






	private static $instance = null;






	private static $setup_options_lock = false;






	private $start_time;






	private $marker_time = [];






	private $debug_log = [];







	private $theme_options_array;







	private $theme_options;







	private $post_options = [];






	private $sysinfo;






	private $plugin_page = false;







	private $features = [];






	private $styles;






	private $scripts;






	private $debug_mode;







	private $reseller_mode;







	private $class;






	private $theme;






	private $parent_theme;







	private $stylesheet_dir;







	private $stylesheet_uri;







	private $template_dir;







	private $template_uri;







	private $drone_dir;







	private $drone_uri;







	private $posts_stack = [];






	protected function onLoad() { }











	protected function onSetupOptions(\Drone\Options\Group\Theme $theme_options)
	{
		$this->includeFile(self::OPTIONS_SETUP_FILENAME, compact('theme_options'));
	}










	public function onThemeOptionsCompatybility(array &$data, $version) { }











	public function onPostOptionsCompatybility(array &$data, $version, $post_type) { }






	protected function onSetupTheme() { }






	protected function onInit() { }






	protected function onWidgetsInit() { }









	protected function onSavePost($post_id, $post_type) { }










	private function getDocComments($filename, $scope = [T_PRIVATE, T_PROTECTED, T_PUBLIC, T_ABSTRACT])
	{

		if (!Func::wpFilesystem()->exists($filename)) {
			return false;
		}

		if (($file = Func::wpFilesystem()->get_contents($filename)) === false) {
			return false;
		}

		$scope  = (array)$scope;
		$tokens = token_get_all($file);
		$tokens_count = count($tokens);

		$doccomments = [];

		for ($i = 0; $i < $tokens_count; ++$i) {
			if (
				isset($tokens[$i+0][0]) && $tokens[$i+0][0] == T_DOC_COMMENT &&
				isset($tokens[$i+1][0]) && $tokens[$i+1][0] == T_WHITESPACE &&
				isset($tokens[$i+2][0]) && in_array($tokens[$i+2][0], $scope) &&
				isset($tokens[$i+3][0]) && $tokens[$i+3][0] == T_WHITESPACE &&
				isset($tokens[$i+4][0]) && $tokens[$i+4][0] == T_FUNCTION &&
				isset($tokens[$i+5][0]) && $tokens[$i+5][0] == T_WHITESPACE &&
				isset($tokens[$i+6][0]) && $tokens[$i+6][0] == T_STRING &&
				isset($tokens[$i+0][1]) && isset($tokens[$i+6][1])
			) {
				$doccomments[$tokens[$i+6][1]] = $tokens[$i+0][1];
			}
		}

		return $doccomments;

	}










	private function includeFile($filename, array $params = [])
	{

		if (!file_exists($__path = $this->template_dir . '/inc/' . $filename) &&
			!file_exists($__path = $this->template_dir . '/' . $filename)) {
			return false;
		}

		extract($params);
		require_once $__path;

		return true;

	}










	private function getUpdateURL($action, $ticket = '')
	{
		$params = [
			$action,
			VERSION,
			$this->base_theme->id,
			$this->base_theme->version ? $this->base_theme->version : '1',
			$this->sysinfo->value('purchase_code')
		];
		if ($action == 'download' && $params[4]) {
			$params[] = $ticket;
		}
		return apply_filters('update_url', self::UPDATE_URL) . rtrim(implode('/', $params), '/ ');
	}






	protected function __construct()
	{


		$this->start_time = microtime(true);
		$this->beginMarker(__METHOD__);


		$this->class = get_class($this);


		$this->theme      = wp_get_theme();
		$this->theme->id  = Func::stringID($this->theme->name);
		$this->theme->id_ = Func::stringID($this->theme->name, '_');


		if (($parent = $this->theme->parent()) !== false) {
			$this->parent_theme      = $parent;
			$this->parent_theme->id  = Func::stringID($parent->name);
			$this->parent_theme->id_ = Func::stringID($parent->name, '_');
		}


		$this->stylesheet_dir = get_stylesheet_directory();
		$this->stylesheet_uri = get_stylesheet_directory_uri();
		$this->template_dir   = get_template_directory();
		$this->template_uri   = get_template_directory_uri();
		$this->drone_dir      = $this->template_dir . '/' . DIRECTORY;
		$this->drone_uri      = $this->template_uri . '/' . DIRECTORY;


		add_action('after_setup_theme',  [$this, '__actionAfterSetupTheme']);
		add_action('init',               [$this, '__actionInit']);
		add_action('after_switch_theme', [$this, '__actionAfterSwitchTheme']);


		$this->endMarker(__METHOD__);

	}









	public function __get($name)
	{

		switch ($name) {

			case 'base_theme':
				return $this->parent_theme === null ? $this->theme : $this->parent_theme;

			case 'version':
				return $this->parent_theme === null ? $this->theme->version : rtrim($this->parent_theme->version . '-child-' . $this->theme->version, '-');

			case 'wp_version':
				return get_bloginfo('version');

			case 'store_page_uri':
				return $this->base_theme->get('ThemeURI') ?: self::HOMEPAGE_URL;

			case 'debug_mode':
			case 'reseller_mode':
			case 'class':
			case 'theme':
			case 'parent_theme':
			case 'stylesheet_dir':
			case 'stylesheet_uri':
			case 'template_dir':
			case 'template_uri':
			case 'drone_dir':
			case 'drone_uri':
			case 'posts_stack':
				return $this->{$name};

		}

	}






	public function __actionAfterSetupTheme()
	{


		$this->beginMarker(__METHOD__);


		load_theme_textdomain('everything', $this->template_dir . '/' . self::WP_LANGUAGES_DIRECTORY);


		$this->beginMarker($this->class . '::onLoad');
		$this->onLoad();
		$this->endMarker($this->class . '::onLoad');


		$this->reseller_mode = apply_filters('reseller_mode', false);


		if (apply_filters('enable_update', true) && !$this->reseller_mode) {
			add_filter('http_headers_useragent',               [$this, '__filterHTTPHeadersUseragent']);
			add_filter('pre_set_site_transient_update_themes', [$this, '__filterPreSetSiteTransientUpdateThemes']);
		}


		$this->theme_options = new Options\Group\Theme($this->theme->id_);
		$this->theme_options_array = get_option(apply_filters('theme_options_id', $this->theme->id_), []);

		if (is_admin() && current_user_can('edit_theme_options')) {


			if (isset($_POST['settings_export'], $_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'settings_export')) {

				$settings = $this->theme_options_array;
				unset($settings[Options\Group\Sysinfo::SLUG]);

				$filename = Func::stringID(sprintf(__('%s theme options settings', 'everything'), $this->theme->name), '.') . '.' . date('Y-m-d') . '.json';

		        header('Content-Type: application/force-download; charset=' . get_option('blog_charset'));
		        header('Content-Disposition: attachment; filename="' . $filename . '"');

				exit(json_encode($settings));

			} else if (isset($_POST['settings_import'], $_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'settings_import')) {

				if (!is_uploaded_file($filename = $_FILES['settings_import_file']['tmp_name'])) {
					header('Location: ' . $_SERVER['REQUEST_URI'] . '&settings_import=no_file');
					die;
				}

				$settings = json_decode(Func::wpFilesystem()->get_contents($filename), true);

				if (!isset($settings[Options\Group\Theme::VERSION_KEY][1]) || version_compare($settings[Options\Group\Theme::VERSION_KEY][1], $this->base_theme->version) > 0) {
					header('Location: ' . $_SERVER['REQUEST_URI'] . '&settings_import=wrong_version');
					die;
				}

				update_option(apply_filters('theme_options_id', $this->theme->id_), $settings);
				header('Location: ' . $_SERVER['REQUEST_URI'] . '&settings_import=success');
				die;

			}


			if (isset($_GET['instagram_response'], $_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'instagram_auth') === 1) {

				if (($instagram_response = json_decode(stripslashes($_GET['instagram_response']), true)) !== null) {

					$request_uri = remove_query_arg(['instagram_response', '_wpnonce'], $_SERVER['REQUEST_URI']);

					if (isset($instagram_response['access_token'])) {
						update_option($this->theme->id_ . '_instagram_app', $instagram_response);
						header('Location: ' . add_query_arg('instagram_bind', 'success', $request_uri));
					} else if (isset($instagram_response['error_message'])) {
						header('Location: ' . add_query_arg('instagram_bind', urlencode($instagram_response['error_message']), $request_uri));
					} else {
						header('Location: ' . add_query_arg('instagram_bind', 'error', $request_uri));
					}

					die;

				}

			} else if (isset($_GET['instagram_unbind'], $_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'instagram_unbind') === 1) {

				delete_option($this->theme->id_ . '_instagram_app');
				header('Location: ' . remove_query_arg(['instagram_unbind', '_wpnonce'], $_SERVER['REQUEST_URI']));
				die;

			}

		}


		if (extension_loaded('eAccelerator') || extension_loaded('SourceGuardian')) {
			$doccomments = $this->getDocComments($this->template_dir . '/' . self::WP_FUNCTIONS_FILENAME, T_PUBLIC);
		}

		$rc = new \ReflectionClass($this->class);
		foreach ($rc->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {


			if ($method->class != $this->class) {
				continue;
			}


			if (isset($doccomments[$method->name])) {
				$phpdoc = $doccomments[$method->name];
			} else if (($phpdoc = $method->getDocComment()) === false) {
				continue;
			}


			if (!preg_match_all('/@internal (?P<type>action|filter|shortcode):(?P<data>.+)$/im', $phpdoc, $phpdoc_matches, PREG_SET_ORDER)) {
				continue;
			}

			foreach ($phpdoc_matches as $phpdoc_match) {

				$phpdoc_data = array_map('trim', explode(',', $phpdoc_match['data']));
				if (empty($phpdoc_data[0])) {
					continue;
				}

				switch (strtolower($phpdoc_match['type'])) {
					case 'action':
					case 'filter':
						add_filter(
							$phpdoc_data[0],
							[$this, $method->name],
							isset($phpdoc_data[1]) ? (int)$phpdoc_data[1] : self::WP_FILTER_PRIORITY_DEFAULT,
							isset($phpdoc_data[2]) ? (int)$phpdoc_data[2] : $method->getNumberOfParameters()
						);
						break;
					case 'shortcode':
						add_shortcode($phpdoc_data[0], [$this, $method->name]);
						break;
				}

			}

		}


		if ($this->includeFile(self::SHORTCODES_INC_FILENAME)) {
			foreach (get_declared_classes() as $class) {
				if (strpos($class, $this->class . '\Shortcodes\Shortcode\\') === 0) {
					new $class();
				}
			}
			add_filter('the_content',                   [$this, '__filterShortcodeParent'], 1);
			add_filter('woocommerce_short_description', [$this, '__filterShortcodeParent'], 1);
		}


		$this->includeFile(self::OPTIONS_INC_FILENAME);


		$this->beginMarker($this->class . '::onSetupOptions');
		self::$setup_options_lock = true;
		$this->onSetupOptions($this->theme_options);
		do_action('theme_on_setup_options', $this->theme_options, $this);
		$this->theme_options->addChild($this->sysinfo = new Options\Group\Sysinfo());
		self::$setup_options_lock = false;
		$this->endMarker($this->class . '::onSetupOptions');


		$this->beginMarker(get_class($this->theme_options) . '::fromArray');
		$this->theme_options->fromArray($this->theme_options_array, [$this, 'onThemeOptionsCompatybility']);
		$this->endMarker(get_class($this->theme_options) . '::fromArray');
		do_action('theme_on_load_options', $this->theme_options, $this);


		$this->debug_mode = $this->sysinfo->value('debug_mode') && !$this->reseller_mode;


		add_theme_support('html5', ['comment-list', 'comment-form', 'search-form', 'gallery', 'caption']);
		add_theme_support('title-tag');
		add_theme_support('automatic-feed-links');


		$this->beginMarker($this->class . '::onSetupTheme');
		$this->onSetupTheme();
		$this->endMarker($this->class . '::onSetupTheme');


		add_action('widgets_init',       [$this, '__actionWidgetsInit']);

		add_action('wp_enqueue_scripts', [$this, '__actionWPEnqueueScripts']);
		add_action('wp_head',            [$this, '__actionWPHead']);
		add_action('wp_footer',          [$this, '__actionWPFooter'], 100);
		add_action('wp_footer',          [$this, '__actionDebugFooter'], 1000);
		add_action('admin_menu',         [$this, '__actionAdminMenu']);


		add_action('wp_enqueue_scripts', ['\Drone\Options\Option\Font', '__actionWPEnqueueScripts'], 5);
		add_action('vc_before_init',     ['\Drone\Shortcodes\Shortcode', '__actionVCBeforeInit']);


		add_filter('the_posts',  [$this, '__filterThePosts']);
		add_filter('body_class', [$this, '__filterBodyClass']);


		$this->endMarker(__METHOD__);

	}






	public function __actionWidgetsInit()
	{


		$this->beginMarker(__METHOD__);


		if ($this->includeFile(self::WIDGETS_INC_FILENAME)) {
			foreach (get_declared_classes() as $class) {
				if (strpos($class, $this->class . '\Widgets\Widget\\') === 0) {
					register_widget('\\' . $class);
				}
			}
		}


		$this->beginMarker($this->class . '::onWidgetsInit');
		$this->onWidgetsInit();
		$this->endMarker($this->class . '::onWidgetsInit');


		$this->endMarker(__METHOD__);

	}






	public function __actionInit()
	{


		$this->beginMarker(__METHOD__);


		$locale = get_locale();
		if (strpos($locale, '_') === false) {
			$locale = strtolower($locale) . '_' . strtoupper($locale);
		}


		wp_register_script($this->theme->id . '-social-media-api', $this->drone_uri . '/js/social-media-api.js', ['jquery'], VERSION, true);
		wp_localize_script($this->theme->id . '-social-media-api', 'drone_social_media_api', [
			'locale' => $locale
		]);


		$this->beginMarker($this->class . '::onInit');
		$this->onInit();
		$this->endMarker($this->class . '::onInit');


		$this->endMarker(__METHOD__);

	}






	public function __actionAfterSwitchTheme()
	{
		flush_rewrite_rules();
	}






	public function __actionAdminMenu()
	{


		$this->beginMarker(__METHOD__);


		$this->plugin_page = isset($GLOBALS['plugin_page']) && strpos($GLOBALS['plugin_page'], $this->theme->id) === 0 ? substr($GLOBALS['plugin_page'], strlen($this->theme->id)+1) : false;


		wp_register_style($this->theme->id . '-options',           $this->drone_uri . '/css/options.css',                  [], VERSION);
		wp_register_style($this->theme->id . '-shortcode-options', $this->drone_uri . '/css/shortcode-options/styles.css', [], VERSION);

		wp_register_script($this->theme->id . '-jscolor',     $this->drone_uri . '/ext/jscolor/jscolor.min.js', [],                                                                              '2.0.4');
		wp_register_script($this->theme->id . '-options',     $this->drone_uri . '/js/options.js',              ['jquery', 'jquery-ui-sortable', $this->theme->id . '-jscolor', 'media-upload'], VERSION);
		wp_register_script($this->theme->id . '-update-core', $this->drone_uri . '/js/update-core.js',          ['jquery'],                                                                      VERSION);


		add_action('admin_notices',         [$this, '__actionAdminNotices']);
		add_action('add_meta_boxes',        [$this, '__actionAddMetaBoxes']);
		add_action('save_post',             [$this, '__actionSavePost']);
		add_action('print_media_templates', [$this, '__actionPrintMediaTemplates']);
		add_action('admin_footer',          [$this, '__actionDebugFooter'], 1000);

		add_action('admin_enqueue_scripts', [$this, '__actionAdminEnqueueScripts']);
		add_action('admin_print_styles',    [$this, '__actionAdminPrintStyles']);
		add_action('admin_print_scripts',   [$this, '__actionAdminPrintScripts']);

		add_action('admin_print_scripts-update-core.php', [$this, '__actionAdminPrintScriptsUpdateCore']);


		if ($this->theme_options->count() > 0) {
			$theme_options_childs = array_filter($this->theme_options->childs(), function ($child) { return $child->isIncluded(); });
			$theme_options_keys   = array_keys($theme_options_childs);
			$menu_slug            = $this->theme->id . '-' . $theme_options_keys[0];
			$label = __('Theme Options', 'everything');
			if (($errors = $this->theme_options->errorsCount()) > 0) {
				$label .= sprintf(' <span class="update-plugins count-%1$d" title="%2$s"><span class="update-count">%1$d</span></span>', $errors, '');
			}
			call_user_func_array('add_' . self::OPTIONS_PAGE_TYPE . '_page', [
				sprintf(__('%s options', 'everything'), $this->theme->name),
				$label,
				'edit_theme_options',
				$menu_slug,
				null,
				'dashicons-screenoptions'
			]);
			foreach ($theme_options_childs as $name => $child) {
				$label = $child->label;
				if (($errors = $child->errorsCount()) > 0) {
					$label .= sprintf(' <span class="update-plugins count-%1$d" title="%2$s"><span class="update-count">%1$d</span></span>', $errors, '');
				}
				$hook_suffix = call_user_func_array('add_' . self::OPTIONS_SUBPAGE_TYPE . '_page', [
					$menu_slug,
					sprintf(__('%s options', 'everything'), $child->label),
					$label,
					'edit_theme_options',
					$this->theme->id . '-' . $name,
					[$this, '__callbackThemeOptions']
				]);
				add_action('admin_print_styles-' . $hook_suffix,  [$this, '__actionAdminPrintStylesOptions']);
				add_action('admin_print_scripts-' . $hook_suffix, [$this, '__actionAdminPrintScriptsOptions']);
				add_action('admin_head-' . $hook_suffix,          [$this, '__actionAdminHeadThemeOptions']);
			}
		}


		add_action('admin_print_styles-post.php',      [$this, '__actionAdminPrintStylesOptions']);
		add_action('admin_print_styles-post-new.php',  [$this, '__actionAdminPrintStylesOptions']);
		add_action('admin_print_scripts-post.php',     [$this, '__actionAdminPrintScriptsOptions']);
		add_action('admin_print_scripts-post-new.php', [$this, '__actionAdminPrintScriptsOptions']);
		add_action('admin_head-post.php',              [$this, '__actionAdminHeadPostOptions']);
		add_action('admin_head-post-new.php',          [$this, '__actionAdminHeadPostOptions']);


		add_action('admin_print_styles-widgets.php',  [$this, '__actionAdminPrintStylesOptions']);
		add_action('admin_print_scripts-widgets.php', [$this, '__actionAdminPrintScriptsOptions']);
		add_action('admin_head-widgets.php',          [$this, '__actionAdminHeadWidgetOptions']);


		if ((get_user_option('rich_editing') == 'true') && (current_user_can('edit_posts') || current_user_can('edit_pages'))) {
			add_action('before_wp_tiny_mce',     ['\Drone\Shortcodes\Shortcode', '__actionBeforeWPTinyMCE']);
			add_filter('tiny_mce_before_init',   ['\Drone\Shortcodes\Shortcode', '__filterTinyMCEBeforeInit']);
			add_filter('mce_external_plugins',   [$this, '__filterMCEExternalPlugins']);
			add_filter('mce_external_languages', [$this, '__filterMCEExternalLanguages']);
			add_filter('mce_css',                [$this, '__filterMCECSS']);
			add_filter('mce_buttons',            [$this, '__filterMCEButtons']);
		}


		if ($this->reseller_mode) {
			add_filter('wp_prepare_themes_for_js', [$this, '__filterWPPrepareThemesForJS']);
		}


		if ($this->isIllegal()) {
			$message = sprintf(__('Your theme comes from unauthorized source and might include viruses or malicious code. Use <a href="%s">official theme</a> version only.', 'everything'), $this->store_page_uri);
			add_settings_error($this->theme->id_, 'illegal', $message, 'error');
		}


		if (isset($_GET['settings_import'])) {
			switch ($_GET['settings_import']) {
				case 'success':
					add_settings_error($this->theme->id_, 'settings_import_success', __('Theme Options imported successfully.', 'everything'), 'updated');
					break;
				case 'no_file':
					add_settings_error($this->theme->id_, 'settings_import_no_file', __('No file was selected for import.', 'everything'), 'error');
					break;
				case 'wrong_version':
					add_settings_error($this->theme->id_, 'settings_import_wrong_version', __('Mismatched version number of the theme.', 'everything'), 'error');
					break;
				default:
					add_settings_error($this->theme->id_, 'settings_import_error', __('File could not be imported.', 'everything'), 'error');
			}
		}


		if (isset($_GET['instagram_bind'])) {
			switch ($_GET['instagram_bind']) {
				case 'success':
					add_settings_error($this->theme->id_, 'instagram_auth_success', __('Instagram app authorized successfully.', 'everything'), 'updated');
					break;
				case 'error':
					add_settings_error($this->theme->id_, 'instagram_auth_error', __('Unknown authorization error occurred.', 'everything'), 'error');
					break;
				default:
					if ($_GET['instagram_bind']) {
						$message = strip_tags(stripslashes($_GET['instagram_bind']));
						add_settings_error($this->theme->id_, 'instagram_auth_fail', sprintf(__('Authorization error occurred: %s', 'everything'), $message), 'error');
					}
			}
		}


		register_setting($this->theme->id_, apply_filters('theme_options_id', $this->theme->id_), [$this, '__callbackThemeOptionsSanitize']);


		$this->endMarker(__METHOD__);

	}






	public function __actionAdminNotices()
	{
		settings_errors($this->theme->id_);
	}









	public function __actionAdminEnqueueScripts($hook)
	{
		wp_enqueue_style($this->theme->id . '-shortcode-options');
	}







	public function __actionAdminPrintStyles()
	{
	}







	public function __actionAdminPrintScripts()
	{
	}






	public function __actionAdminPrintStylesOptions()
	{
		wp_enqueue_style($this->theme->id . '-options');
		wp_enqueue_style('dashicons');
	}






	public function __actionAdminPrintScriptsOptions()
	{
		wp_enqueue_media();
		wp_enqueue_script($this->theme->id . '-options');
	}






	public function __actionAdminPrintScriptsUpdateCore()
	{

		if (!current_user_can('update_themes') || ($update_themes = get_site_transient('update_themes')) === false) {
			return;
		}

		$template = get_option('template');

		if (!isset($update_themes->response[$template])) {
			return;
		}
		$update = $update_themes->response[$template];


		if (isset($update['error']) && $update['error']) {
			$errors = [
				'no_purchase_code'      => __('To enable this update please paste your purchase code in <a href="%s">Theme Options / System</a>.', 'everything'),
				'invalid_purchase_code' => __('Invalid purchase code. Please check <a href="%s">Theme Options / System</a>.', 'everything'),
				'banned_purchase_code'  => __('Abused purchase code in <a href="%s">Theme Options / System</a>. Please use unique purchase code for each site.', 'everything')
			];
			$notice = isset($errors[$update['error']]) ?
				sprintf($errors[$update['error']], menu_page_url($this->theme->id . '-' . Options\Group\Sysinfo::SLUG, false)) :
				sprintf(__('Unknown error (%s).', 'everything'), $update['error']);
		}


		else if (isset($update['php_version']) && version_compare($update['php_version'], PHP_VERSION) > 0) {
			$notice = sprintf(
				__('Upcoming theme update requires at least PHP %1$s. Your server uses version %2$s. Please update PHP on your server.', 'everything'),
				$update['php_version'], PHP_VERSION
			);
		}


		else if (isset($update['wp_version']) && version_compare($update['wp_version'], $this->wp_version) > 0) {
			$notice = sprintf(
				__('Upcoming theme update requires at least WordPress %1$s. You use version %2$s. Please update WordPress first.', 'everything'),
				$update['wp_version'], $this->wp_version
			);
		}

		else {
			return;
		}


		wp_enqueue_script($this->theme->id . '-update-core');
		wp_localize_script($this->theme->id . '-update-core', 'drone_update_core', [
			'template' => $template,
			'notice'   => $notice
		]);

	}






	public function __actionAdminHeadThemeOptions()
	{


		if (($group = $this->theme_options->child($this->plugin_page)) !== null && $styles = $group->styles()) {
			echo "<style>\n" . Func::minify('css', $styles) . "\n</style>\n";
		}


		if (($group = $this->theme_options->child($this->plugin_page)) !== null && $scripts = $group->scripts()) {
			echo "<script>\n" . Func::minify('js', $scripts) . "\n</script>\n";
		}

	}






	public function __actionAdminHeadPostOptions()
	{

		if (($post_options = $this->getPostOptions()) === null) {
			return;
		}


		$styles = [$post_options->styles()];
		foreach (Shortcode::instances(Shortcode::VISIBILITY_ALL) as $shortcode) {
			$styles[] = $shortcode->options->styles();
		}
		if ($styles) {
			echo "<style>\n" . Func::minify('css', implode("\n", $styles)) . "\n</style>\n";
		}


		if ($scripts = $post_options->scripts()) {
			echo "<script>\n" . Func::minify('js', $scripts) . "\n</script>\n";
		}

	}






	public function __actionAdminHeadWidgetOptions()
	{

	}






	public function __actionAddMetaBoxes()
	{

		if (($post_options = $this->getPostOptions()) === null) {
			return;
		}

		foreach ($post_options->childs('group') as $name => $group) {
			add_meta_box(
				Func::stringID($name), $group->label,
				function () use ($group) {
					require Theme::instance()->drone_dir . '/tpl/post-options.php';
				},
				null, $group->context, $group->priority
			);
		}

	}






	public function __actionPrintMediaTemplates()
	{
		if (($gallery = Shortcode::instance('gallery')) === null || $gallery->options->count() == 0) {
			return;
		}
		$options  = $gallery->options;
		$defaults = $gallery->options->getDefaults();
		unset(
			$defaults['order'],
			$defaults['orderby'],
			$defaults['id'],
			$defaults['columns'],
			$defaults['size'],
			$defaults['ids'],
			$defaults['include'],
			$defaults['exclude'],
			$defaults['link']
		);
		require $this->drone_dir . '/tpl/gallery-options.php';
	}








	public function __actionSavePost($post_id)
	{


		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}


		if (!current_user_can('edit_post', $post_id)) {
			return;
		}


		if (isset($_POST[$this->base_theme->id_])) {
			$post_options = $this->getPostOptions((int)$post_id);
			foreach ($post_options->childs('group') as $child) {
				$noncename = $child->attr_name . '_wpnonce';
				if (!isset($_POST[$noncename]) || !wp_verify_nonce($_POST[$noncename], $child->attr_name)) {
					return;
				}
			}
			$post_options->change(wp_unslash($_POST[$this->base_theme->id_]));
			update_post_meta($post_id, apply_filters('post_options_id', '_' . $this->base_theme->id_), $post_options->toArray());
		}


		$this->beginMarker($this->class . '::onSavePost');
		$this->onSavePost((int)$post_id, get_post_type($post_id));
		$this->endMarker($this->class . '::onSavePost');

	}








	public function __actionThePost(&$post)
	{
		$this->posts_stack[] = $post->ID;
	}






	public function __actionWPEnqueueScripts()
	{
		if (!empty($this->scripts['header']['jquery']) || !empty($this->scripts['footer']['jquery'])) {
			wp_enqueue_script('jquery');
		}
	}






	public function __actionWPHead()
	{


		$this->beginMarker(__METHOD__);


		if ($this->styles) {
			echo "<style>\n" . Func::minify('css', implode("\n", $this->styles)) . "\n</style>\n";
		}


		$scripts = !empty($this->scripts['header']['js']) ? $this->scripts['header']['js'] : [];

		if (!empty($this->scripts['header']['jquery'])) {
			$scripts[] = "(function($) {\n$(document).ready(function($) {\n" . implode("\n", $this->scripts['header']['jquery']) . "\n});\n})(jQuery);";
		}

		if ($scripts) {
			echo "<script>\n" . Func::minify('js', implode("\n", $scripts)) . "\n</script>\n";
		}


		$this->endMarker(__METHOD__);

	}






	public function __actionWPFooter()
	{


		$this->beginMarker(__METHOD__);


		$scripts = !empty($this->scripts['footer']['js']) ? $this->scripts['footer']['js'] : [];

		if (!empty($this->scripts['footer']['jquery'])) {
			$scripts[] = "(function($) {\n$(document).ready(function($) {\n" . implode("\n", $this->scripts['footer']['jquery']) . "\n});\n})(jQuery);";
		}

		if ($scripts) {
			echo "<script>\n" . Func::minify('js', implode("\n", $scripts)) . "\n</script>\n";
		}


		$this->endMarker(__METHOD__);

	}






	public function __actionDebugFooter()
	{


		if ($this->debug_mode) {
			usort($this->debug_log, function ($a, $b) {
				return round($a['start_time']*1000+$a['nest'] - ($b['start_time']*1000+$b['nest']));
			});
			echo "\n<!--\n\n";
			require $this->drone_dir . '/odd/signature.php';
			echo "\n";
			foreach ($this->debug_log as $entry) {
				printf(
					"\t| %4dms | %5.2fmb | %-48s | %3dms | %5.2fmb |\n",
					($entry['start_time']-$this->start_time)*1000,
					$entry['start_memory'] / (1024*1024),
					str_repeat('+ ', $entry['nest']) . $entry['name'],
					($entry['end_time']-$entry['start_time'])*1000,
					($entry['end_memory'] - $entry['start_memory']) / (1024*1024)
				);
			}
			echo "\n-->\n";
		}

	}







	public function __actionOGP()
	{


		if (!isset($this->features['ogp'])) {
			return;
		}
		$options = $this->features['ogp']['options'];


		if (!$options->value('enabled')) {
			return;
		}


		$this->beginMarker(__METHOD__);


		$ogp['site_name'] = get_bloginfo('name');


		$ogp['title'] = wp_get_document_title();


		$ogp['locale'] = str_replace('-', '_', get_bloginfo('language'));

		if (is_singular() && !is_front_page()) {

			$post = get_post();


			$ogp['url'] = esc_url(\apply_filters('the_permalink', get_permalink($post->ID)));


			$description = $post->post_excerpt ? $post->post_excerpt : preg_replace('/\[\/?.+?\]/', '', $post->post_content);
			$description = preg_replace('/<(style|script).*>.*<\/\1>/isU', '', $description);
			$description = trim(strip_tags(preg_replace('/\s+/', ' ', $description)));
			$description = Func::stringCut($description, 250, ' [...]');
			$ogp['description'] = $description;


			if (!$ogp['image'] = get_the_post_thumbnail_url($post, 'large')) {
				if (preg_match('/<img[^>]* src=[\'"]([^\'"]+)[\'"]/i', $post->post_content, $m)) {
					$ogp['image'] = $m[1];
				}
			}

		} else {


			$ogp['url'] = home_url('/');


			$ogp['description'] = get_bloginfo('description');

		}


		if (empty($ogp['image'])) {
			$ogp['image'] = $options->value('image');
		}


		$output = HTML::make();
		foreach ($ogp as $property => $content) {
			if ($content) {
				$output->addNew('meta')->property('og:' . $property)->content($content);
			}
		}
		echo $output;


		$this->endMarker(__METHOD__);

	}







	public function __actionWPAjaxContactForm()
	{


		if (!isset($this->features['contact-form'])) {
			exit;
		}
		$contact_form = $this->features['contact-form'];


		$options = $contact_form['options'];


		$output = function ($result, $message) use ($contact_form) {
			echo json_encode([
				$contact_form['result_var']  => $result,
				$contact_form['message_var'] => $message
			]);
			exit;
		};


		$values = [];
		foreach ($options->value('fields') as $field) {
			$value = isset($_POST[$field]) ? trim(strip_tags($_POST[$field])) : '';
			switch ($field) {
				case 'name':
					if (empty($value)) {
						$output(false, __('Please enter your name', 'everything'));
					}
					break;
				case 'email':
					if (!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$/i', $value)) {
						$output(false, __('Invalid email address', 'everything'));
					}
					break;
				case 'website':
					if (!empty($value) && !preg_match('|^(https?://)?(www\.)?([-_a-z0-9]+\.)+[-_a-z0-9]+$|i', $value)) {
						$output(false, __('Invalid website address', 'everything'));
					}
					break;
				case 'phone':
					if (!empty($value) && !preg_match('/^[-_#\+\*\(\)0-9 ]+$/', $value)) {
						$output(false, __('Invalid phone number', 'everything'));
					}
					break;
				case 'message':
					if (strlen($value) < 3) {
						$output(false, __('Please write your message', 'everything'));
					}
					break;
				case 'captcha':
					if (\apply_filters('hctpc_verify', true) !== true) {
						$output(false, __('Please complete the captcha', 'everything'));
					}
					break;
			}
			$values[$field] = $value;
		}


		$to = $options->value('to');
		switch ($options->value('from')) {
			case 'to':    $from = $to; break;
			case 'field': $from = $values['email']; break;
			default:      $from = get_option('admin_email');
		}
		$reply_to = $values['email'];


		$author = isset($values['name']) ? $values['name'] : '';


		$subject = $options->value('subject');
		$subject = str_replace(['%blogname%', '%blogurl%'], [get_bloginfo('name'), home_url('/')], $subject);
		$subject = preg_replace_callback('/%([a-z]+)%/i', function ($m) use ($values) {
			return isset($values[$m[1]]) ? $values[$m[1]] : '';
		}, $subject);
		$subject = wp_specialchars_decode(trim(str_replace(["\r", "\n"], ' ', $subject)));


		$message =
			"{$values['message']}\r\n\r\n---\r\n" .
			implode("\r\n", array_intersect_key(
				$values,
				array_flip(array_intersect($options->value('fields'), ['name', 'email', 'website', 'phone']))
			));


		if ($options->child('settings')->value('akismet') && function_exists('akismet_get_key') && akismet_get_key()) {
			$comment = [
				'blog'         => home_url('/'),
				'blog_lang'    => get_locale(),
				'blog_charset' => get_option('blog_charset'),
				'user_ip'      => $_SERVER['REMOTE_ADDR'],
				'user_agent'   => $_SERVER['HTTP_USER_AGENT'],
				'referrer'     => $_SERVER['HTTP_REFERER'],
				'comment_type' => 'contactform'
			];
			if (isset($values['name'])) {
				$comment['comment_author'] = $values['name'];
			}
			if (isset($values['email'])) {
				$comment['comment_author_email'] = $values['email'];
			}
			if (isset($values['comment_author_url'])) {
				$comment['comment_author_email'] = $values['website'];
			}
			if (isset($values['message'])) {
				$comment['comment_content'] = $values['message'];
			}
			foreach ($_SERVER as $key => $value) {
				if (!in_array($key, ['HTTP_COOKIE', 'HTTP_COOKIE2', 'PHP_AUTH_PW']) && is_string($value)) {
					$comment[$key] = $value;
				} else {
					$comment[$key] = '';
				}
			}
			$query_string = Func::arraySerialize(array_map('stripslashes', $comment));
			$response = akismet_http_post($query_string, $GLOBALS['akismet_api_host'], '/1.1/comment-check', $GLOBALS['akismet_api_port']);
			if ($response[1] == 'true') {
				$output(false, __('Your message is recognized as spam.', 'everything'));
			}
		}


		$result = wp_mail(
			$to, $subject, $message,
			($options->child('settings')->value('from_header') ? "From: \"{$author}\" <{$from}>\r\n" : '') .
			"Reply-to: {$reply_to}\r\n" .
			"Content-type: text/plain; charset=\"" . get_bloginfo('charset') . "\"\r\n"
		);
		if ($result) {
			$output(true, __('Message sent', 'everything'));
		} else {
			$output(false, __('Error occured', 'everything'));
		}

	}









	public function __filterThePosts($posts)
	{
		$this->posts_stack = array_merge($this->posts_stack, array_map(function ($post) {
			return $post->ID;
		}, $posts));
		return $posts;
	}









	public function __filterBodyClass($classes)
	{
		array_unshift($classes, $this->theme->id . '-' . ($this->version ? str_replace('.', '-', $this->version) : 'unknown'));
		if ($this->isIllegal()) {
			$classes[] = 'illegal';
		}
		if ($this->debug_mode) {
			$classes[] = 'debug-mode';
		}
		return $classes;
	}









	public function __filterForceImgCaptionShortcodeFilter($content)
	{
  		return preg_replace_callback(
			'#(?P<caption>\[caption[^\]]*\])?(?:<p[^>]*>)?(?P<content>(?:<a [^>]+>)?<img [^>]+>(?:</a>)?)(?:</p>)?#i',
			[$this, '__filterForceImgCaptionShortcodeFilterCallback'], $content
		);
	}









	protected function __filterForceImgCaptionShortcodeFilterCallback($matches)
	{


		if ($matches['caption']) {
			return $matches[0];
		}


		$attr = [
			'id'      => '',
			'align'   => 'alignnone',
			'width'   => '',
			'caption' => '',
			'class'   => ''
		];
		$content = trim($matches['content']);

		if (preg_match('/<img [^>]*(class="([^"]*)")/i', $content, $m)) {

			list (, $class_attr, $class) = $m;


			if (preg_match('/\bwp-image-([0-9]+)\b/i', $class, $m)) {
				$attr['id'] = 'attachment_' . $m[1];
			}


			if (preg_match('/\b(align(?:none|left|right|center))\b/i', $class, $m)) {
				$attr['align'] = strtolower($m[1]);
				$content = str_replace($class_attr, preg_replace('/\b' . $attr['align'] . '\b/i', '', $class_attr), $content);
			}

		}


		if (preg_match('/width="([0-9]+)"/i', $content, $m)) {
			if (($attr['width'] = $m[1]) <= 1) {
				return $matches[0];
			}
		}

		$output = \apply_filters('img_caption_shortcode', '', $attr, $content);

		return $output != '' ? $output : $matches[0];

	}









	public function __filterShortcodeParent($content)
	{


		$this->beginMarker(__METHOD__);


		if (Shortcode::instance('no_format') !== null && stripos($content, '[/no_format]') !== false) {
			$no_format_blocks = [];
			$content = preg_replace_callback('#\[no_format(?: [^\]]*)?\].*?\[/no_format\]#is', function ($m) use (&$no_format_blocks) {
				$hash = md5($m[0]);
				$no_format_blocks[$hash] = $m[0];
				return "<!-- no_format:{$hash} -->";
			}, $content);
		}

		foreach (Shortcode::instances() as $shortcode) {


			if ($shortcode->parent === null || stripos($content, "[/{$shortcode->tag}]") === false) {
				continue;
			}


			$split_preg = '\[' . $shortcode->parent->tag . '(?: [^\]]*)?\].*?\[/' . $shortcode->parent->tag . '\]';
			$content_parts = preg_split('#(' . $split_preg . ')#is', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
			foreach ($content_parts as &$content_part) {
				if (preg_match('#^' . $split_preg . '$#is', $content_part)) {
					continue;
				}
				$content_part = preg_replace_callback(
					'#(\s*\[' . $shortcode->tag . '( [^\]]*)?\].*?\[/' . $shortcode->tag . '\]\s*)+#is',
					function ($m) use ($shortcode) {
						return preg_replace('/^(\s*)(.*?)(\s*)$/s', "\\1[{$shortcode->parent->tag}]\n\n\\2\n\n[/{$shortcode->parent->tag}]\\3", $m[0]);
					},
					$content_part
				);
			}
			unset($content_part);
			$content = implode('', $content_parts);

		}


		if (isset($no_format_blocks)) {
			foreach ($no_format_blocks as $hash => $block) {
				$content = str_replace("<!-- no_format:{$hash} -->", $block, $content);
			}
		}


		$this->endMarker(__METHOD__);

		return $content;

	}









	public function __filterHTTPHeadersUseragent($user_agent)
	{
		return sprintf('WordPress/%s; PHP/%s; %s', $this->wp_version, PHP_VERSION, home_url('/'));
	}











	public function __filterPreSetSiteTransientUpdateThemes($transient)
	{

		if (empty($transient->checked)) {
			return $transient;
		}


		$update = $this->getTransient('update', function (&$expiration, $outdated_value) {

			$expiration = apply_filters('update_interval', Theme::UPDATE_INTERVAL*HOUR_IN_SECONDS);


			$response = wp_remote_get($this->getUpdateURL('info'), [
				'timeout' => defined('DOING_CRON') && DOING_CRON ? 20 : 5
			]);

			if (
				is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200 || empty($response['body']) ||
				($update = json_decode($response['body'], true)) === null
			) {
				return is_array($outdated_value) ? $outdated_value : [];
			}

			return $update;

		}, 'base_theme');


		if (
			is_array($update) &&
			isset($update['version'], $update['php_version'], $update['wp_version'], $update['url'], $update['error'], $update['ticket']) &&
			version_compare($update['version'], $this->base_theme->version) > 0
		) {

			$transient->response[get_option('template')] = [

				'new_version' => $update['version'],
				'url'         => $update['url'],
				'package'     => $update['ticket'] ? $this->getUpdateURL('download', $update['ticket']) : '',

				'php_version' => $update['php_version'],
				'wp_version'  => $update['wp_version'],
				'error'       => $update['error']

			];

		} else {

			unset($transient->response[get_option('template')]);

		}

		return $transient;

	}









	public function __filterWPPrepareThemesForJS($prepared_themes)
	{
		if ($this->parent_theme !== null) {
			unset($prepared_themes[$this->parent_theme->get_stylesheet()]);
		}
		return $prepared_themes;
	}









	public function __filterMCEExternalPlugins($plugin_array)
	{
		if (count(Shortcode::instances(Shortcode::VISIBILITY_TINYMCE)) > 0) {
			$plugin_array['drone_shortcode_options'] = $this->drone_uri . '/js/shortcode-options.js';
		}
		return $plugin_array;
	}









	public function __filterMCEExternalLanguages($languages_array)
	{
		if (count(Shortcode::instances(Shortcode::VISIBILITY_TINYMCE)) > 0) {
			$languages_array[] = $this->drone_dir . '/odd/shortcode-options.php';
		}
		return $languages_array;
	}









	public function __filterMCECSS($css)
	{
		return ltrim($css . ',' . $this->drone_uri . '/css/shortcode-options.css', ',');
	}









	public function __filterMCEButtons($buttons)
	{
		if (count(Shortcode::instances(Shortcode::VISIBILITY_TINYMCE)) > 0) {
			array_splice($buttons, array_search('wp_more', $buttons)+1, 0, 'drone_shortcode_options');
		}
		return $buttons;
	}






	public function __callbackThemeOptions()
	{
		if (($group = $this->theme_options->child($this->plugin_page)) !== null) {
			require $this->drone_dir . '/tpl/theme-options.php';
		}
	}









	public function __callbackThemeOptionsSanitize($data)
	{
		$this->theme_options->change($data);
		return $this->theme_options->toArray();
	}








	protected function beginMarker($name)
	{
		if ($this->debug_mode !== false) {
			unset($this->marker_time[$name]);
			$this->marker_time[$name] = [
				'time'   => microtime(true),
				'memory' => memory_get_usage()
			];
		}
	}








	protected function endMarker($name)
	{
		if ($this->debug_mode !== false && isset($this->marker_time[$name])) {
			$this->debug_log[] = [
				'name'         => $name,
				'start_time'   => $this->marker_time[$name]['time'],
				'end_time'     => microtime(true),
				'start_memory' => $this->marker_time[$name]['memory'],
				'end_memory'   => memory_get_usage(),
				'nest'         => count($this->marker_time)-1,
			];
			unset($this->marker_time[$name]);
		}
	}









	protected function getPostOptions($post = null)
	{


		if ($post === null) {
			if (($post = (int)get_the_ID()) === 0) {
				return;
			}
		}

		if (!isset($this->post_options[$post])) {

			if (is_int($post)) {


				$post      = wp_is_post_revision($post) ?: $post;
				$post_type = get_post_type($post);


				$this->post_options[$post] = $this->getPostOptions($post_type)->deepClone();


				$post_data = get_post_meta($post, apply_filters('post_options_id', '_' . $this->base_theme->id_), true);

				$this->beginMarker($this->class . '::onPostOptionsCompatybility');
				$this->post_options[$post]->fromArray($post_data, [$this, 'onPostOptionsCompatybility'], [$post_type]);
				$this->endMarker($this->class . '::onPostOptionsCompatybility');

				do_action('post_on_load_options', $this->post_options[$post], $post);

			} else {


				$this->post_options[$post] = new Options\Group\Post($this->base_theme->id_);
				do_action('post_on_setup_options', $this->post_options[$post], $post);

			}

		}

		return $this->post_options[$post];

	}











	protected function foreachPostOptions($posts_types, $callback)
	{
		$posts_types = array_map(function ($s) { return (string)$s; }, (array)$posts_types);
		foreach ($posts_types as $post_type) {
			call_user_func($callback, $post_type, $this->getPostOptions($post_type));
		}
	}








	protected function isIllegal()
	{
		return
			preg_match('/-(kingstheme-com|kingtheme-net|neo-share-net|null-24-net|themekiller-com|themelot-net|wplocker-com|(downloaded|shared?)-.+)$/', $this->base_theme->id) > 0 &&
			(int)$this->sysinfo->value('activation_time')+self::ACTIVATION_TIME_ILLEGAL_SHIFT*86400 <= time();
	}









	public function addThemeFeature($name, $params = [])
	{


		if (is_array($name)) {
			foreach ($name as $_name) {
				$this->addThemeFeature($_name, $params);
			}
			return;
		}


		if (strpos($name, 'option-') === 0 && !self::$setup_options_lock) {
			_doing_it_wrong(__METHOD__ . "({$name})", 'Use inside onSetupOptions() method.', '5.0');
		}


		switch ($name) {


			case 'query-vars':
				if (!$params) {
					break;
				}
				add_action('query_vars', function ($qvars) use ($params) {
					return array_merge($qvars, (array)$params);
				});
				break;


			case 'retina-image-size':
				add_action('init', function () use ($params) {
					if (!$params) {
						$params = array_merge(
							array_keys($GLOBALS['_wp_additional_image_sizes']),
							['thumbnail', 'medium', 'medium_large', 'large']
						);
					}
					foreach ($params as $name) {
						if (strpos($name, '@2x') !== false) {
							continue;
						}
						if (in_array($name, ['thumbnail', 'medium', 'medium_large', 'large'])) {
							$image_size = [
								'width'  => get_option($name . '_size_w'),
								'height' => get_option($name . '_size_h'),
								'crop'   => $name == 'thumbnail' ? (bool)get_option('thumbnail_crop') : false
							];
						} else if (isset($GLOBALS['_wp_additional_image_sizes'][$name])) {
							$image_size = $GLOBALS['_wp_additional_image_sizes'][$name];
						} else {
							continue;
						}
						add_image_size($name . '@2x', $image_size['width']*2, $image_size['height']*2, $image_size['crop']);
					}
				});
				break;


			case 'x-ua-compatible':
				if (is_admin()) {
					break;
				}
				add_action('send_headers', function () {
					header('X-UA-Compatible: IE=edge,chrome=1');
				});
				break;


			case 'default-site-icon':
				if (!$params || has_site_icon()) {
					break;
				}
				add_action('wp_head', function () use ($params) {
					echo "<link rel=\"shortcut icon\" href=\"{$params}\" />\n";
				});
				break;


			case 'nav-menu-current-item':
				extract(array_merge([
					'class' => 'current'
				], $params));
				$filter = function ($items) use ($class) {
					return preg_replace('/(?<=[ "\'])(current((-menu-|-page-|_page_)(item|ancestor|parent)|-cat(-parent)?|-lang))(?=[ "\'])/i', $class . ' \0', $items);
				};
				add_filter('wp_nav_menu_items',  $filter);
				add_filter('wp_list_pages',      $filter);
				add_filter('wp_list_categories', $filter);
				if (get_option('show_on_front') == 'page') {
					$page_for_posts = get_option('page_for_posts');
					add_filter('nav_menu_css_class', function ($classes, $item) use ($page_for_posts) {
						if ($item->object == 'page' && $item->object_id == $page_for_posts) {
							if (!(is_singular('post') || is_category() || is_tag() || is_date() || is_author())) {
								$classes = array_diff($classes, ['current_page_parent']);
							}
						}
						return $classes;
					}, 10, 2);
				}
				break;


			case 'comment-form-fields-reverse-order':
				add_filter('comment_form_fields', function ($fields) {
					$keys = array_unique(array_merge(['author', 'email', 'url', 'comment'], array_keys($fields)));
					return Func::arrayArrange($fields, $keys);
				});
				break;


			case 'force-img-caption-shortcode-filter':
				add_filter('the_content', [$this, '__filterForceImgCaptionShortcodeFilter'], 5);
				break;


			case 'inherit-parent-post-options':
				if (!$params) {
					break;
				}
				$this->features['inherit-parent-post-options'] = (array)$params;
				break;


			case 'social-media-api':
				add_action('wp_enqueue_scripts', function () {
					wp_enqueue_script(Theme::instance()->theme->id . '-social-media-api');
				});
				break;


			case 'widget-unwrapped-text':
				register_widget('\Drone\Widgets\Widget\UnwrappedText');
				break;


			case 'widget-page':
				register_widget('\Drone\Widgets\Widget\Page');
				break;


			case 'widget-contact':
				register_widget('\Drone\Widgets\Widget\Contact');
				break;


			case 'widget-posts-list':
				register_widget('\Drone\Widgets\Widget\PostsList');
				break;


			case 'widget-twitter':
				register_widget('\Drone\Widgets\Widget\Twitter');
				break;


			case 'widget-flickr':
				register_widget('\Drone\Widgets\Widget\Flickr');
				break;


			case 'widget-instagram':
				register_widget('\Drone\Widgets\Widget\Instagram');
				break;


			case 'widget-facebook-like-box':
				register_widget('\Drone\Widgets\Widget\FacebookLikeBox');
				break;


			case 'widget-facebook-page':
				register_widget('\Drone\Widgets\Widget\FacebookPage');
				break;


			case 'shortcode-search':
				new Shortcode\Search();
				break;


			case 'shortcode-page':
				new Shortcode\Page();
				break;


			case 'shortcode-contact':
				new Shortcode\Contact();
				break;


			case 'shortcode-sidebar':
				new Shortcode\Sidebar();
				break;


			case 'shortcode-no-format':
				new Shortcode\NoFormat();
				break;



			case 'option-favicon':

				_deprecated_function(__CLASS__ . '::addThemeFeature(option-favicon)', '5.7', 'default-site-icon');

				if (isset($params['default'])) {
					$this->addThemeFeature('default-site-icon', $params['default']);
				}

				break;


			case 'option-feed-url':

				if (!isset($params['group'])) {
					_doing_it_wrong(__CLASS__ . '::addThemeFeature(option-feed-url)', '$group param is required.', '5.4');
				}


				extract(array_merge([
					'group' => null,
					'name'  => 'feed_url'
				], $params));


				if (!$group instanceof \Drone\Options\Group) {
					break;
				}
				$option = $group->addOption('codeline', $name, '', __('Alternative feed URL', 'everything'), __('E.g. FeedBurner.', 'everything'));


				add_filter('feed_link', function ($output, $feed) use ($option) {
					return !$option->isEmpty() && stripos($output, 'comments') === false ? $option->value : $output;
				}, 10, 2);

				break;


			case 'option-tracking-code':

				if (!isset($params['group'])) {
					_doing_it_wrong(__CLASS__ . '::addThemeFeature(option-tracking-code)', '$group param is required.', '5.4');
				}


				extract(array_merge([
					'group' => null,
					'name'  => 'tracking_code'
				], $params));


				if (!$group instanceof \Drone\Options\Group) {
					break;
				}
				$option = $group->addOption('code', $name, '', __('Tracking code', 'everything'), __('E.g. Google Analitycs.', 'everything'));


				add_action('wp_head', function () use ($option) {
					if (!current_user_can('administrator')) {
						echo $option->value;
					}
				}, 100);

				break;


			case 'option-ogp':

				if (!isset($params['group'])) {
					_doing_it_wrong(__CLASS__ . '::addThemeFeature(option-ogp)', '$group param is required.', '5.4');
				}


				extract($params = array_merge([
					'group' => null,
					'name'  => 'ogp'
				], $params));


				if (!$group instanceof \Drone\Options\Group) {
					break;
				}
				$ogp = $group->addGroup($name, __('Open Graph Protocol', 'everything'));
					$enabled = $ogp->addOption('boolean', 'enabled', true, '', '', ['caption' => __('Enabled', 'everything')]);
					$option = $ogp->addOption('image', 'image', '', __('Default image', 'everything'), '', ['owner' => $enabled, 'indent' => true]);

				$this->features['ogp'] = ['options' => $ogp];


				add_action('wp_head', [$this, '__actionOGP'], 1);

				break;


			case 'option-custom-css':

				if (!isset($params['group'])) {
					_doing_it_wrong(__CLASS__ . '::addThemeFeature(option-custom-css)', '$group param is required.', '5.4');
				}


				extract(array_merge([
					'group' => null,
					'name'  => 'custom_css'
				], $params));


				if (!$group instanceof \Drone\Options\Group) {
					break;
				}
				$option = $group->addOption('code', $name, '', __('Custom CSS code', 'everything'), '', ['error_value' => function ($option, $value) {
					return (bool)preg_match('#^<style[^>]*>.*</style>$#is', trim($value));
				}]);


				add_action('wp_enqueue_scripts', function () use ($option) {
					if (!$option->isEmpty()) {
						Theme::instance()->addDocumentStyle(
							preg_replace('#^(?:<style[^>]*>)?(.*?)(?:</style>)?$#is', '\1', $option->value)
						);
					}
				});

				break;


			case 'option-custom-js':

				if (!isset($params['group'])) {
					_doing_it_wrong(__CLASS__ . '::addThemeFeature(option-custom-js)', '$group param is required.', '5.4');
				}


				extract(array_merge([
					'group' => null,
					'name'  => 'custom_js'
				], $params));


				if (!$group instanceof \Drone\Options\Group) {
					break;
				}
				$option = $group->addOption('code', $name, '', __('Custom JavaScript code', 'everything'), '', ['error_value' => function ($option, $value) {
					return (bool)preg_match('#^<script[^>]*>.*</script>$#is', trim($value));
				}]);


				add_action('wp_enqueue_scripts', function () use ($option) {
					if (!$option->isEmpty()) {
						Theme::instance()->addDocumentScript(
							preg_replace('#^(?:<script[^>]*>)?(.*?)(?:</script>)?$#is', '\1', $option->value)
						);
					}
				});

				break;


			case 'option-contact-form':


				extract(array_merge([
					'group'       => $this->theme_options,
					'name'        => 'contact_form',
					'result_var'  => 'result',
					'message_var' => 'message'
				], $params));


				if (!$group instanceof \Drone\Options\Group) {
					break;
				}

				$cf = $group->addGroup($name, __('Contact form', 'everything'));
					$subject_description =
						'<code>%blogname%</code>&nbsp;-&nbsp;' . __('blog name', 'everything') . ', ' .
						'<code>%blogurl%</code>&nbsp;-&nbsp;' . __('blog url', 'everything') . ', ' .
						'<code>%name%</code>&nbsp;-&nbsp;' . __('name field', 'everything') . ', ' .
						'<code>%email%</code>&nbsp;-&nbsp;' . __('e-mail field', 'everything') . ', ' .
						'<code>%website%</code>&nbsp;-&nbsp;' . __('website field', 'everything') . ', ' .
						'<code>%phone%</code>&nbsp;-&nbsp;' . __('phone number field', 'everything') . ', ' .
						'<code>%subject%</code>&nbsp;-&nbsp;' . __('subject field', 'everything') . ' . ';
					$cf->addOption('group', 'fields', ['name', 'email', 'subject', 'message'], __('Available form fields', 'everything'), '&lowast; ' . __('required fields (if present).', 'everything'), ['options' => [
						'name'    => _x('Name', 'contact form', 'everything') . '<sup>&lowast;</sup>',
						'email'   => __('E-mail', 'everything') . '<sup>&lowast;</sup>',
						'website' => __('Website', 'everything'),
						'phone'   => __('Phone number', 'everything'),
						'subject' => __('Subject', 'everything'),
						'message' => __('Message', 'everything') . '<sup>&lowast;</sup>',
						'captcha' => sprintf('<a href="https://wordpress.org/plugins/captcha/">%s</a><sup>&lowast;</sup>', __('Captcha', 'everything'))
					], 'multiple' => true, 'sortable' => true, 'disabled' => $this->isPluginActive('captcha') ? ['email', 'message'] : ['email', 'message', 'captcha']]);
					$cf->addOption('text', 'subject', '[%blogname%] %subject%', __('E-mail subject', 'everything'), $subject_description, []);
					$cf->addOption('codeline', 'to', get_option('admin_email'), __('Recipient e-mail address', 'everything'), '', []);
					$cf->addOption('select', 'from', 'admin', __('Sender e-mail address', 'everything'), __("Some servers allow only for sending emails from their own domain, so in that case make sure it's the proper email.", 'everything'), ['options' => [
						'admin' => sprintf('%s (%s)', __('WordPress settings e-mail', 'everything'), get_option('admin_email')),
						'to'    => __('Recipient e-mail address', 'everything'),
						'field' => __('E-mail form field', 'everything')
					]]);
					$settings_default = ['from_header'];
					$settings_disabled = [];
					if ($this->isPluginActive('akismet')) {
						$settings_default[] = 'akismet';
					} else {
						$settings_disabled[] = 'akismet';
					}
					$cf->addOption('group', 'settings', $settings_default, __('Advanced settings', 'everything'), '', ['options' => [
						'akismet'     => sprintf(__('Protect from spam with %s', 'everything'), '<a href="https://wordpress.org/plugins/akismet/">Akismet</a>'),
						'from_header' => sprintf(__('Override %s header with Name field', 'everything'), '<code>From</code>')
					], 'multiple' => true, 'disabled' => $settings_disabled]);


				$this->features['contact-form'] = compact(
					'result_var',
					'message_var'
				)+[
					'options' => $cf,
					'action'  => $action = $this->theme->id_ . '_contact_form'
				];


				add_action('wp_ajax_nopriv_' . $action, [$this, '__actionWPAjaxContactForm']);
				add_action('wp_ajax_' . $action,        [$this, '__actionWPAjaxContactForm']);

				break;

		}

	}








	public function addDocumentStyle($style)
	{

		if (empty($style)) {
			return;
		}

		if (!isset($this->styles)) {
			$this->styles = [];
		}

		$this->styles[] = (string)$style;

	}









	public function addDocumentScript($script, $in_footer = false)
	{

		if (empty($script)) {
			return;
		}

		$pos = $in_footer ? 'footer' : 'header';
		if (!isset($this->scripts[$pos]['js'])) {
			$this->scripts[$pos]['js'] = [];
		}

		$this->scripts[$pos]['js'][] = (string)$script;

	}









	public function addDocumentJQueryScript($jquery_script, $in_footer = false)
	{

		if (empty($jquery_script)) {
			return;
		}

		$pos = $in_footer ? 'footer' : 'header';
		if (!isset($this->scripts[$pos]['jquery'])) {
			$this->scripts[$pos]['jquery'] = [];
		}

		$this->scripts[$pos]['jquery'][] = (string)$jquery_script;

	}










	public function getTransientName($name, $context = 'theme')
	{
		if (!isset($this->{$context})) {
			$context = 'theme';
		}
		$theme_prefix = rtrim(substr($this->{$context}->id_, 0, self::WP_TRANSIENT_NAME_MAX_LENGTH-32-1), '_') . '_';
		$name         = Func::stringID($name, '_');
		if (strlen($name) > self::WP_TRANSIENT_NAME_MAX_LENGTH-strlen($theme_prefix)) {
			$name = md5($name);
		}
		return $theme_prefix . $name;
	}
















	public function getTransient($name, $fallback = false, $context = 'theme')
	{


		$transient = $this->getTransientName($name, $context);


		if (is_callable($fallback)) {
			$rf = new \ReflectionFunction($fallback);
			$outdated_value = $rf->getNumberOfParameters() >= 2 ? get_option(self::WP_TRANSIENT_PREFIX . $transient) : false;
		}


		if (($value = get_transient($transient)) !== false) {
			return $value;
		}


		if (!is_callable($fallback)) {
			return $fallback;
		}


		$value = call_user_func_array($fallback, [&$expiration, $outdated_value]);


		if ($value !== null && $value !== false && $expiration > 0) {
			set_transient($transient, $value, $expiration);
		}

		return $value;

	}












	public function setTransient($name, $value, $expiration = 0, $context = 'theme')
	{
		return set_transient($this->getTransientName($name, $context), $value, $expiration);
	}










	public function deleteTransient($name, $context = 'theme')
	{
		return delete_transient($this->getTransientName($name, $context));
	}










	public function getResourcePath($filename, $fallback = null)
	{

		if ($this->parent_theme !== null && file_exists($this->stylesheet_dir . '/' . $filename)) {
			return $this->stylesheet_dir . '/' . $filename;
		}

		if (file_exists($this->template_dir . '/' . $filename)) {
			return $this->template_dir . '/' . $filename;
		}

		if ($fallback) {
			return $this->template_dir . '/' . $fallback;
		}

		return '';

	}










	public function getResourceURI($filename, $fallback = null)
	{

		if ($this->parent_theme !== null && file_exists($this->stylesheet_dir . '/' . $filename)) {
			return $this->stylesheet_uri . '/' . $filename;
		}

		if (file_exists($this->template_dir . '/' . $filename)) {
			return $this->template_uri . '/' . $filename;
		}

		if ($fallback) {
			return $this->template_uri . '/' . $fallback;
		}

		return '';

	}









	public static function getInstance()
	{
		_deprecated_function(__METHOD__, '5.7', __CLASS__ . '::instance()');
		return self::instance();
	}








	public static function instance()
	{
		if (self::$instance === null) {
			$class = get_called_class();
			self::$instance = new $class();
		}
		return self::$instance;
	}










	public static function to_($name, $skip_if = null)
	{
		$child = self::instance()->theme_options->findChild($name, $skip_if);
		if (self::$setup_options_lock) {
			static $imported = [];
			if (!isset($imported[$name]) && $child !== null && $child->isOption()) {
				$child->importFromArray(self::instance()->theme_options_array);
				$imported[$name] = $child;
			}
		}
		return $child;
	}











	public static function to($name, $skip_if = null, $fallback = null)
	{
		$child = self::to_($name, $skip_if);
		return $child !== null && $child->isOption() ? $child->value : $fallback;
	}










	public static function po_($name, $skip_if = null)
	{

		if (($post = get_post()) === null) {
			return;
		}

		$_this = self::instance();

		do {
			$child = $_this->getPostOptions((int)$post->ID)->findChild($name, $skip_if);
			$post =
				$child === null &&
				$post->post_parent > 0 &&
				isset($_this->features['inherit-parent-post-options']) &&
				in_array($post->post_type, $_this->features['inherit-parent-post-options']) ? get_post($post->post_parent) : null;
		} while ($post !== null);

		return $child;

	}











	public static function po($name, $skip_if = null, $fallback = null)
	{
		$child = self::po_($name, $skip_if);
		return $child !== null && $child->isOption() ? $child->value : $fallback;
	}













	public static function io_($po_name, $to_name, $inherit_if = '__default', $skip_if = null, $decapsulate = true)
	{

		if (($child = self::po_($po_name, $inherit_if)) === null) {
			$child = self::to_($to_name, $skip_if);
		}

		if ($decapsulate && $child instanceof Options\Option\iEncapsulated) {
			$child = $child->decapsulate();
		}

		return $child;

	}













	public static function io($po_name, $to_name, $inherit_if = '__default', $skip_if = null, $fallback = null)
	{
		$child = self::io_($po_name, $to_name, $inherit_if, $skip_if);
		return $child !== null ? $child->value : $fallback;
	}




















	public static function getPostMeta($name)
	{

		_deprecated_function(__METHOD__, '5.7', __CLASS__ . '::getMeta()');

		switch ($name) {


			case 'title':
				$result = get_the_title();
				break;


			case 'link':
				$result = esc_url(\apply_filters('the_permalink', get_permalink()));
				break;
			case 'link_edit':
				$result = get_edit_post_link();
				break;


			case 'date_year_link':
				$result = get_year_link(get_the_date('Y'));
				break;
			case 'date_month_link':
				$result = call_user_func_array('get_month_link', explode(' ', get_the_date('Y n')));
				break;
			case 'date_day_link':
				$result = call_user_func_array('get_day_link', explode(' ', get_the_date('Y n j')));
				break;
			case 'date':
				$result = get_the_date();
				break;
			case 'date_modified':
				$result = get_the_modified_date();
				break;


			case 'time':
				$result = get_the_time();
				break;
			case 'time_modified':
				$result = get_the_modified_time();
				break;
			case 'time_diff':
				$result = sprintf(__('%s ago', 'everything'), human_time_diff(get_post_time('U', true)));
				break;
			case 'time_modified_diff':
				$result = sprintf(__('%s ago', 'everything'), human_time_diff(get_post_modified_time('U', true)));
				break;


			case 'category_list':
				$result = get_the_category_list(', ');
				break;


			case 'tags_list':
				$result = get_the_tag_list('', ', ');
				break;


			case 'comments_link':
				$result = get_comments_link();
				break;
			case 'comments_count':
				$result = get_comments_number();
				break;
			case 'comments_number':
				$result = get_comments_number_text();
				break;


			case 'author_link':
				$result = get_author_posts_url($GLOBALS['authordata']->ID, $GLOBALS['authordata']->user_nicename);
				break;
			case 'author_name':
				$result = get_the_author();
				break;


			default:
				return '';

		}

		return trim($result);

	}









	public static function postMeta($name)
	{
		_deprecated_function(__METHOD__, '5.7', 'echo ' . __CLASS__ . '::getMeta()');
		echo self::getPostMeta($name);
	}











	public static function getPostMetaFormat($format)
	{

		_deprecated_function(__METHOD__, '5.7', __CLASS__ . '::getMetaTemplate()');

		$name_pattern = '%(?P<name>[_a-z]{2,}?)(?P<esc>_esc)?%';


		$format = preg_replace_callback('#\[(?P<not>!)?(' . $name_pattern . ')\](?P<content>.*?)\[/\2\]#', function ($m) {
			return ((bool)$m['not'] xor (bool)Theme::getPostMeta($m['name'])) ? $m['content'] : '';
		}, $format);


		$format = preg_replace('/' . $name_pattern . '/', '%\0%', $format);


		$s = call_user_func_array('sprintf', array_merge([$format], array_slice(func_get_args(), 1)));


		$s = preg_replace_callback('/' . $name_pattern . '/', function ($m) {
			$s = Theme::getPostMeta($m['name']);
			if (!empty($m['esc'])) {
				$s = esc_attr($s);
			}
			return $s;
		}, $s);


		return $s;

	}










	public static function postMetaFormat($format)
	{
		_deprecated_function(__METHOD__, '5.7', 'echo ' . __CLASS__ . '::getMetaTemplate()');
		echo call_user_func_array(__CLASS__ . '::getPostMetaFormat', func_get_args());
	}




















	public static function getMeta($name)
	{

		switch ($name) {


			case 'title':
				return strip_tags(get_the_title());

			case 'link':
			case 'permalink':
				return \apply_filters('the_permalink', get_permalink());


			case 'date_year_link':
				return get_year_link(get_the_date('Y'));

			case 'date_month_link':
				return call_user_func_array('get_month_link', explode(' ', get_the_date('Y n')));

			case 'date_day_link':
				return call_user_func_array('get_day_link', explode(' ', get_the_date('Y n j')));

			case 'date':
				return get_the_date();

			case 'date_modified':
				return get_the_modified_date();


			case 'time':
				return get_the_time();

			case 'time_modified':
				return get_the_modified_time();

			case 'time_diff':
				return sprintf(__('%s ago', 'everything'), human_time_diff(get_post_time('U', true)));

			case 'time_modified_diff':
				return sprintf(__('%s ago', 'everything'), human_time_diff(get_post_modified_time('U', true)));


			case 'date_time':
				return sprintf(__('%1$s at %2$s', 'everything'), get_the_date(), get_the_time());


			case 'category_list':
				return get_the_category_list(', ');


			case 'tags_list':
				return get_the_tag_list('', ', ');


			case 'comments_link':
				return get_comments_link();

			case 'comments_count':
			case 'comments_number':
				return get_comments_number();

			case 'comments_count_text':
			case 'comments_number_text':
				return get_comments_number_text();


			case 'author_link':
				return get_author_posts_url($GLOBALS['authordata']->ID, $GLOBALS['authordata']->user_nicename);

			case 'author_name':
				return get_the_author();


			case 'edit_link':
				return get_edit_post_link();


			default:
				return '';

		}

	}










	public static function getMetaTemplate($body, $vars = [])
	{

		if (!is_array($vars) || func_num_args() > 2) {
			$vars = array_slice(func_get_args(), 1);
		}

		return Template::instance($body, $vars)->default([__CLASS__, 'getMeta']);

	}











	public static function getShortcodeOutput($tag, array $atts = [], $content = null)
	{
		if (($shortcode = Shortcode::instance($tag)) !== null) {
			return $shortcode->shortcode($atts, $content);
		}
	}










	public static function shortcodeOutput($tag, array $atts = [], $content = null)
	{
		echo self::getShortcodeOutput($tag, $atts, $content);
	}









	public static function getContactForm($context = '')
	{


		$_this = self::instance();


		if (!isset($_this->features['contact-form'])) {
			return;
		}
		$options = $_this->features['contact-form']['options'];


		$fields = [
			'name' => [
				'label'    => _x('Name', 'contact form', 'everything'),
				'required' => true,
				'template' => '<input type="text" name=":name" placeholder=":label*" required />'
			],
			'email' => [
				'label'    => __('E-mail', 'everything'),
				'required' => true,
				'template' => '<input type="email" name=":name" placeholder=":label*" required />'
			],
			'website' => [
				'label'    => __('Website', 'everything'),
				'required' => false,
				'template' => '<input type="url" name=":name" placeholder=":label" />'
			],
			'phone' => [
				'label'    => __('Phone number', 'everything'),
				'required' => false,
				'template' => '<input type="tel" name=":name" placeholder=":label" />'
			],
			'subject' => [
				'label'    => __('Subject', 'everything'),
				'required' => false,
				'template' => '<input type="text" name=":name" placeholder=":label" />'
			],
			'message' => [
				'label'    => __('Message', 'everything'),
				'required' => true,
				'template' => '<textarea name=":name" placeholder=":label" required></textarea>'
			],
			'captcha' => [
				'label'    => __('Captcha', 'everything'),
				'required' => true,
				'template' => ':captcha'
			],
		];


		$template = Template::instance(apply_filters('contact_form_template', <<<'EOT'
			<form class=":class" action=":action" method="post">
				<input type="hidden" name="action" value=":action_id" />
				:fields
				<input type="submit" value=":submit_label" />
			</form>
EOT
		, $context));

		$template
			->class('contact-form')
			->action(admin_url(self::WP_AJAX_URI))
			->action_id($_this->features['contact-form']['action'])
			->submit_label(__('Send', 'everything'));


		foreach ($options->value('fields') as $name) {

			$field_template = Template::instance(apply_filters(
				'contact_form_field_template', $fields[$name]['template'],
				$name, $fields[$name]['label'], $fields[$name]['required'], $context
			));

			$field_template
				->name($name)
				->label($fields[$name]['label'])
				->required($fields[$name]['required'])
				->captcha(function () {
					return \apply_filters('hctpc_display', '', 'contact-form');
				});

			$template->fields .= $field_template;

		}

		$output = $template->build();


		$output = apply_filters('contact_form_output', $output, $context);


		return $output;

	}








	public static function getBreadcrumbs()
	{


		if (self::isPluginActive('bbpress') && is_bbpress()) {
			$breadcrumbs_html = bbp_get_breadcrumb([
				'before'         => '<ul>',
				'after'          => '</ul>',
				'sep'            => ' ',
				'sep_before'     => '',
				'sep_after'      => '',
				'crumb_before'   => '<li>',
				'crumb_after'    => '</li>',
				'current_before' => '',
				'current_after'  => ''
			]);
		}

		else if (self::isPluginActive('woocommerce') && (is_shop() || is_product_taxonomy() || is_product())) {
			$breadcrumbs_html = \Drone\Func::functionGetOutputBuffer('woocommerce_breadcrumb', [
				'delimiter'   => '',
				'wrap_before' => '<ul>',
				'wrap_after'  => '</ul>',
				'before'      => '<li>',
				'after'       => '</li>'
			]);
		}

		else if (self::isPluginActive('breadcrumb-navxt')) {
			$breadcrumbs_html = '<ul>' . bcn_display_list(true) . '</ul>';
		}

		else if (self::isPluginActive('breadcrumb-trail')) {
			$breadcrumbs_html = breadcrumb_trail([
				'show_browse' => false,
				'echo'        => false
			]);
			if (!preg_match('#<ul[^>]*>.+?</ul>#is', $breadcrumbs_html, $m)) {
				return;
			}
			$breadcrumbs_html = $m[0];
		}

		else if (self::isPluginActive('wordpress-seo')) {
			return;





		}


		$output = \Drone\HTML::makeFromHTML($breadcrumbs_html)
			->each(function (&$child) {
				if (is_string($child)) {
					$child = trim($child);
				}
			});


		$output = apply_filters('breadcrumbs_output', $output);

		return $output->toHTML();

	}









	public static function isPluginActive($name)
	{


		if (func_num_args() > 1) {
			$name = func_get_args();
		}


		if (is_array($name)) {
			foreach ($name as $_name) {
				if (self::isPluginActive($_name)) {
					return true;
				}
			}
			return false;
		}

		switch (strtolower($name)) {

			case 'akismet':
				return defined('AKISMET_VERSION');

			case 'bbpress':
				return function_exists('bbpress');

			case 'breadcrumb-navxt':
				return class_exists('breadcrumb_navxt');

			case 'breadcrumb-trail':
				return class_exists('Breadcrumb_Trail');

			case 'captcha':
				return function_exists('hctpc_init');

			case 'disqus-comment-system':
			case 'disqus':
				return defined('DISQUS_VERSION');

			case 'dynamic-featured-image':
				return class_exists('Dynamic_Featured_Image');

			case 'google-maps-builder':
				return class_exists('Google_Maps_Builder');

			case 'jetpack':
				return defined('JETPACK__VERSION');

			case 'layerslider':
				return defined('LS_PLUGIN_VERSION');

			case 'masterslider':
				return defined('MSWP_AVERTA_VERSION');

			case 'polylang':
				return defined('POLYLANG_VERSION');

			case 'recipes':
				return class_exists('Simmer');

			case 'revslider':
				return isset($GLOBALS['revSliderVersion']);

			case 'visual-composer':
				return defined('WPB_VC_VERSION');

			case 'w3-total-cache':
				return defined('W3TC') && W3TC;

			case 'wild-googlemap':
				return class_exists('WiLD_Plugin_Googlemap');

			case 'woocommerce':
				return defined('WOOCOMMERCE_VERSION');

			case 'woocommerce-brands':
				return class_exists('WC_Brands');

			case 'wordpress-seo':
				return defined('WPSEO_VERSION');

			case 'wp-google-map-plugin':
				return class_exists('Wpgmp_Google_Map_Lite') || class_exists('Google_Maps_Pro');

			case 'sitepress-multilingual-cms':
			case 'wpml':
				return defined('ICL_SITEPRESS_VERSION');

			default:
				return false;

		}

	}

}