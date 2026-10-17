# Deploying the Conferbot PrestaShop Module

## What gets deployed

A single installable zip (`conferbot-prestashop-v1.3.0.zip`) whose top-level directory is `conferbot/`. No backend changes are required: the module only injects the public widget loader (`https://cdn.conferbot.com/dist/v1/widget.min.js`) and calls `window.ConferbotWidget(botId, embedType, options)`. The widget then talks to the production embed-server (`wdt.conferbot.com`) over Socket.IO, exactly like the snippet from the dashboard's **Share > Embed into website** page.

## Prerequisites

- A Conferbot bot that is **published and not disabled**; copy its embed code from **Share > Embed** (the module finds the 24-char Bot ID in it).
- PrestaShop 1.7, 8.x or 9.x (verified on 8.2 and 9.0.3), PHP 7.2+.
- The store's domain must NOT be listed in the bot's **Customize > Advanced > Disabled URLs** (there is no domain whitelist - any origin may embed a bot; disabled URLs is the only suppression mechanism).

## Building the zip

From a clean checkout of this repository, at the release tag:

```bash
git archive --format=zip --prefix=conferbot/ -o conferbot-prestashop-v1.3.0.zip v1.3.0
```

`.gitattributes` keeps `tests/`, `phpunit.xml`, `docs/`, this file and `MARKETPLACE.md` out of the zip. Do not zip the working directory by hand: that would ship `.git` and the test suite.

The zip must contain `conferbot/` as its only top-level entry (PrestaShop derives the module name from it).

## Installing on a store

**Via admin UI (recommended for merchants):**
1. Modules > Module Manager > **Upload a module** > drop the zip.
2. Click **Configure** on "Conferbot Live Chat".
3. Paste the Bot ID, pick display type and page visibility, Save.
4. Open the storefront; the chat bubble appears (allow a few seconds; the bot's own "load widget delay" setting applies).

**Via CLI (for hosted/managed stores):**
```bash
unzip conferbot-prestashop-v1.3.0.zip -d /var/www/html/modules/
chown -R www-data:www-data /var/www/html/modules/conferbot
php bin/console prestashop:module install conferbot
# set config non-interactively if desired:
php -r 'require "config/config.inc.php";
  Configuration::updateValue("CONFERBOT_ENABLED", 1);
  Configuration::updateValue("CONFERBOT_CHATBOT_ID", "<BOT_ID>");
  Configuration::updateValue("CONFERBOT_EMBED_TYPE", "live_chat");'
php bin/console cache:clear
```

## Verifying an installation

1. `curl -s https://store.example.com/ | grep conferbot-js` → the loader script must be present in `<head>`.
2. In a browser, the DOM should contain a `<conferbot-widget>` element after load.
3. Log in as a customer and start a chat: the conversation in the Conferbot inbox should show the visitor name/email (`options.user`) and `platform: prestashop`, cart totals etc. in the visitor context.
4. Toggle "Show on Pages" entries and confirm the snippet disappears from those page types (clear the PrestaShop cache after config changes if the store uses Smarty cache aggressively).

## Distribution channels

- **Direct download**: host the zip at e.g. `conferbot.com/integrations/prestashop` and document the Upload-a-module flow.
- **PrestaShop Addons marketplace**: requires an Addons seller account. Before submitting, run the official validator at https://validator.prestashop.com (checks license headers, index.php stubs in every directory, `escape` usage in templates, `ps_versions_compliancy`). The module already includes index.php stubs and AFL-3.0 headers. Marketplace review typically takes 1–2 weeks.
- **In-dashboard guide**: add PrestaShop to the client's platform picker (`client/src/components/Chatbot/share/ChatbotEmbed.tsx` `platforms` array + an install-guide page at `conferbot.com/resources/install-guide/prestashop`).

## Versioning / upgrades

Bump `$this->version` in `conferbot.php` AND `<version>` in `config.xml` together. PrestaShop runs upgrade scripts from the `upgrade/` directory (`upgrade-X.Y.Z.php`) when the installed version is older. `upgrade-1.3.0.php` removes the legacy 1.0.x keys and makes sure `displayHeader` is registered, so an in-place upgrade keeps the chat (verified on 8.2: 1.2.0 to 1.3.0 with a leftover 1.0.x key and no hook). Uninstall removes all `CONFERBOT_*` configuration keys (including legacy 1.0.x keys).

## Explicitly out of scope in v1.3.0

- **Outbound webhooks** (order status, abandoned cart, product sync): the Conferbot backend has no PrestaShop endpoints yet (`server/docs/integrations/prestashop-integration-guide.md` is a design doc only). Do not re-add the v1.0.0 webhook code until routes exist under the embed-server/server.
- **API-key features** (product search, order lookup from chat): would require new module front controllers plus bot flow nodes; roadmap item.

## Local test environment (used for the e2e run)

```bash
docker network create conferbot-e2e
docker run -d --name cb-mysql --network conferbot-e2e \
  -e MYSQL_ROOT_PASSWORD=admin -e MYSQL_DATABASE=prestashop \
  mysql:8.0 --default-authentication-plugin=mysql_native_password
docker run -d --name cb-presta --network conferbot-e2e -p 8085:80 \
  -e DB_SERVER=cb-mysql -e DB_PASSWD=admin -e DB_NAME=prestashop \
  -e PS_INSTALL_AUTO=1 -e PS_DOMAIN=localhost:8085 \
  -e ADMIN_MAIL=demo@conferbot.com -e ADMIN_PASSWD=Conferbot123! \
  prestashop/prestashop:8.1-apache
# admin: http://localhost:8085/admin1  front: http://localhost:8085
# demo customer: pub@prestashop.com / 123456789
docker cp prestashop-module cb-presta:/var/www/html/modules/conferbot
docker exec cb-presta chown -R www-data:www-data /var/www/html/modules/conferbot
docker exec cb-presta php bin/console prestashop:module install conferbot
```

Teardown: `docker rm -f cb-presta cb-mysql && docker network rm conferbot-e2e`
