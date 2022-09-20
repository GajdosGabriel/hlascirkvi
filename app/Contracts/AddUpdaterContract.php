<?php



namespace App\Contracts;


interface AddUpdaterContract {

    public static function make($post, $updater_id);
}