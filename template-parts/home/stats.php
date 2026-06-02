<?php
/**
 * Statistics strip with count-up animation.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$stats = bia_learn_get_stats();

$items = array(
	array(
		'icon'  => 'book',
		'value' => max( $stats['courses'], 1 ),
		'label' => __( 'คอร์สเรียน', 'bia-learn' ),
	),
	array(
		'icon'  => 'users',
		'value' => max( $stats['students'], 1 ),
		'label' => __( 'ผู้เรียน', 'bia-learn' ),
	),
	array(
		'icon'  => 'user',
		'value' => max( $stats['instructors'], 1 ),
		'label' => __( 'ผู้สอน/วิทยากร', 'bia-learn' ),
	),
	array(
		'icon'  => 'cert',
		'value' => max( $stats['lessons'], 1 ),
		'label' => __( 'บทเรียน', 'bia-learn' ),
	),
);
?>
<section class="section-tight bg-paper-50">
	<div class="container-bia">
		<div class="grid grid-cols-2 gap-4 rounded-3xl border border-paper-200 bg-white p-6 shadow-soft sm:gap-8 sm:p-10 lg:grid-cols-4">
			<?php foreach ( $items as $i => $item ) : ?>
				<div class="flex flex-col items-center gap-2 text-center <?php echo $i < 3 ? 'sm:border-r sm:border-paper-100' : ''; ?>" x-data="countUp(<?php echo (int) $item['value']; ?>)">
					<span class="grid h-12 w-12 place-items-center rounded-2xl bg-crimson-50 text-crimson"><?php echo bia_learn_icon( $item['icon'], 'h-6 w-6' ); // phpcs:ignore ?></span>
					<span class="font-serif text-3xl font-black text-crimson sm:text-4xl"><span x-text="display">0</span><span class="text-gold">+</span></span>
					<span class="text-sm font-medium text-ink-light"><?php echo esc_html( $item['label'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
