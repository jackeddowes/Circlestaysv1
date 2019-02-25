<?php







?>

<div class="wrap">

	<h2><?php echo get_admin_page_title(); ?></h2>

	<?php settings_errors('general'); ?>

	<?php
		if (is_callable($group->description)):
			call_user_func($group->description, $group);
		elseif ($group->description):
			echo '<p>' . $group->description . '</p>';
		endif;
	?>

	<form method="post" action="<?php echo self::WP_THEME_OPTIONS_URI; ?>">
		<?php settings_fields($this->theme->id_); ?>
		<div class="drone-theme-options">
			<?php echo $group->html(); ?>
			<p class="submit">
				<input id="submit" name="submit" type="submit" value="<?php esc_attr_e('Save Changes', 'everything'); ?>" class="button-primary" disabled />
			</p>
		</div>
	</form>

</div>