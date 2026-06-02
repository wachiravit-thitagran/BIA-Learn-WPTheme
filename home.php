<?php
/**
 * The blog / news index (the page assigned as "Posts page").
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

get_header();

$posts_page = get_queried_object();
bia_learn_page_hero(
	array(
		'eyebrow'  => __( 'ข่าวสารและบทความ', 'bia-learn' ),
		'title'    => $posts_page && ! empty( $posts_page->post_title ) ? get_the_title( $posts_page ) : __( 'ข่าวสาร', 'bia-learn' ),
		'subtitle' => $posts_page && ! empty( $posts_page->post_excerpt ) ? $posts_page->post_excerpt : __( 'ติดตามความเคลื่อนไหว กิจกรรม และสาระความรู้จากเรา', 'bia-learn' ),
	)
);
?>

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
