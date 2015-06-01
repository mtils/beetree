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
    * Set the parent node of this node (Only in memory)
    * 
    * @param self $parent
    * @return self
    */
    public function setParent(self $parent);

    /**
     * Clear the parent, which makes the node a root node
     *
     * @return self
     **/
    public function clearParent();

    /**
    * Returns the childs of this node
    * 
    * @return \BeeTree\Contracts\Children
    */
    public function children();

    /**
    * Clears all childNodes. (Only in memory)
    * 
    * @return self
    */
    public function clearChildren();

    /**
    * Adds a childNode to this node (Only in memory)
    * 
    * @return self
    */
    public function addChild(self $child);

    /**
    * Removes a child node (Only in memory)
    * 
    * @return self
    */
    public function removeChild(self $child);

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
    * Returns the level of this node
    * 
    * @return int
    */
    public function getLevel();

    /**
    * Set the levek of this node (usually done by BeeTreeModel)
    *
    * @param int $level
    * @return self
    */
    public function setLevel($level);

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

}