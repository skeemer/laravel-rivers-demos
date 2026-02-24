<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use LsvEu\Rivers\Cartography\Connection;
use LsvEu\Rivers\Cartography\Fork;
use LsvEu\Rivers\Models\River;
use Ramsey\Uuid\Uuid;

new class extends Component {
    public ?string $selectedId = null;

    public \LsvEu\Rivers\Cartography\RiverMap $map;

    public function mount(): void
    {
        $this->map = River::first()->map;
    }

    public function addLink(string $source, ?string $port, string $target): void
    {
        $isFork = $port && $this->map->getElementById($source) instanceof Fork;
        $this->map->connections->push(new Connection([
            'id' => null,
            'startId' => $source,
            'startConditionId' => $isFork && ! str_ends_with($port, 'else') ? $port : null,
            'endId' => $target,
        ]));
    }

    public function removeLink(string $id): void
    {
        $this->map->connections->forget($id);
    }

    public function updatePosition(string $id, int $x, int $y): void
    {
        $element = $this->map->getElementById($id);
        $element->x = $x;
        $element->y = $y;
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
        if ($type === 'fork') {
            $this->map->forks->push(new App\Rivers\Fork(['id' => 'blah', 'x' => $x, 'y' => $y]));
        }
        // TODO Add the other cell types
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
            ->map(fn (Connection $connection) => (object) [
                'id' => $connection->id,
                'source' => $connection->startId,
                'port' => $this->map->getElementById($connection->startId) instanceof Fork && ! $connection->startConditionId ?
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
        @foreach($this->cells as $cell)
            <div
                wire:key="{{ $cell->id }}-{{ count($cell->ports) }}"
                x-data='jjCell("{{ $cell->type }}", "{{ $cell->id }}", {{ $cell->x }}, {{ $cell->y }}, @json($cell->ports))'
                @moved.debounce="$wire.updatePosition(id, x, y)"
                {{-- x-effect="console.log($wire.id)" --}}
                x-show="this.selectedId === '{{ $cell->id }}'"
                wire:ignore
            ></div>
        @endforeach

        @foreach($this->links as $link)
            <div
                wire:key="{{ $link->id }}"
                x-data='jjLink("{{ $link->id }}", "{{ $link->source }}", "{{ $link->target }}", @json($link->port))'
            ></div>
        @endforeach
    </div>
    <div class="w-sm flex flex-col border-l-2 box-content border-gray-400">
        @if ($this->selectedId)
            <p>Selected: {{ $this->selection->id }}</p>
            <p>Position: ({{ $this->selection->x }}, {{ $this->selection->y }})</p>
            @if ($this->selection instanceof Fork)
                <button wire:click="addCondition">Add Condition</button>
            @else
                {{ get_class($this->selection) }}
            @endif
        @endif
    </div>
</div>
