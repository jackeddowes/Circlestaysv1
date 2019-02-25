<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */

namespace Everything\Options\Option;

use \Drone\HTML;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Background
 */
class Background extends \Drone\Options\Option\Background
{

	protected $stripes;
	protected $opacity;

	protected function _options()
	{
		return parent::_options()+[
			'stripes' => 'boolean',
			'opacity' => 'number'
		];
	}

	protected function _html()
	{
		$html = parent::_html()
			->addClass($this->getCSSClass(__CLASS__));
		if (isset($this->stripes)) {
			$html->addNew('div')->add($this->stripes->html());
		}
		if (isset($this->opacity)) {
			$html->addNew('div')->add(__('Content opacity', 'everything'), ' ', $this->opacity->html());
		}
		return $html;
	}

	public function __construct($name, $default, $properties = [])
	{

		parent::__construct($name, $default, $properties);

		if (isset($this->stripes)) {
			$this->stripes->caption = __('Add stripes', 'everything');
		}

		if (isset($this->opacity)) {
			$this->opacity->min = 0;
			$this->opacity->max = 100;
			$this->opacity->unit = '%';
		}

	}

	public function getClass()
	{
		if (isset($this->stripes) && $this->stripes->value) {
			return 'background-stripes';
		}
		return '';
	}

}

/**
 * Banner
 */
class Banner extends \Drone\Options\Option\Complex
{

	protected $type;
	protected $height;
	protected $image;
	protected $slider;
	protected $map;
	protected $page;
	protected $embed;
	protected $custom;

	protected function _options()
	{
		return [
			'type'   => 'select',
			'height' => 'number',
			'image'  => 'attachment',
			'slider' => 'select',
			'map'    => 'select',
			'page'   => 'select',
			'embed'  => 'code',
			'custom' => 'code'
		];
	}

	public function __construct($name, $default, $properties = [])
	{

		parent::__construct($name, $default, $properties);

		$type_options = [
			''          => __('None', 'everything'),
			'empty'     => __('Empty space', 'everything'),
			'image'     => __('Image', 'everything'),
			'thumbnail' => __('Featured image', 'everything'),
			'slider'    => __('Slider', 'everything'),
			'map'       => __('Map', 'everything'),
			'page'      => __('Page', 'everything'),
			'embed'     => __('Embedding code', 'everything'),
			'custom'    => __('Custom HTML', 'everything')
		];
		if (!isset($this->slider)) {
			unset($type_options['slider']);
		}
		if (!isset($this->map)) {
			unset($type_options['map']);
		}
		$this->type->options = $type_options;

		$this->height->unit = 'px';
		$this->height->min = 0;
		$this->height->indent = true;
		$this->height->owner = $this->type;
		$this->height->owner_value = 'empty';

		$this->image->indent = true;
		$this->image->owner = $this->type;
		$this->image->owner_value = 'image';

		if (isset($this->slider)) {
			$this->slider->required = false;
			$this->slider->options = function ($option) {
				$options = [];
				if (\Everything::isPluginActive('layerslider')) {
					foreach (\lsSliders() as $slider) {
						$options['LayerSlider WP']['layerslider-' . $slider['id']] = $slider['name'];
					}
				}
				if (\Everything::isPluginActive('masterslider')) {
					foreach (get_masterslider_names() as $id => $name) {
						$options['Master Slider WP']['masterslider-' . $id] = $name;
					}

				}
				if (\Everything::isPluginActive('revslider')) {
					$revslider = new \RevSlider();
					foreach ($revslider->getArrSliders() as $slider) {
						$options['Revolution Slider']['revslider-' . $slider->getID()] = $slider->getTitle();
					}
				}
				return $options;
			};
			$this->slider->indent = true;
			$this->slider->owner = $this->type;
			$this->slider->owner_value = 'slider';
		}

		if (isset($this->map)) {
			$this->map->required = false;
			$this->map->options = function ($option) {
				$options = [];
				if (\Everything::isPluginActive('wild-googlemap')) {
					$maps = \WiLD_BackendGooglemapManager::getInstance()->get_maps();
					foreach ($maps as $map) {
						$options['wild-googlemap-' . $map->id] = $map->name;
						$option->groups['WiLD Googlemap'][] = 'wild-googlemap-' . $map->id;
					}
				}
				if (\Everything::isPluginActive('wp-google-map-plugin')) {
					$maps = $GLOBALS['wpdb']->get_results("SELECT map_id, map_title FROM {$GLOBALS['wpdb']->prefix}create_map ORDER BY map_id ASC", ARRAY_A);
					foreach ($maps as $map) {
						$options['wp-google-map-plugin-' . $map['map_id']] = $map['map_title'];
						$option->groups['WP Google Map Plugin'][] = 'wp-google-map-plugin-' . $map['map_id'];
					}
				}
				return $options;
			};
			$this->map->indent = true;
			$this->map->owner = $this->type;
			$this->map->owner_value = 'map';
		}

		$this->page->required = false;
		$this->page->options = function () {
			return \Drone\Func::wpPagesList();
		};
		$this->page->indent = true;
		$this->page->owner = $this->type;
		$this->page->owner_value = 'page';

		$this->embed->description = __('Embedding code from YouTube, Vimeo, Google Maps or other.', 'everything');
		$this->embed->indent = true;
		$this->embed->owner = $this->type;
		$this->embed->owner_value = 'embed';

		$this->custom->indent = true;
		$this->custom->owner = $this->type;
		$this->custom->owner_value = 'custom';

	}

}

/**
 * Custom font
 */
class CustomFont extends \Drone\Options\Option\Font
{

	protected $id;

	protected function _options()
	{
		return ['id' => 'text']+parent::_options();
	}

	protected function _html()
	{
		$html = parent::_html();
		$html->insert([$this->id->html(), ' ']);
		return $html;
	}

	protected function _scripts($instance_num)
	{
		return '';
	}

	public function __construct($name, $default, $properties = [])
	{
		parent::__construct($name, $default, $properties);
		$this->id->required = true;
		$this->id->on_html  = function ($option, &$html) { $html->style('width: 140px;'); };
		$this->size->max = 1000;
	}

}

/**
 * Sidebar
 */
class Sidebar extends \Drone\Options\Option\Complex
{

	protected $id;
	protected $width;

	protected function _options()
	{
		return [
			'id'    => 'text',
			'width' => 'number'
		];
	}

	protected function _html()
	{
		return HTML::div()
			->class($this->getCSSClass(__CLASS__))
			->add(
				$this->id->html(), ' ',
				$this->width->html()
			);
	}

	public function __construct($name, $default, $properties = [])
	{
		parent::__construct($name, $default, $properties);
		$this->id->required = true;
		$this->width->min  = 60;
		$this->width->max  = 600;
		$this->width->unit = 'px';
	}

}

/**
 * Layout
 */
class Layout extends \Drone\Options\Option
{

	public $options = [];

	protected function _styles($instance_num)
	{
		static $outputted = false;
		if ($outputted) {
			return '';
		}
		$outputted = true;
		return
<<<'EOS'
			.everything-option-layout {
				margin: -5px;
				overflow: hidden;
			}
			.everything-option-layout > div {
				border: 1px solid #dfdfdf;
				background: #fbfbfb;
				cursor: move;
				float: left;
				margin: 5px;
				padding: 10px;
				width: 120px;
				height: 46px;
			}
			.everything-option-layout > .placeholder {
				border-style: dashed;
				background: none;
			}
			.everything-option-layout > div > select {
				font-weight: normal;
				min-width: auto;
				width: 100%;
			}
EOS;
	}

	protected function _scripts($instance_num)
	{
		static $outputted = false;
		if ($outputted) {
			return '';
		}
		$outputted = true;
		return
<<<'EOS'
			jQuery(document).ready(function($) {
				var attach = function(e, options) {
					$('.everything-option-layout:not(.drone-ready)', options).addClass('drone-ready').sortable({
						items:       '> div',
						placeholder: 'placeholder'
					});
				};
				$('body').on('drone_options_attach', attach); attach();
			});
EOS;
	}

	protected function _sanitize($value)
	{
		$value = (array)$value;
		$value = array_intersect($value, array_merge(['#'], array_keys($this->options)));
		if (count($value) > 3) {
			$value = array_slice($value, -3);
		}
		if (count($value) < 3 || count(array_keys($value, '#')) != 1) {
			$value = $this->default;
		}
		return $value;
	}

	protected function _html()
	{
		$html = HTML::div()->class($this->getCSSClass(__CLASS__));
		foreach ($this->value as $value) {
			if ($value == '#') {
				$html->addNew('div')->add(
					__('Content', 'everything'), HTML::makeInput('hidden', $this->input_name . '[]', '#')
				);
			} else {
				$html->addNew('div')->add(
					__('Sidebar', 'everything'), '<br />', HTML::makeSelect($this->input_name . '[]', $value, $this->options)
				);
			}
		}
		return $html;
	}

}

/**
 * Social media
 */
class SocialMedia extends \Drone\Options\Option\Complex
{

	protected $icon;
	protected $title;
	protected $url;

	protected function _options()
	{
		return [
			'icon'  => 'image_select',
			'title' => 'text',
			'url'   => 'codeline'
		];
	}

	protected function _html()
	{
		$html = HTML::div()->class($this->getCSSClass(__CLASS__));
		$html->add(
			$this->icon->html(),
			HTML::div()->style('margin: 0 0 4px 55px;')->add(
				$this->title->html()
			),
			$this->url->html()
		);
		return $html;
	}

	public function __construct($name, $default, $properties = [])
	{
		parent::__construct($name, $default, $properties);
		$this->icon->options = function () {
			$icons = array_intersect_key(\Drone\Options\Option\ImageSelect::cssToOptions('data/img/icons/icons.css'), array_flip([
				'mail',
				'aim',
				'amazon',
				'app-store',
				'apple',
				'arto',
				'aws',
				'baidu',
				'basecamp',
				'bebo',
				'behance',
				'bing',
				'blip',
				'blogger',
				'bnter',
				'brightkite',
				'cloudapp',
				'dailybooth',
				'delicious',
				'designfloat',
				'designmoo',
				'deviantart',
				'digg',
				'diigo',
				'dribbble',
				'dropbox',
				'drupal',
				'dzone',
				'ebay',
				'ember',
				'etsy',
				'evernote',
				'facebook',
				'facebook-alt',
				'facebook-places',
				'feedburner',
				'flickr',
				'folkd',
				'forrst',
				'foursquare',
				'friendfeed',
				'friendster',
				'gdgt',
				'github',
				'goodreads',
				'googleplus',
				'gowalla',
				'gowalla-alt',
				'grooveshark',
				'hacker-news',
				'hi5',
				'hype-machine',
				'hyves',
				'icq',
				'instapaper',
				'itunes',
				'kik',
				'krop',
				'last',
				'linkedin',
				'linkedin-alt',
				'livejournal',
				'lovedsgn',
				'meetup',
				'metacafe',
				'mister-wong',
				'mobileme',
				'msn-messenger',
				'myspace',
				'newsvine',
				'official',
				'openid',
				'orkut',
				'pandora',
				'path',
				'paypal',
				'photobucket',
				'picasa',
				'pinboard',
				'ping',
				'pingchat',
				'pinterest',
				'playstation',
				'plixi',
				'plurk',
				'podcast',
				'posterous',
				'qik',
				'quora',
				'rdio',
				'readernaut',
				'reddit',
				'retweet',
				'rss',
				'scribd',
				'sharethis',
				'simplenote',
				'skype',
				'slashdot',
				'slideshare',
				'smugmug',
				'soundcloud',
				'spotify',
				'squarespace',
				'squidoo',
				'steam',
				'stumbleupon',
				'technorati',
				'tribe',
				'tripit',
				'tumblr',
				'twitter',
				'viddler',
				'vimeo',
				'virb',
				'vk',
				'w3',
				'whatsapp',
				'wikipedia',
				'windows',
				'wists',
				'wordpress',
				'wordpress-alt',
				'xbox360',
				'xing',
				'yahoo',
				'yahoo-buzz',
				'yahoo-messenger',
				'yelp',
				'youtube',
				'youtube-alt',
				'zerply',
				'zynga',
				'instagram',
				'telegram'
			]));
			ksort($icons);
			return apply_filters('everything_social_media_icons', $icons);
		};
		$this->icon->required  = false;
		$this->icon->font_path = \Everything::ICON_FONT_PATH;
		$this->icon->on_html   = function ($option, &$html) { $html->style('float: left;'); };
	}

}