<?php
/**
 * Created by PhpStorm.
 * User: pooyasaberian
 * Date: 2/16/17
 * Time: 10:37 AM
 */

class Appchar_Cart_Handler {

    public static function add_to_cart_action( $url = false, $add_to_cart_data ) {
        $_POST = $add_to_cart_data;
        $_REQUEST = $add_to_cart_data;
        if ( empty( $add_to_cart_data['add-to-cart'] ) || ! is_numeric( $add_to_cart_data['add-to-cart'] ) ) {
            return false;
        }

        $product_id          = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $add_to_cart_data['add-to-cart'] ) );
        $was_added_to_cart   = false;
        $adding_to_cart      = wc_get_product( $product_id );

        if ( ! $adding_to_cart ) {
            return false;
        }
        $add_to_cart_handler = apply_filters( 'woocommerce_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

        // Variable product handling
        if ( 'variable' === $add_to_cart_handler ) {
            $was_added_to_cart = self::add_to_cart_handler_variable( $product_id, $add_to_cart_data );

            // Grouped Products
        } elseif ( 'grouped' === $add_to_cart_handler ) {
            $was_added_to_cart = self::add_to_cart_handler_grouped( $product_id, $add_to_cart_data );

            // Custom Handler
        } elseif ( has_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler ) ){
            do_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler, $url );

            // Simple Products
        } else {
            $was_added_to_cart = self::add_to_cart_handler_simple( $product_id, $add_to_cart_data );
        }
        // If we added the product to the cart we can now optionally do a redirect.
        if ( $was_added_to_cart !== false && wc_notice_count( 'error' ) === 0 ) {
            return array(
                'status' => true,
                'cart_item_key' => $was_added_to_cart,
            );
        }

        return array(
            'status' => false,
            'cart_item_key' => '',
        );
    }

    private static function add_to_cart_handler_simple( $product_id, $add_to_cart_data ) {
        $quantity 			= empty( $add_to_cart_data['quantity'] ) ? 1 : wc_stock_amount( $add_to_cart_data['quantity'] );
        $passed_validation 	= apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
        if (isset($add_to_cart_data['_measurement_needed']) && isset($add_to_cart_data['weight_needed']) && isset($add_to_cart_data['_measurement_needed_unit'])) {
            $weight_item_data = array(
                '_measurement_needed' => $add_to_cart_data['_measurement_needed'],
                'weight_needed' => $add_to_cart_data['weight_needed'],
                '_measurement_needed_unit' => $add_to_cart_data['_measurement_needed_unit']
            );
            $cart_item_data = apply_filters( 'woocommerce_add_cart_item_data', $weight_item_data, $product_id, $add_to_cart_data['variation_id'] ); 
        }
        if ( $passed_validation && false !== $result = WC()->cart->add_to_cart( $product_id, $quantity ) ) {
            wc_add_to_cart_message( array( $product_id => $quantity ), true );
            return $result;
        }
        return false;
    }

    private static function add_to_cart_handler_grouped( $product_id, $add_to_cart_data) {
        $was_added_to_cart = false;
        $added_to_cart     = array();
        if (isset($add_to_cart_data['_measurement_needed']) && isset($add_to_cart_data['weight_needed']) && isset($add_to_cart_data['_measurement_needed_unit'])) {
            $weight_item_data = array(
                '_measurement_needed' => $add_to_cart_data['_measurement_needed'],
                'weight_needed' => $add_to_cart_data['weight_needed'],
                '_measurement_needed_unit' => $add_to_cart_data['_measurement_needed_unit']
            );
            $cart_item_data = apply_filters( 'woocommerce_add_cart_item_data', $weight_item_data, $product_id, $add_to_cart_data['variation_id'] ); 
        }
        if ( ! empty( $add_to_cart_data['quantity'] ) && is_array( $add_to_cart_data['quantity'] ) ) {
            $quantity_set = false;

            foreach ( $add_to_cart_data['quantity'] as $item => $quantity ) {
                if ( $quantity <= 0 ) {
                    continue;
                }
                $quantity_set = true;

                // Add to cart validation
                $passed_validation 	= apply_filters( 'woocommerce_add_to_cart_validation', true, $item, $quantity );

                if ( $passed_validation && false !==  $result = WC()->cart->add_to_cart( $item, $quantity ) ) {
                    $was_added_to_cart = true;
                    $added_to_cart[ $item ] = $quantity;
                }
            }

            if ( ! $was_added_to_cart && ! $quantity_set ) {
                wc_add_notice( __( 'Please choose the quantity of items you wish to add to your cart&hellip;', 'woocommerce' ), 'error' );
            } elseif ( $was_added_to_cart ) {
                wc_add_to_cart_message( $added_to_cart );
                return $result;
            }

        } elseif ( $product_id ) {
            /* Link on product archives */
            wc_add_notice( __( 'Please choose a product to add to your cart&hellip;', 'woocommerce' ), 'error' );
        }
        return false;
    }

    private static function add_to_cart_handler_variable( $product_id, $add_to_cart_data ) {
        $adding_to_cart     = wc_get_product( $product_id );
        $variation_id       = empty( $add_to_cart_data['variation_id'] ) ? '' : absint( $add_to_cart_data['variation_id'] );
        $quantity           = empty( $add_to_cart_data['quantity'] ) ? 1 : wc_stock_amount( $add_to_cart_data['quantity'] );
        $missing_attributes = array();
        $variations         = array();
        $attributes         = $adding_to_cart->get_attributes();
        if (isset($add_to_cart_data['_measurement_needed']) && isset($add_to_cart_data['weight_needed']) && isset($add_to_cart_data['_measurement_needed_unit'])) {
            $weight_item_data = array(
                '_measurement_needed' => $add_to_cart_data['_measurement_needed'],
                'weight_needed' => $add_to_cart_data['weight_needed'],
                '_measurement_needed_unit' => $add_to_cart_data['_measurement_needed_unit']
            );
            $cart_item_data = apply_filters( 'woocommerce_add_cart_item_data', $weight_item_data, $product_id, $add_to_cart_data['variation_id'] ); 
        }
        // If no variation ID is set, attempt to get a variation ID from posted attributes.
        if ( empty( $variation_id ) ) {
            $variation_id = $adding_to_cart->get_matching_variation( wp_unslash( $add_to_cart_data ) );
        }

        $variation = wc_get_product( $variation_id );

        // Verify all attributes
        foreach ( $attributes as $attribute ) {
            if ( ! $attribute['is_variation'] ) {
                continue;
            }

            $taxonomy = 'attribute_' . sanitize_title( $attribute['name'] );

            if ( isset( $add_to_cart_data[ $taxonomy ] ) ) {

                // Get value from post data
                if ( $attribute['is_taxonomy'] ) {
                    // Don't use wc_clean as it destroys sanitized characters
                    $value = sanitize_title( stripslashes( $add_to_cart_data[ $taxonomy ] ) );
                } else {
                    $value = wc_clean( stripslashes( $add_to_cart_data[ $taxonomy ] ) );
                }

                // Get valid value from variation
                $valid_value = isset( $variation->variation_data[ $taxonomy ] ) ? $variation->variation_data[ $taxonomy ] : '';

                // Allow if valid
                if ( '' === $valid_value || $valid_value === $value ) {
                    $variations[ $taxonomy ] = $value;
                    continue;
                }

            } else {
                $missing_attributes[] = wc_attribute_label( $attribute['name'] );
            }
        }

        if ( ! empty( $missing_attributes ) ) {
            wc_add_notice( sprintf( _n( '%s is a required field', '%s are required fields', sizeof( $missing_attributes ), 'woocommerce' ), wc_format_list_of_items( $missing_attributes ) ), 'error' );
        } elseif ( empty( $variation_id ) ) {
            wc_add_notice( __( 'Please choose product options&hellip;', 'woocommerce' ), 'error' );
        } else {
            // Add to cart validation
            $passed_validation 	= apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );

            if ( $passed_validation && false !==  $result = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations ) ) {
                wc_add_to_cart_message( array( $product_id => $quantity ), true );
                return $result;
            }
        }
        return false;
    }
}