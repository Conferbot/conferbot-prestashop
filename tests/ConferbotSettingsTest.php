<?php

use PHPUnit\Framework\TestCase;

class ConferbotSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        Configuration::reset();
    }

    // ---- defaults --------------------------------------------------------

    public function testDisabledByDefault()
    {
        $this->assertFalse(ConferbotSettings::isEnabled());
    }

    public function testChatbotIdEmptyByDefault()
    {
        $this->assertSame('', ConferbotSettings::getChatbotId());
    }

    public function testEmbedTypeDefaultsToLiveChat()
    {
        $this->assertSame('live_chat', ConferbotSettings::getEmbedType());
    }

    public function testScriptUrlDefaultsToCdn()
    {
        $this->assertSame('https://cdn.conferbot.com/dist/v1/widget.min.js', ConferbotSettings::getScriptUrl());
    }

    public function testPageVisibilityDefaultsAreAllUnsaved()
    {
        $visibility = ConferbotSettings::getPageVisibility();

        $this->assertSame(ConferbotPageResolver::PAGE_TYPES, array_keys($visibility));
        foreach ($visibility as $page => $value) {
            $this->assertFalse($value, "page '$page' should be unsaved by default");
            $this->assertTrue(ConferbotPageResolver::isVisible($page, $visibility));
        }
    }

    // ---- save() / read-back ----------------------------------------------

    public function testSavePersistsAllValues()
    {
        ConferbotSettings::save(array(
            'enabled' => 1,
            'chatbotId' => ConferbotBotId::DEMO_BOT_ID,
            'embedType' => 'popup_chat',
            'scriptUrl' => 'https://staging.example.com/widget.js',
            'pages' => array('home' => 1, 'checkout' => 0),
        ));

        $this->assertTrue(ConferbotSettings::isEnabled());
        $this->assertSame(ConferbotBotId::DEMO_BOT_ID, ConferbotSettings::getChatbotId());
        $this->assertSame('popup_chat', ConferbotSettings::getEmbedType());
        $this->assertSame('https://staging.example.com/widget.js', ConferbotSettings::getScriptUrl());

        $visibility = ConferbotSettings::getPageVisibility();
        $this->assertTrue(ConferbotPageResolver::isVisible('home', $visibility));
        $this->assertFalse(ConferbotPageResolver::isVisible('checkout', $visibility));
    }

    public function testInvalidStoredEmbedTypeFallsBackToLiveChat()
    {
        Configuration::updateValue(ConferbotSettings::KEY_EMBED_TYPE, 'fullpage_chat');

        // fullpage_chat is a valid platform embed type but not one this module
        // offers; the getter must not propagate unexpected stored values.
        $this->assertSame('live_chat', ConferbotSettings::getEmbedType());
    }

    public function testEmptyStoredScriptUrlFallsBackToCdn()
    {
        Configuration::updateValue(ConferbotSettings::KEY_SCRIPT_URL, '');

        $this->assertSame(ConferbotSettings::DEFAULT_SCRIPT_URL, ConferbotSettings::getScriptUrl());
    }

    // ---- deleteAll() -------------------------------------------------------

    public function testDeleteAllRemovesCurrentKeys()
    {
        ConferbotSettings::save(array(
            'enabled' => 1,
            'chatbotId' => ConferbotBotId::DEMO_BOT_ID,
            'embedType' => 'live_chat',
            'scriptUrl' => ConferbotSettings::DEFAULT_SCRIPT_URL,
            'pages' => array('home' => 1),
        ));

        ConferbotSettings::deleteAll();

        $this->assertSame(array(), Configuration::$store);
    }

    public function testDeleteAllRemovesLegacyKeys()
    {
        foreach (ConferbotSettings::$legacyKeys as $key) {
            Configuration::updateValue($key, 'legacy-value');
        }

        ConferbotSettings::deleteAll();

        foreach (ConferbotSettings::$legacyKeys as $key) {
            $this->assertFalse(Configuration::get($key), "legacy key '$key' should be deleted");
        }
    }

    public function testLegacyKeyListCoversAllV10Keys()
    {
        $this->assertSame(array(
            'CONFERBOT_API_KEY',
            'CONFERBOT_WORKSPACE_ID',
            'CONFERBOT_WIDGET_POSITION',
            'CONFERBOT_WIDGET_THEME',
            'CONFERBOT_WIDGET_LANGUAGE',
            'CONFERBOT_SHOW_ON_PAGES',
            'CONFERBOT_WEBHOOK_SECRET',
        ), ConferbotSettings::$legacyKeys);
    }
}
