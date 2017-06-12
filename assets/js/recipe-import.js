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
			fetch: $( 'a#recipe-api-fetch' ),
			fetchMore: $( 'a#recipe-api-fetch-more' )
		};
	};

	// Combine all events.
	plugin.bindEvents = function() {
		// Fetch recipes.
		plugin.$c.fetch.on( 'click', plugin.fetchRecipes );

		// Fetch _moar_ recipes.
		plugin.$c.fetchMore.on( 'click', plugin.fetchMore );
	};

	// Do we meet the requirements?
	plugin.meetsRequirements = function() {
		return plugin.$c.wrapper.length;
	};

	/**
	 * Look for recipes and display a list if we found them.
	 * @return {void}
	 */
	plugin.fetchRecipes = function() {

		let apiUrl       = $( 'input#api_url' ).val(),
		    cmb2form     = $( '.recipe-box-import .cmb2-wrap' ),
		    messagesWrap = $( '.recipe-box-import-messages' ),
		    messagesP    = $( 'p.rb-messages-inner' );

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

				// Render list of recipes.
				plugin.displayRecipeList( data, apiUrl );

			},
			error: function() {
				plugin.messagesError();
			},
			cache: false
		});
	};

	/**
	 * Handle fetching more recipes from remote API.
	 */
	plugin.fetchMore = function() {
		let page = $( 'a#recipe-api-fetch-more' ).data( 'page' ),
		    apiUrl     = $( 'input#api_url' ).val() + '/wp-json/wp/v2/recipes?filter[posts_per_page]=10&page=',
		    moreWrap   = $( '.recipe-box-import-footer p.recipe-box-more' ),
		    moreLink   = $( 'a#recipe-api-fetch-more' );
	}

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

	plugin.messagesError = function() {
		let messagesWrap = $( '.recipe-box-import-messages' ),
		    messagesP = $( 'p.rb-messages-inner' );

		messagesWrap.addClass( 'error' );
		messagesWrap.show();
		messagesP.text( recipe_import_messages.error_invalid_url );
	}

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

	/**
	 * Display the list of recipes.
	 * @param  {array} recipes Array of recipe objects fetched from the API URL.
	 * @param  {string} apiUrl  The API base URL used to fetch the recipes.
	 */
	plugin.displayRecipeList = function( recipes, apiUrl ) {
		let recipeList = $( '.recipe-box-import-recipe-list ul.recipe-list' ),
		    recipeWrap = $( '.recipe-box-import-recipe-list' ),
		    recipe;

		recipeWrap.show();

		for ( var i = 0, length = recipes.length; i < length; i++ ) {
			recipe = recipes[ i ];
			// console.log(recipe);
			recipeList.append( '<li><input id="recipe-' + recipe.id + '" type="checkbox" data-value="' + recipe.id + '"> ' + recipe.title.rendered + '</li>' );
		}

		// Show the list footer.
		plugin.displayFooter( apiUrl );
	};

	/**
	 * Display the footer and handle the fetching of more recipes.
	 * @param  {string} apiUrl The API base URL used to fetch the recipes.
	 */
	plugin.displayFooter = function( apiUrl ) {
		let footer = $( '.recipe-box-import-footer' );
		footer.show();
	}

	// Kick it off.
	$(window).on( 'load', plugin.init );

})( window, jQuery, window.RecipeImport );