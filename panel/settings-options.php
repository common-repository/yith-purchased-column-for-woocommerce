<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

return apply_filters( 'yith_poic_panel_options', array(

    'settings' => array(

        'poic_options_start' => array(
            'type' => 'sectionstart',
        ),

        'poic_options_title' => array(
            'title' => __( 'General Settings', 'yith-purchased-column-for-woocommerce' ),
            'type'  => 'title',
        ),

        'poic_position'    => array(
            'title'   => sprintf( '%s:', __( 'Show the purchased column after column', 'yith-purchased-column-for-woocommerce' ) ),
            'type'    => 'select',
            'options' => YITH_Purchased_Order_Items_Column::get_order_cols(),
            'id'      => 'yith_poic_position',
            'default' => YITH_Purchased_Order_Items_Column::get_default_after_column_arg()
        ),

        'poic_options_end'   => array(
            'type' => 'sectionend',
        ),
    )
), 'settings'
);