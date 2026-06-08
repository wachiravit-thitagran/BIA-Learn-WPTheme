<?php
/**
 * Mobile off-canvas navigation. Visibility is driven by the `siteHeader`
 * Alpine component declared on the <header> element.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- Backdrop -->
<div
	x-show="mobileOpen"
	x-cloak
	x-transition.opacity
	@click="closeMobile()"
	class="fixed inset-0 z-40 bg-plum-900/50 backdrop-blur-sm lg:hidden"
	style="display:none"
	aria-hidden="true"
></div>

<!-- Panel -->
<div
	x-show="mobileOpen"
	x-cloak
	x-transition:enter="transition ease-out-expo duration-300"
	x-transition:enter-start="translate-x-full"
	x-transition:enter-end="translate-x-0"
	x-transition:leave="transition ease-in duration-200"
	x-transition:leave-start="translate-x-0"
	x-transition:leave-end="translate-x-full"
	x-trap="mobileOpen"
	class="fixed inset-y-0 right-0 z-50 flex w-[88%] max-w-sm flex-col bg-paper-50 shadow-2xl lg:hidden"
	style="display:none"
	role="dialog"
	aria-modal="true"
	aria-label="<?php esc_attr_e( 'เมนูหลัก', 'bia-learn' ); ?>"
>
	<div class="flex items-center justify-between border-b border-paper-200 px-6 py-5">
		<span class="font-serif text-lg font-bold text-crimson"><?php bloginfo( 'name' ); ?></span>
		<button type="button" @click="closeMobile()" class="grid h-9 w-9 place-items-center rounded-full hover:bg-paper-100" aria-label="<?php esc_attr_e( 'ปิดเมนู', 'bia-learn' ); ?>">
			<?php echo bia_learn_icon( 'close', 'h-5 w-5' ); // phpcs:ignore ?>
		</button>
	</div>

	<nav class="flex-1 overflow-y-auto px-6 py-6" aria-label="<?php esc_attr_e( 'เมนูบนมือถือ', 'bia-learn' ); ?>">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'flex flex-col gap-1 [&_a]:block [&_a]:rounded-lg [&_a]:px-3 [&_a]:py-2.5 [&_a]:text-base [&_a]:font-medium [&_a]:text-ink-soft [&_a:hover]:bg-paper-100 [&_a:hover]:text-crimson [&_.current-menu-item>a]:bg-crimson-50 [&_.current-menu-item>a]:text-crimson [&_.sub-menu]:ml-3 [&_.sub-menu]:border-l [&_.sub-menu]:border-paper-200 [&_.sub-menu]:pl-2',
					'depth'          => 2,
					'fallback_cb'    => 'bia_learn_default_menu',
				)
			);
		} else {
			bia_learn_default_menu();
		}
		?>
	</nav>

	<div class="border-t border-paper-200 p-6">
		<?php if ( is_user_logged_in() ) : ?>
			<a href="<?php echo esc_url( bia_learn_tutor_dashboard_url( admin_url() ) ); ?>" class="btn-primary w-full">
				<?php echo bia_learn_icon( 'user', 'h-4 w-4' ); // phpcs:ignore ?>
				<?php esc_html_e( 'แดชบอร์ดของฉัน', 'bia-learn' ); ?>
			</a>
		<?php else : ?>
			<div class="grid grid-cols-2 gap-3">
				<a href="<?php echo esc_url( bia_learn_auth_url( 'login' ) ); ?>" class="btn-outline w-full"><?php esc_html_e( 'เข้าสู่ระบบ', 'bia-learn' ); ?></a>
				<a href="<?php echo esc_url( bia_learn_auth_url( 'register' ) ); ?>" class="btn-primary w-full"><?php esc_html_e( 'สมัคร', 'bia-learn' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</div>
