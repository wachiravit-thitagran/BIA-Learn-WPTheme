<?php
/**
 * Default page template.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	bia_learn_page_hero(
		array(
			'title'    => get_the_title(),
			'subtitle' => has_excerpt() ? get_the_excerpt() : '',
		)
	);
	?>

	<main id="main" class="section-tight">
		<div class="container-bia">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="mx-auto mb-10 max-w-4xl overflow-hidden rounded-3xl shadow-card">
					<?php the_post_thumbnail( 'bia-hero', array( 'class' => 'h-full w-full object-cover' ) ); ?>
				</figure>
			<?php endif; ?>

			<div class="mx-auto max-w-3xl">
				<div class="prose-bia">
					<?php the_content(); ?>
				</div>
				<?php
				wp_link_pages(
					array(
						'before' => '<div class="mt-8 flex items-center gap-2 text-sm font-semibold">' . esc_html__( 'หน้า:', 'bia-learn' ),
						'after'  => '</div>',
					)
				);
				?>

				<?php if ( comments_open() || get_comments_number() ) : ?>
					<div class="mt-12"><?php comments_template(); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</main>

	<?php
endwhile;

get_footer();
