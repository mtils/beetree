<?php namespace BeeTree\Eloquent\AdjacencyList;

use OutOfBoundsException;
use BeeTree\Contracts\Node;
use BeeTree\Support\Sorter;

trait WholeTreeModelTrait
{

    protected $_hierachyCache = [];

    protected $_rootNodeCache = [];

    protected $_sorter;

    protected function wholeTreeColumns()
    {
        return [$this->getIdName(), $this->getParentIdName(), $this->getRootIdName()];
    }

    /**
     * Retrieve a tree by its rootId
     * 
     * @param mixed $rootId The id of its root node, which is the same as node->getRootId()
     * @param array $columns (Optional) Determine which columns have to be fetched from persistence
     * @return \BeeTree\NodeInterface
     **/
    public function tree($rootId, array $columns=[])
    {

        $columns = $this->pickColumns($columns);
        $columnsId = $this->getColumnsCacheId($columns);

        if (isset($this->_hierachyCache[$columnsId][$rootId])) {
            return $this->_hierachyCache[$columnsId][$rootId];
        }

        $result = $this->queryTree($rootId)
                       ->get($this->toSelectColumns($columns));

        $this->fillNodeCache($columnsId, $result);

        return $this->_rootNodeCache[$columnsId][$rootId];

    }

    /**
     * Retrieve a tree by its ID
     * 
     * @param mixed $id The id of this node, which is the same as node->getIdentifier()
     * @param mixed $rootId The rootId of the tree, optional to speed up the initial query
     * @return \BeeTree\Contracts\Node
     **/
    public function get($id, $rootId=NULL, array $columns=[])
    {
        $columns = $this->pickColumns($columns);
        $columnsId = $this->getColumnsCacheId($columns);

        if (isset($this->_hierachyCache[$columnsId][$rootId])) {
            return $this->_hierachyCache[$columnsId][$rootId];
        }

        // If we have a root id, try to fetch the whole tree
        if ($rootId) {
            $tree = $this->tree($rootId, $columns);
        }

        // After retrieving the tree, look if it is now in cache
        if (isset($this->_hierachyCache[$columnsId][$rootId])) {
            return $this->_hierachyCache[$columnsId][$rootId];
        }

        $result = $this->queryTreeById($id)
                       ->get($this->toSelectColumns($columns));

        $this->fillNodeCache($result);
        $this->sorter()->toHierarchy($result);

        return $this->_hierachyCache[$columnsId][$id];

    }

    /**
     * Return all children of $node
     *
     * @param \BeeTree\Contracts\Node $node
     * @return \Traversable
     **/
    public function childrenOf(Node $node, array $columns=[])
    {
        $columns = $this->pickColumns($columns);
        $columnsId = $this->getColumnsCacheId($columns);
    }

    /**
     * Retrieve all root nodes
     *
     * @return \Traversable
     **/
    public function roots()
    {
        return $this->newQuery()->roots()->orderBy($this->getParentIdName());
    }

    /**
     * Construct a node (new $NodeClass()) (Doesn't save the node)
     * 
     * @param array $attributes (optional)
     * @return \BeeTree\Contracts\Node
     **/
    public function make(array $attributes=[])
    {
        $attributes[$this->getParentIdName()] = null;
        return $this->newInstance($attributes);
    }

    /**
     * Persist the payload of a node, actually saving it
     *
     * @param \BeeTree\Contracts\Node $node The node you want to save
     * @return self
     **/
    public function savePayload(Node $node)
    {
        $node->save();
        return $this;
    }

    /**
     * Make a root node (and save it)
     * 
     * @param array $attributes
     * @return \BeeTree\Contracts\Node
     **/
    public function createRoot(array $attributes)
    {
        $attributes[$this->getParentIdName()] = null;
        return parent::create($attributes);
    }

    /**
     * Create a new child of $parent If this is an ordered tree,
     * the position should be the last
     *
     * @param \BeeTree\Contracts\Node $node The moved or inserted node
     * @param \BeeTree\Contracts\Node $parent The parent node
     * @return self
     **/
    public function createChildOf(array $attributes, Node $parent)
    {
        return $this->performCreateChildOf($attributes, $parent);
    }

    /**
     * Make node $node a child of $newParent If this is an ordered tree,
     * the position should be the last
     *
     * @param \BeeTree\Contracts\Node $node The moved or inserted node
     * @param \BeeTree\Contracts\Node $newParent The parent node
     * @return self
     **/
    public function makeChildOf(Node $node, Node $newParent)
    {
        return $this->performMakeChildOf($node, $node);
    }

    /**
     * Delete the node. All children will be deleted too
     * 
     * @param \BeeTree\Contracts\Node $node
     * @return self
     **/
    public function remove(Node $node)
    {
        return $this->performRemove($node);
    }

    public function scopeRoots($query)
    {
        $query->whereNull($this->getParentIdName());
    }

    public function queryTree($rootId)
    {
        return $this->getTreeQuery($rootId);
    }

    public function queryTreeById($id)
    {
        return $this->getTreeByIdQuery($id);
    }

    /**
     * Make node $node a child of $newParent If this is an ordered tree,
     * the position should be the last
     *
     * @param \BeeTree\Contracts\Node $node The moved or inserted node
     * @param \BeeTree\Contracts\Node $newParent The parent node
     * @return self
     **/
    protected function performCreateChildOf(array $attributes, Node $parent)
    {

        $parentIdName = $this->getParentIdName();
        $rootIdName = $this->getRootIdName();

        $child = $this->make($attributes);
        $child[$parentIdName] = $parent->getId();
        $child[$rootIdName] = $parent[$rootIdName];

        $child->save();

        // Set in-memory relations
        $child->setParent($parent);
        $parent->addChild($child);

        return $child;

    }

    /**
     * Make node $node a child of $newParent If this is an ordered tree,
     * the position should be the last
     *
     * @param \BeeTree\Contracts\Node $node The moved or inserted node
     * @param \BeeTree\Contracts\Node $newParent The parent node
     * @return self
     **/
    protected function performMakeChildOf(Node $node, Node $newParent)
    {

        $parentIdName = $this->getParentIdName();
        $rootIdName = $this->getRootIdName();

        // The node is already a child of newParent
        if ( ($node->getParentId()== $newParent->getId()) && $node->exists ) {
            return $this;
        }

        // Copy foreign keys
        $node[$parentIdName] = $newParent->getId();
        $node[$rootIdName] = $newParent[$rootIdName];

        // Set in-memory relations
        $node->setParent($newParent);
        $newParent->addChild($node);

        return $this;

    }

    /**
     * Delete the node. All children will be deleted too
     * 
     * @param \BeeTree\Contracts\Node $node
     * @return self
     **/
    protected function performRemove(Node $node)
    {
        $subtree = $this->get(
            $node->getParentId(),
            $node[$this->getRootIdName()]
        );

        $sorter = $this->sorter();

        $byDepth = $sorter->byDepthReversed($sorter->flatify($subtree));

        array_walk_recursive($byDepth, function($node, $key){
            $node->delete();
        });

        return $this;
    }

    protected function getTreeQuery($rootId)
    {
        $table = $this->getTable();
        $query = $this->newQuery();

        return $query->where($this->getRootIdName(), $rootId)
                     ->orderBy("$table.".$this->getParentIdName());
    }

    protected function getTreeByIdQuery($id)
    {

        $table = $this->getTable();
        $joinAlias = "{$table}_join";
        $idName = $this->getIdName();
        $rootIdName = $this->getRootIdName();
        $parentIdName = $this->getParentIdName();
        $query = $this->newQuery();

        return $query->join("$table as $joinAlias",
                            "$table.$rootIdName", '=', "$joinAlias.$rootIdName")
                     ->where("$joinAlias.$idName", $id)
                     ->orderBy("$table.$parentIdName");

    }

    protected function pickColumns(array $passedColumns)
    {
        if ($passedColumns === []) {
            return $this->wholeTreeColumns();
        }
        return $passedColumns;
    }

    protected function getColumnsCacheId(array $columns)
    {
        return implode('|', $columns);
    }

    protected function assureCacheArray($columnsId)
    {
        if (!isset($this->_hierachyCache[$columnsId])) {
            $this->_hierachyCache[$columnsId] = [];
        }
    }

    protected function toSelectColumns(array $columns)
    {
        $table = $this->getTable();

        return array_map($columns, function($column) use ($table) {
            return "$table.$column";
        });

    }

    protected function fillNodeCache($columnsId, $result)
    {

        $this->assureCacheArray($columnsId);

        if (!count($result)) {
            $this->_hierachyCache[$columnsId] = [];
            return;
        }

        foreach ($result as $node) {
            $this->_hierachyCache[$columnsId][$node->getId()] = $node;
            if ($node->isRoot()) {
                $this->_rootNodeCache[$columnsId][$node->getId()] = $node;
            }
        }

        $this->sorter()->toHierarchy($result);
    }

    protected function sorter()
    {
        if (!$this->_sorter) {
            $this->_sorter = new Sorter();
        }
        return $this->_sorter;
    }

    public function setSorter(Sorter $sorter) {
        $this->_sorter = $sorter;
        return $this;
    }

}