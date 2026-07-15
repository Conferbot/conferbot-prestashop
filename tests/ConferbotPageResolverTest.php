<?php

use PHPUnit\Framework\TestCase;

class ConferbotPageResolverTest extends TestCase
{
    // ---- resolve() -----------------------------------------------------

    public function controllerMappingProvider()
    {
        return array(
            'homepage' => array('index', 'home'),
            'category' => array('category', 'category'),
            'search results' => array('search', 'category'),
            'manufacturer listing' => array('manufacturer', 'category'),
            'supplier listing' => array('supplier', 'category'),
            'best sales' => array('best-sales', 'category'),
            'new products' => array('new-products', 'category'),
            'price drops' => array('prices-drop', 'category'),
            'product page' => array('product', 'product'),
            'cart' => array('cart', 'cart'),
            'checkout' => array('order', 'checkout'),
            'order confirmation' => array('order-confirmation', 'checkout'),
            'account home' => array('my-account', 'account'),
            'identity' => array('identity', 'account'),
            'order history' => array('history', 'account'),
            'order detail' => array('order-detail', 'account'),
            'addresses' => array('addresses', 'account'),
            'single address' => array('address', 'account'),
            'vouchers' => array('discount', 'account'),
            'order follow' => array('order-follow', 'account'),
            'credit slips' => array('credit-slip', 'account'),
            'login page' => array('authentication', 'account'),
        );
    }

    /** @dataProvider controllerMappingProvider */
    public function testResolvesKnownControllers($phpSelf, $expected)
    {
        $this->assertSame($expected, ConferbotPageResolver::resolve($phpSelf));
    }

    public function testUnknownControllerFallsBackToOther()
    {
        $this->assertSame('other', ConferbotPageResolver::resolve('cms'));
        $this->assertSame('other', ConferbotPageResolver::resolve('contact'));
        $this->assertSame('other', ConferbotPageResolver::resolve('sitemap'));
        $this->assertSame('other', ConferbotPageResolver::resolve('pagenotfound'));
    }

    public function testEmptyAndNullControllerFallBackToOther()
    {
        $this->assertSame('other', ConferbotPageResolver::resolve(''));
        $this->assertSame('other', ConferbotPageResolver::resolve(null));
    }

    public function testResolveIsCaseSensitiveLikePrestashop()
    {
        // php_self is always lowercase in PrestaShop; anything else is unknown.
        $this->assertSame('other', ConferbotPageResolver::resolve('Product'));
    }

    public function testEveryMappedTargetIsADeclaredPageType()
    {
        foreach ($this->controllerMappingProvider() as $case) {
            $this->assertContains($case[1], ConferbotPageResolver::PAGE_TYPES);
        }
    }

    // ---- isVisible() ---------------------------------------------------

    public function testVisibleByDefaultWhenNeverSaved()
    {
        // Configuration::get returns false for missing keys.
        $this->assertTrue(ConferbotPageResolver::isVisible('home', array('home' => false)));
    }

    public function testVisibleByDefaultWhenKeyAbsent()
    {
        $this->assertTrue(ConferbotPageResolver::isVisible('home', array()));
    }

    public function testHiddenWhenSavedOff()
    {
        $this->assertFalse(ConferbotPageResolver::isVisible('checkout', array('checkout' => '0')));
        $this->assertFalse(ConferbotPageResolver::isVisible('checkout', array('checkout' => 0)));
    }

    public function testVisibleWhenSavedOn()
    {
        $this->assertTrue(ConferbotPageResolver::isVisible('home', array('home' => '1')));
        $this->assertTrue(ConferbotPageResolver::isVisible('home', array('home' => 1)));
    }

    public function testSettingsForOtherPagesDoNotLeak()
    {
        $settings = array('checkout' => 0, 'home' => 1);
        $this->assertTrue(ConferbotPageResolver::isVisible('product', $settings));
        $this->assertFalse(ConferbotPageResolver::isVisible('checkout', $settings));
        $this->assertTrue(ConferbotPageResolver::isVisible('home', $settings));
    }

    public function testPageTypesListIsStable()
    {
        $this->assertSame(
            array('home', 'category', 'product', 'cart', 'checkout', 'account', 'other'),
            ConferbotPageResolver::PAGE_TYPES
        );
    }
}
