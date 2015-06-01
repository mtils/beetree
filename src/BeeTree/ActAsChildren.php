<?php namespace BeeTree;


use ArrayIterator;
use BeeTree\Contracts\Children;
use BeeTree\Contracts\Node;

use OutOfBoundsException;

trait ActAsChildren{

    protected $_children = [];

    /**
     * {@inheritdoc}
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
            if($node->getId() == $this->_children[$i]->getId()){
                return $i;
            }
        }

        throw new OutOfBoundsException('Node ' . $node->getId() . ' not found');
    }

    public function count()
    {
        return count($this->_children);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->_children);
    }

}