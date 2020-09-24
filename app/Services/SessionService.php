<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 06.03.2019
 * Time: 8:04
 */

namespace app\Services;


class SessionService
{
    public function counterView($event)
    {
        session()->push('post.' . $event->model->id, $event->model->title);

//        $this->forgetCounterView();

    }

    protected function setupPostSession()
    {
        if( session()->has('post.setUp'))
        {

        }
    }

    protected function forgetCounterView()
    {
        session()->forget('post');
    }

}