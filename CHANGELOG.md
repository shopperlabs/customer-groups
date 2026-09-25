# Changelog

All notable changes to `shopper/customer-groups` will be documented in this file.

## v1.0.0 - 2026-09-25

> [!IMPORTANT]
First release of Customer Groups, a free add-on for Shopper 3. Segment your customers into groups from the admin and reserve promotions for one or more of them.

### Installation

Shopper 3 is still a release candidate, so your application needs:

```json
"minimum-stability": "RC",
"prefer-stable": true

```
```bash
composer require shopper/customer-groups
php artisan shopper:customer-groups:install

```
The install command runs the migrations and registers the add-on in `AppServiceProvider`. To register it by hand:

```php
use Shopper\CustomerGroups\CustomerGroupsAddon;
use Shopper\Facades\Shopper;

Shopper::addons([
    new CustomerGroupsAddon,
]);

```
### New Features

- feat(admin): customer groups under Customers → Groups: create, edit, activate or deactivate, delete one by one or in bulk, with a member count per group ([#1](https://github.com/shopperlabs/customer-groups/pull/1))
- feat(admin): members slide-over: browse a group's members, add customers through the shared customer picker, remove them one by one or in bulk, open a customer profile
- feat(cart): **Specific groups** discount eligibility: a promotion can be reserved for one or more groups. The rule plugs into Shopper's eligibility registry, so cart validation, persistence and the admin discount form work without patching the core. Guests are asked to sign in, customers outside the targeted groups are refused.
- feat(core): `CustomerGroup` model with a public ULID, an `active()` scope and a `customers()` relation. Memberships are removed with the group.
- feat(console): `shopper:customer-groups:install` runs the migrations and registers the add-on, and stays idempotent when run twice ([#1](https://github.com/shopperlabs/customer-groups/pull/1))
- feat(admin): replaceable Livewire components through `usingLivewireComponents()`
- Translations in English, French and Spanish

### Requirements

- PHP `8.3+`
- Laravel `12.x` or `13.x`
- Shopper `^3.0.0-rc.2`, which also matches `3.0.0` stable

### Contributors

@mckenziearts

**Full Changelog**: https://github.com/shopperlabs/customer-groups/commits/v1.0.0
