document.addEventListener( 'DOMContentLoaded', function () {
	var selectAll = document.querySelector( '.abx-select-all' );
	var selectNone = document.querySelector( '.abx-select-none' );

	function setAll( checked ) {
		document.querySelectorAll( '.abx-post-type-checkbox' ).forEach( function ( box ) {
			box.checked = checked;
		} );
	}

	if ( selectAll ) {
		selectAll.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			setAll( true );
		} );
	}

	if ( selectNone ) {
		selectNone.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			setAll( false );
		} );
	}
} );
