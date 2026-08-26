=== HDWebmobile Order Export ===
Contributors: htrxuan
Donate link: https://paypal.me/htrxuan/20
Tags: woocommerce, order export, csv export, orders, export
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Export WooCommerce orders to CSV, filtered by date range and status -- built entirely on WooCommerce's own query and export classes.

== Description ==

HDWebmobile Order Export adds a simple "Order Export" screen where you pick a date range and one or more order statuses, then download a CSV with order ID, customer details, addresses, items, totals, and coupon codes -- ready for accounting, shipping, or any spreadsheet workflow.

Two real vulnerability classes were researched in this exact plugin category, both found in a widely-installed competing order-export plugin: a SQL injection caused by a sort-direction parameter concatenated straight into a raw query, and CSV/formula injection, where an exported cell value starting with `=`, `+`, `-`, or `@` executes as a formula when the file is opened in Excel or Sheets. This plugin closes both by construction rather than by bolted-on sanitization: every order is fetched through WooCommerce's own `wc_get_orders()` (never a raw SQL string), and every CSV cell is written through WooCommerce core's own `WC_CSV_Exporter` base class, which already implements the OWASP-recommended formula-injection escaping -- this plugin reuses WooCommerce's own trusted, already-audited code instead of writing a new CSV writer from scratch.

= Key Features =
* Filter by date range and/or one or more order statuses before exporting
* One-click CSV download -- no file is ever written to the server, the export streams directly to your browser
* Every order fetched through WooCommerce's own order-query API -- no raw SQL anywhere in this plugin
* Every exported cell escaped through WooCommerce core's own CSV exporter class -- closes the exact formula-injection class found in a competing plugin
* Order status filter is validated against WooCommerce's own registered statuses -- closes the exact unescaped-parameter class behind a SQL-injection CVE found in a competing plugin
* Zero configuration -- no settings to save, nothing persisted in the database

= Columns included =
Order ID, Order Number, Date, Status, Customer Name, Email, Phone, Billing Address, Shipping Address, Payment Method, Items, Item Count, Subtotal, Shipping, Tax, Discount, Order Total, Coupon(s).

= Limitations (please read before installing) =
* Fixed column set in this version -- no column picker
* Single-request export, not a background/batch job -- suited to date-range-scoped exports rather than exporting an entire multi-year order history in one click
* CSV format only, no XLSX

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/hdwebmobile-order-export` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress. WooCommerce must already be installed and active.
3. Go to WooCommerce > HDWebmobile > Order Export -- no configuration needed.

== How to Use ==

= 1. Open the Order Export tab =
WooCommerce > HDWebmobile > Order Export (Screenshot 1).

= 2. Choose your filters =
Pick a from/to date (leave either blank for an open-ended range) and check any order statuses you want included (Screenshot 2). Leave every status unchecked to export all of them.

= 3. Download =
Click "Download CSV" -- the file downloads immediately, nothing is saved on the server (Screenshot 3).

== Screenshots ==

1. The Order Export tab under WooCommerce > HDWebmobile.
2. The date-range and status filters.
3. A downloaded export opened in a spreadsheet.

== Changelog ==

= 1.0.0 =
* Initial release: date-range and status-filtered CSV export, built on WooCommerce's own order-query and CSV-exporter classes, zero configuration.
