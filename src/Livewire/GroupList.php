<?php

declare(strict_types=1);

namespace Shopper\CustomerGroups\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Mckenziearts\Icons\Untitledui\Enums\Untitledui;
use Shopper\CustomerGroups\Models\CustomerGroup;
use Shopper\Livewire\Pages\AbstractPageComponent;
use Shopper\Traits\HandlesAuthorizationExceptions;

class GroupList extends AbstractPageComponent implements HasActions, HasSchemas, HasTable
{
    use HandlesAuthorizationExceptions;
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function mount(): void
    {
        $this->authorize('customers.browse');
    }

    #[On('customer-groups.members.updated')]
    public function refreshGroups(): void {}

    public function createGroupAction(): Action
    {
        return Action::make('createGroup')
            ->label(__('shopper-customer-groups::messages.actions.add'))
            ->authorize('customers.create')
            ->modalWidth(Width::Large)
            ->schema($this->formSchema())
            ->action(function (array $data): void {
                CustomerGroup::query()->create($data);

                Notification::make()
                    ->title(__('shopper-customer-groups::messages.created'))
                    ->success()
                    ->send();
            });
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(CustomerGroup::query()->withCount('customers')->latest())
            ->columns([
                TextColumn::make('name')
                    ->label(__('shopper::forms.label.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customers_count')
                    ->label(__('shopper-customer-groups::messages.members'))
                    ->badge()
                    ->alignCenter(),
                ToggleColumn::make('is_active')
                    ->label(__('shopper::forms.label.active'))
                    ->beforeStateUpdated(fn (): mixed => $this->authorize('customers.edit')),
                TextColumn::make('updated_at')
                    ->label(__('shopper::forms.label.updated_at'))
                    ->date(),
            ])
            ->recordActions([
                Action::make('members')
                    ->label(__('shopper-customer-groups::messages.members_manage'))
                    ->icon(Untitledui::Users02)
                    ->iconButton()
                    ->color('gray')
                    ->authorize('customers.browse')
                    ->action(fn (CustomerGroup $record) => $this->dispatch(
                        'openPanel',
                        'shopper-customer-groups.detail',
                        ['group' => $record->getKey()],
                    )),
                EditAction::make('edit')
                    ->label(__('shopper::forms.actions.edit'))
                    ->icon(Untitledui::Edit03)
                    ->iconButton()
                    ->authorize('customers.edit')
                    ->modalWidth(Width::Large)
                    ->schema($this->formSchema())
                    ->successNotificationTitle(__('shopper-customer-groups::messages.updated')),
                DeleteAction::make('delete')
                    ->label(__('shopper::forms.actions.delete'))
                    ->icon(Untitledui::Trash03)
                    ->iconButton()
                    ->authorize('customers.delete')
                    ->after(fn () => $this->resetPage()),
            ])
            ->selectable()
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->label(__('shopper::forms.actions.delete'))
                    ->icon(Untitledui::Trash03)
                    ->requiresConfirmation()
                    ->authorize('customers.delete')
                    ->visible(shopper()->auth()->user()->can('customers.delete'))
                    ->deselectRecordsAfterCompletion()
                    ->after(fn () => $this->resetPage()),
            ])
            ->emptyState(view('shopper-customer-groups::livewire.tables.empty-states.groups'));
    }

    public function render(): View
    {
        return view('shopper-customer-groups::livewire.pages.group-list')
            ->title(__('shopper-customer-groups::messages.title'));
    }

    /**
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    protected function formSchema(): array
    {
        return [
            TextInput::make('name')
                ->label(__('shopper::forms.label.name'))
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, ?string $state): mixed => $set('slug', Str::slug((string) $state))),
            Hidden::make('slug'),
            Textarea::make('description')
                ->label(__('shopper::forms.label.description'))
                ->rows(2),
            Toggle::make('is_active')
                ->label(__('shopper::forms.label.active'))
                ->default(true),
        ];
    }
}
