<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div class="h-screen w-full flex flex-col items-center justify-center bg-gray-100">
    <div class="border border-gray-300 rounded-lg p-8 bg-white flex flex-col gap-6">
        <a
            href="{{ route('live') }}"
            class="block border border-gray rounded-lg px-4 py-2 text-center hover:bg-slate-200"
        >Run Demo</a>
        <a
            href="{{ route('editor') }}"
            class="block border border-gray rounded-lg px-4 py-2 text-center hover:bg-slate-200"
        >Editor</a>
    </div>
</div>
