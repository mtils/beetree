<?php namespace BeeTree\Eloquent\AdjacencyList;

use OutOfBoundsException;
use BeeTree\Contracts\Node;
use BeeTree\Contracts\Sortable;
use BeeTree\Support\Sorter;

trait WholeOrderedTreeModelTrait
{

    use WholeTreeModelTrait;

    /**
     * Create a new child of $parent If this is an ordered tree,
     * the position should be the last
     *
     * @param \BeeTree\Contracts\Node $node The moved or inserted node
     * @param \BeeTree\Contracts\Node $newParent The parent node
     * @return self
     **/
    public function createChildOf(array $attributes, Node $parent)
    {
        // Get new position
        $position = $this->getLastChildPosition($parent);
        $attributes[$this->getPositionName()] = $position;

        $model = $this->performCreateChildOf($attributes, $parent);

        return $model;
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

        // Collect some informations before the node is changed to correct
        // the nodes in its old position
        $nodeWasNew = !$node->exists;
        $oldParentId = $node->getParentId();
        $oldPosition = $node->getPosition();

        // Get new position
        $position = $this->getLastChildPosition($newParent);

        // Set the position of this node. Default is last position in new Parent
        $node[$this->getPositionName()] = $position;

        $this->performMakeChildOf($node, $node);

        // If the node existed correct the positions of the old parent
        if(!$nodeWasNew && $oldParentId){
            $this->decrementOrderAfter($oldParentId, $oldPosition);
        }

        return $this;

    }

    /**
     * Delete the node. All children will be deleted too
     * 
     * @param \BeeTree\Contracts\Node $node
     * @return self
     **/
    public function remove(Node $node)
    {

        // Collect the parentId to correct the positions of its old ancestors
        $parentId = $node->getParentId();
        $position = $node->getPosition();

        $this->performRemove($node);

        if($parentId){
            $this->decrementOrderAfter($parentId, $position);
        }

        return $this;
    }

    /**
     * Create a node with $attributes in $parent at $position
     *
     * @param array $attributes
     * @param \BeeTree\Contracts\Sortable
     * @param int $position
     * @return self The created sortable
     **/
    public function createAt(array $attributes, Sortable $parent, $position)
    {

        // Create space for new position
        $this->incrementOrderAfter($parent->getId(), $position-1);

        $attributes[$this->getPositionName()] = $position;

        return $this->performCreateChildOf($attributes, $parent);

    }

    /**
     * Move the $movedNode inside $newParent to position $position
     *
     * @param \BeeTree\Contracts\Sortable $movedNode
     * @param \BeeTree\Contracts\Sortable $newParent
     * @param int $position
     * @return self
     **/
    public function placeAt(Sortable $movedNode, Sortable $newParent, $position)
    {
        // 1. movedNode.parentId = newDescendant.parentId, movedNode.position = MAX(newDescendant.position,1)
        // 2. removeFromSort(movedNode) like delete without delete
        // 2. UPDATE SET position = position+1 WHERE parentId = newAncestor.parentId
        //    AND position >= movedNode.position
        // 3. movedNode.save()

        $targetParentId = $newParent->getId();
        $targetPosition = $position;
        $oldParentId = $movedNode->getParentId();
        $oldPosition = $movedNode->getPosition();

        // Nothing to do
        if ($targetParentId == $oldParentId &&
            $targetPosition == $oldPosition &&
            $movedNode->exists) {
            return $this;
        }

        $movedNode[$this->getParentIdName()] = $targetParentId;
        $movedNode[$this->getPositionName()] = $targetPosition;

        // Remove node from old parent
        $this->decrementOrderAfter($oldParentId, $oldPosition);

        // Create space for new position
        $this->incrementOrderAfter($targetParentId, $targetPosition-1);

        $movedNode->save();

        return $this;

    }

    protected function getLastChildPosition(Node $parent){

        $parentIdName = $this->getParentIdName();
        $query = $this->newQuery();

        $pos = $query->where($parentIdName, $parent->getId())
                     ->max($this->getPositionName());


        if (is_numeric($pos) && $pos) {
            return (int)$pos+1;
        }

        return 1;
    }

    protected function decrementOrderAfter($parentId, $position){

        if ($position === null) {
            return;
        }

        $query = $this->newQuery();

        $query->where($this->getParentIdName(), $parentId)
              ->where($this->getPositionName(), '>', $position)
              ->decrement($this->getPositionName());

    }

    protected function incrementOrderAfter($parentId, $position){

        if ($position === null) {
            return;
        }

        $query = $this->newQuery();

        $query->where($this->getParentIdName(), $parentId)
              ->where($this->getPositionName(), '>', $position)
              ->increment($this->getPositionName());

    }

    public function queryTree($rootId)
    {
        return $this->getTreeQuery($rootId)
                    ->orderBy($this->getTable().'.'.$this->getPositionName());
    }

    public function queryTreeById($id)
    {
        return $this->getTreeByIdQuery($id)
                    ->orderBy($this->getTable().'.'.$this->getPositionName());
    }
}