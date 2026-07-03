<?php
/**
 * Plugin Name: Admin Notices Hide Lite
 * Description: Hides WordPress admin notices until toggled on. This is a lightwieght version for users who want to hide notices without the need for a settings page. This version removes all translation files, CSS files, JS files and all assets.
 * Version: 1.0
 * Author: Ben Dishler
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Tested up to: 7.0
 * Requires PHP: 7.0
 * Stable tag: 1.0
 * Tags: admin notices, hide notices
 * 
 */

if (!defined('ABSPATH')) exit;

class Admin_Notices_Hide_Lite_WC_Toggle {

    const OPTION = 'hanl_enabled';
    const MODE_OPTION = 'hanl_mode'; // normal | wc_only
    const TEMP_SHOW = 'hanl_temp_show';

    public function __construct() {
        add_action('admin_init', [$this, 'maybe_hide_notices'], 1);
        add_action('admin_menu', [$this, 'settings_page']);
        add_action('admin_bar_menu', [$this, 'admin_bar_toggle'], 999);
        add_action('init', [$this, 'handle_temp_toggle']);
    }

    /**
     * Handle temporary toggle via admin bar.
     */
    public function handle_temp_toggle() {
        if (!is_admin()) return;

        if (isset($_GET[self::TEMP_SHOW])) {
            if ($_GET[self::TEMP_SHOW] === '1') {
                set_transient(self::TEMP_SHOW, '1', 60); // 1 minute
            } else {
                delete_transient(self::TEMP_SHOW);
            }
            wp_redirect(remove_query_arg(self::TEMP_SHOW));
            exit;
        }
    }

    /**
     * Add admin bar toggle button.
     */
    public function admin_bar_toggle($wp_admin_bar) {
        if (!current_user_can('manage_options')) return;

        $temp = get_transient(self::TEMP_SHOW);

        $label = $temp ? 'Hide Notices' : 'Show Notices (Temp)';
        $toggle = $temp ? '0' : '1';

        $wp_admin_bar->add_node([
            'id'    => 'hanl_toggle',
            'title' => $label,
            'href'  => add_query_arg(self::TEMP_SHOW, $toggle),
        ]);
    }

    /**
     * Determine if notices should be hidden.
     */
    private function should_hide_notices() {
        // Temporary override
        if (get_transient(self::TEMP_SHOW)) {
            return false;
        }

        $enabled = get_option(self::OPTION, '0');
        if ($enabled === '1') {
            return false;
        }

        $mode = get_option(self::MODE_OPTION, 'normal');

        // WooCommerce not active → hide normally
        if (!class_exists('WooCommerce')) {
            return true;
        }

        $screen = get_current_screen();
        if (!$screen) return true;

        $id = $screen->id;

        // WooCommerce critical screens
        $wc_critical = [
            'woocommerce',
            'woocommerce_page_wc-settings',
            'woocommerce_page_wc-status',
            'woocommerce_page_wc-reports',
            'woocommerce_page_wc-orders',
            'edit-shop_order',
            'shop_order',
            'product',
            'edit-product',
        ];

        // WC Admin (React)
        if (strpos($id, 'wc-admin') !== false) {
            return false;
        }

        // WC-only mode → hide everything except WC screens
        if ($mode === 'wc_only') {
            return !in_array($id, $wc_critical, true);
        }

        // Normal mode → hide everywhere except WC-critical screens
        return !in_array($id, $wc_critical, true);
    }

    /**
     * Hide admin notices.
     */
    public function maybe_hide_notices() {
        if ($this->should_hide_notices()) {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
        }
    }

    /**
     * Settings page.
     */
    public function settings_page() {
        add_options_page(
            'Hide Admin Notices',
            'Hide Admin Notices',
            'manage_options',
            'hanl',
            [$this, 'render_settings']
        );
    }

    /**
     * Render settings page.
     */
    public function render_settings() {
        if (!empty($_POST['hanl_save'])) {
            update_option(self::OPTION, isset($_POST['hanl_enabled']) ? '1' : '0');
            update_option(self::MODE_OPTION, $_POST['hanl_mode'] ?? 'normal');
            echo '<div class="updated"><p>Settings saved.</p></div>';
        }

        $enabled = get_option(self::OPTION, '0');
        $mode = get_option(self::MODE_OPTION, 'normal');
        ?>
        <div class="wrap">
            <h1>Hide Admin Notices</h1>
            <form method="post">

                <p>
                    <label>
                        <input type="checkbox" name="hanl_enabled" value="1" <?php checked($enabled, '1'); ?>>
                        Show admin notices
                    </label>
                </p>

                <h2>Mode</h2>
                <p>
                    <label>
                        <input type="radio" name="hanl_mode" value="normal" <?php checked($mode, 'normal'); ?>>
                        Normal Mode (hide most notices)
                    </label><br>

                    <label>
                        <input type="radio" name="hanl_mode" value="wc_only" <?php checked($mode, 'wc_only'); ?>>
                        Show WooCommerce Notices Only
                    </label>
                </p>

                <p><input type="submit" name="hanl_save" class="button button-primary" value="Save"></p>
            </form>
        </div>
        <?php
    }
}

new Admin_Notices_Hide_Lite_WC_Toggle();
