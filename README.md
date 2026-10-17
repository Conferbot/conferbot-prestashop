# Conferbot PrestaShop Module

Embeds the [Conferbot](https://conferbot.com) AI chat widget on a PrestaShop store and passes the logged-in customer identity plus store/cart context to the bot.

## See it in action

Upload the module, paste the Chatbot ID, flip the switch, and the bot is live on the storefront (recorded on PrestaShop 9.0.3). Click the GIF for the full-size MP4:

[![Conferbot on PrestaShop: upload the module, paste the bot ID, save, open the store and chat](docs/media/install.gif)](https://github.com/Conferbot/conferbot-prestashop/releases/download/v1.3.0/install.mp4)

## How it works

The module hooks `displayHeader` and injects the standard Conferbot loader. The widget script loads without blocking the page, and `ConferbotWidget` is called once it has loaded:

```html
<script>
  (function (d, s, id) {
    var js = d.createElement(s); js.async = true; js.id = id;
    js.src = "https://cdn.conferbot.com/dist/v1/widget.min.js";
    d.head.appendChild(js);
    js.onload = function () {
      window.ConferbotWidget("<CHATBOT_ID>", "live_chat", {
        user:       { id: "ps-42", name, email, hash },   // logged-in customer, if any
        customData: { platform: "prestashop", storeName, storeUrl,
                      storeLanguage, storeCurrency, cartTotal, cartItemCount }
      });
      // On the order confirmation page, once per order:
      window.ConferbotWidget.track("purchase", { value, currency, order_id });
    };
  })(document, "script", "conferbot-js");
</script>
```

The only credential needed is the **Chatbot ID**. Everything visitors see is set per bot in the Conferbot dashboard and delivered at runtime, with nothing to configure in PrestaShop: flows and AI agents, live chat with handover to your team (queue position, leave a message when nobody is free, read receipts, file sharing, post-chat rating), the home screen, themes and dark mode, colours, launcher and mascot, greetings, languages and disabled URLs. The module does not duplicate those settings.

### Identity verification (optional)

With a secret from Conferbot (workspace settings > Security > Identity verification) pasted under Advanced settings, each logged-in customer is sent with `hash = HMAC-SHA256(secret, user.id)`, computed on your server. Conferbot then trusts their name and email and can link their chats to their contact. The secret is stored in your PrestaShop configuration and never sent to the browser. Without it, the customer's details are still passed but marked unverified, and if your workspace *requires* verification they are dropped.

### Sales tracking

The order confirmation page reports the customer's own order (`purchase`, with value, currency and order reference) once per order, and every cart change is reported as `cart_updated`. Conferbot credits a sale to a chat that happened within the week before it.

`options.user` and `options.customData` become the visitor's identity/context in Conferbot chats, so the bot and human agents see the customer's name, email, cart total, and store details.

## Requirements

- PrestaShop 1.7, 8.x or 9.x (verified on 8.2 and 9.0.3).
- PHP 7.2 or later (the module itself; your PrestaShop version may need a newer PHP).
- A Conferbot account with a published bot ([app.conferbot.com](https://app.conferbot.com)), or the built-in demo bot to try it first.
- Visitors' browsers load `https://cdn.conferbot.com/dist/v1/widget.min.js` and connect to `wdt.conferbot.com`. If the store sends a Content Security Policy, allow both.

## Installation

### 1. Get the module zip

- **PrestaShop Addons:** not listed yet (see [MARKETPLACE.md](MARKETPLACE.md)). Once it is, install it from **Modules > Marketplace** and skip to step 3.
- **GitHub:** download `conferbot-prestashop-v<version>.zip` from the [latest release](https://github.com/Conferbot/conferbot-prestashop/releases/latest).
- **From a checkout:** the zip must contain a single top-level `conferbot/` folder with `conferbot.php` inside it:

  ```bash
  git archive --format=zip --prefix=conferbot/ -o conferbot.zip HEAD
  ```

### 2. Upload it

1. In the PrestaShop back office go to **Modules > Module Manager**.
2. Click **Upload a module** (top right) and drop the zip on it. PrestaShop installs it straight away and offers **Configure**.

Over FTP/SSH instead: copy the folder to `modules/conferbot/`, then find **Conferbot** in Module Manager and click **Install**. Or from the shop root: `php bin/console prestashop:module install conferbot`.

### 3. Find your Chatbot ID

The Chatbot ID is a 24-character code such as `64a1b2c3d4e5f6a7b8c9d0e1`.

1. Sign in at [app.conferbot.com](https://app.conferbot.com) and open your bot.
2. Click **Share**, then **Embed into your website**.
3. The ID is the first value in the code: `window.ConferbotWidget("64a1b2c3d4e5f6a7b8c9d0e1", "live_chat")`. It is also in the browser's address bar: `.../chatbot/64a1b2c3d4e5f6a7b8c9d0e1/embed`.

You can paste the whole embed code or the dashboard URL instead; the module keeps only the ID.

### 4. Turn it on

1. Open the module's **Configure** screen (Module Manager > Conferbot > Configure).
2. **Step 1:** paste the Chatbot ID.
3. **Step 2:** turn the chat on.
4. Click the big save button. The green banner says *Saved! The chat is now live*.

## Configuration

The configure screen is two steps: paste your Chatbot ID, flip the switch, save. You can paste the bare ID, the full embed snippet, or a dashboard URL - the module extracts the ID for you.

**No account yet?** Click "Try our demo bot" on the configure screen. It fills in the public demo bot (`691c970890527a0468f9b2c9`), which works out of the box so you can see the widget live before signing up.

| Setting | Description |
|---|---|
| Chatbot ID | Required. In the Conferbot dashboard open your bot, go to **Share > Embed**, and copy the embed code; the module finds the 24-character ID in it. |
| Turn the chat on | Master on/off switch. |
| How the chat appears | Floating chat bubble (default) or popup window. *(Advanced)* |
| Show on these pages | Per-page-type visibility: home, category/search, product, cart, checkout, customer account, all other pages. *(Advanced)* |
| Identity verification secret | Optional. Signs logged-in customers so Conferbot can trust them. Write-only: shown as saved, never displayed. *(Advanced)* |
| Widget script URL | Leave as is for the Conferbot CDN. Useful for staging/self-hosted widget builds. *(Advanced)* |

## Architecture

The module class only wires PrestaShop hooks and the configure screen; the logic lives in small, individually tested classes:

```
modules/conferbot/
├── conferbot.php                        # Module: hooks + configure screen wiring
├── .htaccess                            # Denies direct HTTP access to the PHP files (Addons rule)
├── classes/
│   ├── ConferbotBotId.php               # Bot ID extraction/validation + demo bot ID
│   ├── ConferbotPageResolver.php        # Controller -> page type map + visibility rules
│   ├── ConferbotWidgetContext.php       # Builds ConferbotWidget() options + safe JSON
│   └── ConferbotSettings.php            # Configuration keys, defaults, persistence
├── upgrade/upgrade-1.3.0.php            # In-place upgrade: legacy keys out, storefront hook in
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

79 PHPUnit tests (173 assertions; PHP 7.4, 8.2 and 8.4) cover bot ID parsing, controller-to-page-type mapping, visibility rules, widget option building (guest/customer/cart variants), the identity hash and write-only secret, purchase events, script-injection safety of the inlined JSON, settings persistence including legacy-key cleanup, and the Addons packaging rules (explicit compatibility range, root `.htaccess`, matching versions in `conferbot.php` and `config.xml`). The suite runs without a PrestaShop installation - `tests/bootstrap.php` stubs `Configuration`.

```bash
# any PHP 7.2+ with phpunit 9:
phpunit

# or containerized:
docker run --rm -v "$PWD":/mod -v /path/to/phpunit-9.phar:/phpunit.phar:ro \
  -w /mod php:8.2-cli php /phpunit.phar
```

## Check that it works

1. Open the storefront in a private window. The chat bubble appears bottom right (unless the bot's dashboard settings delay it or hide it on that URL).
2. Click it, start the chat and send a message. It shows up in the Conferbot dashboard under the bot's conversations.
3. Log in as a customer and chat again: the conversation shows the customer's name, email and cart total (marked verified when the identity secret is set).
4. Place an order: the order confirmation page reports the purchase once.
5. To check the code is on the page: view the page source and search for `conferbot-js`. It appears once, in the `<head>`.

## Troubleshooting

| Symptom | Cause and fix |
|---|---|
| The upload is refused | The zip must contain a top-level `conferbot/` folder (not the files loose, and not `conferbot-prestashop-main/`). |
| No `conferbot-js` in the page source | The switch is off, the module is disabled in Module Manager, or the page type is unticked under **Show on these pages**. Then clear the cache: **Advanced Parameters > Performance > Clear cache**, plus any page cache module or CDN. |
| Still nothing after clearing the cache | The theme does not call the `displayHeader` hook. Check **Design > Positions** for Conferbot under *displayHeader*, and that the theme's `head.tpl` includes `{hook h='displayHeader'}`. |
| `conferbot-js` is there but no bubble | A Content Security Policy without `cdn.conferbot.com` / `wdt.conferbot.com`, a script blocker, or the bot is unpublished or hides itself on that URL. Check the browser console. |
| Logged-in customers are unverified | Paste the identity verification secret and make sure it matches the one in your Conferbot workspace. |

Still stuck? Write to support@conferbot.com.

## Uninstall

- **Pause it:** turn the chat off on the configure screen and save. The settings are kept.
- **Disable:** Module Manager > Conferbot > **Disable**. Nothing is loaded on the store; the settings are kept.
- **Uninstall:** Module Manager > Conferbot > **Uninstall**. This removes every `CONFERBOT_*` setting, including the identity secret. Then **Delete** removes the files from `modules/conferbot/`.

Your bot and its conversations stay in your Conferbot account.

## Notes & limitations

- No API key or workspace ID is required - the widget resolves everything server-side from the Chatbot ID over Socket.IO (`wdt.conferbot.com`).
- Outbound webhooks (order status, product updates, abandoned cart) are **not** part of this version; the Conferbot backend does not yet expose PrestaShop webhook endpoints. See `server/docs/integrations/prestashop-integration-guide.md` for the roadmap.

## License

Academic Free License (AFL 3.0).
