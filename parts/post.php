<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(['post', 'hentry']); ?>><?php

	$post_format = (string)get_post_format();

	get_template_part('parts/post-thumbnail', Everything::getBlogStyle() == 'bricks' ? 'bricks' : $post_format);
	Everything::meta('before');
	Everything::title();

	if (is_search()) {
		the_excerpt();
	} else if (get_post_type() == 'post') {
		switch (Everything::to(["format_posts/{$post_format}/content", 'format_posts/standard/content'])) {
			case 'excerpt':
				the_excerpt();
				break;
			case 'excerpt_content':
				if (has_excerpt()) {
					the_excerpt();
					break;
				}
			case 'content':
				the_content(Everything::getReadMore());
				break;
		}
	} else {
		the_content(Everything::getReadMore());
	}

	Everything::socialButtons();
	Everything::meta();

?></article>