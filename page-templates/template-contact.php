<?php
/**
 * Template Name: ติดต่อเรา (Contact)
 *
 * Renders a contact form (uses Contact Form 7 / WPForms shortcode if placed in
 * the page content; otherwise an explicit contact link) alongside contact
 * details and an optional Google Map embed from the Customizer.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

get_header();

$address = bia_learn_option( 'bia_contact_address' );
$phone   = bia_learn_option( 'bia_contact_phone' );
$email   = bia_learn_option( 'bia_contact_email' );
$map     = bia_learn_option( 'bia_contact_map' );

while ( have_posts() ) :
	the_post();

	bia_learn_page_hero(
		array(
			'eyebrow'  => __( 'ติดต่อ', 'bia-learn' ),
			'title'    => get_the_title(),
			'subtitle' => __( 'มีคำถามเกี่ยวกับคอร์สเรียนหรือการใช้งาน? ติดต่อทีมงานผ่านช่องทางด้านล่าง', 'bia-learn' ),
		)
	);
	?>

	<section class="section-tight">
		<div class="container-bia grid gap-10 lg:grid-cols-5">

			<!-- Contact details -->
			<div class="lg:col-span-2">
				<h2 class="font-serif text-2xl font-bold text-ink"><?php esc_html_e( 'ข้อมูลติดต่อ', 'bia-learn' ); ?></h2>
				<p class="mt-2 text-ink-light"><?php esc_html_e( 'ทีมงานยินดีให้ความช่วยเหลือในวันและเวลาทำการ', 'bia-learn' ); ?></p>

				<ul class="mt-8 space-y-5">
					<?php if ( $address ) : ?>
						<li class="flex gap-4">
							<span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-crimson-50 text-crimson"><?php echo bia_learn_icon( 'pin', 'h-5 w-5' ); // phpcs:ignore ?></span>
							<div><p class="font-semibold text-ink"><?php esc_html_e( 'ที่อยู่', 'bia-learn' ); ?></p><p class="text-sm text-ink-light"><?php echo wp_kses_post( $address ); ?></p></div>
						</li>
					<?php endif; ?>
					<?php if ( $phone ) : ?>
						<li class="flex gap-4">
							<span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-crimson-50 text-crimson"><?php echo bia_learn_icon( 'phone', 'h-5 w-5' ); // phpcs:ignore ?></span>
							<div><p class="font-semibold text-ink"><?php esc_html_e( 'โทรศัพท์', 'bia-learn' ); ?></p><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>" class="text-sm text-crimson hover:underline"><?php echo esc_html( $phone ); ?></a></div>
						</li>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<li class="flex gap-4">
							<span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-crimson-50 text-crimson"><?php echo bia_learn_icon( 'mail', 'h-5 w-5' ); // phpcs:ignore ?></span>
							<div><p class="font-semibold text-ink"><?php esc_html_e( 'อีเมล', 'bia-learn' ); ?></p><a href="mailto:<?php echo esc_attr( $email ); ?>" class="text-sm text-crimson hover:underline"><?php echo esc_html( $email ); ?></a></div>
						</li>
					<?php endif; ?>
				</ul>
			</div>

			<!-- Form -->
			<div class="lg:col-span-3">
				<div class="card p-8">
					<?php
					$bia_content = get_the_content();
					$bia_is_elementor = isset( $_GET['elementor-preview'] ) || ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display/preview or navigation input; no state change.
					if ( trim( $bia_content ) || $bia_is_elementor ) {
						// Allow Contact Form 7 / WPForms shortcode placed in the page body.
						echo '<div class="bia-contact-form [&_input]:field [&_textarea]:field [&_label]:field-label">';
						the_content();
						echo '</div>';
					} else {
						?>
						<p class="text-ink-soft"><?php esc_html_e( 'สอบถามข้อมูลผ่านช่องทางติดต่อที่ระบุในหน้านี้', 'bia-learn' ); ?></p>
						<?php if ( $email ) : ?>
							<a class="btn-primary mt-5" href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php esc_html_e( 'ติดต่อทางอีเมล', 'bia-learn' ); ?></a>
						<?php elseif ( ! $phone && ! $address ) : ?>
							<p class="mt-4 text-ink-soft"><?php esc_html_e( 'ช่องทางติดต่อกำลังปรับปรุง กรุณากลับมาตรวจสอบอีกครั้ง', 'bia-learn' ); ?></p>
						<?php endif; ?>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</section>

	<?php if ( $map ) : ?>
		<section class="mt-6">
			<div class="aspect-[21/9] w-full overflow-hidden">
				<iframe src="<?php echo esc_url( $map ); ?>" class="h-full w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php esc_attr_e( 'แผนที่', 'bia-learn' ); ?>"></iframe>
			</div>
		</section>
	<?php endif; ?>

	<?php
endwhile;

get_footer();
