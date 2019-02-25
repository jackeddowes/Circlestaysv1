<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */

switch (get_post_type()) {
	case 'portfolio':
		$taxonomy = 'portfolio-category';
		break;
	case 'product':
		$taxonomy = 'product_cat';
		break;
	default:
		$taxonomy = 'category';
}

$prev_post = get_previous_post(Everything::to('nav/in_same_cat'), '', $taxonomy);
$next_post = get_next_post(Everything::to('nav/in_same_cat'), '', $taxonomy);

?>

<div class="nav">

	<?php if ($prev_post): ?>
		<a class="button small" href="<?php echo esc_url(get_permalink($prev_post)); ?>" title="<?php echo esc_attr($prev_post->post_title); ?>">
			<i class="icon-left-open"></i><span><?php _e('previous', 'everything') ?></span>
		</a>
	<?php else: ?>
		<a class="button small disabled"><i class="icon-left-open"></i><span><?php _e('previous', 'everything') ?></span></a>
	<?php endif; ?>

	<?php if ($next_post): ?>
		<a class="button small" href="<?php echo esc_url(get_permalink($next_post)); ?>" title="<?php echo esc_attr($next_post->post_title); ?>">
			<span><?php _e('next', 'everything') ?></span><i class="icon-right-open"></i>
		</a>
	<?php else: ?>
		<a class="button small disabled"><span><?php _e('next', 'everything') ?></span><i class="icon-right-open"></i></a>
	<?php endif; ?>

</div>