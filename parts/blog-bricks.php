<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */
?>

<?php if (have_posts()) : ?>

	<section class="section">
		<div class="bricks alt-mobile" data-bricks-columns="<?php echo Everything::to('site/blog/columns'); ?>" data-bricks-filter="<?php echo \Drone\Func::boolToString($filter = Everything::to('site/blog/filter/filter', '__hidden')); ?>">
			<?php while (have_posts()): the_post(); ?>
				<?php
					if ($filter) {
						$terms = \Drone\Func::wpPostTermsList(get_the_ID(), $filter);
						if (is_category() && ($term_id = array_search(single_cat_title('', false), $terms)) !== false) {
							unset($terms[$term_id]);
						}
						$terms = esc_attr(json_encode(array_values($terms)));
					}
				?>
				<div<?php if ($filter) echo " data-bricks-terms=\"{$terms}\""; ?>>
					<?php get_template_part('parts/post'); ?>
				</div>
			<?php endwhile; ?>
		</div>
		<?php echo Everything::getPaginateLinks('blog'); ?>
	</section>

<?php else: ?>

	<?php get_template_part('parts/no-posts'); ?>

<?php endif; ?>