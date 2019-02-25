<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */

$layout = Everything::to_('footer/layout')->option();

if (!$layout->value) {
	return;
}

?>

<div id="footer">

	<div class="container">

		<section class="section">

			<div class="columns alt-mobile">
				<ul>
					<?php foreach (Everything::stringToColumns($layout->value) as $i => $column): ?>
						<li class="<?php echo $column['class']; ?>">
							<?php dynamic_sidebar(apply_filters('everything_sidebar', "footer-{$layout->name}-{$i}", 'footer')); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div><!-- // .columns -->

		</section>

	</div><!-- // .container -->

</div><!-- // #footer -->