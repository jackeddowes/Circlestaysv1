<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */

if (!apply_filters('everything_author_bio_display', true)) {
	return;
}

?>

<section class="section author-bio">
	<figure class="alignleft fixed inset-border">
		<?php echo get_avatar(get_the_author_meta('ID'), 112); ?>
	</figure>
	<?php if (!Everything::$headline_used): ?>
		<h1><?php the_author(); ?></h1>
	<?php endif; ?>
	<p><?php echo nl2br(get_the_author_meta('description')); ?></p>
</section>