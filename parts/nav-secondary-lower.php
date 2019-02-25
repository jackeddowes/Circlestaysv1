<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */

$nav = Everything::io('layout/nav_secondary/lower/lower', 'nav/secondary/lower', '__hidden_ns');
if (!(\Drone\Func::wpAssignedMenu('secondary-lower') || is_numeric($nav)) || !apply_filters('everything_nav_secondary_lower_display', (bool)$nav)) {
	return;
}

?>

<div class="outer-container">
	<nav class="nav-menu secondary lower">
		<div class="container">
			<div class="section">
				<?php Everything::navMenu('secondary-lower', is_numeric($nav) ? $nav : null); ?>
			</div>
		</div>
	</nav>
</div><!-- // .outer-container -->