jQuery(document).ready(function($) {
	// Show console log if debugging is active.
	if ( recipes.wp_debug ) {
		console.log( recipes.autosuggest );
	}

	if ( recipes.autosuggest ) {
		// wp_localize_script will convert our array into an object. We need to convert it back to an array.
		recipes.autosuggest = $.map( recipes.autosuggest, function( value, index ){
			return [value];
		});

		// Log the autosuggest array after we've modified it.
		if ( recipes.wp_debug ) {
			console.log( recipes.autosuggest );
		}

		// When an ingredient input is clicked, trigger the autocompletion script.
		$('body').on('click','.cmb-repeatable-grouping input.ingredient',function() {
			$(this).autocomplete({
				source: recipes.autosuggest,
			});
		} )
	}
});