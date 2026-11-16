<?php
/**
 * Statistics strip. Metrics come from WordPress/Tutor, not placeholders.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$stats = bia_learn_get_stats();
$items = array(
	array( 'value' => $stats['courses'], 'label' => __( 'คอร์สเรียน', 'bia-learn' ) ),
	array( 'value' => $stats['students'], 'label' => __( 'ผู้เรียน', 'bia-learn' ) ),
	array( 'value' => $stats['instructors'], 'label' => __( 'ผู้สอน/วิทยากร', 'bia-learn' ) ),
	array( 'value' => $stats['lessons'], 'label' => __( 'บทเรียน', 'bia-learn' ) ),
);
?>
<section class="section-tight border-y border-paper-200 bg-white">
	<div class="container-bia">
		<dl class="grid grid-cols-2 gap-x-6 gap-y-8 lg:grid-cols-4">
			<?php foreach ( $items as $item ) : ?>
				<div class="border-l border-paper-200 pl-5">
					<dt class="text-sm text-ink-light"><?php echo esc_html( $item['label'] ); ?></dt>
					<dd class="mt-2 text-3xl font-semibold tabular-nums text-ink"><?php echo esc_html( number_format_i18n( $item['value'] ) ); ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>
