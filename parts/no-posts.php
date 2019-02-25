<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */
?>

<section class="section">

	<?php if (is_search()): ?>

		<p><?php _e('Nothing was found. Please try again with different keywords.', 'everything'); ?></p>
		<?php get_search_form(); ?>

	<?php else: ?>

		<p><?php _e('There\'s nothing here.', 'everything'); ?></p>

	<?php endif; ?>

</section>