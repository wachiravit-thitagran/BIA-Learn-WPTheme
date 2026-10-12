<?php
/**
 * Regression tests for course progress placement in Tutor LMS overrides.
 *
 * @package BIA_Learn
 */

use PHPUnit\Framework\TestCase;

class CourseProgressPlacementTest extends TestCase {

	/**
	 * Course details must not repeat the BIA progress widget above its sections.
	 */
	public function test_course_outline_has_no_duplicate_progress_widget() {
		$curriculum = file_get_contents( dirname( __DIR__ ) . '/tutor/single/course/course-topics.php' );

		$this->assertStringNotContainsString( 'render_segmented_progress_bar(', $curriculum );
		$this->assertStringNotContainsString( 'tutor-course-overall-progress', $curriculum );
		$this->assertStringContainsString( 'get_section_progress(', $curriculum );
	}

	/**
	 * Preserve the BIA widget in the course enrollment/action card.
	 */
	public function test_course_enrollment_box_keeps_bia_progress_widget() {
		$entry_box = file_get_contents( dirname( __DIR__ ) . '/tutor/single/course/course-entry-box.php' );

		$this->assertStringContainsString( 'render_segmented_progress_bar(', $entry_box );
	}
}
