<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */

$banner = Everything::io('layout/banner/banner', 'banner/content', '__hidden_ns');
$banner = apply_filters('everything_banner', $banner);
if (!$banner['type']) {
	return;
}

?>

<div id="banner" class="outer-container banner-type-<?php echo $banner['type']; ?>">

	<?php if ($banner['type'] == 'empty' && $banner['height']): // Empty ?>

		<div class="container" style="height: <?php echo $banner['height']; ?>px;"></div>

	<?php elseif ($banner['type'] == 'image' && $banner['image']): // Image ?>

		<figure class="full-width">
			<?php echo wp_get_attachment_image($banner['image'], Everything::to_('general/layout')->value() == 'boxed' ? 'max-width' : 'full-hd'); ?>
		</figure>

	<?php elseif ($banner['type'] == 'thumbnail' && has_post_thumbnail()): // Featured image ?>

		<figure class="full-width">
			<?php the_post_thumbnail(Everything::to_('general/layout')->value() == 'boxed' ? 'max-width' : 'full-hd'); ?>
		</figure>

	<?php elseif ($banner['type'] == 'slider' && preg_match('/^(?P<type>(layer|master|rev)slider)-(?P<id>[0-9]+)$/', $banner['slider'], $m)): // Slider ?>

		<?php
			switch ($m['type']) {
				case 'layerslider':
					if (Everything::isPluginActive('layerslider')) {
						layerslider($m['id']);
					}
					break;
				case 'masterslider':
					if (Everything::isPluginActive('masterslider')) {
						masterslider($m['id']);
					}
					break;
				case 'revslider':
					if (Everything::isPluginActive('revslider')) {
						putRevSlider($m['id']);
					}
					break;
			}
		?>

	<?php elseif ($banner['type'] == 'map' && preg_match('/^(?P<type>wild-googlemap|wp-google-map-plugin)-(?P<id>[0-9]+)$/', $banner['map'], $m)): // Map ?>

		<?php
			switch ($m['type']) {
				case 'wild-googlemap':
					if (Everything::isPluginActive('wild-googlemap')) {
						echo WiLD_GooglemapManager::getInstance()->render(['id' => $m['id']]);
					}
					break;
				case 'wp-google-map-plugin':
					if (Everything::isPluginActive('wp-google-map-plugin')) {
						echo do_shortcode('[put_wpgm id="' . $m['id'] . '"]');
					}
					break;
			}
		?>

	<?php elseif ($banner['type'] == 'page' && !is_null($page = get_post((int)$banner['page']))): // Page ?>

		<div class="container"><?php echo \Drone\Func::wpProcessContent($page->post_content); ?></div>

	<?php elseif ($banner['type'] == 'embed' && $banner['embed']): // Embed ?>

		<div class="embed"><?php echo $banner['embed']; ?></div>

	<?php elseif ($banner['type'] == 'custom'): // Custom ?>

		<?php echo $banner['custom']; ?>

	<?php endif; ?>

</div><!-- // .outer-container -->