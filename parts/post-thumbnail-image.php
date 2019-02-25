<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */
?>

<?php if (
	has_post_thumbnail() && !post_password_required() &&
	apply_filters('everything_post_thumbnail_display', Everything::to_('format_posts/image/thumbnail')->value(is_singular() ? 'single' : 'list'))
): ?>
	<figure class="thumbnail full-width">
		<a href="<?php
			if (Everything::to('format_posts/image/link') == 'post' && !is_singular()):
				the_permalink();
			else:
				echo wp_get_attachment_image_url(get_post_thumbnail_id(), 'full');
			endif;
		?>" <?php Everything::imageAttrs('a'); ?>>
			<?php the_post_thumbnail(apply_filters('everything_image_size', 'full-width', 'post_image')); ?>
		</a>
	</figure>
<?php endif; ?>