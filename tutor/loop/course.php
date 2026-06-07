<?php
/**
 * BIA Learn override for Tutor LMS loop cards.
 *
 * Reuses the theme's course card partial so Tutor archives, widgets, profile
 * listings, and wishlist screens share the same visual language.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

do_action( 'bia_learn_tutor/loop/before_course_card', get_the_ID() );
get_template_part( 'template-parts/cards/course-card' );
do_action( 'bia_learn_tutor/loop/after_course_card', get_the_ID() );