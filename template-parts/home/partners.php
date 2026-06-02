<?php
/**
 * Partners / supporting organisations logo strip.
 *
 * Pulls images from a "partners" gallery if one is set via the
 * `bia_learn_partner_logos` filter; otherwise renders a quiet quote block so
 * the homepage never ends on an empty section.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filterable list of partner logos. Each item: array( 'name', 'url', 'image' ).
 */
$partners = apply_filters( 'bia_learn_partner_logos', array() );
?>
<section class="section-tight bg-paper-100/60">
	<div class="container-bia">
		<?php if ( ! empty( $partners ) ) : ?>
			<p class="text-center text-xs font-semibold uppercase tracking-[0.2em] text-ink-light"><?php esc_html_e( 'ภายใต้ความร่วมมือกับ', 'bia-learn' ); ?></p>
			<div class="mt-8 flex flex-wrap items-center justify-center gap-x-12 gap-y-8 opacity-70 grayscale">
				<?php foreach ( $partners as $p ) : ?>
					<a href="<?php echo esc_url( $p['url'] ?? '#' ); ?>" class="transition hover:opacity-100 hover:grayscale-0">
						<img src="<?php echo esc_url( $p['image'] ); ?>" alt="<?php echo esc_attr( $p['name'] ?? '' ); ?>" class="h-12 w-auto" loading="lazy">
					</a>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<figure class="mx-auto max-w-3xl text-center">
				<?php echo bia_learn_icon( 'quote', 'mx-auto h-10 w-10 text-gold/60' ); // phpcs:ignore ?>
				<blockquote class="mt-4 font-serif text-2xl font-medium leading-relaxed text-ink sm:text-3xl">
					<?php esc_html_e( '“การงานที่แท้จริงคือการปฏิบัติธรรม การปฏิบัติธรรมคือการทำงานด้วยใจที่ว่าง”', 'bia-learn' ); ?>
				</blockquote>
				<figcaption class="mt-5 text-sm font-semibold uppercase tracking-wider text-crimson">
					<?php esc_html_e( '— ท่านพุทธทาสภิกขุ', 'bia-learn' ); ?>
				</figcaption>
			</figure>
		<?php endif; ?>
	</div>
</section>
