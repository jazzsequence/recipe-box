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
			window:       $(window),
			wrapper:      $( '.recipe-box-import' ),
			fetch:        $( 'a#recipe-api-fetch' ),
			fetchMore:    $( 'a#recipe-api-fetch-more' ),
			messagesWrap: $( '.recipe-box-import-messages' ),
			messagesP:    $( 'p.rb-messages-inner' ),
			import:       $( '.recipe-box-fetch.button' ),
			wpapi:        '/wp-json/wp/v2/recipes?filter[posts_per_page]=10',
		};
	};

	// Combine all events.
	plugin.bindEvents = function() {
		// Fetch recipes.
		plugin.$c.fetch.on( 'click', plugin.fetchRecipes );

		// Fetch _moar_ recipes.
		plugin.$c.fetchMore.on( 'click', plugin.fetchMore );

		// Import selected recipes.
		plugin.$c.import.on( 'click', plugin.importRecipes );
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
		    cmb2form     = $( '.recipe-box-import .cmb2-wrap' );

		if ( "" == apiUrl ) {
			plugin.$c.messagesWrap.show().addClass( 'error' );
			plugin.$c.messagesP.text( recipe_import_messages.error_no_url );
			return;
		}

		apiUrl = plugin.checkProtocol( apiUrl );

		$.ajax({
			url: apiUrl + plugin.$c.wpapi,
			success: function( data ) {
				// Hide the CMB2 input form.
				cmb2form.hide();

				// Display messages.
				plugin.messagesSuccess( apiUrl );

				// Render list of recipes.
				plugin.displayRecipeList( data );

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
		let page       = plugin.$c.fetchMore.data( 'page' ),
		    apiUrl     = $( 'input#api_url' ).val() + plugin.$c.wpapi + '&page=',
		    moreWrap   = $( '.recipe-box-import-footer p.recipe-box-more' ),
		    moreLink   = $( 'a#recipe-api-fetch-more' );

		// Increment the page to fetch.
		page++;

		// Make sure our URL is still properly formatted.
		apiUrl = plugin.checkProtocol( apiUrl );

		$.ajax({
			url: apiUrl + page,
			success: function( data ) {
				// Update the page data attribute.
				plugin.$c.fetchMore.data( 'page', page );

				// Render list of recipes.
				plugin.displayRecipeList( data );

			},
			error: function() {
				plugin.messagesDone();
			},
			cache: false
		});
	}

	/**
	 * Handle the messages if recipes were found.
	 * @param {string} apiUrl The API URL passed from the CMB2 form.
	 */
	plugin.messagesSuccess = function( apiUrl ) {
		let fetchingRecipes = $( '.recipe-box-import-header' );

		if ( plugin.$c.messagesWrap.hasClass( 'error' ) ) {
			plugin.$c.messagesWrap.removeClass( 'error' );
		}

		plugin.$c.messagesWrap.show().addClass( 'updated' );
		plugin.$c.messagesP.text( recipe_import_messages.success );

		fetchingRecipes.find( '#api-url-fetched' ).text( apiUrl );
		fetchingRecipes.show();
	};

	/**
	 * Display an error message if the URL was invalid.
	 */
	plugin.messagesError = function() {
		plugin.$c.messagesWrap.addClass( 'error' );
		plugin.$c.messagesWrap.show();
		plugin.$c.messagesP.text( recipe_import_messages.error_invalid_url );
	}

	/**
	 * Display a message saying we're all done when we've run out of recipes.
	 */
	plugin.messagesDone = function() {
		plugin.$c.messagesWrap.removeClass( 'updated success' );
		plugin.$c.messagesWrap.addClass( 'notice notice-info' );
		plugin.$c.fetchMore.hide();
		plugin.$c.messagesP.text( recipe_import_messages.no_more_recipes );
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
	plugin.displayRecipeList = function( recipes ) {
		let recipeList = $( '.recipe-box-import-recipe-list ul.recipe-list' ),
		    recipeWrap = $( '.recipe-box-import-recipe-list' ),
		    recipe;

		recipeWrap.show();

		for ( var i = 0, length = recipes.length; i < length; i++ ) {
			recipe = recipes[ i ];
			// console.log(recipe);
			recipeList.append( '<li><input id="recipe-' + recipe.id + '" type="checkbox" value="' + recipe.id + '"> ' + recipe.title.rendered + '</li>' );
		}

		// Show the list footer.
		plugin.displayFooter();
	};

	/**
	 * Display the footer and handle the fetching of more recipes.
	 * @param  {string} apiUrl The API base URL used to fetch the recipes.
	 */
	plugin.displayFooter = function() {
		let footer = $( '.recipe-box-import-footer' );
		footer.show();
	}

	plugin.importRecipes = function() {
		let apiUrl    = plugin.checkProtocol( $( 'input#api_url' ).val() ),
		    recipeIds = $( 'ul.recipe-list input:checkbox:checked' ).map( function() {
		    	return $( this ).val();
		    }).get();

		window.location = recipe_import_messages.import_url + '&importIds=' + recipeIds + '&importUrl=' + apiUrl;
		// console.log( recipeIds );
	}

	// Kick it off.
	$(window).on( 'load', plugin.init );

})( window, jQuery, window.RecipeImport );