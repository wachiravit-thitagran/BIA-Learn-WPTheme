<?php
use PHPUnit\Framework\TestCase;
require_once dirname( __DIR__ ) . '/bin/review-content-lib.php';

final class ReviewContentTest extends TestCase {
	public function test_cleanup_removes_only_the_recognized_administrator_paragraph() {
		$post = (object) array( 'post_type' => 'lesson', 'post_title' => 'Lesson', 'post_content' => '<h2>ก่อนรับชม</h2><p>เนื้อหาจริง</p><p><em>หมายเหตุสำหรับผู้ดูแล: ตรวจสอบหรือเพิ่มวิดีโอในส่วน Video ของ Tutor LMS</em></p><p>หลังรับชม</p>' );
		$changes = bia_review_content_changes( $post );
		self::assertSame( '<h2>ก่อนรับชม</h2><p>เนื้อหาจริง</p><p>หลังรับชม</p>', $changes['post_content'] );
	}

	public function test_authored_content_is_not_rewritten() {
		$post = (object) array( 'post_type' => 'lesson', 'post_title' => 'Lesson', 'post_content' => '<p>หมายเหตุสำหรับผู้ดูแล: เป็นข้อความอ้างอิงในบทเรียน</p>' );
		self::assertSame( array(), bia_review_content_changes( $post ) );
	}

	public function test_duplicate_course_overview_heading_keeps_the_course_description() {
		$post = (object) array( 'post_type' => 'courses', 'post_title' => 'Course', 'post_content' => '<section class="bia-course-overview"><h2>เกี่ยวกับคอร์ส</h2><p>เนื้อหาจริง</p></section>' );
		self::assertSame( '<section class="bia-course-overview"><p>เนื้อหาจริง</p></section>', bia_review_content_changes( $post )['post_content'] );
	}

	public function test_placeholder_detection_does_not_discard_authored_benefits() {
		self::assertTrue( bia_review_is_placeholder( 'สิ่งที่จะได้เรียนรู้....' ) );
		self::assertFalse( bia_review_is_placeholder( 'สิ่งที่จะได้เรียนรู้: การฝึกสติในชีวิตประจำวัน' ) );
	}
}
