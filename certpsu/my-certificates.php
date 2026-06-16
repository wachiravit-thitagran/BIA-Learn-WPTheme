<?php
/**
 * My Certificates Template.
 *
 * This template overrides the default template from the CertPSU plugin,
 * using the BIA-Learn theme design language.
 *
 * @package BIA_Learn
 * 
 * @var array<int,array<string,mixed>> $certificates List of certificates.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $certificates ) ) {
	?>
	<div class="mx-auto max-w-md rounded-2xl border border-paper-200 bg-white p-8 text-center shadow-soft">
		<span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-paper-100 text-ink-light">
			<?php echo bia_learn_icon( 'cert', 'h-8 w-8' ); // phpcs:ignore ?>
		</span>
		<h3 class="mt-5 font-sans text-xl font-bold text-ink"><?php esc_html_e( 'ยังไม่มีใบประกาศนียบัตร', 'bia-learn' ); ?></h3>
		<p class="mt-2 text-sm text-ink-light"><?php esc_html_e( 'คุณยังไม่ได้รับใบประกาศนียบัตรใดๆ ในขณะนี้', 'bia-learn' ); ?></p>
	</div>
	<?php
	return;
}
?>

<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
	<?php foreach ( $certificates as $cert ) : ?>
		<?php 
			$date_formatted = '';
			if ( ! empty( $cert['issued_at'] ) ) {
				$date_formatted = date_i18n( get_option( 'date_format' ), strtotime( $cert['issued_at'] ) );
			}
		?>
		<article class="card card-hover flex flex-col justify-between p-6">
			<div>
				<div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-success-light text-success">
					<?php echo bia_learn_icon( 'cert', 'h-6 w-6' ); // phpcs:ignore ?>
				</div>
				<h3 class="font-sans text-lg font-bold leading-snug text-ink">
					<?php echo esc_html( $cert['title'] ); ?>
				</h3>
				<div class="mt-3 flex items-center gap-2 text-sm text-ink-light">
					<?php echo bia_learn_icon( 'calendar', 'h-4 w-4 text-crimson' ); // phpcs:ignore ?>
					<span><?php echo esc_html( $date_formatted ); ?></span>
				</div>
			</div>
			
			<?php if ( ! empty( $cert['certificate_url'] ) ) : ?>
				<div class="mt-6 pt-4 border-t border-paper-200">
					<a href="<?php echo esc_url( $cert['certificate_url'] ); ?>" target="_blank" class="btn-primary w-full justify-center">
						<?php esc_html_e( 'ดูใบประกาศนียบัตร', 'bia-learn' ); ?>
						<?php echo bia_learn_icon( 'arrow', 'h-4 w-4' ); // phpcs:ignore ?>
					</a>
				</div>
			<?php endif; ?>
		</article>
	<?php endforeach; ?>
</div>
