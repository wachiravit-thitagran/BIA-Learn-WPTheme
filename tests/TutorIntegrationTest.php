<?php
/**
 * Tests for inc/tutor.php glue + template integrity regressions.
 *
 * @package BIA_Learn
 */

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class TutorIntegrationTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		require_once dirname( __DIR__ ) . '/inc/tutor.php';
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * The admin_init self-heal used to run wp_insert_post/update_option for ANY
	 * authenticated user (subscribers included). It must bail without touching
	 * the database when the user cannot manage_options.
	 */
	public function test_supporting_pages_blocked_without_manage_options() {
		Monkey\Functions\when( 'wp_doing_ajax' )->justReturn( false );
		Monkey\Functions\when( 'current_user_can' )->justReturn( false );
		Monkey\Functions\expect( 'get_page_by_path' )->never();
		Monkey\Functions\expect( 'wp_insert_post' )->never();

		bia_learn_register_supporting_pages();

		$this->addToAssertionCount( 1 ); // expectations verified on teardown
	}

	/**
	 * Regression: comments.php once dropped a `<?php` opening tag, printing raw
	 * PHP source (and a dead comment form) to every visitor. Tokenize the
	 * template and assert no PHP source leaks out as inline HTML.
	 */
	public function test_comments_template_has_no_raw_php_source_leak() {
		$src    = file_get_contents( dirname( __DIR__ ) . '/comments.php' );
		$tokens = token_get_all( $src );

		$leaked = '';
		foreach ( $tokens as $token ) {
			if ( is_array( $token ) && T_INLINE_HTML === $token[0] && false !== strpos( $token[1], '$user_identity' ) ) {
				$leaked = $token[1];
				break;
			}
		}

		$this->assertSame( '', $leaked, 'comments.php leaks raw PHP source into the page' );
	}

	/**
	 * The branded-login redirect must leave Authorizenter's administrator escape
	 * hatch (wp-login.php?external=wordpress) alone. The branded page offers SSO
	 * buttons only, so hijacking that URL would remove the last way in when the
	 * identity provider is unreachable.
	 */
	public function test_wp_login_redirect_exempts_the_admin_escape_hatch() {
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_GET                      = array( 'external' => 'wordpress' );

		Monkey\Functions\when( 'sanitize_key' )->returnArg();
		Monkey\Functions\when( 'wp_unslash' )->returnArg();
		Monkey\Functions\expect( 'get_page_by_path' )->never();
		Monkey\Functions\expect( 'wp_safe_redirect' )->never();

		bia_learn_redirect_wp_login();

		$this->addToAssertionCount( 1 ); // expectations verified on teardown
		$_GET = array();
	}

	/**
	 * Without the marker the function walks past the exemption and consults the
	 * branded page, which is what decides the redirect. The redirect itself cannot
	 * be asserted here: bia_learn_redirect_wp_login() ends in exit, which would
	 * take the PHPUnit process down with it.
	 */
	public function test_wp_login_redirect_consults_the_branded_page_without_the_marker() {
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_GET                      = array();

		Monkey\Functions\when( 'sanitize_key' )->returnArg();
		Monkey\Functions\when( 'wp_unslash' )->returnArg();
		Monkey\Functions\when( 'is_user_logged_in' )->justReturn( false );
		// No /auth page: the function returns before redirecting (and before exit).
		Monkey\Functions\expect( 'get_page_by_path' )->once()->with( 'auth' )->andReturn( null );
		Monkey\Functions\expect( 'wp_safe_redirect' )->never();

		bia_learn_redirect_wp_login();

		$this->addToAssertionCount( 1 );
	}
}
