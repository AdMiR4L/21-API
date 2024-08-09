<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Reserve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::query()->where('special', 0)->latest()->take(9)->get();
        return response()->json($games);
    }


    public function single($id)
    {
        $game = Game::query()->with("god.avatar")->findOrFail($id);
        $reservations = Reserve::query()->where("event_id", $id)->get();

        // Loop through each reservation and parse the seat numbers
        $unavailableSeats = [];
        foreach ($reservations as $reservation) {
            $seatNumbers = json_decode($reservation->chair_no, true);
            if (is_array($seatNumbers)) {
                $unavailableSeats = array_merge($unavailableSeats, $seatNumbers);
            }
        }

        return response()->json(["game" => $game, "reserves" => $reservations, "unavailable" => $unavailableSeats]);

    }

    public function reserve(Request $request)
    {

        $request->validate([
            'game_id' => 'required|integer',
            'chair_no' => 'required|string|max:255',
        ]);



        // Retrieve the reservations for the given event ID
        $reservations = Reserve::query()->where('game_id', $request->game_id)->get();

        // Initialize an array to hold all unavailable seats
        $unavailableSeats = [];

        // Loop through each reservation and parse the seat numbers
        foreach ($reservations as $reservation) {
            $seatNumbers = json_decode($reservation->chair_no, true);
            if (is_array($seatNumbers)) {
                $unavailableSeats = array_merge($unavailableSeats, $seatNumbers);
            }
        }

        // Check for intersection between requested seats and unavailable seats
        $conflicts = array_intersect(json_decode($request->chair_no), $unavailableSeats);

        if (!empty($conflicts)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Some seats are already reserved.',
                'reserved_seats' => $conflicts,
            ], 409);
        }


        $game = Game::query()->find($request->game_id);
        $game->available_slot -= count(json_decode($request->chair_no));
        $game->save();

        $user = $request->user();
        $reserve = new Reserve();
        $reserve->game_id = $request->game_id;
        $reserve->user_id = $user->id;
        $reserve->chair_no = $request->chair_no;
        $reserve->save();
        return response()->json("success", 200);

    }

    public function change(Request $request)
    {
        $game = Game::query()->findOrFail($request->game_id);

        if ($game->god_id === $request->god_id)
            $game->status = 1;
        else
            return response()->json("Unauthorized Attempt, You filthy.", 400);
    }


    public function cron()
    {
        $clocks = ["16-18", "18-20", "20-22"];
        $salons = [1, 2, 3];

        foreach ($salons as $salon) {
            foreach ($clocks as $clock) {
                $game = new Game();
                $game->title = "";
                $game->capacity = 18;
                $game->extra_capacity = 6;
                $game->available_slots = $game->capacity + $game->extra_capacity;
                $game->mode = "در انتظار";
                $game->price = 35000;
                $game->clock = $clock;
                $game->salon = $salon;
                $game->grade = "21-A-B-C-D";
                $game->paying = 1;
                $game->status = 1;
                $game->mafia_roles = 1;
                $game->god_id = 1;
                $game->total_xp = "220";
                $game->save();
            }
        }
    }
}
