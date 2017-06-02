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
		let apiUrl = $( 'input#api_url' ).val(),
		    cmb2form = $( '.recipe-box-import .cmb2-wrap' ),
		    messagesWrap = $( '.recipe-box-import-messages' ),
		    messagesP = $( 'p.rb-messages-inner' ),
		    fetchingRecipes = $( '.recipe-box-import-header' ),
		    recipeList = $( '.recipe-box-import-recipe-list ul.recipe-list' ),
		    moreWrap = $( '.recipe-box-import-footer p.recipe-box-more' ),
		    moreLink = $( 'a#recipe-api-fetch-more' ),
		    morePage = moreLink.data( 'page' );

		event.preventDefault();

		if ( "" == apiUrl ) {
			messagesWrap.show().addClass( 'error' );
			messagesP.text( recipe_import_messages.error_no_url );
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