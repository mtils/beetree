<?php namespace BeeTree;

interface ModelInterface
{

    /**
     * Retrieve a tree by its rootId
     * 
     * @param mixed $rootId The id of its root node, which is the same as node->getRootId()
     * @return \BeeTree\NodeInterface
     **/
    public function tree($rootId=NULL);

    /**
     * Retrieve a tree by its ID
     * 
     * @param mixed $identifier The id of this node, which is the same as node->getIdentifier()
     * @param mixed $rootId The rootId of the tree, optional to speed up the initial query
     * @return \BeeTree\NodeInterface
     **/
    public function get($identifier, $rootId=NULL);

    /**
     * Retrieve all root nodes
     *
     * @return \Traversable
     **/
    public function rootNodes();

    /**
     * Construct a node (new $NodeClass()) (Doesn't save the node)
     * 
     * @return \BeeTree\NodeInterface
     **/
    public function makeNode();

    /**
     * Save a node
     *
     * @param \BeeTree\NodeInterface $node The node you want to save
     * @return \BeeTree\ModelInterface For fluid syntax
     **/
    public function saveNode(NodeInterface $node);

    /**
     * Mark a node as root AND save it
     * 
     * @param NodeInterface $node The new root node
     * @return ModelInterface For fluid syntax
     **/
    public function makeRootNode(NodeInterface $node);

    /**
     * Make node $node a child of $newParent
     *        If this is an ordered tree, the position should be the last
     *
     * @param NodeInterface $node The moved or inserted node
     * @param NodeInterface $newParent The parent node
     * @return ModelInterface For fluid syntax
     **/
    public function makeChildOf(NodeInterface $node,
                                NodeInterface $newParent);

    /**
     * Delete the node. If the childnodes shouldnt be deleted, the
     *        childNodes will wander one level up
     * 
     * @param NodeInterface $node The node you want to delete
     * @param bool $deleteChildNodes Should this be rekursive or not?
     * @return ModelInterface For fluid syntax
     **/
    public function delete(NodeInterface $node, $deleteChildNodes=TRUE);

}