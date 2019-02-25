<?php
/**
 * @package    WordPress
 * @subpackage Everything
 */
?>
<!DOCTYPE html>
<!--[if lt IE 9]>             <html class="no-js ie lt-ie9" <?php language_attributes(); ?>><![endif]-->
<!--[if IE 9]>                <html class="no-js ie ie9" <?php language_attributes(); ?>>   <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html class="no-js no-ie" <?php language_attributes(); ?>>    <!--<![endif]-->
	<head>
		<meta charset="<?php bloginfo('charset'); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1" />
		<!--[if lt IE 9]>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.js"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/selectivizr/1.0.2/selectivizr-min.js"></script>
		<![endif]-->
		<?php wp_head(); ?>
	</head>

	<?php if (($background = Everything::io_('layout/background/background', 'general/background/background', '__hidden_ns', '__hidden')) !== null): ?>
		<body <?php body_class($background->getClass()); ?> style="<?php echo esc_attr($background->css()); ?>">
	<?php else: ?>
		<body <?php body_class(); ?>>
	<?php endif; ?>

		<div id="wrapper">

			<div id="top-bar" class="outer-container edge-bar<?php if (Everything::to_('header/top_bar/settings')->value('fixed') || Everything::to_('header/style/settings')->value('fixed')) echo ' fixed'; ?>">

				<?php foreach (['desktop', 'mobile'] as $device): ?>
					<?php
						$items = Everything::to("header/top_bar/{$device}/items", '__hidden', []);
						if ($device == 'mobile' && Everything::to_('header/top_bar/settings')->value('mobile_toggle')) {
							array_unshift($items, 'mobile_toggle');
						}
						if (count($items) > 1):
					?>

						<div class="container <?php echo $device ?>-only">

							<section class="section">
								<div class="alignleft fixed"><?php

									foreach ($items as $item) {
										switch ($item) {

											case '_':
												echo '</div><div class="alignright fixed">';
												break;

											case 'tagline':
												echo '<span>';
												if ($device == 'mobile') {
													echo Everything::to(['header/tagline/mobile/text', 'header/tagline/text'], '__hidden', get_bloginfo('description'));
												} else {
													echo Everything::to('header/tagline/text', '__hidden', get_bloginfo('description'));
												}
												echo '</span>';
												break;

											case 'search':
												echo \Drone\HTML::div()
													->addClass('search-box', is_search() ? 'opened' : null)
													->add(preg_replace('/ placeholder="[^"]*"/', '', get_search_form(false)));
												break;

											case 'cart':
												echo Everything::woocommerceGetCartInfo('small');
												break;

											case 'menu':
												echo '<nav class="top-nav-menu">';
												Everything::navMenu('top-bar-' . $device, null, 1);
												echo '</nav>';
												break;

											case 'lang_menu':
												if (count(icl_get_languages('skip_missing=0')) > 0) {
													echo '<nav class="top-nav-menu lang">';
													Everything::langMenu();
													echo '</nav>';
												}
												break;

											case 'mobile_toggle':
												if (Everything::to_('header/main_menu/visible')->value('mobile')) {
													echo '<a id="mobile-section-toggle" title="' . __('Menu', 'everything') . '"><i class="icon-menu"></i></a>';
												}
												break;

										}
									}

								?></div>
							</section>

						</div><!-- // .container -->

					<?php endif; ?>
				<?php endforeach; ?>

			</div><!-- // .outer-container -->

			<header id="header" class="outer-container detached-background detached-border<?php
				echo rtrim(' ' . implode(' ', Everything::to('header/style/settings')));
			?>">

				<div class="container">

					<div class="section">

						<span class="helper">
							<?php if (Everything::to_('header/main_menu/visible')->value('mobile') && !Everything::to_('header/top_bar/settings')->value('mobile_toggle')): ?>
								<a id="mobile-section-toggle" title="<?php _e('Menu', 'everything'); ?>"><i class="icon-menu"></i></a>
							<?php endif; ?>
						</span>

						<?php if (Everything::to_('header/style/settings')->value('centered')) echo '<div>'; ?>

							<div id="logo"<?php if (Everything::to('header/logo/shrunken', '__hidden')) echo ' class="shrunken"'; ?>>
								<?php $name = get_bloginfo('name', 'display'); ?>
								<a href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo esc_attr($name); ?>" rel="home"><?php
									echo Everything::to_('header/logo/image')->image(['alt' => $name]) ?: $name;
								?></a>
							</div><!-- // #logo -->

							<?php if (Everything::to_('header/main_menu/visible')->value('desktop')): ?>
								<nav class="nav-menu main">
									<?php Everything::navMenu('main-desktop'); ?>
								</nav>
							<?php endif; ?>

						<?php if (Everything::to_('header/style/settings')->value('centered')) echo '</div>'; ?>

						<span class="helper">
							<?php echo Everything::woocommerceGetCartInfo('big', Everything::to('header/cart/visible', '__hidden', [])); ?>
						</span>

					</div>

					<div id="mobile-section">

						<?php if (Everything::to_('header/main_menu/visible')->value('mobile')): ?>
							<nav class="mobile-nav-menu main">
								<?php Everything::navMenu('main-mobile'); ?>
							</nav>
							<?php if (\Drone\Func::wpAssignedMenu('additional-mobile')): ?>
								<nav class="mobile-nav-menu additional">
									<?php Everything::navMenu('additional-mobile', null, 1); ?>
								</nav>
							<?php endif; ?>
						<?php endif; ?>

					</div>

				</div><!-- // .container -->

			</header><!-- // #header -->

			<?php get_template_part('parts/banner'); ?>

			<?php get_template_part('parts/nav-secondary', 'upper'); ?>

			<?php get_template_part('parts/headline'); ?>

			<div id="content" class="outer-container detached-background">

				<div class="container">

					<?php Everything::beginContent(); ?>