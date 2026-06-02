<?php
/**
 * The main template — fallback for any query without a more specific template.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<?php get_template_part( 'template-parts/breadcrumb' ); ?>

<main id="main" class="section">
	<div class="container-bia">
		<?php if ( have_posts() ) : ?>
			<div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/cards/post-card' );
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
