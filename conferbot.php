<?php
/**
 * Conferbot - AI Chatbot, Live Chat & Customer Support for PrestaShop.
 *
 * Injects the Conferbot widget loader on the storefront and passes the
 * logged-in customer identity plus store/cart context to the widget.
 * The only credential is the Chatbot ID; appearance and behavior are
 * managed in the Conferbot dashboard and delivered at runtime.
 *
 * Logic lives in classes/ (settings, page resolution, widget context,
 * bot ID parsing); this class only wires PrestaShop hooks and the
 * configuration screen to them.
 *
 * @author    Conferbot <support@conferbot.com>
 * @copyright 2023-2026 Conferbot
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/classes/ConferbotBotId.php';
require_once __DIR__ . '/classes/ConferbotPageResolver.php';
require_once __DIR__ . '/classes/ConferbotWidgetContext.php';
require_once __DIR__ . '/classes/ConferbotSettings.php';

class Conferbot extends Module
{
    public function __construct()
    {
        $this->name = 'conferbot';
        $this->tab = 'front_office_features';
        $this->version = '1.2.0';
        $this->author = 'Conferbot';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Conferbot - AI Chatbot, Live Chat & Customer Support');
        $this->description = $this->l('Turn visitors into customers with an AI chatbot and live chat widget. Automate 24/7 customer support, capture leads, answer product questions, and hand over to human agents - set up in under a minute with no code.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall Conferbot? Module configuration will be removed.');

        if (!ConferbotSettings::getChatbotId()) {
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
        ConferbotSettings::deleteAll();

        return parent::uninstall();
    }

    // -------------------------------------------------------------------
    // Configuration screen
    // -------------------------------------------------------------------

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $output .= $this->processConfiguration();
        }

        $this->context->smarty->assign($this->getConfigureTemplateVars());

        return $output . $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    private function processConfiguration()
    {
        $pasted = (string) Tools::getValue(ConferbotSettings::KEY_CHATBOT_ID);
        $chatbotId = ConferbotBotId::extract($pasted);
        $enabled = (int) (bool) Tools::getValue(ConferbotSettings::KEY_ENABLED);

        if ($chatbotId === '' && trim($pasted) !== '') {
            return $this->displayError($this->l('We could not find a Chatbot ID in what you pasted. It looks like a 24-character code, e.g. 64a1b2c3d4e5f6a7b8c9d0e1. In your Conferbot dashboard, open your bot, click "Share", then "Copy Bot ID".'));
        }
        if ($chatbotId === '' && $enabled) {
            return $this->displayError($this->l('Please paste your Chatbot ID first (step 1), then turn the chat on.'));
        }

        $scriptUrl = trim((string) Tools::getValue(ConferbotSettings::KEY_SCRIPT_URL));
        if ($scriptUrl === '') {
            $scriptUrl = ConferbotSettings::DEFAULT_SCRIPT_URL;
        }
        if (!Validate::isAbsoluteUrl($scriptUrl)) {
            return $this->displayError($this->l('The widget script URL (in Advanced settings) is not a valid web address. Leave it empty to use the default.'));
        }

        $embedType = Tools::getValue(ConferbotSettings::KEY_EMBED_TYPE);
        if (!in_array($embedType, ConferbotSettings::$embedTypes, true)) {
            $embedType = 'live_chat';
        }

        $pages = array();
        foreach (ConferbotPageResolver::PAGE_TYPES as $page) {
            $pages[$page] = (int) (bool) Tools::getValue(ConferbotSettings::KEY_PAGE_PREFIX . $page);
        }

        ConferbotSettings::save(array(
            'enabled' => $enabled,
            'chatbotId' => $chatbotId,
            'embedType' => $embedType,
            'scriptUrl' => $scriptUrl,
            'pages' => $pages,
        ));

        if ($enabled && $chatbotId !== '') {
            return $this->displayConfirmation($this->l('Saved! The chat is now live - open your store to say hello to it.'));
        }

        return $this->displayConfirmation($this->l('Saved. The chat is switched off for now - turn it on whenever you are ready.'));
    }

    private function getConfigureTemplateVars()
    {
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
        $visibility = ConferbotSettings::getPageVisibility();
        foreach (ConferbotPageResolver::PAGE_TYPES as $page) {
            $pages[] = array(
                'id' => $page,
                'label' => $pageLabels[$page],
                'checked' => ConferbotPageResolver::isVisible($page, $visibility),
            );
        }

        return array(
            'cb_form_action' => $this->context->link->getAdminLink('AdminModules', true)
                . '&configure=' . $this->name,
            'cb_enabled' => ConferbotSettings::isEnabled(),
            'cb_chatbot_id' => ConferbotSettings::getChatbotId(),
            'cb_embed_type' => ConferbotSettings::getEmbedType(),
            'cb_script_url' => ConferbotSettings::getScriptUrl(),
            'cb_pages' => $pages,
            'cb_shop_url' => $this->context->shop->getBaseURL(true),
            'cb_logo' => $this->getPathUri() . 'logo.png',
            'cb_demo_bot_id' => ConferbotBotId::DEMO_BOT_ID,
        );
    }

    // -------------------------------------------------------------------
    // Front office
    // -------------------------------------------------------------------

    public function hookDisplayHeader($params)
    {
        if (!ConferbotSettings::isEnabled()) {
            return '';
        }

        $chatbotId = ConferbotSettings::getChatbotId();
        if (!ConferbotBotId::isValid($chatbotId)) {
            return '';
        }

        $controller = $this->context->controller;
        $pageType = ConferbotPageResolver::resolve(
            ($controller && isset($controller->php_self)) ? $controller->php_self : ''
        );
        if (!ConferbotPageResolver::isVisible($pageType, ConferbotSettings::getPageVisibility())) {
            return '';
        }

        $this->context->smarty->assign(array(
            'conferbot_script_url' => ConferbotSettings::getScriptUrl(),
            'conferbot_bot_id' => $chatbotId,
            'conferbot_embed_type' => ConferbotSettings::getEmbedType(),
            'conferbot_options_json' => ConferbotWidgetContext::toJson($this->buildWidgetOptions()),
        ));

        return $this->display(__FILE__, 'views/templates/hook/displayHeader.tpl');
    }

    private function buildWidgetOptions()
    {
        $store = array(
            'name' => Configuration::get('PS_SHOP_NAME'),
            'url' => $this->context->shop->getBaseURL(true),
            'language' => $this->context->language->iso_code,
            'currency' => $this->context->currency ? $this->context->currency->iso_code : '',
        );

        $customer = null;
        if ($this->context->customer && $this->context->customer->isLogged()) {
            $customer = array(
                'id' => $this->context->customer->id,
                'firstname' => $this->context->customer->firstname,
                'lastname' => $this->context->customer->lastname,
                'email' => $this->context->customer->email,
            );
        }

        $cart = null;
        if ($this->context->cart && Validate::isLoadedObject($this->context->cart)) {
            try {
                $cart = array(
                    'total' => $this->context->cart->getOrderTotal(),
                    'itemCount' => $this->context->cart->nbProducts(),
                );
            } catch (Exception $e) {
                // Cart totals can fail during early checkout states; context data is optional.
            }
        }

        return ConferbotWidgetContext::build($store, $this->version, $customer, $cart);
    }
}
