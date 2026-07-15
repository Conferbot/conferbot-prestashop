<?php
/**
 * Conferbot Live Chat — PrestaShop module
 *
 * Embeds the Conferbot AI chat widget on a PrestaShop 1.7/8.x storefront.
 * The widget is identified by the Chatbot ID only (the bot's ID shown in the
 * Conferbot dashboard under Share > Embed into website). All appearance and
 * behavior settings are managed in the Conferbot dashboard and delivered to
 * the widget at runtime; this module only injects the loader snippet and
 * passes the logged-in customer identity and store context to the widget.
 *
 * @author    Conferbot <support@conferbot.com>
 * @copyright 2023-2026 Conferbot
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Conferbot extends Module
{
    const DEFAULT_SCRIPT_URL = 'https://cdn.conferbot.com/dist/v1/widget.min.js';

    /** Page-visibility checkbox ids (suffixes of CONFERBOT_SHOW_ON_PAGES_*) */
    private static $pageTypes = array('home', 'category', 'product', 'cart', 'checkout', 'account', 'other');

    public function __construct()
    {
        $this->name = 'conferbot';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'Conferbot';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Conferbot — AI Chatbot, Live Chat & Customer Support');
        $this->description = $this->l('Turn visitors into customers with an AI chatbot and live chat widget. Automate 24/7 customer support, capture leads, answer product questions, and hand over to human agents — set up in under a minute with no code.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall Conferbot? Module configuration will be removed.');

        if (!Configuration::get('CONFERBOT_CHATBOT_ID')) {
            $this->warning = $this->l('No Chatbot ID configured. Open the module configuration and paste your bot ID from the Conferbot dashboard.');
        }
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHeader');
    }

    public function uninstall()
    {
        $keys = array(
            'CONFERBOT_ENABLED',
            'CONFERBOT_CHATBOT_ID',
            'CONFERBOT_EMBED_TYPE',
            'CONFERBOT_SCRIPT_URL',
            // legacy keys from 1.0.x
            'CONFERBOT_API_KEY',
            'CONFERBOT_WORKSPACE_ID',
            'CONFERBOT_WIDGET_POSITION',
            'CONFERBOT_WIDGET_THEME',
            'CONFERBOT_WIDGET_LANGUAGE',
            'CONFERBOT_SHOW_ON_PAGES',
            'CONFERBOT_WEBHOOK_SECRET',
        );
        foreach (self::$pageTypes as $page) {
            $keys[] = 'CONFERBOT_SHOW_ON_PAGES_' . $page;
        }
        foreach ($keys as $key) {
            Configuration::deleteByName($key);
        }

        return parent::uninstall();
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $output .= $this->processConfiguration();
        }

        $pageLabels = array(
            'home' => $this->l('Home page'),
            'category' => $this->l('Categories & search'),
            'product' => $this->l('Product pages'),
            'cart' => $this->l('Shopping cart'),
            'checkout' => $this->l('Checkout'),
            'account' => $this->l('Customer account'),
            'other' => $this->l('All other pages'),
        );
        $pages = array();
        foreach (self::$pageTypes as $page) {
            $stored = Configuration::get('CONFERBOT_SHOW_ON_PAGES_' . $page);
            $pages[] = array(
                'id' => $page,
                'label' => $pageLabels[$page],
                'checked' => ($stored === false) ? true : (bool) (int) $stored,
            );
        }

        $this->context->smarty->assign(array(
            'cb_form_action' => $this->context->link->getAdminLink('AdminModules', true)
                . '&configure=' . $this->name,
            'cb_enabled' => (bool) Configuration::get('CONFERBOT_ENABLED'),
            'cb_chatbot_id' => Configuration::get('CONFERBOT_CHATBOT_ID') ?: '',
            'cb_embed_type' => Configuration::get('CONFERBOT_EMBED_TYPE') ?: 'live_chat',
            'cb_script_url' => Configuration::get('CONFERBOT_SCRIPT_URL') ?: self::DEFAULT_SCRIPT_URL,
            'cb_pages' => $pages,
            'cb_shop_url' => $this->context->shop->getBaseURL(true),
            'cb_logo' => $this->getPathUri() . 'logo.png',
        ));

        return $output . $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    private function processConfiguration()
    {
        // Accept a bare 24-hex bot ID, or extract it from whatever was pasted
        // (full embed snippet, dashboard URL, surrounding whitespace...).
        $pasted = (string) Tools::getValue('CONFERBOT_CHATBOT_ID');
        $chatbotId = '';
        if (preg_match('/[a-f0-9]{24}/i', $pasted, $m)) {
            $chatbotId = $m[0];
        }

        $enabled = (int) (bool) Tools::getValue('CONFERBOT_ENABLED');

        if ($chatbotId === '' && trim($pasted) !== '') {
            return $this->displayError($this->l('We could not find a Chatbot ID in what you pasted. It looks like a 24-character code, e.g. 64a1b2c3d4e5f6a7b8c9d0e1. In your Conferbot dashboard, open your bot, click "Share", then "Copy Bot ID".'));
        }
        if ($chatbotId === '' && $enabled) {
            return $this->displayError($this->l('Please paste your Chatbot ID first (step 1), then turn the chat on.'));
        }

        $scriptUrl = trim((string) Tools::getValue('CONFERBOT_SCRIPT_URL'));
        if ($scriptUrl === '') {
            $scriptUrl = self::DEFAULT_SCRIPT_URL;
        }
        if (!Validate::isAbsoluteUrl($scriptUrl)) {
            return $this->displayError($this->l('The widget script URL (in Advanced settings) is not a valid web address. Leave it empty to use the default.'));
        }

        $embedType = Tools::getValue('CONFERBOT_EMBED_TYPE');
        if (!in_array($embedType, array('live_chat', 'popup_chat'), true)) {
            $embedType = 'live_chat';
        }

        Configuration::updateValue('CONFERBOT_ENABLED', $enabled);
        Configuration::updateValue('CONFERBOT_CHATBOT_ID', $chatbotId);
        Configuration::updateValue('CONFERBOT_EMBED_TYPE', $embedType);
        Configuration::updateValue('CONFERBOT_SCRIPT_URL', $scriptUrl);
        foreach (self::$pageTypes as $page) {
            Configuration::updateValue(
                'CONFERBOT_SHOW_ON_PAGES_' . $page,
                (int) (bool) Tools::getValue('CONFERBOT_SHOW_ON_PAGES_' . $page)
            );
        }

        if ($enabled && $chatbotId !== '') {
            return $this->displayConfirmation($this->l('Saved! The chat is now live — open your store to say hello to it.'));
        }

        return $this->displayConfirmation($this->l('Saved. The chat is switched off for now — turn it on whenever you are ready.'));
    }

    public function hookDisplayHeader($params)
    {
        if (!Configuration::get('CONFERBOT_ENABLED')) {
            return '';
        }

        $chatbotId = Configuration::get('CONFERBOT_CHATBOT_ID');
        if (!$chatbotId || !preg_match('/^[a-f0-9]{24}$/i', $chatbotId)) {
            return '';
        }

        if (!$this->shouldDisplayOnCurrentPage()) {
            return '';
        }

        $this->context->smarty->assign(array(
            'conferbot_script_url' => Configuration::get('CONFERBOT_SCRIPT_URL') ?: self::DEFAULT_SCRIPT_URL,
            'conferbot_bot_id' => $chatbotId,
            'conferbot_embed_type' => Configuration::get('CONFERBOT_EMBED_TYPE') ?: 'live_chat',
            // JSON_HEX_* make the JSON safe to inline inside a <script> block
            // (customer name/email are user-controlled).
            'conferbot_options_json' => json_encode(
                $this->getWidgetOptions(),
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
            ),
        ));

        return $this->display(__FILE__, 'views/templates/hook/displayHeader.tpl');
    }

    /**
     * Maps the current front controller to a page type and checks the
     * corresponding "Show on Pages" setting.
     */
    private function shouldDisplayOnCurrentPage()
    {
        $controller = $this->context->controller;
        $phpSelf = ($controller && isset($controller->php_self)) ? $controller->php_self : '';

        $map = array(
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

        $pageType = isset($map[$phpSelf]) ? $map[$phpSelf] : 'other';
        $stored = Configuration::get('CONFERBOT_SHOW_ON_PAGES_' . $pageType);

        // Unsaved configuration defaults to visible.
        return ($stored === false) ? true : (bool) (int) $stored;
    }

    /**
     * Builds the third argument for window.ConferbotWidget(botId, embedType, options):
     * options.user carries the logged-in customer identity, options.customData
     * carries store/cart context available to the bot as visitor context.
     */
    private function getWidgetOptions()
    {
        $options = array('customData' => array(
            'platform' => 'prestashop',
            'moduleVersion' => $this->version,
            'storeName' => Configuration::get('PS_SHOP_NAME'),
            'storeUrl' => $this->context->shop->getBaseURL(true),
            'storeLanguage' => $this->context->language->iso_code,
            'storeCurrency' => $this->context->currency ? $this->context->currency->iso_code : '',
        ));

        $customer = $this->context->customer;
        if ($customer && $customer->isLogged()) {
            $options['user'] = array(
                'id' => 'ps-' . (int) $customer->id,
                'name' => trim($customer->firstname . ' ' . $customer->lastname),
                'email' => $customer->email,
            );
        }

        $cart = $this->context->cart;
        if ($cart && Validate::isLoadedObject($cart)) {
            try {
                $options['customData']['cartTotal'] = (float) $cart->getOrderTotal();
                $options['customData']['cartItemCount'] = (int) $cart->nbProducts();
            } catch (Exception $e) {
                // Cart totals can fail during early checkout states; context data is optional.
            }
        }

        return $options;
    }
}
