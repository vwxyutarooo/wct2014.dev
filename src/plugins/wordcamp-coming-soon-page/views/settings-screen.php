<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>Coming Soon</h2>

	<form method="post" action="options.php">
		<?php settings_fields( 'wccsp_settings' ); ?>
		<?php do_settings_sections( 'wccsp_settings' ); ?>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button-primary" value="<?php echo __( 'Save Changes' ); ?>" />
		</p>
	</form>
</div> <!-- .wrap -->
