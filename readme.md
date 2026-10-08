# Admin Notices Hide Lite

Lightweight WordPress plugin to hide admin notices with a settings page and temporary toolbar toggle.

## Requirements

* WordPress 6.0 or later; compatibility declared through WordPress 7.1, including 7.1.3.
* PHP 7.0 or later.

## Installation

Upload the plugin folder, activate it, and open Settings > Hide Admin Notices.

## Notice visibility

The toolbar can show notices site-wide for one minute. When WooCommerce is active, both existing modes preserve notices on WooCommerce screens and hide notices elsewhere. They do not filter individual notices by source.

## Styling

Edit admin.css to customize the settings page. The small stylesheet loads only on this page and uses WordPress native controls and buttons.

## Version History

### 1.0.2

* Add lightweight, scoped settings-page styles.

### 1.0.1

* Add nonce and capability checks, sanitize settings, and use safe redirects.
* Translate and escape interface text.
* Check notice visibility after the current screen is initialized.
* Align plugin and readme metadata for WordPress 7.1 compatibility, including 7.1.3.

### 1.0.0

* Initial release.

## Author

Ben Dishler

## License

GPL version 2 or later. See license.txt.
