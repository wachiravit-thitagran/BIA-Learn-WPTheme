<?php
/**
 * "How it works" — three simple steps to start learning.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$steps = array(
	array(
		'title' => __( 'สมัครสมาชิก', 'bia-learn' ),
		'desc'  => __( 'สร้างบัญชีฟรีเพียงไม่กี่ขั้นตอน เพื่อเข้าถึงคอร์สและบทเรียนทั้งหมด', 'bia-learn' ),
	),
	array(
		'title' => __( 'เลือกคอร์สเรียน', 'bia-learn' ),
		'desc'  => __( 'ค้นหาและลงทะเบียนคอร์สที่สนใจ เรียนได้ทุกที่ทุกเวลาตามจังหวะของคุณ', 'bia-learn' ),
	),
	array(
		'title' => __( 'เรียนและประเมินผล', 'bia-learn' ),
		'desc'  => __( 'ทำบทเรียนและแบบทดสอบให้ครบ เกียรติบัตรขึ้นอยู่กับเงื่อนไขของแต่ละคอร์ส', 'bia-learn' ),
	),
);
?>
<section class="section">
	<div class="container-bia">
		<?php
		bia_learn_section_heading(
			array(
				'eyebrow' => __( 'เริ่มต้น', 'bia-learn' ),
				'title'   => __( 'เริ่มเรียนใน 3 ขั้นตอน', 'bia-learn' ),
			)
		);
		?>

		<ol class="mt-12 grid gap-8 border-t border-paper-200 pt-8 md:grid-cols-3 md:gap-10">
			<?php foreach ( $steps as $i => $step ) : ?>
				<li>
					<span class="text-sm font-semibold tabular-nums text-crimson" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
					<h3 class="mt-4 text-xl font-semibold text-ink"><?php echo esc_html( $step['title'] ); ?></h3>
					<p class="mt-2 max-w-xs text-sm leading-relaxed text-ink-light"><?php echo esc_html( $step['desc'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
