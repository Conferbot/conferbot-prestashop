<?php
/**
 * Upgrade to 1.3.0: removes the settings 1.0.x wrote that later versions no
 * longer use (styling now comes from the Conferbot dashboard) and makes sure
 * the storefront hook is registered, so an in-place upgrade keeps the chat.
 *
 * @author    Conferbot <support@conferbot.com>
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Conferbot $module
 *
 * @return bool
 */
function upgrade_module_1_3_0($module)
{
    foreach (ConferbotSettings::$legacyKeys as $key) {
        Configuration::deleteByName($key);
    }

    return $module->isRegisteredInHook('displayHeader') || $module->registerHook('displayHeader');
}
