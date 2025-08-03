<?php

namespace Database\Seeders;

use App\Models\User;
use App\Rivers\Bridges\RandomTimedBridge;
use App\Rivers\Forks\Conditions\NameSortCondition;
use App\Rivers\Rafts\UserRaft;
use App\Rivers\Ripples\CamelCaseName;
use App\Rivers\Ripples\LowerCaseName;
use App\Rivers\Ripples\SlugName;
use App\Rivers\Ripples\UpperCaseName;
use Illuminate\Database\Seeder;
use LsvEu\Rivers\Cartography\Connection;
use LsvEu\Rivers\Cartography\Fork;
use LsvEu\Rivers\Cartography\Launches\ModelCreated;
use LsvEu\Rivers\Cartography\Rapid;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Models\River;

class RiverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $map = new RiverMap([
            'raftClass' => UserRaft::class,
            'launches' => [
                new ModelCreated([
                    'id' => 'user-created-launch',
                    'class' => User::class,
                    'raftClass' => UserRaft::class,
                ]),
            ],
            'bridges' => [
                new RandomTimedBridge([
                    'id' => 'delay-1',
                ]),
            ],
            'rapids' => [
                new Rapid([
                    'id' => 'rapid-upper',
                    'ripples' => [
                        new UpperCaseName,
                    ],
                ]),
                new Rapid([
                    'id' => 'rapid-camel',
                    'ripples' => [
                        new CamelCaseName,
                    ],
                ]),
                new Rapid([
                    'id' => 'rapid-lower',
                    'ripples' => [
                        new LowerCaseName,
                    ],
                ]),
                new Rapid([
                    'id' => 'rapid-slug',
                    'ripples' => [
                        new SlugName,
                    ],
                ]),
            ],
            'forks' => [
                new Fork([
                    'id' => 'fork-name',
                    'conditions' => [
                        new NameSortCondition([
                            'id' => 'sort-1',
                            'letter' => 'G',
                        ]),
                        new NameSortCondition([
                            'id' => 'sort-2',
                            'letter' => 'M',
                        ]),
                        new NameSortCondition([
                            'id' => 'sort-3',
                            'letter' => 'S',
                        ]),
                    ],
                ]),
            ],
            'connections' => [
                new Connection([
                    'startId' => 'user-created-launch',
                    'endId' => 'delay-1',
                ]),
                new Connection([
                    'startId' => 'delay-1',
                    'endId' => 'rapid-upper',
                ]),
                new Connection([
                    'startId' => 'rapid-upper',
                    'endId' => 'fork-name',
                ]),
                new Connection([
                    'startId' => 'fork-name',
                    'startConditionId' => 'sort-1',
                    'endId' => 'rapid-lower',
                ]),
                new Connection([
                    'startId' => 'fork-name',
                    'startConditionId' => 'sort-2',
                    'endId' => 'rapid-camel',
                ]),
                new Connection([
                    'startId' => 'fork-name',
                    'startConditionId' => 'sort-3',
                    'endId' => 'rapid-slug',
                ]),
            ],
        ]);

        River::create([
            'title' => 'River 1',
            'status' => 'active',
            'map' => $map,
        ]);
    }
}
