<?php
/**
 * Test BIA_Learn_Tutor_Gamification
 *
 * Tutor LMS 4.x records lesson completion as usermeta
 * `_tutor_completed_lesson_id_{id}` (value = tutor_time() unix timestamp) — it
 * never writes `tutor_lesson_completed` comments, so the streak must be
 * derived from usermeta.
 *
 * @package BIA_Learn
 */

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class TutorGamificationTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		require_once dirname( __DIR__ ) . '/inc/tutor-gamification.php';
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_get_user_streak_reads_lesson_completion_usermeta() {
		global $wpdb;
		$wpdb = Mockery::mock( '\WPDB' );
		$wpdb->prefix   = 'wp_';
		$wpdb->usermeta = 'wp_usermeta';
		$wpdb->comments = 'wp_comments';
		$wpdb->shouldReceive( 'prepare' )->andReturnUsing( function( $query, ...$args ) {
			return vsprintf( str_replace( '%s', "'%s'", $query ), $args );
		});
		$wpdb->shouldReceive( 'esc_like' )->andReturnUsing( function( $s ) {
			return $s;
		});

		$captured = '';
		$wpdb->shouldReceive( 'get_col' )->andReturnUsing( function( $query ) use ( &$captured ) {
			$captured = $query;
			// Learner completed lessons today, yesterday, and 3 days ago.
			return array( '2026-07-16', '2026-07-15', '2026-07-13' );
		});

		Monkey\Functions\when( 'current_time' )->justReturn( '2026-07-16' );

		$streak = BIA_Learn_Tutor_Gamification::get_user_streak( 9 );

		// Must read Tutor's real storage (usermeta), not nonexistent comments.
		$this->assertStringContainsString( 'usermeta', $captured );
		$this->assertStringContainsString( '_tutor_completed_lesson_id_', $captured );
		$this->assertStringNotContainsString( 'tutor_lesson_completed', $captured );

		$this->assertSame( 2, $streak['current_streak'] );
		$this->assertTrue( $streak['active_today'] );
		$this->assertSame( 2, $streak['longest_streak'] );
	}
}
