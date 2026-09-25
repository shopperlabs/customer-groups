<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

const PROVIDER_STUB = <<<'PHP'
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
    }
}
PHP;

function providerPath(): string
{
    return app_path('Providers/AppServiceProvider.php');
}

beforeEach(function (): void {
    $this->originalProvider = File::exists(providerPath()) ? File::get(providerPath()) : null;
    File::ensureDirectoryExists(dirname(providerPath()));
});

afterEach(function (): void {
    if ($this->originalProvider !== null) {
        File::put(providerPath(), $this->originalProvider);
    }
});

it('registers the addon inside a provider without an addons block', function (): void {
    File::put(providerPath(), PROVIDER_STUB);

    $this->artisan('shopper:customer-groups:install')->assertSuccessful();

    $contents = File::get(providerPath());

    expect($contents)->toContain('use Shopper\Facades\Shopper;')
        ->and($contents)->toContain('use Shopper\CustomerGroups\CustomerGroupsAddon;')
        ->and($contents)->toContain("Shopper::addons([\n            new CustomerGroupsAddon,\n        ]);");
});

it('appends the addon to an existing addons block and stays idempotent', function (): void {
    File::put(providerPath(), str_replace(
        "public function register(): void\n    {\n    }",
        "public function register(): void\n    {\n        Shopper::addons([\n            new PosAddon,\n        ]);\n    }",
        PROVIDER_STUB,
    ));

    $this->artisan('shopper:customer-groups:install')->assertSuccessful();
    $after = File::get(providerPath());

    $this->artisan('shopper:customer-groups:install')->assertSuccessful();

    expect($after)->toContain('new CustomerGroupsAddon,')
        ->and($after)->toContain('new PosAddon,')
        ->and(File::get(providerPath()))->toBe($after);
});
