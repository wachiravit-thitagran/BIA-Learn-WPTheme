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
	$paths = array(
		'calendar'  => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
		'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
		'book'      => '<path d="M4 5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 0-2 2z"/><path d="M18 3v16"/>',
		'play'      => '<circle cx="12" cy="12" r="9"/><path d="M10 9l5 3-5 3z" fill="currentColor" stroke="none"/>',
		'arrow'     => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'arrow-ul'  => '<path d="M7 17L17 7M9 7h8v8"/>',
		'search'    => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
		'menu'      => '<path d="M4 6h16M4 12h16M4 18h16"/>',
		'close'     => '<path d="M6 6l12 12M18 6L6 18"/>',
		'check'     => '<path d="M20 6 9 17l-5-5"/>',
		'star'      => '<path d="m12 2 3 6.5 7 .9-5 4.8 1.3 7L12 18l-6.6 3.2L6.7 14l-5-4.8 7-.9z" fill="currentColor" stroke="none"/>',
		'users'     => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.3 3-5 6.5-5s6.5 1.7 6.5 5"/><path d="M16 5.2A3.5 3.5 0 0 1 16 12M22 20c0-2.4-1.6-3.9-4-4.6"/>',
		'cert'      => '<circle cx="12" cy="9" r="5"/><path d="M9 13.5 8 22l4-2 4 2-1-8.5"/>',
		'chart'     => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
		'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'phone'     => '<path d="M5 4h4l2 5-3 2a14 14 0 0 0 6 6l2-3 5 2v4a2 2 0 0 1-2 2A17 17 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
		'pin'       => '<path d="M12 22s7-6.5 7-12a7 7 0 1 0-14 0c0 5.5 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/>',
		'quote'     => '<path d="M7 7H4v6h3l-2 4h3l2-4V7zm10 0h-3v6h3l-2 4h3l2-4V7z" fill="currentColor" stroke="none"/>',
		'lotus'     => '<path d="M12 4c1.5 2 1.5 4 0 6-1.5-2-1.5-4 0-6z"/><path d="M12 10c3-1 5 0 6 2-2 2-5 2-6 0zm0 0c-3-1-5 0-6 2 2 2 5 2 6 0z"/><path d="M5 13c-1 2 0 4 2 5 3 1 7 1 10 0 2-1 3-3 2-5"/>',
		'chevron'   => '<path d="m6 9 6 6 6-6"/>',
		'facebook'  => '<path d="M14 9h3V6h-3c-2 0-3 1-3 3v2H8v3h3v7h3v-7h2.5l.5-3H14v-2c0-.6.4-1 1-1z" fill="currentColor" stroke="none"/>',
		'youtube'   => '<rect x="2" y="5" width="20" height="14" rx="4"/><path d="m10 9 5 3-5 3z" fill="currentColor" stroke="none"/>',
		'line'      => '<path d="M12 4c5 0 9 3.2 9 7.2 0 4.4-5 8-9 8-1 0-1.6.3-3 1-1 .4-1.2.1-1-1 .2-1-.4-1.2-1.6-2C3.4 16 3 13.8 3 11.2 3 7.2 7 4 12 4z"/>',
	);

	$path = isset( $paths[ $name ] ) ? $paths[ $name ] : '';
	$fill = in_array( $name, array( 'star', 'play', 'facebook', 'youtube', 'quote' ), true ) ? 'currentColor' : 'none';

	return sprintf(
		'<svg class="%1$s" viewBox="0 0 24 24" fill="%2$s" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $classes ),
		esc_attr( $fill ),
		$path // phpcs:ignore -- static, trusted SVG path data.
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
