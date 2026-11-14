<?php
/**
 * Explain Tutor's estimate without presenting it as measured study time.
 *
 * @package BIA_Learn
 */
defined( 'ABSPATH' ) || exit;
?>
<div x-data="tutorModal({ id: 'tutor-time-spent-modal' })" x-cloak>
	<template x-teleport="body">
		<div x-bind="getModalBindings()">
			<div x-bind="getBackdropBindings()"></div>
			<div x-bind="getModalContentBindings()" style="max-width: min(440px, calc(100vw - 32px))">
				<div class="tutor-modal-body">
					<h2 class="tutor-h4"><?php esc_html_e( 'เวลาเรียนโดยประมาณ', 'bia-learn' ); ?></h2>
					<p class="tutor-my-4"><?php echo esc_html( $time_spent_value ?? '' ); ?></p>
					<p><?php esc_html_e( 'คำนวณจากระยะเวลาคอร์สที่ผู้สอนกำหนดและความคืบหน้า ไม่ใช่เวลาที่จับจากการใช้งานจริง', 'bia-learn' ); ?></p>
					<button type="button" class="tutor-btn tutor-btn-primary tutor-mt-4" @click="TutorCore.modal.closeModal('tutor-time-spent-modal')"><?php esc_html_e( 'ปิด', 'bia-learn' ); ?></button>
				</div>
			</div>
		</div>
	</template>
</div>
