<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Shopper\Cart\Discounts\DiscountEligibilityManager;
use Shopper\Cart\Discounts\SyncDiscountEligibilityAction;
use Shopper\Cart\Models\Cart;
use Shopper\Cart\Pipelines\CartPipelineContext;
use Shopper\Core\Models\Discount;
use Shopper\CustomerGroups\Discounts\GroupEligibilityRule;
use Shopper\CustomerGroups\Models\CustomerGroup;

beforeEach(function (): void {
    $this->group = CustomerGroup::factory()->create();
    $this->discount = Discount::factory()->create(['eligibility' => GroupEligibilityRule::KEY]);

    resolve(SyncDiscountEligibilityAction::class)->execute($this->discount, GroupEligibilityRule::KEY, [$this->group->id]);
});

function checkEligibility(Discount $discount, ?int $customerId): bool
{
    $cart = Cart::factory()->create(['customer_id' => $customerId]);

    return resolve(DiscountEligibilityManager::class)
        ->for(GroupEligibilityRule::KEY)
        ->passes($discount, new CartPipelineContext($cart))
        ->valid;
}

it('registers the group rule with the discount eligibility manager', function (): void {
    expect(resolve(DiscountEligibilityManager::class)->for(GroupEligibilityRule::KEY))
        ->toBeInstanceOf(GroupEligibilityRule::class);
});

it('targets customer groups through the discount items', function (): void {
    expect($this->discount->items()->pluck('discountable_type')->all())
        ->toBe([(new CustomerGroup)->getMorphClass()]);
});

it('accepts a customer who belongs to a targeted group', function (): void {
    $customerId = createCustomer();
    $this->group->customers()->attach($customerId);

    expect(checkEligibility($this->discount, $customerId))->toBeTrue();
});

it('rejects a customer outside the targeted groups', function (): void {
    $customerId = createCustomer();
    CustomerGroup::factory()->create()->customers()->attach($customerId);

    expect(checkEligibility($this->discount, $customerId))->toBeFalse();
});

it('rejects a guest cart and asks the customer to sign in', function (): void {
    $cart = Cart::factory()->create();

    $result = resolve(GroupEligibilityRule::class)->passes($this->discount, new CartPipelineContext($cart));

    expect($result->valid)->toBeFalse()
        ->and($result->failureReason)->toBe(__('shopper-cart::messages.discount.requires_login'));
});

it('rejects everyone when the discount targets no group', function (): void {
    $customerId = createCustomer();
    $this->group->customers()->attach($customerId);

    resolve(SyncDiscountEligibilityAction::class)->execute($this->discount, GroupEligibilityRule::KEY, []);

    expect(checkEligibility($this->discount, $customerId))->toBeFalse();
});

it('stops accepting a customer once removed from the group', function (): void {
    $customerId = createCustomer();
    $this->group->customers()->attach($customerId);
    $this->group->customers()->detach($customerId);

    expect(checkEligibility($this->discount, $customerId))->toBeFalse()
        ->and(DB::table(shopper_table('customer_group_user'))->count())->toBe(0);
});
