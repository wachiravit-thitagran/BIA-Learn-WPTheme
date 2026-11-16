<?php
/**
 * Homepage hero. Content driven by the Customizer (BIA Learn → Hero).
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$title    = bia_learn_option( 'bia_hero_title', __( 'เรียนรู้ พุทธธรรม<br>ในยุค ดิจิทัล', 'bia-learn' ) );
$subtitle = bia_learn_option( 'bia_hero_subtitle', __( 'คอร์สเรียนออนไลน์ บทเรียน และคลังความรู้ เพื่อการเรียนรู้ตลอดชีวิตอย่างเป็นอิสระ', 'bia-learn' ) );
$cta_text = bia_learn_option( 'bia_hero_cta_text', __( 'เริ่มเรียนรู้', 'bia-learn' ) );
$cta_url  = bia_learn_option( 'bia_hero_cta_url' ) ?: bia_learn_courses_url();

$stats = bia_learn_get_stats();
?>
<section class="border-b border-plum-700 bg-plum-900">
	<div class="container-bia grid items-center gap-12 py-16 lg:grid-cols-2 lg:gap-16 lg:py-24">
		<div class="max-w-xl">
			<h1 class="font-sans text-4xl font-semibold leading-tight text-white sm:text-5xl">
				<?php echo wp_kses_post( $title ); ?>
			</h1>
			<p class="mt-6 max-w-lg text-lg leading-relaxed text-paper-300"><?php echo wp_kses_post( $subtitle ); ?></p>

			<!-- Searching courses is the primary action. Preserve Tutor's courses post type. -->
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"
				class="mt-8 flex max-w-xl items-center gap-2 rounded-lg border border-white/20 bg-white p-1.5 focus-within:ring-2 focus-within:ring-gold-light">
				<input type="hidden" name="post_type" value="courses" />
				<span class="grid h-9 w-9 shrink-0 place-items-center text-ink-light" aria-hidden="true"><?php echo bia_learn_icon( 'search', 'h-5 w-5' ); // phpcs:ignore ?></span>
				<label for="bia-hero-search" class="sr-only"><?php esc_html_e( 'ค้นหาคอร์ส', 'bia-learn' ); ?></label>
				<input id="bia-hero-search" type="search" name="s" required
					placeholder="<?php esc_attr_e( 'ค้นหาคอร์ส หัวข้อ หรือผู้สอน…', 'bia-learn' ); ?>"
					class="min-w-0 flex-1 border-0 bg-transparent text-ink placeholder:text-ink-light focus:ring-0" />
				<button type="submit" class="btn-primary shrink-0 rounded-md"><?php esc_html_e( 'ค้นหา', 'bia-learn' ); ?></button>
			</form>

			<a href="<?php echo esc_url( $cta_url ); ?>" class="mt-6 inline-flex items-center gap-2 text-sm font-medium text-white underline underline-offset-4 transition-colors hover:text-gold-light focus-visible:rounded focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold-light">
				<?php echo esc_html( $cta_text ); ?>
				<?php echo bia_learn_icon( 'arrow', 'h-4 w-4' ); // phpcs:ignore ?>
			</a>

			<div class="mt-8 flex flex-wrap gap-x-6 gap-y-2 border-t border-white/15 pt-5 text-sm text-paper-300 lg:hidden" aria-label="<?php esc_attr_e( 'ภาพรวมการเรียนรู้', 'bia-learn' ); ?>">
				<span><strong class="font-semibold tabular-nums text-white"><?php echo esc_html( number_format_i18n( $stats['courses'] ) ); ?></strong> <?php esc_html_e( 'คอร์สเรียน', 'bia-learn' ); ?></span>
				<span><strong class="font-semibold tabular-nums text-white"><?php echo esc_html( number_format_i18n( $stats['students'] ) ); ?></strong> <?php esc_html_e( 'ผู้เรียน', 'bia-learn' ); ?></span>
			</div>
		</div>

		<!-- Real course and learner counts, not decorative feature cards. -->
		<aside class="hidden border-l border-white/20 pl-10 lg:block" aria-label="<?php esc_attr_e( 'ภาพรวมการเรียนรู้', 'bia-learn' ); ?>">
			<p class="text-sm font-medium text-gold-light"><?php esc_html_e( 'เรียนรู้ได้ตามจังหวะของคุณ', 'bia-learn' ); ?></p>
			<h2 class="mt-4 max-w-md font-sans text-2xl font-semibold leading-snug text-white"><?php esc_html_e( 'เริ่มจากหัวข้อที่สนใจ แล้วเรียนต่อได้ทุกเวลา', 'bia-learn' ); ?></h2>
			<p class="mt-4 max-w-md text-sm leading-relaxed text-paper-300"><?php esc_html_e( 'ค้นหาคอร์สที่ต้องการ ลงทะเบียน และติดตามความคืบหน้าได้จากแดชบอร์ดเดียว', 'bia-learn' ); ?></p>

			<dl class="mt-8 grid grid-cols-2 gap-6 border-t border-white/20 pt-6">
				<div>
					<dt class="text-sm text-paper-300"><?php esc_html_e( 'คอร์สเรียน', 'bia-learn' ); ?></dt>
					<dd class="mt-2 text-3xl font-semibold tabular-nums text-white"><?php echo esc_html( number_format_i18n( $stats['courses'] ) ); ?></dd>
				</div>
				<div>
					<dt class="text-sm text-paper-300"><?php esc_html_e( 'ผู้เรียน', 'bia-learn' ); ?></dt>
					<dd class="mt-2 text-3xl font-semibold tabular-nums text-white"><?php echo esc_html( number_format_i18n( $stats['students'] ) ); ?></dd>
				</div>
			</dl>
		</aside>
	</div>
</section>
