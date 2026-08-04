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
	 * Tutor prints its username/password modal from views/modal/login.php in
	 * several templates, so the theme suppresses that one template's output rather
	 * than chasing each caller. Only that path may be swallowed.
	 */
	public function test_only_the_login_modal_template_is_suppressed() {
		$this->assertTrue( bia_learn_is_suppressed_tutor_template( '/plugins/tutor/views/modal/login.php' ) );
		$this->assertTrue( bia_learn_is_suppressed_tutor_template( 'C:\\wp\\plugins\\tutor\\views\\modal\\login.php' ) );

		foreach (
			array(
				'/plugins/tutor/views/modal/enrol.php',
				'/plugins/tutor/templates/login.php',
				'/plugins/tutor/views/modal/login.php.bak',
				'/themes/bia-learn/tutor/login.php',
				'',
			) as $path
		) {
			$this->assertFalse(
				bia_learn_is_suppressed_tutor_template( $path ),
				"'{$path}' must not be suppressed"
			);
		}
	}

	/**
	 * The hook fires for every template rendered through Tutor's public loader,
	 * including other plugins'. Only Tutor's own copy of the modal may be matched,
	 * so a plugin that ships views/modal/login.php of its own keeps working.
	 */
	public function test_another_plugins_template_of_the_same_name_is_left_alone() {
		$base = '/var/www/wp-content/plugins/tutor/';

		$this->assertTrue(
			// Tutor builds the path as `path . '/views/…'`, so it carries a double slash.
			bia_learn_is_suppressed_tutor_template( $base . '/views/modal/login.php', $base ),
			"Tutor's own modal must be matched, double slash and all"
		);
		$this->assertFalse(
			bia_learn_is_suppressed_tutor_template( '/var/www/wp-content/plugins/other-lms/views/modal/login.php', $base ),
			"another plugin's template must render untouched"
		);
		$this->assertFalse(
			bia_learn_is_suppressed_tutor_template( $base . 'views/modal/enrol.php', $base ),
			'only the login modal is suppressed'
		);
	}

	/**
	 * A nested render of a different template must not close our buffer early, and
	 * must not be swallowed itself.
	 */
	public function test_nested_template_render_is_not_disturbed() {
		$modal  = '/plugins/tutor/views/modal/login.php';
		$nested = '/plugins/other/views/partial.php';

		$level = ob_get_level();

		bia_learn_suppress_tutor_template_start( $modal );
		bia_learn_suppress_tutor_template_start( $nested ); // no-op: not suppressed
		bia_learn_suppress_tutor_template_end( $nested );   // must not close ours

		$this->assertGreaterThan( $level, ob_get_level(), 'our buffer must still be open' );
		$this->assertArrayHasKey( 'bia_learn_suppressing_tutor_template', $GLOBALS );

		bia_learn_suppress_tutor_template_end( $modal );

		$this->assertSame( $level, ob_get_level() );
		$this->assertArrayNotHasKey( 'bia_learn_suppressing_tutor_template', $GLOBALS );
	}

	/**
	 * If the template leaves a buffer of its own open, ours still has to close —
	 * otherwise it would keep swallowing the rest of the page.
	 */
	public function test_unbalanced_buffer_inside_the_template_cannot_leak() {
		$modal = '/plugins/tutor/views/modal/login.php';
		$level = ob_get_level();

		bia_learn_suppress_tutor_template_start( $modal );
		ob_start(); // the template forgets to close this
		echo 'leftover';
		bia_learn_suppress_tutor_template_end( $modal );

		$this->assertSame( $level, ob_get_level(), 'buffers must unwind back to where we started' );
	}

	/**
	 * The suppression exists because passwords are dead here. If Tutor's own login
	 * is switched back on, its screens must render untouched.
	 */
	public function test_suppression_stands_down_when_native_login_is_on() {
		$modal = '/plugins/tutor/views/modal/login.php';

		// Stubbing tutor_utils() would leak a real global function into later tests
		// and flip function_exists() guards elsewhere in the theme, so drive the
		// decision through the filter instead.
		Monkey\Filters\expectApplied( 'bia_learn_tutor_native_login' )->andReturn( true );

		$level = ob_get_level();
		bia_learn_suppress_tutor_template_start( $modal );

		$this->assertSame( $level, ob_get_level(), 'no buffer may be opened while native login is on' );
		$this->assertArrayNotHasKey( 'bia_learn_suppressing_tutor_template', $GLOBALS );
	}

	/**
	 * With native login off the modal's markup must never reach the page — hiding
	 * it with CSS would still ship a password field in the HTML.
	 */
	public function test_suppressed_template_output_is_discarded() {
		$modal = '/plugins/tutor/views/modal/login.php';

		// tutor_utils() is absent here, which is the SSO-only case: native login
		// reads as off, so the modal must be swallowed.
		$level = ob_get_level();

		bia_learn_suppress_tutor_template_start( $modal );
		echo '<input type="password" name="pwd">';
		bia_learn_suppress_tutor_template_end( $modal );

		$this->assertSame( $level, ob_get_level(), 'the buffer must be closed again' );
		$this->assertArrayNotHasKey( 'bia_learn_suppressing_tutor_template', $GLOBALS );
	}

	/**
	 * Both the Tailwind source and its build output are committed, so a rule added
	 * to one and not rebuilt into the other ships as a silent no-op. Pin the quiz
	 * footer fix — the submit button sat flush against the viewport edge without
	 * it — in both files.
	 */
	public function test_quiz_footer_rule_is_built_into_the_stylesheet() {
		$src   = (string) file_get_contents( dirname( __DIR__ ) . '/src/css/main.css' );
		$built = (string) file_get_contents( dirname( __DIR__ ) . '/assets/css/main.css' );

		$this->assertStringContainsString( '.tutor-quiz-submission .tutor-quiz-footer', $src );
		$this->assertStringContainsString(
			'.tutor-quiz-submission .tutor-quiz-footer',
			$built,
			'assets/css/main.css is stale — run npm run build:css'
		);
		// The button must fill the centred column rather than sit at its left edge.
		$this->assertMatchesRegularExpression( '/tutor-quiz-footer\{[^}]*margin-left:auto/', $built );
		$this->assertMatchesRegularExpression( '/tutor-quiz-footer\{[^}]*justify-content:center/', $built );
	}

	/**
	 * Tutor's own templates/login.php redirects with a raw header() call after the
	 * page has already been sent, which printed "headers already sent" where the
	 * lesson should be. The override must therefore render, never redirect — and it
	 * must not reintroduce a password form, since password sign-in is disabled.
	 */
	public function test_tutor_login_override_renders_without_redirecting() {
		$src = file_get_contents( dirname( __DIR__ ) . '/tutor/login.php' );

		$this->assertNotFalse( $src, 'tutor/login.php override is missing' );

		// Strip the docblock first: it names these calls while explaining the bug.
		$code = preg_replace( '#/\*.*?\*/#s', '', $src );

		foreach ( array( 'header(', 'wp_redirect', 'wp_safe_redirect' ) as $forbidden ) {
			$this->assertStringNotContainsString(
				$forbidden,
				$code,
				"tutor/login.php must not call {$forbidden} — output has already started by then"
			);
		}

		// The only permitted exit is the direct-access guard.
		$this->assertStringContainsString( "defined( 'ABSPATH' ) || exit;", $code );
		$this->assertSame( 1, substr_count( $code, 'exit' ), 'the only exit may be the ABSPATH guard' );

		$this->assertStringNotContainsString( 'type="password"', $src );
		$this->assertStringNotContainsString( 'name="log"', $src );
		$this->assertStringContainsString( 'authorizenter_button', $src, 'the SSO buttons are the only working sign-in' );
		$this->assertStringContainsString( 'return_to', $src, 'sign-in must return the visitor to the gated page' );
	}

	/**
	 * Tutor's forgot-password screen mails out a password that cannot be used,
	 * because sign-in is SSO-only. Guests belong on the branded auth page.
	 */
	public function test_forgot_password_page_is_redirected_for_guests() {
		$this->assertTrue(
			bia_learn_should_redirect_tutor_password_page( 'retrieve-password', false, false )
		);
		$this->assertTrue(
			bia_learn_should_redirect_tutor_password_page( 'reset-password', false, false )
		);
	}

	/**
	 * Tutor's own login system being on means passwords are in play again, so its
	 * password screens must keep working.
	 */
	public function test_password_pages_are_left_alone_when_native_login_is_on() {
		$this->assertFalse(
			bia_learn_should_redirect_tutor_password_page( 'retrieve-password', false, true )
		);
	}

	public function test_signed_in_users_are_not_redirected() {
		$this->assertFalse(
			bia_learn_should_redirect_tutor_password_page( 'retrieve-password', true, false )
		);
	}

	/**
	 * The guard must not swallow the rest of the dashboard.
	 */
	public function test_other_dashboard_pages_are_untouched() {
		foreach ( array( 'enrolled-courses', 'my-certificates', 'settings', '' ) as $page ) {
			$this->assertFalse(
				bia_learn_should_redirect_tutor_password_page( $page, false, false ),
				"Dashboard page '{$page}' must not be redirected"
			);
		}
	}

	/**
	 * The sub-page comes from Tutor's query var, and falls back to the request
	 * path — Tutor has moved these rewrite rules between releases, and a missing
	 * query var would silently disable the guard.
	 */
	public function test_dashboard_page_falls_back_to_the_request_path() {
		Monkey\Functions\when( 'get_query_var' )->justReturn( '' );
		Monkey\Functions\when( 'wp_unslash' )->returnArg();
		Monkey\Functions\when( 'wp_parse_url' )->alias(
			function ( $url, $component = -1 ) {
				return parse_url( $url, $component );
			}
		);

		$_SERVER['REQUEST_URI'] = '/dashboard/retrieve-password/';
		$this->assertSame( 'retrieve-password', bia_learn_current_tutor_dashboard_page() );

		$_SERVER['REQUEST_URI'] = '/dashboard/retrieve-password?foo=bar';
		$this->assertSame( 'retrieve-password', bia_learn_current_tutor_dashboard_page() );

		unset( $_SERVER['REQUEST_URI'] );
	}

	public function test_dashboard_page_prefers_the_query_var() {
		Monkey\Functions\when( 'get_query_var' )->justReturn( 'enrolled-courses' );

		$_SERVER['REQUEST_URI'] = '/dashboard/retrieve-password/';
		$this->assertSame( 'enrolled-courses', bia_learn_current_tutor_dashboard_page() );

		unset( $_SERVER['REQUEST_URI'] );
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
