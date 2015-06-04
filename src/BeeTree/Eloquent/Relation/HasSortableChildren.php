<?php namespace BeeTree\Eloquent\Relation;


use OutOfBoundsException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Collection;
use BeeTree\Contracts\Children;
use BeeTree\Contracts\Node;
use BeeTree\Contracts\DatabaseSortable;
use BeeTree\Contracts\Repository;
use Illuminate\Database\Eloquent\Builder;


class HasSortableChildren extends Relation implements Children
{

    public function __construct(Builder $builder, DatabaseSortable $parent, Repository $repo)
    {
        parent::__construct($builder, $parent, $repo);
        
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array   $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {

        $dictionary = $this->buildDictionary($results);

        // Once we have the dictionary we can simply spin through the parent models to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work. Then we'll just return them.
        foreach ($models as $model)
        {
            $key = $model->getAttribute($this->localKey);

            if (isset($dictionary[$key]))
            {
                $value = $this->related->newCollection($dictionary[$key]);

                $model->setRelation($relation, $value);
            }
        }

        return $models;
    }

}