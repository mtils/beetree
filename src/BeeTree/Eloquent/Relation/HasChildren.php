<?php namespace BeeTree\Eloquent\Relation;


use ArrayIterator;
use IteratorAggregate;
use ArrayAccess;
use OutOfBoundsException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Collection;
use BeeTree\Contracts\Children;
use BeeTree\Contracts\Node;
use BeeTree\Contracts\DatabaseNode;
use BeeTree\Contracts\Repository;
use Illuminate\Database\Eloquent\Builder;


class HasChildren extends Relation implements Children, IteratorAggregate
{

    protected $repository;

    protected $_children;

    /**
     * The foreign key of the parent model.
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * The local key of the parent model.
     *
     * @var string
     */
    protected $localKey;

    protected $rootKey;

    protected $results;

    public function __construct(Builder $builder, DatabaseNode $parent, Repository $repo)
    {
        parent::__construct($builder, $parent);
        $this->repository = $repo;
        $this->localKey = $parent->getIdName();
        $this->foreignKey = $parent->getParentIdName();
        $this->rootKey = $parent->getRootIdName();
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints)
        {
            $this->query->where($this->foreignKey, '=', $this->getParentKey());

            $this->query->whereNotNull($this->foreignKey);

            $this->query->where($this->rootKey, '=', $this->getRootKey());

            $this->query->whereNotNull($this->rootKey);

        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $this->query->whereIn($this->foreignKey, $this->getKeys($models, $this->localKey));
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array   $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model)
        {
            $model->setRelation($relation, null);
        }

        return $models;
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

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        if ($this->_children === null) {
            $this->_children = $this->getFreshResults();
        }
        return $this->_children;
    }

    public function getFreshResults()
    {
        return $this->repository->childrenOf($this->parent);
    }

    /**
     * Append a node to the list
     *
     * @param \BeeTree\Contracts\Node $node
     * @return self
     **/
    public function append(Node $node)
    {

        if (!is_array($this->_children)) {
            $this->_children = [];
        }

        $this->_children[] = $node;
        return $this;
    }

    /**
     * Remove a node from the list
     *
     * @param \BeeTree\Contracts\Node $node
     * @return self
     **/
    public function remove(Node $node)
    {

        if (!is_array($this->_children)) {
            return $this;
        }

        unset($this->_children[$this->indexOf($node)]);
        $this->_children = array_values($this->_children);
        return $this;
    }

    /**
     * Remove the node at position $index
     *
     * @param int $index
     * @return \BeeTree\Contracts\Node
     **/
    public function at($index)
    {
        if (isset($this->_children[$index])) {
            return $this->_children[$index];
        }
        throw new OutOfBoundsException("Index $index does not exist");
    }

    /**
     * Removes all nodes
     *
     * @return self
     **/
    public function clear()
    {
        $this->_children = [];
        return $this;
    }

    public function indexOf(Node $node)
    {

        $count = $this->count();

        for($i=0; $i<$count; $i++) {
            if($node === $this->_children[$i]){
                return $i;
            }
        }

        throw new OutOfBoundsException('Node ' . $node->getId() . ' not found');
    }

    public function count()
    {
        return count($this->getResults());
    }

    public function getIterator()
    {
        return new ArrayIterator($this->getResults());
    }

    public function offsetExists($offset)
    {
        $results = $this->getResults();
        return isset($offset);
    }

    public function offsetGet($offset)
    {
        return $this->getResults()[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->getResults();
        $this->_children[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        $this->getResults();
        unset($this->_children[$offset]);
    }

    /**
     * Get the key value of the parent's local key.
     *
     * @return mixed
     */
    public function getParentKey()
    {
        return $this->parent->getAttribute($this->localKey);
    }

    public function getRootKey()
    {
        return $this->parent->getAttribute($this->rootKey);
    }

    /**
     * Get the foreign key for the relationship.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * Get the plain foreign key.
     *
     * @return string
     */
    public function getPlainForeignKey()
    {
        $segments = explode('.', $this->getForeignKey());

        return $segments[count($segments) - 1];
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];

        $foreign = $this->getPlainForeignKey();

        // First we will create a dictionary of models keyed by the foreign key of the
        // relationship as this will allow us to quickly access all of the related
        // models without having to do nested looping which will be quite slow.
        foreach ($results as $result)
        {
            $dictionary[$result->{$foreign}][] = $result;
        }

        return $dictionary;
    }

}