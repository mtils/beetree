<?php namespace BeeTree\Eloquent;

use BeeTree\Eloquent\Relation\HasChildren;
use BeeTree\Contracts\Sortable;

trait ActAsEloquentSortableNode
{

    use ActAsEloquentNode;

    protected $_previous;

    protected $_next;

    /**
     * Get the position of this sortable (inside its parent)
     *
     * @return int
     **/
    public function getPosition()
    {
        return $this->getAttribute($this->getPositionName());
    }

    /**
     * Returns the position of this sortable (inside its parent)
     *
     * @param int $position
     * @return self
     **/
    public function setPosition($position)
    {
        $this->setAttribute($this->getPositionName(), $position);
        return $this;
    }

    /**
     * Get the previos sortable (like DOM.previousSibling)
     *
     * @return \BeeTree\Contracts\Sortable|null
     **/
    public function getPrevious()
    {
        return $this->_previous;
    }

    /**
     * Set the previous Sortable. Reset the sortable via
     * setPosition(0)
     *
     * @param self $previous
     * @return self
     **/
    public function setPrevious(Sortable $previous)
    {
        $this->_previous = $previous;
        return $this;
    }

    /**
     * Get the next sibling
     *
     * @return \BeeTree\Contracts\Sortable|null
     **/
    public function getNext()
    {
        return $this->_next;
    }

    /**
     * Set the next sibling
     *
     * @param \BeeTree\Contracts\Sortable $next
     * @return self
     **/
    public function setNext(Sortable $next)
    {
        $this->_next = $next;
        return $this;
    }

    /**
     * Return the name of the position column
     *
     * @return string
     **/
    public function getPositionName()
    {
        return isset($this->positionName) ? $this->positionName : 'position';
    }

    protected function hasManyChildren()
    {

        $instance = new static;

        $localKey = $localKey ?: $this->getKeyName();

        return new HasChildren($this->newQuery(), $this, $this);
    }

    protected function childrenRelation()
    {
        if (!$this->childrenRelation) {
            $this->childrenRelation = $this->hasManyChildren();
        }
        return $this->childrenRelation;
    }

}