<?php
/**
 * Locale-scoped fallbacks for reviewed integration copy, in PHP and JS.
 *
 * @package BIA_Learn
 */
defined( 'ABSPATH' ) || exit;

/** Shared reviewed strings. */
function bia_learn_thai_ui_strings() {
	static $strings = null;
	if ( null === $strings ) {
		$strings = require BIA_LEARN_DIR . '/languages/th-ui.php';
	}
	return $strings;
}

/** Keep English and other locales, wp-admin and unrelated domains untouched. */
function bia_learn_translate_ui( $translation, $text, $domain ) {
	if ( is_admin() || 0 !== strpos( determine_locale(), 'th' ) || ! in_array( $domain, array( 'tutor', 'tutor-pro', 'tutor-course-bundles', 'authorizenter', 'biapsu-profilesync' ), true ) ) {
		return $translation;
	}
	$strings = bia_learn_thai_ui_strings();
	return $strings[ $text ] ?? $translation;
}
add_filter( 'gettext', 'bia_learn_translate_ui', 30, 3 );

/** Contextual strings include the greeting in Tutor's dashboard header. */
function bia_learn_translate_context_ui( $translation, $text, $context, $domain ) {
	return bia_learn_translate_ui( $translation, $text, $domain );
}
add_filter( 'gettext_with_context', 'bia_learn_translate_context_ui', 30, 4 );

/** Tutor 4.1's billing controller registers a raw, untranslated label. */
function bia_learn_translate_settings_nav( $items ) {
	foreach ( $items as $key => $item ) {
		if ( isset( $item['label'] ) && is_string( $item['label'] ) ) {
			$items[ $key ]['label'] = bia_learn_translate_ui( $item['label'], $item['label'], 'tutor' );
		}
	}
	return $items;
}
add_filter( 'tutor_dashboard/nav_items/settings/nav_items', 'bia_learn_translate_settings_nav', 99 );

/** Apply the same fallback to wp.i18n calls, without overwriting locale data. */
function bia_learn_enqueue_ui_translations() {
	if ( is_admin() || 0 !== strpos( determine_locale(), 'th' ) ) {
		return;
	}
	wp_enqueue_script( 'wp-hooks' );
	wp_enqueue_script( 'wp-i18n' );
	$script = '(() => { const strings = ' . wp_json_encode( bia_learn_thai_ui_strings(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';'
		. 'const domains = ["tutor", "tutor-pro", "tutor-course-bundles", "authorizenter", "biapsu-profilesync"];'
		. 'wp.hooks.addFilter("i18n.gettext", "bia-learn/th", (value,text,domain) => domains.includes(domain) && Object.prototype.hasOwnProperty.call(strings,text) ? strings[text] : value);'
		. 'wp.hooks.addFilter("i18n.gettext_with_context", "bia-learn/th-context", (value,text,context,domain) => domains.includes(domain) && Object.prototype.hasOwnProperty.call(strings,text) ? strings[text] : value); })();';
	wp_add_inline_script( 'wp-i18n', $script, 'after' );
}
add_action( 'wp_enqueue_scripts', 'bia_learn_enqueue_ui_translations', 200 );
