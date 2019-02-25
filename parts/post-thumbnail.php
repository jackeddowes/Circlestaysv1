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
	<figure class="thumbnail align<?php echo Everything::to('post/thumbnail/align'); ?> fixed">
		<?php if (is_singular()): ?>
			<div <?php Everything::imageAttrs('div'); ?>>
				<?php the_post_thumbnail(apply_filters('everything_image_size', 'post-thumbnail', 'post'), Everything::to('post/thumbnail/size')); ?>
			</div>
		<?php else: ?>
			<a href="<?php the_permalink(); ?>" <?php Everything::imageAttrs('a'); ?>>
				<?php the_post_thumbnail(apply_filters('everything_image_size', 'post-thumbnail', 'post'), Everything::to('post/thumbnail/size')); ?>
			</a>
		<?php endif; ?>
	</figure>
<?php endif; ?>