<?php
/**
 * Single post (news / article) template.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<article <?php post_class(); ?>>

		<!-- Hero -->
		<header class="relative overflow-hidden bg-plum-wash text-paper-100">
			<div class="absolute inset-0 bg-grain opacity-25" aria-hidden="true"></div>
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="absolute inset-0">
					<?php the_post_thumbnail( 'bia-hero', array( 'class' => 'h-full w-full object-cover opacity-25' ) ); ?>
					<div class="absolute inset-0 bg-plum-wash/80"></div>
				</div>
			<?php endif; ?>
			<div class="container-bia relative flex flex-col items-center gap-5 py-20 text-center">
				<div class="flex flex-wrap items-center justify-center gap-3">
					<?php
					$cats = get_the_category();
					if ( $cats ) :
						?>
						<a href="<?php echo esc_url( get_category_link( $cats[0] ) ); ?>" class="badge bg-white/15 text-paper-100 backdrop-blur"><?php echo esc_html( $cats[0]->name ); ?></a>
					<?php endif; ?>
				</div>
				<h1 class="max-w-3xl font-serif text-4xl font-bold leading-tight text-white sm:text-5xl"><?php the_title(); ?></h1>
				<div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-sm text-paper-300">
					<span class="inline-flex items-center gap-1.5"><?php echo get_avatar( get_the_author_meta( 'ID' ), 28, '', '', array( 'class' => 'rounded-full' ) ); ?><?php the_author(); ?></span>
					<span class="text-paper-500">·</span>
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					<span class="text-paper-500">·</span>
					<span><?php echo esc_html( bia_learn_reading_time() ); ?></span>
				</div>
			</div>
		</header>

		<?php get_template_part( 'template-parts/breadcrumb' ); ?>

		<!-- Body -->
		<div class="section-tight">
			<div class="container-bia">
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

					<!-- Tags -->
					<?php if ( has_tag() ) : ?>
						<div class="mt-10 flex flex-wrap items-center gap-2 border-t border-paper-200 pt-8">
							<span class="text-sm font-semibold text-ink-soft"><?php esc_html_e( 'แท็ก:', 'bia-learn' ); ?></span>
							<?php
							foreach ( get_the_tags() as $tag ) {
								printf( '<a href="%s" class="badge-muted hover:bg-crimson-50 hover:text-crimson">#%s</a>', esc_url( get_tag_link( $tag ) ), esc_html( $tag->name ) );
							}
							?>
						</div>
					<?php endif; ?>

					<!-- Author bio -->
					<?php if ( get_the_author_meta( 'description' ) ) : ?>
						<div class="mt-10 flex gap-5 rounded-2xl border border-paper-200 bg-paper-100/60 p-6">
							<?php echo get_avatar( get_the_author_meta( 'ID' ), 72, '', '', array( 'class' => 'h-18 w-18 shrink-0 rounded-full' ) ); ?>
							<div>
								<p class="text-xs uppercase tracking-wider text-crimson"><?php esc_html_e( 'เขียนโดย', 'bia-learn' ); ?></p>
								<h3 class="font-serif text-lg font-bold text-ink"><?php the_author(); ?></h3>
								<p class="mt-1 text-sm text-ink-light"><?php echo esc_html( get_the_author_meta( 'description' ) ); ?></p>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Related posts -->
		<?php
		$related = new WP_Query(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => 3,
				'post__not_in'        => array( get_the_ID() ),
				'category__in'        => wp_list_pluck( get_the_category(), 'term_id' ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		if ( $related->have_posts() ) :
			?>
			<section class="section bg-paper-100/50">
				<div class="container-bia">
					<?php bia_learn_section_heading( array( 'eyebrow' => __( 'อ่านเพิ่มเติม', 'bia-learn' ), 'title' => __( 'บทความที่เกี่ยวข้อง', 'bia-learn' ), 'align' => 'left' ) ); ?>
					<div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
						<?php
						while ( $related->have_posts() ) :
							$related->the_post();
							get_template_part( 'template-parts/cards/post-card' );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<!-- Comments -->
		<?php if ( comments_open() || get_comments_number() ) : ?>
			<div class="section-tight">
				<div class="container-bia">
					<div class="mx-auto max-w-3xl"><?php comments_template(); ?></div>
				</div>
			</div>
		<?php endif; ?>

	</article>

	<?php
endwhile;

get_footer();
