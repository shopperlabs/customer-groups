<?php

declare(strict_types=1);

namespace Shopper\CustomerGroups\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Shopper\Cart\CartServiceProvider;
use Shopper\Core\CoreServiceProvider;
use Shopper\CustomerGroups\CustomerGroupsServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            PermissionServiceProvider::class,
            CoreServiceProvider::class,
            CartServiceProvider::class,
            CustomerGroupsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing.foreign_key_constraints', true);

        $app->afterResolving('migrator', function ($migrator): void {
            $migrator->path(\Orchestra\Testbench\default_migration_path());
        });
    }
}
