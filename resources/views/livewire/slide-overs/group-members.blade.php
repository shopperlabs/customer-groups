<x-shopper::slideover-card>
    <div class="h-0 flex-1 overflow-y-auto py-6">
        <header class="px-4 sm:px-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-medium text-sh-fg">
                        {{ $group->name }}
                    </h2>
                    @if ($group->description)
                        <p class="text-sh-fg-secondary mt-1 text-sm">
                            {{ $group->description }}
                        </p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    @can('customers.edit')
                        <x-filament::button
                            type="button"
                            size="sm"
                            icon="untitledui-user-plus-02"
                            wire:click="$dispatch('openPanel', {
                                component: 'shopper-slide-overs.customers-picker',
                                arguments: {
                                    exceptIds: {{ json_encode($this->memberIds()) }},
                                    ability: 'customers.edit',
                                    event: 'customer-groups.members.selected',
                                    title: {{ \Illuminate\Support\Js::from(__('shopper-customer-groups::messages.members_add')) }},
                                    description: {{ \Illuminate\Support\Js::from(__('shopper-customer-groups::messages.members_add_description')) }},
                                },
                            })"
                        >
                            {{ __('shopper-customer-groups::messages.members_add') }}
                        </x-filament::button>
                    @endcan
                    <x-livewire-slide-over::close-icon />
                </div>
            </div>
        </header>
        <div class="mt-6 flex-1 px-4 sm:px-6">
            {{ $this->table }}
        </div>
    </div>
</x-shopper::slideover-card>
