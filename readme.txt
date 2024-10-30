=== CloudSwipe :: Ecommerce with security ===
Contributors: reality66,
Donate link: http://cloudswipe.com
Tags: ecommerce, e-commerce, shopping, cart, store, download, digital, downloadable, sell, inventory, shipping, tax, donations products, sales, shopping cart
Requires at least: 2.8.2
Tested up to: 3.5
Stable tag: 1.1.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

CloudSwipe is a WordPress e-commerce plugin that lets you sell digital and physical products with a strong focus on security and PCI compliance.

== Description ==

CloudSwipe will turn any WordPress site, using any theme into a full featured, secure e-commerce store. One of the largest hurdles to overcome when launching an e-commerce site is handling the security. You need to accept credit card payments but it is very complicated and expensive to make sure your site is secure and PCI compliant. It is not enough to simply install an SSL certificate or get a security scan. There are many other [requirements for PCI compliance](http://blog.cloudswipe.com/what-you-need-to-know-about-pci-compliance/ "PCI Compliance").

[vimeo http://vimeo.com/51950175]

= Features =

CloudSwipe provides a full set of e-commerce features including:

- All the security you need (SSL, PCI, etc.)
- Digital products
- Products with unlimited variations
- Tons of payment gateways (39 gateways in 67 countries and counting...)
- [PageSlurp](https://cloudswipe.com/seamless-experience "How CloudSwipe Works") so your secure pages look just like the rest of your store
- Taxes
- Shipping
- Coupons
- Promotions
- Order management and fulfillment
- and more..

== Screenshots ==
1. Products: showing a product with a few options
2. Taxes: showing rates and region editor
3. Payment gateways

== Installation ==

= Minimum Requirements =

* WordPress 2.8.2 or greater
* PHP version 5.2.4 or greater
* MySQL version 5.0 or greater

= Automatic installation =

You can install CloudSwipe directly from the WordPress.org plugin directory withouth having to worry with manually transfering files to your server. Automatic installation is the easiest option for installation. Let WordPress install CloudSwipe for you.

To install CloudSwipe, log in to your WordPress admin panel, go to the Plugins menu and click the "Add New" link.

Type "CloudSwipe" in the search field then click the "Search Plugins" button. You will see the CloudSwipe plugin in the seartch results. To install CloudSwipe simply click the "Install Now" link. After confirming that you do want to install the plugin, WordPress will automatically download and install CloudSwipe directly on your WordPress website.

Once the CloudSwipe is installed, you may want to read our [Getting Started With CloudSwipe](http://docs.cloudswipe.com/cloudswipe-quick-start-guide/) guide for more information on getting started.

= Manual installation =

If you would prefer to manually install CloudSwipe, you will need to download the CloudSwipe plugin and upload it to your webserver via FTP. Here are the steps to follow:

1. Download and unzip the plugin file on your computer
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to the plugin directory on your WordPress site (wp-content/plugins/<cloudswipe>).
3. Activate the CloudSwipe plugin from the "Plugins" menu in your WordPress admin panel.

= Upgrading =

When new versions of CloudSwipe are released, WordPress will notify you about the available update. You can update to the latest version of the CloudSwipe plugin by selecting to do an automatic updates. It is alsways a good idea to backup your site before doing any updates of any kind including updating plugins.

CloudSwipe will remember all of your settings and products after updating so you will not have to re-enter your product information or anythingn else.

== Frequently Asked Questions ==

= Where can I find CloudSwipe documentation? =

You will find detailed instructions in our [CloudSwipe documentation](http://docs.cloudswipe.com). You may also be interested in seeing our [tips and tricks for CloudSwipe e-commerce](http://docs.cloudswipe.com/topics/tips-and-tricks/) as well as [our blog](http://blog.cloudswipe.com).

= Where can I get cool ideas on how to use CloudSwipe? =

Checkout our [tips and tricks](http://docs.cloudswipe.com/topics/tips-and-tricks/) section of our website for interesting ideas like how to add e-commerce to your email newsletters and your social media.

= What is PageSlurp? =

PageSlurp is the technology that we invented to provide a secure and PCI compliant way for us to run your WordPress them on our secure servers so that all your secure pages look just like the rest of your website. For more details on PageSlurp, see [How it works](https://cloudswipe.com/tour)

= What is PCI compliance? =

PCI Compliance is a set of requirements that your business needs to meet in order to accept credit card payments. If you are using CloudSwipe for your e-commerce, then your website is PCI compliant. If you are not using CloudSwipe then you need to handle all of the PCI requirements on your own. Becoming PCI compliant on your own is very expensive and challenging. For more information about PCI compliance, see [What you need to know about PCI compliance](http://blog.cloudswipe.com/what-you-need-to-know-about-pci-compliance/ "PCI Compliance").

== Changelog ==

= 1.1.1 - 2/21/2013 =

- Fixing WP_Error during page slurp caused by some themes attempting to discover the post category for the page slurp template

= 1.1 - 2/8/2013 =

- Adding support for custom subdomains
- Updating CSS for more control over styling
- Adding additional internal features to support theme development

= 1.0.6 - 1/7/2013 =

- The AJAX add to cart notification now contains a view cart button
- The AJAX add to cart notification now says the name of the product that was added to the cart

= 1.0.5 - 12/30/2012 =

- Sending location data when creating carts. When viewing live carts in your CloudSwipe account you can now see where your customers are located in the world.

= 1.0.4 - 12/04/2012 =

- Fixing PHP warnings on CloudSwipe settings page caused by failure to retrieve alternate page templates

= 1.0.3 - 11/30/2012 =

- Adding the ability to add products to the cart via AJAX
- Adding setting for turning debug logging on and off
- Fixing error message about ssl certificate validation failing on some servers

= 1.0.2 - 11/27/2012 =

- Fixing problem where ssl verification would fail on some servers preventing successful api communication

= 1.0.1 - 11/26/2012 =

- Updating CloudSwipe API URLs to use SSL

= 1.0 - 11/01/2012 =

- Initial release of the CloudSwipe plugin for WordPress e-commerce

== Upgrade Notice ==

= 1.0 =
1.0 is the initial release of the CloudSwipe plugin for WordPress e-commerce
