# Changelog

## 1.3.0 - 2026-10-17

- Identity verification: with the workspace secret under Advanced settings, the logged-in customer is sent with `hash = HMAC-SHA256(secret, user.id)`, computed on the server. The secret is write-only and never sent to the browser.
- Sales tracking: purchases are reported once per confirmed order, and cart updates are reported; the loader guards against a missing widget.
- Upgrade script `upgrade-1.3.0.php`: removes the legacy 1.0.x configuration keys and makes sure `displayHeader` is registered, so an in-place upgrade keeps the chat.
- The bot ID help points to Share > Embed into your website in the Conferbot dashboard, with a plain address-bar hint.
- The module name no longer shows a double-escaped `&amp;` in the PrestaShop 9 admin.
- Verified on PrestaShop 8.2 and 9.0.3.

## 1.2.0 - 2026-07-15

- Two-step configure page: paste the Chatbot ID (or the full embed snippet; the ID is extracted) and turn it on.
- One-click demo bot that works without a Conferbot account.
- Logged-in customer (name, email) and cart total/count passed to the bot as visitor context.
- Per-page-type visibility: home, category/search, product, cart, checkout, account, other.
- Logic split into small classes under `classes/` with a PHPUnit suite; context JSON is script-injection safe.
