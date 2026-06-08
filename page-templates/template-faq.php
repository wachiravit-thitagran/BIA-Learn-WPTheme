<?php
/**
 * Template Name: คำถามที่พบบ่อย (FAQ)
 *
 * FAQ items come from the `bia_learn_faq_items` filter (sensible defaults
 * provided). Each item: array( 'q' => question, 'a' => answer ).
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

get_header();

$faqs = bia_learn_get_faqs();

while ( have_posts() ) :
	the_post();

	bia_learn_page_hero(
		array(
			'eyebrow'  => __( 'ศูนย์ช่วยเหลือ', 'bia-learn' ),
			'title'    => get_the_title(),
			'subtitle' => __( 'รวมคำถามที่พบบ่อยเกี่ยวกับการเรียนและการใช้งานแพลตฟอร์ม', 'bia-learn' ),
		)
	);
	?>

	<section class="section-tight">
		<div class="container-bia max-w-3xl">
			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="prose-bia mb-10"><?php the_content(); ?></div>
			<?php endif; ?>

			<div class="space-y-4" x-data="{ open: 0 }">
				<?php foreach ( $faqs as $i => $faq ) : ?>
					<div class="card overflow-hidden">
						<button
							type="button"
							id="faq-q-<?php echo (int) $i; ?>"
							aria-controls="faq-a-<?php echo (int) $i; ?>"
							class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left"
							@click="open === <?php echo (int) $i; ?> ? open = null : open = <?php echo (int) $i; ?>"
							:aria-expanded="(open === <?php echo (int) $i; ?>).toString()"
						>
							<span class="font-serif text-lg font-semibold text-ink"><?php echo esc_html( $faq['q'] ); ?></span>
							<span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-crimson-50 text-crimson transition-transform duration-300" :class="open === <?php echo (int) $i; ?> ? 'rotate-180' : ''">
								<?php echo bia_learn_icon( 'chevron', 'h-5 w-5' ); // phpcs:ignore ?>
							</span>
						</button>
						<div id="faq-a-<?php echo (int) $i; ?>" role="region" aria-labelledby="faq-q-<?php echo (int) $i; ?>" x-show="open === <?php echo (int) $i; ?>" x-collapse x-cloak>
							<div class="border-t border-paper-100 px-6 py-5 text-ink-light leading-relaxed"><?php echo esc_html( $faq['a'] ); ?></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="mt-12 rounded-2xl bg-crimson-wash p-8 text-center text-paper-50">
				<h3 class="font-serif text-xl font-bold text-white"><?php esc_html_e( 'ยังไม่พบคำตอบที่ต้องการ?', 'bia-learn' ); ?></h3>
				<p class="mt-2 text-paper-200"><?php esc_html_e( 'ติดต่อทีมงานของเราได้โดยตรง', 'bia-learn' ); ?></p>
				<a href="<?php echo esc_url( bia_learn_page_url( 'contact' ) ); ?>" class="btn-gold mt-5"><?php esc_html_e( 'ติดต่อเรา', 'bia-learn' ); ?></a>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
