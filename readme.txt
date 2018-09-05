# Sermon Manager #
Contributors: wpforchurch, nikolam  
Donate link: http://wpforchurch.com/  
Tags: church, sermon, sermons, preaching, podcasting, manage, managing, podcasts, itunes  
Requires at least: 4.7.0  
Tested up to: 4.9  
Requires PHP: 5.3  
Stable tag: 2.14.0  
License: GPLv2  
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add audio and video sermons, manage speakers, series, and more to your church website.

## Description ##

### Sermon Manager is the #1 WordPress Sermon Plugin ###

Sermon Manager is designed to help churches easily publish sermons online. Some of the features include:

* Add Speakers, Series, Topics, Books, and Service Types
* Attach images to sermons, series, speakers, and topics
* Attach MP3 files as well as PDF, DOC, PPT (or any other type!)
* Bible references integrated via Bib.ly for easy text viewing
* Completely integrated with WordPress search
* Embed video from popular providers such as Vimeo or YouTube
* Full-featured API for developers (check it out at `/wp-json/wp/v2/wpfc_sermon`)
* Full-featured iTunes podcasting support for all sermons, plus each sermon series, preachers, sermon topics, or book of the Bible!
* Import sermons from other WordPress plugins
* PHP 5.3+ - you can use Sermon Manager even with older websites!
* PHP 7.2 ready - Sermon Manager is 100% compatible with latest PHP version
* Super flexible shortcode system
* Supports 3rd party plugins such as Yoast SEO, Jetpack, etc
* Quick and professional *free* and paid support
* Works with any theme and can be customized to display just the way you like. You’ll find the template files in the `/views` folder. You can copy these into the root of your theme folder and customize to suit your site’s design.

### One-Click Importing ###

Sermon Manager supports migration/importing from other popular sermon plugins, such as Sermon Browser and Series Engine.

This is a one click process and currently only supports migration/importing within existing WordPress installations.
Soon you will be able to migrate from those 3rd party plugins to Sermon Manager on a separate server. (for example: moving to completely new website & WordPress installation)

### Popular Shortcodes ###

* `[sermons]` — This will list the 10 most recent sermons.
* `[sermons per_page="20"]` — This will list the 20 most recent sermons.
* `[sermon_images]` — This will list all sermon series and their associated image in a grid.
* `[list_podcasts]` — This will list available podcast services with nice large buttons.
* `[list_sermons]` — This will list all series or speakers in a simple unordered list.
* `[latest_series]` — This will display information about the latest sermon series, including the image, title (optional), and description (optional).
* `[sermon_sort_fields]` — Dropdown selections to quickly navigate to all sermons in a series or by a particular speaker.

For more information on each of these shortcodes please visit [our knowledge base](https://wpforchurch.com/my/knowledgebase/12/Sermon-Manager).

### Expert Support ###

The Sermon Manager is available as a FREE download however in order to maintain a free version we offer [premium support packages](https://wpforchurch.com/wordpress-plugins/sermon-manager/#pricing) for those who need any custom assistance. Paid support means you get exclusive access to the Sermon Manager forum as well as support tickets. This is also a way you can donate to the project to help us offer prompt support and a free version of the plugin.

You can access the paid support options via [our website](http://wpforchurch.com/).

Bug fixing and fixing unexpected behavior *is free* and *always will be free*. Just [make an issue on GitHub](https://github.com/WP-for-Church/Sermon-Manager/issues/new) or [create a support thread on WordPress](https://wordpress.org/support/plugin/sermon-manager-for-wordpress#new-post) and we will solve it ASAP.

### Developers ###

Would you like to help improve Sermon Manager or report a bug you found? This project is open source on [GitHub](https://github.com/WP-for-Church/Sermon-Manager)!

(Note: Please read [contributing instructions](https://github.com/WP-for-Church/Sermon-Manager/blob/dev/CONTRIBUTING.md) first.)

### WP for Church ###

* [WP for Church](https://wpforchurch.com/) provides plugins and responsive themes for churches using WordPress.
* Keep up with the latest product news & tips, sign up to our [newsletter](https://www.wpforchurch.com/blog)!

## Installation ##

Installation is simple:

1. Just use the “Add New” button in Plugin section of your WordPress blog’s Control panel. To find the plugin there, search for `Sermon Manager`
2. Activate the plugin
3. Add a sermon through the Dashboard
4. To display the sermons on the frontend of your site, just visit the `http://yourdomain.com/sermons` if you have pretty permalinks enabled or `http://yourdomain.com/?post_type=wpfc_sermon` if not. Or you can use the shortcode `[sermons]` in any page.

## Frequently Asked Questions ##

### How do I display sermons on the frontend? ###

Visit the `http://yourdomain.com/sermons` if you have pretty permalinks enabled or `http://yourdomain.com/?post_type=wpfc_sermon` if not. Or you can use the shortcode `[sermons]` in any page or post.

### How do I create a menu link? ###

Go to Appearance → Menus. In the “Custom Links” box add `http://yourdomain.com/?post_type=wpfc_sermon` as the URL and `Sermons` as the label and click “Add to Menu”.

### I wish Sermon Manager could... ###

We are open to suggestions to make this a great tool for churches! Submit your feedback at [WP for Church](https://feedback.userreport.com/05ff651b-670e-4eb7-a734-9a201cd22906/)

### More Questions? ###

Visit the [plugin homepage](https://wpforchurch.com/wordpress-plugins/sermon-manager/ "Sermon Manager homepage")

## Screenshots ##
1. Sermon Details
2. Sermon Files

## Changelog ##
### 2.14.0 ###
* Add: Finally add support for Sermon Browser bible verses
* Change: Adjust width of Title column in admin
* Change: Organize "Debug" (now "Advanced") settings
* Change: Make filters' width shorter
* Fix: Taxonomy feed URLs not picked up by Sermon Manager
* Fix: Allow deleted imported sermons to be re-imported

### 2.13.4 ###
* Change: Update Plyr to latest
* Fix: Small bug in media seeking URL detection
* Fix: PHP notice when non-existing taxonomy used in feed URL
* Fix: URL-encode atom:link in podcast feed

### 2.13.3 ###
* New: Add an option to use native player in Safari
* Change: Revert Plyr for Safari browser

### 2.13.2 ###
* New: Add an option to show date "Published" instead of date "Preached" in feed and frontend
* Change: Use native player in Safari
* Change: Separate "Preached" and "Published" dates in admin view
* Fix: Excerpt meta box not showing up
* Fix: Fix description not showing in the podcast feed
* Fix: Fix memory leak when site has big number of sermons
* Fix: Use non-localized dates in the RSS feed
* Fix: Fix issues with Sermon Browser importing

### 2.13.1 ###
* New: Add `list_podcasts` shortcode (thanks @macbookandrew!)
* New: Support for OceanWP theme (thanks @zSeriesGuy!)
* Fix: Archive page slug not applying
* Fix: Feed showing PHP notice in some rare cases
* Fix: Taxonomy list/images ignoring arguments
* Fix: Wrappers do not get overriden (thanks @zSeriesGuy!)

### 2.13.0 ###
* New: Add a simpler way of overriding sermon render
* New: Add excerpt support (thanks @robertmain!)
* New: Add read more link to the sermon description (thanks @robertmain!)
* New: Add revisions support (thanks @robertmain!)
* New: Add support for custom WP role capabilities (thanks @zSeriesGuy!)
* New: Add support for sermon password protection
* New: Add working file for rendering the feed
* New: Add a tab in settings for controlling the import
* Change: Add more options to the recent sermons widget
* Change: Add a way to get sermon's series image
* Change: Add an option to hide read more when it's not needed
* Fix: Audio download button glitches sometimes
* Fix: Custom preacher label in menu lowercased when label is in lowercase
* Fix: Feed not validating when audio files use SSL
* Fix: Fix spacing on Divi theme
* Fix: Image size shortcode argument not working
* Fix: MP4 video file being detected as YouTube and therefore not working
* Fix: SB image import breaking when image is local and does not exist on filesystem
* Fix: Sermon Browser services import
* Fix: Sermon Details meta not loading under very specific circumstances
* Fix: Sermons do not appear if published via API and "Date Preached" not set
* Fix: Sermons not showing in shortcode under certain timezone conditions
* Fix: Sermons menu title is "All Sermons" instead of "Sermons"
* Fix: Shortcode pagination not working when "Plain" permalinks are used
* Fix: Shortcodes showing in iTunes sermon description
* Fix: Taxonomy image assignment not working
* Fix: Title not being in the same line, even though there's enough space
* Dev: Add a filter for filtering sermon image size
* Dev: Add more hooks
* Dev: Add PHPUnit configuration
* Dev: Add support for WordPress attachment ID for sermon audio files
* Dev: Add WPCS configuration
* Dev: All terms now support ordering by latest sermon
* Dev: Deprecate most of old podcasting functions
* Dev: Refactor widgets code

Note: The rest of the changelog is in changelog.txt
