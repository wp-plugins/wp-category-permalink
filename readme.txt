=== WP Category Permalink ===
Contributors: TigrouMeow, fryaniv
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=H2S7S3G4XMJ6J
Tags: category, permalink, woocommerce
Requires at least: 3.5
Tested up to: 4.2.0
Stable tag: 2.2.3

Allows manual selection of a 'main' category for each post and WooCommerce product for better permalinks and SEO.

== Description ==

This plugin allows you to select a main category for your posts for better permalinks and SEO. It uses the same meta data as the "Hikari Category Permalink" and the "sCategory Permalink", but it has been rewritten using better and cleaner code. 
	
The chosen category is shown in bold on the 'Posts List' page and the 'Post Edit' page. You can select a different permalink category on the 'Post Edit' page using the 'Categories' box (hover over the categories and click on 'Permalink' to select one, then save your post).

= WooCommerce Support =
This plugin can also support WooCommerce products in its Pro version (unlocked through a serial). For more information about the Pro version, please visit the official page of the plugin here: http://apps.meow.fr/wp-category-permalink/.

Languages: English, French.

== Installation ==

1. Upload `wp-category-permalink.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How are subcategories handled? =
Subcategories keeps their parent's slug. For example, suppose you've chosen Pants > Jeans as your permalink, then your %product_cat% placeholder would be replaced with `http://.../pants/jeans/...`. The same goes for posts.

= I've added a category while writing a post but I can't "Permalink" it. What should I do? =
Update your post or product and you'll be able to "Permalink" it.

= Where can I add or edit the product's slug? =
Due to WordPress framework constraints, a product slug cannot be edited in "live" like a post slug. In case you want to add or edit a product slug - click on the "Screen Options" button appearing on the top right of the product editor page. Select the slug checkbox, and then add or edit your product's slug.

= Why do my product's category permalink doesn't have a similar product permalink structure? =
Make sure you've set your product category base to the same base as your product permalink base. For example, if your product permalink base is `/shop/%product_cat%`, then your product category base should be `shop`.

= Do you support WooCommerce permalink without a fixed base, e.g. `http://www.example.com/%product_cat%/...` instead of `http://www.example.com/fixed-base/%product_cat%/...`? =
Unfortunately, at the moment the plugin doesn't support this feature.

= Do you support WooCommerce breadcrumbs? =
Unfortunately, at the moment the plugin doesn't support this feature.

= Can I contact you? =
Please come on the support forums. We are two developers working on this plugin and Yaniv added support for WooCommerce. We will help you!

= I donated, can I get rid of the donation button? =
Of course. I don't like to see too many of those buttons neither ;) You can disable the donation buttons from all my plugins by adding this to your wp-config.php:
`define('WP_HIDE_DONATION_BUTTONS', true);`

== Screenshots ==

1. Pick the category you'd like to have for the permalink.

== Changelog ==

= 2.2.3
* Fix: Product permalink bug (permalink appeared correctly only on product page).

= 2.2.2 =
* Fix: Restored post behaviour.
* Fix: Fixed warnings.

= 2.2.0 =
* Fix: Tags structure issue.

= 2.1 =
* Fix: Fixed subcategory and post type issues.

= 2.0 =
* Fix: Update issue for non-WooCommerce user.
* Change: Let's make it 2.0 since it's a major change.

= 1.7 =
* Fix: Woocommerce compatibility.

= 1.6 =
* Fix: Woocommerce products page.

= 1.4 =
* Links, readme, version.

= 1.2 =
* Works with WordPress 4.0.

= 1.1.0 =
* Nothing major updated.
* New version number + readme + information.

= 1.0 =
* Stable release.

= 0.1.4 =
* Add: default category will be shown in red if no category was picked.

= 0.1 =
* First release.

== Wishlist ==

Do you have suggestions? Feel free to contact me at <a href='http://www.totorotimes.com'>Totoro Times</a>.