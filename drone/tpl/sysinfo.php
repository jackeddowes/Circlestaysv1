<?php







?>

<h3><?php _e('Informations', 'everything'); ?></h3>

<table class="widefat">
	<colgroup>
		<col width="25%" />
		<col width="25%" />
		<col width="50%" />
	</colgroup>
	<thead>
		<tr>
			<th><?php _ex('Name', 'property', 'everything'); ?></th>
			<th><?php _e('Value', 'everything'); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php _e('Server software', 'everything'); ?></td>
			<td><?php if (isset($_SERVER['SERVER_SOFTWARE'])) echo esc_html($_SERVER['SERVER_SOFTWARE']); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php _e('PHP version', 'everything'); ?></td>
			<td><?php if (defined('PHP_VERSION')) echo esc_html(PHP_VERSION); ?></td>
			<td><?php if ($this->notices['outdated_php']): ?>
				<span class="drone-info"><?php _e('You have outdated PHP version on your server. For better performance and reliability make an update (or ask your hosting provider for that).', 'everything'); ?></span>
			<?php endif; ?></td>
		</tr>
		<tr>
			<td><?php _e('MySQL version', 'everything'); ?></td>
			<td><?php
				if (isset($GLOBALS['wpdb']->dbh->server_info)):
					echo esc_html($GLOBALS['wpdb']->dbh->server_info);
				elseif (function_exists('mysql_get_server_info')):
					echo esc_html(mysql_get_server_info());
				endif;
			?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php _e('WordPress version', 'everything'); ?></td>
			<td><?php echo esc_html($this->instance->wp_version); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php $this->instance->parent_theme === null ? _e('Theme version', 'everything') : _e('Parent theme version', 'everything'); ?></td>
			<td><?php echo esc_html($this->instance->base_theme->version); ?></td>
			<td><?php if ($this->notices['version_corrupted']): ?>
				<span class="drone-error"><?php _e('Theme name/version information is corrupted. Probably one of theme files is damaged or modified. If you want to modify theme files, use child theme for that purpose.', 'everything'); ?></span>
			<?php elseif ($this->notices['update_available']): ?>
				<span class="drone-info">
					<?php printf(__('New version (%1$s) of %2$s theme is available.', 'everything'), $this->update['new_version'], $this->instance->base_theme->name); ?>
					<?php if (!is_multisite()): ?>
						<a href="<?php echo get_admin_url(null, 'update-core.php'); ?>"><?php _e('Update now', 'everything'); ?></a>.
					<?php endif; ?>
				</span>
			<?php endif; ?></td>
		</tr>
		<?php if ($this->instance->parent_theme !== null): ?>
			<tr>
				<td><?php _e('Theme version', 'everything'); ?></td>
				<td><?php echo esc_html($this->instance->theme->version); ?></td>
				<td></td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>

<h3><?php _e('Configuration', 'everything'); ?></h3>

<table class="widefat">
	<colgroup>
		<col width="25%" />
		<col width="75%" />
	</colgroup>
	<thead>
		<tr>
			<th><?php _ex('Name', 'property', 'everything'); ?></th>
			<th><?php _e('Value', 'everything'); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php _e('PHP max. execution time', 'everything'); ?></td>
			<td><?php if (function_exists('ini_get')) echo ini_get('max_execution_time'); ?>s</td>
		</tr>
		<tr>
			<td><?php _e('PHP memory limit', 'everything'); ?></td>
			<td><?php if (function_exists('ini_get')) echo ini_get('memory_limit') . 'B'; ?></td>
		</tr>
		<tr>
			<td><?php _e('WordPress memory limit', 'everything'); ?></td>
			<td><?php if (defined('WP_MEMORY_LIMIT')) echo WP_MEMORY_LIMIT . 'B'; ?></td>
		</tr>
	</tbody>
</table>

<h3><?php _e('Paths', 'everything'); ?></h3>

<table class="widefat">
	<colgroup>
		<col width="25%" />
		<col width="75%" />
	</colgroup>
	<thead>
		<tr>
			<th><?php _ex('Name', 'property', 'everything'); ?></th>
			<th><?php _e('Value', 'everything'); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php _e('Home URL', 'everything'); ?></td>
			<td class="code"><?php echo esc_url(home_url('/')); ?></td>
		</tr>
		<tr>
			<td><?php _e('Site URL', 'everything'); ?></td>
			<td class="code"><?php echo esc_url(site_url('/')); ?></td>
		</tr>
		<tr>
			<td><?php _e('Template URL', 'everything'); ?></td>
			<td class="code"><?php echo esc_url(get_template_directory_uri()); ?></td>
		</tr>
		<tr>
			<td><?php _e('Stylesheet URL', 'everything'); ?></td>
			<td class="code"><?php echo esc_url(get_stylesheet_directory_uri()); ?></td>
		</tr>
		<tr>
			<td><?php _e('Template directory', 'everything'); ?></td>
			<td class="code"><?php echo get_template_directory(); ?></td>
		</tr>
		<tr>
			<td><?php _e('Stylesheet directory', 'everything'); ?></td>
			<td class="code"><?php echo get_stylesheet_directory(); ?></td>
		</tr>
	</tbody>
</table>

<h3><?php _e('Settings', 'everything'); ?></h3>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
	<?php wp_nonce_field('settings_export'); ?>
	<p><?php _e('Export Theme Options settings to file.', 'everything'); ?></p>
	<p><input class="button" type="submit" value="<?php esc_attr_e('Export settings', 'everything'); ?>" name="settings_export" /></p>
</form>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data">
	<?php wp_nonce_field('settings_import'); ?>
	<p>
		<label for="settings_file"><?php _e('Choose a file from your computer', 'everything'); ?>:</label>
		<input type="file" name="settings_import_file" />
	</p>
	<p><input class="button" type="submit" value="<?php esc_attr_e('Import settings', 'everything'); ?>" name="settings_import" /></p>
</form>