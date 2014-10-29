<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2><?php esc_html_e( TGGR_NAME ); ?> Settings</h2>

	<p>In order to use <?php echo esc_html( TGGR_NAME ); ?> you'll need to obtain access keys for each service that you would like to pull into your stream.</p>

	<form method="post" action="options.php">
		<?php settings_fields( Tagregator::PREFIX . 'settings' ); ?>
		<?php do_settings_sections( Tagregator::PREFIX . 'settings' ); ?>

		<?php submit_button(); ?>
	</form>
</div> <!-- .wrap -->