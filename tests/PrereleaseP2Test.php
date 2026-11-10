<?php
use PHPUnit\Framework\TestCase;
final class PrereleaseP2Test extends TestCase {
 public function test_quiz_score_and_detail_contracts() {
  $s=file_get_contents(dirname(__DIR__).'/tutor/dashboard/my-quiz-attempts.php');
  self::assertStringNotContainsString('$correct = $earned_marks',$s);
  self::assertStringContainsString("'attempt_id'",$s);
  self::assertStringContainsString('attempts-details.php',$s);
  self::assertStringContainsString('earned_marks',$s);
 }
 public function test_course_accordion_exposes_native_control() {
  $s=file_get_contents(dirname(__DIR__).'/tutor/single/course/course-topics.php');
  self::assertMatchesRegularExpression('/<button[^>]+aria-expanded=/s',$s);
  self::assertStringContainsString('aria-controls=',$s);
 }
 public function test_continue_learning_is_bounded_before_progress_queries() {
  $s=file_get_contents(dirname(__DIR__).'/tutor/dashboard/continue-learning.php');
  self::assertStringNotContainsString('get_enrolled_courses_by_user',$s);
  self::assertStringContainsString('get_continue_candidates',$s);
 }
}
