<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */

if (!apply_filters('everything_author_bio_display', (bool)Everything::io('layout/page/author_bio/author_bio', [get_post_type() . '/author_bio', 'page/author_bio'], '__hidden'))) {
	return;
}

?>

<section class="section author-bio">
	<figure class="alignleft fixed inset-border">
		<?php echo get_avatar(get_the_author_meta('ID'), 64); ?>
	</figure>
	<h3><?php the_author(); ?></h3>
	<p class="small"><?php echo nl2br(get_the_author_meta('description')); ?></p>
</section>