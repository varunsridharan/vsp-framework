<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 31-01-2018
 * Time: 06:52 AM
 */

if( ! defiend("ABSPATH") ) {
    exit;
}

class VSP_Framework_WC_Helper {

    /**
     * Get order line items (products) in a neatly-formatted array of objects
     * with properties:
     *
     * + id - item ID
     * + name - item name, usually product title, processed through htmlentities()
     * + description - formatted item meta (e.g. Size: Medium, Color: blue), processed through htmlentities()
     * + quantity - item quantity
     * + item_total - item total (line total divided by quantity, excluding tax & rounded)
     * + line_total - line item total (excluding tax & rounded)
     * + meta - formatted item meta array
     * + product - item product or null if getting product from item failed
     * + item - raw item array
     *
     * @since 3.0.0
     * @param \WC_Order $order
     * @return array
     */
    public static function get_order_line_items($order) {
        $line_items = array();
        foreach( $order->get_items() as $id => $item ) {
            $line_item = new \stdClass();
            // TODO: remove when WC 3.0 can be required
            $name = $item instanceof \WC_Order_Item_Product ? $item->get_name() : $item['name'];
            $quantity = $item instanceof \WC_Order_Item_Product ? $item->get_quantity() : $item['qty'];
            $item_desc = array();
            $product = ( self::is_wc_version_gte_3_1() ) ? $item->get_product() : $order->get_product_from_item($item);
            // add SKU to description if available
            if( is_callable(array( $product, 'get_sku' )) && $product->get_sku() ) {
                $item_desc[] = sprintf('SKU: %s', $product->get_sku());
            }
            $item_meta = SV_WC_Order_Compatibility::get_item_formatted_meta_data($item, '_', TRUE);
            if( ! empty($item_meta) ) {
                foreach( $item_meta as $meta ) {
                    $item_desc[] = sprintf('%s: %s', $meta['label'], $meta['value']);
                }
            }
            $item_desc = implode(', ', $item_desc);
            $line_item->id = $id;
            $line_item->name = htmlentities($name, ENT_QUOTES, 'UTF-8', FALSE);
            $line_item->description = htmlentities($item_desc, ENT_QUOTES, 'UTF-8', FALSE);
            $line_item->quantity = $quantity;
            $line_item->item_total = isset($item['recurring_line_total']) ? $item['recurring_line_total'] : $order->get_item_total($item);
            $line_item->line_total = $order->get_line_total($item);
            $line_item->meta = $item_meta;
            $line_item->product = is_object($product) ? $product : NULL;
            $line_item->item = $item;
            $line_items[] = $line_item;
        }
        return $line_items;
    }

    /**
     * Determines if the installed version of WooCommerce is 3.1 or greater.
     *
     * @since 4.6.5
     * @return bool
     */
    public static function is_wc_version_gte_3_1() {
        return self::get_wc_version() && version_compare(self::get_wc_version(), '3.1', '>=');
    }

    /**
     * Helper method to get the version of the currently installed WooCommerce
     *
     * @since 3.0.0
     * @return string woocommerce version number or null
     */
    protected static function get_wc_version() {
        return defined('WC_VERSION') && WC_VERSION ? WC_VERSION : NULL;
    }

    /**
     * Determines if an order contains only virtual products.
     *
     * @since 4.5.0
     * @param \WC_Order $order the order object
     * @return bool
     */
    public static function is_order_virtual(\WC_Order $order) {
        $is_virtual = TRUE;
        foreach( $order->get_items() as $item ) {
            if( self::is_wc_version_gte_3_0() ) {
                $product = $item->get_product();
            } else {
                $product = $order->get_product_from_item($item);
            }
            // once we've found one non-virtual product we know we're done, break out of the loop
            if( $product && ! $product->is_virtual() ) {
                $is_virtual = FALSE;
                break;
            }
        }
        return $is_virtual;
    }

    /**
     * Determines if the installed version of WooCommerce is 3.0 or greater.
     *
     * @since 4.6.0
     * @return bool
     */
    public static function is_wc_version_gte_3_0() {
        return self::get_wc_version() && version_compare(self::get_wc_version(), '3.0', '>=');
    }

    /**
     * Safely get and trim data from $_POST
     *
     * @since 3.0.0
     * @param string $key array key to get from $_POST array
     * @return string value from $_POST or blank string if $_POST[ $key ] is not set
     */
    public static function get_post($key) {
        if( isset($_POST[$key]) ) {
            return trim($_POST[$key]);
        }
        return '';
    }

    /**
     * Safely get and trim data from $_REQUEST
     *
     * @since 3.0.0
     * @param string $key array key to get from $_REQUEST array
     * @return string value from $_REQUEST or blank string if $_REQUEST[ $key ] is not set
     */
    public static function get_request($key) {
        if( isset($_REQUEST[$key]) ) {
            return trim($_REQUEST[$key]);
        }
        return '';
    }

    /**
     * Get the count of notices added, either for all notices (default) or for one
     * particular notice type specified by $notice_type.
     *
     * WC notice functions are not available in the admin
     *
     * @since 3.0.2
     * @param string $notice_type The name of the notice type - either error, success or notice. [optional]
     * @return int
     */
    public static function wc_notice_count($notice_type = '') {
        if( function_exists('wc_notice_count') ) {
            return wc_notice_count($notice_type);
        }
        return 0;
    }

    /**
     * Add and store a notice.
     *
     * WC notice functions are not available in the admin
     *
     * @since 3.0.2
     * @param string $message The text to display in the notice.
     * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional]
     */
    public static function wc_add_notice($message, $notice_type = 'success') {
        if( function_exists('wc_add_notice') ) {
            wc_add_notice($message, $notice_type);
        }
    }

    /**
     * Print a single notice immediately
     *
     * WC notice functions are not available in the admin
     *
     * @since 3.0.2
     * @param string $message The text to display in the notice.
     * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional]
     */
    public static function wc_print_notice($message, $notice_type = 'success') {
        if( function_exists('wc_print_notice') ) {
            wc_print_notice($message, $notice_type);
        }
    }

    /**
     * Gets the full URL to the log file for a given $handle
     *
     * @since 4.0.0
     * @param string $handle log handle
     * @return string URL to the WC log file identified by $handle
     */
    public static function get_wc_log_file_url($handle) {
        return admin_url(sprintf('admin.php?page=wc-status&tab=logs&log_file=%s-%s-log', $handle, sanitize_file_name(wp_hash($handle))));
    }

    /**
     * Gets the current WordPress site name.
     *
     * This is helpful for retrieving the actual site name instead of the
     * network name on multisite installations.
     *
     * @since 4.6.0
     * @return string
     */
    public static function get_site_name() {
        return ( is_multisite() ) ? get_blog_details()->blogname : get_bloginfo('name');
    }

    /**
     * Determines if the installed version of WooCommerce is less than 3.0.
     *
     * @since 4.6.0
     * @return bool
     */
    public static function is_wc_version_lt_3_0() {
        return self::get_wc_version() && version_compare(self::get_wc_version(), '3.0', '<');
    }

    /**
     * Determines if the installed version of WooCommerce is less than 3.1.
     *
     * @since 4.6.5
     * @return bool
     */
    public static function is_wc_version_lt_3_1() {
        return self::get_wc_version() && version_compare(self::get_wc_version(), '3.1', '<');
    }

    /**
     * Determines if the installed version of WooCommerce meets or exceeds the
     * passed version.
     *
     * @since 4.7.3
     *
     * @param string $version version number to compare
     * @return bool
     */
    public static function is_wc_version_gte($version) {
        return self::get_wc_version() && version_compare(self::get_wc_version(), $version, '>=');
    }

    /**
     * Determines if the installed version of WooCommerce is lower than the
     * passed version.
     *
     * @since 4.7.3
     *
     * @param string $version version number to compare
     * @return bool
     */
    public static function is_wc_version_lt($version) {
        return self::get_wc_version() && version_compare(self::get_wc_version(), $version, '<');
    }

    /**
     * Returns true if the installed version of WooCommerce is greater than $version
     *
     * @since 2.0.0
     * @param string $version the version to compare
     * @return boolean true if the installed version of WooCommerce is > $version
     */
    public static function is_wc_version_gt($version) {
        return self::get_wc_version() && version_compare(self::get_wc_version(), $version, '>');
    }
}