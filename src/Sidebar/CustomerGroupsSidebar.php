<?php

declare(strict_types=1);

namespace Shopper\CustomerGroups\Sidebar;

use Shopper\Sidebar\AbstractAdminSidebar;
use Shopper\Sidebar\Contracts\Builder\Group;
use Shopper\Sidebar\Contracts\Builder\Item;
use Shopper\Sidebar\Contracts\Builder\Menu;

final class CustomerGroupsSidebar extends AbstractAdminSidebar
{
    public function extendWith(Menu $menu): Menu
    {
        $menu->group(__('shopper::pages/customers.menu'), function (Group $group): void {
            $group->item(__('shopper::pages/customers.menu'), function (Item $item): void {
                $item->item(__('shopper-customer-groups::messages.menu'), function (Item $child): void {
                    $child->weight(1);
                    $child->setAuthorized($this->user->hasPermissionTo('customers.browse'));
                    $child->route('shopper.customers.groups');
                    $child->useSpa();
                });
            });
        });

        return $menu;
    }
}
