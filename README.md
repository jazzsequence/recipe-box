# Recipe Box #
**Contributors:** [Chris Reynolds](https://chrisreynolds.io)  
**Donate link:** https://paypal.me/jazzsequence  
**Requires at least:** 4.4  
**Tested up to:** 6.8  
**Stable tag:** 0.3.5  
**License:** GPLv3  
**License URI:** http://www.gnu.org/licenses/gpl-3.0.html

## Description ##

Easily store and publish recipes in WordPress.

## Installation
### Via Composer

Recipe Box can be installed on Composer-based WordPress sites by using the following command:

`composer require jazzsequence/recipe-box`

### Via Git Updater

Recipe Box supports Andy Fragen's [Git Updater](https://git-updater.com/) method of managing plugins.

1. Download and install [Git Updater](https://git-updater.com/git-updater/) on your WordPress site.
1. From the Git Updater admin pages, navigate to Install Plugin and use the following values:

**Plugin URI:** `jazzsequence/recipe-box`  
**Repository Branch:** `main`  
**Remote Repository Host:** GitHub  
**GitHub Access Token:** (optional, leave blank)

### Manual

1. Upload the entire `/recipe-box` directory to the `/wp-content/plugins/` directory.
2. Activate Recipe Box through the 'Plugins' menu in WordPress.

## Frequently Asked Questions ##


## Screenshots ##


## Changelog ##

### 0.3.5 ###
* Add installation support for [Git Updater](https://git-updater.com/).

### 0.3.4 ###
* Added additional units of measure

### 0.3.3 ###
* Add `composer.json`

### 0.3.2 ###
* fixes styling bugs from Gutenberg update

### 0.3.1 ###
* added support for [Slack](https://wordpress.org/plugins/slack/) plugin to allow recipes to post to Slack.
* fixed a javascript bug (props [@igmoweb](https://github.com/igmoweb)).

### 0.3 ###
* added moar api support
* added ability and admin page to pull recipes from remote Recipe Box site using the API
* check recipes being imported to see if they are duplicating recipes that exist on the current site.

### 0.2 ###
* added options page to determine whether recipes should be mixed with normal blog posts (and therefore easily thrown on the front page of a blog site)
* added taxonomies to display on front end
* more front-end tweaks
* added schema.org structured data
* hooked all the things
* added preheat temperature
* removed tgm plugin activation for REST API now that API is in core
*

### 0.1 ###
* First release

## Upgrade Notice ##

### 0.1 ###
First Release
