<div>
    <x-shopper::container class="space-y-8 py-5">
        <x-shopper::heading :title="__('shopper-customer-groups::messages.title')" :description="__('shopper-customer-groups::messages.description')">
            <x-slot name="action">
                {{ $this->createGroupAction }}
            </x-slot>
        </x-shopper::heading>

        {{ $this->table }}
    </x-shopper::container>

    <x-filament-actions::modals />
</div>
