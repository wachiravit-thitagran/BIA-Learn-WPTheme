<?php
/**
 * Tutor LMS integration.
 *
 * This file only loads when Tutor LMS is active (see functions.php).
 *
 * Strategy
 * --------
 * Tutor LMS renders its own course/lesson/dashboard pages, calling
 * get_header()/get_footer() so they already sit inside this theme's chrome.
 * We theme the inner Tutor markup in two complementary ways:
 *
 *   1. Hooks/filters here — wrap content in our container, set loop columns,
 *      register the supporting WP pages, and replace the loop course card so
 *      the listing matches the homepage cards.
 *   2. A Tailwind layer (src/css/tutor.css, imported into main.css) that maps
 *      Tutor's stable `.tutor-*` classes onto the BIA Learn palette / buttons.
 *
 * To override a Tutor template wholesale, copy it from
 * `wp-content/plugins/tutor/templates/<path>` into this theme's
 * `tutor/<path>` directory and edit the copy. See tutor/README.md.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Declare Tutor monetization / spotlight support and let Tutor use the theme
 * header & footer for its pages.
 */
function bia_learn_tutor_setup() {
	add_theme_support( 'tutor' );

	// Use this theme's header/footer on Tutor's full-width pages.
	add_filter( 'tutor_should_use_theme_header_footer', '__return_true' );
}
add_action( 'after_setup_theme', 'bia_learn_tutor_setup', 11 );

/**
 * Course archive grid: 3 columns to match the rest of the site.
 *
 * @param int $cols Existing column count.
 * @return int
 */
function bia_learn_tutor_loop_columns( $cols ) {
	return is_active_sidebar( 'sidebar-1' ) ? 2 : 3;
}
add_filter( 'tutor_course_archive_grid_column', 'bia_learn_tutor_loop_columns' );
add_filter( 'tutor_courses_col_per_row', 'bia_learn_tutor_loop_columns' );

/**
 * Open a themed wrapper before the course archive list.
 */
function bia_learn_tutor_archive_before() {
	bia_learn_page_hero(
		array(
			'eyebrow'    => __( 'คอร์สเรียน', 'bia-learn' ),
			'title'      => __( 'คอร์สเรียนทั้งหมด', 'bia-learn' ),
			'subtitle'   => __( 'เลือกเรียนรู้ในหัวข้อที่คุณสนใจ เริ่มต้นได้ทันที เรียนฟรีหลากหลายคอร์ส', 'bia-learn' ),
			'breadcrumb' => true,
		)
	);
	echo '<div class="section"><div class="container-bia">';
}
add_action( 'tutor_course/archive/before_loop', 'bia_learn_tutor_archive_before', 5 );

/**
 * Close the themed wrapper after the course archive list.
 */
function bia_learn_tutor_archive_after() {
	echo '</div></div>';
}
add_action( 'tutor_course/archive/after_loop', 'bia_learn_tutor_archive_after', 50 );

/**
 * Give Tutor buttons our pill styling by appending utility classes.
 *
 * @param array $classes Button classes.
 * @return array
 */
function bia_learn_tutor_btn_classes( $classes ) {
	$classes[] = 'bia-tutor-btn';
	return $classes;
}
add_filter( 'tutor_button_class', 'bia_learn_tutor_btn_classes' );

/**
 * Ensure the supporting WP pages used by the theme menus exist after the
 * theme is activated (Instructors, FAQ, About, Contact, News, Statistics).
 *
 * Runs once; safe to re-run (checks by slug).
 */
function bia_learn_register_supporting_pages() {
	if ( get_option( 'bia_learn_pages_created' ) ) {
		return;
	}

	$pages = array(
		'about'       => array( __( 'เกี่ยวกับเรา', 'bia-learn' ), 'page-templates/template-about.php' ),
		'contact'     => array( __( 'ติดต่อเรา', 'bia-learn' ), 'page-templates/template-contact.php' ),
		'faq'         => array( __( 'คำถามที่พบบ่อย', 'bia-learn' ), 'page-templates/template-faq.php' ),
		'instructors' => array( __( 'ผู้สอนและวิทยากร', 'bia-learn' ), 'page-templates/template-instructors.php' ),
		'statistics'  => array( __( 'สถิติการเรียนรู้', 'bia-learn' ), 'page-templates/template-statistics.php' ),
	);

	foreach ( $pages as $slug => $data ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		$page_id = wp_insert_post(
			array(
				'post_title'   => $data[0],
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			)
		);
		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', $data[1] );
		}
	}

	update_option( 'bia_learn_pages_created', 1 );
}
add_action( 'after_switch_theme', 'bia_learn_register_supporting_pages' );

/**
 * Flush the cached stats whenever a course / enrolment changes.
 */
function bia_learn_flush_stats_cache() {
	delete_transient( 'bia_learn_stats' );
}
add_action( 'save_post_courses', 'bia_learn_flush_stats_cache' );
add_action( 'tutor_after_enroll', 'bia_learn_flush_stats_cache' );
add_action( 'tutor_course_complete_after', 'bia_learn_flush_stats_cache' );
