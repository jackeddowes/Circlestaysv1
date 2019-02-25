<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */

if (!apply_filters('everything_comments_display', Everything::to([get_post_type() . '/comments', 'page/comments'])) && !post_password_required()) {
	return;
}

?>

<?php if (have_comments()): ?>

	<section id="comments" class="section">
		<ul class="comments">
			<?php wp_list_comments([
				'style'        => 'div',
				'callback'     => function ($comment, $args, $depth) {
					$GLOBALS['comment'] = $comment;
					require get_template_directory() . '/comment.php';
				},
				'end-callback' => function () {
					echo '</ul></li>';
				}
			]); ?>
		</ul>
		<?php echo Everything::getPaginateLinks('comments'); ?>
	</section>

<?php endif; ?>

<?php if (comments_open()): ?>
	<section class="section">
		<?php comment_form(); ?>
	</section>
<?php endif; ?>