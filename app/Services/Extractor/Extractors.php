<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21. 12. 2019
 * Time: 10:35
 */

namespace App\Services\Extractor;

use DB;
use Carbon\Carbon;
use App\Models\Organization;
use App\Services\DetectService\DetectDateTime;

class Extractors
{
    public $organization;
    public $detectDateTime;


    public function setOrganization($id)
    {
        $this->organization = Organization::whereId($id)->first();
        $this->detectDateTime = new DetectDateTime();
    }

    protected function createPrayer($data)
    {

        foreach ($data as $item) {

            // Find or create new record
            if (DB::table('prayers')->whereBody($item['body'])->first()) {
                continue;
            }

            DB::table('prayers')->insert([
                'title' => isset($item['title'])  ? $item['title'] : '',
                'body' => $item['body'],
                'user_name' => $item['user'],
                'organization_id' => $item['organization'],
                'created_at' => Carbon::now()->subHours(2)->addMinute(rand(3, 55))->toDateTimeString(),
            ]);
        }
    }

}
