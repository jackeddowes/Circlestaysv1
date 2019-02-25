<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */

$nav = Everything::io('layout/nav_secondary/upper/upper', 'nav/secondary/upper', '__hidden_ns');
if (!(\Drone\Func::wpAssignedMenu('secondary-upper') || is_numeric($nav)) || !apply_filters('everything_nav_secondary_upper_display', (bool)$nav)) {
	return;
}

?>

<div class="outer-container">
	<nav class="nav-menu secondary upper">
		<div class="container">
			<div class="section">
				<?php Everything::navMenu('secondary-upper', is_numeric($nav) ? $nav : null); ?>
			</div>
		</div>
	</nav>
</div><!-- // .outer-container -->