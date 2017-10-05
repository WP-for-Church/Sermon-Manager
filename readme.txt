# Sermon Manager #
Contributors: wpforchurch, nikolam  
Donate link: http://wpforchurch.com/  
Tags: church, sermon, sermons, preaching, podcasting, manage, managing, podcasts, itunes  
Requires at least: 4.5  
Tested up to: 4.8.2  
Requires PHP: 5.3  
Stable tag: 2.8.2
License: GPLv2  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Add audio and video sermons, manage speakers, series, and more to your church website.

## Description ##

### Sermon Manager is the #1 WordPress Sermon Plugin ###

Sermon Manager is designed to help churches easily publish sermons online. The features include:

* Add Speakers, Series, Topics, Books and Service Types
* Attach MP3 files as well as PDF, DOC, PPT (or any other type!)
* Embed video from popular providers such as Vimeo or YouTube
* Attach images to sermons, series, speakers and topics
* Super flexible shortcode system
* Bible references integrated via Bib.ly for easy text viewing
* Works with any theme and can be customized to display just the way you like. You'll find the template files in the `/views` folder. You can copy these into the root of your theme folder and customize to suit your site's design.
* Full-featured iTunes podcasting support for all sermons, plus each sermon series, preachers, sermon topics, or book of the Bible!
* Full-featured API for developers (check it out at /wp-json/wp/v2/wpfc_sermon)
* Jetpack support
* Completely integrated with WordPress search
* PHP 7 ready - Sermon Manager is 100% compatible with latest PHP version
* Quick and professional support

### Popular Shortcodes ###

* `[sermons]` - This will list the 10 most recent sermons.
* `[sermons per_page="20"]` - This will list the 20 most recent sermons.
* `[sermon_images]` - This will list all sermon series and their associated image in a grid.
* `[list_sermons]` - This will list all series or speakers in a simple unordered list.
* `[latest_series]` - This will display information about the latest sermon series, including the image, title (optional), and description (optional).
* `[sermon_sort_fields]` - Dropdown selections to quickly navigate to all sermons in a series or by a particular speaker.

For more information on each of these shortcodes please visit [our knowledge base](https://wpforchurch.com/my/knowledgebase/12/Sermon-Manager).

### Expert Support ###

The Sermon Manager is available as a FREE download however in order to maintain a free version we offer [premium support packages](https://wpforchurch.com/wordpress-plugins/sermon-manager/#pricing) for those who need any custom assistance. Paid support means you get exclusive access to the Sermon Manager forum as well as support tickets. This is also a way you can donate to the project to help us offer prompt support and a free version of the plugin.

You can access the paid support options via [our website](http://wpforchurch.com/).

Bug fixing and fixing unexpected behavior *is free* and *always will be free*. Just [make an issue on GitHub](https://github.com/WP-for-Church/Sermon-Manager/issues/new) or [create a support thread on WordPress](https://wordpress.org/support/plugin/sermon-manager-for-wordpress#new-post) and we will solve it ASAP.

### Developers ###

Would you like to help improve Sermon Manager or report a bug you found? This project is open source on [GitHub](https://github.com/WP-for-Church/Sermon-Manager)

Note: Any contribution via pull request will have a note with submitter's username in changelog, example: "(thanks @nikola3244)".

### WP for Church ###

* [WP for Church](https://wpforchurch.com/) provides plugins and responsive themes for churches using WordPress.
* Keep up with the latest product news & tips, sign up to our [newsletter](https://www.wpforchurch.com/blog)!

## Installation ##

Installation is simple:

1. Just use the "Add New" button in Plugin section of your WordPress blog's Control panel. To find the plugin there, search for `Sermon Manager`
2. Activate the plugin
3. Add a sermon through the Dashboard
4. To display the sermons on the frontend of your site, just visit the http://yourdomain.com/sermons if you have permalinks enabled or http://yourdomain.com/?post_type=wpfc_sermon if not. Or you can use the shortcode `[sermons]` in any page.

## Frequently Asked Questions ##

### How do I display sermons on the frontend? ###

Visit the http://yourdomain.com/sermons if you have permalinks enabled or http://yourdomain.com/?post_type=wpfc_sermon if not. Or you can use the shortcode `[sermons]` in any page.

### How do I create a menu link? ###

Go to Appearance => Menus. In the "Custom Links" box add "http://yourdomain.com/?post_type=wpfc_sermon" as the url and "Sermons" as the label and click "Add to Menu".

### I wish Sermon Manager could... ###

We are open to suggestions to make this a great tool for churches! Submit your feedback at [WP for Church](https://feedback.userreport.com/05ff651b-670e-4eb7-a734-9a201cd22906/)

### More Questions? ###

Visit the [plugin homepage](https://wpforchurch.com/wordpress-plugins/sermon-manager/ "Sermon Manager homepage")

## Screenshots ##
1. Sermon Details
2. Sermon Files

## Changelog ##
### 2.8.3 ###
* Fix sermon audio not showing up
* Fix styling of attachment links

### 2.8.2 ###
* Disable error recovery since it's causing too many issues
* Couple bugfixes for PHP 5.3

### 2.8.1 ###
* Fix a PHP notice on new sermon creation
* Fix sermon date not saving on first save
* Fix sermons preached before 09/09/2001 not showing up
* Fix confusion regarding date field (remove placeholder value)

### 2.8 ###
* Add better support for templating plugins (Elementor and Visual Composer, to mention some)
* Add better compatibility with themes and search
* Add more stable code for updating database structure
* Add support for PHP 5.5, 5.4 and 5.3, but we still recommend to use at least PHP 5.6
* Modify: Show all options in filtering dropdown - even if they don't have associated sermon
* Modify: All language strings rewritten (huge thanks to @GITNE)
* Fix styles not loading when "[sermons]" shortcode is used without filters
* Fix image_size not working in "[sermons]" shortcode
* Fix compatibility with PHP 5.3
* Fix Recovery catching 3rd party errors
* Fix 404 when "Common Base Slug" option is changed
* Fix admin menu icon not being blue on hover
* Fix HTML syntax for filtering

### 2.7.2 ###
* Fix 404 on series, preacher, topic, book and service type pages

### 2.7.1 ###
* Fix shortcode ordering/no sermons
* Fix crash on servers that do not have MBString extension
* Fix sermon page sometimes giving 404

### 2.7 ###
* Add WordPress search support to sermons
* Add Jetpack Publicize and Omnisearch support to sermons
* Add REST API support
* Add automagical error recovery - no more unusable websites
* Add support for different plugin languages
* Make "Date Preached" field optional
* Fix sermon ordering
* Fix new sermons not appearing
* Fix widget styling
* Fix new sermons not working on some servers
* Fix sermon time not being correct when using "Date Preached"
* Fix filtering/sorting
* Lots of minor bug fixes
* General performance and compatibility fixes

### 2.6.2 ###
* Add more `<span>` elements for more styling options
* Fix podcast feed not working

### 2.6.1 ###
* Fix fatal error on old PHP versions
* Fix custom preacher slug not working

### 2.6 ###
* Fix PHP warning regarding sorting
* Fix Taxonomy images not working on initial plugin install (and sometimes for existing ones as well - needs plugin re-activation)
* Fix space character is being URL encoded twice
* Add more filters to the shortcode
* Add back Sermon dates

### 2.5.2 ###
* Change preacher slugs as well when changing preacher label in settings
* Fix spelling error (thanks @tythewebguy)
* Fix filtering not working on a subpage
* Fix a security concern (GitHub issue #87)
* Fix preacher image upload button & title not respecting preacher label
* Fix wrong dialog title for Sermon Series image picker
* Fix archive slug change needs saving of settings two times
* Fix taxonomy image errors on first plugin install
* Fix copyright symbol not appearing correctly

### 2.5.1 ###
* Fix fatal error on older PHP versions

### 2.5.0 ###
* Add media functions in the sermon editor
* Add options to filtering shortcode
* Fix filtering shortcode sometimes making `404` on filter
* Fix PHP warning not dismissible
* Tweak sermon audio length field description (thanks @macbookandrew)

### 2.4.11 ###
* Fix paragraphs in podcast feed description
* Fix podcasts not validating with exotic mp3 file names
* Fix double URLs appearing in sermon downloads
* Fix sermons not appearing in shortcodes

### 2.4.10 ###
* Fix filtering styling being broken

### 2.4.9 ###
* Fix new sermons not appearing
* Fix couple PHP warnings
* Fix filtering in shortcode

### 2.4.8 ###
* Fix featured image not working

### 2.4.7 ###
* Fix PHP notice in the footer
* Fix slashes in RSS feed
* Fix podcast cover image not selecting
* Fix first sermon image showing up as podcast cover image when none selected
* Fix all dates issues (please allow the page to load after the update, it will take a minute; if you stop the load, please open `<your website URL>/wp-admin/?sm_fix_dates`)
* Removed "Date" field (left side) in Sermon edit screen, "Published" date (right side) will be used from now on
* Removed "Fix Dates" option tab, it's not necessary anymore
* Removed all warnings and error related to dates
* Replaced old media manager with new one for podcast cover image

### 2.4.6 ###
* Add a setting to define podcast count
* Fix a bug that caused WSOD on some hosts
* Fix a bug that prevented settings to save on some hosts

### 2.4.5 ###
* Fix error that rendered website unusable on some hosts
* Fix double line-breaks in the podcast description not changing into HTML paragraphs
* Make new podcast HTML tags option disabled by default

### 2.4.4 ###
* Fix shortcode pagination broken on front page (thanks @antgiant)
* Fix new audio player not loading on some old themes
* Add an option to show key verse in "Recent Sermons" widget
* Add HTML tags display in podcast description (can be disabled in settings)
* Improve scripts & styles loading (website performance improvements)

### 2.4.3 ###
* Fix HTML formatting
* Fix new player duration always being 0:00 on initial load
* Fix Sermon date breaking the website in some special circumstances

### 2.4.2 ###
* Fix Sermon Manager interfering with regular post dates under some circumstances
* Fix fatal error when using UpdraftPlus
* Fix fatal error for longtime Sermon Manager users (thank you <3)
* Fix audio URL duplication in "Download Files" section
* Fix date checking URL
* Fix scripts and styles not loading when shortcode is used in ordinary posts
* Fix new player styles not loading

### 2.4.1 ###
* Fix fatal error for podcasts

### 2.4.0 ###
* Add a setting for Sermon date format
* Add a filter for changing sermon image link (thanks @macbookandrew)
* Add Polish translation (thanks @GITNE)
* Fix podcast feed not validating because of an non URL encoded ampersand in the Sermon image URL
* Remove sermons that don't have mp3 files assigned to them from podcast XML file
* Performance improvements
* Miscellaneous bug fixes

### 2.3.4 ###
* Improve backend performance
* Fix topics list in backend sometimes being corrupted

### 2.3.3 ###
* Fix a typo in "Sort by Preacher" table column
* Fix a PHP warning when a sermon is created with Service Type set to "None"
* Fix Sermon Audio removing/uploading in backend not working

### 2.3.2 ###
* Allow displaying of audio and video at the same time
* Fix new audio player conflicting with other elements
* Fix Bib.ly not showing on sermon archive pages
* Fix Bib.ly not loading over HTTPS

### 2.3.1 ###
* Fix shortcodes compatibility issue
* Fix `[sermons]` shortcode ordering

### 2.3.0 ###
* Add an option to change "Preacher" to whatever user wants ("Teacher", "Speaker"...)
* Fix series image shortcode not working properly
* Fix disappeared bible passage
* Core improvements

### 2.2.0 ###
* Add `orderby` argument for `[sermons]` shortcode. How to use: https://wpforchurch.com/my/knowledgebase/75/Sermon-Manager-Shortcodes.html
* Add an option to switch between the old and the new MP3 player
* Fix some cases where PodCast feed is invalid

### 2.1.5 ###
* Fix crash on PHP <5.5 (for real this time)
* Fix sermon MP3 file name while downloading

### 2.1.4 ###
* Fix crash on PHP <5.5

### 2.1.3 ###
* Fix podcast feed date being invalid when used on website with non English language
* Fix crashing on older PHP versions

### 2.1.2 ###
* Allow Sermon Manager usage on old PHP versions
* Fix shortcode not working

### 2.1.1 ###
* Fix plugin crashing. Add missing files.
* Remove old screenshots

### 2.1.0 ###
* Fix book sorting bug
* Fix issue where podcast enclosures are sometimes duplicated
* Fix sermon topics custom view not loading
* Fix dashboard icon not displaying
* Fix not displaying correct sermon dates
* Fix parenthesis displaying when service type is not specified
* Fix bib.ly not loading over HTTPS
* Add date fixing feature - should fix most of date-related bugs
* Add a new player for MP3 files
* Add PodTrac Support (thanks @reykroona)
* Add warning if user is using PHP version less than 5.6.0
* Simplify shortcodes

### 2.0.8 ###
* Fix another date error
* Revert fix for "fatal error with date processing", since it caused issues for some users
* Fix video width being too wide
* Fix microphone issue

### 2.0.7 ###
* Fixed fatal error with date processing
* Fixed some PHP 7 warnings
* Added better shortcode book sorting

### 2.0.6 ###
* Fix date warning in the `Date Preached` column
* Fix downloads not showing notes and bulletin
* Fix taxonomy images not working on new installs
* Fix various bug fixes with feeds
* Fix Yoast SEO XML sitemap not working with SM - Please re-save settings and permalinks
* Fix image chooser not opening correctly when user clicks on series/preacher image (browser cache might need to be cleared)

### 2.0.5 ###

* Fix: Sermon Archive shortcode wasn't displaying properly

### 2.0.4 ###

* Fixed error where saving anything in admin caused a php notice
* Fixed setting page and notice to re-save settings even after user had saved
* Updated sermon archive to show new sermon sorting fields function

### 2.0.3 ###

* Fixed error causing sermons views to not count
* Fixed several issues with classes being called improperly

### 2.0.2 ###

* Fixed legacy support and functionality for video embed
* Fixed podcast error causing duplicate entries

### 2.0.1 ###

* Fixed backwards compatibility issues
* Fixed issue with service type dropdown

### 2.0 ###

* Massive update to core code with php7 compatibility
* Bulletin file upload
* Updated dependency libraries
* Updated Views
* Use YouTube/Vimeo link (with legacy support for old video embeds)

### 1.9.6 ###

* Bugfix podcast title is fixed in WP 4.4
* Multiple non-fatal errors have been fixed

### 1.9.5 ###
* Bugfix podcast title is taken from Settings Page
* Bugfix podcast no longer has any missing sermons
* Bugfix properly strip html from podcast summary

### 1.9.4 ###
* Bugfix trailing / removed from podast feed
* Bugfix don't include enclosure in podcast if there is no audio file
* Bugfix `[list_sermons]` order/orderby attributes now work as expected
* Bugfix hide_nav works correctly in the `[sermons]` shortcode
* Bugfix setting defaults on activation now works correctly

### 1.9.3 ###
* Now actions are used to load sermon content in a theme: sermon_single & sermon_excerpt. This makes it easy to override the content by removing the default action and adding your own custom action.
* Bugfix `[sermon_images]` shortcode now displays the description correctly
* Added podcast feed for service types

### 1.9.2 ###
* Improve setting MP3 duration; allow user to edit duration if not set accurately

### 1.9.1 ###
* Minor fixes to the `[latest_series]` shortcode - [updated documentation](http://www.wpforchurch.com/knowledgebase/sermon-manager-shortcodes/)

### 1.9 ###
* NEW FEATURE: Podcast feeds for every Preacher, Service Type, Series, Bible Book, and Topic.
* NEW FEATURE: New shortcode to display the latest sermon series image `[latest_series]` (many options including displaying by service type)
* NEW FEATURE: Admin columns are now sortable - props to @khornberg
* NEW FEATURE: All media is now uploaded to a custom folder `/sermons` under `/uploads`. This will allow easier media management and exclusion from backups (if desired)
* Remove mediaelement audio player and use the built in mediaelement (now requires WordPress 3.6+)
* Remove dependency on wp-pagenavi for shortcode pagination
* Resolved issue with media player not displaying with shortcodes


### 1.8.3 ###
* Require WordPress 3.6+
* Use built in mediaelement player

### 1.8.1 ###
* Fixed errors with saving settings for some users (remove dependency on CURL)

### 1.8 ###
* Improved podcasting performance - props @livingos
* Cleaned up options page with tabs, added hooks for other plugins to hook into the option page.
* Fixed bug causing sermons to display 2x

### 1.7.4 ###
* Updated the way attachments are displayed. Now they will be available for download even if not attached to the sermon.
* Fix sermon-images shortcode

### 1.7.3 ###
* Compatibility with WordPress 3.6

### 1.7.2 ###
* Disable a filter that was causing problems in some themes (added in 1.7)

### 1.7.1 ###
* Fix a few bugs introduced in 1.7

### 1.7 ###
* Improved many areas of the code; organized files
* Made a new permalink structure possible with a common base slug across all taxonomies, e.g. `sermons/preacher` or `sermons/series`.
* Added new template tag for the podcast url
* Add series, preacher, topic, and book to post class
* Trim taxonomy description in Admin
* Improve widget CSS
* Add missing filter for template files
* Add template tags to show preacher and series info on individual sermons
* Allow service type to be empty
* Use date option set in WordPress settings instead of hardcoded format
* Resolve `$wpfc_entry_views` error with PHP 5.4
* Cleaned up CSS ids and classes to be compliant code

### 1.6 ###
* Improved localization & added French translation
* Updated mediaelements.js to the latest version
* Change Service Types to a custom taxonomy so you can add/edit as you wish (you'll see an admin notice to refresh your database)

### 1.5.6 ###
* Added comma separator in case of multiple speakers or multiple series
* Added speaker name to widget
* Added "sort by Book" to sermon sort fields

### 1.5.5 ###
* Fix settings for bib.ly

### 1.5.4 ###
* Added an action `wpfc_settings_form` to add fields to the settings page
* Fixed bug with sermon topic dropdown

### 1.5.3 ###
* Properly prefixed the entry views function to prevent conflicts

### 1.5.2 ###
* Only load admin scripts and styles on Sermon pages

### 1.5.1 ###
* Improve CSS for Chrome
* Add the option to include the audio player in archive view
* Fix display issues on some themes in archive view

### 1.5 ###
* Improve page navigation styles with shortcode
* Improve admin interface & added a "Sermon Notes" field
* Fixed the views count for sermons
* Update function to add images to series & preachers
* Added podcasting with iTunes
* Properly enqueueing all JavaScript and CSS
* New template tags for easier theme customization
* Added new taxonomy "Book of the Bible" to allow easy sorting of sermons
* Display a grid of images assigned to speakers or series with a new shortcode `[sermon-images]`
* Display a list of sermon series, preachers, sermon topics, or book of the Bible with a new shortcode `[list-sermons]`

### 1.3.3 ###
* Bug fix with menu not showing in some themes

### 1.3.1 ###
* Bug fix with Service Type not saving correctly

### 1.3 ###
* Added a settings page
* Now translation ready!
* Added styling to the Recent Sermons Widget
* Added featured image to individual sermons
* Added images to sermon topics
* Created new functions to render sermon archive listing and single sermons
* Added better sorting fields on archive page
* Added shortcode to insert sort fields - `[sermon_sort_fields]`

### 1.2.1 ###
* Enhanced shortcode to allow for Ajax pagination
* Requires a plugin for pagination in shortcode to work: http://wordpress.org/extend/plugins/wp-pagenavi/

### 1.2 ###
* Shortcode completely updated with [documentation](http://www.wpforchurch.com/882/sermon-shortcode/)

### 1.1.4 ###
* Now you can add images to sermon series and preachers!
* Widget now includes the sermon date
* Added icons for audio and video attachments

### 1.1.3 ###
* Theme developers can add support for sermon manager to their theme with `add_theme_support( 'sermon-manager' );` in `functions.php`. For now, this will disable the loading of the jwplayer javascript
* Bug fix to load javascript for sermon player and verse popups on single sermon pages only
* Minor CSS fix to increase font size of popup Bible passages

### 1.1.2 ###
* Bug fixes so everything saved correctly when doing autosave, quick edit, and bulk edit
* Minor CSS fix for icon to display with additional files

### 1.1.1 ###
* Bug fixes to templating system
* Minor CSS fixes

### 1.1 ###
* New much improved templating system!
* Bug fixes related to the loading of JavaScript and CSS

### 1.0 ###
* Fixes related to WordPress 3.3; takes advantage of new tinymce editor

### 0.9 ###
* Added WYSIWYG editor to the sermon description field

### 0.8 ###
* Added Widgets

### 0.7 ###
* Bug Fixes

### 0.6 ###
* Initial public release
