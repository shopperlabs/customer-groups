<?php

declare(strict_types=1);

namespace Shopper\CustomerGroups\Discounts;

use Illuminate\Support\Facades\DB;
use Shopper\Cart\Contracts\DiscountEligibilityRule;
use Shopper\Cart\Discounts\DiscountValidationResult;
use Shopper\Cart\Pipelines\CartPipelineContext;
use Shopper\Core\Enum\DiscountCondition;
use Shopper\Core\Models\Discount;
use Shopper\CustomerGroups\Models\CustomerGroup;

final class GroupEligibilityRule implements DiscountEligibilityRule
{
    public const string KEY = 'group';

    public function key(): string
    {
        return self::KEY;
    }

    public function label(): string
    {
        return __('shopper-customer-groups::messages.eligibility.label');
    }

    public function description(): string
    {
        return __('shopper-customer-groups::messages.eligibility.description');
    }

    public function discountableType(): string
    {
        return (new CustomerGroup)->getMorphClass();
    }

    public function passes(Discount $discount, CartPipelineContext $context): DiscountValidationResult
    {
        if (! $context->cart->customer_id) {
            return new DiscountValidationResult(false, __('shopper-cart::messages.discount.requires_login'));
        }

        $targetGroupIds = $discount->items()
            ->where('condition', DiscountCondition::Eligibility)
            ->where('discountable_type', $this->discountableType())
            ->pluck('discountable_id');

        if ($targetGroupIds->isEmpty()) {
            return new DiscountValidationResult(false, __('shopper-customer-groups::messages.eligibility.not_eligible'));
        }

        $isMember = DB::table(shopper_table('customer_group_user'))
            ->where('user_id', $context->cart->customer_id)
            ->whereIn('customer_group_id', $targetGroupIds)
            ->exists();

        if (! $isMember) {
            return new DiscountValidationResult(false, __('shopper-customer-groups::messages.eligibility.not_eligible'));
        }

        return new DiscountValidationResult(true);
    }
}
