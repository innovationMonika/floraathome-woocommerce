=== Flora@home Plugin ===
Contributors: postnlfloraathome
Tags: PostNL, Flora@home, woocommerce
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WooCommerce Plugin for Flora@home

== Description ==
Flora@Home stelt elke webshop in staat een assortiment verse bloemen en planten te verkopen. Geheel geïntegreerd in de webshop. Zonder risico of investeringen. Direct vanaf Nederlandse kwekers, bezorgd in heel Europa.

== Installation ==

Install and activate the WooCommerce and check Flora@home plugins, if you haven't already done so, then go to "Flora@home" in the WordPress admin menu and check the settings there.

== Changelog ==
Release: 1.2.2
- Compatibility updates.

Release: 1.2.1
- Compatibility updates.

Release: 1.2.0
- Fix issues when exporting of orders in php7.4.
- Compatibility updates.

Release: 1.1.9
- Fix issues when import of product has no images.
- Fix issues when product import creates duplicate products.

Release: 1.1.8
- Added extra data in order API for BREXIT.

Release: 1.1.7
- Fixed issue when product is unselected from Flora@home selection.

Release: 1.1.6
- Fixed issue duplicate images downloaded.
- Fixed issue downloading of images gets stuck if there is a problem in downloading any particular image, system does not download any images after that.
- Additional validation on image download.
- New feature: Option if customer wants to move deleted product to out of stock instead of putting the product in concept. There is a setting which can be enabled if this is desired.

Release: 1.1.5
- Fixed issue with duplicate product meta

Release: 1.1.4
- Fixed issue with duplicate order export when exporting manually through order bulk actions

Release: 1.1.3
- Fixed issue in Order export
- Fixed issue in product sync when no products are selected on Flora@home
- Rolled back support for WooCommerce PostNL shipment plugin

Release: 1.1.2
- Added a cron to sync products with Flora@home daily
- Added support for WooCommerce PostNL shipment plugin

Release: 1.1.1
- Fix for not sending error email if order items are not flora@home
- Send shipping state in order export