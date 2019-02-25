<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */
?>

<?php get_header(); ?>

<?php if (have_posts()): the_post(); ?>

	<section class="section">
		<?php Everything::title(); ?>
		<figure class="full-width">
			<div <?php Everything::imageAttrs('div'); ?>>
				<?php echo wp_get_attachment_image(get_the_ID(), 'full-width'); ?>
			</div>
			<?php if (has_excerpt()): ?>
				<figcaption><?php the_excerpt(); ?></figcaption>
			<?php endif; ?>
		</figure>
	</section>

	<?php get_template_part('parts/author-bio'); ?>
	<?php Everything::socialButtons(); ?>
	<?php Everything::meta(); ?>
	<?php comments_template(); ?>

<?php endif; ?>

<?php get_footer(); ?>