# Sermon Manager #
Contributors: wpforchurch, nikolam  
Donate link: http://wpforchurch.com/  
Tags: church, sermon, sermons, preaching, podcasting, manage, managing, podcasts, itunes  
Requires at least: 4.5  
Tested up to: 4.9
Requires PHP: 5.3  
Stable tag: 2.12.4  
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
* PHP 7.1 ready - Sermon Manager is 100% compatible with latest PHP version
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
4. To display the sermons on the frontend of your site, just visit the `http://yourdomain.com/sermons` if you have permalinks enabled or `http://yourdomain.com/?post_type=wpfc_sermon` if not. Or you can use the shortcode `[sermons]` in any page.

## Frequently Asked Questions ##

### How do I display sermons on the frontend? ###

Visit the `http://yourdomain.com/sermons` if you have permalinks enabled or `http://yourdomain.com/?post_type=wpfc_sermon` if not. Or you can use the shortcode `[sermons]` in any page.

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
### 2.13.0 ###
* New: Add setting to disable images on archive and/or single sermon pages
* Change: Add Sermon image to single sermon view
* Change: Add `hide_title` parameter for `[sermon_images]` shortcode
* Fix: Redundant forward slash in shortcode pagination
* Fix: `[sermon_images]` lacking CSS

### 2.12.4 ###
* Fix: Series image size is small on sermon image output
* Fix: "Psalms" book spelling in book ordering
* Fix: Attachment box styling
* Fix: Attachment box showing up when there's media file, but nothing else
* Fix: No margin between sermons on `[sermons]` shortcode output
* Fix: Sermon image padding on sermons in `[sermons]` shortcode

Note: The rest of the changelog is in changelog.txt
