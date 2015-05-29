<?php namespace BeeTree\Contracts;

interface Repository
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
     * @param mixed $id The id of this node, which is the same as node->getIdentifier()
     * @param mixed $rootId The rootId of the tree, optional to speed up the initial query
     * @return \BeeTree\Contracts\Node
     **/
    public function get($id, $rootId=NULL);

    /**
     * Retrieve all root nodes
     *
     * @return \Traversable
     **/
    public function roots();

    /**
     * Construct a node (new $NodeClass()) (Doesn't save the node)
     * 
     * @param array $attributes (optional)
     * @return \BeeTree\Contracts\Node
     **/
    public function make(array $attributes=[]);

    /**
     * Save a node
     *
     * @param \BeeTree\Contracts\Node $node The node you want to save
     * @return self
     **/
    public function save(Node $node);

    /**
     * Make a root node (and save it)
     * 
     * @param array $attributes
     * @return \BeeTree\Contracts\Node
     **/
    public function createRoot(array $attributes);

    /**
     * Make node $node a child of $newParent If this is an ordered tree,
     * the position should be the last
     *
     * @param \BeeTree\Contracts\Node $node The moved or inserted node
     * @param \BeeTree\Contracts\Node $newParent The parent node
     * @return self
     **/
    public function makeChildOf(Node $node, Node $newParent);

    /**
     * Delete the node. All children will be deleted too
     * 
     * @param \BeeTree\Contracts\Node $node
     * @return self
     **/
    public function delete(Node $node);

}