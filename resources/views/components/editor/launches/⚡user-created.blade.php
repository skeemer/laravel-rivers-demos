<?php

use App\Rivers\Launches\ModelCreated;
use Livewire\Attributes\Locked;
use Livewire\Component;
use LsvEu\Rivers\Cartography\Connection;
use LsvEu\Rivers\Models\River;

new class extends Component {
    public River $river;

    #[Locked]
    public string $elementId;

    public ?string $label;

    public function mount(): void
    {
        $this->label = $this->river->workingVersion->map->getElementById($this->elementId)->label;
    }

    public function delete(): void
    {
        /** @var \LsvEu\Rivers\Cartography\RiverMap $map */
        $map = $this->river->workingVersion->map;
        $element = $map->getElementById($this->elementId);
        $map->launches->forget($this->elementId);
        $map->connections
            ->filter(fn (Connection $conn) => $conn->startId === $this->elementId || $conn->endId === $this->elementId)
            ->each(fn (Connection $conn) => $map->connections->forget($conn->id));
        $this->river->update(['map' => $map]);

        $this->dispatch('map-element-deleted');
    }

    public function updatedLabel(): void
    {
        /** @var \LsvEu\Rivers\Cartography\RiverMap $map */
        $map = $this->river->workingVersion->map;
        $element = $map->getElementById($this->elementId);
        $element->label = $this->label;
        $this->river->update(['map' => $map]);

        $this->dispatch('map-element-updated');
    }
};
?>

<div>
    <button wire:click="delete">Delete Me</button>
    <label>
        Label
        <input type="text" wire:model.live.debounce="label">
    </label>
</div>
