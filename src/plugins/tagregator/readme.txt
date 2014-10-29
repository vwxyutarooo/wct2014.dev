=== Tagregator ===
Contributors:      wordpressdotorg, iandunn, shaunandrews
Donate link:       http://wordpressfoundation.org
Tags:              hashtag, social media, aggregation, stream
Requires at least: 3.5
Tested up to:      4.0-beta2
Stable tag:        0.5
License:           GPLv2 or Later

Aggregates hashtagged content from multiple social media sites into a single stream.


== Description ==

Tagregator lets you add a shortcode to a post or page on your site, and pull in content from various social media networks onto that page. For example, if you add `[tagregator hashtag="#WordPress"]` into a page, then you'll see posts that mention the #WordPress hashtag.

= Included Social Media Sources: =
* Twitter
* Instagram
* Flickr
* Google+


== Installation ==

For help installing this (or any other) WordPress plugin, please read the [Managing Plugins](http://codex.wordpress.org/Managing_Plugins) article on the Codex.

**Step 1)** After installing the plugin, go to the Tagregator > Settings screen and enter the credentials for the services you want to use.

When <a href="https://dev.twitter.com/apps/new">creating a Twitter application</a>, you should enter the URL of your website in the "Website" field (e.g., `http://www.example.org`), and then leave the "Callback URL" field empty. Once the application is created, copy the Consumer Key and Consumer Secret into Tagregator's settings.

**Step 2)** [Add the [tagregator] shortcode to a post or page](http://codex.wordpress.org/Shortcode), and include the hashtag you want to aggregate:

Examples:

`[tagregator hashtag="#WordPress"]`

`[tagregator hashtag="#kiva"]`

`[tagregator hashtag="#overtherhine"]`


You can also enter keywords or search queries, like this:

`[tagregator hashtag="cooking"]`

`[tagregator hashtag="ice cream"]`

**Step 3)** Wait 30-60 seconds for the plugin to pull new content in.


== Frequently Asked Questions ==

= I setup the shortcode, but no posts have been imported =
When setting it up the first time, make sure you wait 30-60 seconds in order to let the plugin pull in the first round of posts.

= Why do posts show up with the wrong time? =
This is probably because you haven't configured your timezone in WordPress's General Settings. After updating the timezone, you may need to wait up to 23 hours for new posts to appear ahead of the ones that were saved with the old timezone.

= Why are some Tweets missing? =
Twitter's API doesn't guarantee that every tweet will be available in the results it returns.

= How should I disclose security vulnerabilities? =
If you find a security issue, please disclose it to us privately by sending an e-mail to security@wordpress.org, so that we can release a fix for it before you publish your findings.

= Can I create my own media sources for services that aren't included (e.g, Facebook, Vine, etc) =
Yes, Tagregator allows you to add custom modules that you develop for other services by hooking into the `tggr_media_sources` filter and adding an instance of your class.

The best way to get started is by [downloading the example plugin](http://plugins.svn.wordpress.org/tagregator/assets/tagregator-custom-media-source-example.zip) and customizing it to fit your needs.

Once you're done, please consider sharing it with others by [submitting it to the WordPress.org repository](http://wordpress.org/plugins/about/).

== Screenshots ==

1. An example of how the social media stream looks
2. The settings panel
3. The social media items stored as a custom post type


== Changelog ==

= v0.5 (2014-07-23) =
* [NEW] Add Google+ media source (props [fahmiadib](https://profiles.wordpress.org/fahmiadib)).
* [UPDATE] Retrieve new content immediately when the page loads.

= v0.4 (2013-12-04) =
* [FIX] Fixed a fatal PHP error on new site activation in Multisite networks.
* [FIX] Fixed a PHP notice when assigning hashtags to posts
* [NEW] Added support for Flickr.

= v0.3 (2013-10-14) =
* [FIX] Fixed "tggrData is not defined" bug.
* [NEW] New single-column design (props [shaunandrews](https://profiles.wordpress.org/shaunandrews)).
* [NEW] Instagram support added.
* [NEW] Pre-fetch media items when the shortcode is setup so they'll be available immediately.
* [NEW] Hashtags and usernames inside Tweets are automatically converted to links.
* [UPDATE] Replaced `global $post` statements with calls to `get_post()`.

= v0.2 (2013-10-09) =
* [FIX] No longer assuming that term slug matches sanitized version of term name. Fixes bug where Tagregator term would be created with "-2" and would never get posts.
* [NEW] Images attached to Tweets are now displayed.
* [NEW] Retweets are no longer imported.
* [NEW] URLs inside posts are now converted to hyperlinks.
* [UPDATE] Tweet content sanitized with wp_kses() instead of sanitize_text_field().
* [UPDATE] Moved all includes to bootstrapper.

= v0.1 (2013-09-17) =
* [NEW] Initial release


== Upgrade Notice ==

= 0.5 =
* Version 0.5 adds support for Google+.

= 0.4 = 
Version 0.4 adds support for Flickr and fixes a few bugs.

= 0.3 = 
Version 0.3 has a new single-column design and support for Instagram.

= 0.2 =
Version 0.2 displayed images attached to tweets and ignores retweets.

= 0.1 =
Initial release.