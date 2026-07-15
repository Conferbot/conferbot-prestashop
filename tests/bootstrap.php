<?php
/**
 * Test bootstrap: loads the module classes without a PrestaShop runtime.
 * CONFERBOT_TEST_SUITE lets the class guards pass, and a minimal
 * Configuration stub backs ConferbotSettings.
 */

define('CONFERBOT_TEST_SUITE', true);

/** In-memory stand-in for PrestaShop's Configuration registry. */
class Configuration
{
    public static $store = array();

    public static function reset()
    {
        self::$store = array();
    }

    public static function get($key)
    {
        return array_key_exists($key, self::$store) ? self::$store[$key] : false;
    }

    public static function updateValue($key, $value)
    {
        self::$store[$key] = $value;

        return true;
    }

    public static function deleteByName($key)
    {
        unset(self::$store[$key]);

        return true;
    }
}

require_once __DIR__ . '/../classes/ConferbotBotId.php';
require_once __DIR__ . '/../classes/ConferbotPageResolver.php';
require_once __DIR__ . '/../classes/ConferbotWidgetContext.php';
require_once __DIR__ . '/../classes/ConferbotSettings.php';
