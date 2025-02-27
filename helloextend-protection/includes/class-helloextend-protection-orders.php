<?php

/**
 * Extend For WooCommerce Orders class
 *
 * @since      1.0.0
 * @package    HelloExtend_Protection
 * @subpackage HelloExtend_Protection/admin
 *
 * Description: The Orders functionality of the plugin.
 *  It hooks onto the WooCommerces order actions and makes API requests to Extend.
 *  It uses the Orders Upsert API (https://docs.extend.com/reference/ordersupsert-1)
 *
 * Features:
- Creates/Updates Orders in Extend
- Creates/Cancels Contracts in Extend
- Searches for Orders in Extend
 **/

// Prevent direct access to the file
if (! defined('ABSPATH') ) {
    exit;
}

class HelloExtend_Protection_Orders
{

    /**
     * The ID of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string $helloextend_protection The ID of this plugin.
     */
    private $helloextend_protection;

    /**
     * The version of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string $version The current version of this plugin.
     */
    private $version;

    /**
     * The settings of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string|array $settings The current options of this plugin.
     */

    private array $settings;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $helloextend_protection The name of this plugin.
     * @param string $version           The version of this plugin.
     * @since 1.0.0
     */
    public function __construct($helloextend_protection, $version)
    {
        $this->helloextend_protection = $helloextend_protection;
        $this->version           = $version;
        /* retrieve environment variables */
        $this->settings = HelloExtend_Protection_Global::get_helloextend_settings();

        // Hook the callback function to the 'woocommerce_new_order' action
        add_action('woocommerce_checkout_order_processed', [$this, 'create_update_order'], 10, 1);

        // Hook the callback function to the order completed action
        add_action('woocommerce_order_status_completed', [$this, 'create_update_order'], 10, 1);
    }

    /**
     * get_helloextend_plans_and_products($order_items)
     * - builds line items array that will be put in order payload
     *
     * @param  $order
     * @param  bool $fulfill_now
     * @return array
     * @since  1.0.0
     */
    public function get_helloextend_plans_and_products($order, $fulfill_now = false)
    {

        $helloextend_plans = array();
        foreach ($order->get_items() as $item_id => $item) {
            $helloextend_meta_data = (array) $item->get_meta('_helloextend_data');

            // if  item id is for extend-product-protection gram $helloextend_meta_data and push it to the plans array
            if ($helloextend_meta_data['planId']) {
                $helloextend_plans[] = array(
                    'id'                 => $helloextend_meta_data['planId'],
                    'purchasePrice'      => $helloextend_meta_data['price'],
                    'covered_product_id' => $helloextend_meta_data['covered_product_id'],
                );
            }
        }

        // Loop through the order items and add them to the line_items array
        $helloextend_line_items = array();
        foreach ($order->get_items() as $item_id => $item) {

            $line_id    = $item->get_id();
            $product    = $item->get_product();
            $product_id = $product->get_id();

            // Get the first product category
            $product_category_ids = $product->get_category_ids();
            $cat_term = get_term_by('id', $product_category_ids[0], 'product_cat');
            $first_category = $cat_term->name;

            // if line_id matches any id in $helloextend_plans[], push the plan data into the covered product
            $plan = array();
            // Loop through the order items and find any items with plan data
            foreach ($helloextend_plans as $helloextend_plan) {
                if ($helloextend_plan['covered_product_id'] == $product_id) {
                    $plan = $helloextend_plan;
                }
            }

            // Get extend product id from settings
            $helloextend_product_protection_id = $this->settings['warranty_product_id'];

            // Add relevant data to the line_items array
            // if product id for extend-product-protection, do not add it to helloextend_line_items array
            if ($product_id != $helloextend_product_protection_id) {
                $helloextend_line_items[] = array(
                    'lineItemTransactionId' => $product->get_id(),
                    'product'               => array(
                        'id'            => $product->get_id(),
                        'title'         => $product->get_name(),
                        'category'      => $first_category,
                        'listPrice'     => (int) floatval($product->get_regular_price() * 100),
                        'purchasePrice' => (int) floatval($product->get_price() * 100),
                        'purchaseDate'  => $order->get_data()['date_created']->getTimestamp() * 1000,
                    ),
                    'quantity'              => $item->get_quantity(),
                    'fulfilledQuantity'     => !$fulfill_now ? 0 : $item->get_quantity(), // Will only fulfill based on contract event
                );

                // if $plan is not empty, add the plan to the current line item
                if (!empty($plan)) {
                    $helloextend_line_items[count($helloextend_line_items) - 1]['plan'] = $plan;
                }
            }
        }
        return $helloextend_line_items;
    }

    /**
     * Create/Update Orders in Extend
     *
     * @param string $order_id The ID of the order.
     * @since 1.0.0
     */
    public function create_update_order(string $order_id, array $order = null)
    {
        // If contract creation is disabled, return
        $contract_creation = $this->settings['helloextend_product_protection_contract_create'];
        if ($contract_creation == 0) {
            if ($this->settings['enable_helloextend_debug'] == 1) {
                HelloExtend_Protection_Logger::helloextend_log_error('Contract creation is disabled. No contract will be created for this order.');
            }
            return;
        }

        if ($order === null) {
            $order = wc_get_order($order_id);
        }
        $order_data = $order->get_data();

        // if contract creation is set to order create, call get_helloextend_plans_and_products
        $contract_creation_event = $this->settings['helloextend_product_protection_contract_create_event'];

        $helloextend_line_items = array();

        if ($contract_creation_event == 'Order Create') {
            // Will pass fulfill as true to the line items array to fulfill the contract immediately
            $helloextend_line_items = $this->get_helloextend_plans_and_products($order, true);
        } else {
            // Check if the current action hook is woocommerce_order_status_completed
            $called_action_hook = current_filter();
            if ($called_action_hook == 'woocommerce_order_status_completed') {
                $helloextend_line_items = $this->get_helloextend_plans_and_products($order, true);
            } else {
                // Does not fulfill product protection line items
                $helloextend_line_items = $this->get_helloextend_plans_and_products($order);
            }
        }

        // Check if shipping protection meta exists and add it as a line item
        $shipping_protection_quote_id = get_post_meta($order_id, '_shipping_protection_quote_id', true);
        // check if shipping protection meta exists
        if ($shipping_protection_quote_id) {
	        // phpcs:disable WordPress.PHP.DevelopmentFunctions
	        HelloExtend_Protection_Logger::helloextend_log_notice('Shipping Protection Meta Exists: ' . print_r($shipping_protection_quote_id, true));
	        // phpcs:enable

            // Push shipping protection line item into helloextend_line_items array
            $helloextend_line_items[] = array(
                'lineItemTransactionId' => $order_id . '-shipping',
                'quoteId'               => $shipping_protection_quote_id,
                'shipmentInfo'          => array(),
            );
        } else {
            if ($this->settings['enable_helloextend_debug'] == 1) {
                HelloExtend_Protection_Logger::helloextend_log_notice('Shipping Protection Meta Does Not Exist');
            }
        }

        $helloextend_order_data = array(
            'currency'      => $order_data['currency'],
            'customer'      => array(
                'email'           => $order_data['billing']['email'],
                'name'            => $order_data['billing']['first_name'] . ' ' . $order_data['billing']['last_name'],
                'phone'           => $order_data['billing']['phone'],
                'locale'          => 'en-US',
                'billingAddress'  => array(
                    'address1'    => $order_data['billing']['address_1'],
                    'city'        => $order_data['billing']['city'],
                    'country'     => $order_data['billing']['country'],
                    'postalCode'  => $order_data['billing']['postcode'],
                    'province'    => $order_data['billing']['state'],
                    'countryCode' => $order_data['billing']['country'],
                ),
                'shippingAddress' => array(
                    'address1'    => $order_data['shipping']['address_1'],
                    'city'        => $order_data['shipping']['city'],
                    'country'     => $order_data['shipping']['country'],
                    'postalCode'  => $order_data['shipping']['postcode'],
                    'province'    => $order_data['shipping']['state'],
                    'countryCode' => $order_data['shipping']['country'],
                ),
            ),
            'lineItems'     => $helloextend_line_items,
            'storeId'       => $this->settings['store_id'],
            'transactionId' => $order_id,
        );

        if ($this->settings['enable_helloextend_debug'] == 1) {
	        // phpcs:disable WordPress.PHP.DevelopmentFunctions
	        HelloExtend_Protection_Logger::helloextend_log_debug('Debug: Extend Order Data: ' . print_r(json_encode($helloextend_order_data, JSON_PRETTY_PRINT), true));
	        // phpcs:enable
        }

        // Get Token from Global function
        $token = HelloExtend_Protection_Global::get_helloextend_token();

        // Log the token
        if ($this->settings['enable_helloextend_debug'] == 1) {
            HelloExtend_Protection_Logger::helloextend_log_debug('Token: ' . $token);
        }

        $request_args = array(
            'method'  => 'PUT',
            'headers' => array(
                'Content-Type'          => 'application/json',
                'Accept'                => 'application/json; version=latest',
                'X-Extend-Access-Token' => $token,
            ),
            'body'    => json_encode($helloextend_order_data),
        );

        $response = wp_remote_request($this->settings['api_host'] . '/orders', $request_args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            HelloExtend_Protection_Logger::helloextend_log_error(' Order ID ' . $order->get_id() . ' : PUT request failed: ' . $error_message);
        } else {
            $response_code = wp_remote_retrieve_response_code($response);

            // New order will return 201, existing order will return 200
            if ($response_code === 201 || $response_code === 200) {
                // Only log if "Enable debugging Log" is enabled
                if ($this->settings['enable_helloextend_debug'] == 1) {
                    HelloExtend_Protection_Logger::helloextend_log_debug('Order ID ' . $order->get_id() . ' : PUT request successful: ' . wp_remote_retrieve_body($response));
                }
                // if put was successful and if there is a contract ID in the response, write it back to the order metadata at the lineitem level
                $data      = json_decode(wp_remote_retrieve_body($response));
                $contracts = array();

                if (isset($data->lineItems) && is_array($data->lineItems)) {
                    foreach ($data->lineItems as $lineItem) {
                        if (isset($lineItem->contractId) && isset($lineItem->product->name)) {
                            $contractId                          = $lineItem->contractId;
                            $lineItemTransactionId               = $lineItem->lineItemTransactionId;
                            $contracts[$lineItemTransactionId] = $contractId;
                        }
                    }

                    // add the contracts array at the order level
                    update_post_meta($order->get_id(), '_product_protection_contracts', $contracts);
                }
            } else {
                if ($this->settings['enable_helloextend_debug'] == 1) {
                    HelloExtend_Protection_Logger::helloextend_log_debug('Order  ID ' . $order->get_id() . ' : PUT request failed with status code ' . $response_code);
                    HelloExtend_Protection_Logger::helloextend_log_debug('Body response: ' . wp_remote_retrieve_body($response));
                } else {
                    HelloExtend_Protection_Logger::helloextend_log_error('Order  ID ' . $order->get_id() . ' : PUT request failed with status code ' . $response_code);
                }
            }
        }

        // make sure to remove any SP session value
        if (isset(WC()->session) && WC()->session->has_session()) {
            WC()->session->set('shipping_fee_remove', true);
            WC()->session->set('shipping_fee', false);
            WC()->session->set('shipping_fee_value', null);
            WC()->session->set('shipping_quote_id', null);
        }
    }
}
