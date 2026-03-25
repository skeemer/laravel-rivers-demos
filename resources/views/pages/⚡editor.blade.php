<?php

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use LsvEu\Rivers\Cartography\Connection;
use LsvEu\Rivers\Cartography\Fork;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Models\River;
use Ramsey\Uuid\Uuid;

new class extends Component {
    public River $river;

    public ?string $selectedId = null;

    public array $cellElements;

    public $listeners = ['map-updated' => '$refresh'];

    #[Locked]
    public RiverMap $map;

    public function mount(): void
    {
        $this->river = River::first();
        $this->map = $this->river->workingVersion->map;
    }

    public function hydrating(): void
    {
    }

    public function rendering(): void
    {
        $this->cellElements = Arr::keyBy($this->cells, 'id');
    }

    public function addLink(string $source, ?string $port, string $target): void
    {
        $isFork = $port && $this->map->getElementById($source) instanceof Fork;
        $this->map->connections->push(new Connection([
            'id' => null,
            'startId' => $source,
            'startConditionId' => $isFork && !str_ends_with($port, 'else') ? $port : null,
            'endId' => $target,
        ]));
    }

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

    public function removeLink(string $id): void
    {
        $this->map->connections->forget($id);
    }

    public function updatePosition(string $id, int $x, int $y): void
    {
        $map = $this->river->workingVersion->map;
        $element = $map->getElementById($id);
        $element->x = $x;
        $element->y = $y;
        $this->river->update(['map' => $map]);
        $this->map = $this->river->workingVersion->map;
        // $this->skipRender();
    }

    public function addCondition(): void
    {
        /** @var Fork $fork */
        $fork = $this->selection;
        $fork->conditions->push(new \App\Rivers\Forks\Conditions\NameSortCondition([
            'id' => Str::random(5),
        ]));
    }

    public function newCell(string $type, int $x, int $y): void
    {
        $map = $this->river->workingVersion->map;
        if ($type === 'fork') {
            $map->forks->push($element = new App\Rivers\Fork(['x' => $x, 'y' => $y]));
        } elseif ($type === 'launch') {
            $map->launches->push($element = new App\Rivers\Launches\ModelCreated([
                'x' => $x,
                'y' => $y,
                'class' => \App\Models\User::class,
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

    public function selectId(string $id): void
    {
        $this->selectedId = $id;
    }

    #[Computed]
    public function cells(): array
    {
        return $this->map->getAllRiverElements()
            ->map(fn($fork) => $fork->convertToCell())
            ->filter(fn($item) => $item->type)
            ->values()
            ->all();
    }

    #[Computed]
    public function links(): array
    {
        return $this->map->connections
            ->map(fn(Connection $connection) => (object)[
                'id' => $connection->id,
                'source' => $connection->startId,
                'port' => $this->map->getElementById($connection->startId) instanceof Fork && !$connection->startConditionId ?
                    "$connection->startId-else" :
                    $connection->startConditionId,
                'target' => $connection->endId,
            ])
            ->all();
    }

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
    <div class="hidden">
        @foreach($this->links as $link)
            <div
                wire:key="{{ $link->id }}"
                x-data='jjLink("{{ $link->id }}", "{{ $link->source }}", "{{ $link->target }}", @json($link->port))'
            ></div>
        @endforeach
    </div>
    <div class="w-sm flex flex-col border-l-2 box-content border-gray-400">
        @if ($this->selectedId)
            @if ($this->selection instanceof App\Rivers\Launches\ModelCreated)
                <livewire:editor.launches.user-created :$river :element-id="$this->selectedId"/>
            @elseif ($this->selection instanceof Fork)
                <livewire:editor.fork :$river :element-id="$this->selectedId"/>
            @else
                <p>Selected: {{ $this->selection->id }}</p>
                <p>Position: ({{ $this->selection->x }}, {{ $this->selection->y }})</p>
                <p>Class: {{ get_class($this->selection) }}</p>

            @endif
        @endif
    </div>
</div>
