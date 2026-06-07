<?php
/**
 * Theme Customizer settings: brand, hero, contact, social, footer.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register Customizer panels, sections, settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function bia_learn_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	// --- Panel -----------------------------------------------------------
	$wp_customize->add_panel(
		'bia_learn_panel',
		array(
			'title'    => __( 'ตั้งค่า BIA Learn', 'bia-learn' ),
			'priority' => 30,
		)
	);

	/**
	 * Helper to add a text-ish setting + control.
	 */
	$add_text = function ( $id, $label, $section, $type = 'text', $default = '' ) use ( $wp_customize ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $default,
				'sanitize_callback' => 'email' === $type ? 'sanitize_email' : ( 'url' === $type ? 'esc_url_raw' : 'wp_kses_post' ),
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $label,
				'section' => $section,
				'type'    => 'textarea' === $type ? 'textarea' : ( 'url' === $type ? 'url' : ( 'email' === $type ? 'email' : 'text' ) ),
			)
		);
	};

	/**
	 * Helper to add a boolean (checkbox) setting + control.
	 */
	$add_toggle = function ( $id, $label, $section, $default = true ) use ( $wp_customize ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $default,
				'sanitize_callback' => 'wp_validate_boolean',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $label,
				'section' => $section,
				'type'    => 'checkbox',
			)
		);
	};

	// --- Hero ------------------------------------------------------------
	$wp_customize->add_section(
		'bia_hero',
		array(
			'title' => __( 'ส่วนหัวหน้าแรก (Hero)', 'bia-learn' ),
			'panel' => 'bia_learn_panel',
		)
	);
	$add_text( 'bia_hero_eyebrow', __( 'ข้อความนำ', 'bia-learn' ), 'bia_hero', 'text', __( 'หอจดหมายเหตุพุทธทาส อินทปัญโญ', 'bia-learn' ) );
	$add_text( 'bia_hero_title', __( 'พาดหัว', 'bia-learn' ), 'bia_hero', 'textarea', __( 'เรียนรู้ธรรมะ ภาวนา และปัญญา จากสวนโมกข์สู่โลกดิจิทัล', 'bia-learn' ) );
	$add_text( 'bia_hero_subtitle', __( 'คำอธิบายใต้พาดหัว', 'bia-learn' ), 'bia_hero', 'textarea', __( 'คอร์สเรียนออนไลน์ บทเรียน และคลังความรู้ เพื่อการเรียนรู้ตลอดชีวิตอย่างเป็นอิสระ', 'bia-learn' ) );
	$add_text( 'bia_hero_cta_text', __( 'ปุ่มหลัก — ข้อความ', 'bia-learn' ), 'bia_hero', 'text', __( 'เริ่มเรียนรู้', 'bia-learn' ) );
	$add_text( 'bia_hero_cta_url', __( 'ปุ่มหลัก — ลิงก์', 'bia-learn' ), 'bia_hero', 'url', '' );

	$wp_customize->add_setting( 'bia_hero_image', array( 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control(
		new WP_Customize_Cropped_Image_Control(
			$wp_customize,
			'bia_hero_image',
			array(
				'label'       => __( 'ภาพประกอบ Hero', 'bia-learn' ),
				'section'     => 'bia_hero',
				'flex_width'  => true,
				'flex_height' => true,
				'width'       => 1200,
				'height'      => 1200,
			)
		)
	);

	// --- Homepage sections (show/hide) -----------------------------------
	$wp_customize->add_section(
		'bia_home_sections',
		array(
			'title'       => __( 'เนื้อหาหน้าแรก', 'bia-learn' ),
			'description' => __( 'เปิด/ปิดแต่ละส่วนของหน้าแรก เพื่อให้หน้ากระชับตามต้องการ (Hero และคอร์สแนะนำแสดงเสมอ)', 'bia-learn' ),
			'panel'       => 'bia_learn_panel',
		)
	);
	$add_toggle( 'bia_show_stats', __( 'แสดงแถบสถิติ', 'bia-learn' ), 'bia_home_sections', true );
	$add_toggle( 'bia_show_how_it_works', __( 'แสดง "เริ่มเรียนใน 3 ขั้นตอน"', 'bia-learn' ), 'bia_home_sections', true );
	$add_toggle( 'bia_show_instructors', __( 'แสดงผู้สอน', 'bia-learn' ), 'bia_home_sections', true );
	$add_toggle( 'bia_show_news', __( 'แสดงข่าวสารล่าสุด', 'bia-learn' ), 'bia_home_sections', true );
	$add_toggle( 'bia_show_partners', __( 'แสดงพันธมิตร/คำคม', 'bia-learn' ), 'bia_home_sections', true );

	// --- Contact ---------------------------------------------------------
	$wp_customize->add_section(
		'bia_contact',
		array(
			'title' => __( 'ข้อมูลติดต่อ', 'bia-learn' ),
			'panel' => 'bia_learn_panel',
		)
	);
	$add_text( 'bia_contact_address', __( 'ที่อยู่', 'bia-learn' ), 'bia_contact', 'textarea', __( 'หอจดหมายเหตุพุทธทาส อินทปัญโญ สวนวชิรเบญจทัศ (สวนรถไฟ) กรุงเทพฯ', 'bia-learn' ) );
	$add_text( 'bia_contact_phone', __( 'โทรศัพท์', 'bia-learn' ), 'bia_contact', 'text', '02 936 2800' );
	$add_text( 'bia_contact_email', __( 'อีเมล', 'bia-learn' ), 'bia_contact', 'email', 'info@bia.or.th' );
	$add_text( 'bia_contact_map', __( 'ลิงก์ Google Maps (embed src)', 'bia-learn' ), 'bia_contact', 'url', '' );

	// --- Social ----------------------------------------------------------
	$wp_customize->add_section(
		'bia_social',
		array(
			'title' => __( 'โซเชียลมีเดีย', 'bia-learn' ),
			'panel' => 'bia_learn_panel',
		)
	);
	$add_text( 'bia_social_facebook', 'Facebook URL', 'bia_social', 'url', '' );
	$add_text( 'bia_social_youtube', 'YouTube URL', 'bia_social', 'url', '' );
	$add_text( 'bia_social_line', 'LINE URL', 'bia_social', 'url', '' );

	// --- Footer ----------------------------------------------------------
	$wp_customize->add_section(
		'bia_footer',
		array(
			'title' => __( 'ส่วนท้าย (Footer)', 'bia-learn' ),
			'panel' => 'bia_learn_panel',
		)
	);
	$add_text( 'bia_footer_about', __( 'ข้อความแนะนำองค์กร', 'bia-learn' ), 'bia_footer', 'textarea', __( 'แพลตฟอร์มการเรียนรู้เพื่อเผยแผ่ธรรมะและส่งเสริมปัญญา ตามปณิธานของท่านพุทธทาสภิกขุ', 'bia-learn' ) );
	$add_text( 'bia_footer_copyright', __( 'ข้อความลิขสิทธิ์', 'bia-learn' ), 'bia_footer', 'text', '' );
	$add_text( 'bia_cta_title', __( 'แถบ CTA ก่อน footer — หัวข้อ', 'bia-learn' ), 'bia_footer', 'textarea', __( 'พร้อมเริ่มต้นการเรียนรู้แล้วหรือยัง?', 'bia-learn' ) );
	$add_text( 'bia_cta_button', __( 'แถบ CTA — ปุ่ม', 'bia-learn' ), 'bia_footer', 'text', __( 'สมัครเรียนฟรี', 'bia-learn' ) );
	$add_text( 'bia_cta_url', __( 'แถบ CTA — ลิงก์', 'bia-learn' ), 'bia_footer', 'url', '' );
}
add_action( 'customize_register', 'bia_learn_customize_register' );

/**
 * Live-preview JS for blogname / blogdescription.
 */
function bia_learn_customize_preview_js() {
	wp_enqueue_script(
		'bia-learn-customizer',
		BIA_LEARN_URI . '/assets/js/customizer.js',
		array( 'customize-preview' ),
		BIA_LEARN_VERSION,
		true
	);
}
add_action( 'customize_preview_init', 'bia_learn_customize_preview_js' );
