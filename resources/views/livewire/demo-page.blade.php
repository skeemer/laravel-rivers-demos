<div wire:poll class="contents">
    <x-card class="m-2">
        <div class="flex gap-2 justify-between">
            <p class="flex-1">Current Time: {{ cache('running') ? now()->format('Y-m-d H:i') : '' }}</p>
            <div class="flex-1 flex justify-center gap-2">
                <button class="cursor-pointer hover:underline" wire:click="add()">Add</button>
            </div>
            <div class="flex-1 flex justify-end gap-2">
                <button class="cursor-pointer hover:underline" wire:click="start()">Start</button>
                <button class="cursor-pointer hover:underline" wire:click="restart()">Stop</button>
            </div>
        </div>
    </x-card>
    <div class="w-full flex gap-4 p-4 overflow-hidden max-h-[90vh] h-[90vh]">
        <div class="flex-1 flex flex-col gap-2 text-center">
            <h2 class="text-xl font-medium">User Created</h2>
            <ul class="flex flex-col gap-2">
                @foreach($this->runs->get('user-created-source')?->all() ?? [] as $run)
                    <li wire:key="l1_{{ $run->raft->id }}">
                        <x-card>{{ $run->raft->name }}</x-card>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="flex-1 flex flex-col gap-2 text-center overflow-auto">
            <h2 class="text-xl font-medium">Delay (Name length - 5)</h2>
            <ul class="flex flex-col gap-2">
                @foreach($this->runs->get('delay-1')?->all() ?? [] as $run)
                    <li wire:key="l2_{{ $run->raft->id }}">
                        <x-card>
                            {{ $run->raft->name }}<br>
                            {{ $run->riverTimedBridge?->resume_at->format('Y-m-d H:i') ?? 'resuming' }}
                        </x-card>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="flex-1 flex flex-col gap-2 text-center">
            <h2 class="text-xl font-medium">Uppercase</h2>
            <ul class="flex flex-col gap-2">
                @foreach($this->runs->get('rapid-upper')?->all() ?? [] as $run)
                    <x-card>{{ $run->raft->name }}</x-card>
                @endforeach
            </ul>
        </div>
        <div class="flex-1 flex flex-col gap-6 text-center">
            <h2 class="font-medium">Fork (First Letter)</h2>
            <div class="flex-1 flex flex-col gap-2">
                <h2 class="text-xl font-medium">Lowercase (A-F)</h2>
                <ul class="flex flex-col gap-2">
                    @foreach($this->runs->get('rapid-lower')?->all() ?? [] as $run)
                        <x-card>{{ $run->raft->name }}</x-card>
                    @endforeach
                </ul>
            </div>
            <div class="flex-1 flex flex-col gap-2">
                <h2 class="text-xl font-medium">Camel case (G-L)</h2>
                <ul class="flex flex-col gap-2">
                    @foreach($this->runs->get('rapid-camel')?->all() ?? [] as $run)
                        <x-card>{{ $run->raft->name }}</x-card>
                    @endforeach
                </ul>
            </div>
            <div class="flex-1 flex flex-col gap-2">
                <h2 class="text-xl font-medium">Slug (M-R)</h2>
                <ul class="flex flex-col gap-2">
                    @foreach($this->runs->get('rapid-slug')?->all() ?? [] as $run)
                        <x-card>{{ $run->raft->name }}</x-card>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="flex-1 flex flex-col gap-2 text-center overflow-auto">
            <h2 class="text-xl font-medium">Completed</h2>
            <ul class="flex flex-col gap-2">
                @foreach($this->runs->get(null)?->sortBy(fn ($r) => strtolower($r->raft->name))->all() ?? [] as $run)
                    <x-card>{{ $run->raft->name }}</x-card>
                @endforeach
            </ul>
        </div>
    </div>
</div>
