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

The only credential needed is the **Chatbot ID** (the bot's ID from the Conferbot dashboard). All widget appearance and behavior — position, colors, theme, language, auto-open, proactive prompts, disabled URLs — is configured in the Conferbot dashboard under **Customize** and delivered to the widget at runtime; the module does not duplicate those settings.

`options.user` and `options.customData` become the visitor's identity/context in Conferbot chats, so the bot and human agents see the customer's name, email, cart total, and store details.

## Requirements

- PrestaShop 1.7 or 8.x
- PHP 7.2+
- A Conferbot account with a published chatbot

## Installation

1. Zip the module folder as `conferbot.zip` (the zip must contain a top-level `conferbot/` directory).
2. In the PrestaShop admin, go to **Modules > Module Manager > Upload a module** and upload the zip.
   (Or copy the folder to `/modules/conferbot/` and install from Module Manager. CLI: `bin/console prestashop:module install conferbot`.)
3. Click **Configure**.

## Configuration

| Setting | Description |
|---|---|
| Enable Conferbot | Master on/off switch. |
| Chatbot ID | Required. In the Conferbot dashboard open your bot, go to **Share > Embed into website**, and copy the Bot ID (24-character hex). |
| Display Type | `Widget (floating bubble)` or `Popup`. |
| Show on Pages | Per-page-type visibility: home, category/search, product, cart, checkout, customer account, and all other pages. |
| Widget Script URL | Advanced. Leave empty for the Conferbot CDN default. Useful for staging/self-hosted widget builds. |

## Testing

1. Save the configuration with a valid Chatbot ID.
2. Open the storefront — the chat bubble should appear (subject to the bot's own dashboard settings such as disabled URLs and load delay).
3. Log in as a customer and start a chat: the conversation in the Conferbot inbox should show the customer's name/email and cart context.

## Notes & limitations

- No API key or workspace ID is required — the widget resolves everything server-side from the Chatbot ID over Socket.IO (`wdt.conferbot.com`).
- Outbound webhooks (order status, product updates, abandoned cart) are **not** part of this version; the Conferbot backend does not yet expose PrestaShop webhook endpoints. See `server/docs/integrations/prestashop-integration-guide.md` for the roadmap.
- If the widget does not appear: check the module is enabled, the Chatbot ID is correct and the bot is published/not disabled, the current page type is enabled in **Show on Pages**, and clear the PrestaShop cache (**Advanced Parameters > Performance > Clear cache**).

## File structure

```
modules/conferbot/
├── conferbot.php        # Module class (config form + displayHeader hook)
├── config.xml           # Module metadata
├── index.php            # Security stub (also in every subdirectory)
├── README.md
└── views/templates/hook/displayHeader.tpl   # Loader snippet template
```

## License

Academic Free License (AFL 3.0).
