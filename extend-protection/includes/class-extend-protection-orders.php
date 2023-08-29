<?php

/**
 * Extend For WooCommerce Orders class
 * @since 1.0.0
 * @package Extend_Protection
 * @subpackage Extend_Protection/admin
 *
 * Description: The Orders functionality of the plugin.
 *  It hooks onto the WooCommerces order actions and makes API requests to Extend.
 *  It uses the Orders Upsert API (https://docs.extend.com/reference/ordersupsert-1)
 *
 * Features:
- Creates/Updates Orders in Extend
- Creates/Cancels Contracts in Extend
- Searches for Orders in Extend
 *
 **/

class Extend_Protection_Orders {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $extend_protection The ID of this plugin.
     */
    private $extend_protection;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * The settings of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string|array $settings The current options of this plugin.
     */

    private array $settings;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $extend_protection The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */

    public function __construct( $extend_protection, $version ) {
        $this->extend_protection = $extend_protection;
        $this->version = $version;
        /* retrieve environment variables */
        $this->settings = Extend_Protection_Global::get_extend_settings();

        // Hook the callback function to the 'woocommerce_new_order' action
        add_action( 'woocommerce_checkout_order_processed', [ $this, 'create_update_order' ], 10, 1 );
    }

    /**
     * Create/Update Orders in Extend
     *
     * @param string $order_id The ID of the order.
     * @since    1.0.0
     */
    public function create_update_order(string $order_id, array $order = null)
    {
        if($order === null){
            $order = wc_get_order($order_id);
        }
        $order_data = $order->get_data();
        $order_items = $order->get_items();

        // Loop through the order items and find any items with plan data
        $extend_plans = array();
        foreach( $order->get_items() as $item_id => $item ){
            $extend_meta_data = (array)$item->get_meta('_extend_data');

            // if  item id is for extend-product-protection gram $extend_meta_data and push it to the plans array
            if ($extend_meta_data['planId']) {
                $extend_plans[] = array(
                    'id' => $extend_meta_data['planId'],
                    'purchasePrice' => $extend_meta_data['price'] * 100,
                    'covered_product_id' => $extend_meta_data['covered_product_id'],
                );
            }
        }

        // Loop through the order items and add them to the line_items array
        $extend_line_items = array();
        foreach( $order->get_items() as $item_id => $item ){

            $line_id = $item->get_id();
            $product = $item->get_product();
            $product_id = $product->get_id();

            // if line_id matches any id in $extend_plans[], push the plan data into the covered product
            $plan = array();
            foreach ($extend_plans as $extend_plan) {
                if ($extend_plan['covered_product_id'] == $product_id) {
                    $plan = $extend_plan;
                }
            }

            // Add relevant data to the line_items array
            // if product id for extend-product-protection, do not add it to extend_line_items array
            if ($product_id != extend_product_protection_id()) {
                $extend_line_items[] = array(
                    'lineItemTransactionId' => $product->get_id(),
                    'product' => array(
                        'id'            => $product->get_id(),
                        'title'         => $product->get_name(),
                        'category'      => 'Electronics',
                        'listPrice'     => $product->get_regular_price() * 100,
                        'purchasePrice' => $product->get_price() * 100,
                        'purchaseDate'  => $order_data['date_created']->getTimestamp() * 1000,
                    ),
                    'quantity'          => $item->get_quantity(),
                    'fulfilledQuantity' => $item->get_quantity(),
                );

                // if $plan is not empty, add the plan to the current line item
                if (!empty($plan)) {
                    $extend_line_items[count($extend_line_items) - 1]['plan'] = $plan;
                }
            }
        }

        // extend_log_notice("Extend Line Items: " . print_r(json_encode($extend_line_items, JSON_PRETTY_PRINT), true));

        $extend_order_data = Array(
            'currency' => $order_data['currency'],
            'customer' => Array(
                'email' =>  $order_data['billing']['email'],
                'name' => $order_data['billing']['first_name'] . ' ' . $order_data['billing']['last_name'],
                'phone' => $order_data['billing']['phone'],
                'locale' => 'en-US',
                'billingAddress' => Array(
                    'address1' => $order_data['billing']['address_1'],
                    'city' => $order_data['billing']['city'],
                    'country' => $order_data['billing']['country'],
                    'postalCode' => $order_data['billing']['postcode'],
                    'province' => $order_data['billing']['state'],
                    'countryCode' => $order_data['billing']['country']
                ),
                'shippingAddress' => Array(
                    'address1' => $order_data['shipping']['address_1'],
                    'city' => $order_data['shipping']['city'],
                    'country' => $order_data['shipping']['country'],
                    'postalCode' => $order_data['shipping']['postcode'],
                    'province' => $order_data['shipping']['state'],
                    'countryCode' => $order_data['shipping']['country']
                )
            ),
            'lineItems' => $extend_line_items,
            'storeId' => $this->settings['store_id'],
            'transactionId' => $order_id
        );

        if ( get_post_meta( $order->get_id(), '_shipping_protection_quote_id', true )){
            //shipping protection node
            $extend_order_data[] = array(
                "quoteId"               => get_post_meta($order->get_id(), '_shipping_protection_quote_id', true),
                "shipmentInfo"          => array()
            );
        }

        if ($this->settings['enable_extend_debug'] == 1){
            Extend_Protection_Logger::extend_log_debug("Debug: Extend Order Data: " . print_r(json_encode($extend_order_data, JSON_PRETTY_PRINT), true));
        }

        $request_args = array(
            'method' => 'PUT',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json; version=latest',
                'X-Extend-Access-Token' => $this->settings['api_key'],
            ),
            'body' => json_encode($extend_order_data),
        );

        $response = wp_remote_request($this->settings['api_host'].'/orders', $request_args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            Extend_Protection_Logger::extend_log_error(" Order ID ".$order->get_id()." : PUT request failed: " . $error_message);
        } else {
            $response_code = wp_remote_retrieve_response_code($response);

            // New order will return 201, existing order will return 200
            if ($response_code === 201 || $response_code === 200) {
                // Only log if "Enable debugging Log" is enabled
                if ($this->settings['enable_extend_debug'] == 1){
                    Extend_Protection_Logger::extend_log_debug("Order ID ".$order->get_id()." : PUT request successful: " . wp_remote_retrieve_body($response));
                }
                //if put was successful and if there is a contract ID in the response, write it back to the order metadata at the lineitem level
                $data       = json_decode(wp_remote_retrieve_body($response));
                $contracts  = array();

                if (isset($data->lineItems) && is_array($data->lineItems)) {
                    foreach ($data->lineItems as $lineItem) {
                        if (isset($lineItem->contractId) && isset($lineItem->product->name)) {
                            $contractId                         = $lineItem->contractId;
                            $lineItemTransactionId              = $lineItem->lineItemTransactionId;
                            $contracts[$lineItemTransactionId]  = $contractId;
                        }
                    }

                    //add the contracts array at the order level
                    update_post_meta($order->get_id(), '_product_protection_contracts', $contracts);
                }

            } else {
                if ($this->settings['enable_extend_debug'] == 1){
                    Extend_Protection_Logger::extend_log_debug('Order  ID '.$order->get_id().' : PUT request failed with status code ' . $response_code) ;
                    Extend_Protection_Logger::extend_log_debug('Body response: '. wp_remote_retrieve_body($response));
                }else{
                    Extend_Protection_Logger::extend_log_error('Order  ID '.$order->get_id().' : PUT request failed with status code ' . $response_code );
                }
            }
        }

        //make sure to remove any SP session value
        WC()->session->set('shipping_fee_remove',   true); // Adding a comment
        WC()->session->set('shipping_fee',          false);
        WC()->session->set('shipping_fee_value',    null);
        WC()->session->set('shipping_quote_id',    null);
    }
}
