<?php

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
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

    public array $data;

    #[Locked]
    public string $elementId;

    public ?string $label;

    public function mount(): void
    {
        $this->form->fill([
            'label' => $this->river->workingVersion->map->getElementById($this->elementId)->label,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('label')
                    ->label('Custom label')
                    ->nullable()

            ]);
    }

    public function delete(): void
    {
        /** @var RiverMap $map */
        $map = $this->river->workingVersion->map;
        $element = $map->getElementById($this->elementId);
        $map->launches->forget($this->elementId);
        $map->connections
            ->filter(fn(Connection $conn) => $conn->startId === $this->elementId || $conn->endId === $this->elementId)
            ->each(fn(Connection $conn) => $map->connections->forget($conn->id));
        $this->river->update(['map' => $map]);

        $this->dispatch('map-element-deleted');
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->color('danger')
            ->requiresConfirmation();
    }

    public function save(): void
    {
        $data = $this->form->validate();

        /** @var RiverMap $map */
        $map = $this->river->workingVersion->map;
        $element = $map->getElementById($this->elementId);
        $element->label = $data['label'];
        $this->river->update(['map' => $map]);
        $this->dispatch('map-element-updated');
    }
};
?>
<div>
    <form wire:submit.prevent="save" class="flex flex-col gap-4">
        {{ $this->form }}
        <div class="flex justify-end gap-4">
            {{ $this->deleteAction }}
            <x-filament::button type="submit">Save</x-filament::button>
        </div>
    </form>
    <x-filament-actions::modals/>
</div>
