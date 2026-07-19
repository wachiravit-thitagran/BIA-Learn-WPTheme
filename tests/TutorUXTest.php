<?php
/**
 * Test BIA_Learn_Tutor_UX
 *
 * @package BIA_Learn
 */

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class TutorUXTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Load the class under test
		require_once dirname( __DIR__ ) . '/inc/tutor-ux.php';

		// Reset per-request memoization between tests.
		if ( property_exists( 'BIA_Learn_Tutor_UX', 'analytics_table_exists' ) ) {
			BIA_Learn_Tutor_UX::$analytics_table_exists = null;
		}
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Standard $wpdb mock: prepare() interpolates like vsprintf.
	 */
	private function mock_wpdb() {
		global $wpdb;
		$wpdb = Mockery::mock( '\WPDB' );
		$wpdb->prefix   = 'wp_';
		$wpdb->comments = 'wp_comments';
		$wpdb->posts    = 'wp_posts';
		$wpdb->shouldReceive( 'prepare' )->andReturnUsing( function( $query, ...$args ) {
			return vsprintf( str_replace( '%s', "'%s'", $query ), $args );
		});
		$wpdb->shouldReceive( 'esc_like' )->andReturnUsing( function( $s ) {
			return $s;
		});
		return $wpdb;
	}

	public function test_get_course_curriculum_status_without_tutor_utils() {
		if ( function_exists( 'tutor_utils' ) ) {
			$this->markTestSkipped( 'tutor_utils already defined by an earlier test.' );
		}
		$result = BIA_Learn_Tutor_UX::get_course_curriculum_status( 123, 1 );
		$this->assertEquals( array(), $result );
	}

	public function test_get_course_curriculum_status_with_mixed_content() {
		$wpdb = $this->mock_wpdb();

		Monkey\Functions\when( 'wp_cache_get' )->justReturn( false );
		Monkey\Functions\when( 'wp_cache_set' )->justReturn( true );

		// Map quiz IDs to their submitted attempt rows (result, earned_marks, total_marks).
		$wpdb->shouldReceive( 'get_results' )->andReturnUsing( function( $query ) {
			if ( strpos( $query, 'quiz_id = 302' ) !== false ) {
				return array( (object) array( 'result' => 'pass', 'earned_marks' => 9, 'total_marks' => 10 ) );
			}
			if ( strpos( $query, 'quiz_id = 303' ) !== false ) {
				return array( (object) array( 'result' => 'fail', 'earned_marks' => 2, 'total_marks' => 10 ) );
			}
			if ( strpos( $query, 'quiz_id = 304' ) !== false ) {
				return array( (object) array( 'result' => 'pending', 'earned_marks' => 0, 'total_marks' => 10 ) );
			}
			if ( strpos( $query, 'quiz_id = 305' ) !== false ) {
				// Legacy attempt: `result` column not populated — pass derived from marks (90% >= 80%).
				return array( (object) array( 'result' => null, 'earned_marks' => 9, 'total_marks' => 10 ) );
			}
			if ( strpos( $query, 'quiz_id = 306' ) !== false ) {
				// Legacy attempt below passing grade (20% < 80%).
				return array( (object) array( 'result' => null, 'earned_marks' => 2, 'total_marks' => 10 ) );
			}
			return array(); // Unattempted
		});

		// Mock tutor_utils()
		$tutor_utils_mock = Mockery::mock();
		Monkey\Functions\when( 'tutor_utils' )->justReturn( $tutor_utils_mock );

		// 1 Topic with 12 items. The topics loop must iterate the ->posts array
		// directly — NOT via the_post()/wp_reset_postdata(), which corrupts the
		// global $post of enclosing custom loops. the_post() is intentionally
		// not mocked so any call to it fails the test.
		$topics = Mockery::mock();
		$topics->posts = array( (object) array( 'ID' => 1 ) );

		$tutor_utils_mock->shouldReceive( 'get_topics' )->with( 123 )->andReturn( $topics );

		$items = array(
			(object) array( 'ID' => 101, 'post_type' => 'lesson' ), // Unattempted lesson
			(object) array( 'ID' => 102, 'post_type' => 'lesson' ), // Completed lesson
			(object) array( 'ID' => 301, 'post_type' => 'tutor_quiz' ), // Unattempted quiz
			(object) array( 'ID' => 302, 'post_type' => 'tutor_quiz' ), // Passed quiz
			(object) array( 'ID' => 303, 'post_type' => 'tutor_quiz' ), // Failed quiz
			(object) array( 'ID' => 304, 'post_type' => 'tutor_quiz' ), // Submitted, awaiting manual review
			(object) array( 'ID' => 305, 'post_type' => 'tutor_quiz' ), // Legacy attempt, passed by marks
			(object) array( 'ID' => 306, 'post_type' => 'tutor_quiz' ), // Legacy attempt, failed by marks
			(object) array( 'ID' => 401, 'post_type' => 'tutor_assignments' ), // Unattempted assignment
			(object) array( 'ID' => 402, 'post_type' => 'tutor_assignments' ), // Passed assignment
			(object) array( 'ID' => 403, 'post_type' => 'tutor_assignments' ), // Failed assignment
			(object) array( 'ID' => 404, 'post_type' => 'tutor_assignments' ), // Submitted, not graded yet
		);
		$tutor_utils_mock->shouldReceive( 'get_course_contents_by_topic' )->with( 1, -1 )->andReturn( $items );

		// Mock lesson completion
		$tutor_utils_mock->shouldReceive( 'is_completed_lesson' )->with( 101, 1 )->andReturn( false );
		$tutor_utils_mock->shouldReceive( 'is_completed_lesson' )->with( 102, 1 )->andReturn( true );

		// Quiz passing grade — only decisive for legacy attempts without a `result` value
		$tutor_utils_mock->shouldReceive( 'get_quiz_option' )->andReturn( 80 );

		// Mock assignment submissions
		$tutor_utils_mock->shouldReceive( 'is_assignment_submitted' )->with( 401, 1 )->andReturn( array() ); // Unattempted
		$tutor_utils_mock->shouldReceive( 'is_assignment_submitted' )->with( 402, 1 )->andReturn( array( (object) array( 'comment_ID' => 902 ) ) );
		$tutor_utils_mock->shouldReceive( 'is_assignment_submitted' )->with( 403, 1 )->andReturn( array( (object) array( 'comment_ID' => 903 ) ) );
		$tutor_utils_mock->shouldReceive( 'is_assignment_submitted' )->with( 404, 1 )->andReturn( array( (object) array( 'comment_ID' => 904 ) ) );

		// Mock assignment pass marks
		$tutor_utils_mock->shouldReceive( 'get_assignment_option' )->andReturn( 50 );

		// Mock get_comment_meta for assignment scores
		Monkey\Functions\when( 'get_comment_meta' )->alias( function( $comment_id, $key, $single ) {
			if ( $comment_id === 902 && $key === 'assignment_mark' ) {
				return 80; // Passed
			}
			if ( $comment_id === 903 && $key === 'assignment_mark' ) {
				return 40; // Below pass mark
			}
			return ''; // Not graded yet
		});

		$result = BIA_Learn_Tutor_UX::get_course_curriculum_status( 123, 1 );

		$expected = array(
			array( 'title' => 'Title', 'status' => 'unattempted' ),  // 101 lesson
			array( 'title' => 'Title', 'status' => 'completed' ),    // 102 lesson
			array( 'title' => 'Title', 'status' => 'unattempted' ),  // 301 quiz
			array( 'title' => 'Title', 'status' => 'completed' ),    // 302 quiz passed
			array( 'title' => 'Title', 'status' => 'quiz_failed' ),  // 303 quiz failed → red
			array( 'title' => 'Title', 'status' => 'quiz_pending' ), // 304 quiz awaiting review → yellow
			array( 'title' => 'Title', 'status' => 'completed' ),    // 305 legacy quiz passed by marks
			array( 'title' => 'Title', 'status' => 'quiz_failed' ),  // 306 legacy quiz failed by marks
			array( 'title' => 'Title', 'status' => 'unattempted' ),  // 401 assignment
			array( 'title' => 'Title', 'status' => 'completed' ),    // 402 assignment passed
			array( 'title' => 'Title', 'status' => 'quiz_failed' ),  // 403 assignment below pass mark → red
			array( 'title' => 'Title', 'status' => 'quiz_pending' ), // 404 assignment not graded → yellow
		);

		$this->assertEquals( $expected, $result );
	}

	/**
	 * Archive pages render many cards per request: a cached status list must be
	 * returned without touching Tutor at all.
	 */
	public function test_curriculum_status_returns_cached_array_without_querying() {
		$canned = array( array( 'title' => 'Cached', 'status' => 'completed' ) );

		Monkey\Functions\when( 'wp_cache_get' )->justReturn( $canned );
		Monkey\Functions\when( 'wp_cache_set' )->justReturn( true );

		$tutor_utils_mock = Mockery::mock();
		$tutor_utils_mock->shouldReceive( 'get_topics' )->never();
		Monkey\Functions\when( 'tutor_utils' )->justReturn( $tutor_utils_mock );

		$result = BIA_Learn_Tutor_UX::get_course_curriculum_status( 55, 9 );

		$this->assertSame( $canned, $result );
	}

	/**
	 * The course card already knows the learner's percent — passing it in must
	 * skip the duplicate get_course_completed_percent() query.
	 */
	public function test_render_progress_bar_uses_supplied_percent_without_requery() {
		$segments = array(
			array( 'title' => 'บทที่ 1', 'status' => 'completed' ),
			array( 'title' => 'แบบทดสอบ', 'status' => 'quiz_failed' ),
		);
		Monkey\Functions\when( 'wp_cache_get' )->justReturn( $segments );
		Monkey\Functions\when( 'wp_cache_set' )->justReturn( true );

		// Strict mock: any call to get_course_completed_percent() fails the test.
		$tutor_utils_mock = Mockery::mock();
		Monkey\Functions\when( 'tutor_utils' )->justReturn( $tutor_utils_mock );
		Monkey\Functions\when( 'esc_html_e' )->echoArg( 1 );

		ob_start();
		BIA_Learn_Tutor_UX::render_segmented_progress_bar( 55, 9, 42 );
		$html = ob_get_clean();

		$this->assertStringContainsString( '42%', $html );
		$this->assertStringContainsString( 'bg-success', $html );
		$this->assertStringContainsString( 'bg-danger', $html );
	}

	/**
	 * The analytics-table existence check must run at most once per request,
	 * not once per card.
	 */
	public function test_get_last_viewed_lesson_url_memoizes_table_check() {
		global $wpdb;
		$wpdb = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'prepare' )->andReturnUsing( function( $query, ...$args ) {
			return vsprintf( str_replace( '%s', "'%s'", $query ), $args );
		});
		$wpdb->shouldReceive( 'esc_like' )->andReturnUsing( function( $s ) {
			return $s;
		});

		$show_tables_calls = 0;
		$wpdb->shouldReceive( 'get_var' )->andReturnUsing( function( $query ) use ( &$show_tables_calls ) {
			if ( strpos( $query, 'SHOW TABLES' ) !== false ) {
				$show_tables_calls++;
				return 'wp_tutorlms_analytics_events';
			}
			return 55; // last viewed lesson id
		});

		Monkey\Functions\when( 'get_permalink' )->justReturn( 'https://example.com/lesson/55' );

		$url_a = BIA_Learn_Tutor_UX::get_last_viewed_lesson_url( 5, 9 );
		$url_b = BIA_Learn_Tutor_UX::get_last_viewed_lesson_url( 6, 9 );

		$this->assertSame( 'https://example.com/lesson/55', $url_a );
		$this->assertSame( $url_a, $url_b );
		$this->assertSame( 1, $show_tables_calls, 'SHOW TABLES must be memoized per request' );
	}

	/**
	 * Section progress must count passed quizzes/assignments — not only lessons —
	 * and expose the per-content statuses so templates don't re-query.
	 */
	public function test_get_section_progress_counts_passed_quiz_and_exposes_statuses() {
		$wpdb = $this->mock_wpdb();

		$wpdb->shouldReceive( 'get_results' )->andReturnUsing( function( $query ) {
			if ( strpos( $query, 'quiz_id = 301' ) !== false ) {
				return array( (object) array( 'result' => 'pass', 'earned_marks' => 10, 'total_marks' => 10 ) );
			}
			return array();
		});

		$tutor_utils_mock = Mockery::mock();
		Monkey\Functions\when( 'tutor_utils' )->justReturn( $tutor_utils_mock );

		$items = array(
			(object) array( 'ID' => 201, 'post_type' => 'lesson' ),
			(object) array( 'ID' => 301, 'post_type' => 'tutor_quiz' ),
		);
		$tutor_utils_mock->shouldReceive( 'get_course_contents_by_topic' )->with( 7, -1 )->andReturn( $items );
		$tutor_utils_mock->shouldReceive( 'is_completed_lesson' )->with( 201, 9 )->andReturn( true );
		$tutor_utils_mock->shouldReceive( 'get_quiz_option' )->andReturn( 80 );

		$progress = BIA_Learn_Tutor_UX::get_section_progress( 7, 9 );

		$this->assertSame( 2, $progress['completed'] );
		$this->assertSame( 2, $progress['total'] );
		$this->assertSame( 100.0, (float) $progress['percent'] );
		$this->assertSame(
			array( 201 => 'completed', 301 => 'completed' ),
			$progress['statuses']
		);
	}
}
