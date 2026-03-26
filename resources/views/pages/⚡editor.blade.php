<?php

use App\Models\User;
use App\Rivers\Launches\ModelCreated;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use LsvEu\Rivers\Cartography\Connection;
use LsvEu\Rivers\Cartography\Fork;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Models\River;

new class extends Component
{
    public River $river;

    public array $cellElements;

    public array $cellLinks;

    #[Locked]
    public RiverMap $map;

    public ?string $selectedId = null;

    //
    // Livecycle hooks
    //

    public function mount(): void
    {
        $this->river = River::first();
        $this->map = $this->river->workingVersion->map;
    }

    public function rendering(): void
    {
        $this->cellElements = $this->map->getAllRiverElements()
            ->map(fn ($fork) => $fork->convertToCell())
            ->filter(fn ($item) => $item->type)
            ->keyBy('id')
            ->all();

        $this->cellLinks = $this->map->connections
            ->map(fn (Connection $connection) => (object) [
                'id' => $connection->id,
                'source' => $connection->startId,
                'port' => $this->map->getElementById($connection->startId) instanceof Fork && ! $connection->startConditionId ?
                    "$connection->startId-else" :
                    $connection->startConditionId,
                'target' => $connection->endId,
            ])
            ->keyBy('id')
            ->all();
    }

    //
    // Event listeners
    //

    #[On('map-element-deleted')]
    public function elementDeleted(): void
    {
        $this->selectedId = null;
        $this->map = $this->river->workingVersion()->first()->map;
    }

    #[On('map-element-updated')]
    public function elementUpdated(): void
    {
        $this->map = $this->river->workingVersion()->first()->map;
    }

    //
    // Other methods
    //

    public function addCell(string $type, int $x, int $y): void
    {
        $map = $this->river->workingVersion->map;
        if ($type === 'fork') {
            $map->forks->push($element = new App\Rivers\Fork(['x' => $x, 'y' => $y]));
        } elseif ($type === 'launch') {
            $map->launches->push($element = new ModelCreated([
                'x' => $x,
                'y' => $y,
                'class' => User::class,
                'raftClass' => $map->raftClass,
            ]));
        } else {
            // TODO Add the other cell types
            return;
        }

        $this->river->update(['map' => $map]);
        $this->map = $this->river->workingVersion->map;
        $this->selectedId = $element->id;
    }

    public function addLink(string $source, ?string $port, string $target): void
    {
        $map = $this->river->workingVersion->map;
        $isFork = $port && $map->getElementById($source) instanceof Fork;
        $map->connections->push(new Connection([
            'id' => null,
            'startId' => $source,
            'startConditionId' => $isFork && ! str_ends_with($port, 'else') ? $port : null,
            'endId' => $target,
        ]));
        $this->river->update(['map' => $map]);
        $this->map = $map;
    }

    public function removeLink(string $id): void
    {
        $map = $this->river->workingVersion->map;
        $map->connections->forget($id);
        $this->river->update(['map' => $map]);
        $this->map = $map;
    }

    public function selectId(string $id): void
    {
        $this->selectedId = $id;
    }

    public function updatePosition(string $id, int $x, int $y): void
    {
        $map = $this->river->workingVersion->map;
        $element = $map->getElementById($id);
        $element->x = $x;
        $element->y = $y;
        $this->river->update(['map' => $map]);
        $this->map = $this->river->workingVersion->map;
    }

    //
    // Computed properties
    //

    #[Computed]
    public function selection(): ?object
    {
        return $this->map->getElementById($this->selectedId);
    }
};
?>
<div x-data='jointJs("container-{{ $this->getId() }}")' class="flex h-screen">
    <div class="w-full h-screen relative" wire:ignore>
        <div class="w-full h-screen" id="container-{{ $this->getId() }}"></div>
        <div class="absolute left-6 inset-y-6 w-[202px] h-[252px] border rounded-xl overflow-hidden">
            <div id="container-{{ $this->getId() }}-palette"></div>
        </div>
        <div class="absolute inset-0" x-show="draggingNew">
            <div id="container-{{ $this->getId() }}-fly"></div>
        </div>
    </div>
    <div class="w-sm flex flex-col border-l-2 box-content border-gray-400">
        @if ($this->selectedId)
            @if ($this->selection instanceof App\Rivers\Launches\ModelCreated)
                <livewire:editor.launches.user-created
                    :$river
                    :element-id="$this->selectedId"
                    wire:key="s_{{ $this->selectedId }}"
                />
            @elseif ($this->selection instanceof Fork)
                <livewire:editor.fork :$river :element-id="$this->selectedId" wire:key="s_{{ $this->selectedId }}"/>
            @else
                <p>Selected: {{ $this->selection->id }}</p>
                <p>Position: ({{ $this->selection->x }}, {{ $this->selection->y }})</p>
                <p>Class: {{ get_class($this->selection) }}</p>
            @endif
        @endif
    </div>
</div>
