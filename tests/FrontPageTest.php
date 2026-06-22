<?php
/**
 * Tests for the front-page.php template display.
 *
 * @package BIA_Learn
 */

namespace BIALearn\Tests;

use PHPUnit\Framework\TestCase;
use Brain\Monkey\Functions;

class FrontPageTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		\Brain\Monkey\setUp();
		
		// Common functions used in templates
		Functions\stubs( array(
			'get_header' => true,
			'get_footer' => true,
			'the_post'   => true,
			'the_content' => true,
		));
	}

	protected function tearDown(): void {
		\Brain\Monkey\tearDown();
		parent::tearDown();
	}

	public function test_front_page_renders_all_sections_by_default() {
		// Expect the core sections
		Functions\expect( 'get_template_part' )
			->with( 'template-parts/home/hero' )->once();
		Functions\expect( 'get_template_part' )
			->with( 'template-parts/home/categories' )->once();
		Functions\expect( 'get_template_part' )
			->with( 'template-parts/home/featured-courses' )->once();
			
		// Options default to true in the code, but we need to mock bia_learn_option.
		Functions\expect( 'bia_learn_option' )
			->andReturn( true );
			
		Functions\expect( 'get_template_part' )
			->with( 'template-parts/home/stats' )->once();
		Functions\expect( 'get_template_part' )
			->with( 'template-parts/home/how-it-works' )->once();
		Functions\expect( 'get_template_part' )
			->with( 'template-parts/home/instructors' )->once();
		Functions\expect( 'get_template_part' )
			->with( 'template-parts/home/latest-news' )->once();
		Functions\expect( 'get_template_part' )
			->with( 'template-parts/home/partners' )->once();

		Functions\expect( 'have_posts' )->never();

		ob_start();
		require dirname( __DIR__ ) . '/front-page.php';
		$output = ob_get_clean();

		$this->assertStringContainsString( '<main id="main">', $output );
	}

	public function test_front_page_hides_optional_sections() {
		// Expect the core sections
		Functions\expect( 'get_template_part' )
			->with( 'template-parts/home/hero' )->once();
		Functions\expect( 'get_template_part' )
			->with( 'template-parts/home/categories' )->once();
		Functions\expect( 'get_template_part' )
			->with( 'template-parts/home/featured-courses' )->once();
			
		// Mock options to return false.
		Functions\expect( 'bia_learn_option' )
			->andReturn( false );
			
		// These should NOT be called.
		Functions\expect( 'get_template_part' )
			->with( 'template-parts/home/stats' )->never();
		Functions\expect( 'get_template_part' )
			->with( 'template-parts/home/how-it-works' )->never();
			
		// Mock loop

		ob_start();
		require dirname( __DIR__ ) . '/front-page.php';
		$output = ob_get_clean();

		$this->assertStringContainsString( '<main id="main">', $output );
	}
}
