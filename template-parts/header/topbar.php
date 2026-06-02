<?php
/**
 * Slim top bar above the main navigation — contact + social + auth links.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$phone = bia_learn_option( 'bia_contact_phone' );
$email = bia_learn_option( 'bia_contact_email' );

$socials = array_filter(
	array(
		'facebook' => bia_learn_option( 'bia_social_facebook' ),
		'youtube'  => bia_learn_option( 'bia_social_youtube' ),
		'line'     => bia_learn_option( 'bia_social_line' ),
	)
);

$dashboard_url = function_exists( 'tutor_utils' ) ? tutor_utils()->tutor_dashboard_url() : wp_login_url();
?>
<div class="hidden bg-plum text-paper-200 lg:block">
	<div class="container-bia flex h-10 items-center justify-between text-xs">
		<div class="flex items-center gap-5">
			<?php if ( $phone ) : ?>
				<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>" class="inline-flex items-center gap-1.5 hover:text-white">
					<?php echo bia_learn_icon( 'phone', 'h-3.5 w-3.5 text-gold-light' ); // phpcs:ignore ?>
					<?php echo esc_html( $phone ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $email ) : ?>
				<a href="mailto:<?php echo esc_attr( $email ); ?>" class="inline-flex items-center gap-1.5 hover:text-white">
					<?php echo bia_learn_icon( 'mail', 'h-3.5 w-3.5 text-gold-light' ); // phpcs:ignore ?>
					<?php echo esc_html( $email ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="flex items-center gap-4">
			<?php foreach ( $socials as $network => $url ) : ?>
				<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" class="hover:text-white" aria-label="<?php echo esc_attr( ucfirst( $network ) ); ?>">
					<?php echo bia_learn_icon( $network, 'h-4 w-4' ); // phpcs:ignore ?>
				</a>
			<?php endforeach; ?>

			<span class="h-4 w-px bg-white/20"></span>

			<?php if ( is_user_logged_in() ) : ?>
				<a href="<?php echo esc_url( $dashboard_url ); ?>" class="inline-flex items-center gap-1.5 font-medium hover:text-white">
					<?php echo bia_learn_icon( 'user', 'h-3.5 w-3.5 text-gold-light' ); // phpcs:ignore ?>
					<?php esc_html_e( 'แดชบอร์ดของฉัน', 'bia-learn' ); ?>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( wp_login_url() ); ?>" class="font-medium hover:text-white"><?php esc_html_e( 'เข้าสู่ระบบ', 'bia-learn' ); ?></a>
				<a href="<?php echo esc_url( wp_registration_url() ); ?>" class="font-medium text-gold-light hover:text-white"><?php esc_html_e( 'สมัครสมาชิก', 'bia-learn' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</div>
