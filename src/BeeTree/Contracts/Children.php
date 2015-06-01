<?php namespace BeeTree\Contracts;

use Traversable;
use Countable;

interface Children extends Traversable, Countable
{

    /**
     * Append a node to the list
     *
     * @param \BeeTree\Contracts\Node $node
     * @return self
     **/
    public function append(Node $node);

    /**
     * Remove a node from the list
     *
     * @param \BeeTree\Contracts\Node $node
     * @return self
     **/
    public function remove(Node $node);

    /**
     * Remove the node at position $index
     *
     * @param int $index
     * @return \BeeTree\Contracts\Node
     **/
    public function at($index);

    /**
     * Removes all nodes
     *
     * @return self
     **/
    public function clear();

}