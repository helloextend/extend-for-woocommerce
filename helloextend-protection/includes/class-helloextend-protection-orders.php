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

    private const TRANSACTION_STORE_PREFIX = 'STORE::';
    private const TRANSACTION_ORDER_PREFIX = '::ORDER::';
    private const TRANSACTION_PRODUCT_PREFIX = '::PRODUCT::';
    private const TRANSACTION_OLI_PREFIX = '::OLI::';

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
        $this->settings = HelloExtend_Protection_Global::helloextend_get_settings();
        $this->helloextend_product_protection_id = $this->settings['warranty_product_id'];
 
        // Hook the callback function to the 'woocommerce_new_order' action
        add_action('woocommerce_checkout_order_processed', [$this, 'create_update_order'], 10, 1);

        // Hook the callback function to the order completed action
        add_action('woocommerce_order_status_completed', [$this, 'create_update_order'], 10, 1);

	    // Hook the callback function to the order cancelled action
	    add_action('woocommerce_order_status_cancelled', [$this, 'cancel_order'], 10, 1);
	    add_action('woocommerce_order_status_refunded', [$this, 'cancel_order'], 10, 1);

        // Handle refunded orders
        add_action('woocommerce_order_refunded', [$this, 'handle_contract_refund'], 10, 2);
    }

    private function get_product_image_url($product)
    {
        // Only accept valid WooCommerce product objects
        if (! $product instanceof \WC_Product) {
            return null;
        }

        // Try featured image first
        $image_id = (int) $product->get_image_id();
        
        // If no featured image, fall back to the first gallery image
        if (empty($image_id) && method_exists($product, 'get_gallery_image_ids')) {
            $gallery = (array) $product->get_gallery_image_ids();
            $image_id = isset($gallery[0]) ? (int) $gallery[0] : 0;
        }
        
        // If still empty, try parent (for variations)
        if (empty($image_id) && method_exists($product, 'get_parent_id')) {
            $parent_id = (int) $product->get_parent_id();
            if ($parent_id) {
                $parent = wc_get_product($parent_id);
                if ($parent instanceof \WC_Product) {
                    // Parent’s featured image
                    $image_id = (int) $parent->get_image_id();
                    // Parent’s first gallery image as fallback
                    if (empty($image_id) && method_exists($parent, 'get_gallery_image_ids')) {
                        $gallery = (array) $parent->get_gallery_image_ids();
                        $image_id = isset($gallery[0]) ? (int) $gallery[0] : 0;
                    }
                }
            }
        }

        // No image available
        if (empty($image_id)) {
            return null;
        }

        // Retrieve URL safely: prefer wp_get_attachment_image_url() on WP ≥4.4
        if (function_exists('wp_get_attachment_image_url')) {
            $url = wp_get_attachment_image_url($image_id, 'full');
        } else {
            $src = wp_get_attachment_image_src($image_id, 'full');
            $url = (is_array($src) && isset($src[0])) ? $src[0] : null;
        }

        return $url ?: null;
    }

    private function is_item_helloextend($item)
    {
        $helloextend_meta = $item->get_meta('_helloextend_data');
        return $item->get_product_id() == $this->helloextend_product_protection_id && isset($helloextend_meta) && isset($helloextend_meta['planId']);
    }

    private function is_item_helloextend_no_lead($item)
    {
        $helloextend_meta = $item->get_meta('_helloextend_data');
        return $this->is_item_helloextend($item) && !isset($helloextend_meta['leadToken']);
    }

    private function is_item_helloextend_lead($item)
    {
        $helloextend_meta = $item->get_meta('_helloextend_data');
        return $this->is_item_helloextend($item) && isset($helloextend_meta['leadToken']);
    }

    private function get_price_in_cents($item_price)
    {
        return (int) floatval($item_price * 100);
    }

    private function get_purchased_leads($order)
    {
        $lead_line_items = array();
        foreach ($order->get_items() as $item) {
            if ($this->is_item_helloextend_lead($item)) {
                $helloextend_meta = $item->get_meta('_helloextend_data');

                $lead_line_items[] = array(
                    'leadToken'             => $helloextend_meta['leadToken'],
                    'lineItemTransactionId' => self::TRANSACTION_STORE_PREFIX . $this->settings['store_id'] . self::TRANSACTION_ORDER_PREFIX . $order->get_id() . self::TRANSACTION_PRODUCT_PREFIX . $helloextend_meta['covered_product_id'] . self::TRANSACTION_OLI_PREFIX . $item->get_id(),
                    'plan'                  => array(
                                                'id' => $helloextend_meta['planId'],
                                                'purchasePrice' => $helloextend_meta['price'],
                                            ),
                    'quantity'              => $item->get_quantity()
                    );
            }
        }

        return empty($lead_line_items) ? null : $lead_line_items;
    }

    /**
     * helloextend_get_plans_and_products($order, $fulfill_now = false)
     * - builds line items array that will be put in order payload
     *
     * @param  $order
     * @param  bool $fulfill_now
     * @return array
     * @since  1.0.0
     */
    public function helloextend_get_plans_and_products($order, $fulfill_now = false)
    {

        $helloextend_plans = array();
        foreach ($order->get_items() as $item_id => $item) {
            $helloextend_meta_data = (array) $item->get_meta('_helloextend_data');

            // if  item id is for extend-product-protection grab $helloextend_meta_data and push it to the plans array
            if ($this->is_item_helloextend_no_lead($item) && $helloextend_meta_data['planId']) {
                $helloextend_plans[] = array(
                    'id'                 => $helloextend_meta_data['planId'],
                    'purchasePrice'      => $helloextend_meta_data['price'],
                    'covered_product_id' => $helloextend_meta_data['covered_product_id']
                );
            }
        }

        $leads = $this->get_purchased_leads($order);

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

            // Add relevant data to the line_items array
            // if product id for extend-product-protection, do not add it to helloextend_line_items array
            if ($product_id != $this->helloextend_product_protection_id) {
               
                $image_url = $this->get_product_image_url($product);

                $helloextend_line_item = array(
                    'lineItemTransactionId' => $product->get_id(),
                    'product'               => array(
                        'id'            => $product->get_id(),
                        'title'         => $product->get_name(),
                        'category'      => $first_category,
                        'listPrice'     => $this->get_price_in_cents($product->get_regular_price() * 100),
                        'purchasePrice' => $this->get_price_in_cents($product->get_price() * 100),
                        'purchaseDate'  => $order->get_data()['date_created']->getTimestamp() * 1000,
                        'imageUrl'      => $image_url
                    ),
                    'quantity'              => $item->get_quantity(),
                    'fulfilledQuantity'     => !$fulfill_now ? 0 : $item->get_quantity(), // Will only fulfill based on contract event
                );

                // if $plan is not empty, add the plan to the current line item
                if (!empty($plan)) {
                    $helloextend_line_item['plan'] = $plan;
                }

                $helloextend_line_items[] = $helloextend_line_item;
            }
        }

        if ($leads) {
            $helloextend_line_items = array_merge($helloextend_line_items, $leads);
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
        $items = $order->get_items();
        $product_id_array = array();
        foreach($items as $item){
            $product_id_array[] = $item->get_product_id();
        }
        $product_id_list = implode(',', $product_id_array);

        // if contract creation is set to order create, call helloextend_get_plans_and_products
        $contract_creation_event = $this->settings['helloextend_product_protection_contract_create_event'];

        $helloextend_line_items = array();

        if ($contract_creation_event == 'Order Create') {
            // Will pass fulfill as true to the line items array to fulfill the contract immediately
            $helloextend_line_items = $this->helloextend_get_plans_and_products($order, true);
        } else {
            // Check if the current action hook is woocommerce_order_status_completed
            $called_action_hook = current_filter();
            if ($called_action_hook == 'woocommerce_order_status_completed') {
                $helloextend_line_items = $this->helloextend_get_plans_and_products($order, true);
            } else {
                // Does not fulfill product protection line items
                $helloextend_line_items = $this->helloextend_get_plans_and_products($order);
            }
        }

        // Check if shipping protection meta exists and add it as a line item
        $shipping_protection_quote_id = get_post_meta($order_id, '_shipping_protection_quote_id', true);
        // check if shipping protection meta exists
        if ($shipping_protection_quote_id) {
	        // phpcs:disable WordPress.PHP.DevelopmentFunctions
            if ($this->settings['enable_helloextend_debug'] == 1) {
                HelloExtend_Protection_Logger::helloextend_log_debug(
                    'Shipping Protection Meta Exists: ' . (string) $shipping_protection_quote_id
                );
            }
	        // phpcs:enable

            // on order completion send shipmentInfo to activate the contract, otherwise send an empty array to create the contract
            $shipmentInfo = array();
            $store_raw_country = get_option('woocommerce_default_country');
            $split_country = explode(":", $store_raw_country);
            $store_country = $split_country[0];
            $store_state = $split_country[1];
            $called_action_hook = current_filter();
            if ($called_action_hook == 'woocommerce_order_status_completed') {
                $arg = array(
                    'limit'  => -1,
                    'status' => 'publish',
                    'return' => 'ids'
                );
                $shipmentInfo[] = array(
                    "shipmentDate" => time(),
                    "shippingProvider" => "custom",
                    "trackingId" => "woocommerce-shipping",
                    "productIds" => $product_id_list,
                    "destination" => array(
                        "address1" => $order_data['shipping']['address_1'],
                        "address2" => $order_data['shipping']['address_2'] ? $order_data['shipping']['address_2'] : null,
                        "city" => $order_data['shipping']['city'],
                        "companyName" => '',
                        "countryCode" => $order_data['shipping']['country'],
                        "personName" => $order_data['shipping']['first_name'] . ' ' . $order_data['shipping']['last_name'],
                        "phone" => '',
                        "postalCode" => $order_data['shipping']['postcode'],
                        "provinceCode" => $order_data['shipping']['state']
                    ),
                    "source" => array(
                        "address1" => get_option('woocommerce_store_address'),
                        "address2" => get_option('woocommerce_store_address_2'),
                        "city" => get_option('woocommerce_store_city'),
                        "companyName" => '',
                        "countryCode" => $store_country,
                        "personName" => '',
                        "phone" => get_option('woocommerce_store_phone'),
                        "postalCode" =>get_option('woocommerce_store_postcode'),
                        "provinceCode" => $store_state
                    )
                );
            }
            // Push shipping protection line item into helloextend_line_items array
            $helloextend_line_items[] = array(
                'lineItemTransactionId' => $order_id . '-shipping',
                'quoteId'               => $shipping_protection_quote_id,
                'shipmentInfo'          => $shipmentInfo,
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
        $token = HelloExtend_Protection_Global::helloextend_get_token();

        // If token exists, log successful token
        if ($this->settings['enable_helloextend_debug'] == 1 && $token) {
            HelloExtend_Protection_Logger::helloextend_log_debug('Access token created successfully');
        }
        // If token does not exist, log error
        if ($this->settings['enable_helloextend_debug'] == 1 && !$token) {
            HelloExtend_Protection_Logger::helloextend_log_error('Error:Access token was not created, exiting order creation');
            return;
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

	/**
	 * Cancel Orders/Contracts in Extend
	 *
	 * @param string $order_id The ID of the order.
	 * @since 1.0.0
	 */
	// Accept a WC_Order object or null.
	// Using `mixed` keeps compatibility with PHP <8 (no union types).
	public function cancel_order(string $order_id, $order = null) /* @param WC_Order|null $order */
	{
        $order = wc_get_order($order_id);

        if ( ! $order instanceof WC_Order ) {
            HelloExtend_Protection_Logger::helloextend_log_error(
                'Cannot cancel Extend order – WooCommerce order ' . $order_id . ' not found.'
            );
            return;
        }

		// Get Token from Global function
		$token = HelloExtend_Protection_Global::helloextend_get_token();

		// If token exists, log successful token
		if ($this->settings['enable_helloextend_debug'] == 1 && $token) {
			HelloExtend_Protection_Logger::helloextend_log_debug('Access token created successfully');
		}
		// If token does not exist, log error
		if ($this->settings['enable_helloextend_debug'] == 1 && !$token) {
			HelloExtend_Protection_Logger::helloextend_log_error('Error:Access token was not created, exiting order cancel');
			return;
		}

		// GET the order uuid
		// {{API_HOST}}/orders/search?transactionId={{transactionId}}
		$request_args = array(
			'method'  => 'GET',
			'headers' => array(
				'Content-Type'          => 'application/json',
				'Accept'                => 'application/json; version=latest',
				'X-Extend-Access-Token' => $token,
			),
		);

        $endpoint = add_query_arg(
            array( 'transactionId' => (string) $order->get_id() ),
            $this->settings['api_host'] . '/orders/search'
        );
		$response = wp_remote_request( $endpoint, $request_args );
		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			HelloExtend_Protection_Logger::helloextend_log_error(' Order ID ' . $order->get_id() . ' : GET request failed: ' . $error_message.', cannot cancel extend order');
		} else {
			$response_code = wp_remote_retrieve_response_code( $response );
			if ( $response_code >= 200 && $response_code < 300 ) {
				// if GET was successful retrieve the response and find order uuid
				$data               = json_decode( wp_remote_retrieve_body( $response ) );
				$extend_order_uuid  = null;
				if ( isset( $data->orders ) && is_array( $data->orders ) && ! empty( $data->orders[0]->id ) ) {
					$extend_order_uuid = $data->orders[0]->id;
				}
				if ( $extend_order_uuid ) {
					//POST cancel the order (uuid)
					//{{API_HOST}}/orders/{{orderId}}/cancel
					$cancel_request_args = array(
						'method'  => 'POST',
						'headers' => array(
							'Content-Type'          => 'application/json',
							'Accept'                => 'application/json; version=latest',
							'X-Extend-Access-Token' => $token,
						),
					);
					$cancel_response      = wp_remote_request(
						$this->settings['api_host'] . '/orders/' . $extend_order_uuid . '/cancel',
						$cancel_request_args
					);
					$cancel_response_code = wp_remote_retrieve_response_code( $cancel_response );
					if ( $cancel_response_code >= 200 && $cancel_response_code < 300 ) {
						HelloExtend_Protection_Logger::helloextend_log_notice(
							'Order ID ' . $order->get_id() . ' : Cancelled Extend order UUID: ' . $extend_order_uuid
						);
					} else {
						HelloExtend_Protection_Logger::helloextend_log_error(
							'Order ID ' . $order->get_id() . ' : Could not cancel Extend order (status ' .
							$cancel_response_code . ')'
						);
					}
				}else{
                    return;
                }

			} else {
				HelloExtend_Protection_Logger::helloextend_log_error(
					'Order ID ' . $order->get_id() . ' : GET request returned status ' . $response_code
				);
                return;
            }
        }
    }

    public function handle_contract_refund(string $order_id, string $refund_id)
    {
        $order = wc_get_order($order_id);

        if ( ! $order instanceof WC_Order ) {
            HelloExtend_Protection_Logger::helloextend_log_error(
                'Cannot refund Extend order - WooCommerce order ' . $order_id . ' not found.'
            );
            return;
        }

        $refund = wc_get_order($refund_id);

        if (!$refund instanceof WC_Order_Refund) {
            HelloExtend_Protection_Logger::helloextend_log_error(
                'Cannot refund Extend order - WooCommerce refund ' . $refund_id . ' not found.'
            );
            return;
        }

        $refund_items = $refund->get_items();
        $refunded_contracts = [];

        // Get contract IDs on this order and add them to contracts array
        $contracts = get_post_meta($order->get_id(), '_product_protection_contracts', true);
        foreach($refund_items as $refund_item) {
            $refunded_item_id = $refund_item->get_meta('_refunded_item_id');
            $order_item = $order->get_item($refunded_item_id);
            $helloextend_data = $order_item->get_meta('_helloextend_data');
            
            if ($refund_item->get_product_id() == $this->helloextend_product_protection_id && $helloextend_data) {
                foreach($contracts as $covered_product => $contract_id) {
                    if ($helloextend_data['covered_product_id'] == $covered_product) {
                        
                        $refunded_contracts[] = $contract_id;
                        break;
                    }
                }
            }
        }


		// Get Token from Global function
		$token = HelloExtend_Protection_Global::helloextend_get_token();

		// If token exists, log successful token
		if ($this->settings['enable_helloextend_debug'] == 1 && $token) {
			HelloExtend_Protection_Logger::helloextend_log_debug('Access token created successfully');
		}
		// If token does not exist, log error
		if ($this->settings['enable_helloextend_debug'] == 1 && !$token) {
			HelloExtend_Protection_Logger::helloextend_log_error('Error:Access token was not created, exiting order refund');
			return;
		}


        $cancellation_errors = [];
        // Cancel the contract
        // {{API_HOST}}/contracts/{{contractId}}/cancel
        foreach ($refunded_contracts as $contract_id) {
            $contract_cancel_endpoint = $this->settings['api_host'] . '/contracts/' . $contract_id . '/cancel';
            $contract_cancel_args = array(
                'method'    => 'POST',
                'headers'   => array(
                    'Content-Type'          => 'application/json',
                    'Accept'                => 'application/json; version=latest',
                    'X-Extend-Access-Token' => $token,
                ),
            );

            $contract_cancel_response = wp_remote_request( $contract_cancel_endpoint, $contract_cancel_args );

            if (is_wp_error($contract_cancel_response)) {
                $error_message = $contract_cancel_response->get_error_message();
                $cancellation_errors[] = 'Cancel Contract Failed for ID ' . $contract_id . ' : POST request failed: ' . $error_message.', cannot cancel contract';
            }

            $contract_cancel_response_code = wp_remote_retrieve_response_code( $contract_cancel_response );
            $data = json_decode(wp_remote_retrieve_body( $contract_cancel_response ));
            if ($contract_cancel_response_code < 200 || $contract_cancel_response_code >= 300) {
                $cancellation_errors[] = 'Contract cancel for ID ' . $contract_id . ' : POST request returned status ' . $contract_cancel_response_code . ' with body ' . $data;
            }

            if ($this->settings['enable_helloextend_debug']) {
                HelloExtend_Protection_Logger::helloextend_log_debug(
                    'Contract ID ' . $contract_id . ' canceled successfully.'
                );
            }
        }

        if (!empty($cancellation_errors)) {
            HelloExtend_Protection_Logger::helloextend_log_error(
                'Some contracts failed to cancel: ' . implode('; ', $cancellation_errors)
            );
        }

        if ($this->settings['enable_helloextend_debug'] == 1 && !empty($refunded_contracts)) {
            HelloExtend_Protection_Logger::helloextend_log_debug(
                'Contract IDs ' . join(", ", $refunded_contracts) . ' canceled succesfully.'
            );
        }
    }
}