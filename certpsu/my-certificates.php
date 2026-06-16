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
		<article class="card card-hover flex flex-col overflow-hidden">
			<?php 
			$is_image = preg_match( '/\.(jpg|jpeg|png|webp|gif)$/i', $cert['certificate_url'] );
			?>
			<a href="<?php echo esc_url( $cert['certificate_url'] ); ?>" target="_blank" class="relative block aspect-[16/11] overflow-hidden bg-paper-100 border-b border-paper-200 group-hover:bg-paper-200 transition">
				<?php if ( $is_image ) : ?>
					<img src="<?php echo esc_url( $cert['certificate_url'] ); ?>" alt="<?php echo esc_attr( $cert['title'] ); ?>" class="h-full w-full object-cover transition duration-500 ease-out-expo group-hover:scale-105" loading="lazy" />
				<?php else : ?>
					<span class="absolute inset-0 flex items-center justify-center text-paper-400 transition duration-500 group-hover:scale-110 group-hover:text-gold-light">
						<?php echo bia_learn_icon( 'cert', 'h-16 w-16' ); // phpcs:ignore ?>
					</span>
				<?php endif; ?>
			</a>
			
			<div class="flex flex-col flex-1 p-6">
				<h3 class="font-sans text-lg font-bold leading-snug text-ink line-clamp-2">
					<a href="<?php echo esc_url( $cert['certificate_url'] ); ?>" target="_blank" class="hover:text-crimson transition">
						<?php echo esc_html( $cert['title'] ); ?>
					</a>
				</h3>
				<div class="mt-3 flex items-center gap-2 text-sm text-ink-light">
					<?php echo bia_learn_icon( 'calendar', 'h-4 w-4 text-crimson' ); // phpcs:ignore ?>
					<span><?php echo esc_html( $date_formatted ); ?></span>
				</div>
				
				<?php if ( ! empty( $cert['certificate_url'] ) ) : ?>
					<div class="mt-auto pt-5">
						<a href="<?php echo esc_url( $cert['certificate_url'] ); ?>" target="_blank" class="btn-outline w-full justify-center">
							<?php esc_html_e( 'ดูใบประกาศนียบัตร', 'bia-learn' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</article>
	<?php endforeach; ?>
</div>
