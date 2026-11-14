<?php
/**
 * Server-rendered sorting: usable without JavaScript or admin-ajax.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;
if ( ! tutor_utils()->get_option( 'course_archive_filter_sorting', true, true, true ) ) {
	return;
}
$order = isset( $_GET['course_order'] ) && is_string( $_GET['course_order'] ) ? sanitize_key( wp_unslash( $_GET['course_order'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only sorting.
$sorts = array(
	'newest_first'    => __( 'ใหม่ล่าสุด', 'bia-learn' ),
	'oldest_first'    => __( 'เก่าสุดก่อน', 'bia-learn' ),
	'course_title_az' => __( 'ชื่อคอร์ส (ก–ฮ)', 'bia-learn' ),
	'course_title_za' => __( 'ชื่อคอร์ส (ฮ–ก)', 'bia-learn' ),
);
?>
<form method="get" action="<?php echo esc_url( remove_query_arg( array_keys( $_GET ), get_pagenum_link( 1, false ) ) ); /* phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only query keys are removed from the form action. */ ?>" class="bia-course-sort flex flex-wrap items-center justify-end gap-3 mb-6">
	<input type="hidden" name="course_filter" value="1">
	<?php if ( ! get_option( 'permalink_structure' ) || ( isset( $_GET['post_type'] ) && 'courses' === $_GET['post_type'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Preserve a read-only archive selector. ?>
		<input type="hidden" name="post_type" value="courses">
	<?php endif; ?>
	<?php
	// Preserve supported filter values, but never serialize actions/nonces or
	// Tutor's internal AJAX/pagination arguments into a public URL.
	foreach ( array( 'keyword', 'tutor-course-filter-category', 'tutor-course-filter-tag', 'tutor-course-filter-level', 'tutor-course-filter-price' ) as $key ) {
		$values = isset( $_GET[ $key ] ) ? (array) map_deep( wp_unslash( $_GET[ $key ] ), 'sanitize_text_field' ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters.
		foreach ( $values as $value ) {
			if ( is_scalar( $value ) ) {
				printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $key . ( 'keyword' === $key ? '' : '[]' ) ), esc_attr( sanitize_text_field( $value ) ) );
			}
		}
	}
	?>
	<label for="bia-course-order"><?php esc_html_e( 'เรียงลำดับคอร์ส', 'bia-learn' ); ?></label>
	<select id="bia-course-order" name="course_order" class="field w-auto">
		<?php foreach ( $sorts as $value => $label ) : ?>
			<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $order ?: 'newest_first' ); ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
	</select>
	<button type="submit" class="btn-primary"><?php esc_html_e( 'แสดงคอร์ส', 'bia-learn' ); ?></button>
</form>
