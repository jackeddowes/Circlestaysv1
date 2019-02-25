<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */

get_header();
get_template_part('parts/author-bio', 'archive');
get_template_part('parts/blog', Everything::to('site/blog/style'));
get_footer();