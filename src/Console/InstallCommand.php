<?php

declare(strict_types=1);

namespace Shopper\CustomerGroups\Console;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Attribute\AsCommand;

use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

#[AsCommand(name: 'shopper:customer-groups:install')]
final class InstallCommand extends Command
{
    protected $signature = 'shopper:customer-groups:install';

    protected $description = 'Install Shopper Customer Groups: run migrations and register the addon';

    public function handle(): int
    {
        intro('Installing Shopper Customer Groups');

        $this->task('Running database migrations', function (): void {
            $this->callSilently('migrate', ['--force' => true]);
        });

        $this->registerAddon();

        outro('Shopper Customer Groups is installed. Manage groups in Customers → Groups.');

        return self::SUCCESS;
    }

    private function task(string $title, Closure $callback): void
    {
        spin(callback: $callback, message: $title);

        $width = 52;
        $dots = str_repeat('.', max(1, $width - mb_strlen($title)));

        $this->output->writeln("  <fg=#94A3B8>{$title}</> <fg=#334155>{$dots}</> <fg=#22C55E>✓</>");
    }

    private function registerAddon(): void
    {
        $path = app_path('Providers/AppServiceProvider.php');

        if (! File::exists($path)) {
            $this->manualRegistrationNotice();

            return;
        }

        $contents = File::get($path);

        if (str_contains($contents, 'CustomerGroupsAddon')) {
            note('CustomerGroupsAddon is already registered in AppServiceProvider.');

            return;
        }

        $updated = $this->addImports($contents);
        $updated = $this->addAddonRegistration($updated);

        if ($updated === null) {
            $this->manualRegistrationNotice();

            return;
        }

        $this->task('Registering CustomerGroupsAddon in AppServiceProvider', function () use ($path, $updated): void {
            File::put($path, $updated);
        });
    }

    private function addImports(string $contents): string
    {
        $imports = '';

        if (! str_contains($contents, 'use Shopper\Facades\Shopper;')) {
            $imports .= "use Shopper\\Facades\\Shopper;\n";
        }

        if (! str_contains($contents, 'use Shopper\CustomerGroups\CustomerGroupsAddon;')) {
            $imports .= "use Shopper\\CustomerGroups\\CustomerGroupsAddon;\n";
        }

        if ($imports === '') {
            return $contents;
        }

        return (string) preg_replace('/^(namespace [^;]+;\n\n?)/m', '$1'.$imports, $contents, 1);
    }

    private function addAddonRegistration(string $contents): ?string
    {
        if (str_contains($contents, 'Shopper::addons([')) {
            return str_replace(
                'Shopper::addons([',
                "Shopper::addons([\n            new CustomerGroupsAddon,",
                $contents,
            );
        }

        $patched = preg_replace(
            '/(public function register\(\): void\s*\{)/',
            "$1\n        Shopper::addons([\n            new CustomerGroupsAddon,\n        ]);\n",
            $contents,
            1,
            $count,
        );

        return $count === 1 ? $patched : null;
    }

    private function manualRegistrationNotice(): void
    {
        warning('Could not update AppServiceProvider automatically. Register the addon manually:');
        note('Shopper::addons([new \Shopper\CustomerGroups\CustomerGroupsAddon]);');
    }
}
