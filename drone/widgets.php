<?php








namespace Drone\Widgets;

use Drone\Func;
use Drone\Template;
use Drone\Options;
use Drone\Theme;









class Widget extends \WP_Widget
{







	const LABEL_SEPARATOR = '|';






	private $_options;






	private $_id;







	private $_id_;








	protected function onSetupOptions(\Drone\Options\Group\Widget $options) { }









	public function onOptionsCompatybility(array &$data, $version) { }









	protected function onWidget(array $args, &$html) { }









	protected function getOptions($data = null)
	{


		$options = new Options\Group\Widget(str_replace('[#]', '', $this->get_field_name('#')));
		$this->onSetupOptions($options);
		\Drone\do_action("widget_{$this->_id_}_on_setup_options", $options, $this);


		if (is_int($data)) {
			$settings = $this->get_settings();
			if (isset($settings[$data])) {
				$data = $settings[$data];
			}
		}
		if (is_array($data)) {
			$options->fromArray($data, [$this, 'onOptionsCompatybility']);
			\Drone\do_action("widget_{$this->_id_}_on_load_options", $options, $this);
		}

		return $options;

	}
























	public function __construct($label, $params = [])
	{

		if (!is_array($params)) {
			_deprecated_argument(get_class($this) . '::' . __FUNCTION__, '5.7', 'Use $params argument instead.');
			$args   = func_get_args();
			$params = [];
			switch (count($args)) {
				case 4: $params['width'] = $args[3];
				case 3: $params['class'] = $args[2];
				case 2: $params['description'] = $args[1];
			}
			$params = array_filter($params);
		}


		$name = str_replace([
			__CLASS__ . '\\',
			Theme::instance()->class . '\Widgets\Widget\\',
			Theme::instance()->class . 'Widget',
			Theme::instance()->class
		], '', get_class($this));

		$this->_id  = Func::stringID($name);
		$this->_id_ = Func::stringID($name, '_');


		$params += [
			'description' => '',
			'class'       => 'widget-' . $this->_id,
			'width'       => null
		];


		parent::__construct(
			\Drone\apply_filters('widget_id_base', Theme::instance()->base_theme->id_ . '_' . $this->_id_, $this->_id_),
			Theme::instance()->theme->name . ' ' . self::LABEL_SEPARATOR . ' ' . $label,
			['classname' => $params['class'], 'description' => $params['description']],
			['width' => $params['width']]
		);

	}









	public function __get($name)
	{
		switch ($name) {
			case '_id':
			case '_id_':
				return $this->{$name};
		}
	}










	public function wo_($name, $skip_if = null)
	{
		return $this->_options->findChild($name, $skip_if);
	}











	public function wo($name, $skip_if = null, $fallback = null)
	{
		$child = $this->wo_($name, $skip_if);
		return $child !== null && $child->isOption() ? $child->value : $fallback;
	}







	public function form($instance)
	{
		echo \Drone\HTML::div()
			->class('drone-widget-options')
			->add($this->getOptions($instance)->html());
	}







	public function update($new_instance, $old_instance)
	{
		$options = $this->getOptions($old_instance);
		$options->change($new_instance);
		return $options->toArray();
	}






	public function widget($args, $instance)
	{


		$this->_options = $this->getOptions($instance);


		$html = '';
		$this->onWidget((array)$args, $html);

		$html = \Drone\apply_filters("widget_{$this->_id_}_html", $html, $this, $args);

		if (!trim($html)) {
			return;
		}


		$template = \Drone\apply_filters('widget_template', <<<"EOT"
			:before_widget
				[if:title]
					:before_title:title:after_title
				[endif]
				:widget
			:after_widget
EOT
		, $this, $args);

		$output = Template::instance($template, $args)
			->title(\apply_filters('widget_title', $this->wo('title'), $instance, $this->id_base))
			->widget($html)
			->build();


		$output = \Drone\apply_filters('widget_output', $output, $this, $args);


		echo $output;

	}

}

namespace Drone\Widgets\Widget;

use Drone\Widgets\Widget;
use Drone\Func;
use Drone\Template;
use Drone\Theme;






class UnwrappedText extends Widget
{







	protected function onSetupOptions(\Drone\Options\Group\Widget $options)
	{
		$options->addOption('text', 'title', '', __('Title', 'everything'));
		$options->addOption('code', 'text', '', __('Text', 'everything'), '', ['on_html' => function ($option, &$html) {
			$html->css('height', '25em');
		}]);
		$options->addOption('boolean', 'paragraphs', false, '', '', ['caption' => __('Automatically add paragraphs', 'everything')]);
		$options->addOption('boolean', 'shortcodes', false, '', '', ['caption' => __('Allow shortcodes', 'everything')]);
	}







	protected function onWidget(array $args, &$html)
	{

		$html = $this->wo_('text')->translate();

		if ($this->wo('paragraphs')) {
			$html = wpautop($html);
		}

		if ($this->wo('shortcodes')) {
			if ($this->wo('paragraphs')) {
				$html = shortcode_unautop($html);
			}
			$html = do_shortcode($html);
		}

	}







	public function __construct()
	{
		parent::__construct(__('Unwrapped text', 'everything'), [
			'description' => __('For pure HTML code.', 'everything'),
			'width'       => 600
		]);
	}

}






class Page extends Widget
{







	protected function onSetupOptions(\Drone\Options\Group\Widget $options)
	{
		$options->addOption('text', 'title', '', __('Title', 'everything'));
		$options->addOption('post', 'id', 0, __('Page', 'everything'), '', ['required' => false, 'options' => function () {
			return Func::wpPagesList();
		}]);
	}







	public function onOptionsCompatybility(array &$data, $version)
	{
		if (version_compare($version, '5.2.6') < 0) {
			if (isset($data['page'])) {
				$data['id'] = $data['page'];
			}
		}
	}







	protected function onWidget(array $args, &$html)
	{
		if (function_exists('is_bbpress') && is_bbpress()) {
			bbp_restore_all_filters('the_content');
		}
		$html = $this->wo_('id')->getContent();
	}







	public function __construct()
	{
		parent::__construct(__('Page', 'everything'), [
			'description' => __('Displays content of a specified page.', 'everything')
		]);
	}

}






class Contact extends Widget
{







	protected function onSetupOptions(\Drone\Options\Group\Widget $options)
	{
		$options->addOption('text', 'title', '', __('Title', 'everything'));
		$options->addOption('memo', 'description', '', __('Description', 'everything'));
	}







	protected function onWidget(array $args, &$html)
	{
		if ($this->wo('description')) {
			$html .= wpautop($this->wo_('description')->translate());
		}
		$html .= Theme::getContactForm('widget-' . $args['id']);
	}







	public function __construct()
	{
		parent::__construct(__('Contact form', 'everything'), [
			'description' => __('Displays contact form, which can be configured in Theme Options.', 'everything')
		]);
	}

}






class PostsList extends Widget
{







	protected function onSetupOptions(\Drone\Options\Group\Widget $options)
	{
		$options->addOption('text', 'title', '', __('Title', 'everything'));
		$options->addOption('select', 'category', 0, __('Category', 'everything'), '', ['options' => function () {
			return [
				0         => '(' . __('All', 'everything') . ')',
				'current' => '(' . __('Current', 'everything') . ')'
			] + Func::wpTermsList('category', ['hide_empty' => false]);
		}]);
		$options->addOption('select', 'format', 0, __('Format', 'everything'), '', ['included' => current_theme_supports('post-formats'), 'options' => function () {
			return [
				''         => '(' . __('All', 'everything') . ')',
				'current'  => '(' . __('Current', 'everything') . ')',
				'standard' => __('Standard', 'everything')
			] + Func::arrayMapKeys(function ($s) {
				return str_replace('post-format-', '', $s);
			}, Func::wpTermsList('post_format', ['hide_empty' => false], 'slug'));
		}]);
		$options->addOption('select', 'orderby', 'date', __('Sort by', 'everything'), '', ['options' => [
			'title'         => __('Title', 'everything'),
			'date'          => __('Date', 'everything'),
			'modified'      => __('Modified date', 'everything'),
			'comment_count' => __('Comment count', 'everything'),
			'rand'          => __('Random order', 'everything')
		]]);
		$options->addOption('select', 'order', 'desc', __('Sort order', 'everything'), '', ['options' => [
			'asc'  => __('Ascending', 'everything'),
			'desc' => __('Descending', 'everything')
		]]);
		$options->addOption('number', 'count', 5, __('Posts count', 'everything'), '', ['min' => 1, 'max' => 50]);
		$options->addOption('number', 'limit', 10, __('Post title words limit', 'everything'), '', ['min' => 1]);
		$options->addOption('boolean', 'author', false, '', '', ['caption' => __('Show post author', 'everything')]);
		$options->addOption('boolean', 'comments', false, '', '', ['caption' => __('Show comments count', 'everything')]);
		$options->addOption('boolean', 'exclude_previous', false, '', '', ['caption' => __('Exclude already displayed posts', 'everything')]);
	}







	protected function onWidget(array $args, &$html)
	{


		$tax_query = [];
		switch ($this->wo('format', '__hidden')) {
			case '':
				break;
			case 'current':
				if ($term_id = Func::wpGetCurrentTermID('post_format')) {
					$tax_query[] = [
						'taxonomy' => 'post_format',
						'field'    => 'term_id',
						'terms'    => [$term_id]
					];
				}
				break;
			case 'standard':
				$tax_query[] = [
					'taxonomy' => 'post_format',
					'field'    => 'slug',
					'terms'    => Func::wpTermsList('post_format', ['hide_empty' => false], 'term_id', 'slug'),
					'operator' => 'NOT IN'
				];
				break;
			default:
				$tax_query[] = [
					'taxonomy' => 'post_format',
					'field'    => 'slug',
					'terms'    => ['post-format-' . $this->wo('format')]
				];
		}


		$exclude = is_single() ? [get_the_ID()] : [];
		if ($this->wo('exclude_previous')) {
			$exclude = array_merge($exclude, Theme::instance()->posts_stack);
		}


		$posts = get_posts([
			'category'         => $this->wo('category') === 'current' ? Func::wpGetCurrentTermID('category') : $this->wo('category'),
			'tax_query'        => $tax_query,
			'numberposts'      => $this->wo('count'),
			'orderby'          => $this->wo('orderby'),
			'order'            => strtoupper($this->wo('order')),
			'exclude'          => array_unique($exclude),
			'suppress_filters' => false
		]);
		if (count($posts) == 0) {
			return;
		}


		$template = Template::instance(\Drone\apply_filters("widget_{$this->_id_}_template", <<<'EOT'
			<ul>
				:posts
			</ul>
EOT
		, $this));


		foreach ($posts as $post) {

			$post_template = Template::instance(\Drone\apply_filters("widget_{$this->_id_}_post_template", <<<'EOT'
				<li>
					<a href=":permalink" title=":title">:display_title</a>
					:author
					[if:comments]
						(:comments)
					[endif]
				</li>
EOT
			, $this, $post));

			$post_template
				->permalink(\apply_filters('the_permalink', get_permalink($post->ID)))
				->title($post->post_title)
				->display_title(wp_trim_words($post->post_title, $this->wo('limit')));

			if ($this->wo('author')) {
				$author = get_userdata($post->post_author);
				$post_template->author = sprintf(
					str_replace(' ', '&nbsp;', __('by %s', 'everything')),
					Template::instance(
						'<a href=":url" title=":display_name">:display_name</a>',
						['url' => get_author_posts_url($post->post_author), 'display_name' => $author->display_name]
					)
				);
			}

			if ($this->wo('comments')) {
				$post_template
					->comments($post->comment_count)
					->comments_text(function () use ($post) {
						$GLOBALS['post'] = $post;
						$comments_text = get_comments_number_text();
						wp_reset_postdata();
						return $comments_text;
					});
			}

			$template->posts .= $post_template;

		}

		$html = $template->build();

	}







	public function __construct()
	{
		parent::__construct(__('Posts list', 'everything'), [
			'description' => __('Displays list of posts by specific criteria (e.g.: newest posts, most commented, random posts, etc.).', 'everything')
		]);
	}

}






class Twitter extends Widget
{







	protected function onSetupOptions(\Drone\Options\Group\Widget $options)
	{
		$options->addOption('text', 'title', '',  __('Title', 'everything'));
		$options->addOption('codeline', 'username', '', __('Username', 'everything'), '', [
			'on_sanitize' => function ($option, $original_value, &$value) {
				if (preg_match('|^((https?://)?(www\.)?twitter\.com/(#!/)?)?(?P<username>.+?)/?$|i', $value, $matches)) {
					$value = $matches['username'];
				}
			}
		]);
		$options->addOption('number', 'count', 5, __('Tweets count', 'everything'), '', ['min' => 1, 'max' => 20]);
		$options->addOption('interval', 'interval', ['quantity' => 30, 'unit' => 'm'], __('Update interval', 'everything'), __('Tweets receiving interval.', 'everything'), ['min' => '1m']);
		$options->addOption('boolean', 'include_retweets', true, '', '', ['caption' => __('Include retweets', 'everything')]);
		$options->addOption('boolean', 'exclude_replies', false, '', '', ['caption' => __('Exclude replies', 'everything')]);
		$options->addOption('boolean', 'embed_media', false, '', '', ['caption' => __('Embed media', 'everything')]);
		$oauth = $options->addGroup('oauth');
			$oauth->addOption('codeline', 'consumer_key', '', __('API key', 'everything'));
			$oauth->addOption('codeline', 'consumer_secret', '', __('API secret', 'everything'), '', ['password' => true]);
			$oauth->addOption('codeline', 'access_token', '', __('Access token', 'everything'));
			$oauth->addOption('codeline', 'access_token_secret', '', __('Access token secret', 'everything'), '', ['password' => true]);
	}







	protected function onWidget(array $args, &$html)
	{


		if (!$this->wo('username')) {
			return;
		}


		$options = [
			'username'         => $this->wo('username'),
			'count'            => $this->wo('count'),
			'interval'         => $this->wo_('interval')->seconds(),
			'include_retweets' => $this->wo('include_retweets'),
			'exclude_replies'  => $this->wo('exclude_replies'),
			'embed_media'      => $this->wo('embed_media'),
			'oauth'            => $this->wo_('oauth')->toArray()
		];

		$tweets = Theme::instance()->getTransient(
			'twitter_' . crc32(serialize($options)),
			function (&$expiration, $outdated_value) use ($options) {
				$expiration = $options['interval'];
				$value = Func::twitterGetTweets(
					$options['oauth'],
					$options['username'],
					$options['include_retweets'],
					$options['exclude_replies'],
					$options['embed_media'],
					$options['count']
				);
				if ($value === false) {
					return $outdated_value;
				}
				return $value;
			}
		);

		if ($tweets === false) {
			return;
		}


		$template = Template::instance(\Drone\apply_filters("widget_{$this->_id_}_template", <<<'EOT'
			<ul>
				:tweets
			</ul>
EOT
		, $this));


		foreach ($tweets as $tweet) {

			$tweet_template = Template::instance(\Drone\apply_filters("widget_{$this->_id_}_tweet_template", <<<'EOT'
				<li>
					:content<br />
					<small><a href=":url">:time_diff</a></small>
				</li>
EOT
			, $this, $tweet));

			$tweet_template
				->content($tweet['html'])
				->url($tweet['url'])
				->time_diff(sprintf(__('%s ago', 'everything'), human_time_diff($tweet['date'])));

			$template->tweets .= $tweet_template;

		}

		$html = $template->build();

	}







	protected function getOptions($data = null)
	{
		$options = parent::getOptions($data);
		if ($data !== null && $options->isDefault()) {
			foreach ($this->get_settings() as $settings) {
				if (isset($settings['oauth']['consumer_key'])        && $settings['oauth']['consumer_key'] &&
					isset($settings['oauth']['consumer_secret'])     && $settings['oauth']['consumer_secret'] &&
					isset($settings['oauth']['access_token'])        && $settings['oauth']['access_token'] &&
					isset($settings['oauth']['access_token_secret']) && $settings['oauth']['access_token_secret']) {
					$options->child('oauth')->fromArray($settings['oauth']);
					break;
				}
			}
		}
		return $options;
	}







	public function __construct()
	{
		parent::__construct(__('Twitter', 'everything'), [
			'description' => __('Twitter stream.', 'everything')
		]);
	}

}






class Flickr extends Widget
{







	protected function onSetupOptions(\Drone\Options\Group\Widget $options)
	{
		$options->addOption('text', 'title', '',  __('Title', 'everything'));
		$options->addOption('codeline', 'username', '', __('Username', 'everything'), __('Screen name from Flickr account settings.', 'everything'));
		$options->addOption('number', 'count', 4, __('Photos count', 'everything'), '', ['min' => 1, 'max' => 50]);
		$options->addOption('interval', 'interval', ['quantity' => 30, 'unit' => 'm'], __('Update interval', 'everything'), __('Photos receiving interval.', 'everything'), ['min' => '1m']);
		$options->addOption('select', 'url', 'flickr', 'Action after clickng on a photo', '', ['options' => [
			'flickr' => __('Open Flickr page with the photo', 'everything'),
			'image'  => __('Open bigger version of the photo', 'everything')
		]]);
		$options->addOption('codeline', 'api_key', '', __('API Key', 'everything'));
	}







	protected function onWidget(array $args, &$html)
	{


		if (!$this->wo('username')) {
			return;
		}


		$options = [
			'username' => $this->wo('username'),
			'count'    => $this->wo('count'),
			'interval' => $this->wo_('interval')->seconds(),
			'api_key'  => $this->wo('api_key')
		];

		$photos = Theme::instance()->getTransient(
			'flickr_' . crc32(serialize($options)),
			function (&$expiration, $outdated_value) use ($options) {
				$expiration = $options['interval'];
				if (
					($userdata = Func::flickrGetUserdata($options['api_key'], $options['username'])) === false ||
					($value = Func::flickrGetPhotos($options['api_key'], $userdata['id'], $options['count'])) === false
				) {
					return $outdated_value;
				}
				return $value;
			}
		);

		if ($photos === false) {
			return;
		}


		$template = Template::instance(\Drone\apply_filters("widget_{$this->_id_}_template", <<<'EOT'
			<ul>
				:photos
			</ul>
EOT
		, $this));


		foreach ($photos as $photo) {

			$photo_template = Template::instance(\Drone\apply_filters("widget_{$this->_id_}_photo_template", <<<'EOT'
				<li>
					<a href=":url" title=":title" rel=":id">
						<img src=":src" width="75" height="75" alt=":title" />
					</a>
				</li>
EOT
			, $this, $photo));

			$photo_template
				->id($this->id)
				->url($this->wo('url') == 'flickr' ? $photo['url'] : sprintf($photo['src'], 'b'))
				->src(sprintf($photo['src'], 's'))
				->title($photo['title']);

			$template->photos .= $photo_template;

		}

		$html = $template->build();

	}







	public function __construct()
	{
		parent::__construct(__('Flickr', 'everything'), [
			'description' => __('Flickr photo stream.', 'everything')
		]);
	}

}






class Instagram extends Widget
{







	protected function onSetupOptions(\Drone\Options\Group\Widget $options)
	{
		$app = $options->addOption('instagram_app', 'app', null, __('Application', 'everything'));
		$authorized = $app->isAuthorized();
		$options->addOption('text', 'title', '',  __('Title', 'everything'), '', ['included' => $authorized]);
		$options->addOption('number', 'count', 4, __('Photos count', 'everything'), '', ['included' => $authorized, 'min' => 1, 'max' => 50]);
		$options->addOption('interval', 'interval', ['quantity' => 30, 'unit' => 'm'], __('Update interval', 'everything'), __('Photos receiving interval.', 'everything'), ['included' => $authorized, 'min' => '1m']);
		$options->addOption('select', 'url', 'instagram', 'Action after clickng on a photo', '', ['included' => $authorized, 'options' => [
			'instagram' => __('Open Instagram page with the photo', 'everything'),
			'image'     => __('Open bigger version of the photo', 'everything')
		]]);
	}







	protected function onWidget(array $args, &$html)
	{

		$app = $this->wo_('app');


		if (!$app->isAuthorized()) {
			return;
		}


		$options = [
			'access_token' => $app->getAccessToken(),
			'count'        => $this->wo('count'),
			'interval'     => $this->wo_('interval')->seconds()
		];

		$photos = Theme::instance()->getTransient(
			'instagram_' . crc32(serialize($options)),
			function (&$expiration, $outdated_value) use ($options) {
				$expiration = $options['interval'];
				$value = Func::instagramGetImages($options['access_token'], 'self', $options['count']);
				if ($value === false) {
					return $outdated_value;
				}
				return $value;
			}
		);

		if ($photos === false) {
			return;
		}


		$template = Template::instance(\Drone\apply_filters("widget_{$this->_id_}_template", <<<'EOT'
			<ul>
				:photos
			</ul>
EOT
		, $this));


		foreach ($photos as $photo) {

			$photo_template = Template::instance(\Drone\apply_filters("widget_{$this->_id_}_photo_template", <<<'EOT'
				<li>
					<a href=":url" title=":title" rel=":id">
						<img src=":src" width="150" height="150" alt=":title" />
					</a>
				</li>
EOT
			, $this, $photo));

			$photo_template
				->id($this->id)
				->url($this->wo('url') == 'instagram' ? $photo['link'] : $photo['images']['standard_resolution']['url'])
				->src($photo['images']['thumbnail']['url'])
				->title(isset($photo['caption']['text']) ? $photo['caption']['text'] : null);

			$template->photos .= $photo_template;

		}

		$html = $template->build();

	}







	public function __construct()
	{
		parent::__construct(__('Instagram', 'everything'), [
			'description' => __('Instagram photo stream.', 'everything')
		]);
	}

}






class FacebookPage extends Widget
{







	protected function onSetupOptions(\Drone\Options\Group\Widget $options)
	{
		$options->addOption('text', 'title', '',  __('Title', 'everything'));
		$options->addOption('codeline', 'href', '', __('Facebook Page URL', 'everything'), sprintf(__('E.g. %s', 'everything'), '<code>https://www.facebook.com/platform</code>'), ['on_sanitize' => function ($option, $original_value, &$value) {
			$value = preg_replace('/\?[^\?]*$/', '', $value);
		}]);
		$options->addOption('boolean', 'small_header', false, '', '', ['caption' => __('Use small header', 'everything')]);
		$options->addOption('boolean', 'show_facepile', true, '', '', ['caption' => __('Show profile photos', 'everything')]);
		$show_posts = $options->addOption('boolean', 'show_posts', false, '', '', ['caption' => __('Show posts from the Page\'s timeline', 'everything')]);
		$options->addOption('number', 'height', 400, __('Height', 'everything'), '', ['unit' => 'px', 'min' => 70, 'max' => 1000, 'owner' => $show_posts, 'indent' => true]);
	}







	public function onOptionsCompatybility(array &$data, $version)
	{
		if (version_compare($version, '5.4.1') < 0) {
			if (isset($data['show_faces'])) {
				$data['show_facepile'] = $data['show_faces'];
			}
			if (isset($data['stream'])) {
				$data['show_posts'] = $data['stream'];
			}
		}
	}







	protected function onWidget(array $args, &$html)
	{


		wp_enqueue_script(Theme::instance()->theme->id . '-social-media-api');


		$template = Template::instance(\Drone\apply_filters("widget_{$this->_id_}_template", <<<'EOT'
			<div class="fb-page"
				data-href=":href"
				data-small-header=":small_header"
				data-show-facepile=":show_facepile"
				data-show-posts=":show_posts"
				[if:height]
					data-height=":height"
					style="height: :{height}px;"
				[endif]
			></div>
EOT
		, $this));


		$template
			->href($this->wo('href'))
			->small_header($this->wo('small_header'))
			->show_facepile($this->wo('show_facepile'))
			->show_posts($this->wo('show_posts'));

		if ($this->wo_('height')->isVisible()) {
			$template->height = $this->wo('height');
		}

		$html = $template->build();

	}







	public function __construct()
	{
		parent::__construct(__('Facebook Page', 'everything'), [
			'description' => __('Configurable Facebook widget.', 'everything')
		]);
	}

}







class FacebookLikeBox extends FacebookPage {}