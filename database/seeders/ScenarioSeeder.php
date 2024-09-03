<?php

namespace Database\Seeders;

use App\Models\Scenario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScenarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Scenario::query()->insert([
            [
                'name' => 'شاهد',
            ],
            [
                'name' => 'بازپرس',
            ],
            [
                'name' => 'آرشیتکت',
            ],
            [
                'name' => 'مذاکره',
            ],
            [
                'name' => 'نماینده',
            ],
        ]);

        $scenario = Scenario::query()->find(1);
        $scenario->characters()->attach([
            51 => ['count' => 1],
            62 => ['count' => 1],
            63 => ['count' => 1],
            64 => ['count' => 1],
            52 => ['count' => 1],
            42 => ['count' => 1],
            16 => ['count' => 3],
            54 => ['count' => 1],
            44 => ['count' => 1],
            65 => ['count' => 1],
            66 => ['count' => 1],
            // Add other characters here
        ]);
        $scenario = Scenario::query()->find(2);
        $scenario->characters()->attach([
            67 => ['count' => 1],
            52 => ['count' => 1],
            28 => ['count' => 1],
            20 => ['count' => 1],
            22 => ['count' => 1],
            59 => ['count' => 1],
            16 => ['count' => 3],
            54 => ['count' => 1],
            56 => ['count' => 1],
            55 => ['count' => 1],
            5 => ['count' => 1],
        ]);
        $scenario = Scenario::query()->find(3);
        $scenario->characters()->attach([
            20 => ['count' => 1],
            28 => ['count' => 1],
            22 => ['count' => 1],
            17 => ['count' => 1],
            18 => ['count' => 1],
            19 => ['count' => 1],
            21 => ['count' => 1],
            1 => ['count' => 1],
            34 => ['count' => 1],
            24 => ['count' => 1],
            25 => ['count' => 1],
            23 => ['count' => 1],
        ]);
        $scenario = Scenario::query()->find(4);
        $scenario->characters()->attach([
            1 => ['count' => 1],
            68 => ['count' => 1],
            5 => ['count' => 2],
            28 => ['count' => 1],
            20 => ['count' => 1],
            22 => ['count' => 1],
            69 => ['count' => 1],
            16 => ['count' => 3],
        ]);
        $scenario = Scenario::query()->find(5);
        $scenario->characters()->attach([
            1 => ['count' => 1],
            45 => ['count' => 1],
            72 => ['count' => 1],
            71 => ['count' => 1],
            44 => ['count' => 1],
            73 => ['count' => 1],
            28 => ['count' => 1],
            11 => ['count' => 1],
            16 => ['count' => 3],
            74 => ['count' => 1],

        ]);
    }
}
