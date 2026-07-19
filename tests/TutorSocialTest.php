<?php
/**
 * Test BIA_Learn_Tutor_Social
 *
 * Tutor LMS 4.x facts (verified against plugin source):
 * - Course completions: comments with comment_type 'course_completed'
 *   (agent 'TutorLMSPlugin'). There is no 'tutor_course_completed' type.
 * - Enrollments: posts with post_type 'tutor_enrolled' — not comments.
 * - Reviews: comments with comment_type 'tutor_course_rating' +
 *   commentmeta 'tutor_rating'. There is no {prefix}tutor_reviews table.
 *
 * @package BIA_Learn
 */

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class TutorSocialTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		require_once dirname( __DIR__ ) . '/inc/tutor-social.php';
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	private function mock_wpdb() {
		global $wpdb;
		$wpdb = Mockery::mock( '\WPDB' );
		$wpdb->prefix      = 'wp_';
		$wpdb->comments    = 'wp_comments';
		$wpdb->commentmeta = 'wp_commentmeta';
		$wpdb->posts       = 'wp_posts';
		$wpdb->users       = 'wp_users';
		$wpdb->shouldReceive( 'prepare' )->andReturnUsing( function( $query, ...$args ) {
			return vsprintf( str_replace( '%s', "'%s'", $query ), $args );
		});
		return $wpdb;
	}

	public function test_recent_activities_reads_tutor4_completions_and_enrollments() {
		$wpdb = $this->mock_wpdb();

		$captured = '';
		$wpdb->shouldReceive( 'get_results' )->andReturnUsing( function( $query ) use ( &$captured ) {
			$captured = $query;
			return array(
				(object) array(
					'course_id' => 5,
					'type'      => 'completed',
					'date'      => '2026-07-16 09:00:00',
					'author'    => 'Somchai Jaidee',
				),
			);
		});

		Monkey\Functions\when( 'human_time_diff' )->justReturn( '5 นาที' );
		Monkey\Functions\when( 'current_time' )->justReturn( 1784000000 );

		$activities = BIA_Learn_Tutor_Social::get_recent_activities( 5 );

		// Real Tutor 4.x sources…
		$this->assertStringContainsString( "'course_completed'", $captured );
		$this->assertStringContainsString( 'tutor_enrolled', $captured );
		// …not the nonexistent comment types.
		$this->assertStringNotContainsString( 'tutor_course_completed', $captured );

		$this->assertCount( 1, $activities );
		$this->assertStringContainsString( 'Som***', $activities[0]['message'] );
		$this->assertStringNotContainsString( 'Jaidee', $activities[0]['message'] );
		$this->assertStringContainsString( 'เพิ่งเรียนจบ', $activities[0]['message'] );
	}

	public function test_featured_review_reads_tutor_rating_comments() {
		$wpdb = $this->mock_wpdb();

		$captured = '';
		$wpdb->shouldReceive( 'get_row' )->andReturnUsing( function( $query ) use ( &$captured ) {
			$captured = $query;
			return (object) array(
				'comment_ID'      => 11,
				'comment_content' => str_repeat( 'คอร์สนี้ดีมาก ', 10 ),
				'comment_author'  => 'Araya',
				'rating'          => '5',
			);
		});

		$review = BIA_Learn_Tutor_Social::get_featured_review( 5 );

		$this->assertStringContainsString( 'commentmeta', $captured );
		$this->assertStringContainsString( 'tutor_course_rating', $captured );
		$this->assertStringContainsString( 'tutor_rating', $captured );
		$this->assertStringNotContainsString( 'tutor_reviews', $captured );
		$this->assertNotNull( $review );
		$this->assertSame( 'Araya', $review->comment_author );
	}
}
