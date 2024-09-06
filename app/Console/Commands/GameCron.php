<?php

namespace App\Console\Commands;

use App\Models\Game;
use Illuminate\Console\Command;

class GameCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $clocks = ["16-18", "18-20", "20-22"];
        // $clocks = ["15:00-16:30", "16:30-18:00", "18:00-19:30", "19:30-21:00", "21:00-22:30", "22:30-00:00" ];
        $clocks = ["16:30-18:00", "18:00-19:30", "19:30-21:00", "21:00-22:30", "22:30-00:00" ];
        $salons = [1, 2, 3];

        foreach ($salons as $salon) {
            foreach ($clocks as $clock) {
                /*if ($salon === 1 && $clock < "18:00-19:30") {
                    continue; // Skip this iteration
                }*/
                $game = new Game();
                $game->capacity = 13;
                $game->extra_capacity = 0;
                $game->available_capacity = $game->capacity + $game->extra_capacity;
                $game->game_scenario = null;
                $game->game_characters = null;
                $game->price = 55000;
                $game->clock = $clock;
                $game->salon = $salon;
                $game->grade = "21-A-B-C-D";

                $game->save();
            }
        }
    }
}
