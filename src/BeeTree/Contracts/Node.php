<?php namespace BeeTree\Contracts;

interface Node
{

    /**
    * Returns if node is a root node
    * 
    * @return bool
    */
    public function isRoot();

    /**
    * Returns the parent node of this node
    * 
    * @return \BeeTree\Contracts\Node
    */
    public function parent();

    /**
    * Set the parent node of this node
    * 
    * @param \BeeTree\Contracts\Node $parent
    * @return self
    */
    public function setParent(Node $parent);

    /**
    * Returns the childs of this node
    * 
    * @return array [\BeeTree\Contracts\Node]
    */
    public function children();

    /**
    * Clears all childNodes
    * 
    * @return self
    */
    public function clearChildren();

    /**
    * Adds a childNode to this node
    * 
    * @return self
    */
    public function addChild(Node $child);

    /**
    * Removes a child node
    * 
    * @return self
    */
    public function removeChild(Node $child);

    /**
    * Does this node have children?
    * 
    * @return bool
    */
    public function hasChildren();

    /**
    * Does this node has a parent?
    * 
    * @return bool
    */
    public function hasParent();

    /**
    * Returns the depth of this node
    * 
    * @return int
    */
    public function getDepth();

    /**
    * Set the depth of this node (usually done by BeeTreeModel)
    *
    * @param int $depth
    * @return self
    */
    public function setDepth($depth);

    /**
    * Returns the identifier of this node
    * Identifiers are used to compare nodes and deceide which
    * child depends to which parent.
    * In a filesystem the path would be the identifier, in
    * a database an id column.
    * 
    * @return mixed
    */
    public function getId();

    /**
    * @brief Returns the identifier of the parent
    * 
    * @return mixed
    */
    public function getParentId();
}