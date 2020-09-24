<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 17.11.2018
 * Time: 9:43
 */

namespace App\Repositories\Contracts;


interface InterfaceRepository
{
    public function all();

    public function find($id);
    public function findWhere($column, $value);
    public function findWhereFirst($column, $value);
    public function paginate($perPage = 10);
    public function create( array $properties);
    public function update($id, array $properties);
    public function delete($id);


}