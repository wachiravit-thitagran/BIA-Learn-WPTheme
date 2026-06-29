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
			return vsprintf( str_replace( '%d', '%d', $query ), $args ); // Simple mock just returning a string
		});
		
		// Map quiz IDs to whether they are passed in DB
		$wpdb->shouldReceive( 'get_var' )->andReturnUsing( function( $query ) {
			if ( strpos( $query, 'quiz_id = 302' ) !== false ) {
				return 1; // Passed quiz
			}
			return null; // Failed quiz
		});

		// Mock tutor_utils()
		$tutor_utils_mock = Mockery::mock();
		Monkey\Functions\when( 'tutor_utils' )->justReturn( $tutor_utils_mock );
		// get_the_ID() is defined in bootstrap.php and returns 1
		$topic_id = 1; 

		// 1 Topic with 8 items
		$topics = Mockery::mock();
		$topics->shouldReceive( 'have_posts' )->andReturn( true, true, false );
		$topics->shouldReceive( 'the_post' )->andReturn();
		
		$tutor_utils_mock->shouldReceive( 'get_topics' )->with( 123 )->andReturn( $topics );

		$items = array(
			(object) array( 'ID' => 101, 'post_type' => 'lesson' ), // Unattempted lesson
			(object) array( 'ID' => 102, 'post_type' => 'lesson' ), // Completed lesson
			(object) array( 'ID' => 301, 'post_type' => 'tutor_quiz' ), // Unattempted quiz
			(object) array( 'ID' => 302, 'post_type' => 'tutor_quiz' ), // Passed quiz
			(object) array( 'ID' => 303, 'post_type' => 'tutor_quiz' ), // Failed quiz
			(object) array( 'ID' => 401, 'post_type' => 'tutor_assignments' ), // Unattempted assignment
			(object) array( 'ID' => 402, 'post_type' => 'tutor_assignments' ), // Passed assignment
			(object) array( 'ID' => 403, 'post_type' => 'tutor_assignments' ), // Failed assignment
		);
		$tutor_utils_mock->shouldReceive( 'get_course_contents_by_topic' )->with( $topic_id, -1 )->andReturn( $items );

		// Mock lesson completion
		$tutor_utils_mock->shouldReceive( 'is_completed_lesson' )->with( 101, 1 )->andReturn( false );
		$tutor_utils_mock->shouldReceive( 'is_completed_lesson' )->with( 102, 1 )->andReturn( true );

		// Mock quiz attempts
		$tutor_utils_mock->shouldReceive( 'has_attempted_quiz' )->with( 1, 301 )->andReturn( false ); // Unattempted
		$tutor_utils_mock->shouldReceive( 'has_attempted_quiz' )->with( 1, 302 )->andReturn( true ); // Attempted & Passed (via $wpdb mock)
		$tutor_utils_mock->shouldReceive( 'has_attempted_quiz' )->with( 1, 303 )->andReturn( true ); // Attempted & Failed (via $wpdb mock)

		// Mock assignment submissions
		$tutor_utils_mock->shouldReceive( 'is_assignment_submitted' )->with( 401, 1 )->andReturn( array() ); // Unattempted
		
		$submission_passed = (object) array( 'comment_ID' => 902 );
		$tutor_utils_mock->shouldReceive( 'is_assignment_submitted' )->with( 402, 1 )->andReturn( array( $submission_passed ) );
		
		$submission_failed = (object) array( 'comment_ID' => 903 );
		$tutor_utils_mock->shouldReceive( 'is_assignment_submitted' )->with( 403, 1 )->andReturn( array( $submission_failed ) );

		// Mock assignment pass marks
		$tutor_utils_mock->shouldReceive( 'get_assignment_option' )->with( 402, 'pass_mark' )->andReturn( 50 );
		$tutor_utils_mock->shouldReceive( 'get_assignment_option' )->with( 403, 'pass_mark' )->andReturn( 50 );

		// Mock get_comment_meta for assignment scores
		Monkey\Functions\when( 'get_comment_meta' )->alias( function( $comment_id, $key, $single ) {
			if ( $comment_id === 902 && $key === 'assignment_mark' ) {
				return 80; // Passed
			}
			if ( $comment_id === 903 && $key === 'assignment_mark' ) {
				return 40; // Failed
			}
			return null;
		});

		$result = BIA_Learn_Tutor_UX::get_course_curriculum_status( 123, 1 );

		$expected = array(
			'unattempted',   // Lesson 101
			'completed',     // Lesson 102
			'unattempted',   // Quiz 301
			'completed',     // Quiz 302
			'quiz_pending',  // Quiz 303
			'unattempted',   // Assignment 401
			'completed',     // Assignment 402
			'quiz_pending',  // Assignment 403
		);

		$this->assertEquals( $expected, $result );
	}
}
