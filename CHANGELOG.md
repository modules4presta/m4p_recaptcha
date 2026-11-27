# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses
[semantic versioning](https://semver.org/).

## [1.1.2] - 2026-11-26

### Changed

- The secret key is a password field: the saved key is not shown in the configuration, and an empty field keeps it.

## [1.1.1] - 2026-11-24

### Changed

- Polish attribution note: "Formularz chroni reCAPTCHA Google: prywatność i warunki."

## [1.1.0] - 2026-10-27

First open-source release, published under the MIT license.

### Added

- `M4PRECAPTCHA_REVIEW_MODULE` setting: the reviews module whose submissions are verified,
  `productcomments` by default. An empty value limits the checks to the contact, login and
  registration forms.
- en-US and pl-PL catalogues, MIT headers, `LICENSE`, `README`, `SECURITY`, `CONTRIBUTING`,
  `composer.json` and `index.php` guards in every directory.

### Changed

- Module settings and the "protected by reCAPTCHA" notice use the new translation system.
- The reviews module is read from the configuration.

## [1.0.0] - 2026-10-06

### Added

- Server-side reCAPTCHA v3 verification of the contact, login, registration and product review forms,
  each switched on separately, with a minimum score shared by all of them.
- Google's `api.js` loaded on the first focus in a protected form, not on every page.
- `displayM4pRecaptcha` hook with an `action` parameter for themes.
