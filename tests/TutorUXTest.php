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
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_get_course_curriculum_status_without_tutor_utils() {
		$result = BIA_Learn_Tutor_UX::get_course_curriculum_status( 123, 1 );
		$this->assertEquals( array(), $result );
	}

	public function test_get_course_curriculum_status_with_mixed_content() {
		// Mock $wpdb
		global $wpdb;
		$wpdb = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'prepare' )->andReturnUsing( function( $query, ...$args ) {
			return vsprintf( $query, $args ); // Simple mock just returning a string
		});

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
		// get_the_ID() is defined in bootstrap.php and returns 1
		$topic_id = 1;

		// 1 Topic with 12 items
		$topics = Mockery::mock();
		$topics->shouldReceive( 'have_posts' )->andReturn( true, false );
		$topics->shouldReceive( 'the_post' )->andReturn();

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
		$tutor_utils_mock->shouldReceive( 'get_course_contents_by_topic' )->with( $topic_id, -1 )->andReturn( $items );

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
}
