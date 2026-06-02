<?php
/**
 * 404 — page not found.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="relative overflow-hidden bg-plum-wash">
	<div class="absolute inset-0 bg-grain opacity-25" aria-hidden="true"></div>
	<div class="container-bia relative flex min-h-[70vh] flex-col items-center justify-center gap-6 py-20 text-center">
		<p class="font-serif text-8xl font-black text-gold-light sm:text-9xl">404</p>
		<h1 class="font-serif text-3xl font-bold text-white sm:text-4xl"><?php esc_html_e( 'ไม่พบหน้าที่คุณกำลังค้นหา', 'bia-learn' ); ?></h1>
		<p class="max-w-md text-paper-300"><?php esc_html_e( 'หน้านี้อาจถูกย้ายหรือไม่มีอยู่แล้ว ลองค้นหาหรือกลับสู่หน้าแรกได้เลย', 'bia-learn' ); ?></p>

		<div class="mt-2 w-full max-w-md [&_input]:bg-white"><?php get_search_form(); ?></div>

		<div class="mt-4 flex flex-wrap items-center justify-center gap-3">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-gold"><?php esc_html_e( 'กลับหน้าแรก', 'bia-learn' ); ?></a>
			<a href="<?php echo esc_url( bia_learn_courses_url() ); ?>" class="btn-outline border-white/30 text-white hover:bg-white hover:text-plum"><?php esc_html_e( 'ดูคอร์สเรียน', 'bia-learn' ); ?></a>
		</div>
	</div>
</main>

<?php
get_footer();
