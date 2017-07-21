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
			wpapi:        '/wp-json/wp/v2/recipes',
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
			url: apiUrl + plugin.$c.wpapi + '?filter[posts_per_page]=10',
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
		    apiUrl     = $( 'input#api_url' ).val() + plugin.$c.wpapi + '?filter[posts_per_page]=10&page=',
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

	/**
	 * Match meta of new recipe (from remote Recipe Box) to old recipe (from current Recipe Box) to determine if they are the same recipe.
	 * @param  {object}  newRecipe The new recipe from the remote site.
	 * @param  {object}  oldRecipe The old recipe from the current site.
	 * @return {Boolean}           Whether the new recipe is identical (or near enough) to the old recipe.
	 */
	plugin.isDuplicateRecipe = function( newRecipe, oldRecipe ) {
		let recipeIngredients = false,
			recipeSteps = false,
			recipeSlug = false,
			recipeServings = false;

		// Check if the ingredients list is undefined.
		if ( typeof oldRecipe.ingredients !== 'undefined' ) {
			recipeIngredients = oldRecipe.ingredients.length === newRecipe.ingredients.length;
		}

		if ( typeof newRecipe.ingredients === 'undefined' ) {
			recipeIngredients = true;
		}

		// Check if the steps are undefined.
		if ( typeof oldRecipe.steps !== 'undefined' ) {
			recipeSteps = oldRecipe.steps.length === newRecipe.steps.length;
		}

		if ( typeof newRecipe.steps === 'undefined' ) {
			recipeSteps = true;
		}

		// Check if the servings are undefined.
		if ( typeof oldRecipe.servings !== 'undefined' ) {
			recipeServings = oldRecipe.servings === newRecipe.servings;
		}

		if ( typeof newRecipe.servings === 'undefined' ) {
			recipeServings = true;
		}

		// The recipe will always have a slug, so we just set the value to whether the old slug matches the new slug.
		recipeSlug = oldRecipe.slug === newRecipe.slug;

		// Check to see if the meta matches.
		if (
			recipeIngredients &&
			recipeSteps &&
			recipeSlug &&
			recipeServings
		) {
			return true;
		}

		return false;
	}
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