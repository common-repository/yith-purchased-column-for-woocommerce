<?php
/**
 * Plugin Name: YITH Purchased Column for WooCommerce
 * Description: YITH Purchased Column for WooCommerce allows reactivating the Purchased column removed in the last WooCommerce version (3.0.0 or greater)
 * Version: 1.1.8
 * Author: YITHEMES
 * Text Domain: yith-purchased-column-for-woocommerce
 * Domain Path: /languages
 * Author URI: http://yithemes.com/
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 4.7
 *
 * @author  yithemes
 * @version 1.0.0
 */
/*  Copyright 2015  Your Inspiration Themes  (email : plugins@yithemes.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/* === DEFINE === */
! defined( 'YITH_POIC_VERSION' )            && define( 'YITH_POIC_VERSION', '1.1.8' );
! defined( 'YITH_POIC_DB_VERSION' )         && define( 'YITH_POIC_DB_VERSION', '1.0.0' );
! defined( 'YITH_POIC_INIT' )               && define( 'YITH_POIC_INIT', plugin_basename( __FILE__ ) );
! defined( 'YITH_POIC_SLUG' )               && define( 'YITH_POIC_SLUG', 'yith-purchased-column-for-woocommerce' );
! defined( 'YITH_POIC_FILE' )               && define( 'YITH_POIC_FILE', __FILE__ );
! defined( 'YITH_POIC_PATH' )               && define( 'YITH_POIC_PATH', plugin_dir_path( __FILE__ ) );
! defined( 'YITH_POIC_URL' )                && define( 'YITH_POIC_URL', plugins_url( '/', __FILE__ ) );
! defined( 'YITH_POIC_OPTIONS_PATH' )       && define( 'YITH_POIC_OPTIONS_PATH', YITH_POIC_PATH . 'panel' );

/* Plugin Framework Version Check */
if( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_POIC_PATH . 'plugin-fw/init.php' ) ) {
    require_once( YITH_POIC_PATH . 'plugin-fw/init.php' );
}
yit_maybe_plugin_fw_loader( YITH_POIC_PATH  );

/* Load YWCM text domain */
load_plugin_textdomain( 'yith-purchased-column-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

if ( ! function_exists( 'YITH_Purchased_Order_Items_Column' ) ) {
    /**
     * Unique access to instance of YITH_Purchased_Order_Items_Column class
     *
     * @return YITH_Purchased_Order_Items_Column
     * @since 1.0.0
     */
    function YITH_Purchased_Order_Items_Column() {
        // Load required classes and functions
        require_once( YITH_POIC_PATH . 'includes/class.yith-purchased-order-items-column.php' );
        return YITH_Purchased_Order_Items_Column::instance();
    }
}

if( ! function_exists( 'yith_poic_install' ) ){
    function yith_poic_install() {
        if ( function_exists( 'WC' ) && version_compare( WC()->version, '3.0.0', '>=' ) && is_admin() && ! defined( 'DOING_AJAX' ) ) {
            YITH_Purchased_Order_Items_Column();
        }

        else {
            add_action ( 'admin_notices', 'yith_poic_install_woocommerce_admin_notice' );
        }
    }
}

if( ! function_exists( 'yith_poic_install_woocommerce_admin_notice' ) ){
    function yith_poic_install_woocommerce_admin_notice () {
        ?>
        <div class="error">
            <p><?php _e ( 'YITH Purchased Column for WooCommerce require WooCommerce version 3.3.0 or greater.', 'yith-woocommerce-additional-uploads' ); ?></p>
        </div>
        <?php
    }
}

add_action( 'plugins_loaded', 'yith_poic_install', 11 );
