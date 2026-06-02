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
	if ( ! is_page_template( 'page-templates/template-contact.php' ) ) {
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
