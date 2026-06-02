<?php
/**
 * The header: opening document markup, skip link and site header.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="scroll-smooth">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'min-h-screen bg-paper-50 text-ink' ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'ข้ามไปยังเนื้อหา', 'bia-learn' ); ?></a>

<div id="page" class="flex min-h-screen flex-col">

	<?php get_template_part( 'template-parts/header/topbar' ); ?>

	<header
		x-data="siteHeader"
		class="site-header site-header--sticky sticky top-0 z-50 transition-all duration-300"
		:class="scrolled ? 'bg-paper-50/95 shadow-soft backdrop-blur' : 'bg-paper-50'"
	>
		<?php get_template_part( 'template-parts/header/navbar' ); ?>
		<?php get_template_part( 'template-parts/header/mobile-menu' ); ?>
	</header>

	<div id="content" class="site-content flex-1">
