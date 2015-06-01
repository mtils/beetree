<?php namespace BeeTree\Eloquent;


use OutOfBoundsException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Collection;
use BeeTree\Contracts\Children;
use BeeTree\Contracts\Node;


class RelationChildren extends Relation implements Children
{

    protected $_children;

    public function __construct()
    {
    
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints();

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models);

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array   $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation);

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array   $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation);

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults();

    /**
     * Append a node to the list
     *
     * @param \BeeTree\Contracts\Node $node
     * @return self
     **/
    public function append(Node $node)
    {
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

}