<?php
/**
 * BIA Quiz Experience: asset delivery and Tutor LMS ownership contracts.
 *
 * @package BIA_Learn
 */

use PHPUnit\Framework\TestCase;

class QuizExperienceTest extends TestCase {

	public function test_quiz_experience_assets_exist_and_are_scoped_to_tutor() {
		$root = dirname( __DIR__ );
		$css  = $root . '/assets/css/bia-quiz-ux.css';
		$js   = $root . '/assets/js/bia-quiz-ux.js';

		$this->assertFileExists( $css );
		$this->assertFileExists( $js );

		$styles = (string) file_get_contents( $css );
		$script = (string) file_get_contents( $js );
		$this->assertStringContainsString( '.tutor-quiz-intro', $styles );
		$this->assertStringContainsString( '.tutor-quiz-submission', $styles );
		$this->assertStringContainsString( '.tutor-quiz-summary-page', $styles );
		$this->assertStringContainsString( '.bia-quiz-review-dialog', $styles );
		$this->assertStringContainsString( 'TutorCore.form', $script );
		$this->assertStringContainsString( 'tutor-quiz-submission', $script );
	}

	public function test_quiz_assets_are_enqueued_with_cache_busting() {
		$enqueue = (string) file_get_contents( dirname( __DIR__ ) . '/inc/enqueue.php' );

		$this->assertStringContainsString( "'bia-quiz-ux-style'", $enqueue );
		$this->assertStringContainsString( "'bia-quiz-ux-script'", $enqueue );
		$this->assertStringContainsString( "bia_learn_asset_version( 'assets/css/bia-quiz-ux.css' )", $enqueue );
		$this->assertStringContainsString( "bia_learn_asset_version( 'assets/js/bia-quiz-ux.js' )", $enqueue );
	}

	public function test_tutor_owns_quiz_submission_timer_scoring_and_attempts() {
		$root = dirname( __DIR__ );
		foreach ( array( 'attempt.php', 'progress-bar.php', 'modals/result.php' ) as $file ) {
			$this->assertFileDoesNotExist( $root . '/tutor/learning-area/quiz/' . $file );
		}

		$script = (string) file_get_contents( $root . '/assets/js/bia-quiz-ux.js' );
		$this->assertStringNotContainsString( 'fetch(', $script, 'Quiz UX must not send quiz submissions itself.' );
		$this->assertStringNotContainsString( 'XMLHttpRequest', $script );
		$this->assertStringNotContainsString( 'setInterval(', $script, 'Tutor LMS alone controls the timer.' );
		$this->assertStringContainsString( 'tutor-quiz-time-expired', $script, 'Review must close on Tutor automatic timeout.' );
		$this->assertStringContainsString( 'tutor-quiz-attempt-completed', $script, 'Review must close after Tutor submission.' );
	}
}
