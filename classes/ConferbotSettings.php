<?php
/**
 * Single home for the module's configuration keys, defaults, and persistence.
 * All Configuration reads/writes go through here so adding a setting is a
 * one-file change (plus the form template).
 *
 * @author    Conferbot <support@conferbot.com>
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_') && !defined('CONFERBOT_TEST_SUITE')) {
    exit;
}

class ConferbotSettings
{
    const DEFAULT_SCRIPT_URL = 'https://cdn.conferbot.com/dist/v1/widget.min.js';

    const KEY_ENABLED = 'CONFERBOT_ENABLED';
    const KEY_CHATBOT_ID = 'CONFERBOT_CHATBOT_ID';
    const KEY_EMBED_TYPE = 'CONFERBOT_EMBED_TYPE';
    const KEY_SCRIPT_URL = 'CONFERBOT_SCRIPT_URL';
    const KEY_PAGE_PREFIX = 'CONFERBOT_SHOW_ON_PAGES_';
    const KEY_IDENTITY_SECRET = 'CONFERBOT_IDENTITY_SECRET';

    /** @var array embed types the widget loader accepts from this module */
    public static $embedTypes = array('live_chat', 'popup_chat');

    /** @var array keys removed on uninstall that newer versions no longer write (1.0.x) */
    public static $legacyKeys = array(
        'CONFERBOT_API_KEY',
        'CONFERBOT_WORKSPACE_ID',
        'CONFERBOT_WIDGET_POSITION',
        'CONFERBOT_WIDGET_THEME',
        'CONFERBOT_WIDGET_LANGUAGE',
        'CONFERBOT_SHOW_ON_PAGES',
        'CONFERBOT_WEBHOOK_SECRET',
    );

    public static function isEnabled()
    {
        return (bool) Configuration::get(self::KEY_ENABLED);
    }

    public static function getChatbotId()
    {
        return Configuration::get(self::KEY_CHATBOT_ID) ?: '';
    }

    public static function getEmbedType()
    {
        $type = Configuration::get(self::KEY_EMBED_TYPE);

        return in_array($type, self::$embedTypes, true) ? $type : 'live_chat';
    }

    public static function getScriptUrl()
    {
        return Configuration::get(self::KEY_SCRIPT_URL) ?: self::DEFAULT_SCRIPT_URL;
    }

    /** The workspace's identity verification secret ('' = off). Never sent to the browser. */
    public static function getIdentitySecret()
    {
        return (string) (Configuration::get(self::KEY_IDENTITY_SECRET) ?: '');
    }

    /**
     * Write-only: a blank field keeps the saved secret, "remove" clears it.
     *
     * @param string $current
     * @param string $submitted
     * @param bool $clear
     *
     * @return string
     */
    public static function nextIdentitySecret($current, $submitted, $clear)
    {
        if ($clear) {
            return '';
        }
        $submitted = trim((string) $submitted);

        return $submitted !== '' ? $submitted : (string) $current;
    }

    /**
     * @return array pageType => stored raw value (false when never saved)
     */
    public static function getPageVisibility()
    {
        $settings = array();
        foreach (ConferbotPageResolver::PAGE_TYPES as $page) {
            $settings[$page] = Configuration::get(self::KEY_PAGE_PREFIX . $page);
        }

        return $settings;
    }

    /**
     * @param array $values keys: enabled(int), chatbotId, embedType, scriptUrl, pages(pageType=>int)
     */
    public static function save(array $values)
    {
        Configuration::updateValue(self::KEY_ENABLED, (int) $values['enabled']);
        Configuration::updateValue(self::KEY_CHATBOT_ID, $values['chatbotId']);
        Configuration::updateValue(self::KEY_EMBED_TYPE, $values['embedType']);
        Configuration::updateValue(self::KEY_SCRIPT_URL, $values['scriptUrl']);
        if (array_key_exists('identitySecret', $values)) {
            Configuration::updateValue(self::KEY_IDENTITY_SECRET, (string) $values['identitySecret']);
        }
        foreach ($values['pages'] as $page => $visible) {
            Configuration::updateValue(self::KEY_PAGE_PREFIX . $page, (int) $visible);
        }
    }

    public static function deleteAll()
    {
        $keys = array_merge(
            array(self::KEY_ENABLED, self::KEY_CHATBOT_ID, self::KEY_EMBED_TYPE, self::KEY_SCRIPT_URL, self::KEY_IDENTITY_SECRET),
            self::$legacyKeys
        );
        foreach (ConferbotPageResolver::PAGE_TYPES as $page) {
            $keys[] = self::KEY_PAGE_PREFIX . $page;
        }
        foreach ($keys as $key) {
            Configuration::deleteByName($key);
        }
    }
}
