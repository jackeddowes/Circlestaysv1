<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */
?>

<?php if (
	has_post_thumbnail() && !post_password_required() &&
	apply_filters('everything_post_thumbnail_display', Everything::to_([sprintf('format_posts/%s/thumbnail', get_post_format()), 'format_posts/standard/thumbnail'])->value(is_singular() ? 'single' : 'list'))
): ?>
	<figure class="thumbnail full-width featured">
		<?php if (is_singular()): ?>
			<div <?php Everything::imageAttrs('div', ['border' => false]); ?>>
				<?php the_post_thumbnail(apply_filters('everything_image_size', 'column-' . min(Everything::to('site/blog/columns'), 4), 'post_columns')); ?>
			</div>
		<?php else: ?>
			<a href="<?php the_permalink(); ?>" <?php Everything::imageAttrs('a', ['border' => false]); ?>>
				<?php the_post_thumbnail(apply_filters('everything_image_size', 'column-' . min(Everything::to('site/blog/columns'), 4), 'post_columns')); ?>
			</a>
		<?php endif; ?>
	</figure>
<?php endif; ?>