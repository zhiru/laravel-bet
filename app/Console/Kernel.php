<?php
namespace Aireset\Console
{
    class Kernel extends \Illuminate\Foundation\Console\Kernel
    {
        protected $commands = [];
        protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule)
        {
            $schedule->command('queue:work --daemon')->everyMinute()->withoutOverlapping();
            $schedule->call(function()
            {
                $users = \Aireset\StatGame::where('date_time', '>', \Carbon\Carbon::now()->subHours()->format('Y-m-d H:i:s'))->groupBy('user_id')->pluck('user_id');
                if( count($users) )
                {
                    foreach( $users as $userID )
                    {
                        $counts = \DB::select("SELECT \n                                        game, COUNT(*) AS number \n                                    FROM \n                                        `w_stat_game` \n                                    WHERE \n                                        user_id=:userID AND \n                                        date_time > :mydate \n                                    GROUP BY game \n                                    ORDER BY COUNT(*) DESC \n                                    LIMIT 5", [
                            'userID' => $userID,
                            'mydate' => \Carbon\Carbon::now()->subHours()->format('Y-m-d H:i:s')
                        ]);
                        foreach( $counts as $count )
                        {
                            \Aireset\GameActivity::create([
                                'game' => $count->game,
                                'number' => $count->number,
                                'user_id' => $userID
                            ]);
                        }
                        $counts = \Aireset\StatGame::where('user_id', $userID)->where('win', '>', 0)->where('date_time', '>', \Carbon\Carbon::now()->subHours()->format('Y-m-d H:i:s'))->orderBy('win', 'DESC')->get();
                        $stats = [];
                        foreach( $counts as $count )
                        {
                            if( isset($stats[$count->game]) )
                            {
                                continue;
                            }
                            $stats[$count->game] = $count->win;
                            if( count($stats) == 5 )
                            {
                                break;
                            }
                        }
                        foreach( $stats as $key => $stat )
                        {
                            \Aireset\GameActivity::create([
                                'game' => $key,
                                'max_win' => $stat,
                                'user_id' => $userID
                            ]);
                        }
                        $counts = \Aireset\StatGame::where('user_id', $userID)->where('date_time', '>', \Carbon\Carbon::now()->subHours()->format('Y-m-d H:i:s'))->orderBy('bet', 'DESC')->get();
                        $stats = [];
                        foreach( $counts as $count )
                        {
                            if( isset($stats[$count->game]) )
                            {
                                continue;
                            }
                            $stats[$count->game] = $count->bet;
                            if( count($stats) == 5 )
                            {
                                break;
                            }
                        }
                        foreach( $stats as $key => $stat )
                        {
                            \Aireset\GameActivity::create([
                                'game' => $key,
                                'max_bet' => $stat,
                                'user_id' => $userID
                            ]);
                        }
                    }
                }
            })->hourly();
            $schedule->call(function()
            {
                \Aireset\Session::where('user_id', 'NULL')->delete();
                \Aireset\Session::where('user_id', '')->delete();
                \Aireset\Task::where('finished', 1)->delete();
            })->everyMinute();
            $schedule->call(function()
            {
                $task = \Aireset\Task::where([
                    'finished' => 0,
                    'category' => 'shop',
                    'action' => 'create'
                ])->first();
                if( $task )
                {
                    $task->update(['finished' => 1]);
                    $shop = \Aireset\Shop::find($task->item_id);
                    $shopCategories = \Aireset\ShopCategory::where('shop_id', $shop->id)->get();
                    if( count($shopCategories) )
                    {
                        $shopCategories = $shopCategories->pluck('category_id')->toArray();
                    }
                    $superCategories = [];
                    $categories = \Aireset\Category::where([
                        'shop_id' => 0,
                        'parent' => 0
                    ])->get();
                    if( count($categories) )
                    {
                        foreach( $categories as $category )
                        {
                            $newCategory = $category->replicate();
                            $newCategory->shop_id = $shop->id;
                            $newCategory->save();
                            $superCategories[$category->id] = $newCategory->id;
                            $categories_2 = \Aireset\Category::where([
                                'shop_id' => 0,
                                'parent' => $category->id
                            ])->get();
                            if( count($categories_2) )
                            {
                                foreach( $categories_2 as $category_2 )
                                {
                                    $newCategory_2 = $category_2->replicate();
                                    $newCategory_2->shop_id = $shop->id;
                                    $newCategory_2->parent = $newCategory->id;
                                    $newCategory_2->save();
                                    $superCategories[$category_2->id] = $newCategory_2->id;
                                }
                            }
                        }
                    }
                    $returns = \Aireset\Returns::where('shop_id', 0)->get();
                    if( count($returns) )
                    {
                        foreach( $returns as $return )
                        {
                            $newReturn = $return->replicate();
                            $newReturn->shop_id = $shop->id;
                            $newReturn->save();
                        }
                    }
                    $jackpots = \Aireset\JPG::where('shop_id', 0)->get();
                    if( count($jackpots) )
                    {
                        foreach( $jackpots as $jackpot )
                        {
                            $newJackpot = $jackpot->replicate();
                            $newJackpot->shop_id = $shop->id;
                            $newJackpot->save();
                        }
                    }
                    $bank = \Aireset\GameBank::where('shop_id', 0)->first();
                    if( $bank )
                    {
                        $newBank = $bank->replicate();
                        $newBank->shop_id = $shop->id;
                        $newBank->save();
                    }
                    $game_ids = [];
                    if( count($shopCategories) )
                    {
                        $categories = \Aireset\Category::whereIn('parent', $shopCategories)->where('shop_id', 0)->pluck('id')->toArray();
                        $categories = array_merge($categories, $shopCategories);
                        $game_ids = \Aireset\GameCategory::whereIn('category_id', $categories)->groupBy('game_id')->pluck('game_id')->toArray();
                    }
                    if( count($game_ids) )
                    {
                        $games = \Aireset\Game::where('shop_id', 0)->whereIn('id', $game_ids)->get();
                    }
                    else
                    {
                        $games = \Aireset\Game::where('shop_id', 0)->get();
                    }
                    if( count($games) )
                    {
                        foreach( $games as $game )
                        {
                            $newGame = $game->replicate();
                            $newGame->original_id = $game->id;
                            $newGame->shop_id = $shop->id;
                            $newGame->save();
                            $categories = \Aireset\GameCategory::where('game_id', $game->id)->get();
                            if( count($categories) && count($superCategories) )
                            {
                                foreach( $categories as $category )
                                {
                                    $newCategory = $category->replicate();
                                    $newCategory->game_id = $newGame->id;
                                    $newCategory->category_id = $superCategories[$category->category_id];
                                    $newCategory->save();
                                }
                            }
                        }
                    }
                    $shop->update(['pending' => 0]);
                }
            })->everyFiveMinutes();
            $schedule->call(function()
            {
                $task = \Aireset\Task::where([
                    'finished' => 0,
                    'category' => 'shop',
                    'action' => 'delete'
                ])->first();
                if( $task )
                {
                    $task->update(['finished' => 1]);
                    $shopId = $task->item_id;
                    $rel_users = \Aireset\User::whereHas('rel_shops', function($query) use ($shopId)
                    {
                        $query->where('shop_id', $shopId);
                    })->get();
                    $toDelete = \Aireset\User::whereIn('role_id', [
                        1,
                        2,
                        3
                    ])->where('shop_id', $shopId)->get();
                    if( $toDelete )
                    {
                        foreach( $toDelete as $userDelete )
                        {
                            $userDelete->detachAllRoles();
                            $userDelete->delete();
                        }
                    }
                    $games = \Aireset\Game::where('shop_id', $shopId)->get();
                    foreach( $games as $game )
                    {
                        \Aireset\Game::where('id', $game->id)->delete();
                        \Aireset\GameCategory::where('game_id', $game->id)->delete();
                        \Aireset\GameLog::where('game_id', $game->id)->delete();
                    }
                    \Aireset\Transaction::where('shop_id', $shopId)->delete();
                    \Aireset\StatGame::where('shop_id', $shopId)->delete();
                    \Aireset\Category::where('shop_id', $shopId)->delete();
                    \Aireset\Returns::where('shop_id', $shopId)->delete();
                    \Aireset\OpenShift::where('shop_id', $shopId)->delete();
                    \Aireset\ShopStat::where('shop_id', $shopId)->delete();
                    \Aireset\ShopUser::where('shop_id', $shopId)->delete();
                    \Aireset\BankStat::where('shop_id', $shopId)->delete();
                    \Aireset\Api::where('shop_id', $shopId)->delete();
                    \Aireset\ShopCategory::where('shop_id', $shopId)->delete();
                    \Aireset\JPG::where('shop_id', $shopId)->delete();
                    \Aireset\InfoShop::where('shop_id', $shopId)->delete();
                    \Aireset\Pincode::where('shop_id', $shopId)->delete();
                    \Aireset\Returns::where('shop_id', $shopId)->delete();
                    \Aireset\HappyHour::where('shop_id', $shopId)->delete();
                    \Aireset\GameBank::where('shop_id', $shopId)->delete();
                    if( $rel_users )
                    {
                        foreach( $rel_users as $user )
                        {
                            if( $user->hasRole([
                                'agent',
                                'distributor'
                            ]) )
                            {
                                $shops = $user->shops(true);
                                if( count($shops) )
                                {
                                    if( !is_array($shops) )
                                    {
                                        $shops = $shops->toArray();
                                    }
                                    $user->update(['shop_id' => array_shift($shops)]);
                                }
                                else
                                {
                                    $user->update(['shop_id' => 0]);
                                }
                            }
                        }
                    }
                    \Aireset\User::doesntHave('rel_shops')->where('shop_id', '!=', 0)->whereIn('role_id', [
                        4,
                        5
                    ])->update(['shop_id' => 0]);
                    $admin = \Aireset\User::where('role_id', 6)->first();
                    if( $admin->shop_id == $shopId )
                    {
                        $admin->update(['shop_id' => 0]);
                    }
                }
            })->everyMinute();
            $schedule->call(function()
            {
                $tasks = \Aireset\Task::where([
                    'finished' => 0,
                    'category' => 'game',
                    'action' => 'delete'
                ])->take(10)->get();
                if( count($tasks) )
                {
                    foreach( $tasks as $task )
                    {
                        $task->update(['finished' => 1]);
                        \Aireset\GameCategory::where('game_id', $task->item_id)->delete();
                        \Aireset\GameLog::where('game_id', $task->item_id)->delete();
                        \Aireset\Game::where('id', $task->item_id)->delete();
                    }
                }
            })->everyMinute();
            $schedule->call(function()
            {
                $tasks = \Aireset\Task::where([
                    'finished' => 0,
                    'category' => 'event',
                    'action' => 'GameEdited'
                ])->take(1)->get();
                if( count($tasks) )
                {
                    foreach( $tasks as $task )
                    {
                        $task->update(['finished' => 1]);
                        $games = explode(',', $task->item_id);
                        if( count($games) )
                        {
                            $games = \Aireset\Game::whereIn('id', $games)->get();
                            foreach( $games as $game )
                            {
                                \Aireset\Services\Logging\UserActivity\Activity::create([
                                    'description' => 'Update Game / ' . $game->name . ' / ' . $task->details,
                                    'user_id' => $task->user_id,
                                    'ip_address' => $task->ip_address,
                                    'user_agent' => $task->user_agent
                                ]);
                            }
                        }
                    }
                }
            })->everyMinute();
        }
        protected function commands()
        {
            require(base_path('routes/console.php'));
        }
    }

}
