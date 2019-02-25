<?php








namespace Drone\Shortcodes;

use Drone\Func;
use Drone\HTML;
use Drone\Options;
use Drone\Theme;












abstract class Shortcode
{







	const VISIBILITY_NONE = 0;







	const VISIBILITY_TINYMCE = 1;







	const VISIBILITY_VC = 2;







	const VISIBILITY_ALL = 3;







	const VISIBILITY_ANY = 255;







	private static $instances = [];







	private static $call_stack = [];







	private $options;







	private $tag;







	private $label;







	private $self_closing = false;







	private $parent;







	private $visibility = self::VISIBILITY_ALL;








	protected function onSetupOptions(\Drone\Options\Group\Shortcode $options) { }








	public function onOptionsCompatybility(array &$data) { }










	abstract protected function onShortcode($content, $code, \Drone\HTML &$html);






	protected function register()
	{
		add_shortcode($this->tag, [$this, 'shortcode']);
	}






	protected function unregister()
	{
		remove_shortcode($this->tag);
	}








	protected function newOptionsGroupInstance()
	{
		return new Options\Group\Shortcode('');
	}









	protected function getOptions(array $data = null)
	{


		$options = $this->newOptionsGroupInstance();
		$this->onSetupOptions($options);
		\Drone\do_action("shortcode_{$this->tag}_on_setup_options", $options, $this);


		if ($data !== null) {
			$options->fromArray($data, [$this, 'onOptionsCompatybility']);
			if (has_filter('shortcode_atts_' . $this->tag)) {
				$options->fromArray(apply_filters('shortcode_atts_' . $this->tag, $options->toArray(), $options->getDefaults(), $data));
			}
			\Drone\do_action("shortcode_{$this->tag}_on_load_options", $options, $this);
		}

		return $options;

	}










	public function __construct($label, $params = [])
	{


		$this->label = $label;

		foreach (array_intersect_key($params, array_flip(['tag', 'self_closing', 'parent', 'visibility'])) as $name => $value) {
			$this->{$name} = $value;
		}


		if ($this->tag === null) {
			$class = explode('\\', get_class($this));
			$this->tag = Func::stringID(array_pop($class), '_');
		}


		if ($this->parent !== null && !$this->parent instanceof self) {
			$this->parent = self::instance($params['parent']);
		}

		self::$instances[$this->tag] = $this;

		$this->register();

	}






	public function __destruct()
	{
		if (isset(self::$instances[$this->tag])) {
			$this->unregister();
			unset(self::$instances[$this->tag]);
		}
	}









	public function __get($name)
	{
		switch ($name) {
			case 'tag':
			case 'label':
			case 'self_closing':
			case 'parent':
			case 'visibility':
				return $this->{$name};
			case 'options':
				return $this->getOptions();
		}
	}










	public function so_($name, $skip_if = null)
	{
		return $this->options->findChild($name, $skip_if);
	}











	public function so($name, $skip_if = null, $fallback = null)
	{
		$child = $this->so_($name, $skip_if);
		return $child !== null && $child->isOption() ? $child->value : $fallback;
	}











	public function shortcode($atts, $content = null, $code = '')
	{


		$atts = $atts ? (array)$atts : [];
		$this->options = $this->getOptions($atts);


		self::$call_stack[] = $this->tag;


		$html = HTML::make();
		$this->onShortcode($content, $code, $html);


		$html = \Drone\apply_filters('shortcode_' . $this->tag . '_shortcode', $html, $this, $atts, $content, $code);


		array_pop(self::$call_stack);


		return $html->toHTML();

	}








	public function tinyMCEData()
	{
		$options = $this->getOptions();
		return [
			'tag'          => $this->tag,
			'self_closing' => $this->self_closing,
			'label'        => $this->label,
			'controls'     => $options->tinyMCEControls()
		];
	}








	public function vcData()
	{
		$options = $this->getOptions();
		$data = [
			'base'     => $this->tag,
			'name'     => $this->label,
			'category' => Theme::instance()->theme->name,
			'icon'     => 'mce-i-drone-' . str_replace('_', '-', $this->tag),
			'params'   => $options->vcControls()
		];
		if (!$this->self_closing) {
			$data['params'][] = [
				'type'       => 'textarea_html',
				'holder'     => 'div',
				'param_name' => 'content',
				'value'      => '',
				'heading'    => __('Content', 'everything')
			];
		}
		return $data;
	}










	public static function getInstance($tag)
	{
		_deprecated_function(__METHOD__, '5.7', __CLASS__ . '::instance()');
		return self::instance($tag);
	}









	public static function instance($tag)
	{
		if (isset(self::$instances[$tag])) {
			return self::$instances[$tag];
		}
	}









	public static function getInstances()
	{
		_deprecated_function(__METHOD__, '5.7', __CLASS__ . '::instances()');
		return self::instances();
	}









	public static function instances($visibility = self::VISIBILITY_ANY)
	{
		return array_filter(self::$instances, function ($shortcode) use ($visibility) {
			return $visibility == Shortcode::VISIBILITY_ANY || $shortcode->visibility & $visibility;
		});
	}









	public static function inShortcode($tag)
	{
		return array_search($tag, self::$call_stack) !== false;
	}






	public static function __actionBeforeWPTinyMCE()
	{
		$scripts = '';
		foreach (self::instances(self::VISIBILITY_TINYMCE) as $shortcode) {
			$scripts .= $shortcode->getOptions()->scripts();
		}
		if ($scripts) {
			echo "<script>{$scripts}</script>\n";
		}
	}






	public static function __actionVCBeforeInit()
	{
		foreach (self::instances(self::VISIBILITY_VC) as $shortcode) {
			vc_map($shortcode->vcData());
		}
	}









	public static function __filterTinyMCEBeforeInit($init_array)
	{
		$data = [];
		foreach (self::instances(self::VISIBILITY_TINYMCE) as $shortcode) {
			$data[] = $shortcode->tinyMCEData();
		}
		$init_array['drone_shortcodes_data'] = json_encode($data);
		return $init_array;
	}

}

namespace Drone\Shortcodes\Shortcode;

use Drone\Shortcodes\Shortcode;
use Drone\Func;
use Drone\HTML;
use Drone\Options;
use Drone\Theme;






abstract class Caption extends Shortcode
{







	protected function register()
	{
		add_filter('img_caption_shortcode', [$this, '__filterImgCaptionShortcode'], Theme::WP_FILTER_PRIORITY_DEFAULT, 3);
	}







	protected function unregister()
	{
		remove_filter('img_caption_shortcode', [$this, '__filterImgCaptionShortcode'], Theme::WP_FILTER_PRIORITY_DEFAULT);
	}








	protected function getID()
	{
		return preg_match('/^attachment_([0-9]+)$/i', $this->so('id'), $m) ? (int)$m[1] : false;
	}







	protected function onSetupOptions(\Drone\Options\Group\Shortcode $options)
	{
		$options->addOption('text', 'id', '');
		$options->addOption('select', 'align', 'alignnone', '', '', ['options' => [
			'alignleft'   => '',
			'aligncenter' => '',
			'alignright'  => '',
			'alignnone'   => ''
		]]);
		$options->addOption('number', 'width', '', '', '', ['required' => false]);
		$options->addOption('text', 'caption', '');
		$options->addOption('text', 'class', '');
	}







	protected function onShortcode($content, $code, \Drone\HTML &$html)
	{


		$html = HTML::figure()
			->id(sanitize_html_class($this->so('id')))
			->addClass($this->so('align'), $this->so('class'))
			->css('width', $this->so('width'))
			->add(do_shortcode($content));


		if ($this->so('caption')) {

			$caption = HTML::figcaption()
				->class('wp-caption-text')
				->add($this->so('caption'));

			$html
				->addClass('wp-caption')
				->add($caption);

		}

	}







	public function __construct()
	{
		parent::__construct('', [
			'tag'        => 'caption',
			'visibility' => self::VISIBILITY_NONE
		]);
	}











	public function __filterImgCaptionShortcode($output, $atts, $content)
	{
		return $this->shortcode($atts, $content, $this->tag);
	}

}






abstract class Gallery extends Shortcode
{







	protected function register()
	{
		add_filter('post_gallery', [$this, '__filterPostGallery'], Theme::WP_FILTER_PRIORITY_DEFAULT, 2);
	}







	protected function unregister()
	{
		remove_filter('post_gallery', [$this, '__filterPostGallery'], Theme::WP_FILTER_PRIORITY_DEFAULT);
	}







	protected function newOptionsGroupInstance()
	{
		return new Options\Group\Gallery('');
	}








	protected function getAttachments()
	{
		$params = [
			'numberposts'    => -1,
			'post_parent'    => $this->so('id'),
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'orderby'        => $this->so('orderby'),
			'order'          => $this->so('order')
		];
		if ($this->so('include')) {
			unset($params['post_parent']);
			$params['include'] = preg_replace('/[^0-9,]+/', '', $this->so('include'));
		} else if ($this->so('exclude')) {
			$params['exclude'] = preg_replace('/[^0-9,]+/', '', $this->so('exclude'));
		}
		return get_posts($params);
	}









	protected function getAttachmentLinkURI(\WP_Post $attachment)
	{
		switch ($this->so('link')) {
			case 'post':
				return get_attachment_link($attachment->ID);
			case 'file':
				return (string)wp_get_attachment_image_url($attachment->ID, 'full');
			default:
				return '';
		}
	}







	protected function onSetupOptions(\Drone\Options\Group\Shortcode $options)
	{
		$options->addOption('select', 'order', 'ASC', '', '', ['options' => [
			'ASC'  => '',
			'DESC' => '',
		]]);
		$options->addOption('text', 'orderby', 'menu_order ID');
		$options->addOption('number', 'id', ($post = get_post()) ? $post->ID : 0);
		$options->addOption('number', 'columns', 3, '', '', ['min' => 1, 'max' => 9]);
		$options->addOption('select', 'size', 'thumbnail', __('Size', 'everything'), '', ['options' => \apply_filters('image_size_names_choose', [
			'thumbnail' => '',
			'medium'    => '',
			'large'     => '',
			'full'      => ''
		])]);
		$options->addOption('text', 'ids', '');
		$options->addOption('text', 'include', '');
		$options->addOption('text', 'exclude', '');
		$options->addOption('select', 'link', 'post', '', '', ['options' => [
			'post' => '',
			'file' => '',
			'none' => ''
		]]);
		foreach ($options->childs() as $child) {
			$child->included = false;
		}
	}







	public function __construct()
	{
		parent::__construct('', [
			'tag'          => 'gallery',
			'self_closing' => true,
			'visibility'   => self::VISIBILITY_NONE
		]);
	}










	public function __filterPostGallery($output, $atts)
	{
		if (Theme::isPluginActive('jetpack') && ((isset($atts['type']) && $atts['type'] && $atts['type'] != 'default') || get_option('tiled_galleries'))) {
			return '';
		}
		return $this->shortcode($atts, null, $this->tag);
	}

}






class Search extends Shortcode
{







	protected function onShortcode($content, $code, \Drone\HTML &$html)
	{
		$html->add(get_search_form(false));
	}







	public function __construct()
	{
		parent::__construct(__('Search form', 'everything'), [
			'self_closing' => true
		]);
	}

}






class Page extends Shortcode
{







	protected function onSetupOptions(\Drone\Options\Group\Shortcode $options)
	{
		$options->addOption('post', 'id', 0, __('Page', 'everything'), '', ['required' => false, 'options' => function () {
			return Func::wpPagesList([], 'ID', 'post_title', function_exists('mb_convert_encoding') ? mb_convert_encoding('&mdash; ', 'UTF-8', 'HTML-ENTITIES') : '');
		}]);
	}







	public function onOptionsCompatybility(array &$data)
	{
		if (isset($data['slug'])) {
			if (($page = get_page_by_path($data['slug'])) !== null) {
				$data['id'] = $page->ID;
			}
		}
	}







	protected function onShortcode($content, $code, \Drone\HTML &$html)
	{
		$html->add($this->so_('id')->getContent());
	}







	public function __construct()
	{
		parent::__construct(__('Page', 'everything'), [
			'self_closing' => true
		]);
	}

}






class Contact extends Shortcode
{







	protected function onShortcode($content, $code, \Drone\HTML &$html)
	{
		$html->add(Theme::getContactForm('shortcode'));
	}







	public function __construct()
	{
		parent::__construct(__('Contact form', 'everything'), [
			'self_closing' => true
		]);
	}

}






class Sidebar extends Shortcode
{







	protected function onSetupOptions(\Drone\Options\Group\Shortcode $options)
	{
		$options->addOption('select', 'id', 0, __('Sidebar', 'everything'), '', ['required' => false, 'options' => function () {
			return array_map(function ($s) { return $s['name']; }, $GLOBALS['wp_registered_sidebars']);
		}]);
	}







	protected function onShortcode($content, $code, \Drone\HTML &$html)
	{
		if ($this->so('id')) {
			$html->add(Func::functionGetOutputBuffer('dynamic_sidebar', $this->so('id')));
		}
	}







	public function __construct()
	{
		parent::__construct(__('Sidebar', 'everything'), [
			'self_closing' => true
		]);
	}

}






class NoFormat extends Shortcode
{







	protected function onSetupOptions(\Drone\Options\Group\Shortcode $options)
	{
		$options->addOption('text', 'tag', 'pre', __('HTML tag', 'everything'), '', ['required' => true]);
		$options->addOption('text', 'class', '', __('CSS class', 'everything'));
	}







	protected function onShortcode($content, $code, \Drone\HTML &$html)
	{


		$content = trim(Func::wpShortcodeContent($content, false));
		if ($this->so('tag') == 'pre') {
			$content = preg_replace('#(^<p>|<br ?/?>|</p>$)#i', '', $content);
			$content = preg_replace('#(</p>\r?\n<p>|</p>\r?\n|\r?\n<p>)#i', "\n\n", $content);
		}
		$content = htmlspecialchars($content, defined('ENT_HTML5') ? ENT_COMPAT | ENT_HTML5 : ENT_COMPAT, get_bloginfo('charset'), false);


		$html = HTML::make($this->so('tag'))->add($content);
		if ($this->so('class')) {
			$html->class = $this->so('class');
		}

	}







	public function __construct()
	{
		parent::__construct(__('No format', 'everything'), [
			'visibility' => self::VISIBILITY_NONE
		]);
	}

}