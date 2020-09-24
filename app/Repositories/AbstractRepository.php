<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 17.11.2018
 * Time: 8:49
 */

namespace App\Repositories;


use App\Events\Posts\ViewCounter;
use App\Repositories\Contracts\InterfaceRepository;
use App\Repositories\Criteria\CriteriaInterface;


abstract class AbstractRepository implements InterfaceRepository, CriteriaInterface
{
    protected $entity;

    public function __construct()
    {
        $this->entity = $this->resolveEntity();
    }


    public function all()
    {
        return $this->entity->get();
    }

    public function find($id)
    {
        $post = $this->entity->findOrFail($id);

//        event(new ViewCounter($post));

        return $post;
    }

    public function findWhere($column, $value)
    {
        return $this->entity->where($column, $value)->get();
    }

    public function findWhereFirst($column, $value)
    {
        return $this->entity->where($column, $value)->first();
    }

    public function paginate($perPage = 10)
    {
        return $this->entity->paginate($perPage);
    }

    public function create(array $properties)
    {
        return $this->entity->create($properties);
    }

    public function update($id, array $properties)
    {
        $post = $this->find($id);
        $post->update($properties);
        return $post;
    }

    public function delete($id)
    {
        return $this->find($id)->delete();
    }


    public function withCriteria(...$criteria)
    {
        $criteria = array_flatten($criteria);

        foreach( $criteria as $criterion) {
            $this->entity = $criterion->apply($this->entity);
        }

        return $this;
    }

    protected function resolveEntity()
    {
        return app()->make($this->entity());
    }






}