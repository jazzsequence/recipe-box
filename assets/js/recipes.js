/**
 * Replaces the CMB2 remove group button with a trash can.
 * @author Greg Rickaby
 */
window.recipe_box_remove_group_row = {};
( function( window, $, that ) {

	// Constructor.
	that.init = function() {
		that.cache();

		if ( that.meetsRequirements ) {
			that.bindEvents();
		}
	};

	// Cache all the things.
	that.cache = function() {
		that.$c = {
			window: $(window),
			recipesPage: $( 'body.post-type-rb_recipe' ),
			removeGroupRowButton: $( '.ingredients .cmb-remove-row .cmb-remove-group-row' )
		};
	};

	// Combine all events.
	that.bindEvents = function() {
		that.$c.window.on( 'load', that.doTrashCan );
	};

	// Do we meet the requirements?
	that.meetsRequirements = function() {
		return that.$c.recipesPage.length;
	};

	// Replace HTML with a Dashicon "trash can".
	that.doTrashCan = function() {
		that.$c.removeGroupRowButton.html( '<span class="dashicons dashicons-trash"></span>' );
	};

	// Engage!
	$( that.init );

})( window, jQuery, window.recipe_box_remove_group_row );

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