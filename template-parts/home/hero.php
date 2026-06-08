<?php
/**
 * Homepage hero. Content driven by the Customizer (BIA Learn → Hero).
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$title    = bia_learn_option( 'bia_hero_title', __( 'เรียนรู้ธรรมะ ภาวนา และปัญญา จากสวนโมกข์สู่โลกดิจิทัล', 'bia-learn' ) );
$subtitle = bia_learn_option( 'bia_hero_subtitle', __( 'คอร์สเรียนออนไลน์ บทเรียน และคลังความรู้ เพื่อการเรียนรู้ตลอดชีวิตอย่างเป็นอิสระ', 'bia-learn' ) );
$cta_text = bia_learn_option( 'bia_hero_cta_text', __( 'เริ่มเรียนรู้', 'bia-learn' ) );
$cta_url  = bia_learn_option( 'bia_hero_cta_url' ) ?: bia_learn_courses_url();

$stats = bia_learn_get_stats();
?>
<section class="relative overflow-hidden bg-plum-wash">
	<!-- atmosphere (kept subtle — flat & clean, like bia.psu.ac.th) -->
	<div class="pointer-events-none absolute -right-20 bottom-0 h-96 w-96 rounded-full bg-crimson/20 blur-3xl" aria-hidden="true"></div>

	<div class="container-bia relative grid items-center gap-12 py-20 lg:grid-cols-2 lg:py-28">
		<!-- copy -->
		<div class="max-w-xl animate-fade-up">
			<h1 class="font-serif text-4xl font-bold leading-[1.15] text-white sm:text-5xl lg:text-[3.25rem]">
				<?php echo wp_kses_post( $title ); ?>
			</h1>

			<p class="mt-6 text-lg leading-relaxed text-paper-300"><?php echo wp_kses_post( $subtitle ); ?></p>

			<!-- Course search — the primary "find a course" entry point -->
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"
				class="mt-8 flex max-w-xl items-center gap-2 rounded-full bg-white/95 p-1.5 shadow-card focus-within:ring-2 focus-within:ring-gold-light">
				<input type="hidden" name="post_type" value="courses" />
				<span class="grid h-9 w-9 shrink-0 place-items-center text-ink-light"><?php echo bia_learn_icon( 'search', 'h-5 w-5' ); // phpcs:ignore ?></span>
				<label for="bia-hero-search" class="sr-only"><?php esc_html_e( 'ค้นหาคอร์ส', 'bia-learn' ); ?></label>
				<input id="bia-hero-search" type="search" name="s" required
					placeholder="<?php esc_attr_e( 'ค้นหาคอร์ส หัวข้อ หรือผู้สอน…', 'bia-learn' ); ?>"
					class="min-w-0 flex-1 border-0 bg-transparent text-ink placeholder:text-ink-light focus:ring-0" />
				<button type="submit" class="btn-primary shrink-0"><?php esc_html_e( 'ค้นหา', 'bia-learn' ); ?></button>
			</form>

			<div class="mt-6 flex flex-wrap items-center gap-4">
				<a href="<?php echo esc_url( $cta_url ); ?>" class="btn-gold btn-lg">
					<?php echo esc_html( $cta_text ); ?>
					<?php echo bia_learn_icon( 'arrow', 'h-5 w-5' ); // phpcs:ignore ?>
				</a>
				<a href="<?php echo esc_url( bia_learn_page_url( 'about' ) ); ?>" class="btn-lg inline-flex items-center gap-2 text-sm font-semibold text-white hover:text-gold-light">
					<span class="grid h-11 w-11 place-items-center rounded-full border border-white/20"><?php echo bia_learn_icon( 'play', 'h-5 w-5' ); // phpcs:ignore ?></span>
					<?php esc_html_e( 'รู้จักเรา', 'bia-learn' ); ?>
				</a>
			</div>
		</div>

		<!-- stat cards -->
		<div class="hidden flex-col gap-4 lg:flex">
			<div class="rounded-2xl border border-white/15 bg-white/10 px-8 py-6 backdrop-blur-sm">
				<p class="font-serif text-4xl font-bold text-white"><?php echo number_format_i18n( $stats['courses'] ); ?>+</p>
				<p class="mt-1 text-base text-paper-300"><?php esc_html_e( 'คอร์สเรียน', 'bia-learn' ); ?></p>
			</div>
			<div class="rounded-2xl border border-white/15 bg-white/10 px-8 py-6 backdrop-blur-sm">
				<p class="font-serif text-4xl font-bold text-white"><?php echo number_format_i18n( $stats['students'] ); ?>+</p>
				<p class="mt-1 text-base text-paper-300"><?php esc_html_e( 'ผู้เรียน', 'bia-learn' ); ?></p>
			</div>
			<div class="rounded-2xl border border-white/15 bg-white/10 px-8 py-6 backdrop-blur-sm">
				<p class="font-serif text-4xl font-bold text-gold-light"><?php esc_html_e( 'FREE', 'bia-learn' ); ?></p>
				<p class="mt-1 text-base text-paper-300"><?php esc_html_e( 'เข้าถึงได้ฟรี', 'bia-learn' ); ?></p>
			</div>
		</div>
	</div>

	<!-- wave divider -->
	<div class="relative -mb-px text-paper-50" aria-hidden="true">
		<svg viewBox="0 0 1440 80" fill="currentColor" preserveAspectRatio="none" class="h-12 w-full sm:h-16"><path d="M0 80h1440V0c-240 53-480 53-720 27S240-13 0 27z"/></svg>
	</div>
</section>
