<?php
/**
 * Template Name: วิธีใช้งาน (Tutorial)
 *
 * Help / onboarding page: quick-start steps, a video-tutorial grid, the page's
 * own written guide, and a help CTA.
 *
 * Populate the video grid with:
 *   add_filter( 'bia_learn_tutorial_videos', function () {
 *       return array(
 *           array(
 *               'title'    => 'วิธีสมัครสมาชิก',
 *               'youtube'  => 'VIDEO_ID',          // YouTube video id
 *               'desc'     => 'สมัครและยืนยันอีเมลใน 1 นาที',
 *               'duration' => '1:24',
 *               'thumb'    => '',                  // optional local thumbnail URL
 *           ),
 *       );
 *   } );
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Quick-start steps (static, always useful).
$bia_steps = array(
	array(
		'icon'  => 'user',
		'title' => __( 'สมัครสมาชิก', 'bia-learn' ),
		'desc'  => __( 'สร้างบัญชีฟรีด้วยอีเมล เพื่อเข้าถึงคอร์สและบทเรียนทั้งหมด', 'bia-learn' ),
		'chip'  => 'icon-chip-crimson',
	),
	array(
		'icon'  => 'search',
		'title' => __( 'ค้นหาคอร์ส', 'bia-learn' ),
		'desc'  => __( 'เลือกหมวดหมู่หรือค้นหาคอร์สที่สนใจจากคลังคอร์สทั้งหมด', 'bia-learn' ),
		'chip'  => 'icon-chip-info',
	),
	array(
		'icon'  => 'book',
		'title' => __( 'เริ่มเรียน', 'bia-learn' ),
		'desc'  => __( 'เรียนบทเรียนและทำแบบทดสอบได้ทุกที่ทุกเวลา ตามจังหวะของคุณ', 'bia-learn' ),
		'chip'  => 'icon-chip-warning',
	),
	array(
		'icon'  => 'cert',
		'title' => __( 'รับเกียรติบัตร', 'bia-learn' ),
		'desc'  => __( 'ทำบทเรียนและแบบทดสอบให้ครบ รับเกียรติบัตรเพื่อยืนยันการเรียนรู้', 'bia-learn' ),
		'chip'  => 'icon-chip-success',
	),
);

$bia_videos = apply_filters( 'bia_learn_tutorial_videos', array() );
?>

<main id="main">

	<!-- Page hero -->
	<section class="dashboard-hero">
		<div class="container-bia relative">
			<p class="eyebrow text-white/80"><?php esc_html_e( 'ศูนย์ช่วยเหลือ', 'bia-learn' ); ?></p>
			<h1 class="dashboard-hero__title mt-3 max-w-2xl">
				<?php echo esc_html( get_the_title() ?: __( 'วิธีใช้งานแพลตฟอร์ม', 'bia-learn' ) ); ?>
			</h1>
			<p class="dashboard-hero__subtitle max-w-xl text-base">
				<?php esc_html_e( 'เริ่มต้นเรียนรู้ได้ง่ายๆ ทำตามขั้นตอนและวิดีโอแนะนำด้านล่าง', 'bia-learn' ); ?>
			</p>
		</div>
	</section>

	<!-- Quick-start steps -->
	<section class="section">
		<div class="container-bia">
			<?php
			bia_learn_section_heading(
				array(
					'eyebrow' => __( 'เริ่มต้นง่ายๆ', 'bia-learn' ),
					'title'   => __( 'เริ่มเรียนใน 4 ขั้นตอน', 'bia-learn' ),
				)
			);
			?>
			<div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
				<?php foreach ( $bia_steps as $i => $step ) : ?>
					<div class="stat-card flex flex-col gap-3">
						<div class="flex items-center gap-3">
							<span class="icon-chip <?php echo esc_attr( $step['chip'] ); ?>"><?php echo bia_learn_icon( $step['icon'], 'h-5 w-5' ); // phpcs:ignore ?></span>
							<span class="text-sm font-semibold text-crimson"><?php printf( esc_html__( 'ขั้นที่ %d', 'bia-learn' ), $i + 1 ); ?></span>
						</div>
						<h3 class="font-sans text-lg font-bold text-ink"><?php echo esc_html( $step['title'] ); ?></h3>
						<p class="text-sm leading-relaxed text-ink-light"><?php echo esc_html( $step['desc'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Video tutorials -->
	<?php if ( ! empty( $bia_videos ) && is_array( $bia_videos ) ) : ?>
		<section class="section bg-paper-100/50">
			<div class="container-bia">
				<?php
				bia_learn_section_heading(
					array(
						'eyebrow' => __( 'วิดีโอสอนใช้งาน', 'bia-learn' ),
						'title'   => __( 'ดูแล้วทำตามได้ทันที', 'bia-learn' ),
					)
				);
				?>
				<div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
					<?php
					foreach ( $bia_videos as $video ) :
						$vid   = isset( $video['youtube'] ) ? preg_replace( '/[^a-zA-Z0-9_-]/', '', $video['youtube'] ) : '';
						$thumb = ! empty( $video['thumb'] ) ? $video['thumb'] : '';
						if ( ! $vid ) {
							continue;
						}
						?>
						<article class="card overflow-hidden" x-data="{ play: false }">
							<div class="relative aspect-video bg-plum-wash">
								<!-- Facade: nothing loads from YouTube until the learner clicks (PDPA-friendly). -->
								<template x-if="!play">
									<button type="button" @click="play = true"
										class="group absolute inset-0 grid h-full w-full place-items-center"
										aria-label="<?php echo esc_attr( sprintf( __( 'เล่นวิดีโอ: %s', 'bia-learn' ), $video['title'] ?? '' ) ); ?>">
										<?php if ( $thumb ) : ?>
											<img src="<?php echo esc_url( $thumb ); ?>" alt="" class="absolute inset-0 h-full w-full object-cover" loading="lazy" />
											<span class="absolute inset-0 bg-plum-900/30"></span>
										<?php endif; ?>
										<span class="relative grid h-16 w-16 place-items-center rounded-full bg-white/90 text-crimson shadow-card transition group-hover:scale-110">
											<?php echo bia_learn_icon( 'play', 'h-8 w-8' ); // phpcs:ignore ?>
										</span>
									</button>
								</template>
								<template x-if="play">
									<iframe class="absolute inset-0 h-full w-full"
										:src="'https://www.youtube-nocookie.com/embed/<?php echo esc_js( $vid ); ?>?autoplay=1&rel=0'"
										title="<?php echo esc_attr( $video['title'] ?? '' ); ?>"
										allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
										allowfullscreen loading="lazy"></iframe>
								</template>
							</div>
							<div class="flex items-start justify-between gap-3 p-5">
								<h3 class="font-sans text-base font-bold leading-snug text-ink"><?php echo esc_html( $video['title'] ?? '' ); ?></h3>
								<?php if ( ! empty( $video['duration'] ) ) : ?>
									<span class="pill pill-crimson shrink-0"><?php echo bia_learn_icon( 'clock', 'h-3.5 w-3.5' ); // phpcs:ignore ?><?php echo esc_html( $video['duration'] ); ?></span>
								<?php endif; ?>
							</div>
							<?php if ( ! empty( $video['desc'] ) ) : ?>
								<p class="-mt-2 px-5 pb-5 text-sm leading-relaxed text-ink-light"><?php echo esc_html( $video['desc'] ); ?></p>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Written guide (page content) -->
	<?php
	while ( have_posts() ) :
		the_post();
		if ( trim( get_the_content() ) ) :
			?>
			<section class="section-tight">
				<div class="container-bia">
					<div class="prose-bia mx-auto"><?php the_content(); ?></div>
				</div>
			</section>
			<?php
		endif;
	endwhile;
	?>

	<!-- Help CTA -->
	<section class="section-tight">
		<div class="container-bia">
			<div class="flex flex-col items-center gap-5 rounded-3xl border border-paper-200 bg-white p-8 text-center shadow-stat sm:flex-row sm:justify-between sm:text-left">
				<div>
					<h2 class="font-sans text-xl font-bold text-ink"><?php esc_html_e( 'ยังต้องการความช่วยเหลือ?', 'bia-learn' ); ?></h2>
					<p class="mt-1 text-ink-light"><?php esc_html_e( 'ดูคำถามที่พบบ่อย หรือติดต่อทีมงานของเราได้เลย', 'bia-learn' ); ?></p>
				</div>
				<div class="flex flex-wrap items-center justify-center gap-3">
					<a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>" class="btn-outline"><?php esc_html_e( 'คำถามที่พบบ่อย', 'bia-learn' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn-primary"><?php esc_html_e( 'ติดต่อเรา', 'bia-learn' ); ?><?php echo bia_learn_icon( 'arrow', 'h-5 w-5' ); // phpcs:ignore ?></a>
				</div>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
