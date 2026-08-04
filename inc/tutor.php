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
 * Add "My Certificates" tab to Tutor LMS Dashboard.
 *
 * @param array $nav_items Existing navigation items.
 * @return array
 */
function bia_learn_add_certificates_dashboard_tab( $nav_items ) {
	$nav_items['my-certificates'] = array(
		'title' => __( 'เกียรติบัตรของฉัน', 'bia-learn' ),
		'icon'  => 'certificate',
	);
	return $nav_items;
}
add_filter( 'tutor_dashboard/nav_items', 'bia_learn_add_certificates_dashboard_tab' );

/**
 * Register endpoints for custom Tutor LMS Dashboard tabs so they don't return 404.
 */
function bia_learn_register_tutor_dashboard_endpoints() {
	add_rewrite_endpoint( 'my-certificates', EP_PAGES );
	add_rewrite_endpoint( 'continue-learning', EP_PAGES );
}
add_action( 'init', 'bia_learn_register_tutor_dashboard_endpoints' );

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
	// Least privilege: this seeds pages and rewrites core reading options, and
	// it is hooked to admin_init as a self-heal — never run it from AJAX or a
	// low-privilege session (subscribers/students also trigger admin_init).
	if ( ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Run the self-heal once per theme version instead of on every admin load.
	if ( doing_action( 'admin_init' ) && get_option( 'bia_learn_pages_seeded' ) === BIA_LEARN_VERSION ) {
		return;
	}

	$pages = array(
		'about'       => array( __( 'เกี่ยวกับเรา', 'bia-learn' ), 'page-templates/template-about.php' ),
		'contact'     => array( __( 'ติดต่อเรา', 'bia-learn' ), 'page-templates/template-contact.php' ),
		'faq'         => array( __( 'คำถามที่พบบ่อย', 'bia-learn' ), 'page-templates/template-faq.php' ),
		'instructors' => array( __( 'ผู้สอนและวิทยากร', 'bia-learn' ), 'page-templates/template-instructors.php' ),
		'statistics'  => array( __( 'สถิติการเรียนรู้', 'bia-learn' ), 'page-templates/template-statistics.php' ),
		'auth'        => array( __( 'เข้าสู่ระบบ', 'bia-learn' ), 'page-templates/template-auth.php' ),
		'dashboard'   => array( __( 'แดชบอร์ดผู้เรียน', 'bia-learn' ), 'page-templates/template-dashboard.php' ),
		'tutorial'    => array( __( 'วิธีใช้งาน', 'bia-learn' ), 'page-templates/template-tutorial.php' ),
		'home'        => array( __( 'หน้าแรก', 'bia-learn' ), '' ),
		'news'        => array( __( 'ข่าวสารและบทความ', 'bia-learn' ), '' ),
	);

	foreach ( $pages as $slug => $data ) {
		$page = get_page_by_path( $slug );
		if ( ! $page ) {
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
				if ( ! empty( $data[1] ) ) {
					update_post_meta( $page_id, '_wp_page_template', $data[1] );
				}
				if ( 'home' === $slug ) {
					update_option( 'show_on_front', 'page' );
					update_option( 'page_on_front', $page_id );
				} elseif ( 'news' === $slug ) {
					update_option( 'page_for_posts', $page_id );
				}
				$page = get_post( $page_id );
			}
		}

		// Ensure Tutor LMS is bound to the dashboard page even if it already existed.
		if ( 'dashboard' === $slug && $page ) {
			if ( function_exists( 'tutor_utils' ) ) {
				$tutor_option = get_option( 'tutor_option', array() );
				if ( empty( $tutor_option['tutor_dashboard_page_id'] ) || (int) $tutor_option['tutor_dashboard_page_id'] !== $page->ID ) {
					$tutor_option['tutor_dashboard_page_id'] = $page->ID;
					update_option( 'tutor_option', $tutor_option );
					flush_rewrite_rules();
				}
			}
		}
	}

	update_option( 'bia_learn_pages_seeded', BIA_LEARN_VERSION );
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
	// Authorizenter's administrator escape hatch (?external=wordpress) is the one
	// URL that still accepts a password when SSO is enforced. Redirecting it to the
	// branded page would leave no way in at all if the identity provider is down —
	// and on this host wp-login.php is the only credential endpoint reachable,
	// since the branded page offers SSO buttons only.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['external'] ) && 'wordpress' === sanitize_key( wp_unslash( $_GET['external'] ) ) ) {
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
	$redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	wp_safe_redirect( bia_learn_auth_url( 'register' === $action ? 'register' : 'login', $redirect ) );
	exit;
}
add_action( 'login_init', 'bia_learn_redirect_wp_login' );

/**
 * Templates whose output must never reach the page on an SSO-only site.
 *
 * Tutor prints its username/password modal from `views/modal/login.php` in more
 * than one place — `templates/archive-course-init.php` line 180 puts it on the
 * course archive, and the public profile does the same — so removing the call
 * from the theme's own template copies is not enough. On this site the markup is
 * pure liability: password sign-in is disabled, so the form cannot succeed, and
 * a hidden password field invites someone to revive it later.
 *
 * @var string[]
 */
const BIA_LEARN_SUPPRESSED_TUTOR_TEMPLATES = array( '/views/modal/login.php' );

/**
 * Whether Tutor's own login system is switched on.
 *
 * With it on, passwords are in play again and Tutor's screens should be left
 * alone; every suppression here is conditional on it being off.
 *
 * @return bool
 */
function bia_learn_tutor_native_login_enabled() {
	$enabled = function_exists( 'tutor_utils' )
		? (bool) tutor_utils()->get_option( 'enable_tutor_native_login', null, true, true )
		: false;

	/**
	 * Whether Tutor's own username/password login is considered active.
	 *
	 * Everything this theme suppresses for SSO-only operation keys off this, so a
	 * site can force either behaviour without touching Tutor's settings.
	 *
	 * @param bool $enabled Value read from Tutor's settings.
	 */
	return (bool) apply_filters( 'bia_learn_tutor_native_login', $enabled );
}

/**
 * Whether a template path is one we suppress.
 *
 * Matched on the path suffix rather than the absolute path, so it keeps working
 * wherever the plugin is installed. Kept pure for the tests.
 *
 * @param string $template Absolute template path Tutor is about to include.
 * @return bool
 */
function bia_learn_is_suppressed_tutor_template( $template ) {
	$template = str_replace( '\\', '/', (string) $template );

	foreach ( BIA_LEARN_SUPPRESSED_TUTOR_TEMPLATES as $needle ) {
		if ( '' !== $needle && substr( $template, -strlen( $needle ) ) === $needle ) {
			return true;
		}
	}

	return false;
}

/**
 * Swallow the output of a suppressed Tutor template.
 *
 * `tutor_load_template_from_custom_path()` offers no filter on the path — it
 * includes the file directly — but it does fire a before/after action pair, and
 * both receive the path. Buffering between them discards exactly that
 * template's markup and nothing else, so the password form never reaches the
 * browser. Doing it server-side matters: hiding it with CSS would still ship a
 * password field in the HTML.
 *
 * @param string $template Template path.
 * @return void
 */
function bia_learn_suppress_tutor_template_start( $template ) {
	if ( bia_learn_tutor_native_login_enabled() || ! bia_learn_is_suppressed_tutor_template( $template ) ) {
		return;
	}

	ob_start();
	$GLOBALS['bia_learn_suppressing_tutor_template'] = ob_get_level();
}
add_action( 'tutor_load_template_from_custom_path_before', 'bia_learn_suppress_tutor_template_start', 10, 1 );

/**
 * Drop the buffer opened above.
 *
 * The buffer level is remembered so a nested template cannot leave a stray
 * buffer open — if anything else has since opened one, leave it alone.
 *
 * @param string $template Template path.
 * @return void
 */
function bia_learn_suppress_tutor_template_end( $template ) {
	if ( empty( $GLOBALS['bia_learn_suppressing_tutor_template'] ) ) {
		return;
	}

	if ( ! bia_learn_is_suppressed_tutor_template( $template ) ) {
		return;
	}

	if ( ob_get_level() === (int) $GLOBALS['bia_learn_suppressing_tutor_template'] ) {
		ob_end_clean();
	}

	unset( $GLOBALS['bia_learn_suppressing_tutor_template'] );
}
add_action( 'tutor_load_template_from_custom_path_after', 'bia_learn_suppress_tutor_template_end', 10, 1 );

/**
 * Tutor dashboard sub-pages that exist only to serve password sign-in.
 *
 * @param string $page Dashboard sub-page slug.
 * @return bool
 */
function bia_learn_is_tutor_password_page( $page ) {
	return in_array( (string) $page, array( 'retrieve-password', 'reset-password' ), true );
}

/**
 * Whether a request for a Tutor dashboard page should be sent to the branded
 * auth page instead. Kept free of WordPress state so it can be tested directly.
 *
 * @param string $page                 Dashboard sub-page slug.
 * @param bool   $is_logged_in         Whether someone is signed in.
 * @param bool   $native_login_enabled Whether Tutor's own login system is on.
 * @return bool
 */
function bia_learn_should_redirect_tutor_password_page( $page, $is_logged_in, $native_login_enabled ) {
	if ( $is_logged_in || $native_login_enabled ) {
		return false;
	}

	return bia_learn_is_tutor_password_page( $page );
}

/**
 * Resolve the requested Tutor dashboard sub-page.
 *
 * Prefers Tutor's query var and falls back to the last path segment, because the
 * rewrite rules have moved between Tutor releases and a missing query var would
 * silently disable the guard.
 *
 * @return string
 */
function bia_learn_current_tutor_dashboard_page() {
	$page = function_exists( 'get_query_var' ) ? (string) get_query_var( 'tutor_dashboard_page' ) : '';
	if ( '' !== $page ) {
		return $page;
	}

	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$bits = array_values( array_filter( explode( '/', $path ) ) );

	return $bits ? (string) end( $bits ) : '';
}

/**
 * Send Tutor's "forgot / reset password" pages to the branded auth page.
 *
 * Those screens mail out a password that cannot be used: sign-in is SSO-only, so
 * the reset succeeds and the login still fails, which is worse than not offering
 * it. Tutor keeps them reachable even with its native login switched off, and
 * they are linked from its login markup, so the guard sits on template_redirect
 * rather than on a template override — Tutor has renamed those templates before.
 *
 * @return void
 */
function bia_learn_redirect_tutor_password_pages() {
	if ( ! function_exists( 'tutor_utils' ) || ! function_exists( 'bia_learn_auth_url' ) ) {
		return;
	}

	$native = (bool) tutor_utils()->get_option( 'enable_tutor_native_login', null, true, true );

	if ( ! bia_learn_should_redirect_tutor_password_page( bia_learn_current_tutor_dashboard_page(), is_user_logged_in(), $native ) ) {
		return;
	}

	wp_safe_redirect( bia_learn_auth_url( 'login' ) );
	exit;
}
add_action( 'template_redirect', 'bia_learn_redirect_tutor_password_pages' );

/**
 * Filter wp_login_url to point to our branded auth page.
 * Prevents Tutor LMS from setting the login URL to the dashboard,
 * which causes an infinite redirect loop when clicking "Enroll Now".
 *
 * @param string $login_url The login URL.
 * @param string $redirect  The redirect URL.
 * @return string
 */
function bia_learn_filter_login_url( $login_url, $redirect = '' ) {
	$auth = get_page_by_path( 'auth' );
	if ( $auth ) {
		$url = get_permalink( $auth );
		if ( $redirect ) {
			$url = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $url );
		}
		return $url;
	}
	return $login_url;
}
add_filter( 'login_url', 'bia_learn_filter_login_url', 99, 2 );

/**
 * Filter wp_registration_url to point to our branded auth page.
 *
 * @param string $register_url The registration URL.
 * @return string
 */
function bia_learn_filter_register_url( $register_url ) {
	$auth = get_page_by_path( 'auth' );
	if ( $auth ) {
		return add_query_arg( 'tab', 'register', get_permalink( $auth ) );
	}
	return $register_url;
}
add_filter( 'register_url', 'bia_learn_filter_register_url', 99 );


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

/**
 * Render ความต้องการ, แท็ก, กลุ่มเป้าหมาย inside the "ข้อมูลคอร์ส" (info) tab.
 *
 * These three sections were removed from the sidebar in tutor/single-course.php
 * and are surfaced here so they appear below the curriculum within the info tab.
 */
function bia_learn_course_info_extra_sections() {
	if ( ! is_singular( 'courses' ) ) {
		return;
	}
	tutor_course_requirements_html();
	tutor_course_target_audience_html();
	tutor_course_tags_html();
}
add_action( 'tutor_course/single/tab/info/after', 'bia_learn_course_info_extra_sections' );
