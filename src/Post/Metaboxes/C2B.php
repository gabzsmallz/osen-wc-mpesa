<?php

/**
 * @package MPesa For WooCommerce
 * @subpackage C2B Metaboxes
 * @author Mauko Maunde < hi@mauko.co.ke >
 * @version 2.0.0
 * @since 0.18.01
 */

namespace Osen\Woocommerce\Post\Metaboxes;

class C2B
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'mpesa_mb_sm']);
    }

    public function mpesa_mb_sm()
    {
        add_meta_box('wc_mpesa_mb_payment_status', 'Incase MPesa timed out', [$this, 'mpesa_status'], ['shop_order'], 'side', 'low');
        add_meta_box('wc_mpesa_mb_payment_create', 'Paid For Via MPesa?', [$this, 'mpesa_payment'], 'shop_order', 'side', 'low');
    }

    public function mpesa_payment($post)
    {
        $order   = new \WC_Order($post);
        $receipt = $order->get_transaction_id();

        echo '<table class="form-table" >
        <tr valign="top" >
            <th scope="row" >
                ' . ($receipt ? "" : "Enter ") . 'M-Pesa Transaction ID
            </th>
        </tr>
        <tr valign="top" >
            <td>
                <input class="input-text" type="text" name="receipt" value="' . esc_attr($receipt) . ' " class="regular-text" / >
            </td>
        </tr>
        </table>';
    }

    public function mpesa_status($post)
    {
        $order    = new \WC_Order($post);
        $request  = \get_post_meta($order->get_ID(), 'mpesa_request_id', true);
        $status   = $order->get_status();
        $statuses = wc_get_order_statuses();

        echo '<table class="form-table" >
            <tr valign="top" >
                <td>
                    <small id="mpesaipn_status_result">This order is ' . $statuses["wc-$status"] . '</small>
                </td>
            </tr>
            <tr valign="top" >
                <td>
                    ' . (($status === 'completed')
            ? '<button id="mpesaipn_status" name="mpesaipn_status" class="button button-large">Check Payment Status</button>
                    <script>
                        jQuery(document).ready(function($){
                            $("#mpesaipn_status").click(function(e){
                                e.preventDefault();
                                $.post("' . admin_url("admin-ajax.php") . '", {request: ' . $request . '}, function(data){
                                    $("#mpesaipn_status_result").html(data);
                                });
                            });
                        });
                    </script>'
            : '<button id="mpesaipn_reinitiate" name="mpesaipn_reinitiate" class="button button-large">Reinitiate Prompt</button>
                    <script>
                        jQuery(document).ready(function($){
                            $("#mpesaipn_reinitiate").click(function(e){
                                e.preventDefault();
                                $.post("' . home_url("wc-api/lipwa?action=request") . '", {order: ' . $order->get_ID() . '}, function(data){
                                if(data.errorCode){
                                    $("#mpesaipn_status_result").html("("+data.errorCode+") "+data.errorMessage);
                                } else{
                                    $("#mpesaipn_status_result").html("STK Resent. Confirming payment <span>.</span><span>.</span><span>.</span><span>.</span><span>.</span><span>.</span>");
                                }
                            });
                            });
                        });
                    </script>') . '
                </td>
            </tr>
        </table>';
    }
}
