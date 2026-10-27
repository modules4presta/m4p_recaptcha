# reCAPTCHA v3 for PrestaShop 8 & 9

**Stops the spam that arrives through your contact form, your registration form and your product reviews, without asking a single customer to click a puzzle.**

> **Meta description (155 chars):** reCAPTCHA v3 for PrestaShop: protects contact, login, registration and reviews with no puzzles and no visible widget. Free, MIT licensed.

---

## Why form spam matters for your store

A shop form that anybody can post to is a shop form that bots will post to. The contact form fills the inbox with link spam, the registration form fills the customer list with addresses that never order, and product reviews fill the catalogue with text nobody wrote about products nobody bought. Each one costs somebody's time to read and delete, and reviews that stay published cost the trust of the next visitor.

The usual answer is a checkbox or an image puzzle, paid for by every honest customer in the queue. reCAPTCHA v3 scores the visit in the background instead: the shop asks Google how human the session looks and only rejects what falls below the score you set. Nothing is shown, nothing is clicked.

- **No friction for customers** – no checkbox, no images, no extra step in registration or checkout.
- **One score, four forms** – contact, login, registration and reviews, each switched on separately.
- **The script loads when it is needed** – Google's `api.js` is fetched on the first focus in a protected form, so pages that nobody types into stay light.

## What the module does

The module renders a hidden token field in the protected forms, asks Google for a score when the form is submitted, and stops the submission before the controller runs when the score is below the threshold. It does not touch the forms themselves, the templates of other modules, or the customer's session.

### Key features

- **Server-side verification** – the token is checked against `siteverify` with the secret key and the action name; a reply that fails, times out or is missing stops the submission.
- **Per-form switches** – protect only the forms that get abused; the rest stay untouched.
- **Any reviews module** – the module whose submissions are checked is named in the configuration, so forks and renamed clones of `productcomments` work as well.
- **Minimum score** – 0.0 to 1.0, applied to every protected form.
- **Hook for themes** – `displayM4pRecaptcha` with an `action` parameter renders the field anywhere a theme needs it.
- **Error handling in the page** – a rejected submission comes back as a form error with the values the customer typed, not as a blank page.

### What happens when reCAPTCHA is unreachable

A verification that times out counts as a failure, so a submission is rejected rather than let through unchecked. The timeout is five seconds. With the keys left empty the module verifies nothing and every form works as it did before installation, which is also what happens after uninstalling.

## Compatibility

| | |
|---|---|
| PrestaShop | 8.0 – 9.x |
| PHP | 7.4+ |
| Requirements | a reCAPTCHA v3 key pair from Google Cloud |
| Multistore | Settings are shared by every shop in the installation |
| Themes | Works with any theme; the review form needs the module's hook (one line, see below) |

The module overrides no core class and creates no database table.

## Installation

1. Upload and install the module from **Modules → Module Manager**.
2. Create a score-based (v3) key in **Google Cloud → Security → reCAPTCHA** and copy its key ID and legacy secret key into the module's configuration.
3. Switch on the forms to protect and set the minimum score.
4. For reviews, add `{hook h='displayM4pRecaptcha' action='review'}` inside the review form of the theme and enter the name of the reviews module in the configuration.

If submissions start failing right after installation, the site key and the secret key have been swapped: the site key is public and travels to the browser, the secret key stays on the server.

## Configuration options

| Setting | Description |
|---|---|
| **Site key** | Key ID of the v3 website key, sent to the browser. |
| **Secret key** | The legacy secret key of the same key, used for server-side verification. |
| **Minimum score** | 0.0 is certainly a bot, 1.0 certainly a human. Google recommends 0.5; raise it only while watching what gets rejected. |
| **Protect the contact form** | Checks `submitMessage` on the contact page. |
| **Protect the login form** | Checks `submitLogin` on the authentication page. |
| **Protect registration** | Checks the account form before the customer is created. |
| **Protect product reviews** | Checks a review before it is saved. |
| **Reviews module** | Technical name of the module that saves reviews, `productcomments` by default. An empty field limits the checks to the three forms above. |

## Frequently asked questions

**Do customers see or click anything?**
No. reCAPTCHA v3 has no widget. The only visible element is the "protected by reCAPTCHA" notice that Google's terms require, rendered as small print under the form.

**What is sent to Google?**
The browser sends what Google's script collects about the session, and the shop sends the token, the secret key and the shop's IP address to `siteverify`. No customer data from the form is sent. The notice under the form links Google's privacy policy and terms, as the service requires.

**My reviews module is not called productcomments.**
Enter its technical name – the directory name under `modules/` – in the **Reviews module** field. A review submission is checked when it is posted to that module with the `add_comment` action, which is what `productcomments` and its forks use.

**Can I turn it off again?**
Yes. Switch off a single form in the configuration, or uninstall the module: the settings are removed and the forms go back to their default behaviour.

---

**Keywords:** prestashop recaptcha, prestashop recaptcha v3, prestashop contact form spam, prestashop registration spam, prestashop fake reviews, prestashop antispam module

## License

MIT – see [LICENSE](LICENSE). Free to use commercially, fork and modify; keep the copyright notice.

## Contributing

Bug reports and pull requests are welcome – see [CONTRIBUTING.md](CONTRIBUTING.md). For security
issues, follow [SECURITY.md](SECURITY.md) instead of opening a public issue.

---

Built by [Nice Code](https://nice-code.com/pl/oferta/moduly-prestashop) – we build and maintain PrestaShop stores.

© Nice Code sp. z o.o. (Modules4Presta) – released under the MIT license.
