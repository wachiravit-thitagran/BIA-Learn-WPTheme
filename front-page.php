<?php
/**
 * The homepage. Composed from self-contained section parts so the order can be
 * rearranged without touching their internals.
 *
 * If the front page is set to a static page that has its own content, we still
 * lead with the designed sections — the page content (if any) renders after.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main">
	<?php
	get_template_part( 'template-parts/home/hero' );
	get_template_part( 'template-parts/home/stats' );
	get_template_part( 'template-parts/home/featured-courses' );
	get_template_part( 'template-parts/home/categories' );
	get_template_part( 'template-parts/home/how-it-works' );
	get_template_part( 'template-parts/home/instructors' );
	get_template_part( 'template-parts/home/latest-news' );
	get_template_part( 'template-parts/home/partners' );

	// Render static front-page content beneath the designed sections, if present.
	if ( is_page() && have_posts() ) :
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
	endif;

	// Closing call-to-action (homepage only — lives here, not in footer.php).
	get_template_part( 'template-parts/footer/cta' );
	?>
</main>

<?php
get_footer();
