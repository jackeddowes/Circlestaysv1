<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */
?>

<li id="comment-<?php comment_ID(); ?>">
	<section class="comment">
		<?php if (!$comment->comment_type): ?>
			<figure class="alignleft fixed inset-border">
				<?php echo get_avatar($comment, 50); ?>
			</figure>
		<?php endif; ?>
		<p class="info">
			<small class="tools alt">
				<?php comment_reply_link(array_merge($args, [
					'reply_text' => __('reply', 'everything'),
					'depth'      => $depth
				])); ?>
				<?php edit_comment_link(__('edit', 'everything'), ' &bull; '); ?>
			</small>
			<strong><?php comment_author_link(); ?></strong>
			<?php if (Everything::to('site/comments/date_format')): ?>
				, <time class="small" datetime="<?php printf('%sT%sZ', get_comment_date('Y-m-d'), get_comment_time('H:i')); ?>">
					<?php
						switch (Everything::to('site/comments/date_format')) {
							case 'relative': printf(__('%s ago', 'everything'), human_time_diff(get_comment_time('U', true))); break;
							case 'absolute': printf(__('%1$s at %2$s', 'everything'), get_comment_date(), get_comment_time()); break;
						}
					?>
				</time>
			<?php endif; ?>
		</p>
		<article class="text">
			<?php if ($comment->comment_approved == '0') : ?>
				<p><em><?php _e('Your comment is awaiting moderation.', 'everything'); ?></em></p>
			<?php endif; ?>
			<?php comment_text(); ?>
		</article>
	</section>
	<ul class="comments">