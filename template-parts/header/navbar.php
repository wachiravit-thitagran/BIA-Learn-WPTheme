<?php
/**
 * Primary navigation bar: logo + menu + search + CTA + burger.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$courses_url = bia_learn_courses_url();
?>
<div class="container-bia flex h-20 items-center justify-between gap-6">

	<!-- Brand -->
	<div class="flex items-center gap-3">
		<?php if ( has_custom_logo() ) : ?>
			<div class="bia-logo flex items-center [&_img]:h-12 [&_img]:w-auto"><?php the_custom_logo(); ?></div>
		<?php else : ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-3" rel="home">
				<span class="inline-flex items-center rounded-lg bg-white px-2 py-1 shadow-soft">
					<img src="<?php echo esc_url( BIA_LEARN_URI . '/assets/images/biaxpsu-logo.png' ); ?>" alt="<?php echo esc_attr( 'BIA × PSU — ' . get_bloginfo( 'name' ) ); ?>" width="273" height="142" class="h-10 w-auto" loading="eager" />
				</span>
				<span class="leading-tight">
					<span class="bia-site-title block font-serif text-lg font-bold text-crimson"><?php bloginfo( 'name' ); ?></span>
					<span class="bia-site-description block text-2xs uppercase tracking-[0.18em] text-ink-light"><?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'แพลตฟอร์มการเรียนรู้', 'bia-learn' ) ); ?></span>
				</span>
			</a>
		<?php endif; ?>
	</div>

	<!-- Desktop menu -->
	<nav class="hidden items-center lg:flex" aria-label="<?php esc_attr_e( 'เมนูหลัก', 'bia-learn' ); ?>">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'flex items-center gap-7',
					'depth'          => 2,
					'fallback_cb'    => false,
				)
			);
		} else {
			bia_learn_default_menu();
		}
		?>
	</nav>

	<!-- Actions -->
	<div class="flex items-center gap-2 sm:gap-3">
		<button
			type="button"
			class="grid h-10 w-10 place-items-center rounded-full text-ink-soft transition hover:bg-paper-100 hover:text-crimson"
			x-data
			@click="$dispatch('open-search')"
			aria-label="<?php esc_attr_e( 'ค้นหา', 'bia-learn' ); ?>"
		>
			<?php echo bia_learn_icon( 'search', 'h-5 w-5' ); // phpcs:ignore ?>
		</button>

		<a href="<?php echo esc_url( $courses_url ); ?>" class="btn-primary hidden sm:inline-flex">
			<?php esc_html_e( 'เริ่มเรียน', 'bia-learn' ); ?>
			<?php echo bia_learn_icon( 'arrow', 'h-4 w-4' ); // phpcs:ignore ?>
		</a>

		<!-- Burger -->
		<button
			type="button"
			class="grid h-10 w-10 place-items-center rounded-full text-ink hover:bg-paper-100 lg:hidden"
			@click="toggleMobile()"
			:aria-expanded="mobileOpen.toString()"
			aria-label="<?php esc_attr_e( 'เปิด/ปิดเมนู', 'bia-learn' ); ?>"
		>
			<template x-if="!mobileOpen"><span><?php echo bia_learn_icon( 'menu', 'h-6 w-6' ); // phpcs:ignore ?></span></template>
			<template x-if="mobileOpen"><span><?php echo bia_learn_icon( 'close', 'h-6 w-6' ); // phpcs:ignore ?></span></template>
		</button>
	</div>
</div>

<!-- Slide-down search panel -->
<div
	x-data="{ open: false }"
	@open-search.window="open = true; $nextTick(() => $refs.q && $refs.q.focus())"
	@keydown.escape.window="open = false"
	x-show="open"
	x-cloak
	x-transition.opacity
	class="border-t border-paper-200 bg-white/95 backdrop-blur"
	style="display:none"
>
	<div class="container-bia py-4">
		<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-3">
			<?php echo bia_learn_icon( 'search', 'h-5 w-5 text-crimson' ); // phpcs:ignore ?>
			<input x-ref="q" type="search" name="s" class="field border-0 bg-transparent text-lg shadow-none focus:ring-0" placeholder="<?php esc_attr_e( 'ค้นหาคอร์ส บทเรียน หรือบทความ…', 'bia-learn' ); ?>">
			<button type="submit" class="btn-primary"><?php esc_html_e( 'ค้นหา', 'bia-learn' ); ?></button>
			<button type="button" class="btn-ghost" @click="open = false" aria-label="<?php esc_attr_e( 'ปิด', 'bia-learn' ); ?>"><?php echo bia_learn_icon( 'close', 'h-5 w-5' ); // phpcs:ignore ?></button>
		</form>
	</div>
</div>
