<?php

use Livewire\Attributes\Locked;
use Livewire\Component;
use LsvEu\Rivers\Models\River;

new class extends Component {
    public River $river;

    #[Locked]
    public string $elementId;

    public function mount(): void
    {
    }

    public function addCondition(): void
    {
        /** @var \LsvEu\Rivers\Cartography\RiverMap $map */
        $map = $this->river->workingVersion->map;
        $element = $map->getElementById($this->elementId);
        $element->conditions->push(new \App\Rivers\Forks\Conditions\NameSortCondition([]));
        $this->river->update(['map' => $map]);

        $this->dispatch('map-element-updated');
    }
};
?>

<div>
    <button wire:click="addCondition">Add condition</button>
</div>
