<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */

namespace Everything\Widgets\Widget;

use \Drone\Widgets\Widget;
use \Drone\Func;
use \Drone\Template;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Social media
 */
class SocialMedia extends Widget
{

	protected function onSetupOptions(\Drone\Options\Group\Widget $options)
	{
		$options->addOption('text', 'title', '', __('Title', 'everything'));
		$options->addOption('memo', 'description', '', __('Description', 'everything'));
		$options->addOption('collection', 'icons', ['icon' => '', 'title' => '', 'url' => 'http://'], __('Icons', 'everything'), '', ['type' => 'social_media']);
		$options->addOption('select', 'gravity', 's', __('Tooltip position', 'everything'), '', ['options' => [
			'se' => __('Northwest', 'everything'),
			's'  => __('North', 'everything'),
			'sw' => __('Northeast', 'everything'),
			'e'  => __('West', 'everything'),
			'w'  => __('East', 'everything'),
			'ne' => __('Southwest', 'everything'),
			'n'  => __('South', 'everything'),
			'nw' => __('Southeast', 'everything')
		]]);
		$options->addOption('boolean', 'native_colors', true, '', '', ['caption' => __('Native hover colors', 'everything')]);
		$options->addOption('boolean', 'new_window', false, '', '', ['caption' => __('Open links in new window', 'everything')]);
	}

	protected function onWidget(array $args, &$html)
	{

		$template = Template::instance(<<<'EOT'
			[if:description]
				:description.wpautop
			[endif]
			[if:icons]
				<div class="social-icons[if:native_colors] native-colors[endif]">
					<ul class="alt">
						:icons
					</ul>
				</div>
			[endif]
EOT
		);

		$template
			->description($this->wo_('description')->translate())
			->native_colors($this->wo('native_colors'));

		foreach ($this->wo('icons') as $icon) {

			$template->icons .= Template::instance(<<<'EOT'
				<li>
					<a href=":url"
						[if:title]
							title=":title"
							class="tipsy-tooltip"
							data-tipsy-tooltip-gravity=":gravity"
						[endif]
						[if:new_window]
							target="_blank"
						[endif]
					>
						<i class="icon-:icon"></i>
					</a>
				</li>
EOT
			, array_merge($icon, [
				'gravity'    => $this->wo('gravity'),
				'new_window' => $this->wo('new_window')
			]));

		}


		$html = $template->build();

	}

	public function __construct()
	{
		parent::__construct(__('Social media', 'everything'), [
			'description' => __('Social media icons.', 'everything')
		]);
	}

}

/**
 * Social buttons
 */
class SocialButtons extends Widget
{

	protected function onSetupOptions(\Drone\Options\Group\Widget $options)
	{
		$options->addOption('text', 'title', '', __('Title', 'everything'));
		$options->addOption('memo', 'description', '', __('Description', 'everything'));
		$options->addOption('collection', 'media', '', __('Media', 'everything'), '', ['type' => 'select', 'trim_default' => false, 'options' => [
			'facebook'   => __('Facebook', 'everything'),
			'twitter'    => __('Twitter', 'everything'),
			'googleplus' => __('Google+', 'everything'),
			'linkedin'   => __('LinkedIn', 'everything'),
			'pinterest'  => __('Pinterest', 'everything')
		]]);
	}

	protected function onWidget(array $args, &$html)
	{

		if (!is_singular()) {
			return;
		}

		$atts['style'] = 'big';
		foreach (array_keys($this->wo_('media')->properties['options']) as $media) {
			$atts['media_' . $media] = in_array($media, $this->wo('media'));
		}

		if ($this->wo('description')) {
			$html = wpautop($this->wo_('description')->translate());
		}

		$html .= \Everything::getShortcodeOutput('social_buttons', $atts);

	}

	public function __construct()
	{
		parent::__construct(__('Social buttons', 'everything'), [
			'description' => __('Social media buttons.', 'everything')
		]);
	}

}

/**
 * Portfolio
 */
class Portfolio extends Widget
{

	protected function onSetupOptions(\Drone\Options\Group\Widget $options)
	{
		$options->addOption('text', 'title', '', __('Title', 'everything'));
		$options->addOption('select', 'id', 0, __('Children of', 'everything'), __('Displays child portfolios of selected portfolio.', 'everything'), ['required' => false, 'options' => function () {
			return array_map(function ($s) { return Func::stringCut($s, 55); }, Func::wpPagesList(['post_type' => 'portfolio']));
		}]);
		$options->addOption('select', 'columns', '2', __('Layout', 'everything'), '', ['options' => [
			'1'  => __('One column', 'everything'),
			'1+' => __('One+ column', 'everything'),
			'2'  => __('Two columns', 'everything'),
			'3'  => __('Three columns', 'everything'),
			'4'  => __('Four columns', 'everything')
		]]);
		$options->addOption('select', 'filter', '', __('Filter', 'everything'), '', ['options' => [
			''         => '(' . __('None', 'everything') . ')',
			'category' => __('Category', 'everything'),
			'tag'      => __('Tag', 'everything')
		]]);
		$options->addOption('select', 'orderby', 'date', __('Sort by', 'everything'), '', ['options' => [
			'title'         => __('Title', 'everything'),
			'date'          => __('Date', 'everything'),
			'modified'      => __('Modified date', 'everything'),
			'comment_count' => __('Comment count', 'everything'),
			'rand'          => __('Random order', 'everything'),
			'menu_order'    => __('Custom order', 'everything')
		]]);
		$options->addOption('select', 'order', 'desc', __('Sort order', 'everything'), '', ['options' => [
			'asc'  => __('Ascending', 'everything'),
			'desc' => __('Descending', 'everything')
		]]);
		$options->addOption('number', 'count', '', __('Count', 'everything'), '', ['required' => false, 'min' => 1]);
		$options->addOption('boolean', 'show_title', true, '', '', ['caption' => __('Show title', 'everything')]);
		$content = $options->addGroup('content', __('Content', 'everything'));
			$visible = $content->addOption('boolean', 'visible', false, '', '', ['caption' => __('Show content', 'everything')]);
			$content->addOption('group', 'content', 'excerpt_content', '', __('Regular content means everything before the "Read more" tag.', 'everything'), ['options' => [
				'content'         => __('Regular content', 'everything'),
				'excerpt_content' => __('Excerpt or regular content', 'everything'),
				'excerpt'         => __('Excerpt', 'everything')
			], 'indent' => true, 'owner' => $visible]);
		$taxonomy = $options->addGroup('taxonomy', __('Taxonomy', 'everything'));
			$visible = $taxonomy->addOption('boolean', 'visible', true, '', '', ['caption' => __('Show taxonomies', 'everything')]);
			$taxonomy->addOption('select', 'taxonomy', 'tag', '', '', ['options' => [
				'category' => __('Categories', 'everything'),
				'tag'      => __('Tags', 'everything')
			], 'indent' => true, 'owner' => $visible]);
		$options->addOption('select', 'image_hover', 'inherit', __('Hover effect', 'everything'), '', ['options' => [
			'inherit'   => __('Inherit', 'everything'),
			''          => __('None', 'everything'),
			'zoom'      => __('Default', 'everything'),
			'grayscale' => __('Grayscale', 'everything')
		]]);
	}

	protected function onWidget(array $args, &$html)
	{

		if (!$this->wo('id')) {
			return;
		}

		$widget = $this;

		// Columns
		$columns_int  = (int)rtrim($this->wo('columns'), '+');
		$columns_plus = $this->wo('columns') != (string)$columns_int;

		// Posts
		$query = new \WP_Query([
			'posts_per_page' => $this->wo('count', '__empty', -1),
			'post_status'    => 'publish',
			'post_type'      => 'portfolio',
			'post_parent'    => $this->wo('id'),
			'post__not_in'   => is_single() ? [get_the_ID()] : [],
			'orderby'        => $this->wo('orderby'),
			'order'          => $this->wo('order')
		]);
		if (!$query->have_posts()) {
			return;
		}

		// Template
		$template = Template::instance(<<<'EOT'
			<div class="bricks" data-bricks-columns=":columns" data-bricks-filter=":filter">
				:items
			</div>
EOT
		);

		$template
			->columns($columns_int)
			->filter((bool)$this->wo('filter'));

		// Items
		while ($query->have_posts()) {

			$query->the_post();

			$item_template = Template::instance(<<<'EOT'
				<div[if:columns_plus] class="one-plus"[endif][if:filter] data-bricks-terms=":terms"[endif]>
					<article id="portfolio-item-:id" class=":class">
						[if:columns_plus]<div class="columns"><ul><li class="col-2-3">[endif]

							[if:thumbnail]
								<figure class="thumbnail featured full-width">
									<a href=":url" :link_attrs>
										:thumbnail
									</a>
								</figure>
							[endif]

						[if:columns_plus]</li><li class="col-1-3">[endif]

							[if:show_title]
								<:title_tag><a href=":url" title=":title">:title</a></:title_tag>
							[endif]

							:content

							[if:taxonomies]
								<p class="small alt">:taxonomies</p>
							[endif]

						[if:columns_plus]</li></ul></div>[endif]
					</article>
				</div>
EOT
			);

			$item_template

				->filter($this->wo('filter'))
				->show_title($this->wo('show_title'))

				->id(get_the_ID())
				->class(implode(' ', get_post_class(['portfolio-item', 'hentry'])))
				->title(get_the_title())
				->url(\Everything::po('portfolio/link/type') == 'external' ? \Everything::po('portfolio/link/url') : get_permalink())

				->columns_plus($columns_plus)
				->link_attrs(\Everything::getImageAttrs('a', ['border' => false, 'hover' => $this->wo('image_hover'), 'fancybox' => false], 'html'))
				->title_tag($columns_int == 1 ? 'h2' : 'h3')

				->terms(function () use ($widget) {
					return array_values(Func::wpPostTermsList(get_the_ID(), 'portfolio-' . $widget->wo('filter')));
				})
				->thumbnail(function () {
					if (has_post_thumbnail()) {
						return get_the_post_thumbnail(null, apply_filters('everything_image_size', 'column-4', 'widget_portfolio'));
					}
				})
				->content(function () use ($widget) {
					switch ($widget->wo('content/content', '__hidden')) {
						case 'excerpt':
							return '<p>' . get_the_excerpt() . '</p>';
						case 'excerpt_content':
							if (has_excerpt()) {
								return '<p>' . get_the_excerpt() . '</p>';
							}
						case 'content':
							$GLOBALS['more'] = 0;
							return \Drone\Func::wpProcessContent(get_the_content(\Everything::getReadMore()));
					}
				})
				->taxonomies(function () use ($widget) {
					if ($widget->wo('taxonomy/visible')) {
						return get_the_term_list(get_the_ID(), 'portfolio-' . $widget->wo('taxonomy/taxonomy'), '', ', ');
					}
				});

			if ($this->wo('show_title') || $this->wo('content/visible') || $this->wo('taxonomy/visible')) {
				$item_template->class .= ' bordered';
			}

			$template->items .= $item_template;

		}

		wp_reset_postdata();

		$html = $template->build();

	}

	public function __construct()
	{
		parent::__construct(__('Portfolio', 'everything'), [
			'description' => __('Displays Portfolio or child portfolios of a specified parent porfolio.', 'everything')
		]);
	}

}