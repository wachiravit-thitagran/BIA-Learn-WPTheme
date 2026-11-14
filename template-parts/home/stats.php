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
		'value' => $stats['courses'],
		'label' => __( 'คอร์สเรียน', 'bia-learn' ),
		'chip'  => 'icon-chip-crimson',
	),
	array(
		'icon'  => 'users',
		'value' => $stats['students'],
		'label' => __( 'ผู้เรียน', 'bia-learn' ),
		'chip'  => 'icon-chip-success',
	),
	array(
		'icon'  => 'user',
		'value' => $stats['instructors'],
		'label' => __( 'ผู้สอน/วิทยากร', 'bia-learn' ),
		'chip'  => 'icon-chip-warning',
	),
	array(
		'icon'  => 'play',
		'value' => $stats['lessons'],
		'label' => __( 'บทเรียน', 'bia-learn' ),
		'chip'  => 'icon-chip-info',
	),
);
?>
<section class="section-tight bg-paper-50">
	<div class="container-bia">
		<div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
			<?php foreach ( $items as $item ) : ?>
				<div class="stat-card flex items-center gap-4">
					<span class="icon-chip <?php echo esc_attr( $item['chip'] ); ?>"><?php echo bia_learn_icon( $item['icon'], 'h-5 w-5' ); // phpcs:ignore ?></span>
					<div>
						<div class="stat-card__num"><span><?php echo esc_html( number_format_i18n( $item['value'] ) ); ?></span></div>
						<div class="stat-card__label"><?php echo esc_html( $item['label'] ); ?></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
