<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 6. 10. 2019
 * Time: 15:17
 */

namespace App\Services;


use Cache;
use Carbon\Carbon;
use Session;

class CreditUser
{
    public function setVisitPage()
    {
//        session()->put('lastCredit', Carbon::now());
//        Cache::put('lastVisit', Carbon::now()->subDays(10));
//        dd(session()->get('lastCredit'));

//        $value = Cache::get('lastCredit');

       if( Cache::get('lastVisit')) $this->checkDailyCredit();

        Cache::put('lastVisit', Carbon::now());

//        dd(Cache::get('lastVisit'));

    }

    protected function checkDailyCredit()
    {
        $lastDay = Carbon::parse( Cache::get('lastVisit'));
        $today   = Carbon::now();

        if($lastDay->dayOfYear() < $today->dayOfYear() )
        {
            $this->addDailyCredit();

            Cache::get('lastVisit', Carbon::now());
        }
    }

    protected function addDailyCredit()
    {
        $newCredit = 10;
//        $oldCredit = Cache::get('lastCredit') ?? 0;

        Cache::increment('actualCredit', $newCredit);

//        $this->saveUserActualCredit();

//        Cache::forget('actualCredit') ;
//        dd( Cache::get('actualCredit') );
    }

    protected function saveUserActualCredit(){
        if(\Auth::check()) {
            \Auth::user()->update(['credit' => Cache::get('actualCredit')]);
            Cache::forget('actualCredit') ;
        }
    }

    // Set postHistory from postController show
    public function setPostHistory($post)
    {
       session()->push('postsHistory',  $post );
    }

}