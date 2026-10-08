<?php
/**
 * Plugin Name: Admin Notices Hide Lite
 * Description: Hides admin notices with a settings page and a temporary toolbar toggle, while preserving notices on WooCommerce screens.
 * Version: 1.0.2
 * Author: Ben Dishler
 * Author URI: https://bendishler.com
 * GitHub Plugin URI: https://github.com/ProjectDeeds/admin-notices-hide-lite
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.0
 * Requires PHP: 7.0
 * Tested up to: 7.1
 * Text Domain: admin-notices-hide-lite
 *
 * @package Admin_Notices_Hide_Lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controls notice visibility and administrator settings.
 */
class HANL_Admin_Notices_Hide_Lite {
	/** @var string|false Settings page hook suffix. */
	private $settings_hook = false;

	const OPTION      = 'hanl_enabled';
	const MODE_OPTION = 'hanl_mode';
	const TEMP_SHOW   = 'hanl_temp_show';

	/** Register hooks. */
	public function __construct() {
		add_action( 'admin_head', array( $this, 'maybe_hide_notices' ), PHP_INT_MAX );
		add_action( 'admin_menu', array( $this, 'settings_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_toggle' ), 999 );
		add_action( 'admin_init', array( $this, 'handle_temp_toggle' ) );
	}

	/** Handle the nonce-protected toolbar toggle. */
	public function handle_temp_toggle() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! isset( $_GET[ self::TEMP_SHOW ] ) ) {
			return;
		}
		check_admin_referer( 'anhl_toggle_notices' );
		$toggle = is_string( $_GET[ self::TEMP_SHOW ] ) ? sanitize_text_field( wp_unslash( $_GET[ self::TEMP_SHOW ] ) ) : '';
		if ( '1' === $toggle ) {
			set_transient( self::TEMP_SHOW, '1', MINUTE_IN_SECONDS );
		} elseif ( '0' === $toggle ) {
			delete_transient( self::TEMP_SHOW );
		}
		wp_safe_redirect( remove_query_arg( array( self::TEMP_SHOW, '_wpnonce' ) ) );
		exit;
	}

	/**
	 * Add the toolbar toggle on administrative screens.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Toolbar instance.
	 */
	public function admin_bar_toggle( $wp_admin_bar ) {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$temp = get_transient( self::TEMP_SHOW );
		$wp_admin_bar->add_node(
			array(
				'id'    => 'hanl_toggle',
				'title' => $temp ? esc_html__( 'Hide Notices', 'admin-notices-hide-lite' ) : esc_html__( 'Show Notices', 'admin-notices-hide-lite' ),
				'href'  => wp_nonce_url( add_query_arg( self::TEMP_SHOW, $temp ? '0' : '1' ), 'anhl_toggle_notices' ),
			)
		);
	}

	/**
	 * Determine whether the current screen's notices should be hidden.
	 *
	 * @return bool
	 */
	private function should_hide_notices() {
		if ( get_transient( self::TEMP_SHOW ) || '1' === get_option( self::OPTION, '0' ) ) {
			return false;
		}
		if ( ! class_exists( 'WooCommerce' ) ) {
			return true;
		}
		$screen = get_current_screen();
		if ( ! $screen ) {
			return true;
		}
		$wc_critical = array(
			'woocommerce',
			'woocommerce_page_wc-settings',
			'woocommerce_page_wc-status',
			'woocommerce_page_wc-reports',
			'woocommerce_page_wc-orders',
			'edit-shop_order',
			'shop_order',
			'product',
			'edit-product',
		);
		if ( false !== strpos( $screen->id, 'wc-admin' ) ) {
			return false;
		}
		// Both existing modes preserve notices on these WooCommerce screens.
		return ! in_array( $screen->id, $wc_critical, true );
	}

	/** Hide notices after the current screen has been initialized. */
	public function maybe_hide_notices() {
		if ( $this->should_hide_notices() ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}

	/** Register the settings page. */
	public function settings_page() {
		$this->settings_hook = add_options_page(
			__( 'Hide Admin Notices', 'admin-notices-hide-lite' ),
			__( 'Hide Admin Notices', 'admin-notices-hide-lite' ),
			'manage_options',
			'hanl',
			array( $this, 'render_settings' )
		);
	}

	/**
	 * Load the small stylesheet only on this plugin's settings page.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue_styles( $hook_suffix ) {
		if ( $hook_suffix !== $this->settings_hook ) {
			return;
		}
		wp_enqueue_style( 'hanl-admin', plugins_url( 'admin.css', __FILE__ ), array(), '1.0.2' );
	}

	/** Render and securely save the settings. */
	public function render_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( isset( $_POST['hanl_save'] ) ) {
			check_admin_referer( 'anhl_save_settings' );
			$enabled = isset( $_POST['hanl_enabled'] ) && is_string( $_POST['hanl_enabled'] ) ? sanitize_text_field( wp_unslash( $_POST['hanl_enabled'] ) ) : '0';
			$mode    = isset( $_POST['hanl_mode'] ) && is_string( $_POST['hanl_mode'] ) ? sanitize_key( wp_unslash( $_POST['hanl_mode'] ) ) : 'normal';
			update_option( self::OPTION, '1' === $enabled ? '1' : '0' );
			update_option( self::MODE_OPTION, in_array( $mode, array( 'normal', 'wc_only' ), true ) ? $mode : 'normal' );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', 'admin-notices-hide-lite' ) . '</p></div>';
		}
		$enabled = get_option( self::OPTION, '0' );
		$mode    = get_option( self::MODE_OPTION, 'normal' );
		?>
<div class="wrap hanl-settings">
    <h1><?php esc_html_e( 'Hide Admin Notices', 'admin-notices-hide-lite' ); ?></h1>
    <form method="post">
        <?php wp_nonce_field( 'anhl_save_settings' ); ?>
        <p><label>
                <input type="checkbox" name="hanl_enabled" value="1" <?php checked( $enabled, '1' ); ?>>
                <?php esc_html_e( 'Show admin notices', 'admin-notices-hide-lite' ); ?>
            </label></p>
        <h2><?php esc_html_e( 'Mode', 'admin-notices-hide-lite' ); ?></h2>
        <p>
            <label><input type="radio" name="hanl_mode" value="normal" <?php checked( $mode, 'normal' ); ?>>
                <?php esc_html_e( 'Normal Mode (hide most notices)', 'admin-notices-hide-lite' ); ?>
            </label><br>
            <label><input type="radio" name="hanl_mode" value="wc_only" <?php checked( $mode, 'wc_only' ); ?>>
                <?php esc_html_e( 'Show WooCommerce Notices Only', 'admin-notices-hide-lite' ); ?>
            </label>
        </p>
        <?php submit_button( __( 'Save', 'admin-notices-hide-lite' ), 'primary', 'hanl_save' ); ?>
    </form>
</div>
<?php
	}
}

new HANL_Admin_Notices_Hide_Lite();