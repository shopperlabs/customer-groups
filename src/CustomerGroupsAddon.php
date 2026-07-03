<?php

declare(strict_types=1);

namespace Shopper\CustomerGroups;

use Illuminate\Support\Facades\Route;
use Shopper\Addon\BaseAddon;
use Shopper\CustomerGroups\Livewire\GroupDetail;
use Shopper\CustomerGroups\Livewire\GroupList;
use Shopper\CustomerGroups\Sidebar\CustomerGroupsSidebar;
use Shopper\ShopperPanel;

final class CustomerGroupsAddon extends BaseAddon
{
    /** @var array<string, class-string> */
    private array $componentOverrides = [];

    public function getId(): string
    {
        return 'customer-groups';
    }

    /**
     * @param  array<string, class-string>  $components
     */
    public function usingLivewireComponents(array $components): static
    {
        $this->componentOverrides = array_merge($this->componentOverrides, $components);

        return $this;
    }

    public function register(ShopperPanel $panel): void
    {
        $components = $this->livewireComponents();

        $panel
            ->addonLivewireComponents($components)
            ->addonSidebar(CustomerGroupsSidebar::class)
            ->addonRoutes(function () use ($components): void {
                Route::prefix('customers')
                    ->as('customers.')
                    ->group(function () use ($components): void {
                        Route::get('/groups', $components['customer-groups.index'])->name('groups');
                    });
            });
    }

    /**
     * @return array<string, class-string>
     */
    private function livewireComponents(): array
    {
        $components = [
            'customer-groups.index' => GroupList::class,
            'customer-groups.detail' => GroupDetail::class,
        ];

        return array_merge($components, array_intersect_key($this->componentOverrides, $components));
    }
}
