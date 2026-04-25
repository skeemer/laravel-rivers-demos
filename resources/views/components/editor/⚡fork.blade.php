<?php

use App\Rivers\Enums\MultiConditionLogic;
use App\Rivers\Enums\NameSortLogic;
use App\Rivers\Fork;
use App\Rivers\Forks\Conditions\MultiCondition;
use App\Rivers\Forks\Conditions\NameSortCondition;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Livewire\Attributes\Locked;
use Livewire\Component;
use LsvEu\Rivers\Cartography\Connection;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Models\River;

new class extends Component implements HasActions, HasForms {
    use InteractsWithActions, InteractsWithForms;

    public River $river;

    #[Locked]
    public string $elementId;

    public array $conditions = [];

    public function mount(): void
    {
        $map = $this->river->workingVersion->map;
        /** @var Fork $fork */
        $fork = $map->forks->get($this->elementId);
        $this->conditions = $fork->conditions
            ->mapWithKeys(fn($condition) => [
                $condition->id => [
                    'type' => get_class($condition),
                    'data' => $condition->toArray(),
                ],
            ])
            ->all();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Builder::make('conditions')->blocks($this->getConditionBlocks(true)),
        ]);
    }

    protected function getConditionBlocks(bool $withMulti = false): array
    {
        return [
            Builder\Block::make(NameSortCondition::class)
                ->label('Name Sort')
                ->schema([
                    Select::make('mode')
                        ->label('Comparison Type')
                        ->options(NameSortLogic::options()),
                    TextInput::make('letter')
                        ->label('Name starts with letter lower than this'),
                ]),
            ...when(
                condition: $withMulti,
                value: fn() => [
                    Builder\Block::make(MultiCondition::class)
                        ->components([
                            Select::make('mode')
                                ->label('Combination Type')
                                ->options(MultiConditionLogic::options()),
                            Builder::make('conditions')
                                ->label('Sub-conditions')
                                ->blocks($this->getConditionBlocks()),
                        ]),
                ],
                default: [],
            ),
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        // dd($data);
        $conditions = $data['conditions'];
        $map = $this->river->workingVersion->map;

        $fork = $map->forks->get($this->elementId);
        $fork->conditions->forget($fork->conditions->keys());
        foreach ($conditions as $id => $item) {
            $fork->conditions->push(new ($item['type'])(['id' => $id] + $item['data']));
        }
        $this->river->update(['map' => $map]);

        $this->dispatch('map-element-updated');
    }

    public function deleteAction(): \Filament\Actions\Action
    {
        return Filament\Actions\Action::make('delete')
            ->requiresConfirmation()
            ->action(function () {
                /** @var RiverMap $map */
                $map = $this->river->workingVersion->map;
                $element = $map->getElementById($this->elementId);
                $map->launches->forget($this->elementId);
                $map->connections
                    ->filter(fn(Connection $conn) => $conn->startId === $this->elementId || $conn->endId === $this->elementId)
                    ->each(fn(Connection $conn) => $map->connections->forget($conn->id));
                $this->river->update(['map' => $map]);

                $this->dispatch('map-element-deleted');
            });
    }
};
?>

<form class="flex flex-col gap-4 p-4" wire:submit.prevent="save">
    {{ $this->form }}
    <div class="flex justify-end gap-4">
        <button type="button" wire:click="addCondition">Add condition</button>
        <x-filament::button type="submit">Save</x-filament::button>
    </div>
</form>
