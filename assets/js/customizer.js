/**
 * Customizer live-preview: blog name + description.
 */
( function ( $ ) {
	wp.customize( 'blogname', function ( value ) {
		value.bind( function ( to ) {
			$( '.bia-site-title' ).text( to );
		} );
	} );
	wp.customize( 'blogdescription', function ( value ) {
		value.bind( function ( to ) {
			$( '.bia-site-description' ).text( to );
		} );
	} );
} )( jQuery );
