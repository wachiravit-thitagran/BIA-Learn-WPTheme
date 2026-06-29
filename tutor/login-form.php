<?php
/**
 * Override Tutor LMS Login Form to force SSO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tutor-login-form-wrap text-center py-8">
	<div class="bia-auth-social">
		<?php
		$bia_current_url = function_exists( 'tutor_utils' ) ? tutor_utils()->get_current_url() : '';
		$bia_providers   = array( 'google', 'facebook', 'line', 'oidc', 'oauth2' );
		foreach ( $bia_providers as $bia_provider ) {
			echo do_shortcode( sprintf( '[authorizenter_button context="default" provider="%s" return_to="%s"]', esc_attr( $bia_provider ), esc_url( $bia_current_url ) ) );
		}
		?>
	</div>
</div>
