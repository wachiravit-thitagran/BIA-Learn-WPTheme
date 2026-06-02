<?php
/**
 * Custom search form.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$unique = wp_unique_id( 'search-field-' );
?>
<form role="search" method="get" class="flex items-center gap-2 rounded-full border border-paper-300 bg-white p-1.5 shadow-sm focus-within:border-crimson focus-within:ring-2 focus-within:ring-crimson/30" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $unique ); ?>" class="sr-only"><?php esc_html_e( 'ค้นหา', 'bia-learn' ); ?></label>
	<span class="pl-3 text-crimson"><?php echo bia_learn_icon( 'search', 'h-5 w-5' ); // phpcs:ignore ?></span>
	<input
		type="search"
		id="<?php echo esc_attr( $unique ); ?>"
		class="flex-1 border-0 bg-transparent text-sm focus:ring-0"
		placeholder="<?php esc_attr_e( 'ค้นหา…', 'bia-learn' ); ?>"
		value="<?php echo get_search_query(); ?>"
		name="s"
	/>
	<button type="submit" class="btn-primary shrink-0 px-5 py-2"><?php esc_html_e( 'ค้นหา', 'bia-learn' ); ?></button>
</form>
