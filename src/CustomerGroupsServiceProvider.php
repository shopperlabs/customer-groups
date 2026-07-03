<?php

declare(strict_types=1);

namespace Shopper\CustomerGroups;

use Shopper\Cart\Discounts\DiscountEligibilityManager;
use Shopper\Core\Traits\HasRegisterConfigAndMigrationFiles;
use Shopper\CustomerGroups\Discounts\GroupEligibilityField;
use Shopper\CustomerGroups\Discounts\GroupEligibilityRule;
use Shopper\Discounts\DiscountEligibilityFieldRegistry;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class CustomerGroupsServiceProvider extends PackageServiceProvider
{
    use HasRegisterConfigAndMigrationFiles;

    protected string $root = __DIR__.'/..';

    public function configurePackage(Package $package): void
    {
        $package
            ->name('shopper-customer-groups')
            ->hasTranslations()
            ->hasViews();
    }

    public function packageRegistered(): void
    {
        $this->registerDatabase();
    }

    public function packageBooted(): void
    {
        $this->app->make(DiscountEligibilityManager::class)->register(new GroupEligibilityRule);

        if (class_exists(DiscountEligibilityFieldRegistry::class)) {
            $this->app->make(DiscountEligibilityFieldRegistry::class)->register(
                GroupEligibilityRule::KEY,
                'groups',
                fn (): mixed => GroupEligibilityField::make(),
                fn (array $ids): array => GroupEligibilityField::resolveIds($ids),
            );
        }
    }
}
