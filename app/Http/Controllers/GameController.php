<?php

namespace App\Http\Controllers;

use App\Events\SendUserCharacterWithSMS;
use App\Models\Game;
use App\Models\History;
use App\Models\Order;
use App\Models\Reserve;
use App\Models\Scenario;
use App\Models\User;
use App\Models\ZarinPal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::query()->where('special', 0)->latest()->take(16)->get();
        return response()->json($games);
    }


    public function single($id)
    {
        $game = Game::query()->with(["god.avatar", "scenario.characters", "history"])->findOrFail($id);
        $reservations = Reserve::with('user')->where("game_id", $id)->where('status' , 1)->get();
        $scenarios =  Scenario::query()->get();
        $histories = History::query()->where("game_id", $id)->get();

        $histories = $histories->pluck('character_id', 'user_id')->toArray();

        return response()->json([
            "game" => $game,
            "reserves" => $reservations,
            "histories" => $histories,
            "scenarios" => $scenarios]);

    }

    public function noPaymentReserve(Request $request)
    {
        $request->validate([
            'game_id' => 'required|integer',
            'chair_no' => 'required|string|max:255',
        ]);
        $user = $request->user();
<<<<<<< HEAD
        if ($user->grade == "A" || $user->grade == "B"){
=======
        if ($user->grade == "A" || $user->grade == "B" || $user->grade == "21"){
>>>>>>> 62af10d6be347c7e3eb5c63ec0e43e60e3ffaed9
            // Retrieve the reservations for the given event ID
            $reservations = Reserve::query()
                ->where('game_id', $request->game_id)
                ->where('status', 1)->get();


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
                    'message' => 'صندلی انتخاب شده موجود نمیباشد',
                    'reserved_seats' => $conflicts,
                ], 409);
            }

            $game = Game::query()->find($request->game_id);
            $game->available_capacity -= count(json_decode($request->chair_no));
            $game->save();


            $reserve = new Reserve();
            $reserve->game_id = $request->game_id;
            $reserve->user_id = $user->id;
            $reserve->status = 1;
            $reserve->chair_no = $request->chair_no;
            $reserve->save();
            return response()->json("جایگاه مورد نظر با موفقیت رزرو شد", 200);
        }
        else
            return response()->json("سطح شما مجاز به رزرو حضوری نیست", 422);

    }

    public function change(Request $request)
    {
        $game = Game::query()->findOrFail($request->game_id);

        if ($game->god_id === $request->god_id)
            $game->status = 1;
        else
            return response()->json("Unauthorized Attempt, You filthy.", 401);
    }

    public function gameEdit(Request $request)
    {

        $request->validate([
            'game_id' => 'required|integer',
            //'god_id' => 'required|integer',
            'game_scenario' => 'required|integer',
        ]);

<<<<<<< HEAD
        $game = Game::query()->find($request->game_id);
        $game->game_scenario = $request->game_scenario;
=======
        $scenario = Scenario::query()->find($request->game_scenario);
        $totalCharacterCount = $scenario->characters->sum('pivot.count');


        $game = Game::query()->find($request->game_id);
        $game->game_scenario = $request->game_scenario;
        $game->game_characters = null;

        $gap = $game->capacity + $game->extra_capacity - $game->available_capacity;
        $game->capacity = $totalCharacterCount + $game->extra_capacity;
        $game->available_capacity = $totalCharacterCount + $game->extra_capacity - $gap;
>>>>>>> 62af10d6be347c7e3eb5c63ec0e43e60e3ffaed9

        if ($request->god_id)
            $game->god_id = $request->god_id;
        if ($request->price)
            $game->price = $request->price;
        if ($request->grades)
            $game->grade = $request->grades;

        $game->update();
        return response()->json("مشخصات بازی با موفقیت ویرایش شد" , 200);
    }
    public function settingEdit(Request $request)
    {
        $request->validate([
            'users_character' => 'required|array',
            'game_id' => 'required|integer',
            'game_scenario' => 'required|integer',
        ]);

        foreach ($request->users_character as $key => $value){
            $history = History::query()
                ->where('game_id', $request->game_id)
                ->where('user_id', $key)
                ->first();
            if ($history){
                $history->character_id = $value;
                $history->update();
            }else{
                $history = new History();
                $history->game_id = $request->game_id;
                $history->game_scenario = $request->game_scenario;
                $history->user_id = $key;
                $history->character_id = $value;
                $history->save();
            }
        }
        return response()->json("تنظیمات بازی با موفقیت ذخیره شد" , 200);
    }

//    public function user(Request $request)
//    {
//        if (is_numeric($request->username))
//                $users = User::query()
//                    ->phone(normalize_number($request->username))
//                    ->get();
//        elseif (preg_match('/^[\x{0600}-\x{06FF}\s]+$/u', $request->username))
//             $users = User::query()
//                ->name($request->username)
//                 ->get();
//        else
//            $users = User::query()->nickname($request->username)->get();
//        return response()->json($users);
//    }


    function normalize_number(String $string): String {
        $persinaDigits1 = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $persinaDigits2 = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];
        $allPersianDigits = array_merge($persinaDigits1, $persinaDigits2);
        $replaces = [...range(0, 9), ...range(0, 9)];

        return str_replace($allPersianDigits, $replaces , $string);
    }


    public function user(Request $request)
    {
        $username = $request->username;

        // Normalize the username by converting Persian digits to English digits
        $normalizedUsername = $this->normalize_number($username);

        if (is_numeric($normalizedUsername)) {
            $users = User::query()
                ->where('phone', $normalizedUsername)
                ->get();
        } elseif (preg_match('/^[\x{0600}-\x{06FF}\s]+$/u', $normalizedUsername)) {
            $users = User::query()
                ->where('name', $normalizedUsername)
                ->get();
        } else {
            $users = User::query()
                ->where('nickname', $normalizedUsername)
                ->get();
        }

        return response()->json($users);
    }


    public function gamePayAttempt(Request $request)
    {
        $request->validate([
            'game_id' => 'required|integer',
            'chair_no' => 'required|string|max:255',
        ]);

<<<<<<< HEAD
        $user = $request->user();
        $check = Reserve::query()
            ->where('game_id', $request->game_id)
            ->where('user_id', $user->id)
            ->where('status', 1)->first();
        if ($check)
            return response()->json("شما قبلا تیکت این رویداد را رزرو کرده اید");
=======
>>>>>>> 62af10d6be347c7e3eb5c63ec0e43e60e3ffaed9

        $reservations = Reserve::query()
            ->where('game_id', $request->game_id)
            ->where('status', 1)->get();
        $unavailableSeats = [];
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

        $game = Game::findOrFail($request->game_id);




        $order = new Order();
        $order->amount = $game->price;
        $order->user_id = $request->user()->id;
        $order->game_id = $game->id;
        $order->type = Game::class;
        $order->method = "ZarinPal";
        $order->save();

        $payment = new ZarinPal($game->price , $order->id);
        $result = $payment->doPayment();

        $order->authority = $result->Authority;
        $order->save();
//        $order->resCode = $result->ResCode;
//        $order->token = $result->Token;


        $reserve = new Reserve();
        $reserve->game_id = $game->id;
        $reserve->user_id = $request->user()->id;
        $reserve->order_id = $order->id;
        $reserve->chair_no = $request->chair_no;
        $reserve->save();


        if ($result->Status == 100) {
            return response()->json([
                'status' => 100,
                'authority' => $result->Authority
            ]);
        } else {
            return response()->json([
                'status' => $result->Status,
                'message' => 'Payment failed'
            ], 400);
        }
    }


    public function gamePaymentVerify(Request $request, $id)
    {

        $reserve = Reserve::query()->where("order_id" , $id)->first();
        $game = Game::query()->find($reserve->game_id);
        $order = Order::find($id);

        $ZarinPal = new ZarinPal($order->amount);
        $result = $ZarinPal->verifyPayment($request->Authority , $request->Status);
        //$result = $ZarinPal->verifyPayment("A000000000000000000000000000dnvmyp67" , "OK");
       // dd($result);

        if ($result) {
            $order->status = 1;
            $order->authority = $request->Authority;
            $order->zarin_status = $request->Status;
            $order->RefID = $result->RefID;
            $reserve->update(['status' => 1]);
            $order->save();
            $game->available_capacity -= count(json_decode($reserve->chair_no));
            $game->save();
            return redirect('https://21sport.club/verify/zarinpal/'.$id)->with('message', 'پرداخت شما با موفقیت انجام شد');
        }
        else
            return redirect('https://21sport.club/verify/zarinpal/'.$id)->with('message', 'در پرداخت شما خطایی به وجود آمده است، لطفا مجددا تلاش کنید');
    }

    public function gamePaymentStatus(Request $request, $id)
    {
        $order = Order::with(['reserve.user' , 'game.god', 'game.scenario', 'user'])->find($id);
        if ($order->user_id === $request->user()->id)
        return response()->json($order);
        else response()->json("Your Cant Visit Order" , 404);
    }






    public function sendUserCharacters(Request $request)
    {
        $request->validate([
            'userCharacters' => 'required|array',
            'game_id' => 'required|integer',
        ]);
        $game = Game::query()->find($request->game_id);
        $game->update(["status" => 1]);
        $history = History::query()->where("game_id", $request->game_id)->get();
        if (count($history)){
            foreach($history as $item)
            {
                $user = User::query()->find ($item->user_id);
                event(new SendUserCharacterWithSMS($user , $game));
            }
            return response()->json("نقش ها با موفقیت از طریق پیامک ارسال شدند", 200);
        }
        return response()->json("لطفا قبل از ارسال نقش اطلاعات را ذخیره کنید", 422);
    }

//    public function scoresEdit(Request $request)
//    {
//        $request->validate([
//            'scores' => 'required|array',
//            'game_id' => 'required|integer',
//            'mvp' => 'required|integer',
//            'side' => 'required|array',
//        ]);
//
//        $game = Game::query()->find($request->game_id);
//        $game->mvp = $request->mvp;
//        if (is_array($request->side)){
//            $win = [];
//            foreach ($request->side as $side => $value)
//                if ($value)
//                    $win[] = $side;
//            $game->win_side = implode(",", $win);
//        }
//        $game->update();
//
//        $history = History::query()->where("game_id", $game->id);
//        foreach ($request->scores as $user => $score){
//            $history = History::query()
//                ->where("game_id" , $game->id)
//                ->where("user_id" , $user)
//                ->first();
//            $history->score = $score;
//            if ($game->win_side === $history->character->side)
//            $history->win = 1;
//            $history->save();
//        }
//        return response()->json("نتیجه و امتیازات بازی با موفقت ذخیره شد" , 200);
//    }


    public function scoresEdit(Request $request)
    {
        // Validate the request inputs
        $request->validate([
            'scores' => 'required|array',
            'game_id' => 'required|integer',
            'mvp' => 'required|integer',
            'side' => 'required|array',
        ]);

        // Find the game by ID
        $game = Game::find($request->game_id);
        if (!$game) {
            return response()->json(['error' => 'Game not found'], 404);
        }

        // Update MVP
        $game->mvp = $request->mvp;
        $game->status = 2;

        // Handle the win_side
        if (is_array($request->side)) {
            $win = array_keys(array_filter($request->side));
            $game->win_side = implode(",", $win);
        }

        // Save the game
        $game->save();

        // Fetch all history records related to the game in one query
        $histories = History::where('game_id', $game->id)->get();

        // Update scores and win status
        foreach ($histories as $history) {
            if (isset($request->scores[$history->user_id])) {
                $history->score = $request->scores[$history->user_id];
                $history->win = in_array($history->character->side, explode(',', $game->win_side)) ? 1 : 0;
                $history->save();
            }
        }

        // Return success response
        return response()->json("نتیجه و امتیازات بازی با موفقیت ذخیره شد", 200);
    }


    public function gameUserRemove(Request $request)
    {
        $request->validate([
            'reserve_id' => 'required|integer',
        ]);
        $reserve = Reserve::query()->find($request->reserve_id);
        $reserve->update(['status' => 3]);
        $game = Game::query()->find($reserve->game_id);
<<<<<<< HEAD
        $game->available_capacity = $game->available_capacity-1;
        $game-save();
        return response()->json("کاربر با موفقیت حذف شد", 200);
    }
//    public function chooseUserChair(Request $request)
//    {
//        $request->validate([
//            'game_id' => 'required|integer',
//            'order_id' => 'required|integer',
//            'chairs' => 'required|array',
//        ]);
//        $notFound = [];
//        foreach ($request->chairs as $chair => $input){
//            if (is_numeric($input))
//                $user = User::query()->where("phone" , $input)->first();
//            else
//                $user = User::query()->where("nickname" , $input)->first();
//
//            if ($user){
//                $oldReserve = Reserve::where('order_id' , $request->order_id)->first();
//                $oldReserve->status = 2;
//                $oldReserve->save();
//                $reserve = new Reserve();
//                $reserve->user_id = $user->id;
//                $reserve->game_id = $request->game_id;
//                $reserve->chair_no = "[".$chair."]";
//                $reserve->order_id = $request->order_id;
//                $reserve->status = 1;
//                $reserve->save();
//            }
//            else  $notFound[] = $chair;
//        }
//        return response()->json([
//            'message' => 'User chair assignment processed.',
//            'not_found_chairs' => $notFound,
//        ]);
//    }

=======
        $game->update(["available_capacity" => $game->available_capacity-1]);
        return response()->json("کاربر با موفقیت حذف شد", 200);
    }
>>>>>>> 62af10d6be347c7e3eb5c63ec0e43e60e3ffaed9


    public function chooseUserChair(Request $request)
    {
        $request->validate([
            'game_id' => 'required|integer',
            'order_id' => 'required|integer',
            'chairs' => 'required|array',
        ]);

        $notFound = [];  // Array to store chair numbers where a user could not be found
        $assignedUsers = [];  // Array to track assigned users to avoid duplicates

        // First, check if all inputs are valid and users exist
        foreach ($request->chairs as $chair => $input) {
            if ($input === null) {
                return response()->json([
                    'message' => 'لطفا نام کاربری یا شماره تماس را وارد کنید',
                    'chair' => [$chair],
                ], 422);  // Return an error if the input is null
            }

            if (is_numeric($input)) {
                $user = User::query()->where('phone', $input)->first();
            } else {
                $user = User::query()->where('nickname', $input)->first();
            }

            if (!$user) {
                // If any user is not found, add the chair to the notFound array
                $notFound[] = $chair;
            } else {
                // Check if the user is already assigned
                if (in_array($user->id, $assignedUsers)) {
                    return response()->json([
                        'message' => 'هر کاربر فقط میتواند یک جایگاه داشته باشد',
                        'chair' => [$chair],
                    ], 422);  // 422 Unprocessable Entity
                }
                $assignedUsers[] = $user->id;
            }
        }

        // If any user was not found, return an error response and do not proceed
        if (!empty($notFound)) {
            return response()->json([
                'message' => 'کاربری با این مشخصات یافت نشد',
                'chair' => $notFound,
            ], 422);  // 422 Unprocessable Entity
        }

        // If all users are found and unique, proceed with the reservation process
        foreach ($request->chairs as $chair => $input) {
            if (is_numeric($input)) {
                $user = User::query()->where('phone', $input)->first();
            } else {
                $user = User::query()->where('nickname', $input)->first();
            }

            // Since we've already checked, $user should not be null here
            $oldReserve = Reserve::where('order_id', $request->order_id)->first();
            if ($oldReserve) {
                $oldReserve->status = 2;
                $oldReserve->save();
            }

            $reserve = new Reserve();
            $reserve->user_id = $user->id;
            $reserve->game_id = $request->game_id;
            $reserve->chair_no = "[" . $chair . "]";
            $reserve->order_id = $request->order_id;
            $reserve->status = 1;
            $reserve->save();
        }

        return response()->json("جایگاه با موفقیت به نام کاربران ثبت شد", 200);
    }





//    public function getScenarioCharacters()
//    {
//        $scenario = Scenario::query()->find(1); // Assuming you have a scenario with ID 1
//    /*
//        $scenario->characters()->attach([
//            51 => ['count' => 1],
//            62 => ['count' => 1],
//            63 => ['count' => 1],
//            64 => ['count' => 1],
//            52 => ['count' => 1],
//            42 => ['count' => 1],
//            16 => ['count' => 3],
//            54 => ['count' => 1],
//            44 => ['count' => 1],
//            65 => ['count' => 1],
//            66 => ['count' => 1],
//            // Add other characters here
//        ]);*/
//
//        $characters = $scenario->characters;
//
//        foreach ($characters as $character) {
//            echo $character->name . ' appears ' . $character->pivot->count . ' times';
//        }
//    }

    public function changeCharacters(Request $request)
    {
        $request->validate([
            'game_id' => 'required|integer',
            'capacity' => 'required|integer',
        ]);
        $game = Game::query()->find($request->game_id);


        $gap = $game->capacity + $game->extra_capacity - $game->available_capacity;

        $game->capacity = $request->capacity;
        $game->available_capacity = $request->capacity - $gap;
        $game->game_characters = $request->characters;
        $game->save();

        return response()->json("نقش های این بازی با موفقیت بروزرسانی شد", 200);

    }

    public function cron()
    {
        $clocks = ["15:00-16:30", "16:30-18:00", "18:00-19:30", "19:30-21:00", "21:00-22:30", "22:30-00:00" ];
        $salons = [1, 2, 3];

        foreach ($salons as $salon) {
            foreach ($clocks as $clock) {
                if ($salon === 1 && $clock < "18:00-19:30") {
                    continue; // Skip this iteration
                }
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
