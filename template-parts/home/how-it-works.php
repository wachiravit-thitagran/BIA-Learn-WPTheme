<?php
/**
 * "How it works" — three simple steps to start learning.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$steps = array(
	array(
		'icon'  => 'user',
		'title' => __( 'สมัครสมาชิก', 'bia-learn' ),
		'desc'  => __( 'สร้างบัญชีฟรีเพียงไม่กี่ขั้นตอน เพื่อเข้าถึงคอร์สและบทเรียนทั้งหมด', 'bia-learn' ),
	),
	array(
		'icon'  => 'book',
		'title' => __( 'เลือกคอร์สเรียน', 'bia-learn' ),
		'desc'  => __( 'ค้นหาและลงทะเบียนคอร์สที่สนใจ เรียนได้ทุกที่ทุกเวลาตามจังหวะของคุณ', 'bia-learn' ),
	),
	array(
		'icon'  => 'cert',
		'title' => __( 'เรียนจบรับเกียรติบัตร', 'bia-learn' ),
		'desc'  => __( 'ทำบทเรียนและแบบทดสอบให้ครบ รับเกียรติบัตรเพื่อยืนยันการเรียนรู้', 'bia-learn' ),
	),
);
?>
<section class="section">
	<div class="container-bia">
		<?php
		bia_learn_section_heading(
			array(
				'eyebrow' => __( 'เริ่มต้นง่ายๆ', 'bia-learn' ),
				'title'   => __( 'เริ่มเรียนใน 3 ขั้นตอน', 'bia-learn' ),
			)
		);
		?>

		<div class="relative mt-14 grid gap-10 md:grid-cols-3">
			<!-- connecting line -->
			<div class="pointer-events-none absolute left-0 right-0 top-9 hidden h-px bg-gradient-to-r from-transparent via-gold/40 to-transparent md:block" aria-hidden="true"></div>

			<?php foreach ( $steps as $i => $step ) : ?>
				<div class="relative flex flex-col items-center text-center">
					<span class="relative grid h-18 w-18 place-items-center rounded-full border-2 border-dashed border-gold/50 bg-paper-50 p-4">
						<span class="grid h-full w-full place-items-center rounded-full bg-crimson-wash text-paper-50"><?php echo bia_learn_icon( $step['icon'], 'h-7 w-7' ); // phpcs:ignore ?></span>
						<span class="absolute -right-1 -top-1 grid h-7 w-7 place-items-center rounded-full bg-gold font-serif text-sm font-bold text-plum-900"><?php echo esc_html( $i + 1 ); ?></span>
					</span>
					<h3 class="mt-6 font-serif text-xl font-bold text-ink"><?php echo esc_html( $step['title'] ); ?></h3>
					<p class="mt-2 max-w-xs text-sm leading-relaxed text-ink-light"><?php echo esc_html( $step['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
