<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */
?>

<?php get_header(); ?>

<?php if (have_posts()): the_post(); ?>

	<section class="section">
		<article id="post-<?php the_ID(); ?>" <?php post_class(['post', 'hentry']); ?>><?php
			get_template_part('parts/post-thumbnail', Everything::getBlogStyle() == 'bricks' ? 'bricks' : get_post_format());
			Everything::meta('before');
			Everything::title();
			the_content(null, Everything::to('post/strip_teaser'));
			echo Everything::getPaginateLinks('page');
		?></article>
	</section>

	<?php get_template_part('parts/author-bio'); ?>
	<?php Everything::socialButtons(); ?>
	<?php Everything::meta(); ?>
	<?php comments_template(); ?>

<?php endif; ?>

<?php get_footer(); ?>