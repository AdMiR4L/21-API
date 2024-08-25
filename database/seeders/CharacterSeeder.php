<?php

namespace Database\Seeders;

use App\Models\Character;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CharacterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Character::query()->insert([
            [
                'name' => 'جنرال',
                'nickname' => 'General',
                'side' => 0,
            ],
            [
                'name' => 'رئیس پلیس',
                'nickname' => 'Police Chief',
                'side' => 0,
            ],
            [
                'name' => 'پلیس مخفی',
                'nickname' => 'Undercover',
                'side' => 0,
            ],
            [
                'name' => 'جاسوس',
                'nickname' => 'Spy',
                'side' => 0,
            ],
            [
                'name' => 'مشاور',
                'nickname' => 'Advisor',
                'side' => 0,
            ],
            [
                'name' => "محقق",
                'nickname' => 'Detective',
                'side' => 0,
            ],
            [
                'name' => "مذاکره کننده",
                'nickname' => 'Negotiator',
                'side' => 0,
            ],
            [
                'name' => "ذره پوش",
                'nickname' => 'Armored',
                'side' => 0,
            ],
            [
                'name' => "خبرنگار",
                'nickname' => 'Journalist',
                'side' => 0,
            ],
            [
                'name' => "مین گذار",
                'nickname' => 'Demolitionist',
                'side' => 0,
            ],
            [
                'name' => "یاغی",
                'nickname' => 'Outlaw',
                'side' => 0,
            ],
            [
                'name' => "راهنما",
                'nickname' => 'Guide',
                'side' => 0,
            ],
            [
                'name' => "سرباز",
                'nickname' => 'Soldier',
                'side' => 0,
            ],
        ]);

    }
}
