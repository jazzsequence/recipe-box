/**
 * Recipe API Import
 */
window.RecipeImport = {};
( function( window, $, plugin ) {

	// Constructor.
	plugin.init = function() {
		plugin.cache();

		if ( plugin.meetsRequirements ) {
			plugin.bindEvents();
		}
	};

	// Cache all the things.
	plugin.cache = function() {
		plugin.$c = {
			window: $(window),
			wrapper: $( '.recipe-box-import' ),
		};
	};

	// Combine all events.
	plugin.bindEvents = function() {
		plugin.fetchRecipes( event );
	};

	// Do we meet the requirements?
	plugin.meetsRequirements = function() {
		return plugin.$c.wrapper.length;
	};

	// Some function.
	plugin.fetchRecipes = function( event ) {
		let apiUrl = $( 'input#api_url' ).val();

		event.preventDefault();

		if ( "" == apiUrl ) {
			return;
			// display some message here.
		}

		$.ajax({
			url: apiUrl + '/wp-json/wp/v2/recipes?filter[posts_per_page]=10',
			success: function( data ) {
				console.log( data )
			},
			cache: false
		});
	};

	// Fetch recipes.
	$( 'a#recipe-api-fetch' ).on( 'click', plugin.init );

})( window, jQuery, window.RecipeImport );