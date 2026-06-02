<?php
/**
 * Comments template.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
		<h2 class="font-serif text-2xl font-bold text-ink">
			<?php
			$count = get_comments_number();
			/* translators: %s: comment count */
			printf( esc_html( _n( 'ความคิดเห็น %s รายการ', 'ความคิดเห็น %s รายการ', $count, 'bia-learn' ) ), esc_html( number_format_i18n( $count ) ) );
			?>
		</h2>

		<ol class="mt-6 space-y-6">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'avatar_size' => 48,
					'short_ping'  => true,
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation(
			array(
				'prev_text' => '&larr; ' . esc_html__( 'ก่อนหน้า', 'bia-learn' ),
				'next_text' => esc_html__( 'ถัดไป', 'bia-learn' ) . ' &rarr;',
			)
		);
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="mt-6 rounded-xl bg-paper-100 px-4 py-3 text-sm text-ink-light"><?php esc_html_e( 'ปิดการแสดงความคิดเห็นแล้ว', 'bia-learn' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_container' => 'comment-respond mt-10 rounded-2xl border border-paper-200 bg-white p-6 sm:p-8',
			'title_reply_before' => '<h3 class="font-serif text-xl font-bold text-ink mb-4">',
			'title_reply_after'  => '</h3>',
			'class_submit'    => 'btn-primary',
			'comment_field'   => '<p class="comment-form-comment"><label class="field-label" for="comment">' . esc_html__( 'ความคิดเห็น', 'bia-learn' ) . '</label><textarea id="comment" name="comment" class="field" rows="5" required></textarea></p>',
		)
	);
	?>
</section>
