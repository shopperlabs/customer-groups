<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Shopper\CustomerGroups\Models\CustomerGroup;

it('links customers to a group', function (): void {
    $group = CustomerGroup::factory()->create();

    $group->customers()->attach([createCustomer(), createCustomer('john@example.com')]);

    expect($group->customers()->count())->toBe(2)
        ->and($group->customers()->first()->pivot->created_at)->not->toBeNull();
});

it('scopes the query to active groups', function (): void {
    $active = CustomerGroup::factory()->create();
    CustomerGroup::factory()->inactive()->create();

    expect(CustomerGroup::query()->active()->pluck('id')->all())->toBe([$active->id]);
});

it('gives each group a public id', function (): void {
    expect(CustomerGroup::factory()->create()->public_id)->not->toBeNull();
});

it('removes memberships when a group is deleted', function (): void {
    $group = CustomerGroup::factory()->create();
    $group->customers()->attach(createCustomer());

    $group->delete();

    expect(DB::table(shopper_table('customer_group_user'))->count())->toBe(0);
});
