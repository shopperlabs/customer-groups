# Shopper Customer Groups

![PHP Version](https://img.shields.io/badge/php-%5E8.3-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/laravel-12.x%20%7C%2013.x-FF2D20?logo=laravel&logoColor=white)
![Shopper](https://img.shields.io/badge/shopper-3.x-6366F1)
![License](https://img.shields.io/badge/license-MIT-blue)

Customer segmentation for [Shopper](https://shopperphp.com): group your customers and target
promotions by group, directly from the admin panel.

## Features

- **Customer groups** create, edit and toggle groups from the admin, under Customers.
- **Members management** browse a group's members in a slide-over, add customers through the
  shared customer picker, remove them one by one or in bulk, and jump to a customer profile.
- **Discount eligibility** reserve a promotion for one or more groups. The rule plugs into
  Shopper's discount eligibility registry, so validation, persistence and the admin form all
  work without patching the core.
- **API ready** groups carry a public ULID identifier, ready to be exposed through the Store API.

## Documentation

Full documentation, configuration and usage live on the Shopper website:
**[shopperphp.com/docs/addons/customer-groups](https://shopperphp.com/docs/addons/customer-groups)**.

## Requirements

- PHP `8.3+`
- Laravel `12.x` or `13.x`
- Shopper `3.x`

## Installation

Require the package:

```bash
composer require shopper/customer-groups
```

Run the migrations:

```bash
php artisan migrate
```

Register the addon in your `AppServiceProvider::register()`:

```php
use Shopper\CustomerGroups\CustomerGroupsAddon;
use Shopper\Facades\Shopper;

Shopper::addons([
    new CustomerGroupsAddon,
]);
```

A **Groups** entry appears in the sidebar under Customers, and a **Specific groups** option is
available in the discount eligibility settings.

## Extending

The Livewire components can be replaced with your own:

```php
Shopper::addons([
    (new CustomerGroupsAddon)->usingLivewireComponents([
        'customer-groups.index' => App\Livewire\CustomerGroupList::class,
    ]),
]);
```

## License

Customer Groups is open-sourced software licensed under the [MIT license](LICENSE.md).
