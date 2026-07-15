# Conferbot PrestaShop Module

Embeds the [Conferbot](https://conferbot.com) AI chat widget on a PrestaShop store and passes the logged-in customer identity plus store/cart context to the bot.

## How it works

The module hooks `displayHeader` and injects the standard Conferbot loader snippet:

```html
<script src="https://cdn.conferbot.com/dist/v1/widget.min.js" async></script>
<script>
  window.ConferbotWidget("<CHATBOT_ID>", "live_chat", {
    user:       { id, name, email },              // logged-in customer, if any
    customData: { platform: "prestashop", storeName, storeUrl,
                  storeLanguage, storeCurrency, cartTotal, cartItemCount }
  });
</script>
```

The only credential needed is the **Chatbot ID** (the bot's ID from the Conferbot dashboard). All widget appearance and behavior - position, colors, theme, language, auto-open, proactive prompts, disabled URLs - is configured in the Conferbot dashboard under **Customize** and delivered to the widget at runtime; the module does not duplicate those settings.

`options.user` and `options.customData` become the visitor's identity/context in Conferbot chats, so the bot and human agents see the customer's name, email, cart total, and store details.

## Requirements

- PrestaShop 1.7 or 8.x
- PHP 7.2+
- A Conferbot account with a published chatbot (or use the built-in demo bot to try it first)

## Installation

1. Zip the module folder as `conferbot.zip` (the zip must contain a top-level `conferbot/` directory).
2. In the PrestaShop admin, go to **Modules > Module Manager > Upload a module** and upload the zip.
   (Or copy the folder to `/modules/conferbot/` and install from Module Manager. CLI: `bin/console prestashop:module install conferbot`.)
3. Click **Configure**.

## Configuration

The configure screen is two steps: paste your Chatbot ID, flip the switch, save. You can paste the bare ID, the full embed snippet, or a dashboard URL - the module extracts the ID for you.

**No account yet?** Click "Try our demo bot" on the configure screen. It fills in the public demo bot (`691c970890527a0468f9b2c9`), which works out of the box so you can see the widget live before signing up.

| Setting | Description |
|---|---|
| Chatbot ID | Required. In the Conferbot dashboard open your bot, go to **Share > Embed into website**, and copy the Bot ID (24-character hex). |
| Turn the chat on | Master on/off switch. |
| How the chat appears | Floating chat bubble (default) or popup window. *(Advanced)* |
| Show on these pages | Per-page-type visibility: home, category/search, product, cart, checkout, customer account, all other pages. *(Advanced)* |
| Widget script URL | Leave as is for the Conferbot CDN. Useful for staging/self-hosted widget builds. *(Advanced)* |

## Architecture

The module class only wires PrestaShop hooks and the configure screen; the logic lives in small, individually tested classes:

```
modules/conferbot/
├── conferbot.php                        # Module: hooks + configure screen wiring
├── classes/
│   ├── ConferbotBotId.php               # Bot ID extraction/validation + demo bot ID
│   ├── ConferbotPageResolver.php        # Controller -> page type map + visibility rules
│   ├── ConferbotWidgetContext.php       # Builds ConferbotWidget() options + safe JSON
│   └── ConferbotSettings.php            # Configuration keys, defaults, persistence
├── views/templates/
│   ├── admin/configure.tpl              # Two-step configure UI
│   └── hook/displayHeader.tpl           # Widget loader snippet
├── tests/                               # PHPUnit suite (not shipped in the zip)
├── phpunit.xml
├── logo.png
├── config.xml
├── DEPLOYMENT.md                        # Packaging, install, verification
└── MARKETPLACE.md                       # PrestaShop Addons listing kit
```

Adding a setting is a one-file change in `ConferbotSettings` plus a form field in `configure.tpl`; adding a page type is one entry in `ConferbotPageResolver`.

## Tests

73 PHPUnit tests (155 assertions) cover bot ID parsing, controller-to-page-type mapping, visibility rules, widget option building (guest/customer/cart variants), script-injection safety of the inlined JSON, and settings persistence including legacy-key cleanup. The suite runs without a PrestaShop installation - `tests/bootstrap.php` stubs `Configuration`.

```bash
# any PHP 7.2+ with phpunit 9:
phpunit

# or containerized:
docker run --rm -v "$PWD":/mod -v /path/to/phpunit-9.phar:/phpunit.phar:ro \
  -w /mod php:8.2-cli php /phpunit.phar
```

## Manual verification

1. Save the configuration with a valid Chatbot ID (or the demo bot).
2. Open the storefront - the chat bubble should appear (subject to the bot's own dashboard settings such as disabled URLs and load delay).
3. Log in as a customer and start a chat: the conversation in the Conferbot inbox should show the customer's name/email and cart context.

## Notes & limitations

- No API key or workspace ID is required - the widget resolves everything server-side from the Chatbot ID over Socket.IO (`wdt.conferbot.com`).
- Outbound webhooks (order status, product updates, abandoned cart) are **not** part of this version; the Conferbot backend does not yet expose PrestaShop webhook endpoints. See `server/docs/integrations/prestashop-integration-guide.md` for the roadmap.
- If the widget does not appear: check the module is enabled, the Chatbot ID is correct and the bot is published/not disabled, the current page type is enabled in **Show on these pages**, and clear the PrestaShop cache (**Advanced Parameters > Performance > Clear cache**).

## License

Academic Free License (AFL 3.0).
