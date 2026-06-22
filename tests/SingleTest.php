<?php
/**
 * Tests for single.php template.
 *
 * @package BIA_Learn
 */

namespace BIALearn\Tests;

use PHPUnit\Framework\TestCase;
use Brain\Monkey\Functions;

class SingleTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		\Brain\Monkey\setUp();
		
		Functions\stubs( array(
			'get_header' => true,
			'get_footer' => true,
			'the_post' => true,
			'get_template_part' => true,
			'comments_template' => true,
			'get_the_post_navigation' => true,
			'has_post_thumbnail' => false,
			'the_post_thumbnail' => true,
			'the_content' => true,
			'the_title' => true,
			'esc_html_e' => true,
		));
	}

	protected function tearDown(): void {
		\Brain\Monkey\tearDown();
		parent::tearDown();
	}

	public function test_single_renders_post() {
		Functions\expect( 'have_posts' )
			->andReturn( true, false );

		Functions\expect( 'comments_open' )->andReturn( true );
		Functions\expect( 'get_comments_number' )->andReturn( 1 );

		ob_start();
		require dirname( __DIR__ ) . '/single.php';
		$output = ob_get_clean();

		$this->assertStringContainsString( '<article class=', $output );
	}
}
