window.recipe_box = {};
( function( window, $, that ) {

	// Constructor.
	that.init = function() {
		that.cache();
		that.bindEvents();
	};

	// Combine all events.
	that.bindEvents = function() {
		that.$c.window.on( 'load', that.doTrashCan );
		that.$c.window.on( 'load', that.doAutosuggest );
	};

 	// Cache all the things.
	that.cache = function() {
		that.$c = {
			window: $(window),
			body: $('body'),
			wp_debug: recipes.wp_debug,
			autosuggest: recipes.autosuggest,
			ingredient: '.cmb-repeatable-grouping input.ingredient',
			recipesPage: $( 'body.post-type-rb_recipe' ),
			removeGroupRowButton: $( '.ingredients .cmb-remove-row .cmb-remove-group-row' ),
		};
	};

	// Replace HTML with a Dashicon "trash can".
	that.doTrashCan = function() {
		that.$c.removeGroupRowButton.html( '<span class="dashicons dashicons-trash"></span>' );
	};

	that.doAutosuggest = function() {

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
			that.$c.body.on('click',that.$c.ingredient,function() {
				$(this).autocomplete({
					source: that.$c.autosuggest,
				});
			} );
		}
	};

	// Engage!
	$( that.init );
})( window, jQuery, window.recipe_box );