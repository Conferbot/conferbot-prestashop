<?php
/**
 * Bot ID parsing and validation.
 *
 * A Conferbot bot ID is the chatbot's MongoDB ObjectId: 24 hex characters.
 * Merchants paste anything from a bare ID to the full embed snippet, so
 * extraction is deliberately forgiving while validation stays strict.
 *
 * @author    Conferbot <support@conferbot.com>
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_') && !defined('CONFERBOT_TEST_SUITE')) {
    exit;
}

class ConferbotBotId
{
    /** Public demo bot - works out of the box so merchants can try the widget instantly. */
    const DEMO_BOT_ID = '691c970890527a0468f9b2c9';

    /**
     * Pulls the first 24-hex token out of whatever was pasted
     * (bare ID, full embed snippet, dashboard URL, extra whitespace).
     *
     * @param string $text
     *
     * @return string extracted ID, or '' when none found
     */
    public static function extract($text)
    {
        if (preg_match('/[a-f0-9]{24}/i', (string) $text, $m)) {
            return $m[0];
        }

        return '';
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public static function isValid($id)
    {
        return (bool) preg_match('/^[a-f0-9]{24}$/i', (string) $id);
    }
}
