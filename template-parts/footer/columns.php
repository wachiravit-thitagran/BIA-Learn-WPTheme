<?php
/**
 * Footer widget columns + brand block.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$about   = bia_learn_option( 'bia_footer_about' );
$address = bia_learn_option( 'bia_contact_address' );
$phone   = bia_learn_option( 'bia_contact_phone' );
$email   = bia_learn_option( 'bia_contact_email' );

$socials = array_filter(
	array(
		'facebook' => bia_learn_option( 'bia_social_facebook' ),
		'youtube'  => bia_learn_option( 'bia_social_youtube' ),
		'line'     => bia_learn_option( 'bia_social_line' ),
	)
);
?>
<div class="container-bia grid grid-cols-1 gap-10 py-16 sm:grid-cols-2 lg:grid-cols-12 lg:gap-8">

	<!-- Brand + about -->
	<div class="lg:col-span-4">
		<div class="flex items-center gap-3">
			<span class="grid h-12 w-12 place-items-center rounded-xl bg-white/10">
				<img src="<?php echo esc_url( BIA_LEARN_URI . '/assets/images/pagoda-logo.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="83" height="83" class="h-8 w-8 object-contain" loading="lazy" />
			</span>
			<span class="font-serif text-xl font-bold text-white"><?php bloginfo( 'name' ); ?></span>
		</div>
		<?php if ( $about ) : ?>
			<p class="mt-5 max-w-sm text-sm leading-relaxed text-paper-300"><?php echo wp_kses_post( $about ); ?></p>
		<?php endif; ?>

		<?php if ( $socials ) : ?>
			<div class="mt-6 flex items-center gap-3">
				<?php foreach ( $socials as $network => $url ) : ?>
					<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"
						class="grid h-10 w-10 place-items-center rounded-full bg-white/10 text-paper-200 transition hover:bg-gold hover:text-plum-900"
						aria-label="<?php echo esc_attr( ucfirst( $network ) ); ?>">
						<?php echo bia_learn_icon( $network, 'h-5 w-5' ); // phpcs:ignore ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<!-- Widget columns -->
	<div class="lg:col-span-5 grid grid-cols-1 gap-10 sm:grid-cols-2">
		<?php
		for ( $i = 1; $i <= 2; $i++ ) {
			if ( is_active_sidebar( 'footer-' . $i ) ) {
				echo '<div class="footer-widgets [&_a]:text-paper-300 [&_a:hover]:text-gold-light [&_ul]:space-y-2.5 [&_li]:text-sm">';
				dynamic_sidebar( 'footer-' . $i );
				echo '</div>';
			} elseif ( 1 === $i ) {
				// Sensible default link list when no widgets configured.
				echo '<div class="[&_a]:text-paper-300 [&_a:hover]:text-gold-light">';
				echo '<h3 class="mb-4 font-serif text-base font-bold text-white/90">' . esc_html__( 'ลิงก์ด่วน', 'bia-learn' ) . '</h3>';
				echo '<ul class="space-y-2.5 text-sm">';
				printf( '<li><a href="%s">%s</a></li>', esc_url( bia_learn_courses_url() ), esc_html__( 'คอร์สเรียนทั้งหมด', 'bia-learn' ) );
				printf( '<li><a href="%s">%s</a></li>', esc_url( bia_learn_page_url( 'instructors' ) ), esc_html__( 'ผู้สอน', 'bia-learn' ) );
				printf( '<li><a href="%s">%s</a></li>', esc_url( bia_learn_news_url() ), esc_html__( 'ข่าวสาร', 'bia-learn' ) );
				printf( '<li><a href="%s">%s</a></li>', esc_url( bia_learn_page_url( 'faq' ) ), esc_html__( 'คำถามที่พบบ่อย', 'bia-learn' ) );
				printf( '<li><a href="%s">%s</a></li>', esc_url( bia_learn_page_url( 'about' ) ), esc_html__( 'เกี่ยวกับเรา', 'bia-learn' ) );
				printf( '<li><a href="%s">%s</a></li>', esc_url( bia_learn_page_url( 'contact' ) ), esc_html__( 'ติดต่อเรา', 'bia-learn' ) );
				echo '</ul></div>';
			}
		}
		?>
	</div>

	<!-- Contact -->
	<div class="lg:col-span-3">
		<h3 class="mb-4 font-serif text-base font-bold text-white/90"><?php esc_html_e( 'ติดต่อเรา', 'bia-learn' ); ?></h3>
		<ul class="space-y-3 text-sm text-paper-300">
			<?php if ( $address ) : ?>
				<li class="flex gap-2.5"><?php echo bia_learn_icon( 'pin', 'mt-0.5 h-4 w-4 shrink-0 text-gold-light' ); // phpcs:ignore ?><span><?php echo wp_kses_post( $address ); ?></span></li>
			<?php endif; ?>
			<?php if ( $phone ) : ?>
				<li class="flex gap-2.5"><?php echo bia_learn_icon( 'phone', 'mt-0.5 h-4 w-4 shrink-0 text-gold-light' ); // phpcs:ignore ?><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li>
			<?php endif; ?>
			<?php if ( $email ) : ?>
				<li class="flex gap-2.5"><?php echo bia_learn_icon( 'mail', 'mt-0.5 h-4 w-4 shrink-0 text-gold-light' ); // phpcs:ignore ?><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
			<?php endif; ?>
		</ul>
	</div>
</div>
