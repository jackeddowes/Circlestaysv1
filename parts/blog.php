<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */
?>

<?php if (have_posts()) : ?>

	<?php while (have_posts()): the_post(); ?>
		<section class="section">
			<?php get_template_part('parts/post'); ?>
		</section>
	<?php endwhile; ?>
	<?php if ($pagination = Everything::getPaginateLinks('blog')): ?>
		<div class="section"><?php echo $pagination; ?></div>
	<?php endif; ?>

<?php else: ?>

	<?php get_template_part('parts/no-posts'); ?>

<?php endif; ?>