<?php namespace BeeTree;

interface NodeInterface{

    /**
    * @brief Returns if node is a root node
    * 
    * @return void
    */
    public function isRootNode();

    /**
    * @brief Returns the parent node of this node
    * 
    * @return NodeInterface
    */
    public function parentNode();

    /**
    * @brief Returns the parent node of this node
    * 
    * @return NodeInterface
    */
    public function setParentNode(NodeInterface $parent);

    /**
    * @brief Returns the childs of this node
    * 
    * @return array [NodeInterface]
    */
    public function childNodes();

    /**
    * @brief Clears all childNodes
    * 
    * @return array [NodeInterface]
    */
    public function clearChildNodes();

    /**
    * @brief Adds a childNode to this node
    * 
    * @return NodeInterface
    */
    public function addChildNode(NodeInterface $childNode);

    /**
    * @brief Removes a child node
    * 
    * @return NodeInterface
    */
    public function removeChildNode(NodeInterface $childNode);

    /**
    * @brief Does this node have children?
    * 
    * @return bool
    */
    public function hasChildNodes();

    /**
    * @brief Does this node has a parent?
    * 
    * @return bool
    */
    public function hasParentNode();

    /**
    * @brief Returns the depth of this node
    * 
    * @return int
    */
    public function getDepth();

    /**
    * @brief Set the depth of this node (usually done by BeeTreeModel)
    *
    * @param int $depth
    * @return NodeInterface
    */
    public function setDepth($depth);

    /**
    * @brief Returns the identifier of this node
    *        Identifiers are used to compare nodes and deceide which
    *        child depends to which parent.
    *        In a filesystem the path would be the identifier, in
    *        a database a id column.
    * 
    * @return mixed
    */
    public function getIdentifier();

    /**
    * @brief Returns the identifier of the parent
    * 
    * @return mixed
    */
    public function getParentIdentifier();
}