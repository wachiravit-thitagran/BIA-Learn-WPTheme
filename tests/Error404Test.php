<?php
/**
 * Tests for 404.php template.
 *
 * @package BIA_Learn
 */

namespace BIALearn\Tests;

use PHPUnit\Framework\TestCase;
use Brain\Monkey\Functions;

class Error404Test extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		\Brain\Monkey\setUp();
		
		Functions\stubs( array(
			'get_header' => true,
			'get_footer' => true,
			'esc_html_e' => true,
			'get_search_form' => true,
		));
	}

	protected function tearDown(): void {
		\Brain\Monkey\tearDown();
		parent::tearDown();
	}

	public function test_404_renders_correctly() {
		ob_start();
		require dirname( __DIR__ ) . '/404.php';
		$output = ob_get_clean();

		$this->assertStringContainsString( '<main id="main"', $output );
		$this->assertStringContainsString( '404', $output );
	}
}
