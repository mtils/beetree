<?php namespace BeeTree\Eloquent;

use BeeTree\Eloquent\Relation\HasChildren;
use BeeTree\Contracts\Node;

trait ActAsEloquentNode
{

    protected $parentRelation;

    protected $childrenRelation;

    protected $isTreePopupated = false;

    protected $_level;

    /**
    * Returns if node is a root node
    * 
    * @return bool
    */
    public function isRoot()
    {
        return ($this->getAttribute($this->getParentIdName()) === null);
    }

    /**
    * Returns the parent node of this node
    * 
    * @return \BeeTree\Contracts\Node
    */
    public function getParent()
    {
        if ($this->isRoot()) {
            return;
        }

        if (isset($this->relations['parent'])) {
            return $this->relations['parent'];
        }
        return $this->getAttribute('parent');
    }

    /**
    * Set the parent node of this node (Only in memory)
    * 
    * @param self $parent
    * @return self
    */
    public function setParent(Node $parent)
    {
        $this->relations['parent'] = $parent;
        $parent->addChild($this);
        return $this;
    }

    /**
     * Clear the parent, which makes the node a root node
     *
     * @return self
     **/
    public function clearParent()
    {
        unset($this->relations['parent']);
        return $this;
    }

    /**
    * Returns the childs of this node
    * 
    * @return \BeeTree\Contracts\Children
    */
    public function getChildren()
    {
        if (isset($this->relations['children'])) {
            return $this->relations['children'];
        }

        $children = $this->getAttribute('children');

        return $children === null ? [] : $children;
    }

    /**
    * Clears all childNodes. (Only in memory)
    * 
    * @return self
    */
    public function clearChildren()
    {
        $this->relations['children'] = [];
        return $this;
    }

    /**
    * Adds a childNode to this node (Only in memory)
    * 
    * @return self
    */
    public function addChild(Node $child)
    {

        if (!isset($this->relations['children'])) {
            $this->relations['children'] = [];
        }

        $this->relations['children'][] = $child;
        return $this;
    }

    /**
    * Removes a child node (Only in memory)
    * 
    * @return self
    */
    public function removeChild(Node $child)
    {

        if (!isset($this->relations['children'])) {
            return $this;
        }

        $this->relations['children'] = array_filter($this->relations['children'], function($item){
            return ($item !== $child);
        });

        return $this;
    }

    /**
    * Does this node have children?
    * 
    * @return bool
    */
    public function hasChildren()
    {
        return (bool)$this->getChildren();
    }

    /**
    * Does this node has a parent?
    * 
    * @return bool
    */
    public function hasParent()
    {
        return !$this->isRoot();
    }

    /**
    * Returns the level of this node
    * 
    * @return int
    */
    public function getLevel()
    {
        if ($this->_level !== null) {
            return $this->_level;
        }

        if ($this->isRoot()) {
            $this->_level = -1;
            return $this->_level;
        }

        $parents = array($this);
        $node = $this;

        while ($parent = $node->getParent()){
            if (!$parent->isRoot()) {
                $parents[] = $parent;
            }
            $node = $parent;
        }

        $this->_level = count($parents);

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
        return $this->getKey();
    }

    /**
     * Return the name of the id column
     *
     * @return string
     **/
    public function getIdName()
    {
        return $this->getKeyName();
    }

    /**
     * Return the name of the parent id column
     *
     * @return string
     **/
    public function getParentId()
    {
        return $this->getAttribute($this->getParentIdName());
    }

    /**
     * Return the name of the parent id column
     *
     * @return string
     **/
    public function getParentIdName()
    {
        return isset($this->parentIdName) ? $this->parentIdName : 'parent_id';
    }

    /**
     * Return the name of the root id column
     *
     * @return string
     **/
    public function getRootIdName()
    {
        return isset($this->rootIdName) ? $this->rootIdName : 'root_id';
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     **/
    public function treeIsPopulated()
    {
        return $this->isTreePopupated;
    }

    /**
     * {@inheritdoc}
     *
     * @param bool $populated (optional)
     * @return self
     **/
    public function setTreeIsPopulated($populated=true)
    {
        $this->isTreePopupated = $populated;
        return $this;
    }

    protected function hasManyChildren()
    {

        $instance = new static;

        $localKey = $localKey ?: $this->getKeyName();

        return new HasChildren($this->newQuery(), $this, $this);
    }

    protected function parentRelation()
    {
        if (!$this->parentRelation) {
            $this->parentRelation = null;
        }
        return $this->parentRelation;
    }

    protected function childrenRelation()
    {
        if (!$this->childrenRelation) {
            $this->childrenRelation = $this->hasManyChildren();
        }
        return $this->childrenRelation;
    }

}