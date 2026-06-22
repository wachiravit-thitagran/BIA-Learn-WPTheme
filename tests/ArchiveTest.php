<?php
/**
 * Tests for archive.php template.
 *
 * @package BIA_Learn
 */

namespace BIALearn\Tests;

use PHPUnit\Framework\TestCase;
use Brain\Monkey\Functions;

class ArchiveTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		\Brain\Monkey\setUp();
		
		Functions\stubs( array(
			'get_header' => true,
			'get_footer' => true,
			'the_archive_title' => true,
			'the_archive_description' => true,
			'the_post' => true,
			'get_template_part' => true,
			'the_posts_pagination' => true,
			'get_the_archive_title' => 'Archive Title',
			'get_the_archive_description' => 'Archive Desc',
		));
	}

	protected function tearDown(): void {
		\Brain\Monkey\tearDown();
		parent::tearDown();
	}

	public function test_archive_renders_posts() {
		Functions\expect( 'have_posts' )
			->andReturn( true, true, false ); // 2 posts, then stop

		ob_start();
		require dirname( __DIR__ ) . '/archive.php';
		$output = ob_get_clean();

		$this->assertStringContainsString( '<main id="main"', $output );
		$this->assertStringContainsString( 'Archive Title', $output );
		$this->assertStringContainsString( '<main id="main"', $output );
	}

	public function test_archive_renders_no_results() {
		Functions\expect( 'have_posts' )->andReturn( false );
		// Functions\expect( 'get_template_part' )->withAnyArgs()->once();

		ob_start();
		require dirname( __DIR__ ) . '/archive.php';
		$output = ob_get_clean();

		$this->assertStringContainsString( '<main id="main"', $output );
	}
}
