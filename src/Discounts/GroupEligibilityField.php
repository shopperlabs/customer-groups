<?php

declare(strict_types=1);

namespace Shopper\CustomerGroups\Discounts;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Shopper\CustomerGroups\Models\CustomerGroup;

final class GroupEligibilityField
{
    public static function make(): Component
    {
        return Select::make('groups')
            ->label(__('shopper-customer-groups::messages.eligibility.field_label'))
            ->multiple()
            ->native(false)
            ->options(fn (): array => CustomerGroup::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all())
            ->required()
            ->visible(fn (Get $get): bool => $get('eligibility') === GroupEligibilityRule::KEY);
    }

    /**
     * @param  array<int>  $ids
     * @return array<int>
     */
    public static function resolveIds(array $ids): array
    {
        return CustomerGroup::query()
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }
}
