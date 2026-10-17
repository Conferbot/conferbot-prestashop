# PrestaShop Addons Marketplace: submission kit

Everything needed to list the Conferbot module (`conferbot` 1.3.0) on PrestaShop Addons. Checked on 2026-10-17 against the current seller guidelines at https://docs.cloud.prestashop.com/ (addons.prestashop.com blocks automated reads; its contributor guides were read from February 2026 snapshots). `MARKETPLACE.md` at the repo root holds the earlier ranking playbook and the French copy.

## 1. Where to submit

| What | Where |
| --- | --- |
| Account | An addons.prestashop.com customer account, then **My account > Open a seller account**. You need: company name, registration number (SIRET or your country's equivalent), tax ID or VAT, address, the director's ID check, and **Stripe with a EUR bank account in Europe**. Guide: https://docs.cloud.prestashop.com/1-getting-started/ |
| Submission | Seller account > Products > add a module: build the product sheet first, then upload the zip. Flow: https://docs.cloud.prestashop.com/5-submission-and-validation-process/ |
| Technical rules | https://docs.cloud.prestashop.com/2-technical-development-standards/ and the validator at https://validator.prestashop.com |
| Content rules | https://docs.cloud.prestashop.com/3-content-and-marketing-standards/ |
| Fees | **€99 a year per product** (annual validation fee for Standard Sellers). Commission on paid sales: you keep 60% to 83%. |

## 2. The deciding rule: free modules

**"Only Official Partners can offer free modules"** (getting-started guide; the product-page guide says the same). A Standard Seller cannot list Conferbot at €0. The choices:

1. **Apply for the PrestaShop Partner / Technology Partner programme**, then list it free. This fits Conferbot, which is a SaaS whose revenue is the subscription. This is the recommended route.
2. List it as a **paid** module at the lowest price on the fixed price list (**€49.99**), plus the €99 a year fee. This works against adoption for a connector merchants can also get by pasting the embed code.
3. Skip Addons and keep distributing from GitHub releases and conferbot.com, which works today.

This needs an owner decision before anything else here matters.

## 3. Current technical requirements and where we stand

| Requirement | Status |
| --- | --- |
| `conferbot/conferbot.php` in the `conferbot/` folder, zipped as `conferbot.zip` with no version in the name; one module per zip | **Rename at upload.** The release asset is `conferbot-prestashop-v1.3.0.zip`, though its contents (`conferbot/...`) are right. Upload a copy renamed to `conferbot.zip`. |
| `index.php` in every folder | OK. |
| `if (!defined('_PS_VERSION_')) exit;` in every PHP file | OK. All class and upgrade files have it; the `index.php` files are the standard redirect stubs. |
| Root `.htaccess` that denies direct PHP access | **Missing: #11.** |
| Explicit `ps_versions_compliancy` (never `_PS_VERSION_` as max) | **Fails: #11.** Today it is `'max' => _PS_VERSION_`. |
| Works on the latest PrestaShop (9.0.x); updates must too (since 1 Feb 2026) | OK. Verified on 8.2 and 9.0.3 (CHANGELOG 1.3.0). |
| `$this->module_key` from the product sheet | **To do: #11.** It only exists once the product sheet is created. |
| Zero errors, warnings or notices in `_PS_MODE_DEV_`; no `serialize`/`eval`; Smarty output escaped; configuration keys prefixed; uninstall cleans up | Believed OK (unit-tested, and the 8.2/9.0.3 runs were clean). Confirm with the validator. |
| Licence compatible with AFL | OK: AFL-3.0 (`LICENSE.txt`). |
| No external links or personal contact details in code or docs, and support only through the Addons inbox | **Risk.** The configure page links to the Conferbot dashboard and the demo bot, and PHP headers name `support@conferbot.com`. For a SaaS connector the dashboard link is functional. Tick **"requires an external paid service"** on the product sheet. If moderation objects, drop the support email from the headers. |
| Name: up to 70 characters, without "PrestaShop" or "module" | OK with the name below. |

Review: automated checks (instant to 24 hours), then technical review (5–7 working days), then marketing review (7–10 working days). The listing goes live the night after approval.

## 4. Blockers

1. **Free listing needs Official Partner status**, or the owner chooses a paid price. Decide this first.
2. **#11**: explicit `ps_versions_compliancy`, a root `.htaccess`, and `module_key`, then release 1.3.1 and run validator.prestashop.com.
3. **Seller account**: company, VAT and tax details, a Stripe EUR account, a director ID check, and €99 a year per product.
4. Required listing items still missing (see the checklist): a `readme_en.pdf` documentation PDF and a 2–5 minute YouTube setup video.

## 5. Listing copy (ready to paste)

- **Name** (52 of 70 characters): `Conferbot - AI Chatbot, Live Chat & Customer Support`
- **Short description** (178 of 250 characters): `AI chatbot and live chat for your store. Answer product questions 24/7, capture leads and hand shoppers over to your team. Paste one ID and switch it on: no code, no theme edits.`
- **Merchant benefits:**
  - Fewer repetitive support emails: the bot answers product, shipping and order questions around the clock
  - More sales: the bot sees the logged-in customer's cart, and orders placed after a chat are credited to that chat
  - More leads: collect and qualify visitors' contact details automatically
- **Customer benefits:**
  - Instant answers at any hour, in the shopper's language
  - A real person when needed: handover to your team with queue position, file sharing and a post-chat rating; leave a message when nobody is online
  - A chat that already knows them when they are logged in
- **Features** (at least 3, specific):
  - Two-step configuration page: paste the Chatbot ID (or the whole embed code), switch the chat on
  - Logged-in customer name, email and cart total passed to the conversation
  - Purchase tracking on the order confirmation page, reported once per order
  - Optional identity verification (HMAC-SHA256, signed on the server; the secret never reaches the browser)
  - Show or hide the chat per page type
  - Widget design, AI agent, flows, greetings and languages managed in the Conferbot dashboard and applied at runtime
  - Compatible with PrestaShop 1.7, 8 and 9 (verified on 8.2 and 9.0.3)
- **Keywords** (no "PrestaShop" or "module"): `chatbot, live chat, ai chatbot, customer support, chat widget, customer service, helpdesk, lead generation, chat bot`
- **Category:** Customer Service > Live Chat (primary)
- **External service:** tick "requires an external paid service". **Pricing text:** "The module connects your store to Conferbot. A Conferbot account is required; a free plan is available (600 conversations a month, no card)."
- **Support:** through the Addons inbox, as Addons requires. Reply within 1 working day. Answers are handled by `support@conferbot.com`.
- **Privacy policy:** https://www.conferbot.com/privacy-policy (HTTP 200). **Terms:** https://www.conferbot.com/terms-of-service (HTTP 200)
- French name and summary: see `MARKETPLACE.md`.

### Data and GDPR statement

This statement matches `classes/ConferbotWidgetContext.php` and `conferbot.php`.

- The module stores only its configuration values: the bot ID, the switch, the embed type, the page visibility and the optional secret. It creates no tables, and uninstalling deletes the configuration.
- On front-office pages where the chat is on and visible, the shopper's browser loads `https://cdn.conferbot.com/dist/v1/widget.min.js`. The page passes the widget:
  - the Chatbot ID and module version
  - `platform: "prestashop"`, plus the store name, URL, language and currency
  - for **logged-in customers only**: the customer ID (`ps-<id>`), first and last name, and email. If a secret is set, it also passes an HMAC-SHA256 of the ID; the secret is never sent.
  - the cart total and item count
  - on the order confirmation page, once per order: the order value, currency and order ID
- Whatever the shopper types or uploads in the chat goes to Conferbot as the conversation.
- The module sets no cookies of its own.

## 6. Reviewer test instructions

> 1. Modules > Module Manager > Upload a module: `conferbot.zip`.
> 2. Click **Configure** on "Conferbot - AI Chatbot, Live Chat & Customer Support".
> 3. Click "Try our demo bot", or paste the public demo Chatbot ID `691c970890527a0468f9b2c9` (a live "Startup Consultation Chatbot"; no Conferbot account needed). Turn the chat on and click Save.
> 4. Open the front office. A chat bubble appears bottom right. Click it and chat with the bot.
> 5. Optional: log in as a customer and add a product. The bot receives the name, email and cart total. Uninstalling the module removes the widget and the configuration.

Addons demo back office login convention: `demo@prestashop.com` / `demodemo`.

## 7. Assets (in `docs/marketplace/assets/`)

| File | Size | Content |
| --- | --- | --- |
| `icon-256x256.png` | 256x256 | Conferbot logo (from the landing site), the current guideline size. The module's own `logo.png` is 128x128. |
| `screenshot-1.png` | 1000x1000 | Back office: the configure page after saving (real frame, PrestaShop 9.0.3) |
| `screenshot-2.png` | 1000x1000 | Front office: the widget's home screen open on the classic theme |
| `screenshot-3.png` | 1000x1000 | Front office: a conversation, including the "Talk to a person" handover |

These are square and at least 1000x1000, the minimum of 3 covering back and front office. Still to make, by the owner: `readme_en.pdf` (installation, configuration, features, FAQ; the README sections convert directly) and a YouTube video. The 72-second install recording at https://github.com/Conferbot/conferbot-prestashop/releases/download/v1.3.0/install.mp4 shows installation and setup, which is what Addons asks for. Upload it as is.

## 8. Owner checklist

- [ ] Decide: Official Partner (free listing), paid at €49.99, or no Addons.
- [ ] Open the seller account: company and VAT details, Stripe (EUR), director ID, accept the seller terms, pay €99 a year per product.
- [ ] Create the product sheet with the copy above. Copy the generated `module_key` into #11, fix the rest of #11, release 1.3.1, and get it clean on validator.prestashop.com.
- [ ] Upload `conferbot.zip` (renamed) with the changelog and supported versions (1.7.0 to 9.0.x).
- [ ] Upload the icon, 3 screenshots, `readme_en.pdf` and the YouTube video. Add the FR translation from `MARKETPLACE.md`.
- [ ] Answer technical and marketing review feedback, and keep to the 1-working-day reply time on the Addons inbox after launch.

Owner time: about 2 hours (seller KYC, sheet, PDF, video upload). Review: about 2–3 weeks. Fees: €99 a year per product, plus Stripe set-up; commission applies only if paid.
