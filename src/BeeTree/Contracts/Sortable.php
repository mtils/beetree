<?php namespace BeeTree\Contracts;

/**
 * The sortable interface is not just for nodes inside a b-tree
 **/
interface Sortable
{

    /**
     * Get the position of this sortable (inside its parent)
     *
     * @return int
     **/
    public function getPosition();

    /**
     * Returns the position of this sortable (inside its parent)
     *
     * @param int $position
     * @return self
     **/
    public function setPosition($position);

    /**
     * Get the previos sortable (like DOM.previousSibling)
     *
     * @return \BeeTree\Contracts\Sortable|null
     **/
    public function getPrevious();

    /**
     * Set the previous Sortable. Reset the sortable via
     * setPosition(0)
     *
     * @param self $previous
     * @return self
     **/
    public function setPrevious(self $previous);

    /**
     * Get the next sibling
     *
     * @return \BeeTree\Contracts\Sortable|null
     **/
    public function getNext();

    /**
     * Set the next sibling
     *
     * @param \BeeTree\Contracts\Sortable $next
     * @return self
     **/
    public function setNext(self $next);

}