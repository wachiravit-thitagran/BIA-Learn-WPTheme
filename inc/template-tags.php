<?php
/**
 * Reusable presentation helpers used across templates.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Output a styled breadcrumb trail.
 *
 * Uses Yoast / Rank Math output when available, otherwise builds a sensible
 * default from the current query.
 */
function bia_learn_breadcrumb() {
	if ( is_front_page() ) {
		return;
	}

	$home_label = __( 'หน้าแรก', 'bia-learn' );
	$items      = array();
	$items[]    = '<a href="' . esc_url( home_url( '/' ) ) . '" class="hover:text-crimson">' . esc_html( $home_label ) . '</a>';

	if ( is_singular( 'post' ) ) {
		$blog_id = (int) get_option( 'page_for_posts' );
		if ( $blog_id ) {
			$items[] = '<a href="' . esc_url( get_permalink( $blog_id ) ) . '" class="hover:text-crimson">' . esc_html( get_the_title( $blog_id ) ) . '</a>';
		}
		$items[] = '<span class="text-ink-soft">' . esc_html( wp_trim_words( get_the_title(), 8, '…' ) ) . '</span>';
	} elseif ( is_page() ) {
		$ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );
		foreach ( $ancestors as $ancestor ) {
			$items[] = '<a href="' . esc_url( get_permalink( $ancestor ) ) . '" class="hover:text-crimson">' . esc_html( get_the_title( $ancestor ) ) . '</a>';
		}
		$items[] = '<span class="text-ink-soft">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$items[] = '<span class="text-ink-soft">' . esc_html( single_term_title( '', false ) ) . '</span>';
	} elseif ( is_search() ) {
		$items[] = '<span class="text-ink-soft">' . sprintf( /* translators: %s: search term */ esc_html__( 'ผลการค้นหา: %s', 'bia-learn' ), esc_html( get_search_query() ) ) . '</span>';
	} elseif ( is_archive() ) {
		$items[] = '<span class="text-ink-soft">' . esc_html( wp_strip_all_tags( get_the_archive_title() ) ) . '</span>';
	} elseif ( is_404() ) {
		$items[] = '<span class="text-ink-soft">' . esc_html__( 'ไม่พบหน้า', 'bia-learn' ) . '</span>';
	}

	$separator = '<svg class="h-3 w-3 text-paper-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M7 5l5 5-5 5" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>';

	echo '<nav class="container-bia flex items-center gap-2 py-4 text-sm text-ink-light" aria-label="' . esc_attr__( 'เส้นทางนำทาง', 'bia-learn' ) . '">';
	echo wp_kses_post( implode( ' ' . $separator . ' ', $items ) );
	echo '</nav>';
}

/**
 * Numbered pagination styled to the theme.
 */
function bia_learn_pagination() {
	$links = paginate_links(
		array(
			'type'      => 'array',
			'mid_size'  => 1,
			'prev_text' => '<span aria-hidden="true">&larr;</span><span class="sr-only">' . esc_html__( 'ก่อนหน้า', 'bia-learn' ) . '</span>',
			'next_text' => '<span class="sr-only">' . esc_html__( 'ถัดไป', 'bia-learn' ) . '</span><span aria-hidden="true">&rarr;</span>',
		)
	);

	if ( empty( $links ) ) {
		return;
	}

	echo '<nav class="mt-12 flex justify-center" aria-label="' . esc_attr__( 'แบ่งหน้า', 'bia-learn' ) . '"><ul class="flex flex-wrap items-center gap-2">';
	foreach ( $links as $link ) {
		$is_current = strpos( $link, 'current' ) !== false;
		$classes    = $is_current
			? 'flex h-10 min-w-10 items-center justify-center rounded-full bg-crimson px-3 font-semibold text-white'
			: 'flex h-10 min-w-10 items-center justify-center rounded-full border border-paper-200 bg-white px-3 text-ink-soft transition hover:border-crimson hover:text-crimson';
		// Inject our utility classes into the generated anchor / span.
		$link = preg_replace( '/<(a|span)\s/', '<$1 class="' . esc_attr( $classes ) . '" ', $link, 1 );
		echo '<li>' . wp_kses_post( $link ) . '</li>';
	}
	echo '</ul></nav>';
}

/**
 * Post meta line (date + author + reading time + category).
 */
function bia_learn_post_meta() {
	$cats = get_the_category_list( '<span class="text-paper-400">·</span>' );
	echo '<div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ink-light">';
	printf(
		'<time datetime="%1$s" class="inline-flex items-center gap-1.5">%2$s%3$s</time>',
		esc_attr( get_the_date( 'c' ) ),
		bia_learn_icon( 'calendar', 'h-4 w-4 text-crimson' ),
		esc_html( get_the_date() )
	);
	echo '<span class="text-paper-400">·</span>';
	printf(
		'<span class="inline-flex items-center gap-1.5">%1$s%2$s</span>',
		bia_learn_icon( 'clock', 'h-4 w-4 text-crimson' ),
		esc_html( bia_learn_reading_time() )
	);
	if ( $cats ) {
		echo '<span class="text-paper-400">·</span>';
		echo '<span class="inline-flex flex-wrap items-center gap-1 [&_a]:text-crimson [&_a:hover]:underline">' . wp_kses_post( $cats ) . '</span>';
	}
	echo '</div>';
}

/**
 * Estimate reading time from the current post content.
 *
 * @return string Human readable reading time.
 */
function bia_learn_reading_time() {
	$words   = str_word_count( wp_strip_all_tags( get_the_content() ) );
	$thai    = mb_strlen( wp_strip_all_tags( get_the_content() ), 'UTF-8' ) / 400; // ~400 thai chars/min.
	$minutes = max( 1, (int) ceil( max( $words / 200, $thai ) ) );
	/* translators: %d: number of minutes */
	return sprintf( _n( 'อ่าน %d นาที', 'อ่าน %d นาที', $minutes, 'bia-learn' ), $minutes );
}

/**
 * Inline SVG icon set. Keeps markup tidy and avoids an icon-font dependency.
 *
 * @param string $name    Icon key.
 * @param string $classes Extra CSS classes.
 * @return string SVG markup (already escaped/safe).
 */
function bia_learn_icon( $name, $classes = 'h-5 w-5' ) {
	// Icons from Tabler Icons (MIT) — inline & self-hosted, no icon font/CDN.
	// 'lotus' is a custom brand mark. To add an icon: copy its inner SVG from
	// the @tabler/icons package (icons/outline or icons/filled) below.
	$outline = array(
		'calendar' => '<path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12" />  <path d="M16 3v4" />  <path d="M8 3v4" />  <path d="M4 11h16" />  <path d="M11 15h1" />  <path d="M12 15v3" />',
		'clock' => '<path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />  <path d="M12 7v5l3 3" />',
		'user' => '<path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />  <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />',
		'book' => '<path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0" />  <path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0" />  <path d="M3 6l0 13" />  <path d="M12 6l0 13" />  <path d="M21 6l0 13" />',
		'arrow' => '<path d="M5 12l14 0" />  <path d="M13 18l6 -6" />  <path d="M13 6l6 6" />',
		'arrow-ul' => '<path d="M17 7l-10 10" />  <path d="M8 7l9 0l0 9" />',
		'search' => '<path d="M3 10a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />  <path d="M21 21l-6 -6" />',
		'menu' => '<path d="M4 6l16 0" />  <path d="M4 12l16 0" />  <path d="M4 18l16 0" />',
		'close' => '<path d="M18 6l-12 12" />  <path d="M6 6l12 12" />',
		'check' => '<path d="M5 12l5 5l10 -10" />',
		'users' => '<path d="M5 7a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />  <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />  <path d="M16 3.13a4 4 0 0 1 0 7.75" />  <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />',
		'cert' => '<path d="M12 15a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />  <path d="M13 17.5v4.5l2 -1.5l2 1.5v-4.5" />  <path d="M10 19h-5a2 2 0 0 1 -2 -2v-10c0 -1.1 .9 -2 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -1 1.73" />  <path d="M6 9l12 0" />  <path d="M6 12l3 0" />  <path d="M6 15l2 0" />',
		'chart' => '<path d="M3 13a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1l0 -6" />  <path d="M15 9a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1l0 -10" />  <path d="M9 5a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1l0 -14" />  <path d="M4 20h14" />',
		'mail' => '<path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10" />  <path d="M3 7l9 6l9 -6" />',
		'phone' => '<path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" />',
		'pin' => '<path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />  <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0" />',
		'quote' => '<path d="M10 11h-4a1 1 0 0 1 -1 -1v-3a1 1 0 0 1 1 -1h3a1 1 0 0 1 1 1v6c0 2.667 -1.333 4.333 -4 5" />  <path d="M19 11h-4a1 1 0 0 1 -1 -1v-3a1 1 0 0 1 1 -1h3a1 1 0 0 1 1 1v6c0 2.667 -1.333 4.333 -4 5" />',
		'chevron' => '<path d="M6 9l6 6l6 -6" />',
		'line' => '<path d="M21 10.663c0 -4.224 -4.041 -7.663 -9 -7.663s-9 3.439 -9 7.663c0 3.783 3.201 6.958 7.527 7.56c1.053 .239 .932 .644 .696 2.133c-.039 .238 -.184 .932 .777 .512c.96 -.42 5.18 -3.201 7.073 -5.48c1.304 -1.504 1.927 -3.029 1.927 -4.715v-.01" />',
		'lotus' => '<path d="M12 4c1.5 2 1.5 4 0 6-1.5-2-1.5-4 0-6z"/><path d="M12 10c3-1 5 0 6 2-2 2-5 2-6 0zm0 0c-3-1-5 0-6 2 2 2 5 2 6 0z"/><path d="M5 13c-1 2 0 4 2 5 3 1 7 1 10 0 2-1 3-3 2-5"/>',
	);
	$filled = array(
		'play' => '<path d="M6 4v16a1 1 0 0 0 1.524 .852l13 -8a1 1 0 0 0 0 -1.704l-13 -8a1 1 0 0 0 -1.524 .852z" />',
		'star' => '<path d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z" />',
		'facebook' => '<path d="M18 2a1 1 0 0 1 .993 .883l.007 .117v4a1 1 0 0 1 -.883 .993l-.117 .007h-3v1h3a1 1 0 0 1 .991 1.131l-.02 .112l-1 4a1 1 0 0 1 -.858 .75l-.113 .007h-2v6a1 1 0 0 1 -.883 .993l-.117 .007h-4a1 1 0 0 1 -.993 -.883l-.007 -.117v-6h-2a1 1 0 0 1 -.993 -.883l-.007 -.117v-4a1 1 0 0 1 .883 -.993l.117 -.007h2v-1a6 6 0 0 1 5.775 -5.996l.225 -.004h3z" />',
		'youtube' => '<path d="M18 3a5 5 0 0 1 5 5v8a5 5 0 0 1 -5 5h-12a5 5 0 0 1 -5 -5v-8a5 5 0 0 1 5 -5zm-9 6v6a1 1 0 0 0 1.514 .857l5 -3a1 1 0 0 0 0 -1.714l-5 -3a1 1 0 0 0 -1.514 .857z" />',
	);

	if ( isset( $filled[ $name ] ) ) {
		$inner = $filled[ $name ];
		$attrs = 'fill="currentColor" stroke="none"';
	} elseif ( isset( $outline[ $name ] ) ) {
		$inner = $outline[ $name ];
		$attrs = 'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';
	} else {
		return '';
	}

	return sprintf(
		'<svg class="%1$s" viewBox="0 0 24 24" %2$s aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $classes ),
		$attrs, // phpcs:ignore -- static, trusted attribute string.
		$inner  // phpcs:ignore -- static, trusted SVG markup.
	);
}

/**
 * Section heading block: eyebrow + title + optional lead, centered or left.
 *
 * @param array $args eyebrow, title, lead, align, class.
 */
function bia_learn_section_heading( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'eyebrow' => '',
			'title'   => '',
			'lead'    => '',
			'align'   => 'center',
			'class'   => '',
		)
	);

	$align_class = 'center' === $args['align'] ? 'mx-auto max-w-2xl text-center items-center' : 'max-w-2xl items-start text-left';
	echo '<div class="flex flex-col gap-4 ' . esc_attr( $align_class . ' ' . $args['class'] ) . '">';
	if ( $args['eyebrow'] ) {
		echo '<span class="eyebrow">' . esc_html( $args['eyebrow'] ) . '</span>';
	}
	if ( $args['title'] ) {
		echo '<h2 class="section-title">' . wp_kses_post( $args['title'] ) . '</h2>';
	}
	if ( $args['lead'] ) {
		echo '<p class="lead">' . wp_kses_post( $args['lead'] ) . '</p>';
	}
	echo '</div>';
}

/**
 * Decorative page hero used by archives, single posts and page templates.
 *
 * @param array $args title, subtitle, eyebrow, breadcrumb (bool), align.
 */
function bia_learn_page_hero( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'title'      => get_the_title(),
			'subtitle'   => '',
			'eyebrow'    => '',
			'breadcrumb' => true,
			'align'      => 'left',
		)
	);

	$align = 'center' === $args['align'] ? 'items-center text-center' : 'items-start text-left';
	?>
	<section class="relative overflow-hidden bg-plum-wash text-paper-100">
		<div class="absolute inset-0 bg-grain opacity-30" aria-hidden="true"></div>
		<div class="pointer-events-none absolute -right-16 -top-20 h-72 w-72 rounded-full bg-crimson/30 blur-3xl" aria-hidden="true"></div>
		<div class="container-bia relative flex flex-col gap-4 py-16 sm:py-20 <?php echo esc_attr( $align ); ?>">
			<?php if ( $args['eyebrow'] ) : ?>
				<span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-gold-light">
					<span class="inline-block h-px w-8 bg-gold-light"></span><?php echo esc_html( $args['eyebrow'] ); ?>
				</span>
			<?php endif; ?>
			<h1 class="max-w-3xl font-serif text-4xl font-bold leading-tight text-white sm:text-5xl"><?php echo wp_kses_post( $args['title'] ); ?></h1>
			<?php if ( $args['subtitle'] ) : ?>
				<p class="max-w-2xl text-lg text-paper-300"><?php echo wp_kses_post( $args['subtitle'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
	if ( $args['breadcrumb'] ) {
		echo '<div class="border-b border-paper-200 bg-paper-100/60">';
		bia_learn_breadcrumb();
		echo '</div>';
	}
}

/**
 * Get a Customizer value with a fallback default.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Default value.
 * @return mixed
 */
function bia_learn_option( $key, $default = '' ) {
	return get_theme_mod( $key, $default );
}

/**
 * Aggregate platform statistics, sourced from Tutor LMS when available.
 *
 * Cached for an hour via a transient to avoid repeated COUNT queries.
 *
 * @return array{courses:int,students:int,instructors:int,lessons:int,certificates:int}
 */
function bia_learn_get_stats() {
	$cached = get_transient( 'bia_learn_stats' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;
	$stats = array(
		'courses'      => 0,
		'students'     => 0,
		'instructors'  => 0,
		'lessons'      => 0,
		'certificates' => 0,
	);

	$user_counts = count_users();
	$tutils      = bia_learn_tutor_utils();

	if ( $tutils ) {
		$courses_counts   = wp_count_posts( 'courses' );
		$lesson_counts    = wp_count_posts( 'lesson' );
		$stats['courses'] = $courses_counts ? (int) $courses_counts->publish : 0;
		$stats['lessons'] = $lesson_counts ? (int) $lesson_counts->publish : 0;

		$stats['students'] = isset( $user_counts['avail_roles']['subscriber'] )
			? (int) $user_counts['avail_roles']['subscriber']
			: (int) $user_counts['total_users'];

		if ( method_exists( $tutils, 'get_total_instructors' ) ) {
			$instructor_count     = $tutils->get_total_instructors();
			$stats['instructors'] = is_numeric( $instructor_count ) ? (int) $instructor_count : 0;
		}

		// Enrolled students is a more meaningful "learners" number when present.
		$enrolled = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT post_author) FROM {$wpdb->posts} WHERE post_type = 'tutor_enrolled' AND post_status = 'completed'" );
		if ( $enrolled ) {
			$stats['students'] = $enrolled;
		}
	} else {
		$post_counts       = wp_count_posts( 'post' );
		$stats['courses']  = $post_counts ? (int) $post_counts->publish : 0;
		$stats['students'] = (int) $user_counts['total_users'];
	}

	set_transient( 'bia_learn_stats', $stats, HOUR_IN_SECONDS );
	return $stats;
}

/**
 * Fetch approved Tutor LMS instructors as WP_User objects.
 *
 * Uses the `tutor_instructor` role directly, which is stable across Tutor
 * versions (unlike the shifting positional signature of get_instructors()).
 *
 * @param int $limit Max instructors to return (-1 for all).
 * @return WP_User[]
 */
function bia_learn_get_instructors( $limit = -1 ) {
	$tutils = bia_learn_tutor_utils();

	if ( ! $tutils ) {
		return array();
	}

	$query = new WP_User_Query(
		array(
			'role__in' => array( 'tutor_instructor' ),
			'number'   => $limit,
			'orderby'  => 'registered',
			'order'    => 'DESC',
			'fields'   => 'all',
		)
	);

	$users = $query->get_results();

	// Fallback to Tutor's own API if the role query comes back empty.
	if ( empty( $users ) && method_exists( $tutils, 'get_instructors' ) ) {
		$raw = $tutils->get_instructors( 0, $limit > 0 ? $limit : 1000 );
		if ( is_array( $raw ) ) {
			foreach ( $raw as $row ) {
				$user_id = null;

				if ( is_object( $row ) && isset( $row->ID ) ) {
					$user_id = (int) $row->ID;
				} elseif ( $row instanceof WP_User ) {
					$user_id = (int) $row->ID;
				} elseif ( is_numeric( $row ) ) {
					$user_id = (int) $row;
				}

				$u = $user_id ? get_user_by( 'id', $user_id ) : false;
				if ( $u ) {
					$users[] = $u;
				}
			}
		}
	}

	return $users;
}

/**
 * Resolve a course's lead instructor as a WP_User (Tutor first, post author as
 * a fallback). Returns null when neither is available.
 *
 * @param int $course_id Course post ID.
 * @return WP_User|null
 */
function bia_learn_course_instructor( $course_id ) {
	$tutils = bia_learn_tutor_utils();
	$user   = null;

	if ( $tutils && method_exists( $tutils, 'get_instructors_by_course' ) ) {
		$list = $tutils->get_instructors_by_course( $course_id );
		if ( is_array( $list ) && ! empty( $list ) ) {
			$row = $list[0];
			$uid = ( is_object( $row ) && isset( $row->ID ) ) ? (int) $row->ID : ( is_numeric( $row ) ? (int) $row : 0 );
			if ( $uid ) {
				$user = get_user_by( 'id', $uid );
			}
		}
	}

	if ( ! $user ) {
		$author = (int) get_post_field( 'post_author', $course_id );
		if ( $author ) {
			$user = get_user_by( 'id', $author );
		}
	}

	return $user instanceof WP_User ? $user : null;
}

/**
 * Completion percentage for the current (or given) user on a course — but only
 * when they are enrolled. Returns null when Tutor is inactive, the user is a
 * guest, not enrolled, or the data is unavailable (so callers can branch on
 * "is this an enrolled learner?").
 *
 * @param int $course_id Course post ID.
 * @param int $user_id   User ID (0 = current user).
 * @return int|null Percentage 0-100, or null.
 */
function bia_learn_course_progress( $course_id, $user_id = 0 ) {
	$tutils = bia_learn_tutor_utils();
	if ( ! $tutils ) {
		return null;
	}

	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return null;
	}

	if ( method_exists( $tutils, 'is_enrolled' ) && ! $tutils->is_enrolled( $course_id, $user_id ) ) {
		return null;
	}

	if ( method_exists( $tutils, 'get_course_completed_percent' ) ) {
		return (int) $tutils->get_course_completed_percent( $course_id, $user_id );
	}

	return null;
}

/**
 * Frequently-asked questions. Single source of truth shared by the FAQ page
 * template and the FAQPage structured data. Override via the
 * `bia_learn_faq_items` filter. Each item: array( 'q' => ..., 'a' => ... ).
 *
 * @return array<int, array{q:string,a:string}>
 */
function bia_learn_get_faqs() {
	$defaults = array(
		array(
			'q' => __( 'การสมัครเรียนมีค่าใช้จ่ายหรือไม่?', 'bia-learn' ),
			'a' => __( 'คอร์สส่วนใหญ่บนแพลตฟอร์มเปิดให้เรียนฟรี เพียงสมัครสมาชิกก็เริ่มเรียนได้ทันที บางคอร์สอาจมีค่าใช้จ่ายซึ่งจะระบุไว้อย่างชัดเจนในหน้าคอร์ส', 'bia-learn' ),
		),
		array(
			'q' => __( 'ต้องเรียนตามเวลาที่กำหนดไหม?', 'bia-learn' ),
			'a' => __( 'ไม่จำเป็น คุณสามารถเรียนได้ทุกที่ทุกเวลาตามจังหวะของตัวเอง ระบบจะบันทึกความคืบหน้าให้อัตโนมัติ', 'bia-learn' ),
		),
		array(
			'q' => __( 'เรียนจบแล้วได้รับเกียรติบัตรหรือไม่?', 'bia-learn' ),
			'a' => __( 'คอร์สที่เปิดให้มีเกียรติบัตร เมื่อคุณเรียนและทำแบบทดสอบครบตามเงื่อนไข ระบบจะออกเกียรติบัตรให้ดาวน์โหลดได้จากแดชบอร์ด “เกียรติบัตรของฉัน”', 'bia-learn' ),
		),
		array(
			'q' => __( 'ลืมรหัสผ่านต้องทำอย่างไร?', 'bia-learn' ),
			'a' => __( 'คลิก “เข้าสู่ระบบ” แล้วเลือก “ลืมรหัสผ่าน” กรอกอีเมลที่ใช้สมัคร ระบบจะส่งลิงก์สำหรับตั้งรหัสผ่านใหม่ให้ทางอีเมล', 'bia-learn' ),
		),
		array(
			'q' => __( 'ดูประวัติการเรียนได้ที่ไหน?', 'bia-learn' ),
			'a' => __( 'เข้าสู่ระบบแล้วไปที่แดชบอร์ดของฉัน จะเห็นคอร์สที่ลงทะเบียน ความคืบหน้า และเกียรติบัตรทั้งหมด', 'bia-learn' ),
		),
	);

	return (array) apply_filters( 'bia_learn_faq_items', $defaults );
}
