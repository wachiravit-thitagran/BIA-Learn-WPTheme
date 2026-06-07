<?php
/**
 * Statistics strip with count-up animation.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$stats = bia_learn_get_stats();

// Multi-colour stat cards mirroring the bia.psu.ac.th user dashboard (/user/):
// each metric gets its own card with a tinted circular icon chip.
$items = array(
	array(
		'icon'  => 'book',
		'value' => max( $stats['courses'], 1 ),
		'label' => __( 'คอร์สเรียน', 'bia-learn' ),
		'chip'  => 'icon-chip-crimson',
	),
	array(
		'icon'  => 'users',
		'value' => max( $stats['students'], 1 ),
		'label' => __( 'ผู้เรียน', 'bia-learn' ),
		'chip'  => 'icon-chip-success',
	),
	array(
		'icon'  => 'user',
		'value' => max( $stats['instructors'], 1 ),
		'label' => __( 'ผู้สอน/วิทยากร', 'bia-learn' ),
		'chip'  => 'icon-chip-warning',
	),
	array(
		'icon'  => 'play',
		'value' => max( $stats['lessons'], 1 ),
		'label' => __( 'บทเรียน', 'bia-learn' ),
		'chip'  => 'icon-chip-info',
	),
);
?>
<section class="section-tight bg-paper-50">
	<div class="container-bia">
		<div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
			<?php foreach ( $items as $item ) : ?>
				<div class="stat-card flex items-center gap-4" x-data="countUp(<?php echo (int) $item['value']; ?>)">
					<span class="icon-chip <?php echo esc_attr( $item['chip'] ); ?>"><?php echo bia_learn_icon( $item['icon'], 'h-5 w-5' ); // phpcs:ignore ?></span>
					<div>
						<div class="stat-card__num"><span x-text="display">0</span><span class="text-gold">+</span></div>
						<div class="stat-card__label"><?php echo esc_html( $item['label'] ); ?></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
