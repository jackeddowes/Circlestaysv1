<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */
?>

					<?php echo Everything::endContent(); ?>

				</div><!-- // .container -->

			</div><!-- // .outer-container -->

			<?php get_template_part('parts/nav-secondary', 'lower'); ?>

			<div id="bottom" class="outer-container">

				<?php get_sidebar('footer'); ?>

				<?php if (Everything::to('footer/end_note/visible')): ?>

					<footer id="end-note" class="edge-bar">

						<div class="container">

							<section class="section">
								<div class="alignleft fixed"><p><?php echo nl2br(Everything::to('footer/end_note/left')); ?></p></div>
								<div class="alignright fixed"><p><?php echo nl2br(Everything::to('footer/end_note/right')); ?></p></div>
							</section>

						</div><!-- // .container -->

					</footer><!-- // #end-note -->

				<?php endif; ?>

			</div><!-- // .outer-container -->

		</div><!-- // #wrapper -->

		<?php wp_footer(); ?>

	</body>

</html>