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


window.recipe_box_autosuggest = {};
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
			wp_debug: recipes.wp_debug,
			autosuggest: recipes.autosuggest,
			ingredient: '.cmb-repeatable-grouping input.ingredient',
		};
	};

	// Show console log if debugging is active.
	if ( that.$c.wp_debug ) {
		console.log( that.$c.autosuggest );
	}

	if ( that.$c.autosuggest ) {
		// wp_localize_script will convert our array into an object. We need to convert it back to an array.
		that.$c.autosuggest = $.map( that.$c.autosuggest, function( value, index ){
			return [value];
		});

		// Log the autosuggest array after we've modified it.
		if ( that.$c.wp_debug ) {
			console.log( that.$c.autosuggest );
		}

		// When an ingredient input is clicked, trigger the autocompletion script.
		$('body').on('click',that.$c.ingredient,function() {
			$(this).autocomplete({
				source: that.$c.autosuggest,
			});
		} )
	}
})( window, jQuery, window.recipe_box_autosuggest );