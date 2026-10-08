=== Admin Notices Hide Lite ===
Contributors: Ben Dishler
Author URI: https://bendishler.com
Tags: admin notices, hide notices
Requires at least: 6.0
Tested up to: 7.1
Stable tag: 1.0.2
Requires PHP: 7.0
License: GPL 2.0 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight plugin to hide admin notices, with a settings page and temporary toolbar toggle.

== Description ==

Admin Notices Hide Lite hides admin notices with a small stylesheet loaded only on its settings page and no JavaScript.

Use Settings > Hide Admin Notices to show notices permanently or select a notice-hiding mode. Administrators can also use the toolbar to show notices for one minute. This temporary override applies site-wide.

When WooCommerce is active, both modes preserve notices on WooCommerce settings, status, reports, orders, products and WC Admin screens. They hide notices on other screens. The plugin does not filter individual notices by their source.

== Installation ==

1. Upload the admin-notices-hide-lite folder to /wp-content/plugins/.
2. Activate the plugin through the Plugins menu.
3. Open Settings > Hide Admin Notices to configure notice visibility.

== Changelog ==

= 1.0.2 =
* Add lightweight styles loaded only on the plugin settings page.
* Update plugin text.
* Add author URI.

= 1.0.1 =
* Secure settings and toolbar actions with capability checks and nonces.
* Sanitize settings input and use safe redirects.
* Make interface strings translatable and escape displayed text.
* Evaluate WooCommerce exceptions after the current screen is available.
* Align release metadata and declare WordPress 7.1 compatibility, including 7.1.3.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.2 =
Adds basic settings-page styling without loading assets on other pages.

= 1.0.1 =
Security update for settings and temporary notice toggles. Existing settings are retained.
