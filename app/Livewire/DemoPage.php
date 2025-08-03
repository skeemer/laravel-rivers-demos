<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use LsvEu\Rivers\Models\RiverRun;

class DemoPage extends Component
{
    public function hydrate(): void
    {
        Artisan::call('rivers:check_timed_bridges');
    }

    public function add(): void
    {
        User::factory()->create();
    }

    public function restart(): void
    {
        DB::table('jobs')->truncate();
        $this->stop();
        RiverRun::truncate();
        User::truncate();
        DB::table('failed_jobs')->truncate();
        $this->redirect('/');
    }

    public function start(): void
    {
        cache()->set('running', time());
        User::factory(20)->create();
    }

    public function stop(): void
    {
        cache()->forget('running');
    }

    #[Computed]
    protected function runs()
    {
        return RiverRun::latest('updated_at')->get()->groupBy('location');
    }
}
