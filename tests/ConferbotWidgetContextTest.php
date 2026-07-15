<?php

use PHPUnit\Framework\TestCase;

class ConferbotWidgetContextTest extends TestCase
{
    private $store = array(
        'name' => 'My Shop',
        'url' => 'https://shop.example.com/',
        'language' => 'en',
        'currency' => 'USD',
    );

    // ---- build(): store context ---------------------------------------

    public function testGuestVisitorGetsStoreContextOnly()
    {
        $options = ConferbotWidgetContext::build($this->store, '1.2.0');

        $this->assertArrayNotHasKey('user', $options);
        $this->assertSame('prestashop', $options['customData']['platform']);
        $this->assertSame('1.2.0', $options['customData']['moduleVersion']);
        $this->assertSame('My Shop', $options['customData']['storeName']);
        $this->assertSame('https://shop.example.com/', $options['customData']['storeUrl']);
        $this->assertSame('en', $options['customData']['storeLanguage']);
        $this->assertSame('USD', $options['customData']['storeCurrency']);
    }

    public function testMissingStoreFieldsBecomeEmptyStrings()
    {
        $options = ConferbotWidgetContext::build(array(), '1.2.0');

        $this->assertSame('', $options['customData']['storeName']);
        $this->assertSame('', $options['customData']['storeUrl']);
        $this->assertSame('', $options['customData']['storeLanguage']);
        $this->assertSame('', $options['customData']['storeCurrency']);
    }

    // ---- build(): customer identity ------------------------------------

    public function testLoggedInCustomerBecomesOptionsUser()
    {
        $options = ConferbotWidgetContext::build($this->store, '1.2.0', array(
            'id' => 2,
            'firstname' => 'John',
            'lastname' => 'DOE',
            'email' => 'pub@prestashop.com',
        ));

        $this->assertSame(array(
            'id' => 'ps-2',
            'name' => 'John DOE',
            'email' => 'pub@prestashop.com',
        ), $options['user']);
    }

    public function testCustomerIdIsPrefixedAndCast()
    {
        $options = ConferbotWidgetContext::build($this->store, '1.2.0', array('id' => '17'));

        $this->assertSame('ps-17', $options['user']['id']);
    }

    public function testCustomerWithoutIdIsTreatedAsGuest()
    {
        $options = ConferbotWidgetContext::build($this->store, '1.2.0', array(
            'firstname' => 'John', 'lastname' => 'DOE', 'email' => 'x@y.z',
        ));

        $this->assertArrayNotHasKey('user', $options);
    }

    public function testCustomerNameIsTrimmedWhenPartsMissing()
    {
        $onlyFirst = ConferbotWidgetContext::build($this->store, '1.2.0', array('id' => 1, 'firstname' => 'John'));
        $this->assertSame('John', $onlyFirst['user']['name']);

        $onlyLast = ConferbotWidgetContext::build($this->store, '1.2.0', array('id' => 1, 'lastname' => 'DOE'));
        $this->assertSame('DOE', $onlyLast['user']['name']);

        $neither = ConferbotWidgetContext::build($this->store, '1.2.0', array('id' => 1));
        $this->assertSame('', $neither['user']['name']);
        $this->assertSame('', $neither['user']['email']);
    }

    // ---- build(): cart context ------------------------------------------

    public function testCartContextIsIncludedWhenPresent()
    {
        $options = ConferbotWidgetContext::build($this->store, '1.2.0', null, array(
            'total' => 11.9,
            'itemCount' => 3,
        ));

        $this->assertSame(11.9, $options['customData']['cartTotal']);
        $this->assertSame(3, $options['customData']['cartItemCount']);
    }

    public function testCartValuesAreCast()
    {
        $options = ConferbotWidgetContext::build($this->store, '1.2.0', null, array(
            'total' => '11.90',
            'itemCount' => '3',
        ));

        $this->assertSame(11.9, $options['customData']['cartTotal']);
        $this->assertSame(3, $options['customData']['cartItemCount']);
    }

    public function testNoCartMeansNoCartKeys()
    {
        $options = ConferbotWidgetContext::build($this->store, '1.2.0');

        $this->assertArrayNotHasKey('cartTotal', $options['customData']);
        $this->assertArrayNotHasKey('cartItemCount', $options['customData']);
    }

    public function testEmptyCartArrayAddsNoKeys()
    {
        $options = ConferbotWidgetContext::build($this->store, '1.2.0', null, array());

        $this->assertArrayNotHasKey('cartTotal', $options['customData']);
        $this->assertArrayNotHasKey('cartItemCount', $options['customData']);
    }

    // ---- toJson(): script-context safety --------------------------------

    public function testJsonIsValidAndRoundTrips()
    {
        $options = ConferbotWidgetContext::build($this->store, '1.2.0', array(
            'id' => 2, 'firstname' => 'John', 'lastname' => 'DOE', 'email' => 'pub@prestashop.com',
        ), array('total' => 11.9, 'itemCount' => 1));

        $json = ConferbotWidgetContext::toJson($options);
        $decoded = json_decode($json, true);

        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('ps-2', $decoded['user']['id']);
        $this->assertSame(11.9, $decoded['customData']['cartTotal']);
    }

    public function testJsonNeutralizesScriptBreakingCharacters()
    {
        // A malicious customer name must not be able to break out of the
        // <script> block or the JS object literal.
        $options = ConferbotWidgetContext::build($this->store, '1.2.0', array(
            'id' => 666,
            'firstname' => '</script><script>alert(1)',
            'lastname' => '"; window.evil = true; var x = "',
            'email' => "o'brien@example.com",
        ));

        $json = ConferbotWidgetContext::toJson($options);

        $this->assertStringNotContainsString('</script>', $json);
        $this->assertStringNotContainsString('<', $json);
        $this->assertStringNotContainsString('>', $json);
        $this->assertStringNotContainsString('"; window', $json);
        $this->assertStringNotContainsString("'", $json);

        // ...while still decoding back to the original values.
        $decoded = json_decode($json, true);
        $this->assertStringContainsString('alert(1)', $decoded['user']['name']);
        $this->assertSame("o'brien@example.com", $decoded['user']['email']);
    }

    public function testJsonHandlesUnicodeStoreName()
    {
        $store = $this->store;
        $store['name'] = 'Boutique Chloé 中文 🛍️';

        $json = ConferbotWidgetContext::toJson(ConferbotWidgetContext::build($store, '1.2.0'));
        $decoded = json_decode($json, true);

        $this->assertSame('Boutique Chloé 中文 🛍️', $decoded['customData']['storeName']);
    }
}
