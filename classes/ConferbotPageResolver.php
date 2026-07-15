<?php
/**
 * Maps PrestaShop front controllers to the module's page types and decides
 * widget visibility from the merchant's "Show on Pages" settings.
 *
 * @author    Conferbot <support@conferbot.com>
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_') && !defined('CONFERBOT_TEST_SUITE')) {
    exit;
}

class ConferbotPageResolver
{
    /** Page-type ids used as CONFERBOT_SHOW_ON_PAGES_* suffixes and form checkbox ids. */
    const PAGE_TYPES = array('home', 'category', 'product', 'cart', 'checkout', 'account', 'other');

    /** @var array front controller php_self => page type */
    private static $controllerMap = array(
        'index' => 'home',
        'category' => 'category',
        'search' => 'category',
        'manufacturer' => 'category',
        'supplier' => 'category',
        'best-sales' => 'category',
        'new-products' => 'category',
        'prices-drop' => 'category',
        'product' => 'product',
        'cart' => 'cart',
        'order' => 'checkout',
        'order-confirmation' => 'checkout',
        'my-account' => 'account',
        'identity' => 'account',
        'history' => 'account',
        'order-detail' => 'account',
        'addresses' => 'account',
        'address' => 'account',
        'discount' => 'account',
        'order-follow' => 'account',
        'credit-slip' => 'account',
        'authentication' => 'account',
    );

    /**
     * @param string $phpSelf front controller name (Controller::$php_self)
     *
     * @return string one of self::PAGE_TYPES
     */
    public static function resolve($phpSelf)
    {
        $phpSelf = (string) $phpSelf;

        return isset(self::$controllerMap[$phpSelf]) ? self::$controllerMap[$phpSelf] : 'other';
    }

    /**
     * @param string $pageType one of self::PAGE_TYPES
     * @param array $settings pageType => stored value; missing/false (unsaved) means visible
     *
     * @return bool
     */
    public static function isVisible($pageType, array $settings)
    {
        if (!array_key_exists($pageType, $settings) || $settings[$pageType] === false) {
            return true;
        }

        return (bool) (int) $settings[$pageType];
    }
}
