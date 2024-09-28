<?php

namespace App\Http\Controllers;

use App\Events\SendRolesNotification;
use App\Events\SendUserCharacterWithSMS;
use App\Models\Game;
use App\Models\History;
use App\Models\Order;
use App\Models\Reserve;
use App\Models\Scenario;
use App\Models\User;
use App\Models\UserLog;
use App\Models\ZarinPal;
use App\Notifications\RolesPushNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::with('scenario')->where('special', 0)->latest()->take(15)->get();
        //$games = Game::query()->where('special', 0)->orderBy('created_at', 'DESC')->take(16)->get();
        return response()->json($games);
    }


    public function single($id)
    {
        $game = Game::query()->with(["god", "scenario.characters", "history"])->findOrFail($id);
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

    public static function checkUserGrade($gameGrade, $userGrade)
    {
        $gameGradeParts = explode('-', $gameGrade);
        if (in_array($userGrade, $gameGradeParts))
            return true;
        else
            return false;
    }

    public function reservationValidate($game, $user, $chairs)
    {
        $gameGrade = Game::findOrFail($game)->grade;
        $gradeCheck = $this->checkUserGrade($gameGrade, $user->grade);
        if ($gradeCheck === false)
            return response()->json('سطح کاربری شما اجازه شرکت در این رویداد را ندارد', 422);

        $check = Reserve::query()
            ->where('game_id', $game)
            ->where('user_id', $user->id)
            ->where('status', 1)->first();
        if ($check)
            return response()->json('شما قبلا تیکت این رویداد را رزرو کرده اید', 422);

        $reservations = Reserve::query()
            ->where('game_id', $game)
            ->where(function ($query) {
                $query->where('status', 1)
                    ->orWhere(function ($query) {
                        $query->where('status', 0)
                            ->where('created_at', '>', Carbon::now()->subMinutes(2));
                    });
            })
            ->get();
        $unavailableSeats = [];
        foreach ($reservations as $reservation) {
            $seatNumbers = json_decode($reservation->chair_no, true);
            if (is_array($seatNumbers)) {
                $unavailableSeats = array_merge($unavailableSeats, $seatNumbers);
            }
        }
        $conflicts = array_intersect(json_decode($chairs), $unavailableSeats);
        if (!empty($conflicts)) {
            return response()->json([
                'status' => 'error',
                'message' => 'صندلی انتخاب شده موجود نمیباشد',
                'reserved_seats' => $conflicts,
            ], 409);
        }
        return true;
    }

    public function gamePayAttempt(Request $request)
    {
        $request->validate([
            'game_id' => 'required|integer',
            'chair_no' => 'required|string|max:255',
            'amount' => 'required|integer',
        ]);
        $user = $request->user();

        $validationResponse = $this->reservationValidate($request->game_id, $user, $request->chair_no);
        if ($validationResponse !== true)
            return $validationResponse; // If validation fails, return the error response


        $game = Game::findOrFail($request->game_id);
        $price = $game->price * $request->amount;
        $order = new Order();
        $order->amount = $price;
        $order->user_id = $request->user()->id;
        $order->game_id = $game->id;
        $order->type = Game::class;
        $order->method = "ZarinPal";
        $order->save();

        $payment = new ZarinPal($price , $order->id);
        $result = $payment->doPayment();
        $order->authority = $result->Authority;
        $order->save();

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

    public function noPaymentReserve(Request $request)
    {
        $request->validate([
            'game_id' => 'required|integer',
            'chair_no' => 'required|string|max:255',
        ]);
        $user = $request->user();

        $validationResponse = $this->reservationValidate($request->game_id, $user, $request->chair_no);
        if ($validationResponse !== true)
            return $validationResponse; // If validation fails, return the error response
//        $check = Reserve::query()
//            ->where('game_id', $request->game_id)
//            ->where('user_id', $user->id)
//            ->where('status', 1)->first();
//        if ($check)
//            return response()->json('شما قبلا تیکت این رویداد را رزرو کرده اید', 422);
        if(count(json_decode($request->chair_no)) > 1)
            return response()->json('در حالت حضوری فقط یک صندلی قابل انتخاب است', 422);

        if ($user->grade == "A" || $user->grade == "B" || $user->grade == "21"){

//            $reservations = Reserve::query()
//                ->where('game_id', $request->game_id)
//                ->where('status', 1)->get();
//            $unavailableSeats = [];
//            foreach ($reservations as $reservation) {
//                $seatNumbers = json_decode($reservation->chair_no, true);
//                if (is_array($seatNumbers)) {
//                    $unavailableSeats = array_merge($unavailableSeats, $seatNumbers);
//                }
//            }
//            $conflicts = array_intersect(json_decode($request->chair_no), $unavailableSeats);
//            if (!empty($conflicts)) {
//                return response()->json([
//                    'status' => 'error',
//                    'message' => 'صندلی انتخاب شده موجود نمیباشد',
//                    'reserved_seats' => $conflicts,
//                ], 409);
//            }

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

        $scenario = Scenario::query()->find($request->game_scenario);
        $totalCharacterCount = $scenario->characters->sum('pivot.count');


        $game = Game::query()->find($request->game_id);
        $game->game_scenario = $request->game_scenario;
        $game->game_characters = null;

        $gap = $game->capacity + $game->extra_capacity - $game->available_capacity;
        $game->capacity = $totalCharacterCount + $game->extra_capacity;
        $game->available_capacity = $totalCharacterCount + $game->extra_capacity - $gap;

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





    public function gamePaymentVerify(Request $request, $id)
    {

        $reserve = Reserve::query()->where("order_id" , $id)->first();
        $game = Game::query()->find($reserve->game_id);
        $order = Order::find($id);

        $ZarinPal = new ZarinPal($order->amount);
        $result = $ZarinPal->verifyPayment($request->Authority , $request->Status);
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
        else response()->json("You Cant Visit Order Info" , 404);
    }



    public function sendUserCharacters(Request $request)
    {
       $request->validate([
            'userCharacters' => 'required|array',
            'game_id' => 'required|integer',
        ]);

        $game = Game::find($request->game_id);
        if (!$game) {
            return response()->json("Game not found", 404);
        }

        // Update the game's status to 1 (assuming 1 means active or processing)
        $game->update(['status' => 1]);

        // Retrieve history records for the given game_id
        $history = History::where('game_id', $request->game_id)->get();

        // Check if any history exists
        if ($history->isEmpty())
            return response()->json("لطفا قبل از ارسال نقش اطلاعات را ذخیره کنید", 422);


        // Use eager loading to optimize user retrieval and prevent N+1 query issue
        $users = User::whereIn('id', $history->pluck('user_id'))->get()->keyBy('id');

        // Send SMS to each user associated with the history
        foreach ($history as $item) {
            $user = $users->get($item->user_id);
            if ($user)
                event(new SendRolesNotification($user, $game));
                //$user->notify(new RolesPushNotification($user, $game));
                //Notification::send($user, new RolesPushNotification($user, $game));

                //*** event(new SendUserCharacterWithSMS($user, $game));
        }

        return response()->json("نقش ها با موفقیت از طریق پیامک ارسال شدند", 200);
    }


    public function sendNotification()
    {
        $user = User::find(1);
            $game = Game::find(336);
            Notification::send($user, new RolesPushNotification($user, $game));
    }

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
        $game->save();

        // Fetch all history records related to the game in one query
        //$histories = History::where('game_id', $game->id)->get();
        $histories = History::where('game_id', $game->id)
            ->whereDate('created_at', '>=', '2024-09-22')
            ->get();
        foreach ($histories as $history) {
            if (isset($request->scores[$history->user_id])) {
                // Update individual history record
                $score = $request->scores[$history->user_id];
                $history->score = $score;
                $history->win = in_array($history->character->side, explode(',', $game->win_side)) ? 1 : 0;
                $history->save();

                // Calculate XP based on score
                $xp = match(true) {
                    $score >= 8 && $score <= 10 => 2,
                    $score >= 6 && $score < 8  => 1.5,
                    $score >= 4 && $score < 6  => 1,
                    $score >= 2 && $score < 4  => 0.5,
                    default => 0,
                };

                // Get the sum of all scores and wins for this user from the histories table
                /*$userHistoryStats = History::query()
                    ->where('user_id', $history->user_id)
                    ->selectRaw('SUM(score) as total_score, SUM(win) as total_wins')
                    ->first();*/
                $userHistoryStats = History::query()
                    ->where('user_id', $history->user_id)
                    ->whereDate('created_at', '>=', '2024-09-22')
                    ->selectRaw('SUM(score) as total_score, SUM(win) as total_wins')
                    ->first();

                // Get the current user details (including XP and Level) using Eloquent
                $user = User::find($history->user_id);

                // Update user's XP
                $newXp = $user->xp + $xp;

                // Define XP thresholds for leveling
                $xpThresholds = [
                    21 => 5242880, 20 => 2621440, 19 => 1310720,
                    18 => 655360, 17 => 327680, 16 => 163840,
                    15 => 81920, 14 => 40960, 13 => 20480,
                    12 => 10240, 11 => 5120, 10 => 2560,
                    9 => 1280, 8 => 640, 7 => 320,
                    6 => 160, 5 => 80, 4 => 40,
                    3 => 20, 2 => 10
                ];

                // Determine new level based on XP thresholds
                $newLevel = 1; // Default to level 1
                foreach ($xpThresholds as $level => $xpRequired) {
                    if ($newXp >= $xpRequired) {
                        $newLevel = $level;
                        break;
                    }
                }

                // Update the user's score, win, XP, and level columns with the new values using Eloquent
                $user->update([
                    'score' => $userHistoryStats->total_score,
                    'win' => $userHistoryStats->total_wins,
                    'xp' => $newXp,
                    'level' => $newLevel,
                ]);
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
        $game->update(["available_capacity" => $game->available_capacity-1]);
        return response()->json("کاربر با موفقیت حذف شد", 200);
    }


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
                $user = User::query()->where('phone', $this->normalize_number($input))->first();
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


    public function archive()
    {
        $perPage = 7; // Number of groups (dates) per page
        $page = request()->input('page', 1); // Current page, default to 1 if not provided
        $today = now()->toDateString();
        //$yesterday = now()->subDay()->toDateString();
        // Retrieve all records
        $games = Game::with('mvpUser', 'scenario')  // Eager load the MVP user relationship
        ->select(
            DB::raw('DATE(created_at) as date'),
            'salon',
            'clock',
            'id',
            'status',
            'win_side',
            'game_scenario',
            'mvp'
        )
            ->whereDate('created_at', '<', $today)
            ->orderBy('created_at')
            ->orderBy('salon')
            ->orderBy('clock')
            ->get();

        // Group records by date
        $groupedGames = $games->groupBy(function($item) {
            return $item->date;
        })->map(function($dateGroup) {
            return $dateGroup->groupBy('salon')->map(function($salonGroup) {
                return $salonGroup;
            });
        });

        // Reverse the order of the grouped dates
        $groupedGames = $groupedGames->sortKeysDesc();

        // Convert grouped data to array for pagination
        $groupedGamesArray = $groupedGames->toArray();

        // Paginate the reversed data
        $groups = array_slice($groupedGamesArray, ($page - 1) * $perPage, $perPage);
        $paginatedGroupedGames = new LengthAwarePaginator(
            $groups,
            count($groupedGamesArray),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );

        // Build pagination metadata
        $pagination = [
            'first_page_url' => $paginatedGroupedGames->url(1),
            'from' => $paginatedGroupedGames->firstItem(),
            'last_page' => $paginatedGroupedGames->lastPage(),
            'last_page_url' => $paginatedGroupedGames->url($paginatedGroupedGames->lastPage()),
            'next_page_url' => $paginatedGroupedGames->nextPageUrl(),
            'prev_page_url' => $paginatedGroupedGames->previousPageUrl(),
            'per_page' => $paginatedGroupedGames->perPage(),
            'current_page' => $paginatedGroupedGames->currentPage(),
            'total' => $paginatedGroupedGames->total(),
            'links' => [
                [
                    'url' => $paginatedGroupedGames->previousPageUrl(),
                    'label' => 'قبلی',
                    'active' => false,
                ],
                ...collect(range(1, $paginatedGroupedGames->lastPage()))->map(function ($page) use ($paginatedGroupedGames) {
                    return [
                        'url' => $paginatedGroupedGames->url($page),
                        'label' => $page,
                        'active' => $page == $paginatedGroupedGames->currentPage(),
                    ];
                })->toArray(),
                [
                    'url' => $paginatedGroupedGames->nextPageUrl(),
                    'label' => 'بعدی',
                    'active' => false,
                ]
            ]
        ];
        $response = [
            'data' => $paginatedGroupedGames->items(),
            'pagination' => $pagination,
        ];
        return response()->json($response);
    }


    public function roleVisits(Request $request)
    {
        $request->validate([
            'game_id' => 'required|integer',
        ]);
        $game = Game::find($request->game_id);
        $log = new UserLog();
        $log->user_id = $request->user()->id;
        $game->logs()->save($log);
    }

    public function roleVisitLogs(Request $request)
    {
        $request->validate([
            'game_id' => 'required|integer',
        ]);
        $game = Game::find($request->game_id);
        $logs = UserLog::with("user")->where("loggable_id", $game->id)->latest()->get();
        return response()->json($logs);
    }
}
