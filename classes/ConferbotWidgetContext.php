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
     *
     * @return array widget options
     */
    public static function build(array $store, $moduleVersion, $customer = null, $cart = null)
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
     * JSON safe to inline inside a <script> block: JSON_HEX_* neutralize
     * user-controlled values (customer name/email) in the HTML context.
     *
     * @param array $options
     *
     * @return string
     */
    public static function toJson(array $options)
    {
        return json_encode($options, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }
}
