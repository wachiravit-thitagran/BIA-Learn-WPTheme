<?php
/**
 * Post / news card. Used in the blog index, archives and related lists.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$cat = get_the_category();
?>
<article <?php post_class( 'card card-hover group flex flex-col' ); ?>>
	<a href="<?php the_permalink(); ?>" class="relative block aspect-[16/10] overflow-hidden bg-paper-100">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php
			the_post_thumbnail(
				'bia-card',
				array(
					'class'   => 'h-full w-full object-cover transition duration-500 ease-out-expo group-hover:scale-105',
					'loading' => 'lazy',
					'alt'     => the_title_attribute( array( 'echo' => false ) ),
				)
			);
			?>
		<?php else : ?>
			<span class="flex h-full w-full items-center justify-center bg-crimson-wash text-paper-50">
				<?php echo bia_learn_icon( 'lotus', 'h-12 w-12 opacity-60' ); // phpcs:ignore ?>
			</span>
		<?php endif; ?>
		<?php if ( ! empty( $cat ) ) : ?>
			<span class="absolute left-4 top-4 badge bg-white/90 backdrop-blur"><?php echo esc_html( $cat[0]->name ); ?></span>
		<?php endif; ?>
	</a>

	<div class="flex flex-1 flex-col gap-3 p-6">
		<div class="flex items-center gap-2 text-xs text-ink-light">
			<?php echo bia_learn_icon( 'calendar', 'h-3.5 w-3.5 text-crimson' ); // phpcs:ignore ?>
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
		</div>

		<h3 class="font-serif text-xl font-bold leading-snug text-ink transition group-hover:text-crimson">
			<a href="<?php the_permalink(); ?>" class="line-clamp-2"><?php the_title(); ?></a>
		</h3>

		<p class="line-clamp-3 text-sm leading-relaxed text-ink-light"><?php echo esc_html( get_the_excerpt() ); ?></p>

		<a href="<?php the_permalink(); ?>" class="mt-auto inline-flex items-center gap-1.5 pt-2 text-sm font-semibold text-crimson">
			<?php esc_html_e( 'อ่านต่อ', 'bia-learn' ); ?>
			<?php echo bia_learn_icon( 'arrow', 'h-4 w-4 transition group-hover:translate-x-1' ); // phpcs:ignore ?>
		</a>
	</div>
</article>
