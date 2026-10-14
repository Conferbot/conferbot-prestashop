<?php
/**
 * Builds the third argument for window.ConferbotWidget(botId, embedType, options).
 *
 * options.user carries the logged-in customer identity; options.customData
 * carries store and cart context. Both become the visitor's context in
 * Conferbot conversations. Pure data-in/data-out so it is fully unit-testable.
 *
 * @author    Conferbot <support@conferbot.com>
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_') && !defined('CONFERBOT_TEST_SUITE')) {
    exit;
}

class ConferbotWidgetContext
{
    /**
     * @param array $store keys: name, url, language, currency
     * @param string $moduleVersion
     * @param array|null $customer keys: id, firstname, lastname, email (null = guest)
     * @param array|null $cart keys: total, itemCount (null = no loaded cart)
     * @param string $secret the workspace's identity verification secret ('' = off)
     *
     * @return array widget options
     */
    public static function build(array $store, $moduleVersion, $customer = null, $cart = null, $secret = '')
    {
        $options = array('customData' => array(
            'platform' => 'prestashop',
            'moduleVersion' => (string) $moduleVersion,
            'storeName' => isset($store['name']) ? (string) $store['name'] : '',
            'storeUrl' => isset($store['url']) ? (string) $store['url'] : '',
            'storeLanguage' => isset($store['language']) ? (string) $store['language'] : '',
            'storeCurrency' => isset($store['currency']) ? (string) $store['currency'] : '',
        ));

        if (is_array($customer) && !empty($customer['id'])) {
            $name = trim(
                (isset($customer['firstname']) ? $customer['firstname'] : '')
                . ' '
                . (isset($customer['lastname']) ? $customer['lastname'] : '')
            );
            $options['user'] = array(
                'id' => 'ps-' . (int) $customer['id'],
                'name' => $name,
                'email' => isset($customer['email']) ? (string) $customer['email'] : '',
            );
            // Identity verification: the id signed with the workspace secret,
            // so Conferbot knows this is the store's own logged-in customer.
            if ((string) $secret !== '') {
                $options['user']['hash'] = self::userHash($options['user']['id'], $secret);
            }
        }

        if (is_array($cart)) {
            if (isset($cart['total'])) {
                $options['customData']['cartTotal'] = (float) $cart['total'];
            }
            if (isset($cart['itemCount'])) {
                $options['customData']['cartItemCount'] = (int) $cart['itemCount'];
            }
        }

        return $options;
    }

    /**
     * HMAC-SHA256 of the signed value (the customer id) with the secret, hex - as Conferbot checks it.
     *
     * @param string $value
     * @param string $secret
     *
     * @return string
     */
    public static function userHash($value, $secret)
    {
        return hash_hmac('sha256', (string) $value, (string) $secret);
    }

    /**
     * The order on the confirmation page as a purchase, reported once per order.
     *
     * @param array|null $order keys: id, reference, total, currency (null = not that page)
     *
     * @return array events for the template: name, props, once
     */
    public static function events($order)
    {
        if (!is_array($order) || empty($order['id'])) {
            return array();
        }

        return array(array(
            'name' => 'purchase',
            'props' => array(
                'value' => (float) $order['total'],
                'currency' => (string) $order['currency'],
                'order_id' => (string) (isset($order['reference']) && $order['reference'] !== '' ? $order['reference'] : $order['id']),
            ),
            'once' => 'order-' . (int) $order['id'],
        ));
    }

    /**
     * JSON safe to inline inside a <script> block: JSON_HEX_* neutralize
     * user-controlled values (customer name/email) in the HTML context.
     *
     * @param array $options
     *
     * @return string
     */
    public static function toJson($options)
    {
        return json_encode($options, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }
}
