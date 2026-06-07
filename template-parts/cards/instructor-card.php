<?php
/**
 * Instructor card. Expects $args['user'] to be a WP_User object.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$user = isset( $args['user'] ) ? $args['user'] : null;
if ( ! $user instanceof WP_User ) {
	return;
}

$bio      = get_the_author_meta( 'description', $user->ID );
$tutils   = bia_learn_tutor_utils();
$profile  = $tutils && method_exists( $tutils, 'profile_url' ) ? $tutils->profile_url( $user->ID, true ) : get_author_posts_url( $user->ID );
$courses  = $tutils && method_exists( $tutils, 'get_course_count_by_instructor' ) ? (int) $tutils->get_course_count_by_instructor( $user->ID ) : 0;
?>
<article class="card card-hover group flex flex-col items-center p-8 text-center">
	<a href="<?php echo esc_url( $profile ); ?>" class="relative">
		<span class="absolute -inset-1 rounded-full bg-crimson/10 opacity-0 transition group-hover:opacity-100" aria-hidden="true"></span>
		<?php echo get_avatar( $user->ID, 112, '', esc_attr( $user->display_name ), array( 'class' => 'relative h-28 w-28 rounded-full object-cover ring-4 ring-paper-100' ) ); ?>
	</a>

	<h3 class="mt-5 font-serif text-lg font-bold text-ink transition group-hover:text-crimson">
		<a href="<?php echo esc_url( $profile ); ?>"><?php echo esc_html( $user->display_name ); ?></a>
	</h3>

	<?php if ( $bio ) : ?>
		<p class="mt-2 line-clamp-2 text-sm text-ink-light"><?php echo esc_html( wp_trim_words( $bio, 18, '…' ) ); ?></p>
	<?php endif; ?>

	<?php if ( $courses ) : ?>
		<div class="mt-4 inline-flex items-center gap-1.5 text-xs font-medium text-crimson">
			<?php echo bia_learn_icon( 'book', 'h-4 w-4' ); // phpcs:ignore ?>
			<?php printf( esc_html__( '%d คอร์ส', 'bia-learn' ), $courses ); ?>
		</div>
	<?php endif; ?>
</article>
