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

	/**
	 * Look for recipes and display a list if we found them.
	 * @param  {event} event The click event that triggered the function.
	 * @return {void}
	 */
	plugin.fetchRecipes = function( event ) {
		let apiUrl     = $( 'input#api_url' ).val(),
		    cmb2form   = $( '.recipe-box-import .cmb2-wrap' ),
		    recipeList = $( '.recipe-box-import-recipe-list ul.recipe-list' ),
		    moreWrap   = $( '.recipe-box-import-footer p.recipe-box-more' ),
		    moreLink   = $( 'a#recipe-api-fetch-more' ),
		    morePage   = moreLink.data( 'page' );

		event.preventDefault();

		if ( "" == apiUrl ) {
			messagesWrap.show().addClass( 'error' );
			messagesP.text( recipe_import_messages.error_no_url );
			return;
		}

		apiUrl = plugin.checkProtocol( apiUrl );

		$.ajax({
			url: apiUrl + '/wp-json/wp/v2/recipes?filter[posts_per_page]=10',
			success: function( data ) {
				// Hide the CMB2 input form.
				cmb2form.hide();

				// Display messages.
				plugin.messagesSuccess( apiUrl );


				console.log( data )
			},
			cache: false
		});
	};

	/**
	 * Handle the messages if recipes were found.
	 * @param {string} apiUrl The API URL passed from the CMB2 form.
	 */
	plugin.messagesSuccess = function( apiUrl ) {
		let messagesWrap = $( '.recipe-box-import-messages' ),
		    messagesP = $( 'p.rb-messages-inner' ),
		    fetchingRecipes = $( '.recipe-box-import-header' );

		if ( messagesWrap.hasClass( 'error' ) ) {
			messagesWrap.removeClass( 'error' );
		}

		messagesWrap.show().addClass( 'updated' );
		messagesP.text( recipe_import_messages.success );

		fetchingRecipes.find( '#api-url-fetched' ).text( apiUrl );
		fetchingRecipes.show();
	};

	/**
	 * Check if the API URL contains http/https. If it doesn't, prepend the URL with the protocol.
	 * @param  {string} apiUrl The API url passed to the form.
	 * @return {string}        The final API URL with a protocol.
	 */
	plugin.checkProtocol = function( apiUrl ) {
		let noProtocol = '://',
		    urlHttp    = 'http://',
		    urlHttps   = 'https://';

		if ( ! apiUrl.includes( urlHttp ) && ! apiUrl.includes( urlHttps ) ) {

			// If the URL was passed like ://domain.com return http://domain.com.
			if ( apiUrl.includes( noProtocol ) ) {
				return apiUrl.replace( noProtocol, urlHttp );
			}

			return urlHttp + apiUrl;
		}

		return apiUrl;
	};

	plugin.displayRecipeList = function( data ) {
		console.log( data );
	};

	// Fetch recipes.
	$( 'a#recipe-api-fetch' ).on( 'click', plugin.init );

})( window, jQuery, window.RecipeImport );