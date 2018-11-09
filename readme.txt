# Sermon Manager #
Contributors: wpforchurch, nikolam
Donate link: http://wpforchurch.com/
Tags: church, sermon, sermons, preaching, podcasting, manage, managing, podcasts, itunes
Requires at least: 4.7.0
Tested up to: 4.9
Requires PHP: 5.3
Stable tag: 2.15.8
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

### Sermon Manager Pro Features ###

* Change your look with Templates
* Multiple Podcast Support
* Divi Support & Custom Divi Builder Modules
* Custom Elementor Elements
* Custom Beaver Builder Modules
* Custom WPBakery Page Builder Modules
* Works with YOUR theme
* Page Assignment for Archive & Taxonomy
* Migration from other plugins is a breeze
* SEO & Marketing Ready
* Live Chat Support Inside the Plugin
* PowerPress Compatibility
* [Full List of Pro Features & Roadmap](https://sermonmanager.pro/)

When you upgrade to Pro you also get premium ticket and live chat support for the free version of Sermon Manager too!
*Grab your copy of Sermon Manager Pro at early adopter pricing for life between Nov 9th and Nov 23!*

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
### 2.15.8 ###
* Dev: Add callable select options (pass function name as string)
* Dev: Add a way to pass custom values to settings

### 2.15.7 ###
* Fix: PHP warning when archive output is used wrongly
* Fix: Podcast items may be sorted the wrong way

### 2.15.6 ###
* Change: Disable autocomplete for date preached, since it obstructed the view on mobile
* Fix: Comments not appearing on Divi
* Fix: All podcast images are invalid

### 2.15.5 ###
* Change: Disable check for PHP output buffering

### 2.15.4 ###
* Fix: Output Buffering detected as disabled when set to 0

### 2.15.3 ###
* New: Add option to disable "views" count for editors and admins
* New: Add option to enable sermon series image fallback in the feed
* Fix: Podcast shortcode SVG icons not working in Firefox
* Fix: Getting 404 on filtering
* Fix: Sermon Manager errors out when output buffering is disabled

### 2.15.2 ###
* Change: Add Maranatha theme support
* Change: Add Saved theme support
* Change: Add Brandon theme support
* Change: Remove default default image
* Fix: Plyr not loading when Cloudflare is used
* Fix: Sermon image not showing up
* Fix: image_size argument not working in shortcode

### 2.15.1 ###
* Fix: Multi-term filter for feeds not working

### 2.15.0 ###
* New: Add ability to override Sermon Manager's CSS by putting "sermon.css" file in theme (thanks @zSeriesGuy)
* New: Add default image during installation (thanks @zSeriesGuy)
* New: Add setting for showing and hiding the filter (shortcode and archive, thanks @zSeriesGuy)
* New: Add setting for default image (thanks @zSeriesGuy)
* Change: Update Plyr to 3.4.3
* Change: Re-organized the settings, with more descriptive options
* Fix: Fix importing from Sermon Browser stopping after first sermon
* Fix: Audio file length and size not being automatically filled
* Fix: Taxonomy archive sermons ordered by date preached
* Fix: "sermon" argument not working in shortcode
* Fix: Database errors on Import/Export screen on some hosts
* Fix: Pause button not showing up when file is being played
* Fix: "Upload Image" button not working in Podcast settings
* Fix: Audio file sometime not being correct
* Fix: Add more theme support for pagination
* Fix: Image selector in settings now showing up
* Fix: Filter not working correctly in shortcode (thanks @zSeriesGuy)
* Fix: Plyr not having border
* Dev: Update function for getting sermon image to return fallback with any option

### 2.14.0 ###
* New: Finally add support for Sermon Browser bible verses
* Change: Adjust width of Title column in admin
* Change: Organize "Debug" (now "Advanced") settings
* Change: Make filters' width shorter
* Fix: Taxonomy feed URLs not picked up by Sermon Manager
* Fix: Allow deleted imported sermons to be re-imported

Note: The rest of the changelog is in changelog.txt
