# HDWebmobile Order Export

Export WooCommerce orders to CSV, filtered by date range and status -- built entirely on WooCommerce's own query and export classes.

- **WordPress.org:** https://wordpress.org/plugins/hdwebmobile-order-export/
- **Requires:** WordPress 6.9+, WooCommerce, PHP 7.4+
- **License:** GPLv2 or later

## Description

HDWebmobile Order Export adds a simple "Order Export" screen where you pick a date range and one or more order statuses, then download a CSV with order ID, customer details, addresses, items, totals, and coupon codes -- ready for accounting, shipping, or any spreadsheet workflow.

Two real vulnerability classes were researched in this exact plugin category, both found in a widely-installed competing order-export plugin: a SQL injection caused by a sort-direction parameter concatenated straight into a raw query, and CSV/formula injection, where an exported cell value starting with `=`, `+`, `-`, or `@` executes as a formula when the file is opened in Excel or Sheets. This plugin closes both by construction rather than by bolted-on sanitization: every order is fetched through WooCommerce's own `wc_get_orders()` (never a raw SQL string), and every CSV cell is written through WooCommerce core's own `WC_CSV_Exporter` base class, which already implements the OWASP-recommended formula-injection escaping -- this plugin reuses WooCommerce's own trusted, already-audited code instead of writing a new CSV writer from scratch.

## Features

* Filter by date range and/or one or more order statuses before exporting
* One-click CSV download -- no file is ever written to the server, the export streams directly to your browser
* Every order fetched through WooCommerce's own order-query API -- no raw SQL anywhere in this plugin
* Every exported cell escaped through WooCommerce core's own CSV exporter class -- closes the exact formula-injection class found in a competing plugin
* Order status filter is validated against WooCommerce's own registered statuses -- closes the exact unescaped-parameter class behind a SQL-injection CVE found in a competing plugin
* Zero configuration -- no settings to save, nothing persisted in the database

## Development

Standard WordPress plugin structure:

```
hdwebmobile-order-export.php    Bootstrap
includes/class-hdoe-activator.php
includes/class-hdoe-admin.php
includes/class-hdoe-core.php
includes/class-hdoe-exporter.php
includes/class-hdoe-hub.php
```

Part of the [HDWebmobile](https://hdwebmobile.com/plugins/) suite of focused, single-purpose WooCommerce plugins.

## License

GPLv2 or later. See [LICENSE](LICENSE).

