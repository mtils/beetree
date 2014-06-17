<?php namespace BeeTree;

interface ModelInterface{

    /**
     * @brief Retrieve a tree by its rootId
     * 
     * @param mixed $rootId The id of its root node, which is the same as node->getRootId()
     * @return NodeInterface
     **/
    public function tree($rootId=NULL);

    /**
     * @brief Retrieve a tree by its _ID_
     * 
     * @param mixed $identifier The id of this node, which is the same as node->getIdentifier()
     * @param mixed $rootId The rootId of the tree, optional to speed up the initial query
     * @return NodeInterface
     **/
    public function get($identifier, $rootId=NULL);

    /**
     * @brief Construct a node (new $NodeClass()) (Doesn't save the node)
     * 
     * @return NodeInterface
     **/
    public function makeNode();

    /**
     * @brief Save a node
     *
     * @param NodeInterface $node The node you want to save
     * @return ModelInterface For fluid syntax
     **/
    public function saveNode(NodeInterface $node);

    /**
     * @brief Mark a node as root AND save it
     * 
     * @param NodeInterface $node The new root node
     * @return ModelInterface For fluid syntax
     **/
    public function makeRootNode(NodeInterface $node);

    /**
     * @brief Make node $node a child of $newParent
     *        If this is an ordered tree, the position should be the last
     *
     * @param NodeInterface $node The moved or inserted node
     * @param NodeInterface $newParent The parent node
     * @return ModelInterface For fluid syntax
     **/
    public function makeChildOf(NodeInterface $node,
                                NodeInterface $newParent);

    /**
     * @brief Delete the node. If the childnodes shouldnt be deleted, the
     *        childNodes will wander one level up
     * 
     * @param NodeInterface $node The node you want to delete
     * @param bool $deleteChildNodes Should this be rekursive or not?
     * @return ModelInterface For fluid syntax
     **/
    public function delete(NodeInterface $node, $deleteChildNodes=TRUE);

}