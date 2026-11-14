<?php

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

final class ReportRemediationTest extends TestCase {
	public function test_raw_tutor_settings_labels_use_the_locale_fallback_without_changing_tab_identity() {
		Functions\when( 'is_admin' )->justReturn( false );
		Functions\when( 'determine_locale' )->justReturn( 'th' );
		$tabs = bia_learn_translate_settings_nav( array( 'billing' => array( 'id' => 'billing', 'label' => 'Billing Address', 'role' => false ) ) );
		self::assertSame( 'billing', $tabs['billing']['id'] );
		self::assertSame( 'ที่อยู่สำหรับใบเสร็จ', $tabs['billing']['label'] );
		self::assertFalse( $tabs['billing']['role'] );
	}
	public function test_only_dedicated_certificate_aliases_redirect() {
		self::assertSame( '/dashboard/my-certificates/', bia_learn_certificate_alias_target( ' [certpsu_my_certificates] ', '/dashboard/my-certificates/', '/my-certificate/' ) );
		self::assertSame( '', bia_learn_certificate_alias_target( '<p>Information</p>[certpsu_my_certificates]', '/dashboard/my-certificates/', '/information/' ) );
		self::assertSame( '', bia_learn_certificate_alias_target( '[certpsu_my_certificates]', '/dashboard/my-certificates/', '/dashboard/my-certificates/' ) );
	}

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_security_headers_preserve_existing_policy() {
		$headers = bia_learn_public_headers( array( 'Content-Security-Policy' => 'existing-policy' ) );
		self::assertSame( 'existing-policy', $headers['Content-Security-Policy'] );
		self::assertSame( 'nosniff', $headers['X-Content-Type-Options'] );
		self::assertSame( 'SAMEORIGIN', $headers['X-Frame-Options'] );
		self::assertSame( 'strict-origin-when-cross-origin', $headers['Referrer-Policy'] );
		self::assertArrayNotHasKey( 'Strict-Transport-Security', $headers );
		$lower = bia_learn_public_headers( array( 'content-security-policy' => 'lowercase-policy' ) );
		self::assertArrayNotHasKey( 'Content-Security-Policy', $lower );
		self::assertSame( 'lowercase-policy', $lower['content-security-policy'] );
	}

	public function test_anonymous_user_routes_are_removed_without_removing_other_routes() {
		Functions\when( 'is_user_logged_in' )->justReturn( false );
		$routes = array( '/wp/v2/users' => array(), '/wp/v2/users/(?P<id>[\\d]+)' => array(), '/wp/v2/posts' => array() );
		self::assertSame( array( '/wp/v2/posts' => array() ), bia_learn_private_user_endpoints( $routes ) );
	}

	public function test_authenticated_user_routes_remain_available() {
		Functions\when( 'is_user_logged_in' )->justReturn( true );
		$routes = array( '/wp/v2/users/me' => array(), '/wp/v2/users' => array() );
		self::assertSame( $routes, bia_learn_private_user_endpoints( $routes ) );
	}

	public function test_empty_search_cannot_return_every_post() {
		Functions\when( 'is_admin' )->justReturn( false );
		$query = new class {
			public $vars = array( 's' => '   ' );
			public function is_main_query() { return true; }
			public function is_search() { return true; }
			public function get( $key ) { return $this->vars[ $key ] ?? ''; }
			public function set( $key, $value ) { $this->vars[ $key ] = $value; }
		};
		bia_learn_public_search( $query );
		self::assertSame( array( 0 ), $query->get( 'post__in' ) );
	}

	public function test_search_preserves_course_only_selection_and_existing_exclusions() {
		Functions\when( 'is_admin' )->justReturn( false );
		Functions\when( 'get_posts' )->justReturn( array( 4, 5 ) );
		$query = new class {
			public $vars = array( 's' => 'ธรรม', 'post_type' => 'courses', 'post__not_in' => array( 9 ) );
			public function is_main_query() { return true; }
			public function is_search() { return true; }
			public function get( $key ) { return $this->vars[ $key ] ?? ''; }
			public function set( $key, $value ) { $this->vars[ $key ] = $value; }
		};
		bia_learn_public_search( $query );
		self::assertSame( array( 'courses' ), $query->get( 'post_type' ) );
		self::assertSame( array( 9, 4, 5 ), $query->get( 'post__not_in' ) );
	}
}
