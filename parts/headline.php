<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */

$headline = Everything::io('layout/headline/headline', 'nav/headline', '__hidden_ns');
if (!apply_filters('everything_headline', $headline)) {
	return;
}

?>

<?php Everything::$headline_used = true; ?>

<div id="headline" class="outer-container">

	<div class="container">

		<section class="section">
			<?php
				switch ($headline) {
					case 'mixed':
						$headline = is_single() ? 'navigation' : 'breadcrumbs';
						break;
					case 'navigation':
						if (!is_single()) $headline = 'none';
						break;
				}
				if ($headline != 'none') {
					get_template_part('parts/' . $headline);
				}
			?>
			<h1><?php
				if (Everything::isPluginActive('woocommerce') && (is_shop() || is_product_taxonomy()) && !is_product()) {
					woocommerce_page_title();
				} else if (is_home()) {
					is_front_page() ? _e('Blog', 'everything') : single_post_title();
				} else if (is_day()) {
					echo get_the_date();
				} else if (is_month()) {
					echo get_the_date('F Y');
				} else if (is_year()) {
					echo get_the_date('Y');
				} else if (is_category() || is_tax('portfolio-category')) { // not required?
					echo single_cat_title('', false);
				} else if (is_tag() || is_tax('portfolio-tag')) { // not required?
					echo single_tag_title('', false);
				} else if (is_tax()) {
					echo single_term_title('', false);
				} else if (is_search()) {
					printf(__('Search results for: %s', 'everything'), get_search_query());
				} else if (is_author()) {
					if (have_posts()) {
						the_post();
						printf(__('All posts by: %s', 'everything'), get_the_author());
						rewind_posts();
					}
				} else if (is_singular()) {
					single_post_title();
				} else {
					echo strstr(wp_title('|', false, 'right'), '|', true);
				}
			?></h1>
		</section>

	</div>

</div><!-- // .outer-container -->