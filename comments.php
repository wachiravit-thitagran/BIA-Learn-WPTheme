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

	$user_identity = wp_get_current_user()->exists() ? wp_get_current_user()->display_name : '';
	
	comment_form(
		array(
			'class_container'    => 'comment-respond mt-10 rounded-2xl border border-paper-200 bg-white p-6 sm:p-8',
			'title_reply_before' => '<h3 class="font-serif text-xl font-bold text-ink mb-2">',
			'title_reply_after'  => '</h3>',
			'class_submit'       => 'btn-primary mt-2',
			'submit_button'      => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>',
			'comment_notes_before' => '<p class="text-sm text-ink-light mb-4">' . esc_html__( 'ช่องข้อมูลจำเป็นถูกทำเครื่องหมาย *', 'bia-learn' ) . '</p>',
			'logged_in_as'       => '<p class="text-sm text-ink-light mb-4">' . sprintf( 
				/* translators: 1: Edit user link, 2: User name, 3: Logout URL */
				__( 'เข้าสู่ระบบในชื่อ <a href="%1$s" class="font-medium text-ink hover:text-crimson">%2$s</a> <span class="mx-1">&middot;</span> <a href="%3$s" class="text-crimson hover:underline">ออกจากระบบ</a>', 'bia-learn' ),
				get_edit_user_link(),
				$user_identity,
				wp_logout_url( apply_filters( 'the_permalink', get_permalink( get_the_ID() ), get_the_ID() ) )
			) . '</p>',
			'comment_field'      => '<div class="comment-form-comment mb-4"><label class="mb-2 block text-sm font-semibold text-ink" for="comment">' . esc_html__( 'ความคิดเห็น', 'bia-learn' ) . ' <span class="text-crimson">*</span></label><textarea id="comment" name="comment" class="field w-full rounded-xl border-paper-200 bg-paper-50 p-4 transition focus:border-crimson focus:bg-white focus:ring focus:ring-crimson/20" rows="4" required placeholder="' . esc_attr__( 'พิมพ์ความคิดเห็นของคุณที่นี่...', 'bia-learn' ) . '"></textarea></div>',
		)
	);
	?>
</section>
