<?php
/**
 * Search results template.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

get_header();

global $wp_query;
$count = (int) $wp_query->found_posts;

bia_learn_page_hero(
	array(
		'eyebrow'    => __( 'ค้นหา', 'bia-learn' ),
		/* translators: %s: search term */
		'title'      => sprintf( __( 'ผลการค้นหา “%s”', 'bia-learn' ), esc_html( get_search_query() ) ),
		/* translators: %d: result count */
		'subtitle'   => sprintf( _n( 'พบ %d รายการ', 'พบ %d รายการ', $count, 'bia-learn' ), $count ),
		'breadcrumb' => false,
	)
);
?>

<main id="main" class="section">
	<div class="container-bia">
		<div class="mx-auto mb-12 max-w-xl"><?php get_search_form(); ?></div>

		<?php if ( have_posts() ) : ?>
			<div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
				<?php
				while ( have_posts() ) :
					the_post();
					if ( function_exists( 'tutor_utils' ) && 'courses' === get_post_type() ) {
						get_template_part( 'template-parts/cards/course-card' );
					} else {
						get_template_part( 'template-parts/cards/post-card' );
					}
				endwhile;
				?>
			</div>
			<?php bia_learn_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content-none' ); ?>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
