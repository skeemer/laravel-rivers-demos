<?php

use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public ?string $selectedId = null;

    public array $cells;

    public array $links;

    public function mount(): void
    {
        $this->cells = [
            (object)[
                'id' => 'launch1',
                'x' => -200,
                'y' => -200,
                'type'=> 'launch',
            ],
            (object)[
                'id' => 'rapid1',
                'x' => -200,
                'y' => 200,
                'type' => 'rapid',
            ],
            (object)[
                'id' => 'rapid2',
                'x' => 200,
                'y' => -200,
                'type' => 'rapid',
            ],
            (object)[
                'id' => 'rapid3',
                'x' => 0,
                'y' => 0,
                'type' => 'rapid',
            ],
        ];

        $this->links = [
            (object) [
                'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'source' => 'rapid1',
                'target' => 'rapid2',
            ],
        ];
    }

    public function addLink(string $source, string $target): void
    {
        $this->links[] = (object) [
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'source' => $source,
            'target' => $target,
        ];
    }

    public function removeLink(string $id): void
    {
        foreach($this->links as $key => $link) {
            if ($link->id == $id) {
                unset($this->links[$key]);
                break;
            }
        }
    }

    public function updatePosition(string $id, int $x, int $y): void
    {
        foreach($this->cells as &$cell) {
            if ($cell->id == $id) {
                $cell->x = $x;
                $cell->y = $y;
                break;
            }
        }
        $this->skipRender();
    }

    public function newCell(string $type, int $x, int $y): void
    {
        $count = collect($this->cells)->filter(fn ($cell) => $cell->type == $type)->count() + 1;
        $name = $type.$count;
        $this->cells[] = (object) [
            'id' => $name,
            'x' => $x,
            'y' => $y,
            'type' => $type,
        ];
    }

    public function selectId(string $id): void
    {
        $this->selectedId = $id;
    }

    #[Computed]
    public function selection(): ?object
    {
        return collect($this->cells)
            ->keyBy('id')
            ->get($this->selectedId);
    }
};
?>
<div x-data='jointJs("container-{{ $this->getId() }}")' class="flex h-screen">
    <div class="w-full h-screen relative" wire:ignore>
        <div class="w-full h-screen" id="container-{{ $this->getId() }}"></div>
        <div class="absolute left-6 inset-y-6 w-[202px] border rounded-xl overflow-hidden">
            <div id="container-{{ $this->getId() }}-palette"></div>
        </div>
        <div class="absolute inset-0" x-show="draggingNew">
            <div id="container-{{ $this->getId() }}-fly"></div>
        </div>
    </div>
    <div class="hidden">
        @foreach($cells as $cell)
            <div
                wire:key="{{ $cell->id }}"
                x-data="jjCell('{{ $cell->type }}', '{{ $cell->id }}', {{ $cell->x }}, {{ $cell->y }})"
                @moved.debounce="$wire.updatePosition(id, x, y)"
                {{-- x-effect="console.log($wire.id)" --}}
                x-show="this.selectedId === '{{ $cell->id }}'"
                wire:ignore
            ></div>
        @endforeach

        @foreach($links as $link)
            <div
                wire:key="{{ $link->id }}"
                x-data="jjLink('{{ $link->id }}', '{{ $link->source }}', '{{ $link->target }}')"
            ></div>
        @endforeach
    </div>
    <div class="w-sm flex flex-col border-l-2 box-content border-gray-400">
        @if ($this->selectedId)
            <p>Selected: {{ $this->selection->id }}</p>
            <p>Position: ({{ $this->selection->x }}, {{ $this->selection->y }})</p>
        @endif
    </div>
</div>
