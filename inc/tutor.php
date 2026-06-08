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
 *   2. A Tailwind layer at the end of src/css/main.css ("Tutor LMS — design
 *      system harmony") that overrides Tutor's own CSS custom properties
 *      (--tutor-color-primary etc.) so its native UI adopts the BIA Learn
 *      brand automatically, plus a thin bridge for shape/typography.
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
	if ( ! is_array( $classes ) ) {
		$classes = preg_split( '/\s+/', trim( (string) $classes ) );
	}

	$classes[] = 'bia-tutor-btn';

	return array_values( array_unique( array_filter( $classes ) ) );
}
add_filter( 'tutor_button_class', 'bia_learn_tutor_btn_classes' );

/**
 * Ensure the supporting WP pages used by the theme menus exist after the
 * theme is activated (Instructors, FAQ, About, Contact, News, Statistics).
 *
 * Runs once; safe to re-run (checks by slug).
 */
function bia_learn_register_supporting_pages() {
	$created = (array) get_option( 'bia_learn_created_pages', array() );

	$pages = array(
		'about'       => array( __( 'เกี่ยวกับเรา', 'bia-learn' ), 'page-templates/template-about.php' ),
		'contact'     => array( __( 'ติดต่อเรา', 'bia-learn' ), 'page-templates/template-contact.php' ),
		'faq'         => array( __( 'คำถามที่พบบ่อย', 'bia-learn' ), 'page-templates/template-faq.php' ),
		'instructors' => array( __( 'ผู้สอนและวิทยากร', 'bia-learn' ), 'page-templates/template-instructors.php' ),
		'statistics'  => array( __( 'สถิติการเรียนรู้', 'bia-learn' ), 'page-templates/template-statistics.php' ),
		'auth'        => array( __( 'เข้าสู่ระบบ', 'bia-learn' ), 'page-templates/template-auth.php' ),
		'tutorial'    => array( __( 'วิธีใช้งาน', 'bia-learn' ), 'page-templates/template-tutorial.php' ),
	);

	$changed = false;
	foreach ( $pages as $slug => $data ) {
		// Create each supporting page at most once ever (tracked in the option)
		// so deleting one in the admin won't have it reappear.
		if ( in_array( $slug, $created, true ) ) {
			continue;
		}
		if ( ! get_page_by_path( $slug ) ) {
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
		$created[] = $slug;
		$changed   = true;
	}

	if ( $changed ) {
		update_option( 'bia_learn_created_pages', $created );
	}
}
add_action( 'after_switch_theme', 'bia_learn_register_supporting_pages' );
// Self-heal on existing installs (e.g. theme deployed via git, not re-activated):
// creates any newly-added supporting page on the next admin visit, once each.
add_action( 'admin_init', 'bia_learn_register_supporting_pages' );

/**
 * Send the default WordPress login / registration screen to the branded Auth
 * page (template-auth.php) when one exists. Only intercepts GET display of the
 * login/register screens — never form posts, logout, or password resets — so
 * authentication keeps working normally.
 */
function bia_learn_redirect_wp_login() {
	if ( 'GET' !== strtoupper( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : 'GET' ) ) {
		return;
	}
	$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'login'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! in_array( $action, array( 'login', 'register' ), true ) ) {
		return; // leave logout / lostpassword / rp / resetpass to WordPress.
	}
	if ( is_user_logged_in() && 'login' === $action ) {
		return;
	}
	if ( ! get_page_by_path( 'auth' ) ) {
		return; // no branded page yet — keep the default screen.
	}
	$redirect = isset( $_GET['redirect_to'] ) ? wp_unslash( $_GET['redirect_to'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	wp_safe_redirect( bia_learn_auth_url( 'register' === $action ? 'register' : 'login', $redirect ) );
	exit;
}
add_action( 'login_init', 'bia_learn_redirect_wp_login' );

/**
 * Flush the cached stats whenever a course / enrolment changes.
 */
function bia_learn_flush_stats_cache() {
	delete_transient( 'bia_learn_stats' );
}
add_action( 'save_post_courses', 'bia_learn_flush_stats_cache' );
add_action( 'tutor_after_enroll', 'bia_learn_flush_stats_cache' );
add_action( 'tutor_course_complete_after', 'bia_learn_flush_stats_cache' );

/**
 * Hide the "free" price label that Tutor LMS prints on free courses
 * (Thai: "เข้าถึงได้ฟรี", English source "Free" / "Free Access"). Scoped to the
 * 'tutor' text domain so other "Free" wording elsewhere is untouched.
 *
 * Adjust or remove this filter to restore / re-label the free indicator.
 *
 * @param string $translation Translated text.
 * @param string $text        Original (untranslated) text.
 * @param string $domain      Text domain.
 * @return string
 */
function bia_learn_hide_tutor_free_label( $translation, $text, $domain ) {
	if ( 'tutor' !== $domain ) {
		return $translation;
	}

	// Match the rendered label only (avoids blanking other "Free" strings such
	// as the price-filter option).
	$free_labels = array( 'เข้าถึงได้ฟรี', 'Free Access', 'Free access' );
	if ( in_array( $translation, $free_labels, true ) ) {
		return '';
	}

	return $translation;
}
add_filter( 'gettext', 'bia_learn_hide_tutor_free_label', 20, 3 );

/**
 * Collect a course's approved reviews (Tutor stores them as comments with a
 * `tutor_rating` meta), sorted highest-rated first, then most recent first.
 *
 * @param int $course_id Course post ID.
 * @return array<int, array{rating:float,content:string,author:string,author_id:int,date:int}>
 */
function bia_learn_get_course_reviews( $course_id ) {
	$comments = get_comments(
		array(
			'post_id' => $course_id,
			'status'  => 'approve',
			'number'  => 100,
			'orderby' => 'comment_date_gmt',
			'order'   => 'DESC',
		)
	);

	$rows = array();
	foreach ( $comments as $c ) {
		$rating = (float) get_comment_meta( $c->comment_ID, 'tutor_rating', true );
		if ( $rating <= 0 ) {
			$rating = (float) get_comment_meta( $c->comment_ID, 'rating', true );
		}
		if ( $rating <= 0 ) {
			continue;
		}
		$rows[] = array(
			'rating'    => $rating,
			'content'   => (string) $c->comment_content,
			'author'    => (string) $c->comment_author,
			'author_id' => (int) $c->user_id,
			'date'      => strtotime( $c->comment_date_gmt ),
		);
	}

	usort(
		$rows,
		static function ( $a, $b ) {
			if ( $a['rating'] !== $b['rating'] ) {
				return $b['rating'] <=> $a['rating']; // highest stars first.
			}
			return $b['date'] <=> $a['date']; // then most recent.
		}
	);

	return $rows;
}

/**
 * Render a row of 5 star icons for a given rating.
 *
 * @param float $rating Rating value (0–5).
 * @return string
 */
function bia_learn_star_row( $rating ) {
	$out = '';
	for ( $i = 1; $i <= 5; $i++ ) {
		$cls  = $i <= round( $rating ) ? 'text-gold' : 'text-paper-300';
		$out .= '<span class="' . $cls . '">' . bia_learn_icon( 'star', 'h-4 w-4' ) . '</span>';
	}
	return '<span class="inline-flex items-center gap-0.5">' . $out . '</span>';
}

/**
 * Build the "รีวิวจากผู้เรียน" sidebar widget HTML for the current course.
 *
 * @param int $course_id Course post ID.
 * @return string
 */
function bia_learn_render_course_reviews( $course_id ) {
	$reviews = bia_learn_get_course_reviews( $course_id );

	ob_start();
	?>
	<div class="bia-course-reviews tutor-mt-24 mt-6">
		<div class="card p-5">
			<h3 class="font-sans text-base font-bold text-ink"><?php esc_html_e( 'รีวิวจากผู้เรียน', 'bia-learn' ); ?></h3>

			<?php if ( empty( $reviews ) ) : ?>
				<p class="mt-2 text-sm text-ink-light"><?php esc_html_e( 'ยังไม่มีรีวิว เป็นคนแรกที่รีวิวคอร์สนี้', 'bia-learn' ); ?></p>
			<?php else : ?>
				<?php
				$count = count( $reviews );
				$avg   = 0;
				foreach ( $reviews as $r ) {
					$avg += $r['rating'];
				}
				$avg = $avg / $count;
				?>
				<div class="mt-2 flex items-center gap-2">
					<span class="font-sans text-2xl font-bold text-ink"><?php echo esc_html( number_format( $avg, 1 ) ); ?></span>
					<?php echo bia_learn_star_row( $avg ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="text-xs text-ink-light"><?php printf( esc_html__( '(%d รีวิว)', 'bia-learn' ), $count ); ?></span>
				</div>

				<ul class="mt-4 space-y-4">
					<?php foreach ( array_slice( $reviews, 0, 5 ) as $r ) : ?>
						<li class="border-t border-paper-100 pt-4 first:border-0 first:pt-0">
							<div class="flex items-center gap-2">
								<?php echo get_avatar( $r['author_id'] ? $r['author_id'] : $r['author'], 32, '', esc_attr( $r['author'] ), array( 'class' => 'h-8 w-8 rounded-full' ) ); ?>
								<div class="min-w-0">
									<p class="truncate text-sm font-semibold text-ink"><?php echo esc_html( $r['author'] ); ?></p>
									<div class="flex items-center gap-2">
										<?php echo bia_learn_star_row( $r['rating'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										<span class="text-2xs text-ink-light"><?php echo esc_html( date_i18n( 'j M Y', $r['date'] ) ); ?></span>
									</div>
								</div>
							</div>
							<?php if ( trim( $r['content'] ) ) : ?>
								<p class="mt-2 line-clamp-3 text-sm leading-relaxed text-ink-light"><?php echo esc_html( $r['content'] ); ?></p>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>

				<?php if ( $count > 5 ) : ?>
					<p class="mt-4 text-xs text-ink-light"><?php printf( esc_html__( 'และอีก %d รีวิว', 'bia-learn' ), $count - 5 ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Inject the reviews widget right after the "คอร์สโดย" instructor box in the
 * Tutor single-course sidebar (.tutor-single-course-sidebar-more), via a small
 * inline script — no Tutor template override required.
 */
function bia_learn_enqueue_course_reviews() {
	if ( ! is_singular( 'courses' ) ) {
		return;
	}

	$html = bia_learn_render_course_reviews( get_queried_object_id() );
	if ( ! $html ) {
		return;
	}

	$script  = 'window.__biaCourseReviews=' . wp_json_encode( $html ) . ';';
	$script .= '(function(){function ins(){var t=document.querySelector(".tutor-single-course-sidebar-more");'
		. 'if(!t||!window.__biaCourseReviews||document.querySelector(".bia-course-reviews"))return;'
		. 'var w=document.createElement("div");w.innerHTML=window.__biaCourseReviews;'
		. 'if(w.firstElementChild)t.insertAdjacentElement("afterend",w.firstElementChild);}'
		. 'if(document.readyState!=="loading")ins();else document.addEventListener("DOMContentLoaded",ins);})();';

	wp_add_inline_script( 'bia-learn-main', $script );
}
add_action( 'wp_enqueue_scripts', 'bia_learn_enqueue_course_reviews', 20 );
