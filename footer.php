<?php
/**
 * The footer: CTA strip, widget columns, bottom bar, closing markup.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;
?>
	</div><!-- #content -->

	<?php
	// Pre-footer CTA shows on the homepage only — keeps inner pages uncluttered.
	if ( is_front_page() ) {
		get_template_part( 'template-parts/footer/cta' );
	}
	?>

	<footer id="colophon" class="site-footer relative overflow-hidden bg-plum-wash text-paper-200">
		<div class="absolute inset-0 bg-grain opacity-40" aria-hidden="true"></div>
		<div class="relative">
			<?php get_template_part( 'template-parts/footer/columns' ); ?>
			<?php get_template_part( 'template-parts/footer/bottom' ); ?>
		</div>
	</footer>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
