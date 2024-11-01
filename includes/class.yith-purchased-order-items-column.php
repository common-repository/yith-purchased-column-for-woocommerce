<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'YITH_POIC_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_Multistep_Checkout_Admin
 * @since      Version 1.0.0
 * @author     Andrea Grillo <andrea.grillo@yithemes.com>
 *
 * @package    Yithemes
 */

if ( ! class_exists( 'YITH_Purchased_Order_Items_Column' ) ) {
	/**
	 * Class YITH_Purchased_Order_Items_Column
	 *
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
	class YITH_Purchased_Order_Items_Column {

		/**
		 * Main Instance
		 *
		 * @since  1.0
		 * @access protected
		 * @var YITH_Multistep_Checkout
		 */
		protected static $_instance = null;

		/**
		 * @var Panel object
		 */
		protected $_panel = null;


		/**
		 * @var Panel page
		 */
		protected $_panel_page = 'yith_poic_panel';

		/**
		 * Construct
		 *
		 * @since  1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function __construct() {
			/* === Load Plugin Framework === */
			add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );

			/* === Register Panel Settings === */
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );

			if ( version_compare( WC()->version, '3.3', '>=' ) ) {
				/* Load List table Classes */
				if ( ! class_exists( 'WC_Admin_List_Table', false ) ) {
					include_once WC()->plugin_path() . '/includes/admin/list-tables/abstract-class-wc-admin-list-table.php';
				}

				if ( ! class_exists( 'WC_Admin_List_Table_Orders', false ) ) {
					include_once WC()->plugin_path() . '/includes/admin/list-tables/class-wc-admin-list-table-orders.php';
				}
			}

			/* === Add Purchesed column to orders page  */
			add_filter( 'manage_shop_order_posts_columns', array( $this, 'manage_shop_order_columns' ), 20 );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'show_order_items' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );
		}

		/**
		 * Main plugin Instance
		 *
		 * @return YITH_Multistep_Checkout Main instance
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Load plugin framework
		 *
		 * @return void
		 * @since  1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
				global $plugin_fw_data;
				if ( ! empty( $plugin_fw_data ) ) {
					$plugin_fw_file = array_shift( $plugin_fw_data );
					require_once( $plugin_fw_file );
				}
			}
		}


		/**
		 * Add purchased column
		 *
		 * @param $columns
		 *
		 * @return array
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function manage_shop_order_columns( $columns ) {
			$after_col   = get_option( 'yith_poic_position', $this->get_default_after_column_arg() );
			$order_items = array( 'order_items' => __( 'Purchased', 'yith-purchased-column-for-woocommerce' ) );
			$ref_pos     = array_search( $after_col, array_keys( $columns ) );
			$columns     = array_slice( $columns, 0, $ref_pos + 1, true ) + $order_items + array_slice( $columns, $ref_pos + 1, count( $columns ) - 1, true );

			return $columns;
		}

		/**
		 * Purchased column render
		 *
		 * @param $column
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function show_order_items( $column ) {
			$is_wc_3_1_or_greater = version_compare( WC()->version, '3.1', '>=' );
			if ( 'order_items' == $column ) {
				global $post, $the_order;

				if ( empty( $the_order ) || yit_get_prop( $the_order, 'id' ) !== $post->ID ) {
					$the_order = wc_get_order( $post->ID );
				}

				echo '<a href="#" class="show_order_items">' . apply_filters( 'woocommerce_admin_order_item_count', sprintf( _n( '%d item', '%d items', $the_order->get_item_count(), 'yith-purchased-column-for-woocommerce' ), $the_order->get_item_count() ), $the_order ) . '</a>';

				if ( sizeof( $the_order->get_items() ) > 0 ) {

					echo '<table class="order_items" cellspacing="0">';

					foreach ( $the_order->get_items() as $item ) {
						$product        = apply_filters( 'woocommerce_order_item_product', $item->get_product(), $item );
						$item_meta      = $is_wc_3_1_or_greater ? array() : new WC_Order_Item_Meta( $item, $product );
						$item_meta_html = $is_wc_3_1_or_greater ? wc_display_item_meta( $item, array( 'echo' => false ) ) : $item_meta->display( true, true );
						?>
                        <tr class="<?php echo apply_filters( 'woocommerce_admin_order_item_class', '', $item, $the_order ); ?>">
                            <td class="qty"><?php echo absint( $item['qty'] ); ?></td>
                            <td class="name">
								<?php if ( $product ) : ?>
									<?php echo ( wc_product_sku_enabled() && $product->get_sku() ) ? $product->get_sku() . ' - ' : ''; ?><a href="<?php echo get_edit_post_link( $product->get_id() ); ?>" title="<?php echo apply_filters( 'woocommerce_order_item_name', $item['name'], $item, false ); ?>"><?php echo apply_filters( 'woocommerce_order_item_name', $item['name'], $item, false ); ?></a>
								<?php else : ?>
									<?php echo apply_filters( 'woocommerce_order_item_name', $item['name'], $item, false ); ?>
								<?php endif; ?>
								<?php if ( ! empty( $item_meta_html ) ) : ?>
									<?php echo wc_help_tip( $item_meta_html ); ?>
								<?php endif; ?>
                            </td>
                        </tr>
						<?php
					}

					echo '</table>';

				} else {
					echo '&ndash;';
				}
			}
		}

		/**
		 * Add inline script for purchased column
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function enqueue_scripts() {
			global $pagenow;
			if ( 'edit.php' == $pagenow && ! empty( $_GET['post_type'] ) && 'shop_order' == $_GET['post_type'] ) {
				$js = "jQuery( document.body ).on( 'click', '.show_order_items', function() {
                    jQuery( this ).closest( 'td' ).find( 'table' ).toggle();
                    return false;
                });";

				wp_add_inline_script( 'woocommerce_admin', $js );
			}
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use      /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {

			if ( ! empty( $this->_panel ) ) {
				return;
			}

			$menu_title = __( 'Purchased Column', 'yith-woocommerce-multi-step-checkout' );

			$admin_tabs = apply_filters( 'yith_wcms_admin_tabs', array(
				                                                   'settings' => __( 'Settings', 'yith-woocommerce-multi-step-checkout' ),
			                                                   )
			);

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'page_title'       => $menu_title,
				'menu_title'       => $menu_title,
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yit_plugin_panel',
				'page'             => $this->_panel_page,
				'admin-tabs'       => $admin_tabs,
				'options-path'     => YITH_POIC_OPTIONS_PATH,
				'links'            => $this->get_sidebar_link()
			);

			$this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Get the panle page id
		 *
		 * @return  string The premium landing link
		 * @since   1.2.1
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function get_panel_page() {
			return $this->_panel_page;
		}

		/**
		 * Sidebar links
		 *
		 * @return   array The links
		 * @since    1.2.1
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function get_sidebar_link() {
			$links = array();

			return $links;
		}

		/**
		 * Sidebar links
		 *
		 * @return   array The links
		 * @since    1.2.1
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function filter_orders_columns( $columns ) {
			if ( isset( $columns['order_items'] ) ) {
				unset( $columns['order_items'] );
			}

			if ( isset( $columns['cb'] ) ) {
				unset( $columns['cb'] );
			}

			$temp_columns = $columns;
			foreach ( $temp_columns as $k => $v ) {
				if ( empty( $columns[ $k ] ) ) {
					$columns[ $k ] = $k;
				}
			}

			return $columns;
		}

		/**
		 * Get default order column
		 *
		 * @return   array The links
		 * @since    1.2.1
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public static function get_default_after_column_arg() {
			return function_exists( 'YITH_Vendors' ) ? 'suborder' : 'order_number';
		}

		/**
		 * Get the orders list cols
		 *
		 * @return array orders list columns
		 * @author Andrea Grillo <andrea.grillo@yithemes.com
		 */
		public static function get_order_cols() {
			$orders_columns = array();
			if ( version_compare( WC()->version, '3.3', '>=' ) ) {

				$post_list_table = new WC_Admin_List_Table_Orders();
				remove_action( 'manage_shop_order_posts_custom_column', array( $post_list_table, 'render_columns' ), 10 );
				$orders_columns = $post_list_table->define_columns( array( 'cb' => '' ) );

			} else {
				$post_list_table = _get_list_table( 'WP_Posts_List_Table' );
				$orders_columns  = $post_list_table->get_columns();
			}

			$orders_columns = apply_filters( 'manage_shop_order_posts_columns', $orders_columns );
			$orders_columns = YITH_Purchased_Order_Items_Column()->filter_orders_columns( $orders_columns );

			return $orders_columns;
		}
	}
}