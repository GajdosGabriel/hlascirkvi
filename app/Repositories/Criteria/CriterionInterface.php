<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 19.11.2018
 * Time: 13:17
 */

namespace App\Repositories\Criteria;


interface CriterionInterface
{
    public function apply($entity);

}