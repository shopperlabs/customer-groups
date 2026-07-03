<?php

declare(strict_types=1);

namespace Shopper\CustomerGroups\Livewire;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Laravelcm\LivewireSlideOvers\SlideOverComponent;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Mckenziearts\Icons\Untitledui\Enums\Untitledui;
use Shopper\CustomerGroups\Models\CustomerGroup;
use Shopper\Traits\HandlesAuthorizationExceptions;

class GroupDetail extends SlideOverComponent implements HasActions, HasSchemas, HasTable
{
    use HandlesAuthorizationExceptions;
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    #[Locked]
    public CustomerGroup $group;

    public static function panelMaxWidth(): string
    {
        return '3xl';
    }

    public static function destroyOnClose(): bool
    {
        return true;
    }

    public function mount(?CustomerGroup $group = null): void
    {
        $this->authorize('customers.browse');

        $this->group = $group;
    }

    /**
     * @return array<int>
     */
    public function memberIds(): array
    {
        return $this->group->customers()->pluck(
            $this->group->customers()
                ->getRelated()
                ->getQualifiedKeyName()
        )->all();
    }

    #[On('panelClosed')]
    public function refreshMembers(string $name): void {}

    /**
     * @param  array<int>  $ids
     */
    #[On('customer-groups.members.selected')]
    public function addMembers(array $ids): void
    {
        $this->authorize('customers.edit');

        $this->group->customers()->syncWithoutDetaching($ids);

        $this->dispatch('customer-groups.members.updated');

        Notification::make()
            ->title(__('shopper-customer-groups::messages.members_added'))
            ->success()
            ->send();
    }

    public function table(Table $table): Table
    {
        $members = $this->group->customers();

        return $table
            ->query($members->getQuery()->orderByDesc($members->getRelated()->getQualifiedCreatedAtColumn()))
            ->columns([
                TextColumn::make('first_name')
                    ->label(__('shopper::forms.label.full_name'))
                    ->formatStateUsing(fn (Model $record): string => $record->getAttribute('full_name'))
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('email')
                    ->label(__('shopper::forms.label.email'))
                    ->searchable(),
            ])
            ->recordActions([
                Action::make('profile')
                    ->label(__('shopper-customer-groups::messages.members_profile'))
                    ->icon(Untitledui::ArrowUpRight)
                    ->iconButton()
                    ->color('gray')
                    ->authorize('customers.browse')
                    ->url(fn (Model $record): string => route('shopper.customers.show', $record)),
                Action::make('remove')
                    ->label(__('shopper-customer-groups::messages.members_remove'))
                    ->icon(Untitledui::UserX02)
                    ->iconButton()
                    ->color('danger')
                    ->authorize('customers.edit')
                    ->requiresConfirmation()
                    ->action(function (Model $record): void {
                        $this->group->customers()->detach($record->getKey());

                        $this->resetPage();

                        $this->dispatch('customer-groups.members.updated');
                    }),
            ])
            ->selectable()
            ->groupedBulkActions([
                BulkAction::make('removeSelected')
                    ->label(__('shopper-customer-groups::messages.members_remove'))
                    ->icon(Untitledui::UserX02)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->authorize('customers.edit')
                    ->visible(shopper()->auth()->user()->can('customers.edit'))
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records): void {
                        $this->group->customers()->detach($records->modelKeys());

                        $this->resetPage();

                        $this->dispatch('customer-groups.members.updated');

                        Notification::make()
                            ->title(__('shopper-customer-groups::messages.members_removed'))
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateIcon(Untitledui::Users02)
            ->emptyStateDescription(__('shopper-customer-groups::messages.members_empty'));
    }

    public function render(): View
    {
        return view('shopper-customer-groups::livewire.slide-overs.group-members');
    }
}
