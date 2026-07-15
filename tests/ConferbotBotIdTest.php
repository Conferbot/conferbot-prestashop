<?php

use PHPUnit\Framework\TestCase;

class ConferbotBotIdTest extends TestCase
{
    const ID = '691c970890527a0468f9b2c9';

    public function testExtractsBareId()
    {
        $this->assertSame(self::ID, ConferbotBotId::extract(self::ID));
    }

    public function testExtractsUppercaseId()
    {
        $this->assertSame(strtoupper(self::ID), ConferbotBotId::extract(strtoupper(self::ID)));
    }

    public function testExtractsIdWithSurroundingWhitespace()
    {
        $this->assertSame(self::ID, ConferbotBotId::extract("  \t" . self::ID . " \n"));
    }

    public function testExtractsIdFromFullEmbedSnippet()
    {
        $snippet = 'js.onload = function () { var w = window.ConferbotWidget("' . self::ID . '", "live_chat"); };';
        $this->assertSame(self::ID, ConferbotBotId::extract($snippet));
    }

    public function testExtractsIdFromDashboardUrl()
    {
        $url = 'https://app.conferbot.com/a/ws/123/u/456/chatbot/' . self::ID . '/embed';
        // The workspace/user segments are numeric and shorter than 24 chars,
        // so the first 24-hex run is the bot ID.
        $this->assertSame(self::ID, ConferbotBotId::extract($url));
    }

    public function testExtractReturnsEmptyStringForGarbage()
    {
        $this->assertSame('', ConferbotBotId::extract('hello grandma'));
    }

    public function testExtractReturnsEmptyStringForEmptyInput()
    {
        $this->assertSame('', ConferbotBotId::extract(''));
    }

    public function testExtractReturnsEmptyStringForTooShortHex()
    {
        $this->assertSame('', ConferbotBotId::extract('abcdef0123456789abcdef')); // 22 chars
    }

    public function testExtractFindsIdInsideLongerHexRun()
    {
        // 26 hex chars: extraction takes the first 24. Strict validation still
        // guards the final stored value in processConfiguration.
        $this->assertSame(substr(self::ID . 'ab', 0, 24), ConferbotBotId::extract(self::ID . 'ab'));
    }

    public function testExtractHandlesNonStringInput()
    {
        $this->assertSame('', ConferbotBotId::extract(null));
        $this->assertSame('', ConferbotBotId::extract(false));
    }

    public function testIsValidAcceptsCanonicalId()
    {
        $this->assertTrue(ConferbotBotId::isValid(self::ID));
    }

    public function testIsValidAcceptsMixedCase()
    {
        $this->assertTrue(ConferbotBotId::isValid('691C970890527A0468F9B2C9'));
    }

    public function testIsValidRejectsWrongLength()
    {
        $this->assertFalse(ConferbotBotId::isValid(substr(self::ID, 0, 23)));
        $this->assertFalse(ConferbotBotId::isValid(self::ID . '0'));
    }

    public function testIsValidRejectsNonHex()
    {
        $this->assertFalse(ConferbotBotId::isValid('691c970890527a0468f9b2cz'));
    }

    public function testIsValidRejectsEmptyAndNull()
    {
        $this->assertFalse(ConferbotBotId::isValid(''));
        $this->assertFalse(ConferbotBotId::isValid(null));
    }

    public function testIsValidRejectsIdWithSurroundingWhitespace()
    {
        $this->assertFalse(ConferbotBotId::isValid(' ' . self::ID));
    }

    public function testDemoBotIdIsValid()
    {
        $this->assertTrue(ConferbotBotId::isValid(ConferbotBotId::DEMO_BOT_ID));
        $this->assertSame('691c970890527a0468f9b2c9', ConferbotBotId::DEMO_BOT_ID);
    }
}
