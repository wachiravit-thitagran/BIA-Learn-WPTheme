<?php
/**
 * Template Name: ติดต่อเรา (Contact)
 *
 * Renders a contact form (uses Contact Form 7 / WPForms shortcode if placed in
 * the page content; otherwise a native mailto-style form) alongside contact
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
			'subtitle' => __( 'มีคำถามเกี่ยวกับคอร์สเรียนหรือการใช้งาน? ส่งข้อความถึงเราได้เลย', 'bia-learn' ),
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
					if ( trim( get_the_content() ) ) {
						// Allow Contact Form 7 / WPForms shortcode placed in the page body.
						echo '<div class="bia-contact-form [&_input]:field [&_textarea]:field [&_label]:field-label">';
						the_content();
						echo '</div>';
					} else {
						?>
						<form class="grid gap-5" method="post" action="<?php echo esc_url( $email ? 'mailto:' . antispambot( $email ) : '#' ); ?>" enctype="text/plain">
							<div class="grid gap-5 sm:grid-cols-2">
								<div><label class="field-label" for="cf-name"><?php esc_html_e( 'ชื่อ-นามสกุล', 'bia-learn' ); ?></label><input class="field" id="cf-name" name="name" type="text" required></div>
								<div><label class="field-label" for="cf-email"><?php esc_html_e( 'อีเมล', 'bia-learn' ); ?></label><input class="field" id="cf-email" name="email" type="email" required></div>
							</div>
							<div><label class="field-label" for="cf-subject"><?php esc_html_e( 'หัวข้อ', 'bia-learn' ); ?></label><input class="field" id="cf-subject" name="subject" type="text"></div>
							<div><label class="field-label" for="cf-message"><?php esc_html_e( 'ข้อความ', 'bia-learn' ); ?></label><textarea class="field" id="cf-message" name="message" rows="5" required></textarea></div>
							<button type="submit" class="btn-primary justify-self-start btn-lg"><?php esc_html_e( 'ส่งข้อความ', 'bia-learn' ); ?><?php echo bia_learn_icon( 'arrow', 'h-5 w-5' ); // phpcs:ignore ?></button>
							<p class="text-xs text-ink-light"><?php esc_html_e( 'เคล็ดลับ: ติดตั้งปลั๊กอินฟอร์ม เช่น Contact Form 7 แล้ววางช็อตโค้ดในหน้านี้เพื่อใช้งานฟอร์มเต็มรูปแบบ', 'bia-learn' ); ?></p>
						</form>
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
