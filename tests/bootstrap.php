<?php
/**
 * PHPUnit bootstrap file for BIA-Learn-WPTheme.
 *
 * @package BIA_Learn
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Stub basic WP constants.
define( 'ABSPATH', dirname( __DIR__ ) . '/' );
define( 'WPINC', 'wp-includes' );

// Setup Brain\Monkey
\Brain\Monkey\setUp();

// Include the theme's functions.php so we can test its features.
// We must mock some WP functions before requiring functions.php.

if ( ! function_exists( 'add_theme_support' ) ) {
	function add_theme_support( $feature, ...$args ) {
		return true;
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
	function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {}
}

if ( ! function_exists( 'wp_enqueue_script' ) ) {
	function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {}
}

if ( ! function_exists( 'get_template_directory_uri' ) ) {
	function get_template_directory_uri() {
		return 'http://example.com/wp-content/themes/bia-learn-wptheme';
	}
}

if ( ! function_exists( 'get_template_directory' ) ) {
	function get_template_directory() {
		return dirname( __DIR__ );
	}
}

if ( ! function_exists( 'wp_get_theme' ) ) {
	function wp_get_theme() {
		return new class {
			public function get( $header ) {
				return '1.0.0';
			}
		};
	}
}

// Require the main theme functions.
require_once dirname( __DIR__ ) . '/functions.php';

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) {
		return $url;
	}
}

if ( ! function_exists( 'post_class' ) ) {
	function post_class( $class = '', $post_id = null ) {
		echo 'class="' . esc_attr( implode( ' ', (array) $class ) ) . '"';
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return $text;
	}
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	function wp_strip_all_tags( $text, $remove_breaks = false ) {
		return strip_tags( $text );
	}
}

if ( ! function_exists( 'get_the_category' ) ) {
	function get_the_category( $id = false ) {
		return array();
	}
}

if ( ! function_exists( 'wp_parse_args' ) ) {
	function wp_parse_args( $args, $defaults = array() ) {
		if ( is_object( $args ) ) {
			$r = get_object_vars( $args );
		} elseif ( is_array( $args ) ) {
			$r =& $args;
		} else {
			wp_parse_str( $args, $r );
		}

		if ( is_array( $defaults ) && ! empty( $defaults ) ) {
			return array_merge( $defaults, $r );
		}
		return $r;
	}
}

if ( ! function_exists( 'wp_parse_str' ) ) {
	function wp_parse_str( $string, &$array ) {
		parse_str( (string) $string, $array );
	}
}

if ( ! function_exists( 'get_avatar' ) ) {
	function get_avatar( $id_or_email, $size = 96, $default = '', $alt = '', $args = null ) {
		return '<img src="avatar.jpg" alt="Avatar">';
	}
}

if ( ! function_exists( 'get_the_title' ) ) {
	function get_the_title( $post = 0 ) {
		return 'Title';
	}
}

if ( ! function_exists( 'get_the_author_meta' ) ) {
	function get_the_author_meta( $field = '', $user_id = false ) {
		return 'Author Meta';
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return $text;
	}
}

if ( ! function_exists( 'the_author' ) ) {
	function the_author() {
		echo 'Author Name';
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $text ) {
		return $text;
	}
}

if ( ! function_exists( 'get_the_date' ) ) {
	function get_the_date( $format = '', $post = null ) {
		return 'January 1, 2026';
	}
}

if ( ! function_exists( 'is_front_page' ) ) {
	function is_front_page() {
		return false;
	}
}

if ( ! function_exists( 'is_home' ) ) {
	function is_home() {
		return false;
	}
}

if ( ! function_exists( 'get_the_content' ) ) {
	function get_the_content( $more_link_text = null, $strip_teaser = false ) {
		return 'Content';
	}
}

if ( ! function_exists( 'wp_trim_words' ) ) {
	function wp_trim_words( $text, $num_words = 55, $more = null ) {
		return $text;
	}
}

if ( ! function_exists( 'get_the_excerpt' ) ) {
	function get_the_excerpt( $post = null ) {
		return 'Excerpt';
	}
}

if ( ! function_exists( 'get_the_permalink' ) ) {
	function get_the_permalink( $post = 0, $leavename = false ) {
		return 'http://example.com/';
	}
}

if ( ! function_exists( 'has_excerpt' ) ) {
	function has_excerpt( $post = 0 ) {
		return false;
	}
}

if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '', $scheme = null ) {
		return 'http://example.com' . $path;
	}
}

if ( ! function_exists( '_n' ) ) {
	function _n( $single, $plural, $number, $domain = 'default' ) {
		return $number === 1 ? $single : $plural;
	}
}

if ( ! function_exists( 'number_format_i18n' ) ) {
	function number_format_i18n( $number, $decimals = 0 ) {
		return $number;
	}
}

if ( ! function_exists( 'is_singular' ) ) {
	function is_singular() {
		return false;
	}
}

if ( ! function_exists( 'wp_link_pages' ) ) {
	function wp_link_pages() {
		return '';
	}
}

if ( ! function_exists( 'is_page' ) ) {
	function is_page( $page = '' ) {
		return false;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'is_category' ) ) {
	function is_category() { return false; }
}
if ( ! function_exists( 'is_tag' ) ) {
	function is_tag() { return false; }
}
if ( ! function_exists( 'is_author' ) ) {
	function is_author() { return false; }
}
if ( ! function_exists( 'is_year' ) ) {
	function is_year() { return false; }
}
if ( ! function_exists( 'is_month' ) ) {
	function is_month() { return false; }
}
if ( ! function_exists( 'is_day' ) ) {
	function is_day() { return false; }
}
if ( ! function_exists( 'is_tax' ) ) {
	function is_tax() { return false; }
}
if ( ! function_exists( 'is_post_type_archive' ) ) {
	function is_post_type_archive() { return false; }
}

if ( ! function_exists( 'has_tag' ) ) {
	function has_tag() { return false; }
}

if ( ! function_exists( 'is_search' ) ) {
	function is_search() { return false; }
}

if ( ! function_exists( 'is_archive' ) ) {
	function is_archive() { return false; }
}

if ( ! function_exists( 'is_404' ) ) {
	function is_404() { return false; }
}

if ( ! class_exists( 'WP_Query' ) ) {
	class WP_Query {
		public function __construct( $args = array() ) {}
		public function have_posts() { return false; }
	}
}

if ( ! function_exists( 'wp_reset_postdata' ) ) {
	function wp_reset_postdata() {}
}

if ( ! function_exists( 'esc_attr__' ) ) {
	function esc_attr__( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'get_the_ID' ) ) {
	function get_the_ID() {
		return 1;
	}
}

if ( ! function_exists( 'paginate_links' ) ) {
	function paginate_links( $args = '' ) {
		return '';
	}
}

if ( ! function_exists( 'wp_list_pluck' ) ) {
	function wp_list_pluck( $list, $field, $index_key = null ) {
		return array();
	}
}
