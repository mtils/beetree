<?php namespace BeeTree;

trait ActAsNode
{

    /**
     * @var \BeeTree\Contracts\Node
     **/
    protected $_parent;

    /**
     * @var \BeeTree\Contracts\Children
     **/
    protected $_children;

    /**
     * @var int
     **/
    protected $_level;

    /**
     * @var mixed
     **/
    protected $_id;

    /**
    * {@inheritdoc}
    * 
    * @return bool
    */
    public function isRoot()
    {
        return !(bool)$this->_parent;
    }

    /**
    * {@inheritdoc}
    * 
    * @return \BeeTree\Contracts\Node
    */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
    * {@inheritdoc}
    * 
    * @param self $parent
    * @return self
    */
    public function setParent(self $parent)
    {
        $this->_parent = $parent;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return self
     **/
    public function clearParent()
    {
        $this->_parent = null;
        return $this;
    }

    /**
    * {@inheritdoc}
    * 
    * @return \BeeTree\Contracts\Children
    */
    public function getChildren()
    {
        if ($this->_children === null) {
            $this->_children = $this->makeChildren();
        }
        return $this->_children;
    }

    /**
    * Clears all childNodes. (Only in memory)
    * 
    * @return self
    */
    public function clearChildren()
    {
        $this->getChildren()->clear();
        return $this;
    }

    /**
    * Adds a childNode to this node (Only in memory)
    * 
    * @return self
    */
    public function addChild(self $child)
    {
        $this->getChildren()->append($child);
        return $this;
    }

    /**
    * Removes a child node (Only in memory)
    * 
    * @return self
    */
    public function removeChild(self $child)
    {
        $this->getChildren()->remove($child);
        return $this;
    }

    /**
    * Does this node have children?
    * 
    * @return bool
    */
    public function hasChildren()
    {
        return (bool)$this->getChildren()->count();
    }

    /**
    * Does this node has a parent?
    * 
    * @return bool
    */
    public function hasParent()
    {
        return (bool)$this->parent;
    }

    /**
    * Returns the level of this node
    * 
    * @return int
    */
    public function getLevel()
    {
        return $this->_level;
    }

    /**
    * Set the levek of this node (usually done by BeeTreeModel)
    *
    * @param int $level
    * @return self
    */
    public function setLevel($level)
    {
        $this->_level = $level;
        return $this;
    }

    /**
    * Returns the identifier of this node
    * Identifiers are used to compare nodes and deceide which
    * child depends to which parent.
    * In a filesystem the path would be the identifier, in
    * a database an id column.
    * 
    * @return mixed
    */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param mixed $id
     * @return self
     **/
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Creates the children
     *
     * @return \BeeTree\Contracts\Children
     **/
    protected function makeChildren()
    {
        return new GenericChildren;
    }

}