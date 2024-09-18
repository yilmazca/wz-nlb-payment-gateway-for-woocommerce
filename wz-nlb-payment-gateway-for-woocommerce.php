<?php
/*
* Plugin Name: WZ NLB Payment Gateway
* Plugin URI: https://github.com/yilmazca/nlb-payment-gateway-for-woocommerce.git
* Description: Credit card payments gateway to accept the payment on your woocommerce store from NLB Bank Gateway.
* Author: ibrahim YILMAZ
* Author URI: https://yilmazca.com
* Version: 1.0
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/
if (!defined('ABSPATH')) {
    exit;
};

define('WZNLB_VERSION', '1.0.0');
define('WZNLB_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
define('WZNLB_PLUGIN_PATH', untrailingslashit(plugin_dir_path(__FILE__)));

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) return;

add_action('plugins_loaded', 'initialize_gateway_class');
function initialize_gateway_class()
{
    class WC_WZ_NLBGateway extends WC_Payment_Gateway
    {
        //Start Plugin Codes from here

        public function __construct()
        {
            $this->id = 'wznlb'; // payment gateway ID
            $this->icon = ''; // payment gateway icon
            //$this->base_url = WZNLB_PLUGIN_URL . '/debit.php';
            $this->has_fields = true; // for custom credit card form
            $this->title = __('NLB Payment Gateway', 'text-domain'); // vertical tab title
            $this->method_title = __('NLB Payment Gateway', 'text-domain'); // payment method name
            $this->method_description = __('WZ NLB Payment Gateway', 'text-domain'); // payment method description

            // load backend options fields
            $this->init_form_fields();

            // load the settings.
            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');


            // Action hook to save the settings
            if (is_admin()) {
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            }

            add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
            add_action('woocommerce_api_' . strtolower(get_class($this)), array(&$this, 'webhook'));
        }

        public function init_form_fields()
        {

            $this->form_fields = array(
                'enabled' => array(
                    'title'       => __('Enable/Disable', 'text-domain'),
                    'label'       => __('Enable NLB Payment Gateway', 'text-domain'),
                    'type'        => 'checkbox',
                    'description' => __('This enable the NLB Payment Gateway which allow to accept payment through creadit card.', 'text-domain'),
                    'default'     => 'no',
                    'desc_tip'    => true
                ),
                'selected_payment_mode' => array(
                    'title'       => __('Payment Mode', 'text-domain'),
                    'type'        => 'select',
                    'options'     => array(
                        'redirect' => __('Pay on NLB Bank Page', 'text-domain'),
                    ),
                    'default'     => 'redirect',
                    'description' => __('In future versions, you will be able to directly accept payments on your own website.', 'text-domain'),
                ),
                'title' => array(
                    'title'       => __('Title', 'text-domain'),
                    'type'        => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'text-domain'),
                    'default'     => __('Credit Card on NLB Payment Gateway', 'text-domain'),
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => __('Description', 'text-domain'),
                    'type'        => 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.', 'text-domain'),
                    'default'     => __('You can use this payment method to make payments through the NLB Bank payment network.', 'text-domain'),
                ),
                'selected_mode' => array(
                    'title'       => __('Select Mode', 'text-domain'),
                    'type'        => 'select',
                    'options'     => array(
                        'test' => __('Test Mode', 'text-domain'),
                        'live' => __('Live Mode', 'text-domain'),
                    ),
                    'default'     => 'test',
                    'description' => __('Please select the mode in which the payment type will be operated.', 'text-domain'),
                ),
                'live_hr' => array(
                    'title'       => __('<h3>LIVE API Credentials</h3>', 'text-domain'),
                    'type'        => 'hidden',
                    'description' => '<hr>',
                ),
                'live_apiUsername' => array(
                    'title'       => __('API Username', 'text-domain'),
                    'type'        => 'text'
                ),
                'live_apiPassword' => array(
                    'title'       => __('API Password', 'text-domain'),
                    'type'        => 'password',
                ),
                'live_apiKey' => array(
                    'title'       => __('API Key', 'text-domain'),
                    'type'        => 'text'
                ),
                'live_sharedSecret' => array(
                    'title'       => __('API Shared Secret', 'text-domain'),
                    'type'        => 'password'
                ),
                'live_publicIntegrationKey' => array(
                    'title'       => __('API Public Integration Key', 'text-domain'),
                    'type'        => 'text'
                ),

                'test_hr' => array(
                    'title'       => __('<h3>Test API Credentials</h3>', 'text-domain'),
                    'type'        => 'hidden',
                    'description' => '<hr>',
                ),
                'test_apiUsername' => array(
                    'title'       => __('API Username', 'text-domain'),
                    'type'        => 'text'
                ),
                'test_apiPassword' => array(
                    'title'       => __('API Password', 'text-domain'),
                    'type'        => 'password',
                ),
                'test_apiKey' => array(
                    'title'       => __('API Key', 'text-domain'),
                    'type'        => 'text'
                ),
                'test_sharedSecret' => array(
                    'title'       => __('API Shared Secret', 'text-domain'),
                    'type'        => 'password'
                ),
                'test_publicIntegrationKey' => array(
                    'title'       => __('API Public Integration Key', 'text-domain'),
                    'type'        => 'text'
                ),

            );
        }

        public function process_payment($order_id)
        {

            $order = wc_get_order($order_id);

            $prefix = 'test_';
            if ($this->get_option("selected_mode") == "live")
                $prefix = 'live_';

            $apiUsername =  $this->get_option($prefix . "apiUsername");
            $apiPassword =  $this->get_option($prefix . "apiPassword");
            $apiKey = $this->get_option($prefix . "apiKey");

            $sharedSecret =  $this->get_option($prefix . "sharedSecret");

            //Check payment method mode
            if ($this->get_option('selected_payment_mode') == 'redirect') {
                require_once('nlb-direct-payment.php');

                $nlbDirectPayment = new WC_WZ_NLBGatewayDirectPayment($apiUsername, $apiPassword, $apiKey, $sharedSecret);

                $nlbDirectPayment->cbURL = site_url() . '/?wc-api=wc_wz_nlbgateway';
                $nlbDirectPayment->scURL = site_url() . '/?wc-api=wc_wz_nlbgateway&status=success&order_id=' . $order_id;
                $nlbDirectPayment->erURL = site_url() . '/?wc-api=wc_wz_nlbgateway&status=error&order_id=' . $order_id;
                $nlbDirectPayment->ccURL = wc_get_checkout_url();

                $nlbDirectPayment->customer(
                    $order->get_billing_first_name(),
                    $order->get_billing_last_name(),
                    $order->get_billing_email(),
                    $order->get_billing_address_1(),
                );

                $result = $nlbDirectPayment->debit($order->get_total(), get_option('woocommerce_currency'), '');

                if (!$result == null)
                    $paymentURL = $result;

                return array(
                    'result' => 'success',
                    'redirect' => $paymentURL,
                );
            }
        }

        function webhook()
        {
            if (!isset($_GET['order_id']) || !isset($_GET['transactionId'])) {
                wp_redirect(wc_get_page_permalink('checkout'));
                exit;
            }

            $order_id = $_GET['order_id'];
            $transactionId = $_GET['transactionId'];
            $order = wc_get_order($order_id);

            if ($_GET['status'] == 'success') {

                $order->payment_complete($order_id);

                $order->add_order_note("Payment processed and approved successfully with NLB Transaction ID: $transactionId");

                wc_empty_cart();

                wp_redirect($this->get_return_url($order));

                exit;
            }

            if (isset($_GET['transactionId']) && $_GET['status'] == 'error') {

                $order->update_status('failed', 'Payment not successful');

                $order->add_order_note("Payment not successful NLB Transaction ID: $transactionId");

                wc_add_notice(__('Payment not successful NLB Transaction ID: ' . $transactionId, 'text-domain'), 'error');

                wp_redirect(add_query_arg('error', 'true', wc_get_checkout_url()));

                exit;
            }
        }
    }
}


add_filter('woocommerce_payment_gateways', 'add_custom_gateway_class');
function add_custom_gateway_class($gateways)
{
    $gateways[] = 'WC_WZ_NLBGateway'; // payment gateway class name
    return $gateways;
}
